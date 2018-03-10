<?php
namespace BaftFeedback\Listener;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\ListenerAggregateInterface;
use BaftFeedback\Event\FeedbackEvent;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use BaftFeedback\Exception\FeedbackFormException;
use Zend\Form\FormInterface;
use BaftFeedback\Exception\FeedbackDbException;
use Doctrine\Common\Collections\ArrayCollection;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmission;
use BaftFeedback\Exception\SubmissionNotFoundException;
use BaftFeedback\Form\QuestionsForm;
use BaftFeedback\Form\SubmissionDataHydrator;
use BaftFeedback\Form\SubjectForm;
use BaftFeedback\Exception\EventException;

class CreateSubmissionListener implements SharedListenerAggregateInterface {

	use ListenerTrait;
	public $serviceLocator;

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

		$this->serviceLocator = $serviceLocator;

	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {

		return $this->serviceLocator;

	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attachShared(SharedEventManagerInterface $events) {

		// to all event of feedbackevent
		// $em->getSharedManager()->attach(feedbackEvent,'*', [
		// $this,
		// 'onCreateSubmissionPre_1'
		// ]);


		// #######################################################
		// ######################## PRE CREATE ###################
		// #######################################################

		/**
		 * check if feasible to create submission for this feedback
		 * check if subject data is set in event
		 */

		// ------------ 1

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION_PRE, [
				$this,
				'onCreateSubmissionPre_validateSubjectData'
		], - 1 );

		// ------------ 2
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION_PRE, [
				$this,
				'onCreateSubmissionPre_feasibiltyChecks'
		], - 2 );

		// ------------ 3
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION_PRE, [
				$this,
				'onCreateSubmissionPre_createLimit'
		], - 3 );


		// #######################################################
		// ######################## CREATE #######################
		// #######################################################


		// ------------ 1
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_createSubmission'
		], - 1 );

		// ------------ 3
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_saveSubjectData'
		], - 2 );

		// ------------ 5
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_createState'
		], - 3 );


		// ------------ 2
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_saveSubmitterData'
		], - 4 );

		// #######################################################
		// ######################## POST CREATE ##################
		// #######################################################


		return $this;

	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Zend\EventManager\SharedListenerAggregateInterface::detachShared()
	 */
	public function detachShared(\Zend\EventManager\SharedEventManagerInterface $events) {


		// $this->detach($events);
	}


	// ############################################################################
	// ############################ CREATE-PRE ####################################
	// ############################################################################


	public function onCreateSubmissionPre_validateSubjectData(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		// #################### validate subject data
		$formData = $event->getParam ( 'form_subject_data', [ ] );
		$subjectForm = $feedbackService->getSubjectForm ( $event->getFeedback () ,$formData );

		// check feedback has subject?
		if (! $subjectForm instanceof SubjectForm)
			return true;

		if (! $subjectForm->isValid ()) {
		    $formExceptin=(new FeedbackFormException ())->addMessage ($subjectForm->getMessages ());
		    $event->addException ( $formExceptin  );
			$event->stopPropagation ( true );
			return false;
		}

		$event->setSubjectData ( $subjectForm->getData ( $subjectForm::VALUES_DICTIONARY ) );


		return true;

	}


	/**
	 * check if feasible to create submission for this feedback
	 * check if subject data is set in event
	 *
	 * @param FeedbackEvent $event
	 * @return boolean
	 */
	public function onCreateSubmissionPre_feasibiltyChecks(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );

		$feedbackEntity = $event->getFeedback ();
		$periodNumber=$event->getPeriodNumber();
		$periodId=$feedbackService->getPeriodId($feedbackEntity, $periodNumber);
		$event->setParam("period_id", $periodId);

		//feedback is active
		if($feedbackEntity->getActive()==false){
		    $event->addException ( new EventException("feedback dose not active" ) );
			$event->stopPropagation ( true );
			return false;
		}

		//feedback period dose not expired
		$feedbackRespiteTime=$feedbackService->getRespiteTime($feedbackEntity,$periodNumber);
		if ($feedbackService->isExpiredRespiteTime ( $feedbackRespiteTime )){
			$event->addException ( new EventException( "feedback {$feedbackEntity->getId()} period $periodNumber is expired" ) );
			$event->stopPropagation ( true );
			return false;
		}

		return true;

	}


	public function onCreateSubmissionPre_createLimit(FeedbackEvent $event){
		/**
		 *
		 * @var \BaftFeedback\Model\SubmissionRepository $submissionRepository
		 */
		$submissionRepository = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );

		$feedbackEntity = $event->getFeedback ();
		$periodId=$event->getParam("period_id");
		$submissionCreationLimit = $feedbackEntity->getSubmissionLimit ();

		if($submissionCreationLimit==0)
			return true;

		$submissions=$submissionRepository->findBySubject($feedbackEntity,$event->getSubject(),$event->getSubjectData(),$periodId);
		$submissionsCount=$submissions->count();

		if($submissionsCount<$submissionCreationLimit)
			return true;

		$event->setSubmission($submissions->last());
		$event->setParam("prev_submissions", $submissions);
		$event->addException ( new EventException( "{$submissionCreationLimit} submission per subject allowed." ) );
		$event->stopPropagation ( true );
		return false;

	}

	// ############################################################################
	// ############################ CREATE ####################################
	// ############################################################################

	/**
	 * create a new submission
	 *
	 * @param FeedbackEvent $event
	 */
	public function onCreateSubmission_createSubmission(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Model\SubmissionRepository $submissionModel
		 */
		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );
		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		
		$submissionEntity = false;
		$feedbackEntity = $event->getFeedback ();
		$feedbackVersion = $event->getFeedbackVersion ();
		$periodNumber=$event->getPeriodNumber();
		$periodId=$event->getParam("period_id");
		$currentTime = $submissionStart = $event->getCurrentTime();

		//previusley feedback expiration checked , so dose not need check
		$submissionExpire = $submissionService->getExpireTime ( $feedbackEntity ,$periodNumber ,$currentTime);

		$event->setParam ( 'submission_start_time', $submissionStart );
		$event->setParam ( 'submission_expire_time', $submissionExpire );

		$submissionEntity = $submissionModel->create ( $feedbackEntity, $feedbackVersion, $submissionExpire, $submissionStart , $periodId);

		//@TODO check if submission created . orm persist dose not return anything about success

		$event->setSubmission ( $submissionEntity );

		return true;

	}

	/**
	 * save submission data
	 *
	 * @param FeedbackEvent $event
	 */
	public function onCreateSubmission_saveSubjectData(FeedbackEvent $event) {

		$subjectDataModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\subjectData' );

		// $subjectData=json_decode(json_encode($subjectForm->getData()),true);
		$subjectData = $event->getSubjectData ();
		$feedback = $event->getFeedback ();
		$submission = $event->getSubmission ();
		$subject = $event->getSubject ();

		if (! $feedback->getSubject ())
			return true;

		$subjectDataCollection = $subjectDataModel->create ( $feedback, $submission, $subject, $subjectData );
		
		if (! $subjectDataCollection instanceof ArrayCollection) {

			$exception = new FeedbackDbException ( 'subjec data dose not saved', null, $subjectDataCollection );

			$event->addException ( $exception );

			$event->stopPropagation ( true );

			return false;
		}

		// refresh to update subject data refrence
		$this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' )->refresh ( $submission );

		$event->setSubmission ( $submission );
		$event->setSubjectData ( $subjectDataCollection );

		return true;

	}

	/**
	 *
	 * @param FeedbackEvent $event
	 */
	public function onCreateSubmission_createState(FeedbackEvent $event) {

		return $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' )->createState ( $event->getSubmission (), 0, [ ] );
	}


	/**
	 *
	 * @param FeedbackEvent $event
	 */
	public function onCreateSubmission_saveSubmitterData(FeedbackEvent $event) {

		return $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submitter' )->create ( $event->getSubmission (), $event->getParam ( 'submitter_data' ) );
	}


}


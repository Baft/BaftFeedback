<?php

namespace BaftFeedback\Listener;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\ListenerAggregateInterface;
use BaftFeedback\Event\FeedbackEvent;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use BaftFeedback\Exception\FeedbackFormException;
use Zend\Json\Json;
use Zend\Form\FormInterface;
use BaftFeedback\Exception\FeedbackDbException;
use Doctrine\Common\Collections\ArrayCollection;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmission;
use BaftFeedback\Exception\SubmissionNotFoundException;
use BaftFeedback\Form\QuestionsForm;
use Zend\Form\Form;
use BaftFeedback\Form\SubmissionDataHydrator;
use BaftFeedback\Form\SubjectForm;

class SubmissionListener implements SharedListenerAggregateInterface {

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
		// ########################### READ ######################
		// #######################################################
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_READ_SUBMISSION, [
				$this,
				'onReadSubmission'
		] );

		// #######################################################
		// ######################## CREATE #######################
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
				'onCreateSubmissionPre_feasibiltyChecks'
		], - 1 );

		// ------------ 2
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION_PRE, [
				$this,
				'onCreateSubmissionPre_validateSubjectData'
		], - 2 );

		// ------------ 3
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION_PRE, [
				$this,
				'onCreateSubmissionPre_validateSubmissionData'
		], - 3 );


		// ------------ 1
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_createSubmission'
		], - 1 );


		// ------------ 2
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_saveSubmitterData'
		], - 2 );

		// ------------ 3
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_saveSubjectData'
		], - 3 );

		// ------------ 4
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_saveSubmissionData'
		], - 4 );

		// ------------ 5
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_CREATE_SUBMISSION, [
				$this,
				'onCreateSubmission_createState'
		], - 5 );

		// #######################################################
		// ######################## EDIT #########################
		// #######################################################

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_UPDATE_SUBMISSION_PRE, [
				$this,
				'onEditSubmissionPre'
		] );

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_UPDATE_SUBMISSION, [
				$this,
				'onEditSubmission'
		] );


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
	// ############################ READ ####################################
	// ############################################################################
	public function onReadSubmission(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );

		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );
		$submission = $event->getTarget ();

		/**
		 *
		 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submissionEntity
		 */
		$submissionEntity = $submissionModel->find ( $submission );

		if (! $submissionEntity instanceof BaftfeedbackFeedbackSubmission)
			throw new SubmissionNotFoundException ( __METHOD__ . " : requestd submission dose not exist ." );

		$event->setSubmission ( $submissionEntity );
		$event->setSubmissionData ( $submissionService->getSubmissionLastData ( $submissionEntity ) );

		return true;

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
		$formData = $event->getParam ( 'subject_data', [ ] );

		$subjectForm = $feedbackService->getSubjectForm ( $event->getFeedback () );
		// check feedback has subject?
		if (! $subjectForm instanceof SubjectForm)
			return true;

		if (! $subjectForm->setData ( $formData )->isValid ()) {

			$exception = new FeedbackFormException ();
			$exception->addMessage ( $subjectForm->getMessages () );

			$event->addException ( $exception );
			$event->stopPropagation ( true );

			return $exception;
		}

		$event->setSubjectData ( $subjectForm->getData ( $subjectForm::VALUES_DICTIONARY ) );

		return true;

	}

	public function onCreateSubmissionPre_validateSubmissionData(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		// #################### validate submission data
		$formData = $event->getParam ( 'questions_data', false );

		if ($formData === false)
			return true;

		$questionsForm = $feedbackService->getQuestionsForm ( $event->getFeedback () );

		if (! $questionsForm->setData ( $formData )->isValid ()) {

			$exception = new FeedbackFormException ();
			$exception->addMessage ( $questionsForm->getMessages () );

			$event->addException ( $exception );
			$event->stopPropagation ( true );

			return $exception;
		}

		$event->setSubmissionData ( $questionsForm->getData ( FormInterface::VALUES_AS_ARRAY ) );

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

		$feedbackVersion = $feedbackEntity->getVersion ();
		if (! $feedbackVersion instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackVersion)
			$feedbackEntity->setVersion ( $feedbackService->getLastVersion ( $feedbackEntity ) );

		$event->setFeedback ( $feedbackEntity );

		// #### 1
		// check if feasible to create new submission for this feedback
		if (! $submissionService->creationFeasibility ( $feedbackEntity )) {
			$event->stopPropagation ( true );
			return 'creating new submission dose not feasible now.';
		}


		return true;

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
		$subjectData = $event->getSubjectData ();
		$feedbackEntity = $event->getFeedback ();
		$feedbackVersion = $event->getFeedbackVersion ();
		$expireTime = $submissionService->getExpireTime ( $feedbackEntity);
		$event->setParam ( 'submission_start_time', time () )
			->setParam ( 'submission_expire_time', $expireTime );


		$submissionExpire = $event->getParam ( 'submission_expire_time' );
		$submissionStart = $event->getParam ( 'submission_start_time' );

		// create submission or find last to be continous
		if ($event->getFeedback ()->getSimultaneous () && $submissionEntity = $submissionService->find ( $event->getFeedback (), $subjectData )->last ()) {
			// creation process have to be stoped . a submission previously created
			$event->setSubmission ( $submissionEntity );
			$event->stopPropagation ( true );
			return true;
		} else {

			try {
				$submissionEntity = $submissionModel->create ( $feedbackEntity, $feedbackVersion, $submissionExpire, $submissionStart );
			}
			catch ( \Exception $ex ) {
				$submissionEntity = $ex;
			}
		}

		if (! $submissionEntity instanceof BaftfeedbackFeedbackSubmission) {
			if ($submissionEntity instanceof \Exception)
				$event->addException ( $submissionEntity );
			$exception = new SubmissionNotFoundException ( 'submission data dose not exist' );
			$event->addException ( $exception );
			$event->stopPropagation ( true );
			return $exception;
		}

		$event->setSubmission ( $submissionEntity );
		//var_dump($submissionEntity);

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

			return $exception;
		}

		// refresh to update subject data refrence
		$this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' )->refresh ( $submission );

		$event->setSubmission ( $submission );
		$event->setSubjectData ( $subjectDataCollection );

		return true;

	}

	/**
	 * save submission data
	 *
	 * @param FeedbackEvent $event
	 */
	public function onCreateSubmission_saveSubmissionData(FeedbackEvent $event) {

		return $this->saveSubmissionData ( $event );

	}

	// ############################################################################
	// ############################ EDIT ####################################
	// ############################################################################

	/**
	 *
	 * @param FeedbackEvent $event
	 * @throws \Exception
	 */
	public function onEditSubmissionPre(FeedbackEvent $event) {

		$serviceManager = $this->getServiceLocator ();

		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $serviceManager->get ( 'BaftFeedback\Service\submission' );
		$submitterModel = $serviceManager->get ( 'BaftFeedback\Model\submitter' );

		// check requested submissin to be appurtenant to requested feedback
		if (! $submissionService->appurtenantSubmission ( $event->getSubmission (), $event->getFeedback ()->getId () )) {
			$event->addException ( "requestd submission dose not match with feedback . on " . __METHOD__ . " in " . __LINE__ );
			$event->stopPropagation ( true );
			return false;
		}

		$submitter = $submitterModel->create ( $event->getSubmission (), $event->getParam ( 'submitter_data' ) );

		if (! $submitter instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData) {
			$event->addException ( "requestd submission dose not match with feedback . on " . __METHOD__ . " in " . __LINE__ );
			$event->stopPropagation ( true );
			return false;
		}
		$event->setSubmitter ( $submitter );

		return true;

	}

	public function onEditSubmission(FeedbackEvent $event) {

		return $this->saveSubmissionData ( $event );

	}

	// ############################################################################
	// ############################################################################
	// ############################################################################

	/**
	 * save submission data
	 *
	 * @param FeedbackEvent $event
	 */
	protected function saveSubmissionData(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );

		$submissionFormData = $event->getParam ( 'questions_data', [ ] );

		if (empty ( $submissionFormData ))
			return true;

		if (is_object ( $submissionFormData )) {
			$hydrator = new SubmissionDataHydrator ( $event->getQuestionsNamespace () );
			$submissionFormData = $hydrator->extract ( $submissionFormData );
		}

		// save data of submission
		$submissionEntity = $submissionService->saveSubmissionData ( $event->getSubmission (), $event->getSubmitter (), $submissionFormData );
		$event->setSubmission ( $submissionEntity );

		return true;

	}


}


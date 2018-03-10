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

class EditSubmissionListener implements SharedListenerAggregateInterface {

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

		// #######################################################
		// ######################## PRE EDIT #####################
		// #######################################################
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_UPDATE_SUBMISSION_PRE, [
				$this,
				'onEditSubmissionPre_validateSubmissionData'
		], - 1 );

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_UPDATE_SUBMISSION_PRE, [
				$this,
				'onEditSubmissionPre_cleanData'
		], - 2 );

		// #######################################################
		// ######################## EDIT #########################
		// #######################################################

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_UPDATE_SUBMISSION_PRE, [
				$this,
				'onEditSubmission_saveSubmitter'
		], - 1 );

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\submissionService'
		], FeedbackEvent::EVENT_UPDATE_SUBMISSION, [
				$this,
				'onEditSubmission_saveData'
		], - 3 );

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
	// ############################ PRE EDIT ##################################
	// ############################################################################
	public function onEditSubmissionPre_validateSubmissionData(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		// #################### validate submission data
		$formData = $event->getParam ( 'feedback_form_raw_data', false );
		$questionsForm = $event->getFeedbackForm ();
		$questionsForm->setData ( $formData );

		if ($formData === false)
			return true;

		if (! $questionsForm->isValid ()) {
			// check requested submissin to be appurtenant to requested feedback
			// - checked on form validation via hidden field
			$event->addException ( (new FeedbackFormException ())->addMessage ( $questionsForm->getMessages () ) );
			$event->stopPropagation ( true );
			return false;
		}

		$event->setParam ( 'feedback_form_raw_data', $questionsForm->getData ( $questionsForm::VALUES_RAW ) );

		return true;

	}

	public function onEditSubmissionPre_cleanData(FeedbackEvent $event) {

		$submissionFormData = $event->getParam ( 'feedback_form_raw_data', false );
		$feedbackForm = $event->getFeedbackForm ();

		// save data of submission
		$formDataArray = (new SubmissionDataHydrator ( $feedbackForm->getNamespace () ))->extract ( $submissionFormData );
		// 		print_r($formDataArray);die("sdasdf");
		// remove recordes when fieldname or fieldvalue is empty
		$cleanedFormData = [ ];
		foreach ( $formDataArray as $index => $row ) {

			$groupId = $row ['question_group_id'];
			$questionId = $row ['question_id'];
			$fieldName = $row ['question_field_name'];
			$fieldValue = $row ['field_value'];

			if (empty ( $fieldName ))
				continue;

			if (empty ( $fieldValue ))
				continue;

			$cleanedFormData [] = $row;
		}
		// 		print_r($cleanedFormData);die("sdasdf");
		$event->setParam ( 'feedback_form_extracted_data', $cleanedFormData );

		return true;

	}

	// ############################################################################
	// ############################ EDIT ######################################
	// ############################################################################

	/**
	 *
	 * @param FeedbackEvent $event
	 * @throws \Exception
	 */
	public function onEditSubmission_saveSubmitter(FeedbackEvent $event) {

		$serviceManager = $this->getServiceLocator ();

		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $serviceManager->get ( 'BaftFeedback\Service\submission' );
		$submitterModel = $serviceManager->get ( 'BaftFeedback\Model\submitter' );
		/*
		 * // check requested submissin to be appurtenant to requested feedback
		 * - checked on form validation via hidden field
		 * if (! $submissionService->appurtenantSubmission (
		 * $event->getSubmission (), $event->getFeedback ()->getId () )) {
		 * $event->addException ( "requestd submission dose not match with
		 * feedback . on " . __METHOD__ . " in " . __LINE__ );
		 * $event->stopPropagation ( true );
		 * return false;
		 * }
		 */
		try {
			$submitter = $submitterModel->create ( $event->getSubmission (), $event->getParam ( 'submitter_data' ) );
			$event->setSubmitter ( $submitter );
		}
		catch ( \Exception $ex ) {
			$event->addException ( "can not set submitter for submission" );
			$event->stopPropagation ( true );
			return false;
		}

		return true;

	}

	public function onEditSubmission_saveData(FeedbackEvent $event) {

		/**
		 *
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );
		$formExtractedData = $event->getParam ( 'feedback_form_extracted_data', false );
		// 		$formQuestionsData = $event->getParam ( 'feedback_form_raw_data', false );
		$submitter = $event->getSubmitter ();
		$submission = $event->getSubmission ();
		$feedbackForm = $event->getFeedbackForm ();

		// 		\var_dump($submissionFormData,$formDataArray);die;
		$submissionEntity = $submissionService->saveSubmissionData ( $submission, $submitter, $formExtractedData );
		$event->setSubmission ( $submissionEntity );
		$event->setSubmissionData ( $submissionService->getSubmissionLastData($submission) );

		$formData = (new SubmissionDataHydrator ( $feedbackForm->getNamespace () ))->hydrate ( $formExtractedData, $feedbackForm->getObject () );
		$feedbackForm->populateValues ( $formData, true );

		$event->setFeedbackForm ( $feedbackForm );

		return true;

	}


}


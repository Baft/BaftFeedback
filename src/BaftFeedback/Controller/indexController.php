<?php

namespace BaftFeedback\Controller;

use BaftFeedback\Feedback\feedbackActionAbstract;
use BaftFeedback\Listener\BaftFeedbackRouteListener;
use BaftFeedback\Entity\BaftfeedbackFeedback;
use Zend\View\Model\ViewModel;
use BaftFeedback\Exception\FeedbackFormException;
use Zend\Json\Json;
use BaftFeedback\Form\SubmissionDataHydrator;

class indexController extends feedbackActionAbstract {

	public function indexAction() {
		// @TODO make a default index page !?!?
		return $this->CreateHttpNotFoundModel ( $this->getResponse () )->setVariables ( [
				'content' => '<h3>please select a feedback first<h3>'
		] );

	}

	/**
	 * - redirect to editAction if submission passed .
	 *
	 *
	 * - view empty feedback form if passed no submission .
	 */
	public function createAction() {

		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );
		$vm = new ViewModel ();

		try {
			$feedback = $this->params ( BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE, null );
			$feedback = $feedbackService->readFeedback ( $feedback );
			$vm->setVariable ( 'feedback', $feedback );
		}
		catch ( \Exception $ex ) {
			return $this->CreateHttpNotFoundModel ( $this->getResponse () )->setVariables ( [
					'content' => $ex->getMessage ()
			] );
		}

		if (! $subject = $feedback->getSubject ()) {

			$submission = $submissionService->createSubmission ( $feedback, [ ], false );

			if ($submission instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission) {
				return $this->redirect ()->toRoute ( BaftFeedbackRouteListener::ROUTE_NAME . '/submission/edit', [
						BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE => $feedback->getId (),
						BaftFeedbackRouteListener::ROUTE_SUBMISSION_VARIABLE => $submission->getId ()
				] );
			}

			return $vm;
		}


		$subjectNamespace = $feedback->getSubjectNamespace ();

		if ($this->request->isPost () && $this->params ()->fromPost ( 'submit', false )) {

			$subjectData = [
					$subjectNamespace => $this->params ()->fromPost ( $subjectNamespace, [ ] )
			];

			$feedbackEvent = $submissionService->createSubmission ( $feedback, $subjectData, false );
			$submission = $feedbackEvent->getSubmission();

			//### go to edit, previous submissions found
			$prevSubmissions=$feedbackEvent->getParam("prev_submissions",false);
			if($prevSubmissions);
			//@TODO what to do with previous submissions

			if($feedbackEvent->hasException()){
				$exceptions = $feedbackEvent->getExceptions();
				foreach ( $exceptions as  $message ) {
					if($exceptions instanceof FeedbackFormException);
					//@TODO how to view form exceptions
					$this->flashMessenger ()->addErrorMessage ( $message->getMessage () );
					//$this->layout()->setVariable("application_messages", "ssss" );
				}
				return $this->redirect ()->refresh ();
			}

			if ($submission instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission) {
				return $this->redirect ()->toRoute ( BaftFeedbackRouteListener::ROUTE_NAME . '/submission/edit', [
						BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE => $feedback->getId (),
						BaftFeedbackRouteListener::ROUTE_SUBMISSION_VARIABLE => $submission->getId ()
				] );
			}
		}

		$vm->setVariable ( 'feedback', $feedback );

		return $vm;

	}

	public function editAction() {

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

		$vm = new ViewModel ();

		try {
			$submission = $this->params ( BaftFeedbackRouteListener::ROUTE_SUBMISSION_VARIABLE, null );
			$submission = $submissionService->readSubmission ( $submission );
		}
		catch ( \Exception $ex ) {
			return $this->CreateHttpNotFoundModel ( $this->getResponse () )->setVariables ( [
					'content' => $ex->getMessage ()
			] );
		}

		$feedbackEntity = $submission->getRefBaftfeedbackFeedback ();

		$vm->setVariable ( 'submission', $submission );
		$vm->setVariable ( 'feedback', $submission->getRefBaftfeedbackFeedback () );
		$vm->setVariable ( 'submitters', $submission->getSubmitters () );



		$questionsForm = $feedbackService->getQuestionsForm ( $feedbackEntity, $submission );
		if ($this->request->isPost () && $this->params ()->fromPost ( 'submit', false )) {

			$post = $this->params ()->fromPost ();
			$questionsForm->bind ( $questionsForm->getObject () );
			$questionsForm->setData ( $post );

			if (! $questionsForm->isValid ()) {
				$exception = new FeedbackFormException ();
				$exception->addMessage ( $questionsForm->getMessages () );
				print_r ( $questionsForm->getMessages () );
				die ( '------------- form data invalid --------------' );
			}

			$submission = $submissionService->editSubmission ( $submission, $questionsForm->getData (), false );

			if ($submission)
				$this->flashMessenger ()->addMessage ( "فرم با موفقیت ذخیره شد", 'success' );
		}

		$submissionLastData = $submissionService->getSubmissionLastData ( $submission );
		$formData = (new SubmissionDataHydrator ( $questionsForm->getNamespace () ))->hydrate ( $submissionLastData, $questionsForm->getObject () );
		$questionsForm->populateValues ( $formData, true );

		$vm->setVariable ( 'form', $questionsForm );
		return $vm;

	}


}

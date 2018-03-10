<?php

namespace BaftFeedback\Service;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback\Feedback\feedbackAwareInterface;
use Zend\Db\Sql\Predicate\IsNull;
use BaftFeedback\Listener\BaftFeedbackRouteListener;

/**
 *
 * @author web
 *
 */
class feedbackAwareInitializer implements InitializerInterface {

	/**
	 * feedback controllers Initializer
	 *
	 * @param
	 *        	$instance
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return mixed
	 */
	public function initialize($instance, ServiceLocatorInterface $controllerManager) {

		if ($instance instanceof feedbackAwareInterface) {
			$serviceLocator = $controllerManager->getServiceLocator ();

			$feedback = $serviceLocator->get ( 'application' )->getMvcEvent ()->getRouteMatch ()->getParam ( BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE, null );
			$submission = $serviceLocator->get ( 'application' )->getMvcEvent ()->getRouteMatch ()->getParam ( BaftFeedbackRouteListener::ROUTE_SUBMISSION_VARIABLE, null );

			//@TODO on not found feedback  or submission or feedbackEvent exceptions , direct request to exception page (notFoundFeedbackAction notFoundSubmissionAction feedbackEventExceptionAction ) may be usefull to use controller>onDispatch

			try {

				if (! is_null ( $submission )) {

					/**
					 *
					 * @var \BaftFeedback\Service\submissionService $submissionService
					 */
					$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );
					$feedbackEvent = $submissionService->readSubmission ( $submission );

					$instance->setFeedbackEvent ( $feedbackEvent );
					return;
				}

				if (! is_null ( $feedback )) {

					/**
					 *
					 * @var \BaftFeedback\Service\feedbackService $feedbackService
					 */
					$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
					$feedbackEvent = $feedbackService->readFeedback ( $feedback );

					$instance->setFeedbackEvent ( $feedbackEvent );
					return;
				}
			} catch (\Exception $e) {

			}
			return;
		}

	}


}
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
use BaftFeedback\Entity\BaftfeedbackFeedback;

class ReadSubmissionListener implements SharedListenerAggregateInterface {
	
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
		$this->listeners [] = $events->attach ( [ 
				'BaftFeedback',
				'BaftFeedback\Service\submissionService' 
		], FeedbackEvent::EVENT_READ_SUBMISSION, [ 
				$this,
				'onReadSubmission' 
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
		
		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbcakService
		 */
		$feedbcakService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		
		$target = $event->getTarget ();
		
		if ($target instanceof BaftfeedbackFeedback){
			$event->setFeedback ( $target );
		}
		
		if ($target instanceof BaftfeedbackFeedbackSubmission) {
			$submission = $target;
			$event->setFeedback ( $submission->getRefBaftfeedbackFeedback () );
			
			$event->setSubmission ( $submission );
			
			$event->setFeedbackVersion ( $submission->getRefBaftfeedbackFeedbackVersion () );
			
			$event->setSubjectData ( $event->getSubmission ()->getSubjectData () );
			
			$event->setSubmissionData ( $submissionService->getSubmissionLastData ( $event->getSubmission () ) );
			
			$event->setSubmitter ( $event->getSubmission ()->getSubmitters () );
			
			$event->setState ( $event->getSubmission ()->getStates () );
			
			$feedbackForm = $feedbcakService->getQuestionsForm ( $event->getFeedback (), $event->getSubmission () );
			$feedbackForm->bind ( $feedbackForm->getObject () );
			$event->setFeedbackForm ( $feedbackForm );
		}
		
		$feedback=$event->getFeedback ();
		
		$event->setSubject ( $feedback->getSubject () );
		$event->setSubjectNamespace ($feedback->getSubjectNamespace () );
		$event->setQuestionsNamespace ( $feedback->getQuestionsNamespace () );
		
		$periodId = $feedbcakService->getCurrentPeriodNumber ( $feedback );
		$event->setPeriodNumber ( $periodId );
		
		return true;
	}
}


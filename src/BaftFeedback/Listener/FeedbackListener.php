<?php

namespace BaftFeedback\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use BaftFeedback\Event\FeedbackEvent;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use BaftFeedback\Entity\BaftfeedbackFeedbackVersion;
use BaftFeedback\Entity\BaftfeedbackFeedback;

class FeedbackListener extends BaftFeedbackListenerAbstract implements SharedListenerAggregateInterface {
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
	 * {@inheritDoc}
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attachShared(SharedEventManagerInterface $events) {

		// #################################################
		// READ
		// #################################################
		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\feedbackService'
		], FeedbackEvent::EVENT_READ_FEEDBACK, [
				$this,
				'onReadFeedback_initFeedbackEvent'
		] );

		// #################################################
		// CREATE
		// #################################################

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\feedbackService'
		], FeedbackEvent::EVENT_CREATE_FEEDBACK_PRE, [
				$this,
				'onCreateFeedbackPre_preCreate'
		] );

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\feedbackService'
		], FeedbackEvent::EVENT_CREATE_FEEDBACK, [
				$this,
				'onCreateFeedback_create'
		] );

		// #################################################
		// UPDATE
		// #################################################

		$this->listeners [] = $events->attach ( [
				'BaftFeedback',
				'BaftFeedback\Service\feedbackService'
		], FeedbackEvent::EVENT_UPDATE_FEEDBACK, [
				$this,
				'onUpdateFeedback_update'
		] );


		return $this;

	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Zend\EventManager\SharedListenerAggregateInterface::detachShared()
	 */
	public function detachShared(\Zend\EventManager\SharedEventManagerInterface $events) {
		// $this->detach($events);
	}

	// #################################################
	// READ
	// #################################################

	/**
	 * make form object from feedbackEntity
	 *
	 * @param FeedbackEvent $event
	 */
	public function onReadFeedback_initFeedbackEvent(FeedbackEvent $event) {

		$event->pushCalledListener ( __FUNCTION__ );


		return true;

	}

	// #################################################
	// CREATE
	// #################################################
	public function onCreateFeedbackPre_preCreate(FeedbackEvent $event) {

	}

	public function onCreateFeedback_create(FeedbackEvent $event) {

		/** @var \BaftFeedback\Model\feedbackRepository $feedbackModel */
		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		$versionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedbackVersion' );

		$feedbackData = $event->getParam ( 'feedback_data', [ ] );

		if (! $feedbackData instanceof BaftfeedbackFeedback) {
			$hydrator = new DoctrineHydrator ( $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' ) );
			$feedbackData = $hydrator->hydrate ( $feedbackData, $event->getFeedback () );
		}

// 		if(empty($feedbackData->getRefBaftfeedbackQuestionGroupId()))
// 			$feedbackData->setRefBaftfeedbackQuestionGroupId();

		$groupEntity = $this->getServiceLocator ()->get ( "BaftFeedback\Model\questionGroup" )->create ( $feedbackData );

		$feedbackEntity = $feedbackModel->create ( $feedbackData, $groupEntity );
		$event->setFeedback ( $feedbackEntity );

		$versionEntity = new BaftfeedbackFeedbackVersion ();
		$versionEntity->setDescription ( 'created feedback' );
		$versionEntity->setDisable ( 0 );

		$newFeedbackVersion = $versionModel->create ( $versionEntity, $event->getFeedback () );
		$event->setFeedbackVersion ( $newFeedbackVersion );

		return true;

	}

	// #################################################
	// UPDATE
	// #################################################
	public function onUpdateFeedback_update(FeedbackEvent $event) {

		$hydrator = new DoctrineHydrator ( $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' ) );
		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		$versionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedbackVersion' );

		$formData = $event->getParam ( 'feedback_data' );
		$newFeedbackEntity = $hydrator->hydrate ( $formData, $event->getFeedback () );

		$newFeedbackEntity = $feedbackModel->update ( $newFeedbackEntity );

		$versionEntity = new BaftfeedbackFeedbackVersion ();
		$versionEntity->setDescription ( $formData ['version_desc'] );
		$versionEntity->setDisable ( 0 );

		$newFeedbackVersion = $versionModel->create ( $versionEntity, $event->getFeedback () );
		$event->setFeedback ( $newFeedbackEntity );
		$event->setFeedbackVersion ( $newFeedbackVersion );

		return true;

	}


}


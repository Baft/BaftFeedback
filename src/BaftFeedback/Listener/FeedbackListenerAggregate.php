<?php
namespace BaftFeedback\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\CallbackHandler;

class FeedbackListenerAggregate implements SharedListenerAggregateInterface {

	/**
	 *
	 * @var array
	 */
	protected $listeners = [ ];
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
		// @TODO Auto-generated method stub
		// $feedbackService=$sm->get('BaftFeedback\Service\feedback');
		// $events->attach($feedbackService);

	    $createSubmissionListener = new CreateSubmissionListener();
		$createSubmissionListener->setServiceLocator ( $this->getServiceLocator () );
		$this->listeners [] = $events->attachAggregate ( $createSubmissionListener );

		$editSubmissionListener = new EditSubmissionListener();
		$editSubmissionListener->setServiceLocator ( $this->getServiceLocator () );
		$this->listeners [] = $events->attachAggregate ( $editSubmissionListener );

		$readSubmissionListener = new ReadSubmissionListener();
		$readSubmissionListener->setServiceLocator ( $this->getServiceLocator () );
		$this->listeners [] = $events->attachAggregate ( $readSubmissionListener );

		$feedbackListener = new FeedbackListener ();
		$feedbackListener->setServiceLocator ( $this->getServiceLocator () );
		$this->listeners [] = $events->attachAggregate ( $feedbackListener );

		return $this;

	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Zend\EventManager\SharedListenerAggregateInterface::detachShared()
	 */
	public function detachShared(\Zend\EventManager\SharedEventManagerInterface $events) {

		foreach ( $this->listeners as $key => $listener ) {
			$detached = false;
			if ($listener === $this) {
				continue;
			}
			if ($listener instanceof SharedListenerAggregateInterface) {
				$detached = $events->detachAggregate ( $listener );
			} elseif ($listener instanceof CallbackHandler) {
				$detached = $events->detach ( $key, $listener );
			}

			if ($detached) {
				unset ( $this->listeners [$key] );
			}
		}

	}


}
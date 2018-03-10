<?php

namespace BaftFeedback\Service;

use BaftFeedback\Entity\BaftfeedbackQuestion;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\Factory;
use Zend\Json\Json;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Session\Container;

class submitterService implements ServiceLocatorAwareInterface, EventManagerAwareInterface {
	public $serviceLocator;
	public $eventManager;


	public function getCurrentSubmitter(){
		$container = new Container( $this->getServiceLocator ()->get ( 'config' ) ['session'] ['name'] );
		
		$submitter=0;
		
		if($this->getServiceLocator()->has('zfcuser_auth_service')){
			$authnService=$this->getServiceLocator()->get('zfcuser_auth_service');
			if($authnService->hasIdentity())
				$submitter=$authnService->getIdentity()->getId();
		}
		
		return ['submitter' => $submitter,
			'submitter_ip' => ip2long ( $container->remoteAddr ),];
	}

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

	/*
	 * (non-PHPdoc)
	 * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
	 */
	public function setEventManager(EventManagerInterface $eventManager) {

		$eventManager->setIdentifiers ( [
				__CLASS__,
				get_called_class ()
		] );
		$this->eventManager = $eventManager;
		return $this;

	}

	/**
	 * Retrieve the event manager
	 *
	 * Lazy-loads an EventManager instance if none registered.
	 *
	 * @return EventManagerInterface
	 */
	public function getEventManager() {

		return $this->eventManager;

	}


}
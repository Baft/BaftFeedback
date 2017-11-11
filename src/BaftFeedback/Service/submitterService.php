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

class submitterService implements ServiceLocatorAwareInterface, EventManagerAwareInterface {
	public $serviceLocator;
	public $eventManager;

	public function saveData($submissionEntity, $submitterData) {

		return $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submitter' )->create ( $submissionEntity->getId (), $submitterData );
	
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
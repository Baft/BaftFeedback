<?php

namespace BaftFeedback\Form;

use Zend\Form\Fieldset;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class FeedbackForm extends Form implements ServiceLocatorAwareInterface {
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	public function getSubject() {

	}

	public function getQuestins() {

	}

	public function __construct() {

		parent::__construct ( 'feedback' );
		

		$this->add ( [ 
				'name' => 'name',
				'options' => [ 
						'label' => 'name of feedback' 
				] 
		] );
		
		$this->add ( [ 
				'name' => 'desc',
				'options' => [ 
						'label' => 'description' 
				] 
		] );
	
	}

	public function init() {

	}

	/**
	 * Set the service locator.
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return AbstractHelper
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

		$this->serviceLocator = $serviceLocator;
		return $this;
	
	}

	/**
	 * Get the service locator.
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {

		return $this->serviceLocator;
	
	}


}
<?php

namespace BaftFeedback\Form\Element;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Element\Select;

/**
 * list of questions groups of feedback
 * 
 * @author web
 *        
 */
class GroupSelectElement extends Select implements ServiceLocatorAwareInterface {
	
	// TODO: form elements that get their data from db (eg. selectBox filled by names from db)
	// contain dates base of feedback period
	// theming and add/remove fields by usecase on presentation/controller
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	public function init() {

		$questionGroups = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' )->getQuestionGroups ( $feedbackId );
		
		$brListValueOptions = [ ];
		
		foreach ( $questionGroups as $group ) {
			$brListValueOptions [$group->getId ()] = $group->getLabel ();
		}
		
		$this->get ( 'questions_group' )->setValueOptions ( $brListValueOptions );
	
	}

	public function __construct() {

		parent::__construct ( 'questions_group' );
		
		$this->setLabel ( 'انتخاب گروه چک لیست:' );
		
		$this->setAttributes ( [ 
				'id' => 'questions_group',
				'class' => 'font-blue-ebonyclay input-large  margin-bottom' 
		] );
		$this->setOptions ( [ 
				'empty_option' => 'انتخاب گروه چک لیست' 
		] );
	
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
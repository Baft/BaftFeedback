<?php

namespace BaftFeedback\Form;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * able to to create subject & questions form by feeback name like:
 * BaftFeedback\[feedback name]\Subject --> create subjectForm
 * BaftFeedback\[feedback name]\Questions --> create questions fieldset
 * BaftFeedback\[feedback name]\Question\[question name] --> create question fieldset of that feedback
 * BaftFeedback\[feedback name]\Group\[group name] --> create group fieldset of that feedback with questions
 * BaftFeedback\[feedback name] --> create fieldset of feedback contain questions and subject
 * 
 * @author web
 *        
 */
class BaftFeedbackFormAbstractFactory implements AbstractFactoryInterface {
	private $feedName;
	private $formType;
	private $formName;

	/*
	 * (non-PHPdoc)
	 * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
	 */
	public function canCreateServiceWithName(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator, $name, $requestedName) {

		$matched = [ ];
		/**
		 * match to these :
		 * BaftFeedback\Form\[feedback name]\Subject
		 * BaftFeedback\Form\[feedback name]\Questions
		 * BaftFeedback\Form\[feedback name]\Question\[question name]
		 * BaftFeedback\Form\[feedback name]\Group\[group name]
		 * BaftFeedback\Form\[feedback name]
		 */
		if (! @preg_match ( '/BaftFeedback\\\Form\\\(?P<feedName>\w+)(\\\(?P<formType>(Subject(?![\w\s\W]+)|Questions(?![\w\s\W]+)|(Question|Group)(?=\\\(?P<name>\w+)))))?/', $requestedName, $matched ))
			return false;
		
		if (empty ( $matched ) || ! isset ( $matched ['feedName'] ))
			return false;
		
		$this->feedName = $matched ['feedName'];
		
		// set a default formType
		if (! isset ( $matched ['formType'] ))
			$matched ['formType'] = 'Feedback';
		
		$this->formType = $matched ['formType'];
		
		return true;
	
	}

	/*
	 * (non-PHPdoc)
	 * @see \Zend\ServiceManager\AbstractFactoryInterface::createServiceWithName()
	 */
	public function createServiceWithName(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator, $name, $requestedName) {

		$appServiceLocator = $serviceLocator->getServiceLocator ();
		$feedbackModel = $appServiceLocator->get ( 'BaftFeedback\Model\feedback' );
		
		if (! $feedbackEntity = $feedbackModel->findByName ( $this->feedName ))
			throw new \Exception ( __METHOD__ . ' : Feedback form with name"' . $this->formName . '" dose not exist!' );
		
		switch ($this->formType) {
			case 'Feedback' :
				$this->formName = $matched ['feedName'];
				break;
			case 'Subject' :
				$this->formName = $matched ['feedName'];
				break;
			case 'Questions' :
				$this->formName = $matched ['feedName'];
				break;
			case 'Question' :
				break;
			case 'Group' :
				break;
			default :
				throw new \Exception ( __METHOD__ . ' : BaftFeedback form factory form Type "' . $this->formType . '" dose not matche!' );
				break;
		}
	
	}


}
    
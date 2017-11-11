<?php

namespace BaftFeedback\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class #FeedbackForm extends Form implements ServiceLocatorAwareInterface , FeedbackFormInterface{

	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	public 
function getSubject() {

}
	
	public function getQuestins(){}
	

	public function __construct() {
	    parent::__construct ( 'feedback' );
	
	    //@TODO create form for this Form Object
	}
	

	public function init() {

	    $questionService = $this->getServiceLocator()->get('BaftFeedback\Form\question');
	    
	    $formBindObject = new \stdClass();
	    
	    $checkListGroups = $this->getGroups($feedbackEntity);
	    // var_dump($em->getConnection()
	    // ->getConfiguration()->getSQLLogger()->queries);
	    
	    // create master fieldset with name of feedback
	    //         $feedbackNamespace = $this->getfeedbackNamespace($feedbackEntity);
	    $feedbackNamespace = "baftfeedback_questions";
	    $feedbackFieldset = new Fieldset($feedbackNamespace );
	    $feedbackFieldset->setHydrator(new ObjectProperty());
	    $feedbackFieldset->setAttribute('class', $feedbackNamespace);
	    $feedbackFieldset->id = $feedbackEntity->getId();
	    
	    $this->getServiceLocator()
	    ->get('FormElementManager')
	    ->setService($feedbackNamespace, $feedbackFieldset);
	    
	    $formFactory = $feedbackFieldset->getFormFactory();
	    $formFactory->setFormElementManager($this->getServiceLocator()
	        ->get('FormElementManager'));
	    
	    // ############################ create groups
	    if (! empty($checkListGroups))
	        foreach ($checkListGroups as $group) {
	            $questionGroupBindObject = new \stdClass();
	            $questionGroupBindObject->id = $group->getId();
	    
	            $groupFieldset = new Fieldset($group->getName());
	            $groupFieldset->setLabel($group->getLabel());
	            $groupFieldset->setAttribute('class', 'question_group');
	            $groupFieldset->setAttribute('data-feedback-group', $group->getName());
	    
	            $questions = $group->getQuestions();
	            foreach ($questions as $question) {
	                // ############################ create question
	                $questionFieldset = $this->getServiceLocator()->get('BaftFeedback\Form\question');
	                $questionGroupBindObject->{$question->getName()} = $questionFieldset->getObject();
	    
	                // register question to group
	                $groupFieldset->add($questionFieldset, [
	                    'priority' => 0
	                ]);
	                // ############################################
	            }
	    
	            $groupFieldset->setHydrator(new ObjectProperty())->setObject($questionGroupBindObject);
	            $formBindObject->{$group->getName()} = $questionGroupBindObject;
	    
	            $feedbackFieldset->add($groupFieldset, [
	                'priority' => $group->getOrder()
	            ]);
	        }
	    // ############################################
	    
	    $feedbackFieldset->setObject($formBindObject);
	}

	/**
	 * Set the service locator.
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
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
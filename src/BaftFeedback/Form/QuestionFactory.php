<?php

namespace BaftFeedback\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Form\Factory;
use Zend\Json\Json;
use Zend\InputFilter\InputFilter;

class QuestionFactory implements ServiceLocatorAwareInterface {
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	protected $question;
	protected $group;
	protected $inputFilter;

	/**
	 */
	public function getInputFilter() {

		if (! isset ( $this->inputFilter )) {
			$inputFilter = [ 
					'type' => 'Zend\InputFilter\InputFilter' 
			];
			$this->setInputFilter ( $inputFilter );
		}
		
		return $this->inputFilter;
	
	}

	public function setInputFilter($inputFilter) {

		$this->inputFilter = $inputFilter;
		return $this;
	
	}

	public function __construct($question) {

		$questionGroup = $question->getRefBaftfeedbackQuestionGroup ();
		$this->setQuestion ( $question );
		$this->setGroup ( $questionGroup );
	
	}

	public function create() {

		if (! isset ( $this->question ))
			throw new \Exception ( __METHOD__ . " question factory required question object to generating fieldset" );

		// ############################ create question
		$formFactory = new Factory ( $this->getServiceLocator ()->get ( 'FormElementManager' ) );
		$questionBindObject = new \stdClass ();
		
		$questionEntity = $this->getQuestion ()->getRefBaftfeedbackQuestion ();
		
		$questionLabel = $this->getQuestion ()->getLabel ();
		if (empty ( $questionLabel ))
			$questionLabel = $questionEntity->getLabel ();
		
		$fieldsetReference = $questionEntity->getRefFieldset ();
		
		$questionNamespace = $this->getQuestion ()->getQuestionNamespace ();
		
		$questionEntityConfig = Json::decode ( $questionEntity->getJsonFieldsetConfig (), Json::TYPE_ARRAY );
		
		$questionConfig = (Json::decode ( $this->getQuestion ()->getJsonFieldsetConfig (), Json::TYPE_ARRAY )) ?: [ ];
		// add default id and class
		$questionConfig = array_merge ( [ 
				"attributes" => [ 
						"id" => $questionNamespace,
						"class" => "feedback_question {$questionNamespace}" 
				] 
		], $questionConfig );
		
		$questionFieldsetConfig = [ 
				'type' => $fieldsetReference,
				'name' => $questionNamespace,
				'options' => [ 
						'label' => $questionLabel 
				] 
		];
		
		$questionFieldsetConfig = array_merge ( $questionEntityConfig, $questionConfig, $questionFieldsetConfig );
		
		$questionFieldset = $formFactory->create ( $questionFieldsetConfig );
		
		// question have to be fieldset not other element
		if (! $questionFieldset instanceof Fieldset)
			throw new \Exception ( __METHOD__ . " question '{$fieldsetReference}' have to be Filedset" );
		
		$questionFieldset->setHydrator ( new ObjectProperty () );
		
		// ##############################################
		

		$inputFilter = $this->getInputFilter ();
		// if question has inputFilter Array
		if ($questionFieldset instanceof InputFilterProviderInterface) {
			$inputFilter = $questionFieldset->getInputFilterSpecification ();
		}
		
		// register bind object . register fields in bind object
		foreach ( $questionFieldset->getElements () as $element ) {
			$questionBindObject->{$element->getName ()} = '';
		}
		
		// ##################################### add reference field to question (baftfeedback_qid)
		$fieldName = 'baftfeedback_qid';
		$questionBindObject->{$fieldName} = $this->getQuestion ()->getId ();
		/*
		 * @TODO do we need to add hidden field for question id ???????
		 * $questionFieldset->add([
		 * 'type' => 'hidden',
		 * 'name' => $fieldName,
		 * 'attributes' => array(
		 * 'value' => $this->getQuestion()->getId()
		 * )
		 * ]);
		 *
		 * $questionFactory=$this;
		 * $inputFilter[]=[
		 * 'name' => $fieldName,
		 * 'required' => false,
		 * 'validators' => [
		 * new \Zend\Validator\Callback([
		 * 'callback' => function ($value) use($questionFactory) {
		 * return true;
		 * return $questionFactory->getServiceLocator()
		 * ->get('BaftFeedback\Model\feedback')
		 * ->hasQuestion($this->getFeedback(), $value);
		 * },
		 * 'messages' => [
		 * \Zend\Validator\Callback::INVALID_VALUE => "requested question dose not exist"
		 * ]
		 * ])
		 * ]
		 * ];
		 */
		
		// #####################################
		
		$this->setInputFilter ( $inputFilter );
		
		$questionFieldset->setObject ( $questionBindObject );
		
		return $questionFieldset;
	
	}

	/**
	 *
	 * @return the $subjectNamespace
	 */
	public function getNamespace() {

		if (! isset ( $this->questionNamespace )) {
			$this->setNamespace ( $this->getQuestion ()->getNamespace () );
		}
		
		return $this->questionNamespace;
	
	}

	/**
	 *
	 * @param
	 *        	Ambigous <string, number> $subjectNamespace
	 */
	public function setNamespace($namespace) {

		$this->questionNamespace = $namespace;
		
		return $this;
	
	}

	public function setQuestion($questionGroup) {

		$this->question = $questionGroup;
		
		return $this;
	
	}

	public function getQuestion() {

		return $this->question;
	
	}

	/**
	 * set parent gorup of question , question need to know about parent
	 * 
	 * @param unknown $group        	
	 * @return \BaftFeedback\Form\QuestionFactory
	 */
	public function setGroup($group) {

		$this->group = $group;
		
		return $this;
	
	}

	public function getGroup() {

		return $this->group;
	
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
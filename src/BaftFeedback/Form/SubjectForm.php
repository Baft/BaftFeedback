<?php

namespace BaftFeedback\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Form\Fieldset;
use Zend\Form\Factory;
use Zend\Json\Json;
use BaftFeedback;

class SubjectForm extends Form implements FeedbackFormInterface {
	const VALUES_DICTIONARY = 'values_dictionary';
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	protected $feedback;
	protected $subjectNamespace;

	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \BaftFeedback\Form\FeedbackFormInterface::getNamespace()
	 */
	public function getNamespace() {

		if (! isset ( $this->subjectNamespace )) {
			$this->setNamespace ( $this->getFeedback ()->getSubjectNamespace () );
		}
		
		return $this->subjectNamespace;
	
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \BaftFeedback\Form\FeedbackFormInterface::setNamespace()
	 */
	public function setNamespace($namespace) {

		$this->subjectNamespace = $namespace;
		
		return $this;
	
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \BaftFeedback\Form\FeedbackFormInterface::getDataEntities()
	 */
	public function getDataEntities() {
		// TODO Auto-generated method stub
	}

	public function init() {
		
		//@TODO all subject forms have to implement SubjectFormInterface->getSubjectItems()

		$this->getFormFactory ()->setFormElementManager ( $this->getServiceLocator ()->get ( 'FormElementManager' ) );
		
		$subjectFieldset = $this->getFieldset ();
		
		$baseBindObject = new \stdClass ();
		$baseBindObject->{$this->getNamespace ()} = $subjectFieldset->getObject ();
		
		$this->setHydrator ( new ObjectProperty () );
		$this->setObject ( $baseBindObject );
		$this->bind ( $this->getObject () );
		$this->add ( $subjectFieldset );
		
		$this->add ( array (
				'type' => 'hidden',
				'name' => 'feedback',
				'attributes' => array (
						'value' => $this->getFeedback ()->getId () 
				) 
		) );
		
		$this->add ( array (
				'name' => 'submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Send' 
				) 
		) );
	
	}

	public function __construct($namespace) {

		parent::__construct ( $namespace );
		
		$this->setAttribute ( 'method', 'POST' )->setBindOnValidate ( Form::BIND_ON_VALIDATE );
	
	}

	/**
	 * make one level array from multi level form data
	 * keep fieldset name as namespace for elements name : [fieldsetName].../[filedName]
	 * [ nameSpace ] => nodeValue
	 * 
	 * @param array $data        	
	 * @param string $container        	
	 */
	public function getData($flag = \Zend\Form\FormInterface::VALUES_NORMALIZED) {

		if (strcasecmp ( $flag, self::VALUES_DICTIONARY ) != 0)
			return parent::getData ( \Zend\Form\FormInterface::VALUES_AS_ARRAY );
		
		$container = '';
		$data = $this->getData ( \Zend\Form\FormInterface::VALUES_AS_ARRAY );
		
		if (empty ( $data ))
			return [ ];
			
			// remve subject namespace from field name
		if (! isset ( $data [$this->getNamespace ()] ))
			throw new \Exception ( __METHOD__ . ' : subject namespace "' . $this->getNamespace () . '" dose not set in form data' );
		
		return $this->toDictionary ( $data [$this->getNamespace ()], $container );
	
	}

	protected function toDictionary($data, $container = '') {

		$flat = [ ];
		
		foreach ( $data as $key => $value ) {
			
			if (! empty ( $container ) && is_string ( $container )) {
				
				if (is_string ( $key ))
					$key = $container . "/" . $key;
				
				if (is_int ( $key ))
					$key = $container;
			}
			
			if (is_array ( $value )) {
				$list = $this->toDictionary ( $value, $key );
			} else
				$list = [ 
						$key => $value 
				];
			
			$flat = array_merge_recursive ( $flat, $list );
		}
		
		return $flat;
	
	}

	
	/**
	 *
	 * @param
	 *        	BaftfeedbackFeedback | BaftfeedbackFeedbackSubject $feedback_subject
	 * @throws \Exception
	 */
	public function getFieldset() {

		if ($this->has ( $this->getNamespace () ))
			return $this->get ( $this->getNamespace () );
		
		$subject = null;
		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		$subjectModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\subject' );
		
		// create subjects container
		$fieldsetName = $this->getNamespace ();
		// $fieldsetName = 'baftfeedback_subject';
		$containerFieldset = new Fieldset ( $fieldsetName );
		$containerFieldset->setAttribute ( 'class', $fieldsetName );
		$containerFieldset->setHydrator ( new ObjectProperty () );
		$subjectsBindObject = new \stdClass ();
		
		// read subject Entity by feedback if $feedback_subject is a feedback
		if (! isset ( $this->feedback ))
			throw new \Exception ( __CLASS__ . " feedback dose not set ." );
		
		$feedbackEntity = $feedbackModel->find ( $this->getFeedback () );
		$subject = $subjectModel->findByFeedback ( $feedbackEntity );
		
		// subjects have to be array
		if (! $subject instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubject)
			throw new \Exception ( __METHOD__ . " expect subject to be array " );
			
			// fieldset name can not be empty
		if (is_null ( $fieldsetName ))
			throw new \Exception ( " fieldset name can not be empty in " . __METHOD__ );
		
		$formFactory = new Factory ( $this->getServiceLocator ()->get ( 'FormElementManager' ) );
		
		// convert subject questions to form object
		// foreach ($subjects as $subject) {
		
		$subjectFieldset = $subject->getRefFieldset ();
		
		$subjectName = $subject->getName ();
		
		$subjectBindObject = new \stdClass ();
		// $subjectBindObject->id = $subject->getId();
		// creating question form object
		$fieldsetConfig = ($subject->getJsonFieldsetConfig ()) ?: '{}';
		$fieldsetConfig = Json::decode ( $subject->getJsonFieldsetConfig (), Json::TYPE_ARRAY );
		$fieldsetConfig = array_merge_recursive ( [ 
				'type' => $subjectFieldset,
				'name' => $subjectName,
				"attributes" => [ 
						"id" => $subjectName 
				],
				'options' => [ 
						'label' => $subject->getLabel () 
				] 
		], $fieldsetConfig );
		
		$subjectFieldset = $formFactory->create ( $fieldsetConfig );
		$subjectFieldset->setHydrator ( new ObjectProperty () );
		
		// register question elements in bind object
		foreach ( $subjectFieldset->getElements () as $element ) {
			$subjectBindObject->{$element->getName ()} = '';
		}
		
		$subjectFieldset->setObject ( $subjectBindObject );
		
		// register to subject container
		$containerFieldset->add ( $subjectFieldset, [ 
				'priority' => $subject->getSubjectOrder () 
		] );
		
		$subjectsBindObject->{$subjectFieldset->getName ()} = $subjectBindObject;
		// }
		
		$containerFieldset->setObject ( $subjectsBindObject );
		
		return $containerFieldset;
	
	}

	/**
	 *
	 * @return the $feedback
	 */
	public function getFeedback() {

		return $this->feedback;
	
	}

	/**
	 *
	 * @param field_type $feedback        	
	 */
	public function setFeedback($feedback) {

		$this->feedback = $feedback;
	
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
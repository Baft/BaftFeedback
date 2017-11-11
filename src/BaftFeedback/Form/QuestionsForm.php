<?php

namespace BaftFeedback\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Doctrine\Common\Collections\ArrayCollection;
use BaftFeedback\Entity\BaftfeedbackFeedback;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData;
use BaftFeedback\Entity\BaftfeedbackQuestion;
use Zend\Form\Fieldset;
use Zend\Form\InputFilterProviderFieldset;
use Zend\InputFilter\InputFilter;

class QuestionsForm extends Form implements ServiceLocatorAwareInterface, FeedbackFormInterface {

	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedback $feedback
	 */
	protected $feedback;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submission
	 */
	protected $submission;
	protected $questionsNamespace;

	public function init() {

		$this->setAttribute ( 'method', 'POST' )->setBindOnValidate ( Form::BIND_ON_VALIDATE )->getFormFactory ()->setFormElementManager ( $this->getServiceLocator ()->get ( 'FormElementManager' ) );
		$this->setHydrator ( new ObjectProperty () );

		$baseBindObject = new \stdClass ();

		$fieldset = $this->buildFieldset ();
		$baseBindObject->{$this->getNamespace ()} = $fieldset->getObject ();

		$this->setObject ( $baseBindObject );
		// $this->bind($this->getObject());

		$this->add ( $fieldset );
		$this->add ( array (
				'name' => 'submit',
				'type' => 'submit',
				'attributes' => array (
						'value' => 'Send'
				)
		) );

	}

	public function __construct($namespace) {

		parent::__construct ( $namespace );

	}

	/**
	 *
	 * @param
	 *        	BaftfeedbackFeedback | BaftfeedbackFeedbackSubject $feedback_subject
	 * @throws \Exception
	 */
	protected function buildFieldset() {

		if (! isset ( $this->feedback ))
			throw new \Exception ( __METHOD__ . " feedback requird for generating questions form" );

		$feedbackEntity = $this->getFeedback ();

		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		$formBindObject = new \stdClass ();

		$questionGroups = $feedbackService->getQuestionGroups ( $feedbackEntity );
		// var_dump($em->getConnection()
		// ->getConfiguration()->getSQLLogger()->queries);

		// ############################ create questions group
		$questionsNamespace = $feedbackEntity->getQuestionsNamespace ();
		$feedbackFieldset = new InputFilterProviderFieldset ( $questionsNamespace );
		$feedbackFieldset->setHydrator ( new ObjectProperty () );
		$feedbackFieldset->setAttribute ( 'class', $questionsNamespace );
		$feedbackFieldset->useAsBaseFieldset ( true );
		$feedbackFieldset->id = $feedbackEntity->getId ();

		$this->getServiceLocator ()->get ( 'FormElementManager' )->setService ( $questionsNamespace, $feedbackFieldset );

		$formFactory = $feedbackFieldset->getFormFactory ();
		$formFactory->setFormElementManager ( $this->getServiceLocator ()->get ( 'FormElementManager' ) );
		// ############################ create questions group

		if (! empty ( $questionGroups ))
			foreach ( $questionGroups as $group ) {

				// ############################ create groups
				$groupFieldset = new QuestionGroupFieldset ( $group );
				$groupFieldset->setServiceLocator ( $this->getServiceLocator () );
				$groupFieldset->init ();
				// ############################################

				$formBindObject->{$groupFieldset->getName ()} = $groupFieldset->getObject ();
				$feedbackFieldset->add ( $groupFieldset, [
						'priority' => $groupFieldset->getGroup ()->getOrder ()
				] );
			}

		$feedbackFieldset->setObject ( $formBindObject );
		$this->getInputFilter ()->add ( $feedbackFieldset, $questionsNamespace );

		return $feedbackFieldset;

	}

	/**
	 * make one level array from multi level form data
	 * keep fieldset name as namespace for elements name : [fieldsetName].../[filedName]
	 * [ nameSpace ] => nodeValue
	 *
	 * @param array $data
	 * @param string $container
	 */
	public function getDataEntities() {

		$container = '';
		$entityCollection = new ArrayCollection ();
		$data = $this->getData ( \Zend\Form\FormInterface::VALUES_AS_ARRAY );

		if (empty ( $data ))
			return [ ];

			// remve subject namespace from field name
		$data = $data [$this->getNamespace ()];

		$dataDict = $this->toDictionary ( $data, $container );

		foreach ( $dataDict as $fieldName => $fieldValue ) {

			$submissionDataEntity = new BaftfeedbackFeedbackSubmissionData ();
			// $submissionDataEntity->setRefBaftfeedbackSubmissionId($this->getSubmission());
			// $submissionDataEntity->setRefBaftfeedbackQuestion();
			// $submissionDataEntity->setRefBaftfeedbackQuestionGroup(2);
			// $submissionDataEntity->setRefBaftfeedbackFeedbackSubmitterData();
			$submissionDataEntity->setValue ( $fieldValue );
			$submissionDataEntity->setQuestionFieldName ( $fieldName );

			$entityCollection->add ( $submissionDataEntity );
		}
		//var_dump ( $entityCollection->toArray () );
		//die ();

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

		return $this->get ( $this->getFeedback ()->getQuestionsNamespace () );

	}

	/**
	 *
	 * @return the $subjectNamespace
	 */
	public function getNamespace() {

		if (! isset ( $this->questionsNamespace )) {
			$this->setNamespace ( $this->getFeedback ()->getQuestionsNamespace () );
		}

		return $this->questionsNamespace;

	}

	/**
	 *
	 * @param
	 *        	Ambigous <string, number> $subjectNamespace
	 */
	public function setNamespace($namespace) {

		$this->questionsNamespace = $namespace;

		return $this;

	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \BaftFeedback\Form\FeedbackFormInterface::getFeedback()
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

		$this->add ( array (
				'type' => 'hidden',
				'name' => 'feedback',
				'attributes' => array (
						'value' => $this->feedback->getId ()
				)
		) );
		$form = $this;
		$this->getInputFilter ()->add ( [
				'name' => 'feedback',
				'required' => true,
				'allow_empty' => false,
				'validators' => [
						new \Zend\Validator\Callback ( [
								'callback' => function ($value) use($form) {
									return $form->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' )->find ( $value );
								},
								'messages' => [
										\Zend\Validator\Callback::INVALID_VALUE => "requested feedback dose not exist"
								]
						] )
				]
		] );

		return $this;

	}

	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function getSubmission() {

		return $this->submission;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submission
	 * @return \BaftFeedback\Form\QuestionsForm
	 */
	public function setSubmission($submission) {

		$this->submission = $submission;

		$this->add ( array (
				'type' => 'hidden',
				'name' => 'submission',
				'attributes' => array (
						'value' => $this->submission->getId ()
				)
		) );

		$form = $this;
		$this->getInputFilter ()->add ( [
				'name' => 'submission',
				'validators' => [
						// existance check
						new \Zend\Validator\Callback ( [
								'callback' => function ($value) use($form) {
									return $form->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' )->find ( $value );
								},
								'messages' => [
										\Zend\Validator\Callback::INVALID_VALUE => "requested submission dose not exist"
								]
						] ),
						// appurtenant check to feedback
						new \Zend\Validator\Callback ( [
								'callback' => function ($value, $context = array()) use($form) {

									if (! isset ( $context ['feedback'] ) || empty ( $context ['feedback'] ))
										return false;

									return $form->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' )->appurtenantSubmission ( $value, $context ['feedback'] );
								},
								'messages' => [
										\Zend\Validator\Callback::INVALID_VALUE => "requested submission dose not appurtenant to requested feedback "
								]
						] )
				]
		] );

		return $this;

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
<?php

namespace BaftFeedback\Form;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Form\InputFilterProviderFieldset;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class QuestionGroupFieldset extends InputFilterProviderFieldset implements ServiceLocatorAwareInterface {

	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	protected $groupNamespace;
	protected $questionGroup;

	public function init() {

		$this->buildFieldset ();

	}

	public function __construct($group) {

		$this->setGroup ( $group );

		parent::__construct ( "baftfeedback_questiongroup_form" );

	}

	protected function buildFieldset() {

		if (! isset ( $this->questionGroup ))
			throw new \Exception ( __METHOD__ . " question group requird for generating question group form" );

		$group = $this->getGroup ();
		$groupOrder = $group->getOrder ();
		$groupNamespace = $this->getNamespace ();
		$groupBindObject = new \stdClass ();

		$this->setName ( $groupNamespace );
		$this->setLabel ( $group->getLabel () );
		$this->setAttribute ( 'class', "feedback_question_group {$groupNamespace}" );
		$this->setAttribute ( 'data-feedback-group', $groupNamespace );
		$this->setAttribute ( 'data-feedback-group-order', $groupOrder );

		$inputFilter = [
				'type' => 'Zend\InputFilter\InputFilter'
		];

		$questions = $group->getQuestions ();

		foreach ( $questions as $question ) {

			$questionEntity = $question->getRefBaftfeedbackQuestion ();
			$questionOrder = $question->getQuestionOrder ();

			// ############################ create question
			$questionFieldsetFactory = new QuestionFactory( $question );
			$questionFieldsetFactory->setServiceLocator ( $this->getServiceLocator () );
			$questionFieldset = $questionFieldsetFactory->create ();
			$questionFieldset->setAttribute ( 'data-feedback-question-order', $questionOrder );
			// ############################################

			$groupBindObject->{$questionFieldset->getName ()} = $questionFieldset->getObject ();
			$this->add ( $questionFieldset, [
					'priority' => $questionOrder
			] );
		}

		// ##################################### add reference field to group (baftfeedback_gid)
		/*
		 * $this->add([
		 * 'type' => 'hidden',
		 * 'name' => 'baftfeedback_gid',
		 * 'attributes' => array(
		 * 'value' => $this->getGroup()
		 * ->getId()
		 * )
		 * ]);
		 *
		 * $form = $this;
		 *
		 * $inputFilter[]=[
		 * 'name' => 'baftfeedback_gid',
		 * 'validators' => [
		 * new \Zend\Validator\Callback([
		 * 'callback' => function ($value) use($form) {
		 * return true;
		 * return $form->getServiceLocator()
		 * ->get('BaftFeedback\Model\feedback')
		 * ->hasGroup($this->getFeedback(), $value);
		 * },
		 * 'messages' => [
		 * \Zend\Validator\Callback::INVALID_VALUE => "requested question group dose not exist"
		 * ]
		 * ])
		 * ]
		 * ];
		 */

		$groupBindObject->baftfeedback_gid = $group->getId ();

		// ###############################################################################

		$this->setHydrator ( new ObjectProperty () )->setObject ( $groupBindObject );
		$this->setInputFilterSpecification ( $inputFilter );

		return $this;

	}

	/**
	 *
	 * @return the $subjectNamespace
	 */
	public function getNamespace() {

		if (! isset ( $this->groupNamespace )) {
			$this->setNamespace ( $this->getGroup ()->getNamespace () );
		}

		return $this->groupNamespace;

	}

	/**
	 *
	 * @param
	 *        	Ambigous <string, number> $subjectNamespace
	 */
	public function setNamespace($namespace) {

		$this->groupNamespace = $namespace;

		return $this;

	}

	public function setGroup($questionGroup) {

		$this->questionGroup = $questionGroup;

		return $this;

	}

	public function getGroup() {

		return $this->questionGroup;

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
<?php

namespace BaftFeedback\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\Model\ViewModel;
use Zend\Form\Form;

class FeedbackFormHelper extends AbstractHelper implements ServiceLocatorAwareInterface {
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	private $feedback;
	private $submission;
	private $form;
	private $options = [
			// for $form->setAttributes
			'form_attributes' => [ ],
			// template file to render form
			'template' => '',
			// pass this variable to template file to be render
			'template_variables' => [ ] 
	];

	public function __invoke($feedback = null, $options = []) {

		$this->setFeedback ( $feedback );
		$this->setOptions ( $options );
		return $this;
	
	}

	public function __toString() {

		try {
			return $this->render ();
		}
		catch ( \Exception $e ) {
			$msg = get_class ( $e ) . ': ' . $e->getMessage ();
			trigger_error ( $msg, E_USER_ERROR );
			return '';
		}
	
	}

	public function render() {

		$formHelper = $this->getView ()->plugin ( 'form' );
		$formObject = $this->getForm ();
		$options = $this->getOptions ();
		// $messageForm=$this->getView()->getHelperPluginManager()->getServiceLocator()->get('BaftComment\Form\MessageForm');
		
		if (! $formObject instanceof Form)
			return '';
		
		if (isset ( $options ['template'] ) && ! empty ( $options ['template'] ))
			return $this->renderTemplate ( $formObject );
		
		$formObject->prepare ();
		return $formHelper ( $formObject );
	
	}

	protected function renderTemplate($form) {

		$options = $this->getOptions ();
		$viewModelVariables = array_merge ( [ 
				'feedback_form' => $form,
				'feedback' => $this->getFeedback () 
		], $options ['template_variables'] );
		$template = $this->getOptions () ['template'];
		
		$viewModel = new ViewModel ();
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate ( $template );
		$viewModel->setVariables ( $viewModelVariables );
		
		// return $this->getView()->getEngine()->setCanRenderTrees(true)->render($viewModel);
		return $this->getView ()->partial ( $viewModel );
	
	}

	public function getForm() {

		if (isset ( $this->form ))
			return $this->form;
		
		$appServiceLocator = $this->getView ()->getHelperPluginManager ()->getServiceLocator ();
		$feedbackService = $appServiceLocator->get ( 'BaftFeedback\Service\feedback' );
		$feedbackEvent = $appServiceLocator->get ( 'BaftFeedback\Event\feedback' );
		
		$feedback = $this->getFeedback ();
		
		$submission = $this->getSubmission ();
		
		$form = $feedbackEvent->getFeedbackForm ();
		
		if (! isset ( $form )) {
			$form = $feedbackService->getFeedbackForm ( $feedback, $submission );
		}
		
		$attrs = $this->getOptions () ['form_attributes'];
		
		$form->setAttributes ( $attrs );
		
		$this->setForm ( $form );
		
		return $this->form;
	
	}

	public function setForm($form) {

		$this->form = $form;
		return $this;
	
	}

	public function getOptions() {

		return $this->options;
	
	}

	public function setOptions($options) {

		$this->options = array_merge ( $this->options, $options );
		
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

		if (! isset ( $this->serviceLocator ))
			$this->setServiceLocator ( $this->getView ()->getHelperPluginManager () );
		return $this->serviceLocator;
	
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getFeedback() {

		if (! isset ( $this->feedback )) {
			$appServiceLocator = $this->getView ()->getHelperPluginManager ()->getServiceLocator ();
			$feedbackEvent = $appServiceLocator->get ( 'BaftFeedback\Event\feedback' );
			$this->setFeedback ( $feedbackEvent->getFeedback () );
		}
		
		return $this->feedback;
	
	}

	/**
	 *
	 * @param unknown_type $commentId        	
	 */
	public function setFeedback($feedback = null) {

		$this->feedback = $feedback;
		

		return $this;
	
	}

	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function getSubmission() {

		if (! isset ( $this->submission )) {
			$appServiceLocator = $this->getView ()->getHelperPluginManager ()->getServiceLocator ();
			$feedbackEvent = $appServiceLocator->get ( 'BaftFeedback\Event\feedback' );
			$this->setSubmission ( $feedbackEvent->getSubmission () );
		}
		
		return $this->submission;
	
	}

	
	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submission        	
	 * @return \BaftFeedback\Form\QuestionsForm
	 */
	public function setSubmission($submission) {

		$this->submission = $submission;
		
		return $this;
	
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getParentMessage() {

		return $this->parentMessage;
	
	}

	/**
	 *
	 * @param unknown_type $parentMessage        	
	 */
	public function setParentMessage($parentMessage) {

		$this->parentMessage = $parentMessage;
		return $this;
	
	}


}
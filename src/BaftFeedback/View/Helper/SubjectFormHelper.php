<?php

namespace BaftFeedback\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\Model\ViewModel;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ObjectProperty;

class SubjectFormHelper extends AbstractHelper implements ServiceLocatorAwareInterface {
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	protected $form;
	private $feedback;
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
				'subject_form' => $form,
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

		if ($this->form instanceof Form)
			return $this->form;
		
		$appServiceLocator = $this->getView ()->getHelperPluginManager ()->getServiceLocator ();
		$feedbackService = $appServiceLocator->get ( 'BaftFeedback\Service\feedback' );
		
		$feedback = $this->getFeedback ();
		
		if (! $form = $feedbackService->getSubjectForm ( $feedback ))
			return $form;
		
		$attrs = $this->getOptions () ['form_attributes'];
		
		$form->setAttributes ( $attrs );
		
		$this->form = $form;
		
		return $this->form;
	
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
	public function setFeedback($feedback) {

		$this->feedback = $feedback;
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
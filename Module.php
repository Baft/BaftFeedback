<?php

namespace BaftFeedback;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use BaftFeedback\Listener\BaftFeedbackRouteListener;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\ViewModel;

class Module implements BootstrapListenerInterface, AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface, FormElementProviderInterface {

	public function onBootstrap(EventInterface $event) {

		$em = $event->getTarget ()->getEventManager ();
		$sm = $event->getTarget ()->getServiceManager ();

		$em->attach ( new BaftFeedbackRouteListener () );

		$this->attachFeedbackEvents ( $em, $sm );

	}

	public function attachFeedbackEvents($eventManager, $serviceLocator) {

		$aggregateListener = new \BaftFeedback\Listener\FeedbackListenerAggregate ();
		$aggregateListener->setServiceLocator ( $serviceLocator );

		$aggregateListener->attachShared ( $eventManager->getSharedManager () );

	}

	public function getAutoloaderConfig() {

		return array (
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
						)
				)
		);

	}

	public function getConfig($env = null) {

		$config = include __DIR__ . '/config/module.config.php';
		return $config;

	}

	public function getFormElementConfig() {

		$formConfigs = include __DIR__ . '/config/form.config.php';
		return $formConfigs;

	}

	public function getViewHelperConfig() {

		return array (
				/*
				 'factories' => array (
						'zfcUserDisplayName' => function ($sm) {
							//enable to display name in view
							$locator = $sm->getServiceLocator ();
							$viewHelper = new View\Helper\ZfcUserDisplayName ();
							$viewHelper->setAuthService ( $locator->get ( 'zfcuser_auth_service' ) );
							return $viewHelper;
						},
						'zfcUserIdentity' => function ($sm) {
							$locator = $sm->getServiceLocator ();
							$viewHelper = new View\Helper\ZfcUserIdentity ();
							$viewHelper->setAuthService ( $locator->get ( 'zfcuser_auth_service' ) );
							return $viewHelper;
						},
						'zfcAuthnOptions' => function ($sm) {
							//make authn options available (read only) in view
							$locator = $sm->getServiceLocator ();
							$moduleOptions=$locator->get ( 'zfcuser_module_options' );
							$moduleOptions->enableReadOnly();
							$viewHelper = new View\Helper\ZfcUserAuthnOptions($moduleOptions);
							$viewHelper->setAuthenticationOptions($moduleOptions);
							return $viewHelper;
						},
						'zfcUserLoginWidget' => function ($sm) {
							//make login form available as a widget
							$locator = $sm->getServiceLocator ();
							$viewHelper = new View\Helper\ZfcUserLoginWidget ();
							$viewHelper->setViewTemplate ( $locator->get ( 'zfcuser_module_options' )
									->getUserLoginWidgetViewTemplate () );
							$viewHelper->setLoginForm ( $locator->get ( 'zfcuser_login_form' ) );
							return $viewHelper;
						}
				)
				*/
		);

	}

	public function getServiceConfig() {

		$serviceConfig = include_once (__DIR__ . '/config/service.config.php');

		return $serviceConfig;

	}


}

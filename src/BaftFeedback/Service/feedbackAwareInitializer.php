<?php

namespace BaftFeedback\Service;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback\Feedback\feedbackAwareInterface;
use Zend\Db\Sql\Predicate\IsNull;

/**
 *
 * @author web
 *        
 */
class feedbackAwareInitializer implements InitializerInterface {

	/**
	 * feedback controllers Initializer
	 *
	 * @param
	 *        	$instance
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return mixed
	 */
	public function initialize($instance, ServiceLocatorInterface $controllerManager) {

		if ($instance instanceof feedbackAwareInterface) {
			$serviceLocator = $controllerManager->getServiceLocator ();
			$feedback = $serviceLocator->get ( 'application' )->getMvcEvent ()->getRouteMatch ()->getParam ( 'baftfeedback', null );
			if (! is_null ( $feedback ))
				$instance->setFeedback ( $feedback );
		}
	
	}


}
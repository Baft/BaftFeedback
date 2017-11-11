<?php

namespace BaftFeedback\Listener;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session as session;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router as router;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;
use Zend\Session\Container;
use DDD\vo as vo;
use Zend\Mvc\Application;
use Zend\Permissions\Acl\Acl;
use Zend\Mvc\Controller\AbstractController;
use Zend\Stdlib\DispatchableInterface;

class BaftFeedbackRouteListener implements ListenerAggregateInterface {
	const ROUTE_NAME = 'baftFeedback';
	const ROUTE_FEEDBACK_VARIABLE = '__BAFTFEEDBACK__';
	const ROUTE_SUBMISSION_VARIABLE = '__BAFTSUBMISSION__';

	/**
	 *
	 * @var \Zend\Stdlib\CallbackHandler[]
	 */
	protected $listeners = array ();

	/**
	 * Attach to an event manager
	 *
	 * @param EventManagerInterface $events
	 * @param integer $priority
	 */
	public function attach(EventManagerInterface $events, $priority = 1) {

		$this->listeners [] = $events->attach ( MvcEvent::EVENT_ROUTE, array (
				$this,
				'onRoute'
		) );

	}

	public function onRoute(MvcEvent $e) {

		$routeMatch = $e->getRouteMatch ();
		$sm = $e->getApplication ()->getServiceManager ();


		// return $routeMatch;


		// $sm->get('BaftFeedback\Service\feedback')->readFeedback($feedback);
		// @TODO is need to pre load feedback on route request ?????!!!!!

		$feedback = $routeMatch->getParam ( static::ROUTE_FEEDBACK_VARIABLE, false );

		// feedback have to be exist in route.
		if ($feedback === false)
			return $routeMatch;

			// just listening to BaftFeedback route name
		if (stripos ( $routeMatch->getMatchedRouteName (), self::ROUTE_NAME ) != 0)
			return $routeMatch;

			// if route is BaftFeedback/general : do nothing and continue.
		if (strcasecmp ( $routeMatch->getMatchedRouteName (), self::ROUTE_NAME . "/general" ) == 0)
			return $routeMatch;

		$feedbackControllers = $sm->get ( 'config' ) ['baft_feedback'] ['feedbacks'];

		// there is not any registerd controller for this feedback
		if (! isset ( $feedbackControllers [$feedback] ))
			return $routeMatch;

		$controller = $feedbackControllers [$feedback];
		$controllerNamespace = explode ( '\\', $controller );

		// there is no namespace defined in controller name
		if (empty ( $controllerNamespace ) || count ( $controllerNamespace ) < 1)
			return $routeMatch;
		$routeMatch->setParam ( '__NAMESPACE__', $controllerNamespace [0] );
		$routeMatch->setParam ( 'controller', end ( $controllerNamespace ) );

		return $routeMatch;

	}

	/**
	 * Detach all our listeners from the event manager
	 *
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function detach(EventManagerInterface $events) {

		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}

	}

	public function isAllowed() {

		return true;

		$sessionConfig = $this->getServiceManager ()->get ( 'config' ) ['session'];
		$allowed = false;
		$container = new Container ( $sessionConfig ['name'] );
		$role = $container->role;
		$sm = null;

		$controller = $this->getController ();
		if ($controller instanceof DispatchableInterface)
			$sm = $controller->getServiceLocator ();

		if ($this->getServiceManager ())
			$sm = $this->getServiceManager ();

		if (! isset ( $sm ))
			return $allowed;

		$routeMatch = $sm->get ( 'application' )->getMvcEvent ()->getRouteMatch ();
		$routeModule = $routeMatch->getParam ( '__NAMESPACE__', false );
		$routeController = strtolower ( $routeMatch->getParam ( 'controller', false ) );
		$routeAction = strtolower ( $routeMatch->getParam ( 'action', false ) );

		// each module have resources config : [moduleName=>[ControllersName=>[ActionNames=>[Parameters]...]...]...]
		$resources = $sm->get ( 'config' ) ["resources"];

	}


}

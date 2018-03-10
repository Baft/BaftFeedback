<?php

namespace BaftFeedback\Feedback;

use Zend\Mvc\Controller\AbstractActionController;
use BaftFeedback\Event\FeedbackEvent;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;

abstract class feedbackActionAbstract extends AbstractActionController implements feedbackAwareInterface {

	/**
	 * FeedbackEvent
	 * @var unknown
	 */
	protected $feedbackEvent;

	/**
	 * automatically attach listener methods to their event
	 * method name structure have to be "on[eventName]_[customeName]" to be abel attach to eventName
	 * @TODO need to slugify eventName before use in regex to remove unwanted caracters like "." , "_" and convert to camelcase
	 *
	 * {@inheritDoc}
	 *
	 * @see \Zend\Mvc\Controller\AbstractController::dispatch()
	 */
	public function dispatch(Request $request, Response $response = null) {

		return parent::dispatch ( $request, $response );

		$methods = get_class_methods ( $this );
		// @TODO need to slugify eventName before implode to remove unwanted caracters like "." , "_" and convert to camelcase
		$events = implode ( '|', $this->getFeedbackEvent ()->getEvents () );
		foreach ( $methods as $methodName ) {
			$matched = [ ];
			/**
			 * find methods by Name structure:
			 * on[eventName]_[customeName]
			 * and automatically attach on EventName
			 */
			if (@preg_match ( '/on(?P<eventName>' . $events . ')_(?P<listenerName>\w+)/', $methodName, $matched )) {
				// var_dump($methodName);

				if (! isset ( $matched ['eventName'] ))
					continue;

				$eventName = $matched ['eventName'];

				$this->getEventManager ()->getSharedManager ()->attach ( [
						'BaftFeedback',
						get_class ( $this )
				], $eventName, [
						$this,
						$methodName
				] );
			}
		}

	}



	public function setFeedbackEvent(FeedbackEvent $feedbackEvent) {

		$this->feedbackEvent=$feedbackEvent;
	}

	public function getFeedbackEvent() {

		return $this->feedbackEvent;

	}


}
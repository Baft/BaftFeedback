<?php

namespace BaftFeedback\Listener;

use Zend\EventManager\SharedEventManagerInterface;

class BaftFeedbackListenerAbstract {

	public function attachShared(SharedEventManagerInterface $events) {

		return;
		
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


}
<?php

namespace BaftFeedback\Exception;

use Zend\Stdlib\SplStack;

class FeedbackFormException implements BaftFeedbackExceptionInterface {
	protected $messages;
	private $previous;

	public function setPrevious($exception) {

		$this->previous = $exception;
	
	}

	public function addMessage($formMessage) {

		if (! is_array ( $formMessage ))
			$formMessage = [ 
					$formMessage 
			];
		
		$formMessage = $this->toDictionary ( $formMessage );
		
		$this->messages = array_merge ( $this->getMessages (), $formMessage );
		return $this;
	
	}

	public function toDictionary($data, $container = '') {

		$dict = [ ];
		
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
						$key => new \RuntimeException ( $value ) 
				];
			
			$dict = array_merge_recursive ( $dict, $list );
		}
		
		return $dict;
	
	}

	public function getMessages() {

		if (! isset ( $this->messages ))
			$this->messages = [ ];
		return $this->messages;
	
	}


}
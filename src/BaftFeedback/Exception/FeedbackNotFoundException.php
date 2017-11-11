<?php

namespace BaftFeedback\Exception;

class FeedbackNotFoundException extends \UnexpectedValueException implements BaftFeedbackExceptionInterface {
	private $previous;

	public function setPrevious($exception) {

		$this->previous = $exception;
	
	}


}
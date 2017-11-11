<?php

namespace BaftFeedback\Exception;

class FeedbackDbException extends \Exception implements BaftFeedbackExceptionInterface {
	private $previous;

	public function setPrevious($exception) {

		$this->previous = $exception;
	
	}


}
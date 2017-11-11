<?php

namespace BaftFeedback\Exception;

class SubmissionNotFoundException extends \UnexpectedValueException implements BaftFeedbackExceptionInterface {
	private $previous;

	public function setPrevious($exception) {

		$this->previous = $exception;
	
	}


}
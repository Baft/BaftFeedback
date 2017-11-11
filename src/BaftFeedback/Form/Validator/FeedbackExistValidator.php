<?php

namespace BaftFeedback\Form\Validator;

class FeedbackExistValidator extends \Zend\Validator\AbstractValidator {
	const NOT_FOUND = 'not_found';
	const EXISTS = 'exists';
	protected $messageVariables = array (
			'feedabck' => 'feedback' 
	);
	protected $messageTemplates = array (
			self::NOT_FOUND => " feedback '%value%' is not found",
			self::EXISTS => "feedback '%value%' created previously" 
	);

	public function isValid($value) {

		$this->setValue ( $value );
		

		$newFeedback;
		if ($value instanceof \BaftFeedback\Entity\BaftfeedbackFeedback) {
			$this->error ( self::MSG_NUMERIC );
			return false;
		}
		
		if ($value < $this->minimum) {
			$this->error ( self::MSG_MINIMUM );
			return false;
		}
		
		if ($value > $this->maximum) {
			$this->error ( self::MSG_MAXIMUM );
			return false;
		}
		
		return true;
	
	}


}
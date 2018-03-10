<?php

namespace BaftFeedback\Exception;

class EventException extends \Exception implements BaftFeedbackExceptionInterface {
    private $previous;
    
    public function setPrevious($exception) {
        
        $this->previous = $exception;
        
    }
    
    
}
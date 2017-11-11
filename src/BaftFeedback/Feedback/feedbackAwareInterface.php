<?php

namespace BaftFeedback\Feedback;

/**
 * feedback entity inject to services that is implimented feedbackAwareInterface by service initializer
 * 
 * @author web
 *        
 */
interface feedbackAwareInterface {

	/**
	 *
	 * @return feedback
	 */
	public function getFeedback();

	/**
	 * inject feedback
	 * 
	 * @param unknown $feedback        	
	 */
	public function setFeedback($feedback);


}
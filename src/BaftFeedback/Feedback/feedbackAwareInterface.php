<?php

namespace BaftFeedback\Feedback;

use BaftFeedback\Event\FeedbackEvent;

/**
 * feedback entity inject to services that is implimented feedbackAwareInterface by service initializer
 *
 * @author web
 *
 */
interface feedbackAwareInterface {

	public function setFeedbackEvent(FeedbackEvent $feedbackEvent);

	public function getFeedbackEvent();

}
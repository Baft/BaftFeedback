<?php

namespace BaftFeedback\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

interface FeedbackFormInterface extends ServiceLocatorAwareInterface {

	public function getDataEntities();

	/**
	 *
	 * @return the $subjectNamespace
	 */
	public function getNamespace();

	/**
	 *
	 * @param
	 *        	Ambigous <string, number> $subjectNamespace
	 */
	public function setNamespace($namespace);

	/**
	 *
	 * @param
	 *        	BaftfeedbackFeedback | BaftfeedbackFeedbackSubject $feedback_subject
	 * @throws \Exception
	 */
	public function getFieldset();

	/**
	 *
	 * @return the $feedback
	 */
	public function getFeedback();

	/**
	 *
	 * @param field_type $feedback        	
	 */
	public function setFeedback($feedback);


}
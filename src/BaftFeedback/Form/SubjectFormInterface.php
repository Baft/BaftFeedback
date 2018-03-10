<?php
namespace BaftFeedback\Form;

//@TODO make subject forms implement this interface to be able provide list of subject item available
interface SubjectFormInterface {
	
	/**
	 * array of subject items 
	 * @return array
	 */
	public function getSubjectItems();
}
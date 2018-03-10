<?php
namespace BaftFeedback\Acl;
use BjyAuthorize\Provider\Resource\ProviderInterface;
use BaftFeedback\Entity\BaftfeedbackFeedback;

class SubjectResourceProvider implements ProviderInterface{
	/**
	 * @var \Zend\Permissions\Acl\Resource\ResourceInterface[]
	 */
	protected $resources = array();
	/**
	 * 
	 * @var BaftfeedbackFeedback
	 */
	protected $feedback ;
	
	//@TODO read from subject form BaftFeedback\Form\SubjectForm getSubjectItems() and make resources from items "rsrc_feedbackSubjectNamespace_itemName"
	
	/**
	 * {@inheritDoc}
	 */
	public function getResources()
	{
		$this->getFeedback()->getSubject();
	}
	
	
	public function getFeedback(){
		return $this->feedback;
	}
	
	public function setFeedback($feedback){
		$this->feedback=$feedback;
	}
}
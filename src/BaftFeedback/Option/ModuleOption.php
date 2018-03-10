<?php
namespace BaftFeedback\Option;

use Zend\Stdlib\AbstractOptions;

class ModuleOption extends AbstractOptions
{
    
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;
    
    /**
     * list of feedback-controller pairs , use when need to assign controller for specific feedback to handle usecases
	 * eg. '2' => 'service name of controller'
     * @var array
     */
    protected $feedbacks=[];
    
    
    public function setFeedbacks(array $feedbacks){
        $this->feedbacks=$feedbacks;
        return $this;
    }
    
    public function getFeedbacks(){
        return $this->feedbacks;
    }

}
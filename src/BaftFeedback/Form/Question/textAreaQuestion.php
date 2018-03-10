<?php

namespace inspFeedback\Form\Question;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class textAreaQuestion extends Fieldset implements InputFilterProviderInterface {

	public function __construct() {

		parent::__construct ( 'textAreaQuestion' );
		$this->setLabel ( 'textAreaQuestion default label' );
		$this->setAttribute ( 'class', 'comments question' );
		
		$this->add ( array (
				'type' => 'textarea',
				'name' => 'comment',
				'attributes' => [ 
						'class' => "feedback_comment form-control placeholder-no-fix  rtl align-right bg-grey-steel color-black margin-bottom",
						// 'value' => 'description',
						'cols' => '90',
						'placeholder' => 'افزودن ملاحظات و پیشنهادات در مورد شعبه' 
				],
				'options' => array (
						'label' => '',
						'label_attributes' => [ 
								'class' => ' font-blue-ebonyclay' 
						] 
				) 
		) );
	
	}

	public function getInputFilterSpecification() {

		return array (
				'comment' => array (
						'required' => false,
						'allow_empty' => false,
						'continue_if_empty' => false 
				) 
		);
	
	}


}
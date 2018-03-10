<?php

namespace BaftFeedback\Form\Question;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class threeRadioQuestion extends Fieldset implements InputFilterProviderInterface {

	public function __construct() {

		parent::__construct ( 'threeRadioQuestion' );
		$this->setLabel ( 'three radio question template label' );
		
		$this->add ( array (
				'type' => 'radio',
				'name' => 'rdo0',
				'attributes' => [ 
						'class' => 'form-control icheck display-block',
						"data-radio" => "iradio_square-red" 
				],
				'options' => array (
						'label' => '',
						'label_attributes' => [ 
								'class' => 'display-block small-margin-bottom feedback_question_rdo0' 
						],
						'value_options' => array (
								array (
										'value' => 'ans1',
										'label' => 'A',
										'attributes' => array (
												'class' => 'form-control icheck display-block',
												"data-radio" => "iradio_square-green" 
										),
										'label_attributes' => array (
												'class' => 'display-block small-margin-bottom feedback_question_rdo0' 
										) 
								),
								'ans2' => 'B',
								'ans3' => 'C' 
						) 
				) 
		) );
		

		$this->add ( array (
				'type' => 'textarea',
				'name' => 'desc0',
				'attributes' => [ 
						'class' => "form-control placeholder-no-fix  rtl align-right bg-grey-steel color-black",
						'cols' => '70',
						'placeholder' => 'Desc.' 
				],
				'options' => array (
						'label' => 'Desc.:',
						'label_attributes' => [ 
								'class' => 'feedback_question_desc0 font-blue-ebonyclay' 
						] 
				) 
		) );
	
	}

	public function getInputFilterSpecification() {

		return array (
				'rdo0' => array (
						'required' => false,
						'allow_empty' => true,
						'continue_if_empty' => true,
						'filters' => array (),
						'validators' => array () 
				),
				'desc0' => array (
						'required' => true,
						'allow_empty' => false 
				) 
		);
	
	}


}
<?php
return [
		'factories' => [ ],

		'abstract_factories' => [
// 				'BaftFeedback\Form\BaftFeedbackFormAbstractFactory',
				'BaftFeedback\Form\EntityToFormAbstractFactory'
		],

		'invokables' => [
				'BaftFeedback\Form\Element\PeriodSelect' => 'BaftFeedback\Form\Element\PeriodSelectElement',
				'BaftFeedback\Form\Filter\QuestionGroup' => 'BaftFeedback\Form\Filter\QuestionGroupFilter'
		]
];
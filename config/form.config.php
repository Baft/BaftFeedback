<?php
return [ 
		'factories' => [ ],
		
		'abstract_factories' => [ 
				'BaftFeedback\Form\BaftFeedbackFormAbstractFactory',
				'BaftFeedback\Form\EntityToFormAbstractFactory' 
		],
		
		'invokables' => [ 
				'BaftFeedback\Form\Filter\period' => 'BaftFeedback\Form\Filter\periodFilter',
				'BaftFeedback\Form\Filter\QuestionGroup' => 'BaftFeedback\Form\Filter\QuestionGroupFilter' 
		] 
];
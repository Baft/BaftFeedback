<?php
use BaftFeedback\Listener\BaftFeedbackRouteListener;
return array (
		'view_helpers' => array (
				'invokables' => array (
						'baftFeedbackSubjectForm' => 'BaftFeedback\View\Helper\SubjectFormHelper',
						'baftFeedbackQuestionsForm' => 'BaftFeedback\View\Helper\QuestionsFormHelper',
						'baftFeedbackFeedbackForm' => 'BaftFeedback\View\Helper\FeedbackFormHelper'
				)
		),
		'view_manager' => array (
				'template_path_stack' => array (
						'BaftFeedback' => __DIR__ . '/../view'
				)
		),
		'service_manager' => array (
				'aliases' => array (
						'BaftFeedback_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
				)
		),
		'strategies' => array (
				'ViewJsonStrategy'
		),

		'router' => array (
				'routes' => array (

						BaftFeedbackRouteListener::ROUTE_NAME => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/feedback',
										'defaults' => array (
												// set default feedback to 0 , then set a controller for 0 in baftFeedback config for default controller on no feedback set
												BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE => 0,
												'controller' => 'BaftFeedback\index',
												'action' => 'index'
										)
								),

								'may_terminate' => true,
								'child_routes' => array (

// 										'general' => array (
// 												'type' => 'Segment',
// 												'options' => array (
// 														'route' => '/general/:__NAMESPACE__/:controller/:action',
// 														'constraints' => [
// 																'__NAMESPACE__' => '[a-zA-Z][a-zA-Z0-9_-]*',
// 																'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
// 																'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
// 														]
// 												),
// 												'may_terminate' => true
// 										),

										'submission' => array (
												//create submission
												'type' => 'Segment',
												'options' => array (
														'route' => '/submission/:' . BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE,
														'constraints' => [
																BaftFeedbackRouteListener::ROUTE_FEEDBACK_VARIABLE => '[0-9][0-9]*'
														],
														'defaults' => array (
																'action' => 'create'
														)
												),
												'may_terminate' => true,
												'child_routes' => array (
														// edit submission
														'edit' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/:' . BaftFeedbackRouteListener::ROUTE_SUBMISSION_VARIABLE,
																		'Constraints' => [
																				BaftFeedbackRouteListener::ROUTE_SUBMISSION_VARIABLE => '[0-9][0-9_-]*'
																		],
																		'defaults' => array (
																				'action' => 'edit'
																		)
																)
														)
												)

										)
								)

						)
				)
		),

		'doctrine' => array (
				'driver' => array (
						'orm_default' => array (
								'drivers' => array (
										'BaftFeedback\Entity' => 'BaftFeedback_annotation'
								)
						),

						'BaftFeedback_annotation' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array (
										APP_ROOT . DS . 'BaftFeedback' . DS . 'src' . DS . 'BaftFeedback' . DS . 'Entity'
								)
						)
				)
		),

		'baft_feedback' => array (
				// list of feedback-controller pairs , use when need to assign controller for specific feedback to handle usecases
				// '2' => 'inspFeedback\arziFeedback'
				'feedbacks' => [ ],

				// path of form config files . save as file after created. cached.
				'forms_path' => 'data/feedbacks'
		),

		'translator' => array (
				'locale' => 'ir_FA',
				'translation_file_patterns' => array (
						array (
								'type' => 'phpArray',
								'base_dir' => __DIR__ . '/../language',
								'pattern' => '%s.php',
								'text_domain' => __NAMESPACE__
						),
						array (
								'type' => 'phpArray',
								'base_dir' => __DIR__ . '/../language',
								'pattern' => '%s.php',
								'text_domain' => 'default'
						)
				)
		),

		'controllers' => array (
				'invokables' => array (
						'BaftFeedback\index' => 'BaftFeedback\Controller\indexController',
						'BaftFeedback\admin' => 'BaftFeedback\Controller\adminController'
				),
				'initializers' => [ ]
		)
);
// new \BaftFeedback\Service\feedbackAwareInitializer()



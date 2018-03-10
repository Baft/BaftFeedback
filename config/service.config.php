<?php
namespace BaftFeedback;
return [
    'invokables' => [
        
        'BaftFeedback\Model\submissionStates' => 'BaftFeedback\Model\submissionStatesEnum',
        'BaftFeedback\Service\subject' => 'BaftFeedback\Service\subjectService',
        'BaftFeedback\Service\submitter' => 'BaftFeedback\Service\submitterService',
        'BaftFeedback\Service\feedback' => 'BaftFeedback\Service\feedbackService',
        'BaftFeedback\Service\question' => 'BaftFeedback\Service\questionService',
        'BaftFeedback\Service\questionGroup' => 'BaftFeedback\Service\questionGroupService',
        'BaftFeedback\Service\submission' => 'BaftFeedback\Service\submissionService'
    ],
    
    'factories' => [
        'BaftFeedback\Event\feedback' => function &($sm) {
            $feedbackEvent = new \BaftFeedback\Event\FeedbackEvent();
            // $feedbackEvent->setViewModel($sm->get('application')->getMvcEvent()->getViewModel());
            // $feedbackEvent->setViewModel(new ViewModel());
            
            // $feedbackId=$sm->get('application')->getMvcEvent()->getRouteMatch()->getParam ( 'feedback', false );
            // if($feedbackId!==false){
            // $feedbackEntity=$sm->get('BaftFeedback\Service\feedback')->find($feedbackId);
            // }
            
            return $feedbackEvent;
        },
        'BaftFeedback\Model\feedback' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            
            // 1 ############## if 'Doctrine\ORM\EntityManager' dose no accesible
            // $repository = (new DefaultRepositoryFactory())->getRepository($em, 'BaftFeedback\Entity\BaftfeedbackFeedback');
            
            // 2 ############## done by 'Doctrine\ORM\EntityManager'
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackFeedback');
            
            // 3 ############## if metaClass dose not connected to repositoryClass by annotation
            // $metaClass = $em->getClassMetadata('BaftFeedback\Entity\BaftfeedbackFeedback');
            // $repository = new BaftFeedback\Model\feedbackRepository($em,$metaClass);
            
            return $repository;
        },
        'BaftFeedback\Model\submission' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackFeedbackSubmission');
            return $repository;
        },
        'BaftFeedback\Model\submitter' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData');
            return $repository;
        },
        'BaftFeedback\Model\questionGroup' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackQuestionGroup');
            return $repository;
        },
        'BaftFeedback\Model\question' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\QuestionRepository');
            return $repository;
        },
        'BaftFeedback\Model\feedbackQuestion' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions');
            return $repository;
        },
        'BaftFeedback\Model\subjectData' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData');
            return $repository;
        },
        'BaftFeedback\Model\subject' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackFeedbackSubject');
            return $repository;
        },
        'BaftFeedback\Model\feedbackVersion' => function ($sm) {
            $em = $sm->get('Doctrine\ORM\EntityManager');
            $repository = $em->getRepository('BaftFeedback\Entity\BaftfeedbackFeedbackVersion');
            return $repository;
        },
        'Baftfeedback\Option\Module' => function ($sm) {
            $config = $sm->get('Config');
            return new Option\ModuleOption(isset($config['baftfeedback_option']) ? $config['baftfeedback_option'] : array());
        }
    ],
    
    'aliases' => [
        'baftfeedback.feedbackservice' => 'BaftFeedback\Service\feedback',
        'baftfeedback.submissionservice' => 'BaftFeedback\Service\submission',
        'baftfeedback.option' => 'BaftFeedback\Option\Module',
    ],
    
    'abstract_factories' => [],
    
    'initializers' => []
];
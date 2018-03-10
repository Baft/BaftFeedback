<?php

namespace BaftFeedback\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use BaftFeedback\Entity\BaftfeedbackFeedback;
use BaftFeedback\Listener\BaftFeedbackRouteListener;
use BaftFeedback\Exception\FeedbackFormException;
use BaftFeedback\Event\FeedbackEvent;
use Zend\Form\Form;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubject;
use BaftFeedback\Entity\BaftfeedbackQuestionGroup;
use Zend\View\Model\JsonModel;
use Zend\InputFilter\InputFilter;
use jdf;

class adminController extends AbstractActionController {

	public function createFeedbackAction() {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$hydrator = new DoctrineHydrator ( $em );

		$vm = new ViewModel ();
		$feedbackEntity = new BaftfeedbackFeedback ();
		$vm->setVariable ( 'feedback', $feedbackEntity );

		/**
		 *
		 * @var \Zend\Form\Form $form
		 */
		$form = $this->getServiceLocator ()->get ( 'formelementmanager' )->get ( 'BaftFeedback\Entity\BaftfeedbackFeedback' );
		$form->remove ( 'refBaftfeedbackQuestionGroupId' );
		// $form->add(
		// array(
		// 'type' => '\DoctrineModule\Form\Element\ObjectSelect',
		// 'name' => 'subject',
		// 'options' => array(
		// 'object_manager' => $em,
		// 'target_class' => BaftfeedbackFeedbackSubject::class,
		// 'property' => 'name',
		// 'is_method' => true,
		// 'find_method' => array(
		// 'name' => 'findAll',
		// ),
		// 'label'=>'subject',
		// ),
		// )
		// );
		$form->setHydrator ( $hydrator );
		$form->bind ( $feedbackEntity );

		if ($this->request->isPost ()) {
			$form->setData ( $this->params ()->fromPost () );

			if (! $form->isValid ()) {
				$this->showExceptions ( $form );
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'createFeedback'
				] );
			}

			$feedbackEntity = $feedbackService->createFeedback ( $form->getData () );

			if ($feedbackEntity instanceof FeedbackEvent) {
				$this->showExceptions ( $feedbackEntity );
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'createFeedback'
				] );
			}

			$this->flashMessenger ()->addSuccessMessage ( "feedback successfully created" );

			return $this->redirect ()->toRoute (  'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'listFeedback'
			], [
					'query' => [
							'fdbck' => $feedbackEntity->getId ()
					]
			] );
		}


		$vm->setVariable ( 'form', $form );
		return $vm;

	}

	public function editFeedbackAction() {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$hydrator = new DoctrineHydrator ( $em );

		$vm = new ViewModel ();
		$vm->setTemplate ( 'baft-feedback' . DS . 'admin' . DS . 'create-feedback.phtml' );

		$feedbackId = $this->params ()->fromQuery ( 'fdbck', false );
		$feedbackEvent = $feedbackService->readFeedback ( $feedbackId );
		$feedbackEntity = $feedbackEvent->getFeedback();
		$vm->setVariable ( 'feedback', $feedbackEntity );

		/**
		 *
		 * @var \Zend\Form\Form $form
		 */
		$form = $this->getServiceLocator ()->get ( 'formelementmanager' )->get ( 'BaftFeedback\Entity\BaftfeedbackFeedback' );
		$form->get ( 'name' )->setAttribute ( 'disabled', 'disabled' );
		$form->add ( array (
				'name' => 'version_desc',
				'options' => [
						'label' => 'why need to update'
				],
				'attributes' => array (
						'type' => 'textarea',
						'required' => 'required'
				)
		) );
		$form->getInputFilter ()->add ( [
				'required' => true,
				'allow_empty' => false
		], 'version_desc' )->remove ( 'name' );

		if ($this->request->isPost ()) {
			$form->setData ( $this->params ()->fromPost () );

			if (! $form->isValid ()) {
				$this->showExceptions ( $form );
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'editFeedback'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$newFeedbackEntity = $feedbackService->editFeedback ( $feedbackEntity, $form->getData () );

			if ($newFeedbackEntity instanceof FeedbackEvent) {
				$this->showExceptions ( $feedbackEntity );
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'editFeedback'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$this->flashMessenger ()->addSuccessMessage ( "feedback successfully edited" );

			return $this->redirect ()->toRoute (  'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'listFeedback'
			] );
		}

		$form->populateValues ( $hydrator->extract ( $feedbackEntity ) );
		$vm->setVariable ( 'form', $form );

		return $vm;

	}

	public function listFeedbackAction() {

		$feedbackEntity=new BaftfeedbackFeedback();
		$feedbackEntity->setAvailableTime(time()-3600*24*600);
		$feedbackEntity->setDurationTime(3600*24*10);
		$feedbackEntity->setExpireTime(time());
		$feedbackEntity->setIntervalTime(3600*24*10);
		$feedbackEntity->setRepeat(1);
		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$jdf=new jdf();
		$period=$feedbackService->getCurrentPeriodNumber($feedbackEntity);
		$periodId=$feedbackService->getCurrentPeriodId($feedbackEntity);

		$available=$feedbackService->getAvailableTime($feedbackEntity);
		$expire=$feedbackService->getExpireTime($feedbackEntity , $available);
		$respit=$feedbackService->getRespiteTime($feedbackEntity,$period);

// 		var_dump(
// 				$period,
// 				$periodId,
// 				$jdf->jdate("Y m d - H i s",$available),
// 				$jdf->jdate("Y m d - H i s",$expire),
// 				$respit

// 				);

		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		$vm = new ViewModel ();

		$feedbackList = $feedbackModel->findByDeleted (0);

		$vm->setVariable ( 'feedback_list', $feedbackList );

		return $vm;

	}

	// #######################################################
	// #######################################################

	/**
	 * create question .
	 *
	 * write to baftfeedback_question.
	 */
	public function createQuestionAction() {

	}

	/**
	 * add question to feedback .
	 *
	 * label, score , order ,.. can be set here (question localization)
	 * write to baftfeedback_question_group_questions
	 */
	public function addQuestionAction() {

		$vm = new ViewModel ();
		$vm->setTemplate ( 'baft-feedback' . DS . 'admin' . DS . 'create.phtml' );
		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$feedbackQuestionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedbackQuestion' );
		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );
		$hydrator = new DoctrineHydrator ( $em );

		$feedbackId = $this->params ()->fromQuery ( 'fdbck', false );
		$feedbackEvent = $feedbackService->readFeedback ( $feedbackId );
		$feedbackEntity = $feedbackEvent->getFeedback();
		$vm->setVariable ( 'feedback', $feedbackEntity );

		$questionEntity = new \BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions ();

		// if group id is passed
		if ($groupId = $this->params ()->fromQuery ( 'g', false )) {
			$groupEntity = $questionGroupModel->find ( $groupId );
			if ($groupEntity)
				$questionEntity->setRefBaftfeedbackQuestionGroup ( $groupEntity );
		}


		$form = $this->getServiceLocator ()->get ( 'formelementmanager' )->get ( 'BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions' );
		$form->setHydrator ( $hydrator );
		$form->bind ( $questionEntity );

		if ($this->request->isPost ()) {
			$form->setData ( $this->params ()->fromPost () );

			if (! $form->isValid ()) {
				$this->showExceptions ( $form );
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'addQuestion'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$newFeedbackQuestionModel = $feedbackQuestionModel->create ( $form->getData () );

			if (! $newFeedbackQuestionModel instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions) {
				$this->showExceptions ( $newFeedbackQuestionModel );
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'addQuestion'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$this->flashMessenger ()->addSuccessMessage ( "question successfully added" );

			return $this->redirect ()->toRoute (  'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'listQuestion'
			], [
					'query' => [
							'fdbck' => $feedbackEntity->getId ()
					]
			] );
		}

		// $form->populateValues($hydrator->extract($feedbackEntity));
		$vm->setVariable ( 'form', $form );
		return $vm;

	}


	/**
	 * add question to feedback .
	 *
	 * label, score , order ,.. can be set here (question localization)
	 * write to baftfeedback_question_group_questions
	 */
	public function editQuestionAction() {

		$vm = new ViewModel ();
		$vm->setTemplate ( 'baft-feedback' . DS . 'admin' . DS . 'create.phtml' );
		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$feedbackQuestionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedbackQuestion' );
		$hydrator = new DoctrineHydrator ( $em );

		$feedbackId = $this->params ()->fromQuery ( 'fdbck', false );
		$feedbackEvent = $feedbackService->readFeedback ( $feedbackId );
		$feedbackEntity = $feedbackEvent->getFeedback();
		$vm->setVariable ( 'feedback', $feedbackEntity );

		$questionId = $this->params ()->fromQuery ( 'q', false );
		$feedbackQuestionEntity = $feedbackQuestionModel->find ( $questionId );

		$form = $this->getServiceLocator ()->get ( 'formelementmanager' )->get ( 'BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions' );

		$form->setHydrator ( $hydrator );
		$form->bind ( $feedbackQuestionEntity );

		if ($this->request->isPost ()) {
			$form->setData ( $this->params ()->fromPost () );

			if (! $form->isValid ()) {
				$this->showExceptions ( $form );
				// well just actually refresh ;)
				return $this->redirect ()->toRoute (  'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'editQuestion'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId (),
								'q' => $feedbackQuestionEntity->getId ()
						]
				] );
			}

			$newFeedbackQuestionModel = $feedbackQuestionModel->update ( $form->getData () );

			if (! $newFeedbackQuestionModel instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions) {
				$this->showExceptions ( $newFeedbackQuestionModel );
				// well just actually refresh ;)
				return $this->redirect ()->toRoute ( 'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'editQuestion'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId (),
								'q' => $feedbackQuestionEntity->getId ()
						]
				] );
			}

			$this->flashMessenger ()->addSuccessMessage ( "question successfully added" );

			return $this->redirect ()->toRoute ( 'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'listQuestion'
			], [
					'query' => [
							'fdbck' => $feedbackEntity->getId ()
					]
			] );
		}

		$form->populateValues ( $hydrator->extract ( $feedbackQuestionEntity ) );
		$vm->setVariable ( 'form', $form );
		return $vm;

	}

	/**
	 * to move action in groups or change order in group
	 */
	public function moveQuestionAction() {

		$feedbackQuestionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedbackQuestion' );
		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		$questionId = $this->params ()->fromPost ( 'question', false );
		$feedbackQuestionEntity = $feedbackQuestionModel->find ( $questionId );

		$groupId = $this->params ()->fromPost ( 'group', false );
		$groupEntity = $questionGroupModel->findByName ( $groupId );

		if (empty ( $groupEntity ))
			$groupEntity = null;
		else
			$groupEntity = $groupEntity [0];

		$order = $this->params ()->fromPost ( 'order', false );

		$feedbackService->moveQuestion ( $feedbackQuestionEntity, $order, $groupEntity );
		$vm = new JsonModel ();
		return $vm;

	}

	public function listQuestionAction() {

		$vm = new ViewModel ();
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		$feedbackId = $this->params ()->fromQuery ( 'fdbck', false );
		$feedbackEvent = $feedbackService->readFeedback ( $feedbackId );
		$feedbackEntity = $feedbackEvent->getFeedback();

		$vm->setVariable ( 'groups', $feedbackService->getQuestionGroups ( $feedbackEntity ) );
		$vm->setVariable ( 'feedback', $feedbackEntity );
		return $vm;

	}

	// #######################################################
	// #######################################################
	public function createGroupAction() {

		$vm = new ViewModel ();
		$vm->setTemplate ( 'baft-feedback' . DS . 'admin' . DS . 'create.phtml' );
		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );
		$hydrator = new DoctrineHydrator ( $em );

		$feedbackId = $this->params ()->fromQuery ( 'fdbck', false );
		$feedbackEvent = $feedbackService->readFeedback ( $feedbackId );
		$feedbackEntity = $feedbackEvent->getFeedback();
		$vm->setVariable ( 'feedback', $feedbackEntity );

		$form = $this->getServiceLocator ()->get ( 'formelementmanager' )->get ( 'BaftFeedback\Entity\BaftfeedbackQuestionGroup' );

		$form->setHydrator ( $hydrator );
		$form->bind ( new \BaftFeedback\Entity\BaftfeedbackQuestionGroup () );

		if ($this->request->isPost ()) {
			$form->setData ( $this->params ()->fromPost () );

			if (! $form->isValid ()) {
				$this->showExceptions ( $form );
				return $this->redirect ()->toRoute ( 'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'createGroup'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$newQuestionGroupEntity = $questionGroupModel->create ( $form->getData (), $feedbackEntity );

			if (! $newQuestionGroupEntity instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroup) {
				$this->showExceptions ( $newQuestionGroupEntity );
				return $this->redirect ()->toRoute ( 'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'createGroup'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$this->flashMessenger ()->addSuccessMessage ( "group successfully added" );

			return $this->redirect ()->toRoute ( 'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'listQuestion'
			], [
					'query' => [
							'fdbck' => $feedbackEntity->getId (),
							'g' => $newQuestionGroupEntity->getId ()
					]
			] );
		}

		$vm->setVariable ( 'form', $form );
		return $vm;

	}

	public function editGroupAction() {

		$vm = new ViewModel ();
		$vm->setTemplate ( 'baft-feedback' . DS . 'admin' . DS . 'create.phtml' );

		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );

		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );

		$feedbackId = $this->params ()->fromQuery ( 'fdbck', false );
		$feedbackEvent = $feedbackService->readFeedback ( $feedbackId );
		$feedbackEntity = $feedbackEvent->getFeedback();
		$vm->setVariable ( 'feedback', $feedbackEntity );

		$groupId = $this->params ()->fromQuery ( 'g', false );
		$questionGroupEntity = $questionGroupModel->find ( $groupId );

		$form = $this->getServiceLocator ()->get ( 'formelementmanager' )->get ( 'BaftFeedback\Entity\BaftfeedbackQuestionGroup' );

		$hydrator = new DoctrineHydrator ( $em );
		$form->setHydrator ( $hydrator );
		$form->bind ( $questionGroupEntity );

		if ($this->request->isPost ()) {
			$form->setData ( $this->params ()->fromPost () );

			if (! $form->isValid ()) {
				$this->showExceptions ( $form );
				return $this->redirect ()->toRoute ( 'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'createGroup'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$newQuestionGroupEntity = $questionGroupModel->update ( $form->getData () );

			if (! $newQuestionGroupEntity instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroup) {
				$this->showExceptions ( $newQuestionGroupEntity );
				return $this->redirect ()->toRoute ( 'main/general', [
						"__NAMESPACE__" => 'BaftFeedback',
						"controller" => 'admin',
						'action' => 'createGroup'
				], [
						'query' => [
								'fdbck' => $feedbackEntity->getId ()
						]
				] );
			}

			$this->flashMessenger ()->addSuccessMessage ( "group successfully added" );

			return $this->redirect ()->toRoute ( 'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'listQuestion'
			], [
					'query' => [
							'fdbck' => $feedbackEntity->getId ()
					]
			] );
		}

		$form->populateValues ( $hydrator->extract ( $questionGroupEntity ) );
		$vm->setVariable ( 'form', $form );
		return $vm;

	}

	public function submissionAction(){
		$vm = new ViewModel ();
		$vm->setTemplate ( 'baft-feedback' . DS . 'admin' . DS . 'create.phtml' );

		$em = $this->getServiceLocator ()->get ( 'Doctrine\Orm\EntityManager' );
		$submissionService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submission' );

		$actionInput = (new \Zend\InputFilter\Factory())->createInputFilter(array(
				'submission' => array(
						'name' =>'submission' , 'required'=>true , 'allow_empty'=>false , 'continue_if_empty'=>false ,
						'filters' => [
								function ($value) use($submissionService){
									return $submissionService->readSubmission( $actionInput->get('submission') );
								}
						],
						'validators' => array(
								array(
										'name' => 'not_empty',
								),
								function ($value){
									return $value instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission;
								}
						),
				),
		));

		$actionInput->setData( array_merge( $this->params()->fromPost() , $this->params()->fromQuery() ));

		if(!$actionInput->isValid())
			return $this->redirect ()->toRoute ( 'main/general', [
					"__NAMESPACE__" => 'BaftFeedback',
					"controller" => 'admin',
					'action' => 'submission'
			], [
					'query' => [
							'submission' => $submissionEntity->getId ()
					]
			] );


		$submissionEntity = $submissionService->readSubmission( $actionInput->get('submission') );
		$vm->setVariable ( 'submission', $submissionEntity );


	}

	protected function showExceptions($exceptions) {

		if ($exceptions instanceof FeedbackEvent) {
			$feedbackEvent = $exceptions;

			foreach ( $feedbackEvent->getExceptions () as $exceptin ) {
				$this->flashMessenger ()->addErrorMessage ( $exceptin->getMessage () );
			}

			return;
		}

		if ($exceptions instanceof Form) {
			$exception = new FeedbackFormException ();
			$exception->addMessage ( $exceptions->getMessages () );

			$messages = $exception->getMessages ();
			foreach ( $messages as $field => $message ) {
				$this->flashMessenger ()->addErrorMessage ( $field . " : " . $message->getMessage () );
			}

			return;
		}

	}


}
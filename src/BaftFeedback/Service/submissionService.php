<?php

namespace BaftFeedback\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Session\Container;
use Zend\Json\Json;
use Zend\Form\Form;
use Zend\EventManager\ListenerAggregateTrait;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Common\Collections\ArrayCollection;
use BaftFeedback\Event\FeedbackEvent;
use BaftFeedback\Entity\BaftfeedbackFeedback;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmission;
use BaftFeedback\Exception\SubmissionNotFoundException;
use jdf;
use Zend\EventManager\EventInterface;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionState;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData;

class submissionService implements ServiceLocatorAwareInterface, EventManagerAwareInterface {
	public $serviceLocator;
	public $eventManager;
	const DATA_AS_ARRAY = 2;
	const DATA_AS_RESULTSET = 3;


	/**
	 * facade method for 'read' event on submission
	 * find and load submission and submission data
	 *
	 * @param
	 *        	\BaftFeedback\Entity\BaftfeedbackFeedbackSubmission | BaftFeedback\Event\feedbackEvent $submission
	 * @return \BaftFeedback\Event\feedbackEvent
	 */
	public function readSubmission($submission) {

		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );
		$submission = $submissionModel->find ( $submission );
		if (! $submission instanceof BaftfeedbackFeedbackSubmission)
			throw new SubmissionNotFoundException ( __METHOD__ . " : requested submission dose not set ." );

		/**
		 *
		 * @var \BaftFeedback\Event\feedbackEvent $feedbackEvent
		 */
		$feedbackEvent = new FeedbackEvent ();
		$feedbackEvent->setTarget ( $submission );

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_READ_SUBMISSION_PRE, $feedbackEvent );

		if ($feedbackEvent->hasException ())
			return $feedbackEvent;

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_READ_SUBMISSION, $feedbackEvent );

		return $feedbackEvent;

	}

	/**

	 *
	 * @param FeedbackEvent|int|BaftfeedbackFeedbackSubmission $submission
	 * @param array $newData
	 * @param array $subjectData
	 * @return \BaftFeedback\Event\feedbackEvent
	 */
	public function editSubmission($submission, $newData, $subjectData = null) {

		$feedbackEvent = $submission;

		if (! $submission instanceof FeedbackEvent) {

			$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );
			$submission = $submissionModel->find ( $submission );
			if (! $submission instanceof BaftfeedbackFeedbackSubmission)
				throw new SubmissionNotFoundException ( __METHOD__ . " : requested submission dose not set ." );


			/**
			 *
			 * @var \BaftFeedback\Event\feedbackEvent $feedbackEvent
			 */
			$feedbackEvent = new FeedbackEvent ();
			$feedbackEvent->setTarget ( $submission );
			$feedbackEvent->init ( $this->getServiceLocator (), null, $submission );
		} else
			$feedbackEvent = $submission;

		$submitterData = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submitter' )->getCurrentSubmitter ();
		$feedbackEvent->setParam ( 'feedback_form_raw_data', $newData );
		$feedbackEvent->setParam ( 'submitter_data', array_merge ( [
				'submit_time' => time (),
				'start_time' => 0
		], $submitterData ) );

		if (isset ( $subjectData ))
			$feedbackEvent->setParam ( 'form_subject_data', $subjectData );

		$preUpdateSubmissionResult = $this->getEventManager ()->trigger ( FeedbackEvent::EVENT_UPDATE_SUBMISSION_PRE, $feedbackEvent );

		if ($feedbackEvent->hasException ())
			return $feedbackEvent;

		$updateSubmissionResult = $this->getEventManager ()->trigger ( FeedbackEvent::EVENT_UPDATE_SUBMISSION, $feedbackEvent );

		return $feedbackEvent;

	}

	/**
	 *
	 * @param
	 *        	int | BaftFeedbackFeedback $feedback
	 * @param array $subjectData
	 *        	subject data is must parameter , pass empty array if feedback dose not have subject
	 * @param
	 *        	array | boolean $questionsData on false means dose not submission data dose not passed
	 * @return FeedbackEvent
	 */
	public function createSubmission($feedback, array $subjectData = [], $questionsData = false, $feedbackVersion = null) {

		/**
		 * convert feedbackId to feedbackEntity
		 *
		 * @var \BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity
		 */
		$feedbackEntity = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' )->find ( $feedback );


		$submitterData = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\submitter' )->getCurrentSubmitter ();

		$feedbackEvent = new FeedbackEvent ();
		$feedbackEvent->setTarget ( $feedbackEntity );
		$feedbackEvent->init ( $this->getServiceLocator (), $feedbackEntity );
		$feedbackEvent->setFeedbackVersion ( $feedbackEntity->getVersions ()->last () );
		$feedbackEvent->setParam ( 'form_subject_data', $subjectData );
		$feedbackEvent->setParam ( 'feedback_form_raw_data', $questionsData );
		$feedbackEvent->setParam ( 'submitter_data', array_merge ( [
				'submit_time' => time (),
				'start_time' => 0
		], $submitterData ) );

		$preCreateSubmissionResult = $this->getEventManager ()->trigger ( FeedbackEvent::EVENT_CREATE_SUBMISSION_PRE, $feedbackEvent );


		if ($feedbackEvent->hasException ()) {
			return $feedbackEvent;
		}

		$createSubmissionResult = $this->getEventManager ()->trigger ( FeedbackEvent::EVENT_CREATE_SUBMISSION, $feedbackEvent );

		return $feedbackEvent;

	}

	/**
	 * check submission to be appurtenant of requested feedback
	 *
	 * @param int|BaftfeedbackFeedbackSubmission $submissionEntity
	 * @param int|BaftfeedbackFeedback $feedbackEntity
	 * @throws \Exception
	 * @return boolean
	 */
	public function appurtenantSubmission($submissionEntity, $feedbackEntity) {

		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );
		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );

		$submissionEntity = $submissionModel->find ( $submissionEntity );
		$feedbackEntity = $feedbackModel->find ( $feedbackEntity );

		// check requested submissin to be appurtenant to requested feedback
		if ($submissionEntity->getRefBaftfeedbackFeedback ()->getId () != $feedbackEntity->getId ())
			return false;

		return true;

	}

	public function getExpireTime(\BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity, $periodNumber = null, $submissionStartTime = null) {

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbackService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		$respiteTime = $feedbackService->getRespiteTime ( $feedbackEntity, $periodNumber );

		if ($submissionStartTime == null)
			$submissionStartTime = time ();

		$feedbackRrespiteTimeSatrt = $respiteTime ['available_time'];
		$feedbackRespiteTimeEnd = $respiteTime ['expire_time'];

		// check if feedback respite time dose not expired
		if ($feedbackService->isExpiredRespiteTime ( $respiteTime, $submissionStartTime ) != 0) {
			return false;
		}

		$submissionDuraionTime = $feedbackEntity->getSubmissionDuration ();
		// if duration dose not set or duration is longer than respite time
		if ($submissionDuraionTime == 0 || $submissionStartTime + $submissionDuraionTime > $feedbackRespiteTimeEnd)
			$submissionDuraionTime = $feedbackRespiteTimeEnd - $submissionStartTime;

		$expireTime = $submissionStartTime + $submissionDuraionTime;

		return $expireTime;

	}


	/**
	 * get current state of submission
	 *
	 * @param unknown $submission
	 */
	public function getCurrentState($submission) {

		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );

		return $submissionModel->currentState ( $submission );

	}

	/**
	 * is current state equal to $state
	 *
	 * @param unknown $submissionId
	 * @param int $state
	 */
	public function isState($submission, $state) {

		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );

		$currentState = $submissionModel->currentState ( $submission );

		// submission has not any state
		if (! $currentState instanceof BaftfeedbackFeedbackSubmissionState)
			return false;

		return (( int ) $currentState->getState ()) === ( int ) $state;

	}


	/**
	 * dose $state exist in submission state history?
	 * this is not current state, may be current submission state is A but has B state
	 *
	 * @param unknown $submission
	 * @param unknown $state
	 * @return false|BaftfeedbackFeedbackSubmissionState
	 */
	public function hasState($submissionEntity, $state) {

		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );

		return $submissionModel->hasState ( $submissionEntity, $state );

	}

	/**
	 * compare two state
	 *
	 * @param unknown $soureceState
	 * @param array|int $destState
	 *        	array of integers to be compare with $soureceState in "OR" combination
	 */
	public function equalState($soureceState, $destState) {

		if (! is_array ( $destState ))
			$destState = [
					$destState
			];

		foreach ( $destState as $dState ) {
			if ($soureceState == $dState)
				return true;
		}

		return false;

	}


	/**
	 * return subject values of submission
	 */
	public function getSubject($submissionId) {

		$subjectModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\subjectData' );
		$subjectDataEntities = $subjectModel->findBySubmission ( $submissionId );

		$subjectData = [ ];

		foreach ( $subjectDataEntities as $subject ) {
			$subjectData [$subject->getRefBaftfeedbackSubject ()->getName ()] [$subject->getFieldName ()] = $subject->getValue ();
		}

		return $subjectData;

	}

	/**
	 * save submitter
	 * save submission data
	 *
	 * @param int $feedbackId
	 * @param array $feedbackData
	 * @param array $submitterData
	 * @param int $submissionId
	 * @throws \Exception
	 * @throws Exception
	 * @return boolean|BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function saveSubmissionData($submissionEntity, $submitter, $feedbackData) {

		if (empty ( $feedbackData ))
			return $submissionEntity;

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		$connection = $em->getConnection ();
		// $em->getConnection ()
		// ->getConfiguration ()
		// ->setSQLLogger ( new \Doctrine\DBAL\Logging\EchoSQLLogger () );

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $submissionService
		 */
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );

		/**
		 *
		 * @var BaftFeedback\Model\feedbackSubmissionInterface $submissionModel
		 */
		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );

		$submissionEntity = $submissionModel->find ( $submissionEntity );
		$feedbackEntity = $submissionEntity->getRefBaftfeedbackFeedback ();
		$feedbackVersion = $feedbackService->getLastVersion ( $feedbackEntity );

		$connection->beginTransaction ();
		// 		\var_dump($feedbackData);die;
		foreach ( $feedbackData as $row ) {

			$groupId = $row ['question_group_id'];
			$questionId = $row ['question_id'];
			$fieldName = $row ['question_field_name'];
			$fieldValue = $row ['field_value'];

			try {
				$insertData = "
                INSERT INTO `baftfeedback_feedback_submission_data`
                (
                `ref_baftfeedback_feedback_submission_id`,
                `ref_baftfeedback_feedback_submitter_data_id`,
                `ref_baftfeedback_question_group_id`,
                `ref_baftfeedback_question_id`,
                `question_field_name`,
                `value`,
                `version`
                )
                VALUES
                ( '{$submissionEntity->getId()}', '{$submitter->getId()}', '{$groupId}', '{$questionId}', '{$fieldName}', '{$fieldValue}' , '{$feedbackVersion->getId()}' )";

				//print $insertData;

				if (! $connection->query ( $insertData ))
					throw new \Exception ( 'can not register feedback data to db' );
			}
			catch ( Exception $e ) {
				$connection->rollBack ();
				throw $e;
			}
		}

		$connection->commit ();
		$em->refresh ( $submissionEntity );
		return $submissionEntity;

	}


	/**
	 * get all submission data by date , submitter , question
	 *
	 * @param int $submissionId
	 * @param int|array $byDate
	 *        	array of submit times in unixtime or (for int) just a unixtime to delegate a day (program read only "day" part of unixtime)
	 * @param int $bySubmitter
	 * @param int $byQuestion
	 */
	public function getSubmissionData($submissionEntity, $byDate = 'FALSE', $bySubmitter = 'FALSE', $byQuestion = 'FALSE') {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		$connection = $em->getConnection ();

		if (is_numeric ( $byDate ))
			$dateCondition = " DATE_FORMAT(from_unixtime(submit_time),'%m-%d-%Y')=DATE_FORMAT(from_unixtime(@submit_time),'%m-%d-%Y') ";
		if (is_array ( $byDate )) {
			// convert php array to the correspond sql condition then enervate $byDate self (replace by "TRUE") to be used in sql
			$dateCondition = " ( ";
			foreach ( $byDate as $submitTime ) {
				$dateCondition .= " submit_time={$submitTime} OR ";
			}
			$dateCondition .= " FALSE ) ";
			$byDate = " TRUE ";
		}


		$selectQuery = "
			set @submission_id:={$submissionEntity->getId()};
			set @submit_time:={$byDate};
			set @submitter:={$bySubmitter};
			set @question:={$byQuestion};

			select
				ref_baftfeedback_feedback_submission_id as submission,
			    ref_baftfeedback_question_group_id as question_group,
			    ref_baftfeedback_question_id as question,
			    question_field_name as field,
			    `value`,
			    submitter.id as submit_id,
			    submitter,
			    submit_time,
				DATE_FORMAT(from_unixtime(submit_time),'%m-%d-%Y') as submit_day

			from
			    insp_v2.baftfeedback_feedback_submission_data as submission inner join
			    insp_v2.baftfeedback_feedback_submitter_data as submitter

			on
				submission.ref_baftfeedback_feedback_submission_id = @submission_id
				and submission.ref_baftfeedback_feedback_submitter_data_id = submitter.id
				and submitter.ref_baftfeedback_submission_id = submission.ref_baftfeedback_feedback_submission_id

			where
				and if( @submit_time=FALSE , {$dateCondition} , true)
				and if( @submitter=FALSE , submitter=@submitter , true )
				and if( @question=FALSE , ref_baftfeedback_question_id=@question , true )
			";

		$loadLastDataResult = $connection->query ( $selectQuery );

		return $loadLastDataResult->fetchAll ();

	}

	/**
	 * get submission data base on last state(current state)
	 *
	 * @param unknown $submissionEntity
	 * @param boolean $array
	 *        	return resultset as array or as resultset object
	 * @return ResultStatement|array
	 */
	public function getSubmissionLastData($submissionEntity, $format = self::DATA_AS_ARRAY) {

		/**
		 *
		 * @var \Doctrine\ORM\EntityManager $em
		 */
		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		$connection = $em->getConnection ();

		$loadLastData = "
				select
					submission.ref_baftfeedback_feedback_id as feedback_id,
					submission.id as submission_id,
					submission.submission_period as submission_period,
					submitter.id as submitter_id,
					submitter.submitter,
					submitter.submit_time,
					submission_data.ref_baftfeedback_question_group_id as question_group_id,
					submission_data.ref_baftfeedback_question_id as question_id,
					submission_data.question_field_name,
					submission_data.`value` as field_value,
					submission_data.version as submission_data_version,
					submitter.submitter_ip,
					state.state,
					state.json_state_data
				from
						baftfeedback_feedback_submission as submission
						JOIN (select * from baftfeedback_feedback_submission_state order by id desc) as state
						JOIN (select * from baftfeedback_feedback_submitter_data order by submit_time desc) AS submitter
						JOIN (select * from baftfeedback_feedback_submission_data order by ref_baftfeedback_feedback_submitter_data_id desc)AS submission_data
						on
							submission.ref_baftfeedback_feedback_id={$submissionEntity->getRefBaftfeedbackFeedback()->getId()} and
							submission.id='{$submissionEntity->getId()}' and
							state.ref_baftfeedback_feedback_submission_id=submission.id and
							submitter.ref_baftfeedback_submission_id=submission.id and
							submission_data.ref_baftfeedback_feedback_submitter_data_id=submitter.id

				GROUP BY
					feedback_id,
					submission_id,
					ref_baftfeedback_question_group_id,
					ref_baftfeedback_question_id,
					question_field_name
				order by
					submission_id
		";

		// 		print $loadLastData;die;

		if ($format == self::DATA_AS_ARRAY) {
			$loadLastDataResult = $connection->query ( $loadLastData );
			return $loadLastDataResult->fetchAll ();
		}


		$rsm = new ResultSetMapping ();

		$rsm->addEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', 'submission' );
		$rsm->addFieldResult ( 'submission', 'submission_id', 'id' );
		$rsm->addFieldResult ( 'submission', 'submission_period', 'submissionPeriod' );
		$rsm->addMetaResult ( 'submission', 'feedback_id', 'ref_baftfeedback_feedback_id', true ); // ($alias, $columnName, $fieldName)
		$rsm->addMetaResult ( 'submission', 'ref_feedback_version', 'ref_baftfeedback_feedback_version_id', true );

		$rsm->addJoinedEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionState', 'state', 'submission', 'state' );
		$rsm->addFieldResult ( 'state', 'state', 'state' );
		$rsm->addMetaResult ( 'state', 'ref_baftfeedback_feedback_submission_id', 'ref_baftfeedback_feedback_submission_id', true );

		$rsm->addJoinedEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData', 'submitter', 'submission', 'submitter' );
		$rsm->addFieldResult ( 'submitter', 'submitter_id', 'id' );
		$rsm->addFieldResult ( 'submitter', 'submitter', 'submitter' );
		$rsm->addFieldResult ( 'submitter', 'submit_time', 'submitTime' );
		$rsm->addMetaResult ( 'submitter', 'ref_baftfeedback_submission_id', 'ref_baftfeedback_submission_id', true );

		$rsm->addJoinedEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData', 'submission_data', 'submission', 'submissionData' );
		$rsm->addFieldResult ( 'submission_data', 'question_group_id', 'id' );
		$rsm->addFieldResult ( 'submission_data', 'question_id', 'refBaftfeedbackQuestion' );
		$rsm->addFieldResult ( 'submission_data', 'question_field_name', 'questionFieldName' );
		$rsm->addFieldResult ( 'submission_data', 'field_value', 'value' );
		$rsm->addFieldResult ( 'submission_data', 'submission_data_version', 'version' );
		$rsm->addMetaResult ( 'submission_data', 'ref_baftfeedback_feedback_submission_id', 'ref_baftfeedback_feedback_submission_id', true );
		$rsm->addMetaResult ( 'submission_data', 'ref_baftfeedback_feedback_submitter_data_id', 'ref_baftfeedback_feedback_submitter_data_id', true );


		$query = $em->createNativeQuery ( $loadLastData, $rsm );

		// \print_r((new \Doctrine\Common\Collections\ArrayCollection ( $query->getResult () ))->toArray());die;
		/**
		 *
		 * @var \Doctrine\Common\Collections\ArrayCollection $submissions
		 */
		return new \Doctrine\Common\Collections\ArrayCollection ( $query->getResult () );

	}

	/**
	 * calculate submission last data from records of submission_data table by php not sql
	 * read submission data records from submission->getSubmissionData()
	 *
	 * @param BaftfeedbackFeedbackSubmission $submission
	 *        	submission entity have to be update and contain records of submission data
	 */
	public function getSubmissionLastDataByPhp(BaftfeedbackFeedbackSubmission $submission) {

		$submissionData = $submission->getSubmissionData ();
		/*
		 submission.ref_baftfeedback_feedback_id as feedback_id,
		 submission.id as submission_id,
		 submission.submission_period as submission_period,
		 submitter.id as submitter_id,
		 submitter.submitter,
		 submitter.submit_time,
		 submission_data.ref_baftfeedback_question_group_id as question_group_id,
		 submission_data.ref_baftfeedback_question_id as question_id,
		 submission_data.question_field_name,
		 submission_data.`value` as field_value,
		 submission_data.version as submission_data_version,
		 submitter.submitter_ip,
		 state.state,
		 state.json_state_data
		 */

		$checkTempArray = [ ];
		foreach ( $submissionData as $record ) {
			/**
			 *
			 * @var BaftfeedbackFeedbackSubmissionData $dataRecord
			 */
			$dataRecord = $record;
			$submission_id = $submission->getId ();
			$question_group_id = $dataRecord->getRefBaftfeedbackQuestionGroup ()->getId ();
			$question_id = $dataRecord->getRefBaftfeedbackQuestion ()->getId ();
			$question_field_name = $dataRecord->getQuestionFieldName ();
			$field_value = $dataRecord->getValue ();
			$submitter = $dataRecord->getRefBaftfeedbackFeedbackSubmitterData ();
			$submitTime = $submitter->getSubmitTime ();

			if (! isset ( $checkTempArray [$question_group_id] ))
				$checkTempArray [$question_group_id] = [ ];
			if (! isset ( $checkTempArray [$question_group_id] [$question_id] ))
				$checkTempArray [$question_group_id] [$question_id] = [ ];

			$previousSubmitId = 0;
			if (isset ( $checkTempArray [$question_group_id] [$question_id] [$question_field_name] ))
				$previousSubmitId = $checkTempArray [$question_group_id] [$question_id] [$question_field_name] ["submitter_id"];

			//only save last value of field_name if submit id is greater
				if ($submitter->getId ()  > $previousSubmitId)
				$checkTempArray [$question_group_id] [$question_id] [$question_field_name] = [
						"feedback_id" => $submission->getRefBaftfeedbackFeedback ()->getId (),
						"submission_period" => $submission->getSubmissionPeriod (),
						"submission_id" => $submission_id,
						"question_group_id" => $question_group_id,
						"question_id" => $question_id,
						"question_field_name" => $question_field_name,
						"field_value" => $field_value,
						"submitter_id" => $submitter->getId (),
						"submitter" => $submitter->getSubmitter (),
						"submit_time" => $submitTime,
						"submitter_ip" => $submitter->getSubmitterIp ()

				];
		}

		return $checkTempArray;

	}


	/*
	 * (non-PHPdoc)
	 * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
	 */
	public function setEventManager(EventManagerInterface $eventManager) {

		$eventManager->setIdentifiers ( [
				__CLASS__,
				get_called_class ()
		] );
		$this->eventManager = $eventManager;
		return $this;

	}

	/**
	 * Retrieve the event manager
	 *
	 * Lazy-loads an EventManager instance if none registered.
	 *
	 * @return EventManagerInterface
	 */
	public function getEventManager() {

		return $this->eventManager;

	}

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

		$this->serviceLocator = $serviceLocator;

	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {

		return $this->serviceLocator;

	}


	/**
	 * get last submission of feedback with submitters on one subject or just last if subject be empty
	 *
	 * @param null|subjectEntity $subject
	 * @todo READ SUBJECt from enitty
	 * @todo submission condition is difference between feedabacks
	 *       get last submission of feedback wiht same subject
	 * @todo read feedback version from feedback entity , if number passed read latest version
	 * @todo read submission period
	 * @todo if feedback is a continous , just get one latest open submission in subject
	 * @todo if feedback has a submission in a subject with continous condition , reurn just it
	 * @param unknown $feedback
	 * @param unknown $subject
	 * @return null|integer
	 */
	public function __getLastSubmission($feedback, $subjectData, $feedbackVersion = null) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		$feedbackService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' );
		$feedback = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' )->find ( $feedback );

		$connection = $em->getConnection ();

		if (is_null ( $feedbackVersion ))
			$feedbackVersion = $feedbackService->getLastVersion ( $feedback->getId () );

		$fieldValueCondition = "";
		$fieldsAsColumns = "";
		foreach ( $subjectData as $fieldName => $value ) {
			$fieldsAsColumns .= " , max(if(question_field_name='{$fieldName}',`value`,null)) as `{$fieldName}` ";
			$fieldValueCondition .= " and subject_data.`{$fieldName}`='{$value}' ";
		}

		$lastSubmissionQuesry = "
            SELECT *
            FROM
            baftfeedback_feedback_submission as submission ,

            (SELECT
            id as subject_data_id ,
            ref_baftfeedback_submission_id ,
            ref_baftfeedback_subject_id
            {$fieldsAsColumns}
            FROM
            baftfeedback_feedback_subject_data
            group by ref_baftfeedback_submission_id , ref_baftfeedback_subject_id
            ) as subject_data

            where
            submission.id=subject_data.ref_baftfeedback_submission_id
            and submission.ref_baftfeedback_feedback_id='{$feedback->getId()}'
            and submission.ref_baftfeedback_feedback_version_id='{$feedbackVersion->getId()}'
            and ( true {$fieldValueCondition} )
            group by submission.ref_baftfeedback_feedback_id , submission.id
            order by id desc
            limit 1
            ";

		// $lastSubmissionQuesry="
		// select
		// submitter.id,
		// submitter.submitter,
		// submitter.start_time,
		// submitter.submitter_ip,
		// submitter.submit_time,
		// submission.id as submission_id,
		// submission.ref_baftfeedback_feedback_id as feedback_id,
		// submission.brcode as subject_brcode,
		// submission.brboss as subject_brboss
		// from
		// (select * from baftfeedback_feedback_submission where brcode='{$brcode}' and ref_baftfeedback_feedback_id='{$feedbackId}' ORDER BY id desc limit 1) as submission
		// JOIN (select * from baftfeedback_feedback_submitter_data order by submit_time desc) AS submitter
		// on
		// submitter.ref_baftfeedback_submission_id=submission.id
		// GROUP BY
		// submission_id,
		// submit_time,
		// submitter";

		$submissions = $connection->query ( $lastSubmissionQuesry )->fetchAll ();
		// $lastSubmissions=$this->getServiceLocator()->get('BaftFeedback\Model\feedback')->getLastSubmission($feedbackId,$subject);

		if (empty ( $submissions ))
			return null;

		// $currentMidYear=(date('n',time())>=6)?2:1;
		// $submisstionMidYear=(date('n',end($lastSubmissions)['submit_time'])>=6)?2:1;

		// not in same mid year
		// if($currentMidYear!=$submisstionMidYear)
		// return null;

		$lastSubmission = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' )->find ( current ( $submissions ) ['id'] );

		return $lastSubmission;

	}


	/**
	 *
	 * @param unknown $submissionEntity
	 * @param string $formBindObject
	 * @return ResultStatement|formBindInterface
	 */
	public function _submissionLastData($submissionEntity, $formBindObject = null) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		$connection = $em->getConnection ();

		$loadLastData = "
        select
        submitter.id,
        submitter.submitter,
        submitter.start_time,
        submitter.submitter_ip,
        submitter.submit_time,
        submission.id as submission_id,
        submission.ref_baftfeedback_feedback_id as feedback_id,
        submission_data.*
        from
        baftfeedback_feedback_submission as submission
        JOIN (select * from baftfeedback_feedback_submitter_data order by submit_time desc) AS submitter
        JOIN (select * from baftfeedback_feedback_submission_data order by ref_baftfeedback_feedback_submitter_data_id desc)AS submission_data
        on
        submission.id='{$submissionEntity->getId()}' and
        submission_data.ref_baftfeedback_feedback_submitter_data_id=submitter.id and
        submitter.ref_baftfeedback_submission_id=submission.id
        GROUP BY
        ref_baftfeedback_question_group_id,
        ref_baftfeedback_question_id,
        question_field_name
        ";

		$loadLastDataResult = $connection->query ( $loadLastData );

		$lastData = [ ];

		if (is_null ( $formBindObject )) {
			return $loadLastDataResult;
		}

		while ( $row = $loadLastDataResult->fetch () ) {

			if (! isset ( $lastData [$row ['ref_baftfeedback_question_group_id']] ))
				$lastData [$row ['ref_baftfeedback_question_group_id']] = [ ];

			if (! isset ( $lastData [$row ['ref_baftfeedback_question_group_id']] [$row ['ref_baftfeedback_question_id']] ))
				$lastData [$row ['ref_baftfeedback_question_group_id']] [$row ['ref_baftfeedback_question_id']] = [ ];

			if (! isset ( $lastData [$row ['ref_baftfeedback_question_group_id']] [$row ['ref_baftfeedback_question_id']] [$row ['question_field_name']] ))
				$lastData [$row ['ref_baftfeedback_question_group_id']] [$row ['ref_baftfeedback_question_id']] [$row ['question_field_name']] = '';

			$lastData [$row ['ref_baftfeedback_question_group_id']] [$row ['ref_baftfeedback_question_id']] [$row ['question_field_name']] = "{$row['value']}";
		}

		if (empty ( $lastData ))
			return [ ];

		// print_r($lastData);

		$formBindObject = Json::decode ( Json::encode ( $formBindObject ), Json::TYPE_ARRAY );
		foreach ( $formBindObject as $groupName => &$group ) {
			$groupId = $group ['id'];

			if (is_array ( $group ))
				foreach ( $group as $questionName => &$question ) {
					$questionId = $question ['id'];

					if (is_array ( $question ))
						foreach ( $question as $fieldName => &$fieldValue ) {
							// print_r([[$groupId],[$questionId],[$fieldName]]);

							if ($fieldName == 'id')
								continue;

							// check array depth to be exist
							if (isset ( $lastData [$groupId] ) && isset ( $lastData [$groupId] [$questionId] ) && isset ( $lastData [$groupId] [$questionId] [$fieldName] ))
								$fieldValue = $lastData [$groupId] [$questionId] [$fieldName];
						}
				}
		}

		return $formBindObject;

	}


}

?>
<?php

namespace BaftFeedback\Model;

use BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData;

class SubmissionDataRepository extends EntityRepository implements ServiceLocatorAwareInterface {
	public $serviceLocator;

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
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submissionEntity
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData $submitter
	 * @param array $data
	 * @throws \Exception
	 * @throws Exception
	 */
	public function create(\BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submissionEntity, $submitter, $data) {

		if (empty ( $data ))
			return $submissionEntity;

		$em = $this->getEntityManager ();
		$connection = $em->getConnection ();
		// $em->getConnection ()
		// ->getConfiguration ()
		// ->setSQLLogger ( new \Doctrine\DBAL\Logging\EchoSQLLogger () );


		/**
		 *
		 * @var BaftFeedback\Model\feedbackSubmissionInterface $submissionModel
		 */
		$submissionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\submission' );


		$submissionEntity = $submissionModel->find ( $submissionEntity );
		$feedbackVersion = $submissionEntity->getRefBaftfeedbackFeedbackVersion ();

		$connection->beginTransaction ();

		foreach ( $data as $row ) {

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
	 *
	 * @param int $submissionId
	 * @param int|array $byDate array of submit times in unixtime or (for int) just a unixtime to delegate a day (program read only "day" part of unixtime)
	 * @param int $bySubmitter
	 * @param int $byQuestion
	 */
	public function removeSubmissionData($submissionId,$byDate='FALSE',$bySubmitter='FALSE',$byQuestion='FALSE'){
		//for removing a submission completely refrencing to submissionRepository > removeSubmission

		$dateCondition=" DATE_FORMAT(from_unixtime(submit_time),'%m-%d-%Y')=DATE_FORMAT(from_unixtime(@submit_time),'%m-%d-%Y') ";
		if(is_array($byDate) ){
			$dateCondition=" ( ";
			foreach ($byDate as $submitTime){
				$dateCondition .= " submit_time={$submitTime} OR ";
			}
			$dateCondition .= " FALSE ) ";
		}

		$deleteQuery = "

		set @submission_id:={$submissionId};
		set @submit_time:={$byDate};
		set @submitter:={$bySubmitter};
		set @question:={$byQuestion};
		set @questionSubmitters:='';

		start transaction;

		-- submitters of question
		select ref_baftfeedback_feedback_submitter_data_id INTO @questionSubmitters
		from baftfeedback_feedback_submission_data
		where ref_baftfeedback_question_id=@question

		delete FROM
			insp_v2.baftfeedback_feedback_submission_data
			where
				ref_baftfeedback_feedback_submission_id=@submission_id and
				ref_baftfeedback_feedback_submitter_data_id in (@questionSubmitters
					SELECT id FROM insp_v2.baftfeedback_feedback_submitter_data
					where
						ref_baftfeedback_submission_id=@submission_id
						and if( @submit_time=FALSE , true , {$dateCondition}  )
						and if( @submitter=FALSE , true , submitter=@submitter ) and
						and if( @question=FALSE , true , ref_baftfeedback_question_id=@question ) and
					);

		delete FROM
			insp_v2.baftfeedback_feedback_submitter_data
			where
				ref_baftfeedback_submission_id=@submission_id and
				submitter=@submitter and submit_time<=@submit_time;

		commit;

		";

		//@TODO update state of submission base of changes in submission data (ex. when submission was completed ,so deleting some submission data make it incomplete)

	}


}


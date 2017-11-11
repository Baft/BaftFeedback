<?php

namespace BaftFeedback\Model;

use Zend\Json\Json;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Driver\ResultStatement;
// use BaftFeedback\Model\feedbackSubmission;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionState;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmission;
use Bundle\jdf;
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

	public function __create($submission, $submitter, $data) {

		$connection = $this->getConnection ();
		
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
		$subjectService = $this->getServiceLocator ()->get ( 'BaftFeedback\Service\subject' );
		$statesEnum = $this->getServiceLocator ()->get ( "statesEnum" );
		
		$submissionEntity = $submissionModel->find ( $submissionEntity );
		
		$feedbackEntity = $submissionEntity->getRefBaftfeedbackFeedback ();
		
		$feedbackVersion = $feedbackService->getLastVersion ( $feedbackEntity );
		
		$connection->beginTransaction ();
		
		try {
			
			// we need current state later , so keep it before change it
			$currentSubmissionState = $submissionModel->getState ( $submissionEntity->getId () )->getState ();
			
			foreach ( $data as $record ) {
				$submissionDataEntity = new BaftfeedbackFeedbackSubmissionData ();
				$submissionDataEntity->setRefBaftfeedbackSubmissionId ( $submission );
				$submissionDataEntity->set;
				$submissionDataEntity->set;
			}
			// save submission subject
			// $subjectService->saveData ( $submissionEntity, $subjectData );
			
			// iterate over post data and save them for submission
			foreach ( $feedbackData as $groupName => $group ) {
				$groupId = $group ['id'];
				unset ( $group ['id'] );
				
				foreach ( $group as $questionName => $question ) {
					$questionId = $question ['id'];
					unset ( $question ['id'] );
					
					foreach ( $question as $fieldName => $fieldValue ) {
						
						// @TODO move this to another place this just work for arzi checkList ###########################
						
						// prevent save rdo0=null just to avoid bootless data AND with all type of clients
						if (strcasecmp ( $fieldName, 'rdo0' ) == 0 && empty ( $fieldValue ))
							continue;
							
							// prevent save desc0=null just to avoid bootless data
						if (strcasecmp ( $fieldName, 'desc0' ) == 0 && empty ( $fieldValue ))
							continue;
							
							// filter when state is $statesEnum::SAR_CONFIRM before save
						if ($currentSubmissionState === $statesEnum::SAR_CONFIRM) {
							
							// prevent save desc0
							if (strcasecmp ( $fieldName, 'rdo0' ) != 0)
								continue;
								
								// just save new answers if it is resolved rdo0=ans2, so prevent save rdo0=ans0 and rdo0=ans1 and rdo0=ans3
							if (strcasecmp ( $fieldValue, 'ans2' ) != 0)
								continue;
						}
						
						// do not change form subject on edit (when state exist so we are in edit mode)
						if ($fieldName == 'brcode' && $currentSubmissionState !== false)
							$fieldValue = $submissionEntity->getBrcode ();
							
							// ######################################################################################################
						
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
                                    ( '{$submissionEntity->getId()}', '{$submitter}', '{$groupId}', '{$questionId}', '{$fieldName}', '{$fieldValue}' , '{$feedbackVersion->getId()}' )";
						
						if (! $connection->query ( $insertData ))
							throw new \Exception ( 'can not register feedback data to db' );
					}
				}
			}
			
			$connection->commit ();
			
			// reload object
			$submissionEntity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', $submissionEntity->getId () );
			
			return $submissionEntity;
		}
		catch ( Exception $e ) {
			$connection->rollBack ();
			throw $e;
		}
		
		return false;
	
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
		
		start transaction;		
		
		delete FROM 
			insp_v2.baftfeedback_feedback_submission_data 
			where 
				ref_baftfeedback_feedback_submission_id=@submission_id and 
				ref_baftfeedback_feedback_submitter_data_id in (
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


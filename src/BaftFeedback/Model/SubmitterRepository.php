<?php

namespace BaftFeedback\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Driver\ResultStatement;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SubmitterRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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
	 * register submitter for a submission
	 * 
	 * @param int $submissionId        	
	 * @param array|\BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData $submitter        	
	 * @throws \Exception
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData|int
	 */
	public function create($submission, $submitter) {

		$em = $this->getEntityManager ();
		
		if (is_array ( $submitter )) {
			$submitterEntity = new BaftfeedbackFeedbackSubmitterData ();
			$submitterEntity->setSubmitter ( $submitter ['submitter'] );
			$submitterEntity->setSubmitterIp ( $submitter ['submitter_ip'] );
			$submitterEntity->setSubmitTime ( $submitter ['submit_time'] );
			$submitterEntity->setStartTime ( $submitter ['start_time'] );
			$submitterEntity->setRefBaftfeedbackSubmissionId ( $submission );
			$submitter = $submitterEntity;
		}
		
		if (! $submitter instanceof BaftfeedbackFeedbackSubmitterData) {
			throw new \Exception ( 'submitter detail have to be array or instance of feedbackSubmitter on registering submitter' );
		}
		
		try {
			$em->persist ( $submitter );
			$em->flush ( $submitter );
		}
		catch ( \Exception $ex ) {
			return false;
		}
		
		return $submitter;
		
		// $insertSubmitter = "
		// INSERT INTO `baftfeedback_feedback_submitter_data`
		// (`submitter`, `submitter_ip`, `submit_time`, `start_time`, `ref_baftfeedback_submission_id`)
		// VALUES
		// ('{$submitter['submitter']}', '{$submitter['submitter_ip']}', '{$submitter['submit_time']}', '{$submitter['start_time']}', '{$submissionId}');";
	}

	public function replaceSubmitter($submitter, $submission_id) {

		/**
		 * replace all recorde with submitter=$submitter in all submissions with latest previously submitter in same submission
		 * 
		 * @var unknown
		 */
		$query = "
	        set @submitter:={$submitter};
	        set @submission_id:={$submission_id};
            update
            insp_v2.baftfeedback_feedback_submitter_data as orgin join
            (select a.* 
            	from (select * from insp_v2.baftfeedback_feedback_submitter_data 
            		where submitter <> @submitter 
            		order by ref_baftfeedback_submission_id , submit_time desc ) as a  
            	group by a.ref_baftfeedback_submission_id 
            )as other
            set 
                orgin.submitter=other.submitter
            where 
            -- other.ref_baftfeedback_submission_id=@submission_id and 
            other.ref_baftfeedback_submission_id=orgin.ref_baftfeedback_submission_id 
            and orgin.submitter=@submitter
            ;
	        ";
	
	}
	
	public function removeSubmitter($submitter){
		// ref to submissionDataRepository > removeSubmissionData  (remove by submitter)
	}


}
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

class SubmissionRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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
	 * convert id to entity
	 *
	 * @param
	 *        	int | \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $entity
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission | boolean
	 */
	public function find($entity) {

		$em = $this->getEntityManager ();

		if (is_numeric ( $entity ))
			$entity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', $entity );

		// if not found submission
		if (! ($entity instanceof BaftfeedbackFeedbackSubmission))
			return null;

		return $entity;

	}

	/**
	 * find submissions of feedback , base of other optional parameters as filter
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $feedback
	 * @param array $timeSpan
	 * @param array $subject
	 * @param int $submitter
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function findFilter(\BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity,
	        \BaftFeedback\Entity\BaftfeedbackFeedbackVersion $feedbackversion=null,
	        $periodId=null ,
	        \BaftFeedback\Entity\BaftfeedbackFeedbackSubject $subject =null,
	        array $subjectData = [],
	        array $timeSpan = [],
	        $submitter = null,
			array $states=[]) {

	    $em = $this->getEntityManager();

	    if(!isset($feedbackversion))
	       $feedbackversion = $feedbackEntity->getVersions ()->last();

	       $feedbackVersionFilter=" submission.ref_baftfeedback_feedback_version_id={$feedbackversion->getId()} ";

	    $periodFilter='';
	    if(isset($periodId))
	        $periodFilter=" and submission.submission_period='{$periodId}' ";

	    // submissions that created in this time span
	    $timefilter = '';
	    if (isset ( $timeSpan ['start'] ) && isset ( $timeSpan ['end'] )) {
	        $timefilter = " and start_time >= '{$timeSpan['start']}' ";
	        $timefilter = " and start_time < '{$timeSpan['end']}' ";
	    }

	    $subjectDatafilter = '';
	    $subjectfieldsascolumns = '';
	    if (! empty ( $subjectData )) {

	        foreach ( $subjectData as $fieldname => $value ) {

	            if ($value instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData) {
	                $fieldname = $value->getFieldName ();
	                $value = $value->getValue ();
	            }

	            if (empty ( $fieldname ))
	                throw new \Exception ( "filed name have to be not empty." );

	                $subjectfieldsascolumns .= " , max(if(field_name='{$fieldname}',`value`,null)) as `{$fieldname}` ";
	                if (is_array ( $value )) {
	                    $subjectDatafilter .= " and ( false ";
	                    foreach ( $value as $val )
	                        $subjectDatafilter .= " or `{$fieldname}`='{$val}' ";
	                        $subjectDatafilter .= " )";
	                } else
	                    $subjectDatafilter .= " and `{$fieldname}`='{$value}' ";
	        }


	    }

	    $subjectFilter='';
	    if($subject){
	        $subjectFilter=" and ref_baftfeedback_subject_id={$subject->getId()} ";
	    }

	    $subjectSelect='';
	    if($subjectFilter || $subjectDatafilter){
	        $subjectSelect="select
    	        id as subject_data_id ,
    	        ref_baftfeedback_submission_id ,
    	        ref_baftfeedback_subject_id
    	        {$subjectfieldsascolumns}
    	        from
    	        baftfeedback_feedback_subject_data
    	        where true {$subjectFilter}
    	        group by ref_baftfeedback_submission_id , ref_baftfeedback_subject_id
    	        having 1=1 {$subjectDatafilter}
    	        ";
	    }

	    $submitterfilter = '';
	    if (! empty ( $submitter )) {
	        $submitterfilter = " and submission.id=submitters.ref_baftfeedback_submission_id ";
	        $submitterfilter .= " and submitters.submitter='{$submitter}' ";
	    }


	    if(!empty($states)){
	    	foreach ($states as $state){

	    	}
	    }

	    $query = "
          select
                submission.ref_baftfeedback_feedback_id as feedback_id,
                submission.id as submission_id,
                submission.ref_baftfeedback_feedback_version_id as ref_feedback_version,
                submission.expire_time,
                submission.start_time ,
                submission.submission_period " .
                ((! empty ( $subjectDatafilter )) ? " , subject_data.* " : "") .
                " from
				    baftfeedback_feedback_submission as submission " .
				    ((! empty ( $subjectDatafilter )) ? " , ( {$subjectSelect} ) as subject_data " : "") .
				    ((! empty ( $submitterfilter )) ? " , baftfeedback_feedback_submitter_data as submitters" : "") .
				" where
				    submission.ref_baftfeedback_feedback_id='{$feedbackEntity->getid()}'
				    and {$feedbackVersionFilter} " .
				    ((! empty ( $subjectDatafilter )) ? " and submission.id=subject_data.ref_baftfeedback_submission_id  " : "") .
				    $timefilter .
				    $submitterfilter .
				    $periodFilter .
				" group by submission.ref_baftfeedback_feedback_id , submission.id " .
                " order by id asc "
                    ;

		        // @TODO implement doctrine "SqlResultSetMappings" to return entity collection instead of array

		         //print_r($query);die;


		        $rsm = new ResultSetMapping ();

		        $rsm->addEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', 'submission' );
		        $rsm->addFieldResult ( 'submission', 'submission_id', 'id' );
		        $rsm->addFieldResult ( 'submission', 'expire_time', 'expireTime' );
		        $rsm->addFieldResult ( 'submission', 'start_time', 'startTime' );
		        $rsm->addFieldResult ( 'submission', 'submission_period', 'submissionPeriod' );
		        $rsm->addMetaResult ( 'submission', 'feedback_id', 'ref_baftfeedback_feedback_id', true ); // ($alias, $columnName, $fieldName)
		        $rsm->addMetaResult ( 'submission', 'ref_feedback_version', 'ref_baftfeedback_feedback_version_id', true );

		        if (! empty ( $subjectDatafilter )) {
		            $rsm->addJoinedEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData', 'subject_data', 'submission', 'subjectData' );
		            $rsm->addFieldResult ( 'subject_data', 'subject_data_id', 'id' );
		            $rsm->addMetaResult ( 'subject_data', 'ref_baftfeedback_submission_id', 'ref_baftfeedback_submission_id', true );
		            $rsm->addMetaResult ( 'subject_data', 'ref_baftfeedback_subject_id', 'ref_baftfeedback_subject_id', true );
		        }

		        if (! empty ( $submitterfilter )) {
		            $rsm->addJoinedEntityResult ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData', 'submitters', 'submission', 'submitters' );
		        }

		        $query = $em->createNativeQuery ( $query, $rsm );


		        /**
		         *
		         * @var \Doctrine\Common\Collections\ArrayCollection $submissions
		         */
		        $submissions = new \Doctrine\Common\Collections\ArrayCollection ( $query->getResult () );

		        return $submissions;

        }

	/**
	 * find all submission entity in subject ,
	 * 	for specific feedback
	 * 	for specific feedback period if period defined
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubject $subject
	 * @param array $subjectData
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity
	 * @param int $periodId
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
        public function findBySubject(\BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity,\BaftFeedback\Entity\BaftfeedbackFeedbackSubject $subject ,array $subjectData ,$periodId=null){
            return $this->findFilter($feedbackEntity,null,$periodId, $subject,$subjectData);
        }


        public function findByState($state,\BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity=null){}


	/**
	 * get Aggregated submitters of a submission .
	 *
	 *
	 *
	 * (distinct on submitterId)
	 * sort by date ASC
	 *
	 * @param int|BaftfeedbackFeedbackSubmission $submissionEntity
	 * @param bool $distinct
	 *        	distinct of submitters with lastest submit time
	 */
	public function getSubmitters($submissionEntity, $distinct = false) {

		$em = $this->getEntityManager ();

		if (is_numeric ( $submissionEntity ))
			$submissionEntity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', $submissionEntity );

		// if not found submission
		if (! $submissionEntity)
			return [ ];

		$submitters = $submissionEntity->getSubmitters ();

		if ($submitters->count () == 0)
			return [ ];

		if (! $distinct)
			return $submitters;

		$distinctSubmiters = new ArrayCollection ();

		foreach ( $submitters as $submitter ) {

			// distinction operation
			if ($distinctSubmiters->containsKey ( $submitter->getSubmitter () )) {
				$oldSubmitTime = $distinctSubmiters->get ( $submitter->getSubmitter () )->getSubmitTime ();
				$newSubmitTime = $submitter->getSubmitTime ();

				if ($oldSubmitTime < $newSubmitTime)
					$distinctSubmiters->set ( $submitter->getSubmitter (), $submitter );

				continue;
			}
			$distinctSubmiters->set ( $submitter->getSubmitter (), $submitter );
		}

		// sorting arrayCollection
		$iterator = $distinctSubmiters->getIterator ();
		$iterator->uasort ( function ($a, $b) {
			return ($a->getSubmitTime () < $b->getSubmitTime ()) ? - 1 : 1;
		} );

		$distinctSubmiters = new ArrayCollection ( iterator_to_array ( $iterator ) );

		return $distinctSubmiters;

	}

	/**
	 *
	 * count questtions that has answer in a submission until now
	 *
	 * @param unknown $submissionId
	 * @return number
	 */
	public function countAnswers($submissionId) {

		$connection = $this->getEntityManager ()->getConnection ();

		$answwersList = "
			SELECT
				submission_data.*
			FROM
				baftfeedback_feedback_submission_data AS submission_data
			WHERE
				submission_data.ref_baftfeedback_feedback_submission_id = '{$submissionId}'
			GROUP BY
				ref_baftfeedback_question_group_id,
				ref_baftfeedback_question_id;
				";

		return $connection->query ( $answwersList )->rowCount ();

	}

	/**
	 * calculate if submission is expired
	 *
	 * @param
	 *        	\BaftFeedback\Entity\BaftfeedbackFeedbackSubmission | int $submissionEntity
	 */
	public function isExpired($submissionEntity) {

		if (! $submissionEntity = $this->find ( $submissionEntity ))
			return true;

		if ($submissionEntity->getExpireTime () >= time ())
			return false;

		return true;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity
	 * @param unknown $feedbackVersion
	 * @param unknown $expireTime
	 * @param unknown $startTime
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function create($feedbackEntity, $feedbackVersion, $expireTime, $startTime ,$periodId) {

		$submissionEntity = new BaftfeedbackFeedbackSubmission ();
		$submissionEntity->setRefBaftfeedbackFeedback ( $feedbackEntity )
			->setRefBaftfeedbackFeedbackVersion ( $feedbackVersion )
			->setExpireTime ( $expireTime )
			->setStartTime ( $startTime )
			->setEditable ( $feedbackEntity->getSubmissionEditable () )
		    ->setSubmissionPeriod($periodId);

		return $this->save( $submissionEntity );

	}

	public function save($entity) {

		$em = $this->getEntityManager ();
		$em->persist ( $entity );
		$em->flush ( $entity );

		return $entity;
	}

	/**
	 * delete submission with all dependant entities
	 */
	public function removeSubmission($submissionId) {

		$deleteQuery = "

	    set @submission_id:={$submissionId};
	    start transaction;

	    delete FROM baftfeedback_feedback_subject_data where ref_baftfeedback_submission_id=@submission_id;

	    delete FROM baftfeedback_feedback_submission_state where ref_baftfeedback_feedback_submission_id=@submission_id;

	    delete FROM baftfeedback_feedback_submission_data where ref_baftfeedback_feedback_submission_id=@submission_id;

	    delete FROM baftfeedback_feedback_submitter_data where ref_baftfeedback_submission_id=@submission_id;

	    delete FROM baftfeedback_feedback_submission where id=@submission_id;

	    commit;

	    ";

	}

	/**
	 * set/reset state of feedback submission
	 *
	 * @see \baftFeedback\Model\feedbackSubmission::updateState()
	 */
	public function createState($submission, $state = 0, $data = []) {

		$submissionEntity = $this->find ( $submission );

		$data = Json::encode ( $data );

		$submissionStateEntity = new BaftfeedbackFeedbackSubmissionState ();
		$submissionStateEntity->setState ( $state );
		$submissionStateEntity->setVersion ( time () );
		$submissionStateEntity->setJsonStateData ( $data );
		$submissionStateEntity->setRefBaftfeedbackFeedbackSubmissionId ( $submissionEntity );
		$submissionStateEntity->setChangeTime ( time () );

		$this->save( $submissionStateEntity );

		return $submissionStateEntity;

	}

	public function updateState($submission, $state, $data = []) {

		return $this->createState ( $submission, $state, $data );

	}

	public function getStates($submission) {

		$submission= $this->find ( $submission );
		return $submission->getStates();

	}

	/**
	 * return state number or false if submissionId not exist
	 *
	 * @param unknown $submissionEntity
	 * @return false|integer statecode
	 */
	public function currentState($submissionEntity) {

		if (! $submissionEntity = $this->find ( $submissionEntity ))
			return false;
		if (! $submissionEntity->getStates ()->count () || is_null ( $submissionEntity ))
			return false;

		return $submissionEntity->getStates ()->last ();

	}

	/**
	 * is submission has this state
	 *
	 * @param unknown $submissionEntity
	 * @param int|string $state
	 */
	public function hasState($submissionEntity, $state) {

		if (! $submissionEntity = $this->find ( $submissionEntity ))
			return false;

		$states = $submissionEntity->getStates ();
		if (! $states->count () || is_null ( $submissionEntity ))
			return false;

		foreach ( $states as $st ) {
			if ($st->getState () == $state)
				return $st;
		}

		return false;

	}


}
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

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		if (is_numeric ( $entity ))
			$entity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', $entity );

			// if not found submission
		if (! ($entity instanceof BaftfeedbackFeedbackSubmission))
			return null;

		return $entity;

	}

	/**
	 * find open submission
	 * 	- Continuous =1
	 * 	- status dose not closed
	 * 	- dose not expired
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity
	 */
	public function findContinuous($feedbackEntity){
		$connection = $this->getEntityManager ()->getConnection ();

		$sql="select *
		form baftfeedback_feedback_submission
		where
		ref_baftfeedback_feedback_id={$feedbackEntity->getId()} and
		Continuous = 1
		";

		return $connection->query ( $sql );




	}

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
	 * if entity pass as param then insert , then return persited object
	 * if array padd as param then insert and return inerted id
	 *
	 * @param array|BaftfeedbackFeedbackSubmission $submissinEntity
	 * @throws \Exception
	 * @return int
	 */
	public function create($feedbackEntity, $feedbackVersion, $expireTime, $startTime = null) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		if (is_null ( $startTime ))
			$startTime = time ();

		$submissionEntity = new BaftfeedbackFeedbackSubmission ();
		$submissionEntity->setRefBaftfeedbackFeedback ( $feedbackEntity )
			->setRefBaftfeedbackFeedbackVersion ( $feedbackVersion )
			->setExpireTime ( $expireTime )
			->setContinuous ( $feedbackEntity->getContinuous () )
			->setStartTime ( $startTime )
			->setEditable($feedbackEntity->getEditable());

		$em->persist ( $submissionEntity );
		$em->flush ( $submissionEntity );

		return $submissionEntity;

	}

	/**
	 * delete submission with all dependant entities
	 */
	public function removeSubmission($submissionId) {

		$deleteQuery = "

	    set @submission_id:={$submissionId};
	    start transaction;

	    delete FROM insp_v2.baftfeedback_feedback_subject_data where ref_baftfeedback_submission_id=@submission_id;

	    delete FROM insp_v2.baftfeedback_feedback_submission_state where ref_baftfeedback_feedback_submission_id=@submission_id;

	    delete FROM insp_v2.baftfeedback_feedback_submission_data where ref_baftfeedback_feedback_submission_id=@submission_id;

	    delete FROM insp_v2.baftfeedback_feedback_submitter_data where ref_baftfeedback_submission_id=@submission_id;

	    delete FROM insp_v2.baftfeedback_feedback_submission where id=@submission_id;

	    commit;

	    ";

	}

	/**
	 * set/reset state of feedback submission
	 *
	 * @see \baftFeedback\Model\feedbackSubmission::updateState()
	 */
	public function createState($submission, $state = 0, $data = []) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		$submissionEntity = $this->find ( $submission );

		$data = Json::encode ( $data );

		$submissionStateEntity = new BaftfeedbackFeedbackSubmissionState ();
		$submissionStateEntity->setState ( $state );
		$submissionStateEntity->setVersion ( time () );
		$submissionStateEntity->setJsonStateData ( $data );
		$submissionStateEntity->setRefBaftfeedbackFeedbackSubmissionId ( $submissionEntity );
		$submissionStateEntity->setChangeTime ( time () );

		$em->persist ( $submissionStateEntity );
		$em->flush ();

		return $submissionStateEntity;

	}

	public function updateState($submission, $state , $data = []){
		return $this->createState($submission, $state, $data);
	}

	public function getState($submissionId, $state) {

		return $this->hasState ( $submissionId, $state );

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
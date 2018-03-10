<?php

namespace BaftFeedback\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback\Entity\BaftfeedbackFeedbackVersion;
use Doctrine\ORM\EntityRepository;
use BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions;
use BaftFeedback\Entity\BaftfeedbackQuestion;
use BaftFeedback\Entity\BaftfeedbackQuestionGroup;
use Zend\Stdlib\Hydrator\ObjectProperty;

class FeedbackQuestionRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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
	 * find one version by its reference
	 * 
	 * @param int|\BaftFeedback\Model\BaftfeedbackFeedbackVersion $entity        	
	 * @return boolean|\BaftFeedback\Model\BaftfeedbackFeedbackVersion
	 */
	public function find($entity) {

		$em = $this->getEntityManager ();
		
		if (is_numeric ( $entity ))
			$entity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions', $entity );
			
			// if not found submission
		if (! ($entity instanceof BaftfeedbackQuestionGroupQuestions))
			return null;
		
		return $entity;
	
	}

	/**
	 *
	 * @param
	 *        	array | BaftfeedbackQuestionGroupQuestion $feedbackQuestion
	 * @param
	 *        	null | BaftfeedbackQuestion $question
	 * @param
	 *        	null | BaftfeedbackQuestionGroup $group
	 */
	public function create($feedbackQuestion, BaftfeedbackQuestion $question = null, BaftfeedbackQuestionGroup $group = null) {

		$em = $this->getEntityManager ();
		
		$feedbackQuestionEntity = null;
		
		// try to map passed array to object entity
		if (is_array ( $feedbackQuestion )) {
			$feedbackQuestionEntity = new BaftfeedbackQuestionGroupQuestions ();
			$hydrator = new ObjectProperty ();
			$feedbackQuestionEntity = $hydrator->hydrate ( $feedbackQuestion, $feedbackQuestionEntity );
		}
		
		if ($feedbackQuestion instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions)
			$feedbackQuestionEntity = $feedbackQuestion;
		
		if (! $feedbackQuestionEntity instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions)
			throw new \Exception ( __METHOD__ . ' : question entity dose not define ' );
			
			// try to find question entity
		$questionEntity = $feedbackQuestionEntity->getRefBaftfeedbackQuestion ();
		if (! isset ( $questionEntity ) && ! isset ( $question ))
			throw new \Exception ( __METHOD__ . ' : question structure dose not define ' );
		if (isset ( $question ))
			$questionEntity = $question;
		$question = $questionEntity;
		
		// try to find question group entity
		$groupEntity = $feedbackQuestionEntity->getRefBaftfeedbackQuestionGroup ();
		if (! isset ( $groupEntity ) && ! isset ( $group ))
			throw new \Exception ( __METHOD__ . ' : feedback question group dose not define ' );
		if (isset ( $group ))
			$groupEntity = $group;
		$group = $groupEntity;
		
		$feedbackQuestionEntity->setRefBaftfeedbackQuestion ( $questionEntity );
		$feedbackQuestionEntity->setRefBaftfeedbackQuestionGroup ( $groupEntity );
		
		try {
			$em->persist ( $feedbackQuestionEntity );
			$em->flush ( $feedbackQuestionEntity );
		}
		catch ( \Exception $ex ) {
			return null;
		}
		
		return $feedbackQuestionEntity;
	
	}

	public function update($feedbackQuestionEntity) {

		$em = $this->getEntityManager ();
		try {
			$em->persist ( $feedbackQuestionEntity );
			$em->flush ( $feedbackQuestionEntity );
		}
		catch ( \Exception $ex ) {
			return null;
		}
		
		return $feedbackQuestionEntity;
	
	}

	public function injectQuestionData() {

		$insertDataQuery = "
	
            -- insert an answer for a question field of question , just in all submissions previously created . (dose not create submission nor submitter)
            set @submission_id:=50;
            set @ref_submittrdata_id:=1033;
            set @question_group:=119;
            set @question_id:=50;
            set @question_fieldname:='rdo0';
            set @question_fieldvalue:='ans1';
	
	
			insert into
			`baftfeedback_feedback_submission_data`
			(
				`ref_baftfeedback_feedback_submission_id`,
				`ref_baftfeedback_feedback_submitter_data_id`,
				`ref_baftfeedback_question_group_id`,
				`ref_baftfeedback_question_id`,
				`question_field_name`,
				`value`
			)
			select
                baftfeedback_feedback_submission.id ,
                baftfeedback_feedback_submitter_data.id ,
                @question_group ,
                @question_id,
                @question_fieldname ,
                @question_fieldvalue
            from
                baftfeedback_feedback_submission,
                baftfeedback_feedback_submitter_data
			where
                -- baftfeedback_feedback_submission.id = @submission_id and
                baftfeedback_feedback_submission.id=baftfeedback_feedback_submitter_data.ref_baftfeedback_submission_id and
			    baftfeedback_feedback_submitter_data.submitter=@ref_submittrdata_id
				;
	
	
            ";
	
	}


}
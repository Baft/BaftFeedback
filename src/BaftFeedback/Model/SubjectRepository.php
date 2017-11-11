<?php

namespace BaftFeedback\Model;


// use BaftFeedback\Model\feedbackSubmission;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmission;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubject;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback;

class SubjectRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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
	 *        	int | \BaftFeedback\Entity\BaftfeedbackFeedbackSubject $entity
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject | boolean
	 */
	public function find($entity) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		
		if (is_numeric ( $entity ))
			$entity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubject', $entity );
			
			// if not found submission
		if (! ($entity instanceof BaftfeedbackFeedbackSubject))
			return null;
		
		return $entity;
	
	}

	/**
	 * find subject of feedback
	 *
	 * @param int $submissionId        	
	 */
	public function findByFeedback(\BaftFeedback\Entity\BaftfeedbackFeedback $feedback) {

		return $feedback->getSubject ();
	
	}

	/**
	 * create subject
	 * if entity passed ,insert and return persited object
	 * if array padded , insert and return inerted id
	 *
	 * @param array|BaftfeedbackFeedbackSubject $submissinEntity        	
	 * @throws \Exception
	 * @return int|BaftfeedbackFeedbackSubject
	 */
	public function create($subjectEntity) {

		$em = $this->getEntityManager ();
		
		$connection = $em->getConnection ();
		
		if ($subjectEntity instanceof BaftfeedbackFeedbackSubject) {
			$em->persist ( $subjectEntity );
			$em->flush ( $subjectEntity );
			return $subjectEntity;
		}
		
		if (! is_array ( $subjectEntity ))
			throw new \Exception ( 'submission detail have to be array or instance of feedbackSubmission on creating submission' );
		
		if (! isset ( $subjectEntity ['name'] ))
			$subjectEntity ['name'] = '';
		
		if (! isset ( $subjectEntity ['label'] ))
			$subjectEntity ['label'] = '';
			
			// @TODO create input filter for subject data
		
		$insertSubmission = "
		INSERT INTO `baftfeedback_feedback_subject`
		(`ref_baftfeedback_question_id` , `ref_baftfeedback_feedback_id` , `question_order` , `name` , label )
		VALUES
		('{$subjectEntity['question_id']}', '{$subjectEntity['feedback_id']}' , '{$subjectEntity['question_order']}' , '{$subjectEntity['name']}' ,'{$subjectEntity['label']}' )";
		
		if (! $connection->query ( $insertSubmission ))
			throw new \Exception ( 'can not create feedbcak submission' );
			
			// return $em->find('BaftFeedback\Entity\BaftfeedbackFeedbackSubmission', $connection->lastInsertId());
		return $connection->lastInsertId ();
	
	}

	public function saveSubjectData() {

	}

	public function findSubjectData() {

	}


}
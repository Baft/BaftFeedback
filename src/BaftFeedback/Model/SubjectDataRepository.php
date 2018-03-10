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
use jdf;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubject;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Collections\Criteria;

class SubjectDataRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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
	 * find subject Data of submission
	 *
	 * @param int $submissionId
	 */
	public function findBySubmission($submissionId) {

		return $this->getEntityManager ()->getRepository ( 'BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData' )->findBy ( [
				'refBaftfeedbackSubmission' => $submissionId
		] );

	}

	/**
	 * datasets with this $subjectData
	 * @param unknown $subjectId
	 * @param array $subjectData
	 */
	public function findBySubjectData($subjectId,array $subjectData){

		$dql=$this->createQueryBuilder("findBySubjectData")
		->select('subjectdata')
		->from($this->getEntityName(), 'subjectdata');

		$conditions=$dql->expr();
		foreach ($subjectData as $fieldName => $value){
			$fieldCondition=$dql->expr()->eq('subjectdata.fieldName', $fieldName);
			$valueCondition=$dql->expr()->in('subjectdata.value', $value);
			$conditions->andX($dql->expr()->orX($fieldCondition,$valueCondition));
		}
		$conditions->andX($dql->expr()->eq('subjectdata.refBaftfeedbackSubject', $subjectId));

		$dql->where($conditions);

		var_dump($dql->getQuery());die;
	}

	/**
	 * create subject
	 * if entity pass as param then insert , then return persited object
	 * if array padd as param then insert and return inerted id
	 *
	 * @param array|BaftfeedbackFeedbackSubject $submissinEntity
	 * @throws \Exception
	 * @return int|BaftfeedbackFeedbackSubject
	 */
	public function create($feedback, $submission, $subject, $subjectData) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );

		// $connection=$em->getConnection();

		$subjectDataEntityCollection = new ArrayCollection ();

		foreach ( $subjectData as $fieldName => $fieldValue ) {

			if ($fieldValue instanceof BaftfeedbackFeedbackSubjectData) {
				$em->persist ( $fieldValue );
				$em->flush ( $fieldValue );
				continue;
			}

			if (! is_string ( $fieldName ) || ! is_scalar ( $fieldValue ))
				return new \Exception ( __METHOD__ . ' subject data dose not match requirement. [string field name] => [scalar value]' );

			$subjectDataEntity = new BaftfeedbackFeedbackSubjectData ();
			$subjectDataEntity->setFieldName ( $fieldName );
			$subjectDataEntity->setValue ( $fieldValue );
			$subjectDataEntity->setRefBaftfeedbackFeedback ( $feedback );
			$subjectDataEntity->setRefBaftfeedbackSubmission ( $submission );
			$subjectDataEntity->setRefBaftfeedbackSubject ( $subject );
			// var_dump($subjectDataEntity);
			$em->persist ( $subjectDataEntity );

			$subjectDataEntityCollection->add ( $subjectDataEntity );

			// $insertSubmission="
			// INSERT INTO `baftfeedback_feedback_subject_data`
			// ( `ref_baftfeedback_feedback_id` , `ref_baftfeedback_submission_id` , `ref_baftfeedback_subject_id` , `field_name` , `value` )
			// VALUES
			// ('{$insertRow['ref_baftfeedback_feedback_id']}', '{$insertRow['ref_baftfeedback_submission_id']}' , '{$insertRow['ref_baftfeedback_subject_id']}' , '{$insertRow['field_name']}' ,'{$insertRow['value']}' )";

			// if(!$connection->query($insertSubmission))
			// throw new \Exception('can not save subject data');
		}

		try {
			$em->flush ();
		}
		catch ( \Exception $ex ) {
			return $ex;
		}

		return $subjectDataEntityCollection;

	}

	public function saveSubjectData() {

	}

	public function findSubjectData() {

	}


}
<?php

namespace BaftFeedback\Model;

use BaftFeedback\Entity\BaftfeedbackFeedback;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class feedbackRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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

		if (is_numeric ( $entity ))
			$entity = $this->getEntityManager ()->find ( 'BaftFeedback\Entity\BaftfeedbackFeedback', $entity );

			// if not found submission
		if (! ($entity instanceof BaftfeedbackFeedback)) {
			throw new \Exception ( "feedback dose not found ." );
			return null;
		}

		return $entity;

	}

	/**
	 *
	 * @param unknown $entity
	 * @param unknown $feedbackGroup
	 * @return unknown
	 */
	public function create($entity, $feedbackGroup) {

		$em = $this->getEntityManager ();

		$entity->setRefBaftfeedbackQuestionGroupId ( $feedbackGroup );

		$em->persist ( $entity );
		$em->flush ( $entity );

		return $entity;

	}

	public function update($entity) {

		$em = $this->getEntityManager ();
		$em->persist ( $entity );
		$em->flush ( $entity );

		return $entity;

	}

	public function isExist($feedbackId) {

		return $this->getEntityManager ()->find ( 'BaftFeedback\Entity\BaftfeedbackFeedback', $feedbackId );

	}


}
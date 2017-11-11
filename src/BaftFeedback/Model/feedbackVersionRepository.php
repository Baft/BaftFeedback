<?php

namespace BaftFeedback\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback\Entity\BaftfeedbackFeedbackVersion;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Version\Version;
use Zend\Stdlib\Hydrator\ObjectProperty;
use BaftFeedback;

class feedbackVersionRepository extends EntityRepository implements ServiceLocatorAwareInterface {
	public $serviceLocator;

	/**
	 * find one version by its reference
	 *
	 * @param int|\BaftFeedback\Model\BaftfeedbackFeedbackVersion $entity        	
	 * @return boolean|\BaftFeedback\Model\BaftfeedbackFeedbackVersion
	 */
	public function find($entity) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		
		if (is_numeric ( $entity ))
			$entity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackFeedbackVersion', $entity );
			
			// if not found submission
		if (! ($entity instanceof BaftfeedbackFeedbackVersion))
			return null;
		
		return $entity;
	
	}

	/**
	 * list versinos of feedback
	 *
	 * @param int $feedbackId        	
	 * @return array
	 */
	public function findByFeedback($feedback) {

		return new ArrayCollection ( $this->findBy ( [ 
				'refBaftfeedbackFeedback' => $feedback->getId (),
				'disable' => 0 
		], [ 
				'version' => 'ASC' 
		] ) );
	
	}

	/**
	 *
	 * @param
	 *        	array | \BaftFeedback\Entity\BaftfeedbackFeedbackVersion $entity
	 * @param
	 *        	\BaftFeedback\Entity\BaftfeedbackFeedback | null $feedback
	 * @throws \Exception if feedback dose not define for vesion
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackVersion | false
	 */
	public function create($entity, \BaftFeedback\Entity\BaftfeedbackFeedback $feedback = null) {

		$em = $this->getEntityManager ();
		
		$versionEntity = null;
		
		if (is_array ( $entity )) {
			$versionEntity = new BaftfeedbackFeedbackVersion ();
			$hydrator = new ObjectProperty ();
			$versionEntity = $hydrator->hydrate ( $entity, $versionEntity );
		}
		
		if ($entity instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackVersion)
			$versionEntity = $entity;
		
		$feedbackEntity = $versionEntity->getRefBaftfeedbackFeedback ();
		if (! isset ( $feedbackEntity ) && ! isset ( $feedback ))
			throw new \Exception ( __METHOD__ . ' : feedback entity dose not define for versioning' );
			
			// overwrite with new feedback
		if (isset ( $feedback ))
			$feedbackEntity = $feedback;
			
			// overwitre with refBaftfeedbackFeedback when $feedback dose not set
		$feedback = $feedbackEntity;
		$versionEntity->setRefBaftfeedbackFeedback ( $feedback );
		
		// automatic increase version number if dose not set by developer
		$newVersionNumber = $versionEntity->getVersion ();
		if (! isset ( $newVersionNumber )) {
			$newVersionNumber = "0";
			if ($previousVersion = $feedback->getVersions ()->last ())
				$newVersionNumber = $previousVersion->getVersion () + 1;
			$versionEntity->setVersion ( $newVersionNumber );
		}
		
		// else cases for $entity argument
		if (! $versionEntity instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackVersion)
			throw new \Exception ( __METHOD__ . ' : argument have to be array or instance of \BaftFeedback\Entity\BaftfeedbackFeedbackVersion or \BaftFeedback\Entity\BaftfeedbackFeedback ' );
		

		try {
			$em->persist ( $versionEntity );
			$em->flush ( $versionEntity );
		}
		catch ( \Exception $ex ) {
			return null;
		}
		
		return $versionEntity;
	
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


}
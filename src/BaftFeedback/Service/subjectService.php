<?php

namespace BaftFeedback\Service;

use BaftFeedback\Entity\BaftfeedbackQuestion;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\Factory;
use Zend\Json\Json;
use Zend\Stdlib\Hydrator\ObjectProperty;
use BaftFeedback\Entity\BaftfeedbackFeedback;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubject;
use Zend\Form\Fieldset;
use Zend\EventManager\ListenerAggregateInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData;

/**
 * just proxy of feedback .
 *
 *
 *
 * @deprecated remove in the future
 * @author web
 *        
 */
class subjectService implements ServiceLocatorAwareInterface {
	public $serviceLocator;

	public function __call($method, $params) {
		
		// map some method to another name in feedback service
		$method = (strcasecmp ( $method, 'getFieldset' ) == 0) ? 'getSubjectFieldset' : $method;
		$method = (strcasecmp ( $method, 'saveData' ) == 0) ? 'saveSubjectData' : $method;
		
		return call_user_func_array ( [ 
				$this->getServiceLocator ()->get ( 'BaftFeedback\Service\feedback' ),
				$method 
		], $params );
	
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

	/**
	 * this is implementing a convertion of columnar data to a row
	 * convert subject Data array to one equivalent entity
	 */
	// @todo using feedback , submission , period (start-end range)
	public function findByData(array $subjectData, $feedback = null, $submission = null) {

		$em = $this->getservicelocator ()->get ( 'doctrine\orm\entitymanager' );
		
		$connection = $em->getConnection ();
		
		$subjectfilter = ' 1=1 ';
		$subjectfieldsascolumns = '';
		$fields = [ ];
		if (! empty ( $subjectData )) {
			foreach ( $subjectData as $fieldname => $value ) {
				
				if ($value instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData) {
					$fieldname = $value->getFieldName ();
					$value = $value->getValue ();
				}
				
				$fields [] = $fieldname;
				
				$subjectfieldsascolumns .= " , max(if(field_name='{$fieldname}',`value`,null)) as `{$fieldname}` ";
				if (is_array ( $value )) {
					$subjectfilter .= " and ( false ";
					foreach ( $value as $val )
						$subjectfilter .= " or subject_data.`{$fieldname}`='{$val}' ";
					$subjectfilter .= " )";
				} else
					$subjectfilter .= " and `{$fieldname}`='{$value}' ";
			}
		}
		

		$query = "
    	    select 
                id as subject_data_id,
                field_name,
                value,
                ref_baftfeedback_feedback_id,
                ref_baftfeedback_submission_id,
                ref_baftfeedback_subject_id
                {$subjectfieldsascolumns}
            from
                baftfeedback_feedback_subject_data as subject_data
            group by 
            	ref_baftfeedback_submission_id , ref_baftfeedback_subject_id
            having 
                {$subjectfilter}
        ";
		
		// $rsm = new ResultSetMapping();
		
		// $rsm->addEntityResult('BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData', 'subject_data');
		// $rsm->addFieldResult('subject_data', 'subject_data_id', 'id');
		// $rsm->addFieldResult('subject_data', 'field_name', 'fieldName');
		// $rsm->addFieldResult('subject_data', 'value', 'value');
		// $rsm->addMetaResult('subject_data', 'ref_baftfeedback_feedback_id', 'ref_baftfeedback_feedback_id', true);
		// $rsm->addMetaResult('subject_data', 'ref_baftfeedback_submission_id', 'ref_baftfeedback_submission_id', true);
		// $rsm->addMetaResult('subject_data', 'ref_baftfeedback_subject_id', 'ref_baftfeedback_subject_id', true);
		
		// $query = $em->createNativeQuery($query, $rsm);
		
		/**
		 *
		 * @var \Doctrine\Common\Collections\ArrayCollection $submissions
		 */
		$subjectDataCollection = new ArrayCollection ( $query->getResult () );
		
		$subjectDataCollection = new ArrayCollection ();
		$resultset = $connection->query ( $query )->fetchAll ();
		foreach ( $resultset as $record ) {
			foreach ( $fields as $field ) {
				
				$subjectDataEntity = new BaftfeedbackFeedbackSubjectData ();
				$subjectDataEntity->setFieldName ( $field );
				$subjectDataEntity->setValue ( $record ['value'] );
				$subjectDataEntity->setRefBaftfeedbackFeedback ( $record ['ref_baftfeedback_feedback_id'] );
				$subjectDataEntity->setRefBaftfeedbackSubmission ( $record ['ref_baftfeedback_submission_id'] );
				$subjectDataEntity->setRefBaftfeedbackSubject ( $record ['ref_baftfeedback_subject_id'] );
				
				$subjectDataCollection->add ( $subjectDataEntity );
			}
		}
		
		return $subjectDataCollection;
	
	}


}
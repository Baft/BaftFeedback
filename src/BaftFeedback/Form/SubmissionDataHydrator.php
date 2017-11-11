<?php

namespace BaftFeedback\Form;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Json\Json;

class SubmissionDataHydrator {
	private $namespace;

	public function __construct($namespace = null) {

		if (! is_null ( $namespace ))
			$this->setNamespace ( $namespace );
	
	}

	/**
	 * convert form object to submission data records
	 *
	 * @param unknown $object        	
	 * @return array
	 */
	public function extract($object) {

		$dataCollection = [ ];
		
		$formBindObject = Json::decode ( Json::encode ( $object ), Json::TYPE_ARRAY );
		$namespace = $this->getNamespace ();
		
		if (! isset ( $formBindObject [$namespace] ))
			throw new \Exception ( __METHOD__ . ' "' . $namespace . '" property dose not set in form object ' );
		
		foreach ( $formBindObject [$namespace] as $groupName => &$group ) {
			
			if (! is_array ( $group ) || empty ( $group ))
				continue;
			
			if (! isset ( $group ['baftfeedback_gid'] ))
				throw new \Exception ( __METHOD__ . ' "baftfeedback_gid" property dose not set in form object ' );
			$groupId = $group ['baftfeedback_gid'];
			
			foreach ( $group as $questionName => &$question ) {
				
				if (! is_array ( $question ) || empty ( $question ))
					continue;
				
				if (! isset ( $question ['baftfeedback_qid'] ))
					throw new \Exception ( __METHOD__ . ' "baftfeedback_qid" property dose not set in form object ' );
				$questionId = $question ['baftfeedback_qid'];
				
				foreach ( $question as $fieldName => &$fieldValue ) {
					
					if ($fieldName == 'baftfeedback_qid')
						continue;
					
					$record = [ 
							'question_group_id' => $groupId,
							'question_id' => $questionId,
							'question_field_name' => $fieldName,
							'field_value' => $fieldValue 
					];
					$dataCollection [] = $record;
				}
			}
		}
		
		// print_r($dataCollection);die;
		return $dataCollection;
	
	}

	/**
	 * convert submisison data records to form object
	 *
	 * @param
	 *        	array | \Doctrine\DBAL\Driver\Statement $data
	 * @param unknown $object        	
	 * @return object
	 */
	public function hydrate($data, $object) {

		$formDataTree = [ ];
		
		foreach ( $data as $row ) {
			
			// create question group container
			if (! isset ( $formDataTree [$row ['question_group_id']] ))
				$formDataTree [$row ['question_group_id']] = [ ];
				
				// create question container
			if (! isset ( $formDataTree [$row ['question_group_id']] [$row ['question_id']] ))
				$formDataTree [$row ['question_group_id']] [$row ['question_id']] = [ ];
				
				// add field name to quesiton container
			if (! isset ( $formDataTree [$row ['question_group_id']] [$row ['question_id']] [$row ['question_field_name']] ))
				$formDataTree [$row ['question_group_id']] [$row ['question_id']] [$row ['question_field_name']] = '';
				
				// set value for field name
			$formDataTree [$row ['question_group_id']] [$row ['question_id']] [$row ['question_field_name']] = "{$row['field_value']}";
		}
		
		// print_r($formDataTree);
		
		if (empty ( $formDataTree ))
			return $formDataTree;
		
		$formBindObject = Json::decode ( Json::encode ( $object ), Json::TYPE_ARRAY );
		
		$namespace = $this->getNamespace ();
		
		if (! isset ( $formBindObject [$namespace] ))
			throw new \Exception ( __METHOD__ . ' "' . $namespace . '" property dose not set in form object ' );
		
		foreach ( $formBindObject [$namespace] as $groupName => &$group ) {
			
			if (! is_array ( $group ) || empty ( $group ))
				continue;
			
			if (! isset ( $group ['baftfeedback_gid'] ))
				throw new \Exception ( __METHOD__ . ' "baftfeedback_gid" property dose not set in form object ' );
			$groupId = $group ['baftfeedback_gid'];
			
			foreach ( $group as $questionName => &$question ) {
				
				if (! is_array ( $question ) || empty ( $question ))
					continue;
				
				if (! isset ( $question ['baftfeedback_qid'] ))
					throw new \Exception ( __METHOD__ . ' "baftfeedback_qid" property dose not set in form object ' );
				$questionId = $question ['baftfeedback_qid'];
				
				foreach ( $question as $fieldName => &$fieldValue ) {
					
					if ($fieldName == 'baftfeedback_qid')
						continue;
						
						// check array depth to be exist
					if (isset ( $formDataTree [$groupId] ) && isset ( $formDataTree [$groupId] [$questionId] ) && isset ( $formDataTree [$groupId] [$questionId] [$fieldName] ))
						$fieldValue = $formDataTree [$groupId] [$questionId] [$fieldName];
				}
			}
		}
		
		// print_r($formBindObject);
		
		return $formBindObject;
	
	}

	public function getNamespace() {

		return $this->namespace;
	
	}

	public function setNamespace($namespace) {

		$this->namespace = $namespace;
		return $this;
	
	}


}
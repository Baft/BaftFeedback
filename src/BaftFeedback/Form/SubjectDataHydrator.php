<?php

namespace BaftFeedback\Form;

use Zend\Json\Json;

class SubjectDataHydrator {

	/**
	 * convert form object to submission data records
	 * 
	 * @param unknown $object        	
	 * @return array
	 */
	public function extract($object) {

		$dataCollection = [ ];
		
		$formBindObject = Json::decode ( Json::encode ( $object ), Json::TYPE_ARRAY );
		
		if (! isset ( $formBindObject ['baftfeedback_arzi_feedback_subject'] ))
			throw new \Exception ( __METHOD__ . ' "baftfeedback_arzi_feedback_questions" property dose not set in form object ' );
			
			// @TODO do extaraction
		
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

		$formBindObject = Json::decode ( Json::encode ( $object ), Json::TYPE_ARRAY );
		
		if (! isset ( $formBindObject ['baftfeedback_arzi_feedback_questions'] ))
			throw new \Exception ( __METHOD__ . ' "baftfeedback_arzi_feedback_questions" property dose not set in form object ' );
			

			// @TODO do hydration
		
		return $formBindObject;
	
	}

	
	/**
	 * make one level array from multi level form data
	 * keep fieldset name as namespace for elements name : [fieldsetName].../[filedName]
	 * [ nameSpace ] => nodeValue
	 * 
	 * @param array $data        	
	 * @param string $container        	
	 */
	protected function getDataDictionary($withNamespace = false) {

		$container = '';
		$data = $this->getData ( \Zend\Form\FormInterface::VALUES_AS_ARRAY );
		
		if (empty ( $data ))
			return [ ];
			
			// remve subject namespace from field name
		if (! $withNamespace)
			$data = $data [$this->getNamespace ()];
		
		return $this->toDictionary ( $data, $container );
	
	}

	protected function toDictionary($data, $container = '') {

		$flat = [ ];
		
		foreach ( $data as $key => $value ) {
			
			if (! empty ( $container ) && is_string ( $container )) {
				
				if (is_string ( $key ))
					$key = $container . "/" . $key;
				
				if (is_int ( $key ))
					$key = $container;
			}
			
			if (is_array ( $value )) {
				$list = $this->toDictionary ( $value, $key );
			} else
				$list = [ 
						$key => $value 
				];
			
			$flat = array_merge_recursive ( $flat, $list );
		}
		
		return $flat;
	
	}


}
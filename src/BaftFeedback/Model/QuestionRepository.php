<?php

namespace BaftFeedback\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BaftFeedback\Entity\BaftfeedbackFeedbackVersion;
use Doctrine\ORM\EntityRepository;
use BaftFeedback\Entity\BaftfeedbackQuestion;

class QuestionRepository extends EntityRepository implements ServiceLocatorAwareInterface {
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

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		
		if (is_numeric ( $entity ))
			$entity = $em->find ( 'BaftFeedback\Entity\BaftfeedbackQuestion', $entity );
			
			// if not found submission
		if (! ($entity instanceof BaftfeedbackQuestion))
			return null;
		
		return $entity;
	
	}

	public function insertQuestion() {

		$insertQustionQuery = "
	
          
            set @question_group:=119;
            set @question_order:=1;
            set @question_label:='ffff';
            set @question_structure:=1;
            set @question_fieldset:='feedbackExtendThreeRadio';
            set @question_fieldset_config:='{\"attributes\":{\"class\":\"question\"}}';
            set @question_config:='{}';
	
            -- question name is unique and contain [prefix letter][incremental number]
            set @question_name_prefix:='Q';
	
            -- find last question name and increment numeric part +1
            select  @question_name:=concat(@question_name_prefix,CAST(substring(`name`,char_length(@question_name_prefix)+1)+1 AS CHAR) ) qname
            from `baftfeedback_question`
            where substring(`name`,1,char_length(@question_name_prefix)) = @question_name_prefix
            order by
            	substring(`name`,1,char_length(@question_name_prefix)),
                CAST(substring(`name`,char_length(@question_name_prefix)+1) AS UNSIGNED)
            	desc
            limit 1
            ;
	
			-- create question name base of @question_name_prefix and id number
			-- select @question_name:=concat(@question_name_prefix,CAST(id+1 AS CHAR) ) qname
			-- from `baftfeedback_question`
			-- order by id desc
			-- limit 1;
	
            start transaction;
	
            -- save question with new name (incrimental name)
            INSERT INTO
            	`baftfeedback_question` (`label`, `name`, `ref_baftfeedback_question_structure_id`
            -- ,`json_feildset_confiig`,`json_question_config`
            )
            VALUES (
            	@question_label,
            	@question_name,
            	@question_structure
            	-- ,@question_fieldset ,@question_fieldset_config,@question_config
            );
	
            set @question_id:=last_insert_id();
	
            -- set last saved question under a specific group
            INSERT INTO
            `baftfeedback_question_group_questions` (`ref_baftfeedback_question_group_id`, `ref_baftfeedback_question_id`, `question_order`)
            VALUES (
            	@question_group ,
            	@question_id ,
            	@question_order
            );
	
		
            commit;
	
	
        ";
	
	}


}
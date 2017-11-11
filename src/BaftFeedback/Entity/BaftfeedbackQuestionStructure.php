<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackQuestionStructure
 *
 * @ORM\Table(name="baftfeedback_question_structure")
 * @ORM\Entity
 */
class BaftfeedbackQuestionStructure implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="version", type="string", length=255, nullable=true)
	 */
	private $version;
	
	/**
	 *
	 * @var string @ORM\Column(name="feildset_name", type="string", length=255, nullable=false)
	 */
	private $feildsetName;
	
	/**
	 *
	 * @var string @ORM\Column(name="json_fieldset_config", type="text", nullable=true)
	 */
	private $jsonFieldsetConfig;

	
	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {

		return $this->id;
	
	}

	/**
	 * Set version
	 *
	 * @param string $version        	
	 *
	 * @return BaftfeedbackQuestionStructure
	 */
	public function setVersion($version) {

		$this->version = $version;
		
		return $this;
	
	}

	/**
	 * Get version
	 *
	 * @return string
	 */
	public function getVersion() {

		return $this->version;
	
	}

	/**
	 * Set feildsetName
	 *
	 * @param string $feildsetName        	
	 *
	 * @return BaftfeedbackQuestionStructure
	 */
	public function setFeildsetName($feildsetName) {

		$this->feildsetName = $feildsetName;
		
		return $this;
	
	}

	/**
	 * Get feildsetName
	 *
	 * @return string
	 */
	public function getFeildsetName() {

		return $this->feildsetName;
	
	}

	/**
	 * Set jsonFieldsetConfig
	 *
	 * @param string $jsonFieldsetConfig        	
	 *
	 * @return BaftfeedbackQuestionStructure
	 */
	public function setJsonFieldsetConfig($jsonFieldsetConfig) {

		$this->jsonFieldsetConfig = $jsonFieldsetConfig;
		
		return $this;
	
	}

	/**
	 * Get jsonFieldsetConfig
	 *
	 * @return string
	 */
	public function getJsonFieldsetConfig() {

		return $this->jsonFieldsetConfig;
	
	}


}

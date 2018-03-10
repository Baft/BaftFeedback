<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as ZendForm;

/**
 * BaftfeedbackQuestion
 *
 * @ORM\Table(name="baftfeedback_question")
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\QuestionRepository")
 */
class BaftfeedbackQuestion implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="label", type="text", length=65535, nullable=true)
	 * @ZendForm\Type("textarea")
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":1500}})
	 */
	private $label;

	/**
	 *
	 * @var string @ORM\Column(name="name", type="string", length=255, nullable=true)
	 */
	private $name;

	/**
	 *
	 * @var integer @ORM\Column(name="disable", type="integer", nullable=true)
	 */
	private $disable;

	/**
	 *
	 * @var integer @ORM\Column(name="active", type="integer", nullable=true)
	 */
	private $active;

	/**
	 *
	 * @var string @ORM\Column(name="ref_fieldset", type="string", length=255, nullable=true)
	 */
	private $refFieldset;

	/**
	 *
	 * @var string @ORM\Column(name="json_fieldset_config", type="text", length=65535, nullable=true)
	 */
	private $jsonFieldsetConfig;

	/**
	 *
	 * @var string @ORM\Column(name="json_question_config", type="text", length=65535, nullable=true)
	 */
	private $jsonQuestionConfig;

	/**
	 * virtual field , make bidirectional connection to association table BaftfeedbackQuestionGroupQuestions with groups .
	 *
	 * contain groups that this question is member of it.
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackQuestionGroup @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions", mappedBy="refBaftfeedbackQuestion")
	 */
	private $groups;

	public function __construct() {

		$this->groups = new \Doctrine\Common\Collections\ArrayCollection ();

	}

	public function getNamespace() {

		return $this->getName ();

	}

	/**
	 * virtual field , groups that question is member of it.
	 * bidirection association to BaftfeedbackQuestionGroupQuestions
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackQuestionGroup
	 */
	public function getGroups() {

		return $this->groups;

	}

	/**
	 * virtual field
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $groups
	 * @return \BaftFeedback\Entity\BaftfeedbackQuestion
	 */
	public function setGroups($groups) {

		$this->groups = $groups;
		return $this;

	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {

		return $this->id;

	}

	/**
	 * Set label
	 *
	 * @param string $label
	 *
	 * @return BaftfeedbackQuestion
	 */
	public function setLabel($label) {

		$this->label = $label;

		return $this;

	}

	/**
	 * Get label
	 *
	 * @return string
	 */
	public function getLabel() {

		return $this->label;

	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return BaftfeedbackQuestion
	 */
	public function setName($name) {

		$this->name = $name;

		return $this;

	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;

	}

	/**
	 * Set disable
	 *
	 * @param integer $disable
	 *
	 * @return BaftfeedbackQuestion
	 */
	public function setDisable($disable) {

		$this->disable = $disable;

		return $this;

	}

	/**
	 * Get disable
	 *
	 * @return integer
	 */
	public function getDisable() {

		return $this->disable;

	}

	/**
	 * Set active
	 *
	 * @param integer $active
	 *
	 * @return BaftfeedbackQuestion
	 */
	public function setActive($active) {

		$this->active = $active;

		return $this;

	}

	/**
	 * Get active
	 *
	 * @return integer
	 */
	public function getActive() {

		return $this->active;

	}


	/**
	 *
	 * @return the string
	 */
	public function getRefFieldset() {

		return $this->refFieldset;

	}

	/**
	 *
	 * @param string $refFieldsett
	 */
	public function setRefFieldset($refFieldsett) {

		$this->refFieldset = $refFieldsett;
		return $this;

	}

	/**
	 *
	 * @return the string
	 */
	public function getJsonFieldsetConfig() {

		return $this->jsonFieldsetConfig;

	}

	/**
	 *
	 * @param string $jsonFieldsetConfig
	 */
	public function setJsonFieldsetConfig($jsonFieldsetConfig) {

		$this->jsonFieldsetConfig = $jsonFieldsetConfig;
		return $this;

	}

	/**
	 *
	 * @return the string
	 */
	public function getJsonQuestionConfig() {

		return $this->jsonQuestionConfig;

	}

	/**
	 *
	 * @param string $jsonQuestionConfig
	 */
	public function setJsonQuestionConfig($jsonQuestionConfig) {

		$this->jsonQuestionConfig = $jsonQuestionConfig;
		return $this;

	}


}

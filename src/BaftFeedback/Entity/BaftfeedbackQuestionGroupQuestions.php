<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as ZendForm;

/**
 * BaftfeedbackQuestionGroupQuestions
 *
 * @ORM\Table(name="baftfeedback_question_group_questions", indexes={@ORM\Index(name="fk_baftfeedback_question_group_questions_baftfeedback_quest_idx", columns={"ref_baftfeedback_question_group_id"}), @ORM\Index(name="fk_baftfeedback_question_group_questions_baftfeedback_quest_idx1", columns={"ref_baftfeedback_question_id"})})
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\FeedbackQuestionRepository")
 */
class BaftfeedbackQuestionGroupQuestions implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 *
	 * @var integer @ORM\Column(name="question_order", type="integer", nullable=true)
	 *
	 *      @ZendForm\Options({"label":"order"})
	 *      @ZendForm\Attributes({"value" : "0"})
	 */
	private $questionOrder;

	/**
	 *
	 * @var float @ORM\Column(name="score", type="float", precision=10, scale=0, nullable=true)
	 *
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"score"})
	 *
	 */
	private $score;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackQuestionGroup @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestionGroup", inversedBy="questions")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_question_group_id", referencedColumnName="id")
	 *      })
	 *
	 *      @ZendForm\Name("refBaftfeedbackQuestionGroup")
	 *      @ZendForm\Instance("\BaftFeedback\Entity\BaftfeedbackQuestionGroup")
	 *      @ZendForm\Type("\DoctrineORMModule\Form\Element\EntitySelect")
	 *      @ZendForm\Attributes({"required" : "true"})
	 *      @ZendForm\Options({
	 *      "label":"feedback group" ,
	 *      "target_class":"\BaftFeedback\Entity\BaftfeedbackQuestionGroup" ,
	 *      "property" : "label" ,
	 *      "optgroup_identifier" : "ref_group_parent" ,
	 *      "optgroup_default" : "parent",
	 *      "display_empty_item" : "true",
	 *      "empty_item_label" : "---"
	 *      })
	 *      @ZendForm\Flags({"priority" : "98"})
	 *
	 */
	private $refBaftfeedbackQuestionGroup;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackQuestion @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestion", inversedBy="groups")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_question_id", referencedColumnName="id")
	 *      })
	 *
	 *      @ZendForm\Name("refBaftfeedbackQuestion")
	 *      @ZendForm\Instance("\BaftFeedback\Entity\BaftfeedbackQuestion")
	 *      @ZendForm\Type("\DoctrineORMModule\Form\Element\EntitySelect")
	 *      @ZendForm\Attributes({"required" : "true"})
	 *      @ZendForm\Options({
	 *      "label":"question type" ,
	 *      "target_class":"\BaftFeedback\Entity\BaftfeedbackQuestion" ,
	 *      "property" : "label"
	 *      })
	 *      @ZendForm\Flags({"priority" : "99"})
	 *
	 */
	private $refBaftfeedbackQuestion;


	/**
	 *
	 * @var string @ORM\Column(name="label", type="text", length=65535, nullable=true)
	 *
	 *      @ZendForm\Type("textarea")
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":1500}})
	 *      @ZendForm\Options({"label":"label" })
	 *      @ZendForm\Attributes({"required" : "true", "order" : "100"})
	 *      @ZendForm\Flags({"priority" : "100"})
	 */
	private $label;

	/**
	 *
	 * @var integer @ORM\Column(name="disable", type="boolean", nullable=true)
	 *
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"disable"})
	 */
	private $disable;

	/**
	 *
	 * @var integer @ORM\Column(name="deleted", type="boolean", nullable=true)
	 *
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"deleted"})
	 */
	private $deleted;

	/**
	 *
	 * @var integer @ORM\Column(name="required", type="boolean", nullable=true)
	 *
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"required"})
	 */
	private $required;

	/**
	 *
	 * @var string @ORM\Column(name="json_fieldset_config", type="text", length=65535, nullable=true)
	 *
	 *      @ZendForm\Type("textarea")
	 *      @ZendForm\Options({"label":"json fieldset config"})
	 *      @ZendForm\Attributes({"value" : "{}"})
	 */
	private $jsonFieldsetConfig;

	/**
	 *
	 * @var string @ORM\Column(name="json_question_config", type="text", length=65535, nullable=true)
	 *
	 *      @ZendForm\Type("textarea")
	 *      @ZendForm\Options({"label":"json question config"})
	 *      @ZendForm\Attributes({"value" : "{}"})
	 */
	private $jsonQuestionConfig;

	public function getQuestionNamespace() {

		return $this->getRefBaftfeedbackQuestion ()->getNamespace () . "_" . $this->getId ();

	}

	public function getGroupNamespace() {

		return $this->getRefBaftfeedbackQuestionGroup ()->getNamespace () . "_" . $this->getId ();

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
	 * Set questionOrder
	 *
	 * @param integer $questionOrder
	 *
	 * @return BaftfeedbackQuestionGroupQuestions
	 */
	public function setQuestionOrder($questionOrder) {

		$this->questionOrder = $questionOrder;

		return $this;

	}

	/**
	 * Get questionOrder
	 *
	 * @return integer
	 */
	public function getQuestionOrder() {

		return $this->questionOrder;

	}

	/**
	 * Set score
	 *
	 * @param float $score
	 *
	 * @return BaftfeedbackQuestionGroupQuestions
	 */
	public function setScore($score) {

		$this->score = $score;

		return $this;

	}

	/**
	 * Get score
	 *
	 * @return float
	 */
	public function getScore() {

		return $this->score;

	}

	/**
	 * Set disable
	 *
	 * @param integer $disable
	 *
	 * @return BaftfeedbackQuestionGroupQuestions
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
	 * Set refBaftfeedbackQuestionGroup
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackQuestionGroup $refBaftfeedbackQuestionGroup
	 *
	 * @return BaftfeedbackQuestionGroupQuestions
	 */
	public function setRefBaftfeedbackQuestionGroup(\BaftFeedback\Entity\BaftfeedbackQuestionGroup $refBaftfeedbackQuestionGroup = null) {

		$this->refBaftfeedbackQuestionGroup = $refBaftfeedbackQuestionGroup;

		return $this;

	}

	/**
	 * Get refBaftfeedbackQuestionGroup
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackQuestionGroup
	 */
	public function getRefBaftfeedbackQuestionGroup() {

		return $this->refBaftfeedbackQuestionGroup;

	}

	/**
	 * Set refBaftfeedbackQuestion
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackQuestion $refBaftfeedbackQuestion
	 *
	 * @return BaftfeedbackQuestionGroupQuestions
	 */
	public function setRefBaftfeedbackQuestion(\BaftFeedback\Entity\BaftfeedbackQuestion $refBaftfeedbackQuestion = null) {

		$this->refBaftfeedbackQuestion = $refBaftfeedbackQuestion;

		return $this;

	}

	/**
	 * Get refBaftfeedbackQuestion
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackQuestion
	 */
	public function getRefBaftfeedbackQuestion() {

		return $this->refBaftfeedbackQuestion;

	}

	/**
	 *
	 * @return the string
	 */
	public function getLabel() {

		return $this->label;

	}

	/**
	 *
	 * @param string $label
	 */
	public function setLabel($label) {

		$this->label = $label;
		return $this;

	}

	/**
	 *
	 * @return the integer
	 */
	public function getDeleted() {

		return $this->deleted;

	}

	/**
	 *
	 * @param integer $deleted
	 */
	public function setDeleted($deleted) {

		$this->deleted = $deleted;
		return $this;

	}

	/**
	 *
	 * @return the integer
	 */
	public function getRequired() {

		return $this->required;

	}

	/**
	 *
	 * @param integer $required
	 */
	public function setRequired($required) {

		$this->required = $required;
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

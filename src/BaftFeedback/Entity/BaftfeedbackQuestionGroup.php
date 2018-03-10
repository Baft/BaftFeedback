<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as ZendForm;

/**
 * BaftfeedbackQuestionGroup
 *
 * @ORM\Table(name="baftfeedback_question_group")
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\feedbackQuestionGroupRepository")
 */
class BaftfeedbackQuestionGroup implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *
	 *      @ZendForm\Exclude()
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="`name`", type="string", length=255, nullable=true)
	 *
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":35}})
	 *      @ZendForm\Attributes({"type":"text" , "required" : "true"})
	 *      @ZendForm\Options({"label":"system name"})
	 */
	private $name;

	/**
	 *
	 * @var string @ORM\Column(name="label", type="text", length=65535, nullable=true)
	 *
	 *      @ZendForm\Type("textarea")
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":500}})
	 *      @ZendForm\Options({"label":"label"})
	 *      @ZendForm\Attributes({"required" : "true"})
	 */
	private $label;

	/**
	 *
	 * @var integer @ORM\Column(name="`order`", type="integer", nullable=true)
	 *
	 *      @ZendForm\Options({"label":"order"})
	 *
	 */
	private $order;

	/**
	 * @ORM\OneToMany(targetEntity="BaftfeedbackQuestionGroup", mappedBy="refGroupParent")
	 * @ORM\OrderBy({"order" = "DESC"})
	 *
	 * @ZendForm\Exclude()
	 */
	private $children;

	/**
	 *
	 * @ORM\ManyToOne(targetEntity="BaftfeedbackQuestionGroup", inversedBy="children")
	 * @ORM\JoinColumn(name="ref_group_parent", referencedColumnName="id", nullable=true )
	 *
	 * @ZendForm\Exclude()
	 */
	private $refGroupParent;


	/**
	 * var array collection of \BaftFeedback\Entity\BaftfeedbackQuestion
	 *
	 * ORM\ManyToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestion")
	 * ORM\JoinTable(name="baftfeedback_question_group_questions",
	 * joinColumns={
	 * @ORM\JoinColumn(name="ref_baftfeedback_question_group_id", referencedColumnName="id")
	 * },
	 * inverseJoinColumns={
	 * @ORM\JoinColumn(name="ref_baftfeedback_question_id", referencedColumnName="id")
	 * }
	 * )
	 *
	 * private $questions;
	 */

	/**
	 * virtual field , make bidirectional connection to association table BaftfeedbackQuestionGroupQuestions with question .
	 *
	 * contain questions that is member of this group.
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection collection of BaftfeedbackQuestion
	 *
	 *      @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions", mappedBy="refBaftfeedbackQuestionGroup")
	 *      @ORM\OrderBy({"questionOrder" = "DESC"})
	 *
	 *      @ZendForm\Exclude()
	 */
	private $questions;

	public function __construct() {

		$this->questions = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->children = new \Doctrine\Common\Collections\ArrayCollection ();

	}

	public function getNamespace() {

		return $this->getName ();

	}

	/**
	 *
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getQuestions() {

		return $this->questions;

	}

	// /**
	// *
	// * @param \Doctrine\Common\Collections\Collection $collection
	// * @return \BaftFeedback\Entity\BaftfeedbackQuestionGroup
	// */
	// public function setQuestions(\Doctrine\Common\Collections\Collection $collection){
	// $this->questions=$collection;
	// return $this;
	// }


	// /**
	// *
	// * @param \Doctrine\Common\Collections\Collection $collection
	// * @return \BaftFeedback\Entity\BaftfeedbackQuestionGroup
	// */
	// public function setChildren(\Doctrine\Common\Collections\Collection $collection){
	// $this->children=$collection;
	// return $this;
	// }

	/**
	 *
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildren() {

		return $this->children;

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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return BaftfeedbackQuestionGroup
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
	 * Set label
	 *
	 * @param string $label
	 *
	 * @return BaftfeedbackQuestionGroup
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
	 * Set order
	 *
	 * @param integer $order
	 *
	 * @return BaftfeedbackQuestionGroup
	 */
	public function setOrder($order) {

		$this->order = $order;

		return $this;

	}

	/**
	 * Get order
	 *
	 * @return integer
	 */
	public function getOrder() {

		return $this->order;

	}


	/**
	 * Set refGroupParent
	 *
	 * @param integer $refGroupParent
	 *
	 * @return BaftfeedbackQuestionGroup
	 */
	public function setRefGroupParent($refGroupParent) {

		$this->refGroupParent = $refGroupParent;

		return $this;

	}

	/**
	 * Get refGroupParent
	 *
	 * @return integer
	 */
	public function getRefGroupParent() {

		return $this->refGroupParent;

	}


}

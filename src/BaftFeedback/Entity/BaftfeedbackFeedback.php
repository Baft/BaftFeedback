<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as ZendForm;

/**
 * BaftfeedbackFeedback
 *
 * @ORM\Table(name="baftfeedback_feedback")
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\feedbackRepository")
 */
class BaftfeedbackFeedback implements BaftFeedbackEntityInterface {
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
	 * @var string @ORM\Column(name="`name`", type="string", length=255, nullable=false)
	 *
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":35}})
	 *      @ZendForm\Validator({"name":"Regex", "options":{"pattern":"/[a-zA-Z][a-zA-Z0-9_]+/"}})
	 *      @ZendForm\Attributes({"type":"text" , "required" : "true"})
	 *      @ZendForm\Options({"label":"system name"})
	 *
	 *      @ZendForm\Flags({"priority" : "99"})
	 */
	private $name;

	/**
	 *
	 * @var string @ORM\Column(name="label", type="string", length=255, nullable=true)
	 *
	 *      @ZendForm\Type("textarea")
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":500}})
	 *      @ZendForm\Options({"label":"label"})
	 *      @ZendForm\Attributes({"required" : "true"})
	 *
	 */
	private $label;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackQuestionGroup $refBaftfeedbackQuestionGroupId @ORM\OneToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestionGroup" )
	 *      @ORM\JoinColumn(name="ref_baftfeedback_question_group_id", referencedColumnName="id")
	 *
	 *      @ZendForm\Name("refBaftfeedbackQuestionGroupId")
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
	 *
	 */
	private $refBaftfeedbackQuestionGroupId;

	/**
	 *
	 * @var string @ORM\Column(name="`desc`", type="string", length=255, nullable=true)
	 *
	 *      @ZendForm\Type("textarea")
	 *      @ZendForm\Filter({"name":"StringTrim"})
	 *      @ZendForm\Validator({"name":"StringLength", "options":{"min":1, "max":500}})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Required(false)
	 *      @ZendForm\Options({"label":"description"})
	 */
	private $desc;


	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubject $subject @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubject" , inversedBy="refBaftfeedbackFeedback")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_subject_fieldset", referencedColumnName="id", nullable=true)
	 *      })
	 *
	 *      @ZendForm\Instance("\BaftFeedback\Entity\BaftfeedbackFeedbackSubject")
	 *      @ZendForm\Type("\DoctrineORMModule\Form\Element\EntitySelect")
	 *      @ZendForm\Options({
	 *      "label":"feedback subject" ,
	 *      "target_class":"\BaftFeedback\Entity\BaftfeedbackFeedbackSubject" ,
	 *      "property" : "label"
	 *      })
	 *
	 *      @ZendForm\Flags({"priority" : "98"})
	 */
	private $subject;


	/**
	 *
	 * @var string @ORM\Column(name="available_time", type="string", length=255, nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Attributes({"value":"sa" })
	 *      @ZendForm\Options({"label":"available time" })
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Required(false)
	 */
	private $availableTime;

	/**
	 *
	 * @var string @ORM\Column(name="expire_time", type="string", length=255, nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"expire time"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Required(false)
	 */
	private $expireTime;

	/**
	 *
	 * @var string @ORM\Column(name="duration_time", type="string", length=255, nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"duration time"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Required(false)
	 */
	private $durationTime;

	/**
	 *
	 * @var string period time is ISO_8601 : https://en.wikipedia.org/wiki/ISO_8601#Durations
	 *      @ORM\Column(name="interval_time", type="string", length=255, nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"interval time"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Required(false)
	 */
	private $intervalTime;


	/**
	 *
	 * @var string @ORM\Column(name="`repeat`", type="integer", length=255, nullable=false, options={"default":1})
	 *
	 *      @ZendForm\Attributes({"value":"1"})
	 *      @ZendForm\Options({"label":"repeat"})
	 */
	private $repeat;

	/**
	 *
	 * @var string @ORM\Column(name="score", type="string", length=255, nullable=false, options={"default":0})
	 *
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"score"})
	 */
	private $score;

	/**
	 *
	 * @var integer @ORM\Column(name="`deleted`", type="integer", nullable=true, options={"default":0})
	 *
	 *      @ZendForm\Exclude()
	 *
	 *      @ZendForm\Type("checkbox")
	 *      @ZendForm\Attributes({"value":"0" })
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Options({
	 *      "label":"delete",
	 *      "use_hidden_element" : "false",
	 *      "checked_value" : "1",
	 *      "unchecked_value" : "0"
	 *      })
	 */
	private $deleted;

	/**
	 *
	 * @var integer @ORM\Column(name="active", type="integer", nullable=false, options={"default":1})
	 *
	 *      @ZendForm\Type("checkbox")
	 *      @ZendForm\Attributes({"value":"1" , "checked":"checked"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Options({
	 *      "label":"active",
	 *      "use_hidden_element" : "false",
	 *      "checked_value" : "1",
	 *      "unchecked_value" : "0"
	 *      })
	 */
	private $active;

	/**
	 *
	 * @var  integer @ORM\Column(name="submission_limit", type="integer", nullable=false, options={"default":0})
	 *
	 * 		@ZendForm\Type("input")
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Options({
	 *      "label":"submission limit",
	 *      "use_hidden_element" : "false",
	 *      "checked_value" : "0",
	 *      "unchecked_value" : "1"
	 *      })
	 */
	private $submissionLimit;


	/**
	 *
	 * @var integer @ORM\Column(name="`submission_editable`", type="integer", nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Type("checkbox")
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Options({
	 *      "label":"submission editable",
	 *      "use_hidden_element" : "false",
	 *      "checked_value" : "0",
	 *      "unchecked_value" : "1"
	 *      })
	 */
	private $submissionEditable;

	/**
	 *
	 * @var string @ORM\Column(name="submission_duration", type="string", length=255, nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\Options({"label":"submission duration time"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Required(false)
	 */
	private $submissionDuration;


	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection  @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackVersion", mappedBy="refBaftfeedbackFeedback")
	 *
	 *      @ZendForm\Exclude()
	 *
	 */
	private $versions;


	public function __construct() {

		$this->feedbackGroups = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->versions = new \Doctrine\Common\Collections\ArrayCollection ();

	}

	/**
	 *
	 * @return the $versions
	 */
	public function getVersions() {

		return $this->versions;

	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $versions
	 */
	public function setVersions($versions) {

		$this->versions = $versions;
		return $this;

	}

	public function getNamespace() {

		if (! isset ( $this->name ))
			throw new \Exception ( __METHOD__ . ' : feedback entity dose not initialized ' );
		return "baftfeedback_" . $this->getName ();

	}

	public function getQuestionsNamespace() {

		return $this->getNamespace () . "_questions";

	}

	public function getSubjectNamespace() {

		return $this->getNamespace () . "_subject";

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
	 * Get name
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;

	}

	/**
	 * set name
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setName($name) {

		$this->name = $name;
		return $this;

	}

	/**
	 * Set label
	 *
	 * @param string $label
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setLabel($Label) {

		$this->label = $Label;

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
	 * Set refBaftfeedbackQuestionGroupId
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackQuestionGroup $refBaftfeedbackQuestionGroupId
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setRefBaftfeedbackQuestionGroupId($refBaftfeedbackQuestionGroupId) {

		$this->refBaftfeedbackQuestionGroupId = $refBaftfeedbackQuestionGroupId;

		return $this;

	}

	/**
	 * Get refBaftfeedbackQuestionGroupId
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackQuestionGroup
	 */
	public function getRefBaftfeedbackQuestionGroupId() {

		return $this->refBaftfeedbackQuestionGroupId;

	}

	/**
	 * Set desc
	 *
	 * @param string $desc
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setDesc($desc) {

		$this->desc = $desc;

		return $this;

	}

	/**
	 * Get desc
	 *
	 * @return string
	 */
	public function getDesc() {

		return $this->desc;

	}

	/**
	 * Set availableTime
	 *
	 * @param string $availableTime
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setAvailableTime($availableTime) {

		$this->availableTime = $availableTime;

		return $this;

	}

	/**
	 * Get availableTime
	 *
	 * @return string
	 */
	public function getAvailableTime() {

		return $this->availableTime;

	}

	/**
	 * Set expireTime
	 *
	 * @param string $expireTime
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setExpireTime($expireTime) {

		$this->expireTime = $expireTime;

		return $this;

	}

	/**
	 * Get expireTime
	 *
	 * @return string
	 */
	public function getExpireTime() {

		return $this->expireTime;

	}

	/**
	 * Set durationTime
	 *
	 * @param string $durationTime
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setDurationTime($durationTime) {

		$this->durationTime = $durationTime;

		return $this;

	}

	/**
	 * Get durationTime
	 *
	 * @return string
	 */
	public function getDurationTime() {

		return $this->durationTime;

	}

	/**
	 * Set repeat
	 *
	 * @param string $repeat
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setRepeat($repeat) {

		$this->repeat = $repeat;

		return $this;

	}

	/**
	 * Get repeat
	 *
	 * @return string
	 */
	public function getRepeat() {

		return $this->repeat;

	}

	/**
	 * Set score
	 *
	 * @param string $score
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setScore($score) {

		$this->score = $score;

		return $this;

	}

	/**
	 * Get score
	 *
	 * @return string
	 */
	public function getScore() {

		return $this->score;

	}

	/**
	 * Set deleted
	 *
	 * @param integer $deleted
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function setDeleted($deleted) {

		$this->deleted = $deleted;

		return $this;

	}

	/**
	 * Get deleted
	 *
	 * @return integer
	 */
	public function getDeleted() {

		return $this->deleted;

	}

	/**
	 * Set active
	 *
	 * @param integer $active
	 *
	 * @return BaftfeedbackFeedback
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
	 * @return string
	 */
	public function getIntervalTime() {

		return $this->intervalTime;

	}

	/**
	 *
	 * @param string $periodTime
	 */
	public function setIntervalTime($intervalTime) {

		$this->intervalTime = $intervalTime;
		return $this;

	}

	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject
	 */
	public function getSubject() {

		return $this->subject;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubject $subject
	 */
	public function setSubject(\BaftFeedback\Entity\BaftfeedbackFeedbackSubject $subject = null) {

		$this->subject = $subject;
		return $this;

	}


	/**
	 * @return int $submissionLimit
	 */
	public function getSubmissionLimit() {

		return $this->submissionLimit;
	}



	/**
	 * @param int $submissionLimit
	 *  @return BaftfeedbackFeedback
	 */
	public function setSubmissionLimit($submissionLimit) {

		$this->submissionLimit = $submissionLimit;
		return $this;
	}


	/**
	 * @return the $editable
	 */
	public function getSubmissionEditable() {

		return $this->submissionEditable;
	}



	/**
	 * @param number $editable
	 */
	public function setSubmissionEditable($editable) {

		$this->submissionEditable = $editable;
		return $this;
	}
	/**
	 * @return the $submissionDurationTime
	 */
	public function getSubmissionDurationTime() {

		return $this->submissionDurationTime;
	}



	/**
	 * @param string $submissionDurationTime
	 */
	public function setSubmissionDurationTime($submissionDurationTime) {

		$this->submissionDurationTime = $submissionDurationTime;
		return $this;
	}
	/**
	 * @return the $submissionDuration
	 */
	public function getSubmissionDuration() {

		return $this->submissionDuration;
	}



	/**
	 * @param string $submissionDuration
	 */
	public function setSubmissionDuration($submissionDuration) {

		$this->submissionDuration = $submissionDuration;
		return $this;
	}








}

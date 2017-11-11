<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as ZendForm;

/**
 * BaftfeedbackFeedbackSubmission
 *
 * @ORM\Table(name="baftfeedback_feedback_submission",
 * indexes={
 * @ORM\Index(name="fk_baftfeedback_submission_baftfeedback_feedback1_idx", columns={"ref_baftfeedback_feedback_id"})
 * })
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\SubmissionRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class BaftfeedbackFeedbackSubmission implements BaftFeedbackEntityInterface {

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedback @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedback")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_feedback_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackFeedback;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackVersion @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackVersion")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_feedback_version_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackFeedbackVersion;

	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData" , mappedBy="refBaftfeedbackSubmission", cascade={"all"})
	 *      @ORM\OrderBy({"id" = "ASC"})
	 */
	private $subjectData;

	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmission" , mappedBy="refBaftfeedbackFeedbackSubmissionId", cascade={"all"})
	 *      @ORM\OrderBy({"id" = "ASC"})
	 */
	private $submissionData;


	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData" , mappedBy="refBaftfeedbackSubmissionId", cascade={"all"})
	 *      @ORM\OrderBy({"submitTime" = "ASC"})
	 */
	private $submitters;

	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionState" , mappedBy="refBaftfeedbackFeedbackSubmissionId", cascade={"all"})
	 *      @ORM\OrderBy({"version" = "ASC"})
	 *
	 */
	private $states;

	/**
	 *
	 * @var string @ORM\Column(name="expire_time", type="string", length=255, nullable=true, options={"default":0})
	 */
	private $expireTime;

	/**
	 *
	 * @var string @ORM\Column(name="start_time", type="string", length=255, nullable=true, options={"default":0})
	 */
	private $startTime;

	/**
	 *
	 * @var integer @ORM\Column(name="continuous", type="integer", nullable=false, options={"default":0})
	 */
	private $continuous;

	/**
	 *
	 * @var integer @ORM\Column(name="`editable`", type="integer", nullable=false, options={"default":0})
	 *
	 *      @ZendForm\Type("checkbox")
	 *      @ZendForm\Attributes({"value":"0"})
	 *      @ZendForm\AllowEmpty()
	 *      @ZendForm\Options({
	 *      "label":"editable",
	 *      "use_hidden_element" : "false",
	 *      "checked_value" : "0",
	 *      "unchecked_value" : "1"
	 *      })
	 */
	private $editable;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="submission_period", type="string", length=255, nullable=true, options={"default":0})
	 */
	private $submissionPeriod;

	function __construct() {

		$this->submitters = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->states = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->subjectData = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->submissionData = new \Doctrine\Common\Collections\ArrayCollection ();

	}


	/**
	 * Set refBaftfeedbackFeedbackSubjectData
	 *
	 * @param \Doctrine\Common\Collections\Collection $refBaftfeedbackFeedbackSubjectData
	 *
	 * @return BaftfeedbackFeedbackSubmission
	 */
	public function setSubjectData(\Doctrine\Common\Collections\Collection $refBaftfeedbackFeedbackSubjectData) {

		$this->subjectData = $refBaftfeedbackFeedbackSubjectData;

		return $this;

	}

	/**
	 * Get refBaftfeedbackFeedbackSubjectData
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSubjectData() {

		return $this->subjectData;

	}

	/**
	 *
	 * @return integer
	 */
	public function getId() {

		return $this->id;

	}

	/**
	 *
	 * @param integer $id
	 */
	public function setId($id) {

		$this->id = $id;
		return $this;

	}

	/**
	 *
	 * @return BaftfeedbackFeedback
	 */
	public function getRefBaftfeedbackFeedback() {

		return $this->refBaftfeedbackFeedback;

	}

	/**
	 *
	 * @param
	 *        	$refBaftfeedbackFeedback
	 */
	public function setRefBaftfeedbackFeedback($refBaftfeedbackFeedback) {

		$this->refBaftfeedbackFeedback = $refBaftfeedbackFeedback;
		return $this;

	}

	/**
	 *
	 * @return ArrayCollection
	 */
	public function getSubmitters() {

		return $this->submitters;

	}

	/**
	 *
	 * @param
	 *        	$submitters
	 */
	public function setSubmitters(\Doctrine\Common\Collections\Collection $submitters) {

		$this->submitters = $submitters;
		return $this;

	}

	/**
	 *
	 * @return ArrayCollection
	 */
	public function getStates() {

		return $this->states;

	}

	/**
	 *
	 * @param
	 *        	$submitters
	 */
	public function setStates(\Doctrine\Common\Collections\Collection $states) {

		$this->states = $states;
		return $this;

	}

	/**
	 *
	 * @return string
	 */
	public function getExpireTime() {

		return $this->expireTime;

	}

	/**
	 *
	 * @param string $expireTime
	 */
	public function setExpireTime($expireTime) {

		$this->expireTime = $expireTime;
		return $this;

	}

	/**
	 *
	 * @return string
	 */
	public function getStartTime() {

		return $this->startTime;

	}

	/**
	 *
	 * @param string $availableTime
	 */
	public function setStartTime($availableTime) {

		$this->startTime = $availableTime;
		return $this;

	}

	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackVersion
	 */
	public function getRefBaftfeedbackFeedbackVersion() {

		return $this->refBaftfeedbackFeedbackVersion;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackVersion $refBaftfeedbackFeedbackVersion
	 */
	public function setRefBaftfeedbackFeedbackVersion($refBaftfeedbackFeedbackVersion) {

		$this->refBaftfeedbackFeedbackVersion = $refBaftfeedbackFeedbackVersion;
		return $this;

	}

	/**
	 *
	 * @return the integer
	 */
	public function getContinuous() {

		return $this->continuous;

	}

	/**
	 *
	 * @param integer $continous
	 */
	public function setContinuous($continuous) {

		$this->continuous = $continuous;
		return $this;

	}


	/**
	 *
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getSubmissionData() {

		return $this->submissionData;

	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $submissionData
	 */
	public function setSubmissionData(\Doctrine\Common\Collections\ArrayCollection $submissionData) {

		$this->submissionData = $submissionData;

	}

	/**
	 * @return the $editable
	 */
	public function getEditable() {

		return $this->editable;
	}



	/**
	 * @param number $editable
	 */
	public function setEditable($editable) {

		$this->editable = $editable;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getSubmissionPeriod() {
		return $this->submissionPeriod;
	}

	/**
	 * @param string $SubmissionPeriod
	 */
	public function setSubmissionPeriod( $SubmissionPeriod) {
		$this->submissionPeriod = $SubmissionPeriod;
		return $this;
	}



}
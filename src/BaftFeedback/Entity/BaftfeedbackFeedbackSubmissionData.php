<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackFeedbackSubmissionData
 *
 * @ORM\Table(name="baftfeedback_feedback_submission_data",
 * indexes={
 * @ORM\Index(
 * name="fk_baftfeedback_question_submission_baftfeedback_form_submi_idx",
 * columns={"ref_baftfeedback_feedback_submitter_data_id"}),
 * @ORM\Index(
 * name="fk_baftfeedback_feedback_data_1_idx",
 * columns={"ref_baftfeedback_question_id"}),
 * @ORM\Index(
 * name="fk_baftfeedback_feedback_data_2_idx",
 * columns={"ref_baftfeedback_question_group_id"})
 * })
 * @ORM\Entity
 */
class BaftfeedbackFeedbackSubmissionData implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="question_field_name", type="string", length=255, nullable=false)
	 */
	private $questionFieldName;
	
	/**
	 *
	 * @var string @ORM\Column(name="value", type="text", length=65535, nullable=true)
	 */
	private $value;
	
	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmission" )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_submission_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackSubmissionId;
	
	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData" ,cascade={"persist"})
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_feedback_submitter_data_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackFeedbackSubmitterData;
	

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackQuestionGroup @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestionGroup")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_question_group_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackQuestionGroup;
	
	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackQuestion @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestion")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_question_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackQuestion;
	
	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmission" )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_feedback_submission_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackFeedbackSubmissionId;

	
	/**
	 * Set id
	 *
	 * @param integer $id        	
	 *
	 * @return BaftfeedbackFeedbackSubmissionData
	 */
	public function setId($id) {

		$this->id = $id;
		
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
	 * Set questionFieldName
	 *
	 * @param string $questionFieldName        	
	 *
	 * @return BaftfeedbackFeedbackSubmissionData
	 */
	public function setQuestionFieldName($questionFieldName) {

		$this->questionFieldName = $questionFieldName;
		
		return $this;
	
	}

	/**
	 * Get questionFieldName
	 *
	 * @return string
	 */
	public function getQuestionFieldName() {

		return $this->questionFieldName;
	
	}

	/**
	 * Set value
	 *
	 * @param string $value        	
	 *
	 * @return BaftfeedbackFeedbackSubmissionData
	 */
	public function setValue($value) {

		$this->value = $value;
		
		return $this;
	
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue() {

		return $this->value;
	
	}

	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission BaftfeedbackSubmission
	 */
	public function getRefBaftfeedbackSubmissionId() {

		return $this->refBaftfeedbackSubmissionId;
	
	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $refBaftfeedbackSubmissionId        	
	 */
	public function setRefBaftfeedbackSubmissionId(\BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $refBaftfeedbackSubmissionId) {

		$this->refBaftfeedbackSubmissionId = $refBaftfeedbackSubmissionId;
		return $this;
	
	}

	/**
	 * Set refBaftfeedbackFeedbackSubmitterData
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData $refBaftfeedbackFeedbackSubmitterData        	
	 *
	 * @return BaftfeedbackFeedbackSubmissionData
	 */
	public function setRefBaftfeedbackFeedbackSubmitterData(\BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData $refBaftfeedbackFeedbackSubmitterData = null) {

		$this->refBaftfeedbackFeedbackSubmitterData = $refBaftfeedbackFeedbackSubmitterData;
		
		return $this;
	
	}

	/**
	 * Get refBaftfeedbackFeedbackSubmitterData
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData
	 */
	public function getRefBaftfeedbackFeedbackSubmitterData() {

		return $this->refBaftfeedbackFeedbackSubmitterData;
	
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


}

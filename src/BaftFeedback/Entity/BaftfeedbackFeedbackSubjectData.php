<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackFeedbackSubjectData
 *
 * @ORM\Table(name="baftfeedback_feedback_subject_data", indexes={@ORM\Index(name="fk_baftfeedback_feedback_subject_data_baftfeedback_feedback_idx", columns={"ref_baftfeedback_feedback_id"}), @ORM\Index(name="fk_baftfeedback_feedback_subject_data_baftfeedback_question_idx", columns={"ref_baftfeedback_question_id"}), @ORM\Index(name="fk_baftfeedback_feedback_subject_data_1_idx", columns={"ref_baftfeedback_submission_id"})})
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\SubjectDataRepository")
 */
class BaftfeedbackFeedbackSubjectData implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="field_name", type="string", length=255, nullable=true)
	 */
	private $fieldName;
	
	/**
	 *
	 * @var string @ORM\Column(name="value", type="text", length=65535, nullable=true)
	 */
	private $value;
	
	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmission")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_submission_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackSubmission;
	
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
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubject @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubject")
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_subject_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackSubject;

	
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
	 * @return BaftfeedbackFeedbackSubjectData
	 */
	public function setFieldName($questionFieldName) {

		$this->fieldName = $questionFieldName;
		
		return $this;
	
	}

	/**
	 * Get questionFieldName
	 *
	 * @return string
	 */
	public function getFieldName() {

		return $this->fieldName;
	
	}

	/**
	 * Set value
	 *
	 * @param string $value        	
	 *
	 * @return BaftfeedbackFeedbackSubjectData
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
	 * Set refBaftfeedbackSubmission
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $refBaftfeedbackSubmission        	
	 *
	 * @return BaftfeedbackFeedbackSubjectData
	 */
	public function setRefBaftfeedbackSubmission($refBaftfeedbackSubmission = null) {

		$this->refBaftfeedbackSubmission = $refBaftfeedbackSubmission;
		
		return $this;
	
	}

	/**
	 * Get refBaftfeedbackSubmission
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function getRefBaftfeedbackSubmission() {

		return $this->refBaftfeedbackSubmission;
	
	}

	/**
	 * Set refBaftfeedbackFeedback
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $refBaftfeedbackFeedback        	
	 *
	 * @return BaftfeedbackFeedbackSubjectData
	 */
	public function setRefBaftfeedbackFeedback($refBaftfeedbackFeedback = null) {

		$this->refBaftfeedbackFeedback = $refBaftfeedbackFeedback;
		
		return $this;
	
	}

	/**
	 * Get refBaftfeedbackFeedback
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedback
	 */
	public function getRefBaftfeedbackFeedback() {

		return $this->refBaftfeedbackFeedback;
	
	}

	/**
	 * Set refBaftfeedbackQuestion
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubject $refBaftfeedbackQuestion        	
	 *
	 * @return BaftfeedbackFeedbackSubjectData
	 */
	public function setRefBaftfeedbackSubject($refBaftfeedbackSubject = null) {

		$this->refBaftfeedbackSubject = $refBaftfeedbackSubject;
		
		return $this;
	
	}

	/**
	 * Get refBaftfeedbackQuestion
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject
	 */
	public function getRefBaftfeedbackSubject() {

		return $this->refBaftfeedbackSubject;
	
	}


}

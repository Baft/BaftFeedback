<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackFeedbackSubmissionState
 *
 * @ORM\Table(name="baftfeedback_feedback_submission_state",
 * indexes={
 * @ORM\Index(name="fk_baftfeedback_feedback_submission_state_baftfeedback_feed_idx", columns={"ref_baftfeedback_feedback_submission_id"})
 * })
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class BaftfeedbackFeedbackSubmissionState implements BaftFeedbackEntityInterface {
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 *
	 * @var integer @ORM\Column(name="state", type="integer", nullable=true)
	 */
	private $state;
	
	/**
	 *
	 * @var string @ORM\Column(name="version", type="string", length=255, nullable=true)
	 */
	private $version;
	
	/**
	 *
	 * @var string @ORM\Column(name="description", type="text", nullable=true)
	 */
	private $description;
	
	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmission" )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="ref_baftfeedback_feedback_submission_id", referencedColumnName="id")
	 *      })
	 */
	private $refBaftfeedbackFeedbackSubmissionId;
	
	/**
	 *
	 * @var string @ORM\Column(name="json_state_data", type="text", nullable=true)
	 */
	private $jsonStateData;
	
	/**
	 *
	 * @var string @ORM\Column(name="change_time", type="string", length=255, nullable=true)
	 */
	private $changeTime;

	
	/**
	 *
	 * @return integer
	 */
	public function getId() {

		return $this->id;
	
	}

	/**
	 * Set state change time
	 *
	 * @param string $changeTime        	
	 *
	 * @return BaftfeedbackFeedbackSubmissionState
	 */
	function setChangeTime() {

	}

	/**
	 *
	 * @return string
	 */
	function getChangeTime() {

		return $this->changeTime;
	
	}

	/**
	 *
	 * @return integer
	 */
	public function getState() {

		return $this->state;
	
	}

	/**
	 *
	 * @param integer $state        	
	 * @return BaftfeedbackFeedbackSubmissionState
	 */
	public function setState($state) {

		$this->state = $state;
		return $this;
	
	}

	/**
	 * Set version
	 *
	 * @param string $version        	
	 *
	 * @return BaftfeedbackFeedbackSubmissionState
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
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function getRefBaftfeedbackFeedbackSubmissionId() {

		return $this->refBaftfeedbackFeedbackSubmissionId;
	
	}

	/**
	 *
	 * @param
	 *        	BaftfeedbackFeedbackSubmission
	 * @return BaftfeedbackFeedbackSubmissionState
	 */
	public function setRefBaftfeedbackFeedbackSubmissionId($refBaftfeedbackFeddbackSubmissionId) {

		$this->refBaftfeedbackFeedbackSubmissionId = $refBaftfeedbackFeddbackSubmissionId;
		return $this;
	
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {

		return $this->description;
	
	}

	/**
	 *
	 * @param string $description        	
	 * @return BaftfeedbackFeedbackSubmissionState
	 */
	public function setDescription($description) {

		$this->description = $description;
		return $this;
	
	}

	/**
	 *
	 * @return string
	 */
	public function getJsonStateData() {

		return $this->jsonStateData;
	
	}

	/**
	 *
	 * @param string $jsonStateData        	
	 * @return BaftfeedbackFeedbackSubmissionState
	 */
	public function setJsonStateData($jsonStateData) {

		$this->jsonStateData = $jsonStateData;
		return $this;
	
	}


}
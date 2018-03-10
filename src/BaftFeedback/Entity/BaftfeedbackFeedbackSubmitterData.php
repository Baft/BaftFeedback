<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackFeedbackSubmitterData
 *
 * @ORM\Table(name="baftfeedback_feedback_submitter_data")
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\SubmitterRepository")
 */
class BaftfeedbackFeedbackSubmitterData implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="submitter", type="string", length=255, nullable=true)
	 */
	private $submitter;
	
	/**
	 *
	 * @var string @ORM\Column(name="submitter_ip", type="string", length=255, nullable=true)
	 */
	private $submitterIp;
	
	/**
	 *
	 * @var string @ORM\Column(name="submit_time", type="string", length=255, nullable=true)
	 */
	private $submitTime;
	
	/**
	 *
	 * @var string @ORM\Column(name="start_time", type="string", length=255, nullable=true)
	 */
	private $startTime;
	
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
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData" , mappedBy="ref_baftfeedback_feedback_submitter_data_id", cascade={"all"})
	 *     
	 */
	private $submitedData;

	function __construct() {

		$this->submitedData = new \Doctrine\Common\Collections\ArrayCollection ();
	
	}

	public function __toString() {

		return $this->id;
	
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
	 * Set submitter
	 *
	 * @param string $submitter        	
	 *
	 * @return BaftfeedbackFeedbackSubmitterData
	 */
	public function setSubmitter($submitter) {

		$this->submitter = $submitter;
		
		return $this;
	
	}

	/**
	 * Get submitter
	 *
	 * @return string
	 */
	public function getSubmitter() {

		return $this->submitter;
	
	}

	/**
	 * Set submitterIp
	 *
	 * @param string $submitterIp        	
	 *
	 * @return BaftfeedbackFeedbackSubmitterData
	 */
	public function setSubmitterIp($submitterIp) {

		$this->submitterIp = $submitterIp;
		
		return $this;
	
	}

	/**
	 * Get submitterIp
	 *
	 * @return string
	 */
	public function getSubmitterIp() {

		return $this->submitterIp;
	
	}

	/**
	 * Set submitTime
	 *
	 * @param string $submitTime        	
	 *
	 * @return BaftfeedbackFeedbackSubmitterData
	 */
	public function setSubmitTime($submitTime) {

		$this->submitTime = $submitTime;
		
		return $this;
	
	}

	/**
	 * Get submitTime
	 *
	 * @return string
	 */
	public function getSubmitTime() {

		return $this->submitTime;
	
	}

	/**
	 * Set startTime
	 *
	 * @param string $startTime        	
	 *
	 * @return BaftfeedbackFeedbackSubmitterData
	 */
	public function setStartTime($startTime) {

		$this->startTime = $startTime;
		
		return $this;
	
	}

	/**
	 * Get startTime
	 *
	 * @return string
	 */
	public function getStartTime() {

		return $this->startTime;
	
	}

	/**
	 * Set final
	 *
	 * @param integer $startTime        	
	 *
	 * @return BaftfeedbackFeedbackSubmitterData
	 */
	public function setFinal($startTime) {

		$this->startTime = $startTime;
		
		return $this;
	
	}

	/**
	 * Get final
	 *
	 * @return integer
	 */
	public function getFinal() {

		return $this->startTime;
	
	}

	
	/**
	 *
	 * @return BaftfeedbackSubmission
	 */
	public function getRefBaftfeedbackSubmissionId() {

		return $this->refBaftfeedbackSubmissionId;
	
	}

	/**
	 *
	 * @param
	 *        	$refBaftfeedbackSubmissionId
	 */
	public function setRefBaftfeedbackSubmissionId($refBaftfeedbackSubmissionId) {

		$this->refBaftfeedbackSubmissionId = $refBaftfeedbackSubmissionId;
		return $this;
	
	}

	/**
	 *
	 * @return ArrayCollection
	 */
	public function getSubmitedData() {

		return $this->submitedData;
	
	}

	/**
	 *
	 * @param
	 *        	$submitedData
	 */
	public function setSubmitedData(\Doctrine\Common\Collections\Collection $submitedData) {

		$this->submitedData = $submitedData;
		return $this;
	
	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData $submitedData        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData
	 */
	public function addSubmitedData(\BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData $submitedData) {

		if (! $this->submitedData->contains ( submitedData )) {
			$this->submitedData->add ( $submitedData );
		}
		return $this;
	
	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData $submitedData        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmitterData
	 */
	public function removeSubmitedData(\BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionData $submitedData) {

		if (! $this->submitedData->contains ( submitedData )) {
			$this->submitedData->removeElement ( $submitedData );
		}
		return $this;
	
	}


}

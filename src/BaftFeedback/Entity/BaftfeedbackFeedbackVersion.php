<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BaftfeedbackFeedbackVersion
 *
 * @ORM\Table(name="baftfeedback_feedback_version", indexes={@ORM\Index(name="fk_baftfeedback_feedback_version_baftfeedback_feedback1_idx", columns={"ref_baftfeedback_feedback_id"})})
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\feedbackVersionRepository")
 */
class BaftfeedbackFeedbackVersion implements BaftFeedbackEntityInterface {
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
	 * @var string @ORM\Column(name="label", type="string", length=255, nullable=true)
	 */
	private $label;
	
	/**
	 *
	 * @var string @ORM\Column(name="description", type="text", length=65535, nullable=true)
	 */
	private $description;
	
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
	 * @var integer @ORM\Column(name="disable", type="integer", nullable=false, options={"default":0})
	 */
	private $disable;
	
	/**
	 * @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubmission", mappedBy="refBaftfeedbackFeedbackVersion" )
	 */
	private $submissions;

	public function __construct() {

		$this->submissions = new ArrayCollection ();
	
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
	 * Set version
	 *
	 * @param string $version        	
	 *
	 * @return BaftfeedbackFeedbackVersion
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
	 * Set label
	 *
	 * @param string $label        	
	 *
	 * @return BaftfeedbackFeedbackVersion
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
	 * Set description
	 *
	 * @param string $description        	
	 *
	 * @return BaftfeedbackFeedbackVersion
	 */
	public function setDescription($description) {

		$this->description = $description;
		
		return $this;
	
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription() {

		return $this->description;
	
	}

	/**
	 * Set refBaftfeedbackFeedback
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $refBaftfeedbackFeedback        	
	 *
	 * @return BaftfeedbackFeedbackVersion
	 */
	public function setRefBaftfeedbackFeedback(\BaftFeedback\Entity\BaftfeedbackFeedback $refBaftfeedbackFeedback = null) {

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
	 * Get submissions of this version
	 *
	 * @return ArrayCollection
	 */
	public function getSubmissions() {

		return $this->submissions;
	
	}

	/**
	 * Set disable
	 *
	 * @param integer $disable        	
	 *
	 * @return BaftfeedbackFeedbackVersion
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


}

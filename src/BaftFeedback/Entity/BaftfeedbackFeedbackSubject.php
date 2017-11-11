<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackFeedbackSubject
 *
 * @ORM\Table(name="baftfeedback_feedback_subject", indexes={@ORM\Index(name="fk_baftfeedback_feedback_subject_baftfeedback_question1_idx", columns={"ref_baftfeedback_question_id"}), @ORM\Index(name="fk_baftfeedback_feedback_subject_baftfeedback_feedback1_idx", columns={"ref_baftfeedback_feedback_id"})})
 * @ORM\Entity(repositoryClass="BaftFeedback\Model\SubjectRepository")
 */
class BaftfeedbackFeedbackSubject implements BaftFeedbackEntityInterface {
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="name", type="string", length=255, nullable=true)
	 */
	private $name;
	
	/**
	 *
	 * @var string @ORM\Column(name="label", type="string", length=255, nullable=true)
	 */
	private $label;
	
	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedback", mappedBy="subject")
	 *     
	 */
	private $refBaftfeedbackFeedback;
	
	/**
	 *
	 * @var string @ORM\Column(name="ref_Fieldset", type="string", length=255, nullable=true)
	 */
	private $refFieldset;
	
	/**
	 *
	 * @var int @ORM\Column(name="subject_order", type="integer", nullable=true , options={"default":1})
	 */
	private $subjectOrder;
	

	/**
	 *
	 * @var string @ORM\Column(name="json_Fieldset_config", type="text", nullable=true)
	 */
	private $jsonFieldsetConfig;
	
	/**
	 *
	 * @var string @ORM\Column(name="json_subject_config", type="text", nullable=true)
	 */
	private $jsonSubjectConfig;
	
	/**
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection @ORM\OneToMany(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData" , mappedBy="refBaftfeedbackSubject", cascade={"all"})
	 *      @ORM\OrderBy({"id" = "ASC"})
	 */
	private $subjectData;

	function __construct() {

		$this->subjectData = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->refBaftfeedbackFeedback = new \Doctrine\Common\Collections\ArrayCollection ();
	
	}

	public function getNamespace() {

		if (! isset ( $this->id ))
			throw new \Exception ( __METHOD__ . 'subject entity dose not initialized ' );
		return $this->getRefBaftfeedbackFeedback ()->getNamespace () . "_subject";
	
	}

	/**
	 *
	 * @return the $subjectData
	 */
	public function getSubjectData() {

		return $this->subjectData;
	
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $subjectData        	
	 */
	public function setSubjectData($subjectData) {

		$this->subjectData = $subjectData;
	
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
	 * Set refBaftfeedbackFeedback
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $refBaftfeedbackFeedback        	
	 *
	 * @return BaftfeedbackFeedbackSubject
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
	 *
	 * @param string $Fieldset        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject
	 */
	public function setRefFieldset($Fieldset) {

		$this->refFieldset = $Fieldset;
		return $this;
	
	}

	/**
	 *
	 * @param string $Fieldset        	
	 */
	public function getRefFieldset() {

		return $this->refFieldset;
	
	}

	/**
	 *
	 * @param string $json        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject
	 */
	public function setJsonFieldsetConfig($json) {

		$this->jsonFieldsetConfig = $json;
		return $this;
	
	}

	/**
	 *
	 * @return string
	 */
	public function getJsonFieldsetConfig() {

		return $this->jsonFieldsetConfig;
	
	}

	/**
	 *
	 * @param string $json        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject
	 */
	public function setJsonSubjectConfig($json) {

		$this->jsonSubjectConfig = $json;
		return $this;
	
	}

	/**
	 *
	 * @return string
	 */
	public function getJsonSubjectConfig() {

		return $this->jsonSubjectConfig;
	
	}

	/**
	 *
	 * @return int
	 */
	public function getSubjectOrder() {

		return $this->subjectOrder;
	
	}

	/**
	 *
	 * @param int $order        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubject
	 */
	public function setSubjectOrder($order) {

		$this->subjectOrder = $order;
		return $this;
	
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;
	
	}

	/**
	 *
	 * @param string $name        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedback
	 */
	public function setName($name) {

		$this->name = $name;
		return $this;
	
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {

		return $this->label;
	
	}

	/**
	 *
	 * @param string $label        	
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedback
	 */
	public function setLabel($label) {

		$this->label = $label;
		return $this;
	
	}


}

<?php

namespace BaftFeedback\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaftfeedbackFeedbackGroups
 *
 * @ORM\Table(name="baftfeedback_feedback_groups", indexes={@ORM\Index(name="fk_baftfeedback_form_groups_baftfeedback_form1_idx", columns={"ref_baftfeedback_feedback_id"}), @ORM\Index(name="fk_baftfeedback_form_groups_baftfeedback_question_group1_idx", columns={"ref_baftfeedback_question_group_id"})})
 * @ORM\Entity
 */
class #BaftfeedbackFeedbackGroups implements BaftFeedbackEntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", type="string", length=255, nullable=true)
     */
    private $groupName;

    /**
     * @var string
     *
     * @ORM\Column(name="group_label", type="text", length=65535, nullable=true)
     */
    private $groupLabel;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_order", type="integer", nullable=true)
     */
    private $groupOrder;

    /**
     * @var \BaftFeedback\Entity\BaftfeedbackFeedback
     *
     * @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackFeedback")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_baftfeedback_feedback_id", referencedColumnName="id")
     * })
     */
    private $refBaftfeedbackFeedback;

    /**
     * @var \BaftFeedback\Entity\BaftfeedbackQuestionGroup
     *
     * @ORM\ManyToOne(targetEntity="BaftFeedback\Entity\BaftfeedbackQuestionGroup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_baftfeedback_question_group_id", referencedColumnName="id")
     * })
     */
    private $refBaftfeedbackQuestionGroup;


    /**
     * Get id
     *
     * @return integer
     */
    public 
function getId() {

	return $this->id;

}

    /**
     * Set groupName
     *
     * @param string $groupName
     *
     * @return BaftfeedbackFeedbackGroups
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * Get groupName
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Set groupLabel
     *
     * @param string $groupLabel
     *
     * @return BaftfeedbackFeedbackGroups
     */
    public function setGroupLabel($groupLabel)
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * Get groupLabel
     *
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->groupLabel;
    }

    /**
     * Set groupOrder
     *
     * @param integer $groupOrder
     *
     * @return BaftfeedbackFeedbackGroups
     */
    public function setGroupOrder($groupOrder)
    {
        $this->groupOrder = $groupOrder;

        return $this;
    }

    /**
     * Get groupOrder
     *
     * @return integer
     */
    public function getGroupOrder()
    {
        return $this->groupOrder;
    }

    /**
     * Set refBaftfeedbackFeedback
     *
     * @param \BaftFeedback\Entity\BaftfeedbackFeedback $refBaftfeedbackFeedback
     *
     * @return BaftfeedbackFeedbackGroups
     */
    public function setRefBaftfeedbackFeedback(\BaftFeedback\Entity\BaftfeedbackFeedback $refBaftfeedbackFeedback = null)
    {
        $this->refBaftfeedbackFeedback = $refBaftfeedbackFeedback;

        return $this;
    }

    /**
     * Get refBaftfeedbackFeedback
     *
     * @return \BaftFeedback\Entity\BaftfeedbackFeedback
     */
    public function getRefBaftfeedbackFeedback()
    {
        return $this->refBaftfeedbackFeedback;
    }

    /**
     * Set refBaftfeedbackQuestionGroup
     *
     * @param \BaftFeedback\Entity\BaftfeedbackQuestionGroup $refBaftfeedbackQuestionGroup
     *
     * @return BaftfeedbackFeedbackGroups
     */
    public function setRefBaftfeedbackQuestionGroup(\BaftFeedback\Entity\BaftfeedbackQuestionGroup $refBaftfeedbackQuestionGroup = null)
    {
        $this->refBaftfeedbackQuestionGroup = $refBaftfeedbackQuestionGroup;

        return $this;
    }

    /**
     * Get refBaftfeedbackQuestionGroup
     *
     * @return \BaftFeedback\Entity\BaftfeedbackQuestionGroup
     */
    public function getRefBaftfeedbackQuestionGroup()
    {
    	$this->refBaftfeedbackQuestionGroup->setName($this->getGroupName());
        return $this->refBaftfeedbackQuestionGroup;
    }
}

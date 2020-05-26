<?php

// This file is part of the ProEthos Software. 
// 
// Copyright 2013, PAHO. All rights reserved. You can redistribute it and/or modify
// ProEthos under the terms of the ProEthos License as published by PAHO, which
// restricts commercial use of the Software. 
// 
// ProEthos is distributed in the hope that it will be useful, but WITHOUT ANY
// WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
// PARTICULAR PURPOSE. See the ProEthos License for more details. 
// 
// You should have received a copy of the ProEthos License along with the ProEthos
// Software. If not, see
// https://github.com/bireme/proethos2/blob/master/LICENSE.txt


namespace Proethos2\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="protocol_committee_revision")
 * @ORM\Entity
 */
class ProtocolCommitteeRevision extends Base
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\ManyToOne(targetEntity="User", inversedBy="members") 
     */ 
    private $member;

    /**
     * @ORM\Column(type="boolean")
     */
    private $answered = false;

    /**
     * @ORM\ManyToOne(targetEntity="Protocol", inversedBy="revision")
     * @ORM\JoinColumn(name="protocol_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $protocol;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_final_revision = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $rejected = false;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $reject_reason;

    /**
     * @var string
     *
     * @ORM\Column(name="positive_aspects", type="text", nullable=true)
     */
    private $positive_aspects;

    /**
     * @var string
     *
     * @ORM\Column(name="negative_aspects", type="text", nullable=true)
     */
    private $negative_aspects;

    /**
     * @var string
     *
     * @ORM\Column(name="other_comments", type="text", nullable=true)
     */
    private $other_comments;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accept_journal", type="integer", nullable=true)
     */
    private $accept_journal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accept_conditions", type="boolean", nullable=true)
     */
    private $accept_conditions;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set answered
     *
     * @param boolean $answered
     *
     * @return ProtocolRevision
     */
    public function setAnswered($answered)
    {
        $this->answered = $answered;

        return $this;
    }

    /**
     * Get answered
     *
     * @return boolean
     */
    public function getAnswered()
    {
        return $this->answered;
    }

    /**
     * Set member
     *
     * @param \Proethos2\ModelBundle\Entity\User $member
     *
     * @return ProtocolRevision
     */
    public function setMember(\Proethos2\ModelBundle\Entity\User $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \Proethos2\ModelBundle\Entity\User
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set protocol
     *
     * @param \Proethos2\ModelBundle\Entity\Protocol $protocol
     *
     * @return ProtocolRevision
     */
    public function setProtocol(\Proethos2\ModelBundle\Entity\Protocol $protocol = null)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return \Proethos2\ModelBundle\Entity\Protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Set isFinalRevision
     *
     * @param boolean $isFinalRevision
     *
     * @return ProtocolRevision
     */
    public function setIsFinalRevision($isFinalRevision)
    {
        $this->is_final_revision = $isFinalRevision;

        return $this;
    }

    /**
     * Get isFinalRevision
     *
     * @return boolean
     */
    public function getIsFinalRevision()
    {
        return $this->is_final_revision;
    }

    /**
     * Set positiveAspects
     *
     * @param string $positiveAspects
     *
     * @return ProtocolCommitteeRevision
     */
    public function setPositiveAspects($positiveAspects)
    {
        $this->positive_aspects = $positiveAspects;

        return $this;
    }

    /**
     * Get positiveAspects
     *
     * @return string
     */
    public function getPositiveAspects()
    {
        return $this->positive_aspects;
    }

    /**
     * Set negativeAspects
     *
     * @param string $negativeAspects
     *
     * @return ProtocolCommitteeRevision
     */
    public function setNegativeAspects($negativeAspects)
    {
        $this->negative_aspects = $negativeAspects;

        return $this;
    }

    /**
     * Get negativeAspects
     *
     * @return string
     */
    public function getNegativeAspects()
    {
        return $this->negative_aspects;
    }

    /**
     * Set otherComments
     *
     * @param string $otherComments
     *
     * @return ProtocolCommitteeRevision
     */
    public function setOtherComments($otherComments)
    {
        $this->other_comments = $otherComments;

        return $this;
    }

    /**
     * Get otherComments
     *
     * @return string
     */
    public function getOtherComments()
    {
        return $this->other_comments;
    }

    /**
     * Set acceptJournal
     *
     * @param integer $acceptJournal
     *
     * @return ProtocolCommitteeRevision
     */
    public function setAcceptJournal($acceptJournal)
    {
        $this->accept_journal = $acceptJournal;

        return $this;
    }

    /**
     * Get acceptJournal
     *
     * @return integer
     */
    public function getAcceptJournal()
    {
        return $this->accept_journal;
    }

    /**
     * Set acceptConditions
     *
     * @param boolean $acceptConditions
     *
     * @return ProtocolCommitteeRevision
     */
    public function setAcceptConditions($acceptConditions)
    {
        $this->accept_conditions = $acceptConditions;

        return $this;
    }

    /**
     * Get acceptConditions
     *
     * @return boolean
     */
    public function getAcceptConditions()
    {
        return $this->accept_conditions;
    }

    /**
     * Set rejected
     *
     * @param boolean $rejected
     *
     * @return ProtocolCommitteeRevision
     */
    public function setRejected($rejected)
    {
        $this->rejected = $rejected;

        return $this;
    }

    /**
     * Get rejected
     *
     * @return boolean
     */
    public function getRejected()
    {
        return $this->rejected;
    }

    /**
     * Set rejectReason
     *
     * @param string $rejectReason
     *
     * @return ProtocolCommitteeRevision
     */
    public function setRejectReason($rejectReason)
    {
        $this->reject_reason = $rejectReason;

        return $this;
    }

    /**
     * Get rejectReason
     *
     * @return string
     */
    public function getRejectReason()
    {
        return $this->reject_reason;
    }
}

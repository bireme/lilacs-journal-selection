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
 * @ORM\Table(name="protocol_adhoc_revision")
 * @ORM\Entity
 */
class ProtocolAdhocRevision extends Base
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
     * @ORM\Column(type="string", length=510, nullable=true)
     */
    private $other_reject_reason;

    /**
     * @var string
     *
     * @ORM\Column(name="editorial_team", type="text", nullable=true)
     */
    private $editorial_team;

    /**
     * @var string
     *
     * @ORM\Column(name="content_a", type="text", nullable=true)
     */
    private $content_a;

    /**
     * @var string
     *
     * @ORM\Column(name="content_b", type="text", nullable=true)
     */
    private $content_b;

    /**
     * @var string
     *
     * @ORM\Column(name="content_c", type="text", nullable=true)
     */
    private $content_c;

    /**
     * @var string
     *
     * @ORM\Column(name="content_d", type="text", nullable=true)
     */
    private $content_d;

    /**
     * @var string
     *
     * @ORM\Column(name="content_e", type="text", nullable=true)
     */
    private $content_e;

    /**
     * @var string
     *
     * @ORM\Column(name="content_f", type="text", nullable=true)
     */
    private $content_f;

    /**
     * @var string
     *
     * @ORM\Column(name="content_g", type="text", nullable=true)
     */
    private $content_g;

    /**
     * @var string
     *
     * @ORM\Column(name="content_h", type="text", nullable=true)
     */
    private $content_h;

    /**
     * @var string
     *
     * @ORM\Column(name="content_i", type="text", nullable=true)
     */
    private $content_i;

    /**
     * @var string
     *
     * @ORM\Column(name="content_j", type="text", nullable=true)
     */
    private $content_j;

    /**
     * @var string
     *
     * @ORM\Column(name="content_k", type="text", nullable=true)
     */
    private $content_k;

    /**
     * @var string
     *
     * @ORM\Column(name="content_l", type="text", nullable=true)
     */
    private $content_l;

    /**
     * @var string
     *
     * @ORM\Column(name="content_m", type="text", nullable=true)
     */
    private $content_m;

    /**
     * @var string
     *
     * @ORM\Column(name="peer_arbitration_a", type="text", nullable=true)
     */
    private $peer_arbitration_a;

    /**
     * @var string
     *
     * @ORM\Column(name="peer_arbitration_b", type="text", nullable=true)
     */
    private $peer_arbitration_b;

    /**
     * @var string
     *
     * @ORM\Column(name="peer_arbitration_c", type="text", nullable=true)
     */
    private $peer_arbitration_c;

    /**
     * @var boolean
     *
     * @ORM\Column(name="journal_concept", type="integer", nullable=true)
     */
    private $journal_concept;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_density", type="text", nullable=true)
     */
    private $journal_density;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_relevance", type="text", nullable=true)
     */
    private $journal_relevance;

    /**
     * @var string
     *
     * @ORM\Column(name="other_comments", type="text", nullable=true)
     */
    private $other_comments;

    /**
     * @var string
     *
     * @ORM\Column(name="other_journals", type="text", nullable=true)
     */
    private $other_journals;

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
     * Set editorialTeam
     *
     * @param string $editorialTeam
     *
     * @return ProtocolAdhocRevision
     */
    public function setEditorialTeam($editorialTeam)
    {
        $this->editorial_team = $editorialTeam;

        return $this;
    }

    /**
     * Get editorialTeam
     *
     * @return string
     */
    public function getEditorialTeam()
    {
        return $this->editorial_team;
    }

    /**
     * Set contentA
     *
     * @param string $contentA
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentA($contentA)
    {
        $this->content_a = $contentA;

        return $this;
    }

    /**
     * Get contentA
     *
     * @return string
     */
    public function getContentA()
    {
        return $this->content_a;
    }

    /**
     * Set contentB
     *
     * @param string $contentB
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentB($contentB)
    {
        $this->content_b = $contentB;

        return $this;
    }

    /**
     * Get contentB
     *
     * @return string
     */
    public function getContentB()
    {
        return $this->content_b;
    }

    /**
     * Set contentC
     *
     * @param string $contentC
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentC($contentC)
    {
        $this->content_c = $contentC;

        return $this;
    }

    /**
     * Get contentC
     *
     * @return string
     */
    public function getContentC()
    {
        return $this->content_c;
    }

    /**
     * Set contentD
     *
     * @param string $contentD
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentD($contentD)
    {
        $this->content_d = $contentD;

        return $this;
    }

    /**
     * Get contentD
     *
     * @return string
     */
    public function getContentD()
    {
        return $this->content_d;
    }

    /**
     * Set peerArbitrationA
     *
     * @param string $peerArbitrationA
     *
     * @return ProtocolAdhocRevision
     */
    public function setPeerArbitrationA($peerArbitrationA)
    {
        $this->peer_arbitration_a = $peerArbitrationA;

        return $this;
    }

    /**
     * Get peerArbitrationA
     *
     * @return string
     */
    public function getPeerArbitrationA()
    {
        return $this->peer_arbitration_a;
    }

    /**
     * Set peerArbitrationB
     *
     * @param string $peerArbitrationB
     *
     * @return ProtocolAdhocRevision
     */
    public function setPeerArbitrationB($peerArbitrationB)
    {
        $this->peer_arbitration_b = $peerArbitrationB;

        return $this;
    }

    /**
     * Get peerArbitrationB
     *
     * @return string
     */
    public function getPeerArbitrationB()
    {
        return $this->peer_arbitration_b;
    }

    /**
     * Set peerArbitrationC
     *
     * @param string $peerArbitrationC
     *
     * @return ProtocolAdhocRevision
     */
    public function setPeerArbitrationC($peerArbitrationC)
    {
        $this->peer_arbitration_c = $peerArbitrationC;

        return $this;
    }

    /**
     * Get peerArbitrationC
     *
     * @return string
     */
    public function getPeerArbitrationC()
    {
        return $this->peer_arbitration_c;
    }

    /**
     * Set journalConcept
     *
     * @param integer $journalConcept
     *
     * @return ProtocolAdhocRevision
     */
    public function setJournalConcept($journalConcept)
    {
        $this->journal_concept = $journalConcept;

        return $this;
    }

    /**
     * Get journalConcept
     *
     * @return integer
     */
    public function getJournalConcept()
    {
        return $this->journal_concept;
    }

    /**
     * Set journalRelevance
     *
     * @param string $journalRelevance
     *
     * @return ProtocolAdhocRevision
     */
    public function setJournalRelevance($journalRelevance)
    {
        $this->journal_relevance = $journalRelevance;

        return $this;
    }

    /**
     * Get journalRelevance
     *
     * @return string
     */
    public function getJournalRelevance()
    {
        return $this->journal_relevance;
    }

    /**
     * Set otherComments
     *
     * @param string $otherComments
     *
     * @return ProtocolAdhocRevision
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
     * Set acceptConditions
     *
     * @param boolean $acceptConditions
     *
     * @return ProtocolAdhocRevision
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
     * Set contentE
     *
     * @param string $contentE
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentE($contentE)
    {
        $this->content_e = $contentE;

        return $this;
    }

    /**
     * Get contentE
     *
     * @return string
     */
    public function getContentE()
    {
        return $this->content_e;
    }

    /**
     * Set contentF
     *
     * @param string $contentF
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentF($contentF)
    {
        $this->content_f = $contentF;

        return $this;
    }

    /**
     * Get contentF
     *
     * @return string
     */
    public function getContentF()
    {
        return $this->content_f;
    }

    /**
     * Set contentG
     *
     * @param string $contentG
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentG($contentG)
    {
        $this->content_g = $contentG;

        return $this;
    }

    /**
     * Get contentG
     *
     * @return string
     */
    public function getContentG()
    {
        return $this->content_g;
    }

    /**
     * Set contentH
     *
     * @param string $contentH
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentH($contentH)
    {
        $this->content_h = $contentH;

        return $this;
    }

    /**
     * Get contentH
     *
     * @return string
     */
    public function getContentH()
    {
        return $this->content_h;
    }

    /**
     * Set contentI
     *
     * @param string $contentI
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentI($contentI)
    {
        $this->content_i = $contentI;

        return $this;
    }

    /**
     * Get contentI
     *
     * @return string
     */
    public function getContentI()
    {
        return $this->content_i;
    }

    /**
     * Set contentJ
     *
     * @param string $contentJ
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentJ($contentJ)
    {
        $this->content_j = $contentJ;

        return $this;
    }

    /**
     * Get contentJ
     *
     * @return string
     */
    public function getContentJ()
    {
        return $this->content_j;
    }

    /**
     * Set contentK
     *
     * @param string $contentK
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentK($contentK)
    {
        $this->content_k = $contentK;

        return $this;
    }

    /**
     * Get contentK
     *
     * @return string
     */
    public function getContentK()
    {
        return $this->content_k;
    }

    /**
     * Set contentL
     *
     * @param string $contentL
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentL($contentL)
    {
        $this->content_l = $contentL;

        return $this;
    }

    /**
     * Get contentL
     *
     * @return string
     */
    public function getContentL()
    {
        return $this->content_l;
    }

    /**
     * Set contentM
     *
     * @param string $contentM
     *
     * @return ProtocolAdhocRevision
     */
    public function setContentM($contentM)
    {
        $this->content_m = $contentM;

        return $this;
    }

    /**
     * Get contentM
     *
     * @return string
     */
    public function getContentM()
    {
        return $this->content_m;
    }

    /**
     * Set journalDensity
     *
     * @param string $journalDensity
     *
     * @return ProtocolAdhocRevision
     */
    public function setJournalDensity($journalDensity)
    {
        $this->journal_density = $journalDensity;

        return $this;
    }

    /**
     * Get journalDensity
     *
     * @return string
     */
    public function getJournalDensity()
    {
        return $this->journal_density;
    }

    /**
     * Set otherJournals
     *
     * @param string $otherJournals
     *
     * @return ProtocolAdhocRevision
     */
    public function setOtherJournals($otherJournals)
    {
        $this->other_journals = $otherJournals;

        return $this;
    }

    /**
     * Get otherJournals
     *
     * @return string
     */
    public function getOtherJournals()
    {
        return $this->other_journals;
    }

    /**
     * Set rejected
     *
     * @param boolean $rejected
     *
     * @return ProtocolAdhocRevision
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
     * @return ProtocolAdhocRevision
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

    /**
     * Set otherRejectReason
     *
     * @param string $otherRejectReason
     *
     * @return ProtocolAdhocRevision
     */
    public function setOtherRejectReason($otherRejectReason)
    {
        $this->other_reject_reason = $otherRejectReason;

        return $this;
    }

    /**
     * Get otherRejectReason
     *
     * @return string
     */
    public function getOtherRejectReason()
    {
        return $this->other_reject_reason;
    }
}

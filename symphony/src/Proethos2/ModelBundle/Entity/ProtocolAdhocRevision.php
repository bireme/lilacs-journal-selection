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
}

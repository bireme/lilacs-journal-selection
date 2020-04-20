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
 * @ORM\Table(name="protocol_adhoc_revision_evaluation")
 * @ORM\Entity
 */
class ProtocolAdhocRevisionEvaluation extends Base
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="members") 
     */ 
    private $reviewer;

    /**
     * @ORM\ManyToOne(targetEntity="Protocol", inversedBy="revision")
     * @ORM\JoinColumn(name="protocol_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $protocol;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_valid;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank 
     */
    private $relevance;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank 
     */
    private $pertinence;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank 
     */
    private $clarity;

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
     * Set reviewer
     *
     * @param \Proethos2\ModelBundle\Entity\User $reviewer
     *
     * @return ProtocolAdhocRevisionEvaluation
     */
    public function setReviewer(\Proethos2\ModelBundle\Entity\User $reviewer = null)
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    /**
     * Get reviewer
     *
     * @return \Proethos2\ModelBundle\Entity\User
     */
    public function getReviewer()
    {
        return $this->reviewer;
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
     * Set relevance
     *
     * @param integer $relevance
     *
     * @return ProtocolAdhocRevisionEvaluation
     */
    public function setRelevance($relevance)
    {
        $this->relevance = $relevance;

        return $this;
    }

    /**
     * Get relevance
     *
     * @return integer
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * Set pertinence
     *
     * @param integer $pertinence
     *
     * @return ProtocolAdhocRevisionEvaluation
     */
    public function setPertinence($pertinence)
    {
        $this->pertinence = $pertinence;

        return $this;
    }

    /**
     * Get pertinence
     *
     * @return integer
     */
    public function getPertinence()
    {
        return $this->pertinence;
    }

    /**
     * Set clarity
     *
     * @param integer $clarity
     *
     * @return ProtocolAdhocRevisionEvaluation
     */
    public function setClarity($clarity)
    {
        $this->clarity = $clarity;

        return $this;
    }

    /**
     * Get clarity
     *
     * @return integer
     */
    public function getClarity()
    {
        return $this->clarity;
    }

    /**
     * Set isValid
     *
     * @param boolean $isValid
     *
     * @return ProtocolAdhocRevisionEvaluation
     */
    public function setIsValid($isValid)
    {
        $this->is_valid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return boolean
     */
    public function getIsValid()
    {
        return $this->is_valid;
    }
}

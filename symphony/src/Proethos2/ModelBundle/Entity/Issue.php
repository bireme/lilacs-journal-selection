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
use Cocur\Slugify\Slugify;

/**
 * Issue
 *
 * @ORM\Table(name="submission_issue")
 * @ORM\Entity
 */
class Issue extends Base
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Submission")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank
     */
    private $submission;

    /**
     * @ORM\Column(type="integer")
     */
    private $submission_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $volume;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="string")
     */
    private $full_text;

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
     * Set submission
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $submission
     *
     * @return SubmissionUpload
     */
    public function setSubmission(\Proethos2\ModelBundle\Entity\Submission $submission = null)
    {
        $this->submission = $submission;

        return $this;
    }

    /**
     * Get submission
     *
     * @return \Proethos2\ModelBundle\Entity\Submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Set submissionNumber
     *
     * @param integer $submissionNumber
     *
     * @return SubmissionUpload
     */
    public function setSubmissionNumber($submissionNumber)
    {
        $this->submission_number = $submissionNumber;

        return $this;
    }

    /**
     * Get submissionNumber
     *
     * @return integer
     */
    public function getSubmissionNumber()
    {
        return $this->submission_number;
    }

    /**
     * Set user
     *
     * @param \Proethos2\ModelBundle\Entity\User $user
     *
     * @return SubmissionUpload
     */
    public function setUser(\Proethos2\ModelBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Proethos2\ModelBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set volume
     *
     * @param string $volume
     *
     * @return Issue
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return string
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Issue
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Issue
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set fullText
     *
     * @param string $fullText
     *
     * @return Issue
     */
    public function setFullText($fullText)
    {
        $this->full_text = $fullText;

        return $this;
    }

    /**
     * Get fullText
     *
     * @return string
     */
    public function getFullText()
    {
        return $this->full_text;
    }
}

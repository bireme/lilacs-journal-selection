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
 * Submission
 *
 * @ORM\Table(name="submission")
 * @ORM\Entity(repositoryClass="Proethos2\ModelBundle\Repository\SubmissionRepository")
 */
class Submission extends Base
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
     * @var Protocol
     *
     * @ORM\ManyToOne(targetEntity="Protocol", inversedBy="submissions")
     * @ORM\JoinColumn(name="protocol_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank
     */
    private $protocol;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_translation", type="boolean")
     */
    private $is_translation = false;

    /**
     * @var Submission
     *
     * @ORM\ManyToOne(targetEntity="Submission")
     * @ORM\JoinColumn(name="original_submission_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $original_submission;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $owner;

    /**
     * @var Team
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="users")
     * @ORM\JoinTable(name="submission_user")
     */
    private $team;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="original_submission", cascade={"remove"})
     */
    private $translations;

    /**
     * @ORM\Column(name="number", type="integer")
     * @Assert\NotBlank
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=510)
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="short_title", type="string", length=510)
     */
    private $shortTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="acronym_title", type="string", length=255)
     */
    private $acronymTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="previous_title", type="string", length=510)
     */
    private $previousTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_title", type="string", length=510)
     */
    private $additionalTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="issn", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $issn;

    /**
     * @var string
     *
     * @ORM\Column(name="issn_online", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $issnOnline;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=255)
     * @Assert\NotBlank
     */
    private $frequency;

    /**
     * @var string
     *
     * @ORM\Column(name="standards", type="string", length=510)
     * @Assert\NotBlank
     */
    private $standards;

    /**
     * @var string
     *
     * @ORM\Column(name="bibliographic_subtitle", type="string", length=510)
     */
    private $bibliographicSubtitle;

    /**
     * @ORM\Column(name="creation_year", type="integer")
     * @Assert\NotBlank
     */
    private $creationYear;

    /**
     * @ORM\Column(name="lifetime", type="string", length=255)
     */
    private $lifetime;

    /**
     * @var string
     *
     * @ORM\Column(name="support", type="string", length=255)
     * @Assert\NotBlank
     */
    private $support;

    /**
     * @ORM\Column(name="full_text", type="string", length=255)
     * @Assert\NotBlank
     */
    private $full_text;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string")
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="website_info", type="string")
     */
    private $websiteInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="social_networks_info", type="text", nullable=true)
     */
    private $socialNetworksInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="mission", type="text", nullable=true)
     * @Assert\NotBlank
     */
    private $mission;

    /**
     * @var PublicationType
     *
     * @ORM\ManyToMany(targetEntity="PublicationType", inversedBy="submissions")
     * @ORM\JoinTable(name="submission_publication_type")
     * @Assert\NotBlank
     */
    private $publication_type;

    /**
     * @var Language
     *
     * @ORM\ManyToMany(targetEntity="Language", inversedBy="submissions")
     * @ORM\JoinTable(name="submission_language")
     * @Assert\NotBlank
     */
    private $language;

    /**
     * @var ThematicArea
     *
     * @ORM\ManyToOne(targetEntity="ThematicArea")
     * @ORM\JoinColumn(name="thematic_area_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $thematicArea;

    /**
     * @var Specialty
     *
     * @ORM\ManyToOne(targetEntity="Specialty")
     * @ORM\JoinColumn(name="specialty_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $specialty;

    /**
     * @ORM\Column(name="qualis", type="string", length=255)
     */
    private $qualis;

    /**
     * @var boolean
     *
     * @ORM\Column(name="funded_by_cnpq", type="boolean")
     * @Assert\NotBlank
     */
    private $funded_by_cnpq;

    /**
     * @ORM\Column(name="funders", type="string", length=510)
     * @Assert\NotBlank
     */
    private $funders;

    /**
     * @ORM\Column(name="indexed_in", type="string", length=510)
     */
    private $indexed_in;

    /**
     * @ORM\Column(name="db", type="string", length=510)
     */
    private $db;


    /******************** Institution Info  ********************/

    /**
     * @ORM\Column(name="editor_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $editorName;

    /**
     * @ORM\Column(name="editor_email", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $editorEmail;

    /**
     * @ORM\Column(name="editor_alternate_email", type="string", length=255, nullable=true)
     */
    private $editorAlternateEmail;

    /**
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $phone;

    /**
     * @ORM\Column(name="address", type="string", length=510, nullable=true)
     * @Assert\NotBlank
     */
    private $address;

    /**
     * @ORM\Column(name="cep", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $cep;

    /**
     * @ORM\Column(name="institution", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $institution;

    /**
     * @var string
     *
     * @ORM\Column(name="editor_state", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $editorState;

    /**
     * @var string
     *
     * @ORM\Column(name="editor_city", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $editorCity;

    /**
     * @var string
     *
     * @ORM\Column(name="postgraduate_grade", type="string", length=255, nullable=true)
     */
    private $postgraduateGrade;


    /******************** Publisher Info  ********************/

    /**
     * @ORM\Column(name="publisher_name", type="string", length=255, nullable=true)
     */
    private $publisherName;

    /**
     * @ORM\Column(name="publisher_contact_name", type="string", length=255, nullable=true)
     */
    private $publisherContactName;

    /**
     * @ORM\Column(name="publisher_email", type="string", length=255, nullable=true)
     */
    private $publisherEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="publisher_state", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $publisherState;

    /**
     * @var string
     *
     * @ORM\Column(name="publisher_city", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $publisherCity;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $country;

    /******************** Evaluation Process Info  ********************/

    /**
     * @ORM\Column(name="requestor_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $requestorName;

    /**
     * @ORM\Column(name="requestor_email", type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $requestorEmail;

    /**
     * @var Issue
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="submission", cascade={"persist"})
     * @ORM\JoinTable(name="submission_issue")
     * @Assert\NotBlank
     */
    private $issue;

    /**
     * @var SubmissionUpload
     * @ORM\OneToMany(targetEntity="SubmissionUpload", mappedBy="submission", cascade={"persist"})
     * @ORM\JoinTable(name="submission_upload")
     */
    private $attachments;


    public function __construct() {

        $this->issue = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        // call Grandpa's constructor
        parent::__construct();
    }

    public function __clone() {

        $this->setCreated(new \Datetime());
        $this->setUpdated(new \Datetime());
        $this->setIsSended(false);

        foreach(array('issue', 'attachments') as $attribute) {

            $mClone = new ArrayCollection();
            foreach ($this->$attribute as $item) {
                $itemClone = clone $item;
                $itemClone->setSubmission($this);
                $mClone->add($itemClone);
            }

            $this->$attribute = $mClone;
        }
    }

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
     * Set protocol
     *
     * @param \Proethos2\ModelBundle\Entity\Protocol $protocol
     *
     * @return Submission
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
     * Set owner
     *
     * @param \Proethos2\ModelBundle\Entity\User $owner
     *
     * @return Submission
     */
    public function setOwner(\Proethos2\ModelBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Proethos2\ModelBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add team
     *
     * @param \Proethos2\ModelBundle\Entity\User $team
     *
     * @return Submission
     */
    public function addTeam(\Proethos2\ModelBundle\Entity\User $team)
    {
        $this->team[] = $team;

        return $this;
    }

    /**
     * Remove team
     *
     * @param \Proethos2\ModelBundle\Entity\User $team
     */
    public function removeTeam(\Proethos2\ModelBundle\Entity\User $team)
    {
        $this->team->removeElement($team);
    }

    /**
     * Get team
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get total team
     *
     * @return int
     */
    public function getTotalTeam()
    {
        return count($this->team) + 1;
    }

    public function isOwner(User $user)
    {
        if($this->getTeam()->contains($user)) {
            return true;
        }

        if($user == $this->getOwner()) {
            return true;
        }

        return false;
    }

    /**
     * Set isSended
     *
     * @param boolean $isSended
     *
     * @return Submission
     */
    public function setIsSended($isSended)
    {
        $this->is_sent = $isSended;

        return $this;
    }

    /**
     * Get isSended
     *
     * @return boolean
     */
    public function getIsSended()
    {
        return $this->is_sent;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Submission
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
     * Set isSent
     *
     * @param boolean $isSent
     *
     * @return Submission
     */
    public function setIsSent($isSent)
    {
        $this->is_sent = $isSent;

        return $this;
    }

    /**
     * Get isSent
     *
     * @return boolean
     */
    public function getIsSent()
    {
        return $this->is_sent;
    }

    /**
     * Add tranlsation
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $tranlsation
     *
     * @return Submission
     */
    public function addTranlsation(\Proethos2\ModelBundle\Entity\Submission $tranlsation)
    {
        $this->translations[] = $tranlsation;

        return $this;
    }

    /**
     * Remove tranlsation
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $tranlsation
     */
    public function removeTranlsation(\Proethos2\ModelBundle\Entity\Submission $tranlsation)
    {
        $this->translations->removeElement($tranlsation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get total translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTotalTranslations()
    {
        return count($this->getTranslations());
    }

    /**
     * Set isTranslation
     *
     * @param boolean $isTranslation
     *
     * @return Submission
     */
    public function setIsTranslation($isTranslation)
    {
        $this->is_translation = $isTranslation;

        return $this;
    }

    /**
     * Get isTranslation
     *
     * @return boolean
     */
    public function getIsTranslation()
    {
        return $this->is_translation;
    }

    /**
     * Set originalSubmission
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $originalSubmission
     *
     * @return Submission
     */
    public function setOriginalSubmission(\Proethos2\ModelBundle\Entity\Submission $originalSubmission = null)
    {
        $this->original_submission = $originalSubmission;

        return $this;
    }

    /**
     * Get originalSubmission
     *
     * @return \Proethos2\ModelBundle\Entity\Submission
     */
    public function getOriginalSubmission()
    {
        return $this->original_submission;
    }

    /**
     * can be edited?
     *
     * @return boolean
     */
    public function getCanBeEdited()
    {
        if(in_array($this->getProtocol()->getStatus(), array('D'))) {
            return true;
        } else {
            if($this->getProtocol()->getIsMigrated()) {
                return true;
            }
            return false;
        }
    }

    /**
     * Add translation
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $translation
     *
     * @return Submission
     */
    public function addTranslation(\Proethos2\ModelBundle\Entity\Submission $translation)
    {
        $this->translations[] = $translation;

        return $this;
    }

    /**
     * Remove translation
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $translation
     */
    public function removeTranslation(\Proethos2\ModelBundle\Entity\Submission $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Submission
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set shortTitle
     *
     * @param string $shortTitle
     *
     * @return Submission
     */
    public function setShortTitle($shortTitle)
    {
        $this->shortTitle = $shortTitle;

        return $this;
    }

    /**
     * Get shortTitle
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
    }

    /**
     * Set acronymTitle
     *
     * @param string $acronymTitle
     *
     * @return Submission
     */
    public function setAcronymTitle($acronymTitle)
    {
        $this->acronymTitle = $acronymTitle;

        return $this;
    }

    /**
     * Get acronymTitle
     *
     * @return string
     */
    public function getAcronymTitle()
    {
        return $this->acronymTitle;
    }

    /**
     * Set previousTitle
     *
     * @param string $previousTitle
     *
     * @return Submission
     */
    public function setPreviousTitle($previousTitle)
    {
        $this->previousTitle = $previousTitle;

        return $this;
    }

    /**
     * Get previousTitle
     *
     * @return string
     */
    public function getPreviousTitle()
    {
        return $this->previousTitle;
    }

    /**
     * Set additionalTitle
     *
     * @param string $additionalTitle
     *
     * @return Submission
     */
    public function setAdditionalTitle($additionalTitle)
    {
        $this->additionalTitle = $additionalTitle;

        return $this;
    }

    /**
     * Get additionalTitle
     *
     * @return string
     */
    public function getAdditionalTitle()
    {
        return $this->additionalTitle;
    }

    /**
     * Set frequency
     *
     * @param string $frequency
     *
     * @return Submission
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set standards
     *
     * @param string $standards
     *
     * @return Submission
     */
    public function setStandards($standards)
    {
        $this->standards = $standards;

        return $this;
    }

    /**
     * Get standards
     *
     * @return string
     */
    public function getStandards()
    {
        return $this->standards;
    }

    /**
     * Set bibliographicSubtitle
     *
     * @param string $bibliographicSubtitle
     *
     * @return Submission
     */
    public function setBibliographicSubtitle($bibliographicSubtitle)
    {
        $this->bibliographicSubtitle = $bibliographicSubtitle;

        return $this;
    }

    /**
     * Get bibliographicSubtitle
     *
     * @return string
     */
    public function getBibliographicSubtitle()
    {
        return $this->bibliographicSubtitle;
    }

    /**
     * Set creationYear
     *
     * @param integer $creationYear
     *
     * @return Submission
     */
    public function setCreationYear($creationYear)
    {
        $this->creationYear = $creationYear;

        return $this;
    }

    /**
     * Get creationYear
     *
     * @return integer
     */
    public function getCreationYear()
    {
        return $this->creationYear;
    }

    /**
     * Set lifetime
     *
     * @param string $lifetime
     *
     * @return Submission
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Get lifetime
     *
     * @return string
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set support
     *
     * @param string $support
     *
     * @return Submission
     */
    public function setSupport($support)
    {
        $this->support = $support;

        return $this;
    }

    /**
     * Get support
     *
     * @return string
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Submission
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set websiteInfo
     *
     * @param string $websiteInfo
     *
     * @return Submission
     */
    public function setWebsiteInfo($websiteInfo)
    {
        $this->websiteInfo = $websiteInfo;

        return $this;
    }

    /**
     * Get websiteInfo
     *
     * @return string
     */
    public function getWebsiteInfo()
    {
        return $this->websiteInfo;
    }

    /**
     * Set socialNetworksInfo
     *
     * @param string $socialNetworksInfo
     *
     * @return Submission
     */
    public function setSocialNetworksInfo($socialNetworksInfo)
    {
        $this->socialNetworksInfo = $socialNetworksInfo;

        return $this;
    }

    /**
     * Get socialNetworksInfo
     *
     * @return string
     */
    public function getSocialNetworksInfo()
    {
        return $this->socialNetworksInfo;
    }

    /**
     * Set mission
     *
     * @param string $mission
     *
     * @return Submission
     */
    public function setMission($mission)
    {
        $this->mission = $mission;

        return $this;
    }

    /**
     * Get mission
     *
     * @return string
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * Set qualis
     *
     * @param string $qualis
     *
     * @return Submission
     */
    public function setQualis($qualis)
    {
        $this->qualis = $qualis;

        return $this;
    }

    /**
     * Get qualis
     *
     * @return string
     */
    public function getQualis()
    {
        return $this->qualis;
    }

    /**
     * Set funders
     *
     * @param string $funders
     *
     * @return Submission
     */
    public function setFunders($funders)
    {
        $this->funders = $funders;

        return $this;
    }

    /**
     * Get funders
     *
     * @return string
     */
    public function getFunders()
    {
        return $this->funders;
    }

    /**
     * Set indexedIn
     *
     * @param string $indexedIn
     *
     * @return Submission
     */
    public function setIndexedIn($indexedIn)
    {
        $this->indexed_in = $indexedIn;

        return $this;
    }

    /**
     * Get indexedIn
     *
     * @return string
     */
    public function getIndexedIn()
    {
        return $this->indexed_in;
    }

    /**
     * Set editorName
     *
     * @param string $editorName
     *
     * @return Submission
     */
    public function setEditorName($editorName)
    {
        $this->editorName = $editorName;

        return $this;
    }

    /**
     * Get editorName
     *
     * @return string
     */
    public function getEditorName()
    {
        return $this->editorName;
    }

    /**
     * Set editorEmail
     *
     * @param string $editorEmail
     *
     * @return Submission
     */
    public function setEditorEmail($editorEmail)
    {
        $this->editorEmail = $editorEmail;

        return $this;
    }

    /**
     * Get editorEmail
     *
     * @return string
     */
    public function getEditorEmail()
    {
        return $this->editorEmail;
    }

    /**
     * Set editorAlternateEmail
     *
     * @param string $editorAlternateEmail
     *
     * @return Submission
     */
    public function setEditorAlternateEmail($editorAlternateEmail)
    {
        $this->editorAlternateEmail = $editorAlternateEmail;

        return $this;
    }

    /**
     * Get editorAlternateEmail
     *
     * @return string
     */
    public function getEditorAlternateEmail()
    {
        return $this->editorAlternateEmail;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Submission
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Submission
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set cep
     *
     * @param string $cep
     *
     * @return Submission
     */
    public function setCep($cep)
    {
        $this->cep = $cep;

        return $this;
    }

    /**
     * Get cep
     *
     * @return string
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * Set institution
     *
     * @param string $institution
     *
     * @return Submission
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set editorState
     *
     * @param string $editorState
     *
     * @return Submission
     */
    public function setEditorState($editorState)
    {
        $this->editorState = $editorState;

        return $this;
    }

    /**
     * Get editorState
     *
     * @return string
     */
    public function getEditorState()
    {
        return $this->editorState;
    }

    /**
     * Set editorCity
     *
     * @param string $editorCity
     *
     * @return Submission
     */
    public function setEditorCity($editorCity)
    {
        $this->editorCity = $editorCity;

        return $this;
    }

    /**
     * Get editorCity
     *
     * @return string
     */
    public function getEditorCity()
    {
        return $this->editorCity;
    }

    /**
     * Set postgraduateGrade
     *
     * @param string $postgraduateGrade
     *
     * @return Submission
     */
    public function setPostgraduateGrade($postgraduateGrade)
    {
        $this->postgraduateGrade = $postgraduateGrade;

        return $this;
    }

    /**
     * Get postgraduateGrade
     *
     * @return string
     */
    public function getPostgraduateGrade()
    {
        return $this->postgraduateGrade;
    }

    /**
     * Set publisherName
     *
     * @param string $publisherName
     *
     * @return Submission
     */
    public function setPublisherName($publisherName)
    {
        $this->publisherName = $publisherName;

        return $this;
    }

    /**
     * Get publisherName
     *
     * @return string
     */
    public function getPublisherName()
    {
        return $this->publisherName;
    }

    /**
     * Set publisherContactName
     *
     * @param string $publisherContactName
     *
     * @return Submission
     */
    public function setPublisherContactName($publisherContactName)
    {
        $this->publisherContactName = $publisherContactName;

        return $this;
    }

    /**
     * Get publisherContactName
     *
     * @return string
     */
    public function getPublisherContactName()
    {
        return $this->publisherContactName;
    }

    /**
     * Set publisherEmail
     *
     * @param string $publisherEmail
     *
     * @return Submission
     */
    public function setPublisherEmail($publisherEmail)
    {
        $this->publisherEmail = $publisherEmail;

        return $this;
    }

    /**
     * Get publisherEmail
     *
     * @return string
     */
    public function getPublisherEmail()
    {
        return $this->publisherEmail;
    }

    /**
     * Set publisherState
     *
     * @param string $publisherState
     *
     * @return Submission
     */
    public function setPublisherState($publisherState)
    {
        $this->publisherState = $publisherState;

        return $this;
    }

    /**
     * Get publisherState
     *
     * @return string
     */
    public function getPublisherState()
    {
        return $this->publisherState;
    }

    /**
     * Set publisherCity
     *
     * @param string $publisherCity
     *
     * @return Submission
     */
    public function setPublisherCity($publisherCity)
    {
        $this->publisherCity = $publisherCity;

        return $this;
    }

    /**
     * Get publisherCity
     *
     * @return string
     */
    public function getPublisherCity()
    {
        return $this->publisherCity;
    }

    /**
     * Set requestorName
     *
     * @param string $requestorName
     *
     * @return Submission
     */
    public function setRequestorName($requestorName)
    {
        $this->requestorName = $requestorName;

        return $this;
    }

    /**
     * Get requestorName
     *
     * @return string
     */
    public function getRequestorName()
    {
        return $this->requestorName;
    }

    /**
     * Set requestorEmail
     *
     * @param string $requestorEmail
     *
     * @return Submission
     */
    public function setRequestorEmail($requestorEmail)
    {
        $this->requestorEmail = $requestorEmail;

        return $this;
    }

    /**
     * Get requestorEmail
     *
     * @return string
     */
    public function getRequestorEmail()
    {
        return $this->requestorEmail;
    }

    /**
     * Set thematicArea
     *
     * @param \Proethos2\ModelBundle\Entity\ThematicArea $thematicArea
     *
     * @return Submission
     */
    public function setThematicArea(\Proethos2\ModelBundle\Entity\ThematicArea $thematicArea = null)
    {
        $this->thematicArea = $thematicArea;

        return $this;
    }

    /**
     * Get thematicArea
     *
     * @return \Proethos2\ModelBundle\Entity\ThematicArea
     */
    public function getThematicArea()
    {
        return $this->thematicArea;
    }

    /**
     * Set specialty
     *
     * @param \Proethos2\ModelBundle\Entity\Specialty $specialty
     *
     * @return Submission
     */
    public function setSpecialty(\Proethos2\ModelBundle\Entity\Specialty $specialty = null)
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Get specialty
     *
     * @return \Proethos2\ModelBundle\Entity\Specialty
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * Add language
     *
     * @param \Proethos2\ModelBundle\Entity\Language $language
     *
     * @return Submission
     */
    public function addLanguage(\Proethos2\ModelBundle\Entity\Language $language)
    {
        $this->language[] = $language;

        return $this;
    }

    /**
     * Remove language
     *
     * @param \Proethos2\ModelBundle\Entity\Language $language
     */
    public function removeLanguage(\Proethos2\ModelBundle\Entity\Language $language)
    {
        $this->language->removeElement($language);
    }

    /**
     * Get language
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Add issue
     *
     * @param \Proethos2\ModelBundle\Entity\Issue $issue
     *
     * @return Submission
     */
    public function addIssue(\Proethos2\ModelBundle\Entity\Issue $issue)
    {
        $this->issue[] = $issue;

        return $this;
    }

    /**
     * Remove issue
     *
     * @param \Proethos2\ModelBundle\Entity\Issue $issue
     */
    public function removeIssue(\Proethos2\ModelBundle\Entity\Issue $issue)
    {
        $this->issue->removeElement($issue);
    }

    /**
     * Get issue
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Set fundedByCnpq
     *
     * @param boolean $fundedByCnpq
     *
     * @return Submission
     */
    public function setFundedByCnpq($fundedByCnpq)
    {
        $this->funded_by_cnpq = $fundedByCnpq;

        return $this;
    }

    /**
     * Get fundedByCnpq
     *
     * @return boolean
     */
    public function getFundedByCnpq()
    {
        return $this->funded_by_cnpq;
    }

    /**
     * Set fullText
     *
     * @param string $fullText
     *
     * @return Submission
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

    /**
     * Set db
     *
     * @param string $db
     *
     * @return Submission
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Get db
     *
     * @return string
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Set issn
     *
     * @param string $issn
     *
     * @return Submission
     */
    public function setIssn($issn)
    {
        $this->issn = $issn;

        return $this;
    }

    /**
     * Get issn
     *
     * @return string
     */
    public function getIssn()
    {
        return $this->issn;
    }

    /**
     * Set issnOnline
     *
     * @param string $issnOnline
     *
     * @return Submission
     */
    public function setIssnOnline($issnOnline)
    {
        $this->issnOnline = $issnOnline;

        return $this;
    }

    /**
     * Get issnOnline
     *
     * @return string
     */
    public function getIssnOnline()
    {
        return $this->issnOnline;
    }

    /**
     * Set country
     *
     * @param \Proethos2\ModelBundle\Entity\Country $country
     *
     * @return Submission
     */
    public function setCountry(\Proethos2\ModelBundle\Entity\Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \Proethos2\ModelBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add attachment
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionUpload $attachment
     *
     * @return Submission
     */
    public function addAttachment(\Proethos2\ModelBundle\Entity\SubmissionUpload $attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Remove attachment
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionUpload $attachment
     */
    public function removeAttachment(\Proethos2\ModelBundle\Entity\SubmissionUpload $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * Get attachments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Add publicationType
     *
     * @param \Proethos2\ModelBundle\Entity\PublicationType $publicationType
     *
     * @return Submission
     */
    public function addPublicationType(\Proethos2\ModelBundle\Entity\PublicationType $publicationType)
    {
        $this->publication_type[] = $publicationType;

        return $this;
    }

    /**
     * Remove publicationType
     *
     * @param \Proethos2\ModelBundle\Entity\PublicationType $publicationType
     */
    public function removePublicationType(\Proethos2\ModelBundle\Entity\PublicationType $publicationType)
    {
        $this->publication_type->removeElement($publicationType);
    }

    /**
     * Get publicationType
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPublicationType()
    {
        return $this->publication_type;
    }
}

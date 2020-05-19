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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Proethos2\ModelBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 */
class User extends Base implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     * @ORM\JoinTable(name="user_role")
     */
    private $proethos2_roles;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /** 
     * @var Country
     * 
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="users") 
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL") 
     */ 
    private $country;

    /**
     * @var Specialty
     *
     * @ORM\ManyToOne(targetEntity="Specialty")
     * @ORM\JoinColumn(name="specialty_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $specialty;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $other_specialty;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $institution;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lattes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hashcode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $first_access = true;

    /**
     * @ORM\Column(name="average", type="float")
     * @Assert\NotBlank
     */
    private $average;

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        parent::__construct();
        $this->proethos2_roles = new ArrayCollection();
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid(null, true));
    }    

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
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
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set institution
     *
     * @param string $institution
     *
     * @return User
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
     * Add proethos2Role
     *
     * @param \Proethos2\ModelBundle\Entity\Role $proethos2Role
     *
     * @return User
     */
    public function addProethos2Role(\Proethos2\ModelBundle\Entity\Role $proethos2Role)
    {
        $this->proethos2_roles[] = $proethos2Role;

        return $this;
    }

    /**
     * Remove proethos2Role
     *
     * @param \Proethos2\ModelBundle\Entity\Role $proethos2Role
     */
    public function removeProethos2Role(\Proethos2\ModelBundle\Entity\Role $proethos2Role)
    {
        $this->proethos2_roles->removeElement($proethos2Role);
    }

    /**
     * Get proethos2Roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProethos2Roles()
    {
        return $this->proethos2_roles;
    }

    public function getRolesSlug() {

        $slugs = array();
        foreach($this->getProethos2Roles() as $role) {
            $slugs[] = $role->getSlug();
        }

        return $slugs;
    }

    /**
     * Set country
     *
     * @param \Proethos2\ModelBundle\Entity\Country $country
     *
     * @return User
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
     * Set firstAccess
     *
     * @param boolean $firstAccess
     *
     * @return User
     */
    public function setFirstAccess($firstAccess)
    {
        $this->first_access = $firstAccess;

        return $this;
    }

    /**
     * Get firstAccess
     *
     * @return boolean
     */
    public function getFirstAccess()
    {
        return $this->first_access;
    }

    /**
     * Set hashcode
     *
     * @param string $hashcode
     *
     * @return User
     */
    public function setHashcode($hashcode)
    {
        $this->hashcode = $hashcode;

        return $this;
    }

    public function generateHashcode()
    {
        $this->setHashcode(md5($this->getId() . $this->getUsername() . date("YmdHisss")));
        return $this->getHashcode();
    }

    public function cleanHashcode()
    {
        $this->setHashcode(NULL);
    }

    /**
     * Get hashcode
     *
     * @return string
     */
    public function getHashcode()
    {
        return $this->hashcode;
    }

    /**
     * Set lattes
     *
     * @param string $lattes
     *
     * @return User
     */
    public function setLattes($lattes)
    {
        $this->lattes = $lattes;

        return $this;
    }

    /**
     * Get lattes
     *
     * @return string
     */
    public function getLattes()
    {
        return $this->lattes;
    }

    /**
     * Set specialty
     *
     * @param \Proethos2\ModelBundle\Entity\Specialty $specialty
     *
     * @return User
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
     * Set average
     *
     * @param float $average
     *
     * @return User
     */
    public function setAverage($average)
    {
        $this->average = $average;

        return $this;
    }

    /**
     * Get average
     *
     * @return float
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * Set otherSpecialty
     *
     * @param string $otherSpecialty
     *
     * @return User
     */
    public function setOtherSpecialty($otherSpecialty)
    {
        $this->other_specialty = $otherSpecialty;

        return $this;
    }

    /**
     * Get otherSpecialty
     *
     * @return string
     */
    public function getOtherSpecialty()
    {
        return $this->other_specialty;
    }

    public function getAdhocRevisionsHistory()
    {
        global $kernel;
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('Proethos2ModelBundle:User')->find($this->id);
        $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');
        
        // totalActive
        $adhoc_active_revisions = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "is_final_revision" => false));
        $total_adhoc_active_revisions = count($adhoc_active_revisions);

        // totalFinal
        $adhoc_final_revisions = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "is_final_revision" => true));
        $total_adhoc_final_revisions = count($adhoc_final_revisions);

        // totalReject
        $adhoc_rejected_revisions = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "rejected" => true));
        $total_adhoc_rejected_revisions = count($adhoc_rejected_revisions);

        // totalRejectA
        $reject_a = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "rejected" => true, "reject_reason" => 'A'));
        $total_reject_a = count($reject_a);

        // totalRejectB
        $reject_b = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "rejected" => true, "reject_reason" => 'B'));
        $total_reject_b = count($reject_b);

        // totalRejectC
        $reject_c = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "rejected" => true, "reject_reason" => 'C'));
        $total_reject_c = count($reject_c);

        // totalRejectD
        $reject_d = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "rejected" => true, "reject_reason" => 'D'));
        $total_reject_d = count($reject_d);

        // totalRejectE
        $reject_e = $protocol_adhoc_revision_repository->findBy(array("member" => $user, "rejected" => true, "reject_reason" => 'E'));
        $total_reject_e = count($reject_e);

        // lastRevisionDays
        $now = time();
        $adhoc_last_revision_days = end($adhoc_active_revisions);
        $date = ( $adhoc_last_revision_days ) ? $adhoc_last_revision_days->getCreated()->format('U') : $now;
        $datediff = $now - $date;
        $last_revision_days = round($datediff / (60 * 60 * 24));

        // averageRevisionDays
        $average_revision_days = 0;
        if ( $total_adhoc_final_revisions > 0 ) {
            $days = array();
            
            foreach ($adhoc_final_revisions as $revision) {
                $created = $revision->getCreated()->format('U');
                $updated = $revision->getUpdated()->format('U');
                $datediff = $updated - $created;
                $day = round($datediff / (60 * 60 * 24));
                $days[] = $day;
            }

            if(count($days)) {
                $average_revision_days = round(array_sum($days)/count($days));
            }
        }

        $adhoc_history = array(
            'totalActive' => $total_adhoc_active_revisions,
            'totalFinal' => $total_adhoc_final_revisions,
            'totalReject' => $total_adhoc_rejected_revisions,
            'totalRejectA' => $total_reject_a,
            'totalRejectB' => $total_reject_b,
            'totalRejectC' => $total_reject_c,
            'totalRejectD' => $total_reject_d,
            'totalRejectE' => $total_reject_e,
            'lastRevisionDays' => $last_revision_days,
            'averageRevisionDays' => $average_revision_days
        );

        return $adhoc_history;
    }
}

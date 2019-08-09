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


namespace Proethos2\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Proethos2\CoreBundle\Util\Util;
use Proethos2\CoreBundle\Util\CountryLocale;

use Proethos2\ModelBundle\Entity\Submission;
use Proethos2\ModelBundle\Entity\SubmissionCountry;
use Proethos2\ModelBundle\Entity\SubmissionCost;
use Proethos2\ModelBundle\Entity\SubmissionTask;
use Proethos2\ModelBundle\Entity\SubmissionUpload;
use Proethos2\ModelBundle\Entity\Protocol;
use Proethos2\ModelBundle\Entity\ProtocolHistory;
use Proethos2\ModelBundle\Entity\SubmissionClinicalTrial;
use Proethos2\ModelBundle\Entity\Issue;

class NewSubmissionController extends Controller
{
    /**
     * @Route("/submission/new/first", name="submission_new_first_step")
     * @Template()
     */
    public function FirstStepAction(Request $request)
    {
        $output = array();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        // getting publication type list
        $publication_type_repository = $em->getRepository('Proethos2ModelBundle:PublicationType');
        // $publication_type = $publication_type_repository->findByStatus(true);
        $publication_type = $publication_type_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['publication_type'] = $publication_type;

        // getting language list
        $language_repository = $em->getRepository('Proethos2ModelBundle:Language');
        // $language = $language_repository->findByStatus(true);
        $language = $language_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['language'] = $language;

        // getting thematic area list
        $thematic_area_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        // $thematic_area = $thematic_area_repository->findByStatus(true);
        $thematic_area = $thematic_area_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['thematic_area'] = $thematic_area;

        // getting specialty list
        $specialty_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        // $specialty = $specialty_repository->findByStatus(true);
        $specialty = $specialty_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['specialty'] = $specialty;

        // getting frequency list
        $frequency = array(
            "FC"  => $translator->trans("Fluxo Contínuo"),
            "S"   => $translator->trans("Semestral"),
            "QUA" => $translator->trans("Quadrimestral"),
            "T"   => $translator->trans("Trimestral"),
            "B"   => $translator->trans("Bimestral"),
            "M"   => $translator->trans("Mensal"),
            "QUI" => $translator->trans("Quinzenal")
        );

        $output['frequency'] = $frequency;

        // getting support list
        $support = array(
            "I"  => $translator->trans("Impresso"),
            "E"  => $translator->trans("Eletrônico"),
            "IE" => $translator->trans("Impresso e Eletrônico")
        );

        $output['support'] = $support;

        // getting fulltext list
        $fulltext = array(
            "T" => $translator->trans("Portal próprio"),
            "B" => $translator->trans("Repositório institucional"),
            "M" => $translator->trans("Não provemos acesso ao texto completo. No entanto, vamos ingressar na iniciativa LILACS-Express para disponibilizá-los")
        );
        
        $output['fulltext'] = $fulltext;

        $user = $this->get('security.token_storage')->getToken()->getUser();


        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            $output['post_data'] = $post_data;

            // required fields
            $required_fields = array(
                'title',
                'frequency',
                'standards',
                'creation_year',
                'support',
                'fulltext',
                'mission',
                'publication_type',
                'language',
                'thematic_area',
                'specialty',
                'funders'
            );

            // checking required fields
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            // checking required fields
            if(empty($post_data['issn']) and empty($post_data['issn_online'])) {
                $session->getFlashBag()->add('error', $translator->trans("Please enter the ISSN or ISSN Online field.", array("%field%" => $field)));
                return $output;
            }

            $protocol = new Protocol();
            $protocol->setOwner($user);
            $protocol->setStatus('D');
            $em->persist($protocol);
            $em->flush();

            $submission = new Submission();
            $submission->setTitle($post_data['title']);
            $submission->setShortTitle($post_data['short_title']);
            $submission->setAcronymTitle($post_data['acronym_title']);
            $submission->setPreviousTitle($post_data['previous_title']);
            $submission->setAdditionalTitle($post_data['additional_title']);
            $submission->setIssn($post_data['issn']);
            $submission->setIssnOnline($post_data['issn_online']);
            $submission->setFrequency($post_data['frequency']);
            $submission->setStandards($post_data['standards']);
            $submission->setBibliographicSubtitle($post_data['bibliographic_subtitle']);
            $submission->setCreationYear($post_data['creation_year']);
            $submission->setLifetime($post_data['lifetime']);
            $submission->setSupport($post_data['support']);
            $submission->setFullText($post_data['fulltext']);
            $submission->setWebsite($post_data['website']);
            $submission->setWebsiteInfo($post_data['website_info']);
            $submission->setSocialNetworksInfo($post_data['social_networks_info']);
            $submission->setMission($post_data['mission']);
            $submission->setQualis($post_data['qualis']);
            $submission->setFundedByCnpq(($post_data['funded_by_cnpq'] == 'yes') ? true : false);
            $submission->setFunders($post_data['funders']);
            $submission->setIndexedIn($post_data['indexed_in']);
            $submission->setDb($post_data['databases']);
            $submission->setProtocol($protocol);
            $submission->setNumber(1);

            $submission->setOwner($user);

            // thematic area
            $selected_thematic_area = $thematic_area_repository->find($post_data['thematic_area']);
            $submission->setThematicArea($selected_thematic_area);

            // specialty
            $selected_specialty = $specialty_repository->find($post_data['specialty']);
            $submission->setSpecialty($selected_specialty);

            // removing all publication types to re-add
            if ($submission->getPublicationType()) {
                foreach($submission->getPublicationType() as $publication_type) {
                    $submission->removePublicationType($publication_type);
                }
            }

            // re-add publication types
            if(isset($post_data['publication_type'])) {
                foreach($post_data['publication_type'] as $publication_type) {
                    $publication_type = $publication_type_repository->find($publication_type);
                    $submission->addPublicationType($publication_type);
                }
            }

            // removing all languages to re-add
            if ($submission->getLanguage()) {
                foreach($submission->getLanguage() as $language) {
                    $submission->removeLanguage($language);
                }
            }

            // re-add languages
            if(isset($post_data['language'])) {
                foreach($post_data['language'] as $language) {
                    $language = $language_repository->find($language);
                    $submission->addLanguage($language);
                }
            }


            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            $protocol->setMainSubmission($submission);
            $em->persist($protocol);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Submission started with success."));
            return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/first", name="submission_new_first_created_protocol_step")
     * @Template()
     */
    public function FirstStepCreatedProtocolAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        // getting publication type list
        $publication_type_repository = $em->getRepository('Proethos2ModelBundle:PublicationType');
        // $publication_type = $publication_type_repository->findByStatus(true);
        $publication_type = $publication_type_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['publication_type'] = $publication_type;

        // getting language list
        $language_repository = $em->getRepository('Proethos2ModelBundle:Language');
        // $language = $language_repository->findByStatus(true);
        $language = $language_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['language'] = $language;

        // getting thematic area list
        $thematic_area_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        // $thematic_area = $thematic_area_repository->findByStatus(true);
        $thematic_area = $thematic_area_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['thematic_area'] = $thematic_area;

        // getting specialty list
        $specialty_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        // $specialty = $specialty_repository->findByStatus(true);
        $specialty = $specialty_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['specialty'] = $specialty;
        // getting frequency list
        $frequency = array(
            "FC"  => $translator->trans("Fluxo Contínuo"),
            "S"   => $translator->trans("Semestral"),
            "QUA" => $translator->trans("Quadrimestral"),
            "T"   => $translator->trans("Trimestral"),
            "B"   => $translator->trans("Bimestral"),
            "M"   => $translator->trans("Mensal"),
            "QUI" => $translator->trans("Quinzenal")
        );

        $output['frequency'] = $frequency;

        // getting support list
        $support = array(
            "I"  => $translator->trans("Impresso"),
            "E"  => $translator->trans("Eletrônico"),
            "IE" => $translator->trans("Impresso e Eletrônico")
        );

        $output['support'] = $support;

        // getting fulltext list
        $fulltext = array(
            "T" => $translator->trans("Portal próprio"),
            "B" => $translator->trans("Repositório institucional"),
            "M" => $translator->trans("Não provemos acesso ao texto completo. No entanto, vamos ingressar na iniciativa LILACS-Express para disponibilizá-los")
        );
        
        $output['fulltext'] = $fulltext;

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or !$submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        $allow_to_edit_submission = true;
        // if current user is not owner, check the team
        if ($user != $submission->getOwner()) {
            $allow_to_edit_submission = false;

            if(in_array('administrator', $user->getRolesSlug())) {
                $allow_to_edit_submission = true;

            } else {
                foreach($submission->getTeam() as $team_member) {
                    // if current user = some team member, than it allows to edit
                    if ($user == $team_member) {
                        $allow_to_edit_submission = true;
                    }
                }
            }
        }
        
        if (!$allow_to_edit_submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        $users = $user_repository->findAll();
        $output['users'] = $users;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // required fields
            $required_fields = array(
                'title',
                'frequency',
                'standards',
                'creation_year',
                'support',
                'fulltext',
                'mission',
                'publication_type',
                'language',
                'thematic_area',
                'specialty',
                'funders'
            );

            // checking required fields
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            // checking required fields
            if(empty($post_data['issn']) and empty($post_data['issn_online'])) {
                $session->getFlashBag()->add('error', $translator->trans("Please enter the ISSN or ISSN Online field.", array("%field%" => $field)));
                return $output;
            }

            $submission->setTitle($post_data['title']);
            $submission->setShortTitle($post_data['short_title']);
            $submission->setAcronymTitle($post_data['acronym_title']);
            $submission->setPreviousTitle($post_data['previous_title']);
            $submission->setAdditionalTitle($post_data['additional_title']);
            $submission->setIssn($post_data['issn']);
            $submission->setIssnOnline($post_data['issn_online']);
            $submission->setFrequency($post_data['frequency']);
            $submission->setStandards($post_data['standards']);
            $submission->setBibliographicSubtitle($post_data['bibliographic_subtitle']);
            $submission->setCreationYear($post_data['creation_year']);
            $submission->setLifetime($post_data['lifetime']);
            $submission->setSupport($post_data['support']);
            $submission->setFullText($post_data['fulltext']);
            $submission->setWebsite($post_data['website']);
            $submission->setWebsiteInfo($post_data['website_info']);
            $submission->setSocialNetworksInfo($post_data['social_networks_info']);
            $submission->setMission($post_data['mission']);
            $submission->setQualis($post_data['qualis']);
            $submission->setFundedByCnpq(($post_data['funded_by_cnpq'] == 'yes') ? true : false);
            $submission->setFunders($post_data['funders']);
            $submission->setIndexedIn($post_data['indexed_in']);
            $submission->setDb($post_data['databases']);

            // thematic area
            $selected_thematic_area = $thematic_area_repository->find($post_data['thematic_area']);
            $submission->setThematicArea($selected_thematic_area);

            // specialty
            $selected_specialty = $specialty_repository->find($post_data['specialty']);
            $submission->setSpecialty($selected_specialty);

            // removing all publication types to re-add
            if ($submission->getPublicationType()) {
                foreach($submission->getPublicationType() as $publication_type) {
                    $submission->removePublicationType($publication_type);
                }
            }

            // re-add publication types
            if(isset($post_data['publication_type'])) {
                foreach($post_data['publication_type'] as $publication_type) {
                    $publication_type = $publication_type_repository->find($publication_type);
                    $submission->addPublicationType($publication_type);
                }
            }

            // removing all languages to re-add
            if ($submission->getLanguage()) {
                foreach($submission->getLanguage() as $language) {
                    $submission->removeLanguage($language);
                }
            }

            // re-add languages
            if(isset($post_data['language'])) {
                foreach($post_data['language'] as $language) {
                    $language = $language_repository->find($language);
                    $submission->addLanguage($language);
                }
            }

            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("First step saved with success."));
            return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/translation", name="submission_new_first_translation_protocol_step")
     * @Template()
     */
    public function FirstStepTranslationProtocolAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        $users = $user_repository->findAll();
        $output['users'] = $users;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach(array('scientific_title', 'public_title', 'language') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return array();
                }
            }

            $protocol = $submission->getProtocol();

            $new_submission = new Submission();
            $new_submission->setIsTranslation(true);
            $new_submission->setOriginalSubmission($submission);
            $new_submission->setIsClinicalTrial($submission->getIsClinicalTrial());
            $new_submission->setIsConsultation($submission->getIsConsultation());
            $new_submission->setPublicTitle($post_data['public_title']);
            $new_submission->setScientificTitle($post_data['scientific_title']);
            $new_submission->setTitleAcronym($post_data['title_acronym']);
            $new_submission->setLanguage($post_data['language']);
            $new_submission->setProtocol($protocol);
            $new_submission->setNumber(1);

            $new_submission->setGender($submission->getGender());
            $new_submission->setSampleSize($submission->getSampleSize());
            $new_submission->setMinimumAge($submission->getMinimumAge());
            $new_submission->setMaximumAge($submission->getMaximumAge());
            $new_submission->setRecruitmentInitDate($submission->getRecruitmentInitDate());
            $new_submission->setRecruitmentStatus($submission->getRecruitmentStatus());
            $new_submission->setPriorEthicalApproval($submission->getPriorEthicalApproval());

            $new_submission->setOwner($submission->getOwner());

            $em = $this->getDoctrine()->getManager();
            $em->persist($new_submission);
            $em->flush();

            $submission->addTranlsation($new_submission);
            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("First step saved with success."));
            return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $new_submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/second", name="submission_new_second_step")
     * @Template()
     */
    public function SecondStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        $allow_to_edit_submission = true;
        // if current user is not owner, check the team
        if ($user != $submission->getOwner()) {
            $allow_to_edit_submission = false;

            if(in_array('administrator', $user->getRolesSlug())) {
                $allow_to_edit_submission = true;

            } else {
                foreach($submission->getTeam() as $team_member) {
                    // if current user = some team member, than it allows to edit
                    if ($user == $team_member) {
                        $allow_to_edit_submission = true;
                    }
                }
            }
        }
        
        if (!$allow_to_edit_submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        $users = $user_repository->findAll();
        $output['users'] = $users;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // removing all team to re-add
            foreach($submission->getTeam() as $team_user) {
                $submission->removeTeam($team_user);
            }

            // re-add
            if(isset($post_data['team_user'])) {
                foreach($post_data['team_user'] as $team_user) {
                    $team_user = $user_repository->find($team_user);
                    $submission->addTeam($team_user);
                }
            }

            // new owner
            if(isset($post_data['team-new-owner'])) {
                $new_owner = $user_repository->find($team_user);
                if($new_owner) {
                    $old_owner = $submission->getOwner();
                    $submission->setOwner($new_owner);

                    $protocol = $submission->getProtocol();
                    $protocol->setOwner($new_owner);
                    $em->persist($protocol);
                    $em->flush();

                    $submission->removeTeam($new_owner);
                    $submission->addTeam($old_owner);
                }
            }

            // if is a post to set a new owner, returns to the same page
            if(isset($post_data['stay_on_the_same_page']) and $post_data['stay_on_the_same_page'] == 'true') {
                $em->persist($submission);
                $em->flush();
                return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $submission->getId()), 301);
            }

            // checking required fields
            $required_fields = array('editor_name', 'editor_email', 'phone', 'address', 'cep', 'institution', 'state', 'city');
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            // adding fields to model
            $submission->setEditorName($post_data['editor_name']);
            $submission->setEditorEmail($post_data['editor_email']);
            $submission->setEditorAlternateEmail($post_data['editor_alternate_email']);
            $submission->setPhone($post_data['phone']);
            $submission->setAddress($post_data['address']);
            $submission->setCep($post_data['cep']);
            $submission->setInstitution($post_data['institution']);
            $submission->setEditorState($post_data['state']);
            $submission->setEditorCity($post_data['city']);
            $submission->setPostgraduateGrade($post_data['postgraduate_grade']);

            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Second step saved with success."));
            return $this->redirectToRoute('submission_new_third_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/third", name="submission_new_third_step")
     * @Template()
     */
    public function ThirdStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $submission_country_repository = $em->getRepository('Proethos2ModelBundle:SubmissionCountry');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        // getting countries list
        $country_repository = $em->getRepository('Proethos2ModelBundle:Country');
        $countries = $country_repository->findBy(array(), array('name' => 'asc'));
        $output['countries'] = $countries;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        $allow_to_edit_submission = true;
        // if current user is not owner, check the team
        if ($user != $submission->getOwner()) {
            $allow_to_edit_submission = false;

            if(in_array('administrator', $user->getRolesSlug())) {
                $allow_to_edit_submission = true;

            } else {
                foreach($submission->getTeam() as $team_member) {
                    // if current user = some team member, than it allows to edit
                    if ($user == $team_member) {
                        $allow_to_edit_submission = true;
                    }
                }
            }
        }
        if (!$allow_to_edit_submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();
/*
            if(!$submission->getIsTranslation()) {
                $recruitment_init_date = new \DateTime($post_data['recruitment-init-date']);
                if(new \DateTime('NOW') > $recruitment_init_date) {
                    $session->getFlashBag()->add('error', $translator->trans("The recruitment start date has to be subsequent to the date of protocol submission."));
                    return $output;
                }
            }
*/
            // adding fields to model
            $submission->setPublisherName($post_data['publisher_name']);
            $submission->setPublisherContactName($post_data['publisher_contact_name']);
            $submission->setPublisherEmail($post_data['publisher_email']);
            $submission->setPublisherState($post_data['state']);
            $submission->setPublisherCity($post_data['city']);

            // country
            $selected_country = $country_repository->find($post_data['country']);
            $submission->setCountry($selected_country);

            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Third step saved with success."));
            return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/fourth", name="submission_new_fourth_step")
     * @Template()
     */
    public function FourthStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $issue_repository = $em->getRepository('Proethos2ModelBundle:Issue');

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        $allow_to_edit_submission = true;
        // if current user is not owner, check the team
        if ($user != $submission->getOwner()) {
            $allow_to_edit_submission = false;

            if(in_array('administrator', $user->getRolesSlug())) {
                $allow_to_edit_submission = true;

            } else {
                foreach($submission->getTeam() as $team_member) {
                    // if current user = some team member, than it allows to edit
                    if ($user == $team_member) {
                        $allow_to_edit_submission = true;
                    }
                }
            }
        }
        if (!$allow_to_edit_submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // adding fields to model
            $submission->setRequestorName($post_data['requestor_name']);
            $submission->setRequestorEmail($post_data['requestor_email']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            if(isset($post_data['issue']) and !empty($post_data['issue'])) {

                $issue = new Issue();
                $issue->setSubmission($submission);
                $issue->setUser($user);
                $issue->setSubmissionNumber($submission->getNumber());
                $issue->setVolume($post_data['issue_volume']);
                $issue->setNumber($post_data['issue_number']);
                $issue->setYear($post_data['issue_year']);
                $issue->setFulltext($post_data['issue_fulltext']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($issue);
                $em->flush();

                $submission->addIssue($issue);
                $em = $this->getDoctrine()->getManager();
                $em->persist($submission);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Issue added with success."));
                return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);

            }

            if(isset($post_data['delete-issue-id']) and !empty($post_data['delete-issue-id'])) {
                $issue = $issue_repository->find($post_data['delete-issue-id']);
                
                if($issue) {
                    $em->remove($issue);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("Issue removed with success."));
                    return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
                }
            }

            // checking required fields
            $required_fields = array('requestor_name', 'requestor_email');
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            // checking required issues
            $total_issues = count($submission->getIssue());
            if( $total_issues < 2 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit at least 2 issues."));
                return $output;
            }

            $session->getFlashBag()->add('success', $translator->trans("Fourth step saved with success."));
            return $this->redirectToRoute('submission_new_fifth_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/fifth", name="submission_new_fifth_step")
     * @Template()
     */
    public function FifthStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        $upload_types = $upload_type_repository->findByStatus(true);
        $output['upload_types'] = $upload_types;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        $allow_to_edit_submission = true;
        // if current user is not owner, check the team
        if ($user != $submission->getOwner()) {
            $allow_to_edit_submission = false;

            if(in_array('administrator', $user->getRolesSlug())) {
                $allow_to_edit_submission = true;

            } else {
                foreach($submission->getTeam() as $team_member) {
                    // if current user = some team member, than it allows to edit
                    if ($user == $team_member) {
                        $allow_to_edit_submission = true;
                    }
                }
            }
        }
        if (!$allow_to_edit_submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            $file = $request->files->get('new-attachment-file');
            if(!empty($file)) {

                if(!isset($post_data['new-attachment-type']) or empty($post_data['new-attachment-type'])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field 'new-attachment-type' is required."));
                    return $output;
                }

                $upload_type = $upload_type_repository->find($post_data['new-attachment-type']);
                if (!$upload_type) {
                    throw $this->createNotFoundException($translator->trans('No upload type found'));
                    return $output;
                }

                $submission_upload = new SubmissionUpload();
                $submission_upload->setSubmission($submission);
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($submission->getNumber());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $submission->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($submission);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("File uploaded with success."));
                return $this->redirectToRoute('submission_new_fifth_step', array('submission_id' => $submission->getId()), 301);

            }

            if(isset($post_data['delete-attachment-id']) and !empty($post_data['delete-attachment-id'])) {
                $submission_upload = $submission_upload_repository->find($post_data['delete-attachment-id']);
                if($submission_upload) {
                    $em->remove($submission_upload);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("File removed with success."));
                    return $this->redirectToRoute('submission_new_fifth_step', array('submission_id' => $submission->getId()), 301);
                }
            }

            // checking required peer review attachment
            $upload_type = $upload_type_repository->findBy(array('slug' => 'peer-review'));
            $upload_type_id = $upload_type[0]->getId();
            $peer_review = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
            if( !$peer_review or count($peer_review) != 1 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit 1 peer review and 1 submission data attachments."));
                return $output;
            }

            // checking required submission data attachment
            $upload_type = $upload_type_repository->findBy(array('slug' => 'submission-data'));
            $upload_type_id = $upload_type[0]->getId();
            $submission_data = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
            if( !$submission_data or count($submission_data) != 1 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit 1 peer review and 1 submission data attachments."));
                return $output;
            }

            $session->getFlashBag()->add('success', $translator->trans("Fifth step saved with success."));
            return $this->redirectToRoute('submission_new_sixth_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/sixth", name="submission_new_sixth_step")
     * @Template()
     */
    public function SixtyStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $util = new Util($this->container, $this->getDoctrine());

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        $allow_to_edit_submission = true;
        // if current user is not owner, check the team
        if ($user != $submission->getOwner()) {
            $allow_to_edit_submission = false;

            if (in_array('administrator', $user->getRolesSlug())) {
                $allow_to_edit_submission = true;
            } else {
                foreach($submission->getTeam() as $team_member) {
                    // if current user = some team member, than it allows to edit
                    if ($user == $team_member) {
                        $allow_to_edit_submission = true;
                    }
                }
            }
        }
        if (!$allow_to_edit_submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        // Revisions
        $revisions = array();
        $final_status = true;

        $text = $translator->trans('Title');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getTitle())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Frequency');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getFrequency())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Standards');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getStandards())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Creation Year');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getCreationYear())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Support');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getSupport())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Full Text');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getFullText())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Mission');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getMission())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Publication Type');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getPublicationType())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Language');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getLanguage())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Thematic Area');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getThematicArea())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Specialty');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getSpecialty())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Funders');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getFunders())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Editor Name');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getEditorName())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Editor Email');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getEditorEmail())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Phone');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getPhone())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Address');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getAddress())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('CEP');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getCep())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Institution');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getInstitution())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('State');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getEditorState())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('City');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getEditorCity())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Requestor Name');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getRequestorName())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Requestor Contact Emails');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getRequestorEmail())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Issues');
        $item = array('text' => $text, 'status' => true);
        $total_issues = count($submission->getIssue());
        if($total_issues < 2) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Peer Review');
        $item = array('text' => $text, 'status' => true);
        $upload_type = $upload_type_repository->findBy(array('slug' => 'peer-review'));
        $upload_type_id = $upload_type[0]->getId();
        $peer_review = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
        if( !$peer_review or count($peer_review) != 1 ) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Submission Data');
        $item = array('text' => $text, 'status' => true);
        $upload_type = $upload_type_repository->findBy(array('slug' => 'submission-data'));
        $upload_type_id = $upload_type[0]->getId();
        $submission_data = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
        if( !$submission_data or count($submission_data) != 1 ) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $output['revisions'] = $revisions;
        $output['final_status'] = $final_status;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            if($final_status) {

                if($post_data['accept-fulltext-upload'] == 'on' and $post_data['confirm-auto-eval'] == 'on') {

                    // gerando um novo pdf da submission original
                    try {
                        $html = $this->renderView(
                            'Proethos2CoreBundle:NewSubmission:showPdf.html.twig',
                            $output
                        );

                        $pdf = $this->get('knp_snappy.pdf');

                        // setting margins
                        $pdf->getInternalGenerator()->setOption('margin-top', '50px');
                        $pdf->getInternalGenerator()->setOption('margin-bottom', '50px');
                        $pdf->getInternalGenerator()->setOption('margin-left', '20px');
                        $pdf->getInternalGenerator()->setOption('margin-right', '20px');

                        // adding pdf to tmp file
                        $filepath = "/tmp/" . date("Y-m-d-H\hi\ms\s") . "-submission.pdf";
                        file_put_contents($filepath, $pdf->getOutputFromHtml($html));

                        $submission_number = count($submission->getProtocol()->getSubmission());

                        $upload_type = $upload_type_repository->findOneBy(array("slug" => "journal"));

                        // send tmp file to upload class and save
                        $pdfFile = new SubmissionUpload();
                        $pdfFile->setSubmission($submission);
                        $pdfFile->setSimpleFile($filepath);
                        $pdfFile->setUploadType($upload_type);
                        $pdfFile->setUser($user);
                        $pdfFile->setSubmissionNumber($submission->getNumber());
                        $em->persist($pdfFile);
                        $em->flush();

                    } catch(\RuntimeException $e) {

                        if($post_data['extra'] != 'no-pdf') {
                            $session->getFlashBag()->add('error', $translator->trans('Problems generating PDF. Please contact the administrator.'));
                            return $output;
                        }
                    }

                    // genrating pdf from translations
                    foreach($submission->getTranslations() as $translation) {

                        $new_output = $output;
                        $new_output['submission'] = $translation;

                        // gerando um novo pdf
                        try {
                            $html = $this->renderView(
                                'Proethos2CoreBundle:NewSubmission:showPdf.html.twig',
                                $new_output
                            );

                            $pdf = $this->get('knp_snappy.pdf');

                            // setting margins
                            $pdf->getInternalGenerator()->setOption('margin-top', '50px');
                            $pdf->getInternalGenerator()->setOption('margin-bottom', '50px');
                            $pdf->getInternalGenerator()->setOption('margin-left', '20px');
                            $pdf->getInternalGenerator()->setOption('margin-right', '20px');

                            // adding pdf to tmp file
                            $filepath = "/tmp/" . date("Y-m-d-H\hi\ms\s") . "-submission.pdf";
                            file_put_contents($filepath, $pdf->getOutputFromHtml($html));

                            $upload_type = $upload_type_repository->findOneBy(array("slug" => "journal"));

                            // send tmp file to upload class and save
                            $pdfFile = new SubmissionUpload();
                            $pdfFile->setSubmission($submission);
                            $pdfFile->setSimpleFile($filepath);
                            $pdfFile->setUploadType($upload_type);
                            $pdfFile->setUser($user);
                            $pdfFile->setSubmissionNumber($submission->getNumber());
                            $em->persist($pdfFile);
                            $em->flush();

                        } catch(\RuntimeException $e) {

                            if($post_data['extra'] != 'no-pdf') {
                                $session->getFlashBag()->add('error', $translator->trans('Problems generating PDF. Please contact the administrator.'));
                                return $output;
                            }
                        }
                    }

                    // in case of editing migrated posts
                    if ($submission->getProtocol()->getIsMigrated() and !$submission->getCanBeEdited()) {
                        $em->persist($submission);
                        $em->flush();

                        $session->getFlashBag()->add('success', $translator->trans("Journal submitted with success!"));
                        return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $submission->getProtocol()->getId()), 301);
                    }

                    // updating protocol and setting status
                    $protocol = $submission->getProtocol();
                    $protocol->setStatus("S");
                    $protocol->setDateInformed(new \DateTime());
                    $protocol->setUpdatedIn(new \DateTime());
                    $em->persist($protocol);
                    $em->flush();

                    $submission->setIsSended(true);
                    $em->persist($submission);
                    $em->flush();

                    $protocol_history = new ProtocolHistory();
                    $protocol_history->setProtocol($protocol);

                    $protocol_history->setMessage($translator->trans("Submission of journal."));
                    $em->persist($protocol_history);
                    $em->flush();

                    if($protocol->getMonitoringAction()) {
                        // sending email
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                        $recipients = array();
                        foreach($user_repository->findAll() as $secretary) {
                            if(in_array("secretary", $secretary->getRolesSlug())) {
                                $recipients[] = $secretary;
                            }
                        }

                        foreach($recipients as $recipient) {
                            $message = \Swift_Message::newInstance()
                            ->setSubject("[LILACS] " . $translator->trans("A new monitoring action has been submitted."))
                            ->setFrom($util->getConfiguration('committee.email'))
                            ->setTo($recipient->getEmail())
                            ->setBody(
                                $translator->trans("Dear investigator,") .
                                "<br />" .
                                "<br />" . $translator->trans("This is to remind you that protocol <b>%protocol%</b> has a pending
                                                                   monitoring action.",
                                                                   array(
                                                                       '%protocol%' => $protocol->getCode(),
                                                                   )) .
                                "<br />" .
                                "<br />" . $translator->trans("Please access your account in the system to present your monitoring action.") .
                                "<br />" .
                                "<br />" . $translator->trans("Regards") . "," .
                                "<br />" . $translator->trans("Proethos2 Team") .
                                "<br /><br />"
                                ,
                                'text/html'
                            );

                            $send = $this->get('mailer')->send($message);
                        }

                        $session->getFlashBag()->add('success', $translator->trans("Amendment submitted with success!"));
                    } else {
                        // sending email
                        $_locale = $request->getSession()->get('_locale');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        $home_url = $baseurl . $this->generateUrl('home');
                        $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                        $help = $help_repository->find(108);
                        $translations = $trans_repository->findTranslations($help);
                        $text = $translations[$_locale];
                        $body = $text['message'];
                        $body = str_replace("%home_url%", $home_url, $body);
                        $body = str_replace("%journal_url%", $url, $body);
                        $body = str_replace("\r\n", "<br />", $body);
                        $body .= "<br /><br />";

                        $recipients = array($protocol->getMainSubmission()->getOwner());
                        foreach($recipients as $recipient) {
                            $body = str_replace("%username%", $recipient->getName(), $body);
                            $message = \Swift_Message::newInstance()
                            ->setSubject("[LILACS] " . $translator->trans("Your journal was sent to review."))
                            ->setFrom($util->getConfiguration('committee.email'))
                            ->setTo($recipient->getEmail())
                            ->setBody(
                                $body
                                ,
                                'text/html'
                            );

                            $send = $this->get('mailer')->send($message);
                        }

                        $help = $help_repository->find(121);
                        $translations = $trans_repository->findTranslations($help);
                        $text = $translations[$_locale];
                        $body = $text['message'];
                        $body = str_replace("%home_url%", $home_url, $body);
                        $body = str_replace("%journal_url%", $url, $body);
                        $body = str_replace("\r\n", "<br />", $body);
                        $body .= "<br /><br />";

                        $secretaries_emails = array();
                        foreach($user_repository->findAll() as $secretary) {
                            if(in_array("secretary", $secretary->getRolesSlug())) {
                                $secretaries_emails[] = $secretary->getEmail();
                            }
                        }

                        $message = \Swift_Message::newInstance()
                        ->setSubject("[LILACS] " . $translator->trans("New submission on LILACS Journal Evaluation platform"))
                        ->setFrom($util->getConfiguration('committee.email'))
                        ->setTo($secretaries_emails)
                        ->setBody(
                            $body
                            ,
                            'text/html'
                        );

                        $send = $this->get('mailer')->send($message);

                        $session->getFlashBag()->add('success', $translator->trans("Journal submitted with success!"));
                    }

                    return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);

                } else {
                    $session->getFlashBag()->add('error', $translator->trans("You must accept the conditions for sending submission."));
                }
            } else {
                $session->getFlashBag()->add('error', $translator->trans('You have pending reviews.'));
            }
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/pdf", name="submission_generate_pdf")
     * @Template()
     */
    public function showPdfAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        // getting frequency list
        $frequency = array(
            "FC"  => $translator->trans("Fluxo Contínuo"),
            "S"   => $translator->trans("Semestral"),
            "QUA" => $translator->trans("Quadrimestral"),
            "T"   => $translator->trans("Trimestral"),
            "B"   => $translator->trans("Bimestral"),
            "M"   => $translator->trans("Mensal"),
            "QUI" => $translator->trans("Quinzenal")
        );

        $output['frequency'] = $frequency;

        // getting support list
        $support = array(
            "I"  => $translator->trans("Impresso"),
            "E"  => $translator->trans("Eletrônico"),
            "IE" => $translator->trans("Impresso e Eletrônico")
        );

        $output['support'] = $support;

        // getting fulltext list
        $fulltext = array(
            "T" => $translator->trans("Portal próprio"),
            "B" => $translator->trans("Repositório institucional"),
            "M" => $translator->trans("Não provemos acesso ao texto completo. No entanto, vamos ingressar na iniciativa LILACS-Express para disponibilizá-los")
        );
        
        $output['fulltext'] = $fulltext;

        if (!$submission or ($submission->getCanBeEdited() and !in_array('administrator', $user->getRolesSlug()))) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        $datetime = new \DateTime();
        $year = $datetime->format('Y');
        $output['year'] = $year;

        // $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        // if (($number %100) >= 11 && ($number%100) <= 13)
        //    $abbreviation = $number. 'th';
        // else
        //    $abbreviation = $number. $ends[$number % 10];

        $season = $year - 1999;
        $season .= 'ª';
        $output['season'] = $season;

        $html = $this->renderView(
            'Proethos2CoreBundle:NewSubmission:showPdf.html.twig',
            $output
        );

        $pdf = $this->get('knp_snappy.pdf');

        // setting margins
        $pdf->getInternalGenerator()->setOption('margin-top', '50px');
        $pdf->getInternalGenerator()->setOption('margin-bottom', '50px');
        $pdf->getInternalGenerator()->setOption('margin-left', '20px');
        $pdf->getInternalGenerator()->setOption('margin-right', '20px');

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf'
            )
        );
    }
}

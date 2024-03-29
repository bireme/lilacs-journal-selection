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

use Proethos2\CoreBundle\Util\Util;
use Proethos2\CoreBundle\Util\CSVResponse;

use Proethos2\ModelBundle\Entity\Meeting;
use Proethos2\ModelBundle\Entity\Faq;
use Proethos2\ModelBundle\Entity\Document;
use Proethos2\ModelBundle\Entity\User;
use Proethos2\ModelBundle\Entity\UploadTypeExtension;
use Proethos2\ModelBundle\Entity\UploadType;
use Proethos2\ModelBundle\Entity\Specialty;
use Proethos2\ModelBundle\Entity\PublicationType;
use Proethos2\ModelBundle\Entity\Language;
use Proethos2\ModelBundle\Entity\ThematicArea;


class CRUDController extends Controller
{
    /**
     * @Route("/committee/meeting", name="crud_committee_meeting_list")
     * @Template()
     */
    public function listMeetingAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $meeting_repository = $em->getRepository('Proethos2ModelBundle:Meeting');

        $meetings = $meeting_repository->findBy(array(), array('date' => 'DESC'));

        // search parameter
        $search_query = $request->query->get('q');
        if($search_query) {
            $meetings = $meeting_repository->createQueryBuilder('m')
               ->where('m.subject LIKE :query')
               ->setParameter('query', "%". $search_query ."%")
               ->addOrderBy('m.date', 'DESC')
               ->getQuery()
               ->getResult();
        }

        $output['meetings'] = $meetings;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            if(isset($post_data['new-meeting-date'])) {


                // checking required fields
                foreach(array('new-meeting-date', 'new-meeting-subject', 'new-meeting-content') as $field) {
                    if(!isset($post_data[$field]) or empty($post_data[$field])) {
                        $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                        return $output;
                    }
                }

                $meeting = new Meeting();
                $meeting->setDate(new \DateTime($post_data['new-meeting-date']));
                $meeting->setSubject($post_data['new-meeting-subject']);
                $meeting->setContent($post_data['new-meeting-content']);

                $em->persist($meeting);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Meeting created with success."));
                return $this->redirectToRoute('crud_committee_meeting_list', array(), 301);
            }

        }

        return $output;
    }

    /**
     * @Route("/committee/meeting/{meeting_id}", name="crud_committee_meeting_update")
     * @Template()
     */
    public function updateMeetingAction($meeting_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $meeting_repository = $em->getRepository('Proethos2ModelBundle:Meeting');

        // getting the current meeting
        $meeting = $meeting_repository->find($meeting_id);
        $output['meeting'] = $meeting;

        if (!$meeting) {
            throw $this->createNotFoundException($translator->trans('No meeting found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('new-meeting-date', 'new-meeting-subject', 'new-meeting-content') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $meeting->setDate(new \DateTime($post_data['new-meeting-date']));
            $meeting->setSubject($post_data['new-meeting-subject']);
            $meeting->setContent($post_data['new-meeting-content']);

            $em->persist($meeting);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Meeting updated with success."));
            return $this->redirectToRoute('crud_committee_meeting_list', array(), 301);
        }

        return $output;
    }

     /**
     * @Route("/committee/meeting/{meeting_id}/show", name="crud_committee_meeting_show")
     * @Template()
     */
    public function showMeetingAction($meeting_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $meeting_repository = $em->getRepository('Proethos2ModelBundle:Meeting');
        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');

        // getting the current meeting
        $meeting = $meeting_repository->find($meeting_id);
        $output['meeting'] = $meeting;

        if (!$meeting) {
            throw $this->createNotFoundException($translator->trans('No meeting found'));
        }

        $protocols = $protocol_repository->findBy(array('meeting' => $meeting));
        $output['protocols'] = $protocols;

        return $output;
    }

    /**
     * @Route("/committee/meeting/{meeting_id}/delete", name="crud_committee_meeting_delete")
     * @Template()
     */
    public function deleteMeetingAction($meeting_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $meeting_repository = $em->getRepository('Proethos2ModelBundle:Meeting');

        // getting the current meeting
        $meeting = $meeting_repository->find($meeting_id);
        $output['meeting'] = $meeting;

        if (!$meeting) {
            throw $this->createNotFoundException($translator->trans('No meeting found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('meeting-delete') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $em->remove($meeting);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Meeting deleted with success."));
            return $this->redirectToRoute('crud_committee_meeting_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/journal", name="crud_committee_protocol_list")
     * @Template()
     */
    public function listCommitteeProtocolAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $committee_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolCommitteeRevision');
        $adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');

        // getting the reject reason options
        $reject_reason = array(
            'A' => $translator->trans('Fiz ou faço parte da equipe editorial'),
            'B' => $translator->trans('Conheço os editores'),
            'C' => $translator->trans('Estou aposentado(a)'),
            'D' => $translator->trans('Muitas atividades no momento'),
            'E' => $translator->trans('Outro motivo')
        );
        $output['reject_reason'] = $reject_reason;

        // search and status parameter
        $status_array = array('D', 'S', 'R', 'I', 'E', 'H', "F", "A", "N", "C", "X", "V");
        $search_query = $request->query->get('q');
        $status_query = $request->query->get('status');

        $p_order_query  = "p.created";
        $pr_order_query = "pr.created";
        
        if ( $request->query->get('order') and 'date_informed' == $request->query->get('order') ) {
            $p_order_query  = "p.date_informed";
            $pr_order_query = "pr.date_informed";
        }

        if(!empty($status_query))
            $status_array = array($status_query);

        if ( in_array('secretary', $user->getRolesSlug()) ) {
            $query = $protocol_repository->createQueryBuilder('p')
                ->join('p.main_submission', 's')
                ->where("s.title LIKE :query AND p.status IN (:status)")
                ->addOrderBy($p_order_query, 'DESC')
                ->addOrderBy('p.created', 'DESC')
                ->setParameter('query', "%". $search_query ."%")
                ->setParameter('status', $status_array);
        } elseif ( in_array('member-of-committee', $user->getRolesSlug()) ) {
            $subquery = $protocol_repository->createQueryBuilder('pr')
                ->join('pr.committee_revision', 'r')
                ->where("r.member = :owner AND (r.is_final_revision = 1 OR r.rejected = 1)")
                ->addOrderBy($pr_order_query, 'DESC')
                ->addOrderBy('pr.created', 'DESC')
                ->setParameter('owner', $user);
            $query = $protocol_repository->createQueryBuilder('p')
                ->join('p.main_submission', 's')
                ->where("s.title LIKE :query AND p.status IN (:status) AND p NOT IN ($subquery)")
                ->addOrderBy($p_order_query, 'DESC')
                ->addOrderBy('p.created', 'DESC')
                ->setParameter('query', "%". $search_query ."%")
                ->setParameter('status', $status_array)
                ->setParameter('owner', $user);
        } else {
            $query = $protocol_repository->createQueryBuilder('p')
                ->join('p.main_submission', 's')
                ->leftJoin('p.adhoc_revision', 'r')
                ->where("s.title LIKE :query AND p.status = 'E' AND r.member = :owner AND r.is_final_revision = 0 AND r.rejected = 0")
                ->addOrderBy($p_order_query, 'DESC')
                ->addOrderBy('p.created', 'DESC')
                ->setParameter('query', "%". $search_query ."%")
                ->setParameter('owner', $user);
        }

        $protocols = $query->getQuery()->getResult();
        $output['protocols'] = $protocols;

        // output parameter
        $output_parameter = $request->query->get('output');
        if($output_parameter == 'csv') {
            $csv_headers = array('CODE', 'ID', 'OWNER', 'STATUS', 'PUBLIC TITLE', 'TYPE', 'REJECT REASON',
                'OPINIONS REQUIRED', 'DATE INFORMED', 'UPDATED IN', 'REVISED IN', 'DECISION IN', 'MEETING');
            $csv_output = array();
            foreach($protocols as $protocol) {
                $type = "Journal";

                $current_line = array();
                $current_line[] = $protocol->getCode();
                $current_line[] = $protocol->getId();
                $current_line[] = $protocol->getOwner()->getUsername();
                $current_line[] = $protocol->getStatusLabel();
                $current_line[] = $protocol->getMainSubmission()->getTitle();
                $current_line[] = $type;
                $current_line[] = $protocol->getRejectReason();
                $current_line[] = $protocol->getOpinionRequired();
                $current_line[] = $protocol->getDateInformed() ? $protocol->getDateInformed()->format("Y-m-d H:i") : "";
                $current_line[] = $protocol->getUpdatedIn() ? $protocol->getUpdatedIn()->format("Y-m-d H:i") : "";
                $current_line[] = $protocol->getRevisedIn() ? $protocol->getRevisedIn()->format("Y-m-d H:i") : "";
                $current_line[] = $protocol->getDecisionIn() ? $protocol->getDecisionIn()->format("Y-m-d H:i") : "";
                $current_line[] = $protocol->getMeeting() ? $protocol->getMeeting()->getSubject() : "";
                $csv_output[] = $current_line;
            }

            $response = new CSVResponse( $csv_output, 200, $csv_headers );
            $response->setFilename( "lilacs-journals.csv" );
            return $response;
        }

        $output['committee_revisions'] = $committee_revision_repository->findBy(array("member" => $user, "is_final_revision" => 1));
        
        // $query = $protocol_repository->createQueryBuilder('p')
        //     ->join('p.main_submission', 's')
        //     ->leftJoin('p.committee_revision', 'r')
        //     ->where("s.title LIKE :query AND p.status IN (:status) AND r.member = :owner AND r.is_final_revision = 1")
        //     ->orderBy("p.created", 'DESC')
        //     ->setParameter('query', "%". $search_query ."%")
        //     ->setParameter('status', $status_array)
        //     ->setParameter('owner', $user);
        
        // $committee_revisions = $query->getQuery()->getResult();
        // $output['committee_revisions'] = $committee_revisions;

        $output['adhoc_revisions'] = $adhoc_revision_repository->findBy(array("member" => $user, "is_final_revision" => 1));
        
        // $query = $protocol_repository->createQueryBuilder('p')
        //     ->join('p.main_submission', 's')
        //     ->leftJoin('p.adhoc_revision', 'r')
        //     ->where("s.title LIKE :query AND p.status IN (:status) AND r.member = :owner AND r.is_final_revision = 1")
        //     ->orderBy("p.created", 'DESC')
        //     ->setParameter('query', "%". $search_query ."%")
        //     ->setParameter('status', $status_array)
        //     ->setParameter('owner', $user);
        
        // $adhoc_revisions = $query->getQuery()->getResult();
        // $output['adhoc_revisions'] = $adhoc_revisions;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {
            // getting post data
            $post_data = $request->request->all();
        }

        return $output;
    }

    /**
     * @Route("/editor/journal", name="crud_investigator_protocol_list")
     * @Template()
     */
    public function listInvestigatorProtocolAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');

        // search and status parameter
        $status_array = array('D', 'S', 'R', 'I', 'E', 'H', 'F', 'A', 'N', 'C', 'X', 'V');
        $search_query = $request->query->get('q');
        $status_query = $request->query->get('status');

        if(!empty($status_query))
            $status_array = array($status_query);

        $query = $protocol_repository->createQueryBuilder('p')
           ->join('p.main_submission', 's')
           ->leftJoin('s.team', 't')
           ->leftJoin('p.committee_revision', 'cr')
           ->leftJoin('p.adhoc_revision', 'ar')
           ->where("s.title LIKE :query AND p.status IN (:status) AND ((s.owner = :owner OR t = :owner) OR (cr.member = :owner OR ar.member = :owner))")
           ->orderBy("p.created", 'DESC')
           ->setParameter('query', "%". $search_query ."%")
           ->setParameter('status', $status_array)
           ->setParameter('owner', $user);

        $protocols = $query->getQuery()->getResult();
        $output['protocols'] = $protocols;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

        }

        return $output;
    }

    /**
     * @Route("/committee/faq", name="crud_committee_faq_list")
     * @Template()
     */
    public function listCommitteeFaqAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $faq_repository = $em->getRepository('Proethos2ModelBundle:Faq');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $faqs = $faq_repository->findAll();

        // search parameter
        $search_query = $request->query->get('q');
        if($search_query) {
            $faqs = $faq_repository->createQueryBuilder('m')
               ->where('m.question LIKE :query')
               ->setParameter('query', "%". $search_query ."%")
               ->getQuery()
               ->getResult();
        }

        $output['faqs'] = $faqs;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach(array('new-question', 'new-question-answer') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $question = new Faq();
            $question->setTranslatableLocale('en');

            $question->setQuestion($post_data['new-question']);
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["new-question-$locale"])) {
                    $trans_repository = $trans_repository->translate($question, 'question', $locale, $post_data["new-question-$locale"]);
                }
            }

            $question->setAnswer($post_data['new-question-answer']);
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["new-question-answer-$locale"])) {
                    $trans_repository = $trans_repository->translate($question, 'answer', $locale, $post_data["new-question-answer-$locale"]);
                }
            }

            if(isset($post_data['new-question-status'])) {
                $question->setStatus(true);
            }

            $em->persist($question);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Question created with success."));
            return $this->redirectToRoute('crud_committee_faq_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/faq/{faq_id}", name="crud_committee_faq_update")
     * @Template()
     */
    public function updateCommitteeFaqAction($faq_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $faq_repository = $em->getRepository('Proethos2ModelBundle:Faq');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');


        // getting the current faq
        $question = $faq_repository->find($faq_id);
        $output['question'] = $question;

        if (!$question) {
            throw $this->createNotFoundException($translator->trans('No FAQ found'));
        }

        $translations = $trans_repository->findTranslations($question);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach(array('new-question', 'new-question-answer') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $question->setTranslatableLocale('en');

            $question->setQuestion($post_data['new-question']);
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["new-question-$locale"])) {
                    $trans_repository = $trans_repository->translate($question, 'question', $locale, $post_data["new-question-$locale"]);
                }
            }

            $question->setAnswer($post_data['new-question-answer']);
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["new-question-answer-$locale"])) {
                    $trans_repository = $trans_repository->translate($question, 'answer', $locale, $post_data["new-question-answer-$locale"]);
                }
            }

            $question->setStatus(false);
            if(isset($post_data['new-question-status'])) {
                $question->setStatus(true);
            }

            $em->persist($question);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Question updated with success."));
            return $this->redirectToRoute('crud_committee_faq_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/faq/{faq_id}/delete", name="crud_committee_faq_delete")
     * @Template()
     */
    public function deleteCommitteeFaqAction($faq_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $faq_repository = $em->getRepository('Proethos2ModelBundle:Faq');

        // getting the current faq
        $question = $faq_repository->find($faq_id);
        $output['question'] = $question;

        if (!$question) {
            throw $this->createNotFoundException($translator->trans('No FAQ found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('question-delete') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $this->redirectToRoute('crud_committee_faq_list', array(), 301);
                }
            }

            $em->remove($question);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Question deleted with success."));
            return $this->redirectToRoute('crud_committee_faq_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/faq", name="crud_admin_faq_list")
     * @Route("/public/faq", name="crud_public_faq_list")
     * @Template()
     */
    public function listFaqAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $faq_repository = $em->getRepository('Proethos2ModelBundle:Faq');

        $questions = $faq_repository->findByStatus(true);
        $output['questions'] = $questions;

        return $output;
    }

    /**
     * @Route("/committee/documents", name="crud_committee_document_list")
     * @Template()
     */
    public function listCommitteeDocumentAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $document_repository = $em->getRepository('Proethos2ModelBundle:Document');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');

        $documents = $document_repository->findAll();

        // search parameter
        $search_query = $request->query->get('q');
        if($search_query) {
            $documents = $document_repository->createQueryBuilder('m')
               ->where('m.title LIKE :query')
               ->setParameter('query', "%". $search_query ."%")
               ->getQuery()
               ->getResult();
        }

        $output['documents'] = $documents;

        $roles = $role_repository->findAll();
        $output['roles'] = $roles;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();
            $file = $request->files->get('file');

            if(empty($file)) {
                $session->getFlashBag()->add('error', $translator->trans("Field 'file' is required."));
                return $output;
            }

            // checking required fields
            foreach(array('title', 'roles') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $document = new Document();
            foreach($post_data['roles'] as $role) {
                $role = $role_repository->find($role);
                $document->addRole($role);
            }

            $document->setTitle($post_data['title']);
            $document->setDescription($post_data['description']);
            $document->setFile($file);

            if(isset($post_data['status'])) {
                $document->setStatus(true);
            }

            $em->persist($document);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Question created with success."));
            return $this->redirectToRoute('crud_committee_document_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/document/{document_id}", name="crud_committee_document_update")
     * @Template()
     */
    public function updateCommitteeDocumentAction($document_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $document_repository = $em->getRepository('Proethos2ModelBundle:Document');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');

        // getting the current document
        $document = $document_repository->find($document_id);
        $output['document'] = $document;

        if (!$document) {
            throw $this->createNotFoundException($translator->trans('No document found'));
        }

        $roles = $role_repository->findAll();
        $output['roles'] = $roles;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach(array('title',) as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            foreach($document->getRoles() as $role) {
                $document->removeRole($role);
            }

            foreach($post_data['roles'] as $role) {
                $role = $role_repository->find($role);
                $document->addRole($role);
            }

            $document->setTitle($post_data['title']);
            $document->setDescription($post_data['description']);

            $document->setStatus(false);
            if(isset($post_data['status'])) {
                $document->setStatus(true);
            }

            $em->persist($document);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Document updated with success."));
            return $this->redirectToRoute('crud_committee_document_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/document/{document_id}/delete", name="crud_committee_document_delete")
     * @Template()
     */
    public function deleteCommitteeDocumentAction($document_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $document_repository = $em->getRepository('Proethos2ModelBundle:Document');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');

        // getting the current document
        $document = $document_repository->find($document_id);
        $output['document'] = $document;

        if (!$document) {
            throw $this->createNotFoundException($translator->trans('No document found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('delete') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $em->remove($document);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Document deleted with success."));
            return $this->redirectToRoute('crud_committee_document_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/document", name="crud_document_list")
     * @Template()
     */
    public function listDocumentAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $document_repository = $em->getRepository('Proethos2ModelBundle:Document');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');

        $documents = $document_repository->findAll();

        // search parameter
        $search_query = $request->query->get('q');
        if($search_query) {
            $documents = $document_repository->createQueryBuilder('m')
               ->where('m.title LIKE :query')
               ->setParameter('query', "%". $search_query ."%")
               ->getQuery()
               ->getResult();
        }

        $output['documents'] = $documents;

        $roles = $role_repository->findAll();
        $output['roles'] = $roles;

        return $output;
    }

    /**
     * @Route("/committee/users", name="crud_committee_user_list")
     * @Template()
     */
    public function listCommitteeUserAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $util = new Util($this->container, $this->getDoctrine());

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');
        $country_repository = $em->getRepository('Proethos2ModelBundle:Country');

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        // getting specialty list
        $specialty_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        $specialty = $specialty_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['specialty'] = $specialty;

        $rating_query = $request->query->get('rating');
        $order_field = 'name';
        $order_sort = 'ASC';
        if ( $rating_query ) {
            $order_field = 'average';
            $order_sort = $rating_query;
        }

        $role_query = $request->query->get('role');
        if ( $role_query ) {
            if ( 'none' == $role_query ) {
                $users = $user_repository->createQueryBuilder('u')
                    ->leftJoin('u.proethos2_roles', 'r')
                    ->where('r.slug IS NULL')
                    ->addOrderBy('u.'.$order_field, $order_sort)
                    ->getQuery()
                    ->getResult();
            } else {
                $users = $user_repository->createQueryBuilder('u')
                    ->join('u.proethos2_roles', 'r', 'WITH', 'r.slug = :slug')
                    ->addOrderBy('u.'.$order_field, $order_sort)
                    ->setParameter('slug', $role_query)
                    ->getQuery()
                    ->getResult();
            }
        } else {
            $users = $user_repository->findBy(array(), array($order_field => $order_sort));
        }
        
        // search parameters
        $search_query = $request->query->get('q');
        $status_query = $request->query->get('status');
        $specialty_query = intval($request->query->get('specialty'));
        $specialty_object = $specialty_repository->find($specialty_query);

        if($search_query or $specialty_object or $status_query) {
            $where = 'u.name LIKE :query';
            $orm_params = array('query' => "%". $search_query ."%");
            
            if ( $status_query and in_array($status_query, array('active', 'inactive')) ) {
                $orm_params['is_active'] = ( 'active' == $status_query ) ? 1 : 0;
                $where = $where . ' AND u.isActive = :is_active';
            }

            if ( $specialty_object ) {
                $orm_params['specialty'] = $specialty_object;
                $where = $where . ' AND u.specialty = :specialty';
            }

            if ( $role_query ) {
                if ( 'none' == $role_query ) {
                    $where = $where . ' AND r.slug IS NULL';
                    $users = $user_repository->createQueryBuilder('u')
                        ->leftJoin('u.proethos2_roles', 'r')
                        ->where($where)
                        ->addOrderBy('u.'.$order_field, $order_sort)
                        ->setParameters($orm_params)
                        ->getQuery()
                        ->getResult();
                } else {
                    $orm_params['slug'] = $role_query;
                    $users = $user_repository->createQueryBuilder('u')
                        ->join('u.proethos2_roles', 'r', 'WITH', 'r.slug = :slug')
                        ->where($where)
                        ->addOrderBy('u.'.$order_field, $order_sort)
                        ->setParameters($orm_params)
                        ->getQuery()
                        ->getResult();
                }
            } else {
                $users = $user_repository->createQueryBuilder('u')
                    ->where($where)
                    ->addOrderBy('u.'.$order_field, $order_sort)
                    ->setParameters($orm_params)
                    ->getQuery()
                    ->getResult();
            }
        }

        $output['users'] = $users;

        // output parameter
        $output_parameter = $request->query->get('output');
        if($output_parameter == 'csv') {
            $csv_headers = array('ID', 'USERNAME', 'EMAIL', 'ROLES', 'ACTIVE?', 'NAME', 'COUNTRY', 'INSTITUTION', 'LATTES', 'PRACTICE AREA', 'OTHER PRACTICE AREA');
            $csv_output = array();
            foreach($users as $user) {
                $roles = array(
                    'investigator'        => $translator->trans("Editor"),
                    'secretary'           => $translator->trans("Coordination"),
                    'member-of-committee' => $translator->trans("Member of Committee"),
                    'member-ad-hoc'       => $translator->trans("Reviewer ad-hoc"),
                    'administrator'       => $translator->trans("Administrator")
                );
                $csv_roles = array();
                foreach( $user->getProethos2Roles() as $role ) {
                    $csv_roles[] = $roles[$role->getSlug()];
                }

                $current_line = array();
                $current_line[] = $user->getId();
                $current_line[] = $user->getUsername();
                $current_line[] = $user->getEmail();
                $current_line[] = implode(", ", $csv_roles);
                $current_line[] = $user->getIsActive() == 1 ? $translator->trans("Yes") : $translator->trans("No");
                $current_line[] = $user->getName();
                $current_line[] = $user->getCountry() ? $user->getCountry()->getName() : '';
                $current_line[] = $user->getInstitution();
                $current_line[] = $user->getLattes();
                $current_line[] = $user->getSpecialty();
                $current_line[] = $user->getOtherSpecialty();
                $csv_output[] = $current_line;
            }

            $response = new CSVResponse( $csv_output, 200, $csv_headers );
            $response->setFilename( "proethos2-users.csv" );
            return $response;
        }

        $roles = $role_repository->findAll();
        $output['roles'] = $roles;

        $countries = $country_repository->findBy(array(), array('name' => 'asc'));
        $output['countries'] = $countries;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach( array('name', 'username', 'email', 'country', 'lattes') as $field ) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $country = $country_repository->find($post_data['country']);

            $user = new User();
            $user->setCountry($country);
            $user->setName($post_data['name']);
            $user->setUsername($post_data['username']);
            $user->setEmail($post_data['email']);
            $user->setOtherSpecialty($post_data['other-specialty']);
            $user->setInstitution($post_data['institution']);
            $user->setLattes($post_data['lattes']);
            $user->setFirstAccess(false);

            // specialty
            $selected_specialty = $specialty_repository->find($post_data['specialty']);
            $user->setSpecialty($selected_specialty);

            if(isset($post_data['status'])) {
                $user->setIsActive(true);
            }

            // adding user role
            $user->addProethos2Role($role_repository->findOneBy(array('slug' => 'member-ad-hoc')));

            $encoderFactory = $this->get('security.encoder_factory');
            $encoder = $encoderFactory->getEncoder($user);
            $salt = $user->getSalt(); // this should be different for every user
            $password = $encoder->encodePassword(md5(date("YmdHis")), $salt);
            $user->setPassword($password);

            // Send email to created user with the link to change the first password
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

            $hashcode = $user->generateHashcode();
            $em->persist($user);
            $em->flush();

            // TODO need to get the relative path
            $url = $baseurl . "/public/account/reset_my_password?hashcode=" . $hashcode;
            
            $locale = $request->getSession()->get('_locale');
            $help = $help_repository->find(114);
            $translations = $trans_repository->findTranslations($help);
            $text = $translations[$locale];
            $body = $text['message'];
            $body = str_replace("%username%", $post_data['name'], $body);
            $body = str_replace("%reset_password_url%", $url, $body);
            $body = str_replace("\r\n", "<br />", $body);
            $body .= "<br /><br />";

            $message = \Swift_Message::newInstance()
            ->setSubject("[LILACS] " . $translator->trans("Set your password"))
            ->setFrom($util->getConfiguration('committee.email'))
            ->setTo($post_data['email'])
            ->setBody(
                $body
                ,
                'text/html'
            );

            $send = $this->get('mailer')->send($message);

            $em->persist($user);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("User created with success."));
            return $this->redirectToRoute('crud_committee_user_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/account/update_profile", name="crud_user_profile_update")
     * @Template()
     */
    public function updateUserProfileAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $country_repository = $em->getRepository('Proethos2ModelBundle:Country');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $output['user'] = $user;

        $countries = $country_repository->findBy(array(), array('name' => 'asc'));
        $output['countries'] = $countries;

        // getting specialty list
        $specialty_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        $specialty = $specialty_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['specialty'] = $specialty;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach(array('email', 'name', 'country', 'lattes' ) as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $country = $country_repository->find($post_data['country']);

            $user->setCountry($country);
            $user->setEmail($post_data['email']);
            $user->setName($post_data['name']);
            $user->setLattes($post_data['lattes']);
            $user->setOtherSpecialty($post_data['other-specialty']);
            $user->setInstitution($post_data['institution']);

            // specialty
            $selected_specialty = $specialty_repository->find($post_data['specialty']);
            $user->setSpecialty($selected_specialty);

            $em->persist($user);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("User updated with success."));
            $referer = $request->headers->get('referer');
            return $this->redirect($referer, 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/user/{user_id}", name="crud_committee_user_update")
     * @Template()
     */
    public function updateCommitteeUserAction($user_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $util = new Util($this->container, $this->getDoctrine());

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');
        $country_repository = $em->getRepository('Proethos2ModelBundle:Country');

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        // getting specialty list
        $specialty_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        $specialty = $specialty_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['specialty'] = $specialty;

        // getting the current user
        $user = $user_repository->find($user_id);
        $output['user'] = $user;

        if (!$user) {
            throw $this->createNotFoundException($translator->trans('No user found'));
        }

        $roles = $role_repository->findAll();
        $output['roles'] = $roles;

        $countries = $country_repository->findBy(array(), array('name' => 'asc'));
        $output['countries'] = $countries;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {
            // user status
            $user_status = $user->getIsActive();

            // getting post data
            $post_data = $request->request->all();

            // checking required fields
            foreach(array('email', 'name', 'country', 'lattes' ) as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $country = $country_repository->find($post_data['country']);

            $user->setCountry($country);
            $user->setEmail($post_data['email']);
            $user->setName($post_data['name']);
            $user->setOtherSpecialty($post_data['other-specialty']);
            $user->setInstitution($post_data['institution']);
            $user->setLattes($post_data['lattes']);

            // specialty
            $selected_specialty = $specialty_repository->find($post_data['specialty']);
            $user->setSpecialty($selected_specialty);

            $user->setIsActive(false);
            if(isset($post_data['status'])) {
                $user->setIsActive(true);
            }

            $em->persist($user);
            $em->flush();

            if(isset($post_data['status']) && !$user_status) {
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $home_url = $baseurl . $this->generateUrl('home');

                $locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(115);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$locale];
                $body = $text['message'];
                $body = str_replace("%username%", $user->getName(), $body);
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";

                $message = \Swift_Message::newInstance()
                ->setSubject("[LILACS] " . $translator->trans("Confirmation of valid access to the Proethos2 platform"))
                ->setFrom($util->getConfiguration('committee.email'))
                ->setTo($user->getEmail())
                ->setBody(
                    $body
                    ,
                    'text/html'
                );

                $send = $this->get('mailer')->send($message);
            }

            $session->getFlashBag()->add('success', $translator->trans("User updated with success."));
            return $this->redirectToRoute('crud_committee_user_list', array(), 301);

        }

        return $output;
    }

    /**
     * @Route("/committee/user/{user_id}/key", name="crud_committee_user_get_key_to_change_password")
     * @Template()
     */
    public function getKeyToChangePasswordCommitteeUserAction($user_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        // getting the current user
        $user = $user_repository->find($user_id);
        $output['user'] = $user;

        if (!$user) {
            throw $this->createNotFoundException($translator->trans('No user found'));
        }

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        $hashcode = $user->generateHashcode();
        $em->persist($user);
        $em->flush();

        // TODO need to get the relative path
        $output['url'] = $baseurl . "/public/account/reset_my_password?hashcode=" . $hashcode;

        return $output;
    }

    /**
     * @Route("/committee/user/{user_id}/role", name="crud_committee_user_role_update")
     * @Template()
     */
    public function updateCommitteeUserRoleAction($user_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');

        // getting the current user
        $user = $user_repository->find($user_id);
        $output['user'] = $user;

        if (!$user) {
            throw $this->createNotFoundException($translator->trans('No user found'));
        }

        $roles = $role_repository->findAll();
        $output['roles'] = $roles;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            foreach($roles as $role) {
                $user->removeProethos2Role($role);

                if(in_array($role->getSlug(), array_keys($post_data))) {
                    $user->addProethos2Role($role);
                }
            }

            // var_dump($post_data);die;
            $em->persist($user);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("User updated with success."));
            return $this->redirectToRoute('crud_committee_user_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/committee/user/{user_id}/delete", name="crud_committee_user_delete")
     * @Template()
     */
    public function deleteCommitteeUserAction($user_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        // getting the current user
        $user = $user_repository->find($user_id);
        $output['user'] = $user;

        if (!$user) {
            throw $this->createNotFoundException($translator->trans('No user found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('delete') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $em->remove($user);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("User deleted with success."));
            return $this->redirectToRoute('crud_committee_user_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/contact", name="crud_admin_contact_list")
     * @Route("/public/contact", name="crud_public_contact_list")
     * @Template()
     */
    public function listContactAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $route = $request->get('_route');

        $util = new Util($this->container, $this->getDoctrine());

        $output['committee_prefix'] = $util->getConfiguration('committee.prefix');
        $output['committee_name'] = $util->getConfiguration('committee.name');
        $output['committee_email'] = $util->getConfiguration('committee.email');
        $output['committee_address'] = $util->getConfiguration('committee.address');
        $output['committee_phones'] = $util->getConfiguration('committee.phones');

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('contact-form', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();
            array_walk($post_data, function(&$item) {
                $item = trim($item);
            });

            // checking required files
            foreach(array('name', 'email', 'subject', 'message') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $locale = $request->getSession()->get('_locale');
            $help = $help_repository->find(116);
            $translations = $trans_repository->findTranslations($help);
            $text = $translations[$locale];
            $body = $text['message'];
            $body = str_replace("%username%", $post_data['name'], $body);
            $body = str_replace("%email%", $post_data['email'], $body);
            $body = str_replace("%subject%", $post_data['subject'], $body);
            $body = str_replace("%message%", nl2br($post_data['message']), $body);
            $body = str_replace("\r\n", "<br />", $body);
            $body .= "<br /><br />";

            $message = \Swift_Message::newInstance()
            ->setSubject("[LILACS] " . $translator->trans("Message from plataform"))
            ->setFrom($post_data['email'])
            ->setTo($output['committee_email'])
            ->setBody(
                $body
                ,
                'text/html'
            );

            $send = $this->get('mailer')->send($message);
            $session->getFlashBag()->add('success', $translator->trans("Message sent to administrators."));
            return $this->redirectToRoute($route, array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/help", name="crud_admin_help_list")
     * @Template()
     */
    public function listHelpAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        $helps = $help_repository->findBy(array("status" => true, "type" => "help"));

        $id = $request->query->get('id');
        if($id) {
            $helps = $help_repository->findBy(array("id" => $id));
        }

        $output['helps'] = $helps;
        return $output;
    }

    /**
     * @Route("/admin/help/{help_id}/update", name="crud_admin_help_update")
     * @Template()
     */
    public function updateHelpAction($help_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        // getting the current help
        $help = $help_repository->find($help_id);
        $output['help'] = $help;


        if (!$help) {
            throw $this->createNotFoundException($translator->trans('No help found'));
        }

        $translations = $trans_repository->findTranslations($help);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('help-message-en') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $help->setTranslatableLocale('en');
            $help->setMessage($post_data['help-message-en']);
            $help->setStatus(true);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["help-message-$locale"])) {
                    $trans_repository = $trans_repository->translate($help, 'message', $locale, $post_data["help-message-$locale"]);
                }
            }

            $em->persist($help);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Help updated with success."));
            return $this->redirectToRoute('crud_admin_help_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/help/{help_id}", name="crud_admin_help_show")
     * @Template()
     */
    public function showHelpAction($help_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        // getting the current help
        $help = $help_repository->find($help_id);
        $output['help'] = $help;

        if (!$help) {
            throw $this->createNotFoundException($translator->trans('No help found'));
        }

        $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $repository->findTranslations($help);
        $output['translations'] = $translations;

        return $output;
    }

    /**
     * @Route("/admin/help/{help_id}/check", name="crud_admin_help_check")
     */
    public function checkHelpAction($help_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        // getting the current help
        $help = $help_repository->find($help_id);

        if (!$help) {
            throw $this->createNotFoundException($translator->trans('No help found'));
        }

        $output['status'] = true;
        if(!$help->getMessage()) {
            $output['status'] = false;
        }
        $response = new Response();
        $response->setContent(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/admin/mail", name="crud_admin_mail_list")
     * @Template()
     */
    public function listMailAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        $mails = $help_repository->findBy(array("status" => true, "type" => "mail"));

        $id = $request->query->get('id');
        if($id) {
            $mails = $help_repository->findBy(array("id" => $id));
        }

        $output['mails'] = $mails;
        return $output;
    }

    /**
     * @Route("/admin/mail/{mail_id}/update", name="crud_admin_mail_update")
     * @Template()
     */
    public function updateMailAction($mail_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        // getting the current mail
        $mail = $help_repository->find($mail_id);
        $output['mail'] = $mail;

        if (!$mail) {
            throw $this->createNotFoundException($translator->trans('No mail found'));
        }

        $translations = $trans_repository->findTranslations($mail);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('mail-message-en') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $mail->setTranslatableLocale('en');
            $mail->setMessage($post_data['mail-message-en']);
            $mail->setStatus(true);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["mail-message-$locale"])) {
                    $trans_repository = $trans_repository->translate($mail, 'message', $locale, $post_data["mail-message-$locale"]);
                }
            }

            $em->persist($mail);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Help updated with success."));
            return $this->redirectToRoute('crud_admin_mail_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/mail/{mail_id}", name="crud_admin_mail_show")
     * @Template()
     */
    public function showMailAction($mail_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        // getting the current mail
        $mail = $help_repository->find($mail_id);
        $output['mail'] = $mail;

        if (!$mail) {
            throw $this->createNotFoundException($translator->trans('No mail found'));
        }

        $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $repository->findTranslations($mail);
        $output['translations'] = $translations;

        return $output;
    }

    /**
     * @Route("/admin/mail/{mail_id}/check", name="crud_admin_mail_check")
     */
    public function checkMailAction($mail_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');

        // getting the current mail
        $mail = $help_repository->find($mail_id);

        if (!$mail) {
            throw $this->createNotFoundException($translator->trans('No mail found'));
        }

        $output['status'] = true;
        if(!$mail->getMessage()) {
            $output['status'] = false;
        }
        $response = new Response();
        $response->setContent(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/admin/configuration", name="crud_admin_configuration_list")
     * @Template()
     */
    public function listConfigurationAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $currencies = Intl::getCurrencyBundle()->getCurrencyNames();
        $output['currencies'] = $currencies;

        $country_repository = $em->getRepository('Proethos2ModelBundle:Country');
        $countries = $country_repository->findBy(array(), array('name' => 'asc'));
        $output['countries'] = $countries;

        $configuration_repository = $em->getRepository('Proethos2ModelBundle:Configuration');
        $configurations = $configuration_repository->findAll();
        $output['configurations'] = $configurations;

        $country_locale = $configuration_repository->findOneBy(array('key' => 'country.locale'));
        $country_code   = explode('|', $country_locale->getValue())[0];
        $currency_code  = explode('|', $country_locale->getValue())[1];
        $output['country_code']  = $country_code;
        $output['currency_code'] = $currency_code;

        $country  = $country_repository->findBy(array('code' => $country_code));
        $currency_name = $currencies[$currency_code];
        $output['country_name']  = $country[0]->getName();
        $output['currency_name'] = $currency_name;

        $submission_period = $configuration_repository->findOneBy(array('key' => 'submission.period'));
        $submission_period_start_date = '';
        $submission_period_end_date = '';

        if ( $submission_period ) {
            $submission_period_start_date = explode('|', $submission_period->getValue())[0];
            $submission_period_end_date = explode('|', $submission_period->getValue())[1];
        }

        $output['submission_period_start_date'] = $submission_period_start_date;
        $output['submission_period_end_date'] = $submission_period_end_date;

        return $output;
    }

    /**
     * @Route("/admin/configuration/{configuration_id}/update", name="crud_admin_configuration_update")
     * @Template()
     */
    public function updateConfigurationAction($configuration_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $configuration_repository = $em->getRepository('Proethos2ModelBundle:Configuration');

        // getting the current configuration
        $configuration = $configuration_repository->find($configuration_id);

        if (!$configuration) {
            throw $this->createNotFoundException($translator->trans('No configuration found'));
        }
        $output['configuration'] = $configuration;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            $configuration->setValue($post_data['configuration-value']);

            $em->persist($configuration);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Configuration updated with success."));
            return $this->redirectToRoute('crud_admin_configuration_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/upload-type-extension", name="crud_admin_controlled_list_upload_type_extension_list")
     * @Template()
     */
    public function listControlledListUploadTypeExtensionAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:UploadTypeExtension');

        $items = $item_repository->findAll();
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('extension') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new UploadTypeExtension();
            $item->setExtension($post_data['extension']);

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Upload Extension Type created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_upload_type_extension_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/upload-type-extension/{item_id}", name="crud_admin_controlled_list_upload_type_extension_update")
     * @Template()
     */
    public function updateControlledListUploadTypeExtensionAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:UploadTypeExtension');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No extension found'));
        }
        $output['item'] = $item;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('extension') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setExtension($post_data['extension']);
            if(isset($post_data['status']) and $post_data['status'] == "true") {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Upload Extension Type created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_upload_type_extension_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/upload-type", name="crud_admin_controlled_list_upload_type_list")
     * @Template()
     */
    public function listControlledListUploadTypeAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $extensions_repository = $em->getRepository('Proethos2ModelBundle:UploadTypeExtension');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $items = $item_repository->findAll();
        $output['items'] = $items;

        $extensions = $extensions_repository->findByStatus(true);
        $output['extensions'] = $extensions;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // var_dump($post_data);die;

            // checking required files
            foreach(array('name', 'extensions') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new UploadType();
            $item->setTranslatableLocale('en');
            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if(isset($post_data['extensions'])) {
                foreach($post_data['extensions'] as $extension) {
                    $extension = $extensions_repository->find($extension);
                    $item->addExtension($extension);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Upload Extension Type created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_upload_type_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/upload-type/{item_id}", name="crud_admin_controlled_list_upload_type_update")
     * @Template()
     */
    public function updateControlledListUploadTypeAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $extensions_repository = $em->getRepository('Proethos2ModelBundle:UploadTypeExtension');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No type found'));
        }
        $output['item'] = $item;

        $extensions = $extensions_repository->findByStatus(true);
        $output['extensions'] = $extensions;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name', 'extensions') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            foreach($item->getExtensions() as $extension) {
                $item->removeExtension($extension);
            }

            $item->setTranslatableLocale('en');
            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if(isset($post_data['extensions'])) {
                foreach($post_data['extensions'] as $extension) {
                    $extension = $extensions_repository->find($extension);
                    $item->addExtension($extension);
                }
            }

            if(isset($post_data['status']) and $post_data['status'] == "true") {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_upload_type_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/recruitment-status", name="crud_admin_controlled_list_recruitment_status_list")
     * @Template()
     */
    public function listControlledListRecruitmentStatusAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:RecruitmentStatus');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $items = $item_repository->findAll();
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new RecruitmentStatus();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Status created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_recruitment_status_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/recruitment-status/{item_id}", name="crud_admin_controlled_list_recruitment_status_update")
     * @Template()
     */
    public function updateControlledListRecruitmentStatusAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:RecruitmentStatus');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if(isset($post_data['status']) and $post_data['status'] == "true") {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Status created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_recruitment_status_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/monitoring-action", name="crud_admin_controlled_list_monitoring_action_list")
     * @Template()
     */
    public function listControlledListMonitoringActionAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:MonitoringAction');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $items = $item_repository->findAll();
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new MonitoringAction();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_monitoring_action_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/monitoring-action/{item_id}", name="crud_admin_controlled_list_monitoring_action_update")
     * @Template()
     */
    public function updateControlledListMonitoringActionAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:MonitoringAction');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if(isset($post_data['status']) and $post_data['status'] == "true") {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_monitoring_action_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/clinical-trial-name", name="crud_admin_controlled_list_clinical_trial_name_list")
     * @Template()
     */
    public function listControlledListClinicalTrialNameAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:ClinicalTrialName');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $items = $item_repository->findAll();
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new ClinicalTrialName();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['code']);
            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_clinical_trial_name_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/clinical-trial-name/{item_id}", name="crud_admin_controlled_list_clinical_trial_name_update")
     * @Template()
     */
    public function updateControlledListClinicalTrialNameAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:ClinicalTrialName');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['code']);
            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if(isset($post_data['status']) and $post_data['status'] == "true") {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_clinical_trial_name_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/gender", name="crud_admin_controlled_list_gender_list")
     * @Template()
     */
    public function listControlledListGenderAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:Gender');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $items = $item_repository->findAll();
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new Gender();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_gender_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/gender/{item_id}", name="crud_admin_controlled_list_gender_update")
     * @Template()
     */
    public function updateControlledListGenderAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:Gender');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if(isset($post_data['status']) and $post_data['status'] == "true") {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_gender_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/specialty", name="crud_admin_controlled_list_specialty_list")
     * @Template()
     */
    public function listControlledListSpecialtyAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        // getting thematic area list
        $thematic_area_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        $thematic_area = $thematic_area_repository->findByStatus(true);
        $output['thematic_area'] = $thematic_area;

        $item_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        // $items = $item_repository->findAll();
        $items = $item_repository->findBy(array(), array('name' => 'ASC'));
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new Specialty();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            // thematic area
            $selected_thematic_area = $thematic_area_repository->find($post_data['thematic_area']);
            $item->setThematicArea($selected_thematic_area);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_specialty_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/specialty/{item_id}", name="crud_admin_controlled_list_specialty_update")
     * @Template()
     */
    public function updateControlledListSpecialtyAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        // getting thematic area list
        $thematic_area_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        $thematic_area = $thematic_area_repository->findByStatus(true);
        $output['thematic_area'] = $thematic_area;

        $item_repository = $em->getRepository('Proethos2ModelBundle:Specialty');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            // thematic area
            $selected_thematic_area = $thematic_area_repository->find($post_data['thematic_area']);
            $item->setThematicArea($selected_thematic_area);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if ( isset($post_data['status']) and $post_data['status'] == "true" ) {
                $item->setStatus(true);
            } else {
                $item->setStatus(false);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_specialty_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/publication-type", name="crud_admin_controlled_list_publication_type_list")
     * @Template()
     */
    public function listControlledListPublicationTypeAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:PublicationType');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        // $items = $item_repository->findAll();
        $items = $item_repository->findBy(array(), array('name' => 'ASC'));
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new PublicationType();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_publication_type_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/publication-type/{item_id}", name="crud_admin_controlled_list_publication_type_update")
     * @Template()
     */
    public function updateControlledListPublicationTypeAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:PublicationType');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if ( isset($post_data['status']) and $post_data['status'] == "true" ) {
                $item->setStatus(true);
            } else {
                $item->setStatus(false);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_publication_type_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/language", name="crud_admin_controlled_list_language_list")
     * @Template()
     */
    public function listControlledListLanguageAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:Language');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        // $items = $item_repository->findAll();
        $items = $item_repository->findBy(array(), array('name' => 'ASC'));
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name', 'code') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new Language();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);
            $item->setCode($post_data['code']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_language_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/language/{item_id}", name="crud_admin_controlled_list_language_update")
     * @Template()
     */
    public function updateControlledListLanguageAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:Language');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name', 'code') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);
            $item->setCode($post_data['code']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if ( isset($post_data['status']) and $post_data['status'] == "true" ) {
                $item->setStatus(true);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_language_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/thematic-area", name="crud_admin_controlled_list_thematic_area_list")
     * @Template()
     */
    public function listControlledListThematicAreaAction()
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        // $items = $item_repository->findAll();
        $items = $item_repository->findBy(array(), array('name' => 'ASC'));
        $output['items'] = $items;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {

                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item = new ThematicArea();
            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item created with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_thematic_area_list', array(), 301);
        }

        return $output;
    }

    /**
     * @Route("/admin/controlled-list/thematic-area/{item_id}", name="crud_admin_controlled_list_thematic_area_update")
     * @Template()
     */
    public function updateControlledListThematicAreaAction($item_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $item_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $item = $item_repository->find($item_id);

        if (!$item) {
            throw $this->createNotFoundException($translator->trans('No item found'));
        }
        $output['item'] = $item;

        $translations = $trans_repository->findTranslations($item);
        $output['translations'] = $translations;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('name') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $item->setTranslatableLocale('en');

            $item->setName($post_data['name']);

            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $locale) {
                if(!empty($post_data["name-$locale"])) {
                    $trans_repository = $trans_repository->translate($item, 'name', $locale, $post_data["name-$locale"]);
                }
            }

            if ( isset($post_data['status']) and $post_data['status'] == "true" ) {
                $item->setStatus(true);
            } else {
                $item->setStatus(false);
            }

            $em->persist($item);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Item updated with success."));
            return $this->redirectToRoute('crud_admin_controlled_list_thematic_area_list', array(), 301);
        }

        return $output;
    }
}

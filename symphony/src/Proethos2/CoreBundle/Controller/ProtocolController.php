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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;

use Proethos2\CoreBundle\Util\Util;

use Proethos2\ModelBundle\Entity\ProtocolComment;
use Proethos2\ModelBundle\Entity\ProtocolHistory;
use Proethos2\ModelBundle\Entity\ProtocolCommitteeRevision;
use Proethos2\ModelBundle\Entity\ProtocolAdhocRevision;
use Proethos2\ModelBundle\Entity\ProtocolAdhocRevisionEvaluation;
use Proethos2\ModelBundle\Entity\Submission;
use Proethos2\ModelBundle\Entity\SubmissionUpload;
use Proethos2\ModelBundle\Entity\Issue;

class ProtocolController extends Controller
{
    /**
     * @Route("/journal/{protocol_id}", name="protocol_show_protocol")
     * @Template()
     */
    public function showProtocolAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        // checking required deployment report attachment
        $upload_type = $upload_type_repository->findOneBy(array("slug" => "attachment"));
        $upload_type_id = $upload_type->getId();
        $submission_upload = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
        $output['submission_upload'] = $submission_upload;

        $is_owner = ( $user == $protocol->getOwner() ) ? true : false;
        $output['is_owner'] = $is_owner;

        if (!$protocol) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            if ( isset($post_data['extra-file']) and $post_data['extra-file'] == "yes" ) {
                $file = $request->files->get('extra-attachment');
                if ( empty($file) ) {
                    $session->getFlashBag()->add('error', $translator->trans("The attachment is required."));
                    return $output;
                }

                // remove the old file uploaded
                if ( $submission_upload ) {
                    $em->remove($submission_upload[0]);
                    $em->flush();
                }

                $submission_upload = new SubmissionUpload();
                $submission_upload->setSubmission($protocol->getMainSubmission());
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $protocol->getMainSubmission()->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($protocol->getMainSubmission());
                $em->flush();
/*
                $protocol_history = new ProtocolHistory();
                $protocol_history->setProtocol($protocol);
                $protocol_history->setMessage($translator->trans(
                    'The journal attachment were sent by the publisher.',
                    array(
                        '%user%' => $user->getUsername(),
                    )
                ));
                $em->persist($protocol_history);
                $em->flush();
*/
                $session->getFlashBag()->add('success', $translator->trans("Attachment added with success."));
                return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);
            }

            // if has new comment
            if(isset($post_data['new-comment-message'])) {

                $user = $this->get('security.token_storage')->getToken()->getUser();

                $comment = new ProtocolComment();
                $comment->setProtocol($protocol);
                $comment->setOwner($user);
                $comment->setMessage($post_data['new-comment-message']);
                $comment->setRole($post_data['new-comment-role']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Comment was created with success."));
/*
                foreach($user_repository->findAll() as $member) {
                    foreach(array("members-of-committee") as $role) {
                        if(in_array($role, $member->getRolesSlug())) {
                            $message = \Swift_Message::newInstance()
                            ->setSubject("[LILACS] " . $translator->trans("A new journal needs your analysis."))
                            ->setFrom($util->getConfiguration('committee.email'))
                            ->setTo($member->getEmail())
                            ->setBody(
                                $body
                                ,
                                'text/html'
                            );

                            $send = $this->get('mailer')->send($message);
                        }
                    }
                }
*/
            }
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/comment", name="protocol_new_comment")
     */
    public function newCommentProtocolAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        if (!$protocol) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        $referer = $request->headers->get('referer');

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $comment = new ProtocolComment();
            $comment->setProtocol($protocol);
            $comment->setOwner($user);
            $comment->setMessage($post_data['new-comment-message']);
            $comment->setRole($post_data['new-comment-role']);

            if(isset($post_data['new-comment-is-confidential']) and $post_data['new-comment-is-confidential'] == "yes") {
                $comment->setIsConfidential(true);
            }

            $em->persist($comment);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Comment was created with success."));
/*
            foreach($user_repository->findAll() as $member) {
                foreach(array("members-of-committee") as $role) {
                    if(in_array($role, $member->getRolesSlug())) {
                        $message = \Swift_Message::newInstance()
                        ->setSubject("[LILACS] " . $translator->trans("A new journal needs your analysis."))
                        ->setFrom($util->getConfiguration('committee.email'))
                        ->setTo($member->getEmail())
                        ->setBody(
                            $body
                            ,
                            'text/html'
                        );

                        $send = $this->get('mailer')->send($message);
                    }
                }
            }
*/
        }

        return $this->redirect($referer, 301);
    }

    /**
     * @Route("/protocol/{protocol_id}/attachment", name="protocol_new_attachment")
     * @Template()
     */
    public function newAttachmentProtocolAction($protocol_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $upload_types = $upload_type_repository->findByStatus(true);
        $output['upload_types'] = $upload_types;

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$protocol) {
            throw $this->createNotFoundException($translator->trans('No protocol found'));
        }

        $referer = $request->headers->get('referer');

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            $file = $request->files->get('new-attachment-file');
            if(!empty($file)) {

                $upload_type = $upload_type_repository->findOneBy(array('slug' => 'others'));
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

            }

            if(isset($post_data['delete-attachment-id']) and !empty($post_data['delete-attachment-id'])) {

                $submission_upload = $submission_upload_repository->find($post_data['delete-attachment-id']);
                if($submission_upload) {

                    $em->remove($submission_upload);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("File removed with sucess."));
                }

            }

            return $this->redirect($referer, 301);

        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/analyze", name="protocol_analyze_protocol")
     * @Template()
     */
    public function analyzeProtocolAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $util = new Util($this->container, $this->getDoctrine());

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        $upload_types = $upload_type_repository->findByStatus(true);
        $output['upload_types'] = $upload_types;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$protocol or $protocol->getStatus() != "S") {
            throw $this->createNotFoundException($translator->trans('No journal found'));
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
                $submission_upload->setSubmission($protocol->getMainSubmission());
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $protocol->getMainSubmission()->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($protocol->getMainSubmission());
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("File uploaded with success."));
                return $this->redirectToRoute('protocol_analyze_protocol', array('protocol_id' => $protocol->getId()), 301);

            }

            if(isset($post_data['delete-attachment-id']) and !empty($post_data['delete-attachment-id'])) {
                $submission_upload = $submission_upload_repository->find($post_data['delete-attachment-id']);
                if($submission_upload) {
                    $em->remove($submission_upload);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("File removed with success."));
                    return $this->redirectToRoute('protocol_analyze_protocol', array('protocol_id' => $protocol->getId()), 301);
                }
            }

            if(isset($post_data['is-reject']) and $post_data['is-reject'] == "true") {

                // sending email
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $home_url = $baseurl . $this->generateUrl('home');
                $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                $_locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(109);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$_locale];
                $body = $text['message'];
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("%journal_url%", $url, $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";

                $recipients = array($protocol->getOwner());
                foreach($protocol->getMainSubmission()->getTeam() as $team_member) {
                    $recipients[] = $team_member;
                }

                foreach($recipients as $recipient) {
                    $body = str_replace("%username%", $recipient->getName(), $body);
                    $message = \Swift_Message::newInstance()
                    ->setSubject("[LILACS] " . $translator->trans("Your journal was rejected"))
                    ->setFrom($util->getConfiguration('committee.email'))
                    ->setTo($recipient->getEmail())
                    ->setBody(
                        $body
                        ,
                        'text/html'
                    );

                    $send = $this->get('mailer')->send($message);
                }

                if($protocol->getMonitoringAction()) {

                    $protocol_history = new ProtocolHistory();
                    $protocol_history->setProtocol($protocol);
                    $protocol_history->setMessage($translator->trans('Monitoring action was rejected by secretary with this justification "%justify%".',
                        array(
                            '%user%' => $user->getUsername(),
                            '%justify%' => $post_data['reject-reason'],
                        )
                    ));
                    $em->persist($protocol_history);
                    $em->flush();

                    $protocol->setStatus("A");
                    $protocol->setMonitoringAction(NULL);

                    $em->persist($protocol);
                    $em->flush();

                    $session->getFlashBag()->add('success', $translator->trans("Journal rejected with success!"));
                    return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);
                }

                // cloning submission
                $new_submission = clone $submission;
                $new_submission->setNumber($submission->getNumber() + 1);
                $em->persist($new_submission);

                // cloning translations
                foreach($submission->getTranslations() as $translation) {
                    $new_translation = clone $translation;
                    $new_translation->setOriginalSubmission($new_submission);
                    $new_translation->setNumber($translation->getNumber() + 1);
                    $em->persist($new_translation);

                    $new_submission->addTranslation($new_translation);
                    $em->persist($new_submission);
                }
                $em->flush();

                // setting new main submission
                $protocol->setMainSubmission($new_submission);

                // setting the Rejected status
                $protocol->setStatus("R");

                // setting protocol history
                $protocol_history = new ProtocolHistory();
                $protocol_history->setProtocol($protocol);
                // $protocol_history->setMessage($translator->trans("Journal was rejected by") ." ". $user . ".");
                $protocol_history->setMessage($translator->trans('Journal was rejected by secretary with this justification "%justify%".',
                    array(
                        '%user%' => $user->getUsername(),
                        '%justify%' => $post_data['reject-reason'],
                    )
                ));
                $em->persist($protocol_history);
                $em->flush();

                // setting the reason
                $protocol->setRejectReason($post_data['reject-reason']);

                $em->persist($protocol);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Journal rejected with success!"));
                return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);

            } else {

                // generate the code
                $committee_prefix = $util->getConfiguration('committee.prefix');
                $total_submissions = count($protocol->getSubmission());
                $protocol_code = sprintf('%s.%04d.%02d', $committee_prefix, $protocol->getId(), $total_submissions);
                $protocol->setCode($protocol_code);

                if($post_data['send-to'] == "committee") {

                    // setting the Waiting for Committee status
                    $protocol->setStatus("E");

                    // setting protocol history
                    $protocol_history = new ProtocolHistory();
                    $protocol_history->setProtocol($protocol);
                    $protocol_history->setMessage($translator->trans("Journal accepted for review by secretary and members notified.",
                        array(
                            "%user%" => $user->getUsername(),
                        )
                    ));
                    $em->persist($protocol_history);
                    $em->flush();

                    $em->persist($protocol);
                    $em->flush();

                    $investigators = array();
                    $investigators[] = $protocol->getMainSubmission()->getOwner();
                    foreach($protocol->getMainSubmission()->getTeam() as $investigator) {
                        $investigators[] = $investigator;
                    }
                    foreach($investigators as $investigator) {
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        $home_url = $baseurl . $this->generateUrl('home');
                        $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                        $_locale = $request->getSession()->get('_locale');
                        $help = $help_repository->find(110);
                        $translations = $trans_repository->findTranslations($help);
                        $text = $translations[$_locale];
                        $body = $text['message'];
                        $body = str_replace("%home_url%", $home_url, $body);
                        $body = str_replace("%username%", $investigator->getName(), $body);
                        $body = str_replace("%journal_url%", $url, $body);
                        $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                        $body = str_replace("\r\n", "<br />", $body);
                        $body .= "<br /><br />";

                        $message = \Swift_Message::newInstance()
                        ->setSubject("[LILACS] " . $translator->trans("Your journal was sent to review"))
                        ->setFrom($util->getConfiguration('committee.email'))
                        ->setTo($investigator->getEmail())
                        ->setBody(
                            $body
                            ,
                            'text/html'
                        );

                        $send = $this->get('mailer')->send($message);
                    }
/*
                    foreach($user_repository->findAll() as $member) {
                        foreach(array("members-of-committee") as $role) {
                            if(in_array($role, $member->getRolesSlug())) {
                                $message = \Swift_Message::newInstance()
                                ->setSubject("[LILACS] " . $translator->trans("A new journal needs your analysis."))
                                ->setFrom($util->getConfiguration('committee.email'))
                                ->setTo($member->getEmail())
                                ->setBody(
                                    $body
                                    ,
                                    'text/html'
                                );

                                $send = $this->get('mailer')->send($message);
                            }
                        }
                    }
*/
                    $session->getFlashBag()->add('success', $translator->trans("Journal updated with success!"));
                    return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);
                }

                if($post_data['send-to'] == "notification-only") {

                    $protocol->setStatus("A");
                    $protocol->setMonitoringAction(NULL);

                    // setting protocol history
                    $protocol_history = new ProtocolHistory();
                    $protocol_history->setProtocol($protocol);
                    $protocol_history->setMessage($translator->trans("Monitoring action was accepted by secretary as notification only.",
                        array(
                            "%user%" => $user->getUsername(),
                        )
                    ));
                    $em->persist($protocol_history);
                    $em->flush();

                    $em->persist($protocol);
                    $em->flush();

                    $session->getFlashBag()->add('success', $translator->trans("Journal updated with success!"));
                    return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);
                }

            }
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/initial-committee-screening", name="protocol_initial_committee_screening")
     * @Template()
     */
    public function initCommitteeScreeningAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $util = new Util($this->container, $this->getDoctrine());

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        $upload_types = $upload_type_repository->findByStatus(true);
        $output['upload_types'] = $upload_types;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$protocol or !in_array($protocol->getStatus(), array("I", "E", "H"))) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();
/*
            // setting the Committee Screening
            $protocol->setCommitteeScreening($post_data['committee-screening']);
            $em->persist($protocol);
            $em->flush();
*/
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
                $submission_upload->setSubmission($protocol->getMainSubmission());
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $protocol->getMainSubmission()->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($protocol->getMainSubmission());
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("File uploaded with success."));
                return $this->redirectToRoute('protocol_initial_committee_screening', array('protocol_id' => $protocol->getId()), 301);

            }

            if(isset($post_data['delete-attachment-id']) and !empty($post_data['delete-attachment-id'])) {
                $submission_upload = $submission_upload_repository->find($post_data['delete-attachment-id']);
                if($submission_upload) {
                    $em->remove($submission_upload);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("File removed with success."));
                    return $this->redirectToRoute('protocol_initial_committee_screening', array('protocol_id' => $protocol->getId()), 301);
                }
            }
/*
            // checking required format attachment
            $upload_type = $upload_type_repository->findBy(array('slug' => 'format'));
            $upload_type_id = $upload_type[0]->getId();
            $format = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
            if( !$format or count($format) != 1 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                return $output;
            }

            // checking required endogeny attachment
            $upload_type = $upload_type_repository->findBy(array('slug' => 'endogeny'));
            $upload_type_id = $upload_type[0]->getId();
            $endogeny = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
            if( !$endogeny or count($endogeny) != 1 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                return $output;
            }

            // checking required citation attachment
            $upload_type = $upload_type_repository->findBy(array('slug' => 'citation'));
            $upload_type_id = $upload_type[0]->getId();
            $citation = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
            if( !$citation or count($citation) != 1 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                return $output;
            }

            // checking required retrospective attachment
            $upload_type = $upload_type_repository->findBy(array('slug' => 'retrospective'));
            $upload_type_id = $upload_type[0]->getId();
            $retrospective = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
            if( !$retrospective or count($retrospective) != 1 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                return $output;
            }
*/
            // setting the Waiting for Committee status
            $protocol->setStatus("E");

            // setting protocol history
            $protocol_history = new ProtocolHistory();
            $protocol_history->setProtocol($protocol);
            $protocol_history->setMessage($translator->trans("Your journal has been accepted for evaluation. The committee's decision will be informed when the process is finalized."));
            $em->persist($protocol_history);
            $em->flush();

            $em->persist($protocol);
            $em->flush();
/*
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            $home_url = $baseurl . $this->generateUrl('home');
            $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

            if ( $post_data['committee-screening'] ) {
                $_locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(110);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$_locale];
                $body = $text['message'];
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("%journal_url%", $url, $body);
                $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                $body = str_replace("%committee_screening%", $post_data['committee-screening'], $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";
            } else {
                $_locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(110);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$_locale];
                $body = $text['message'];
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("%journal_url%", $url, $body);
                $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";
            }

            $investigators = array();
            $investigators[] = $protocol->getMainSubmission()->getOwner();
            foreach($protocol->getMainSubmission()->getTeam() as $investigator) {
                $investigators[] = $investigator;
            }
            foreach($investigators as $investigator) {
                $body = str_replace("%username%", $investigator->getName(), $body);
                $message = \Swift_Message::newInstance()
                ->setSubject("[LILACS] " . $translator->trans("Your journal was sent to review"))
                ->setFrom($util->getConfiguration('committee.email'))
                ->setTo($investigator->getEmail())
                ->setBody(
                    $body
                    ,
                    'text/html'
                );

                $send = $this->get('mailer')->send($message);
            }
*/
            $session->getFlashBag()->add('success', $translator->trans("Journal updated with success!"));
            return $this->redirectToRoute('protocol_initial_committee_screening', array('protocol_id' => $protocol->getId()), 301);

        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/initial-committee-review", name="protocol_initial_committee_review")
     * @Template()
     */
    public function initCommitteeReviewAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');
        $protocol_committee_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolCommitteeRevision');
        $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');
        $meeting_repository = $em->getRepository('Proethos2ModelBundle:Meeting');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        // gettings reviewers members
        $role_member_of_committee = $role_repository->findOneBy(array('slug' => 'member-of-committee'));
        $role_member_ad_hoc = $role_repository->findOneBy(array('slug' => 'member-ad-hoc'));

        $output['role_member_of_committee'] = $role_member_of_committee;
        $output['role_member_ad_hoc'] = $role_member_ad_hoc;

        // getting users
        $users = $user_repository->findBy(array(), array('average' => 'DESC', 'name' => 'ASC')); // $users = $user_repository->findAll();
        $output['users'] = $users;

        // gettings meetings
        $meetings = $meeting_repository->findAll();
        $output['meetings'] = $meetings;

        // getting total of revision with final revision from this protocol
        $committee_final_revisions       = $protocol_committee_revision_repository->findBy(array("protocol" => $protocol, "is_final_revision" => true));
        $total_committee_final_revisions = count($committee_final_revisions);
        $adhoc_final_revisions           = $protocol_adhoc_revision_repository->findBy(array("protocol" => $protocol, "is_final_revision" => true));
        $total_adhoc_final_revisions     = count($adhoc_final_revisions);
        $output['total_final_revisions'] = $total_committee_final_revisions + $total_adhoc_final_revisions;

        $upload_types = $upload_type_repository->findByStatus(true);
        $output['upload_types'] = $upload_types;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        // getting the reject reason options
        $reject_reason = array(
            'A' => $translator->trans('Fiz ou faço parte da equipe editorial'),
            'B' => $translator->trans('Conheço os editores'),
            'C' => $translator->trans('Estou aposentado(a)'),
            'D' => $translator->trans('Muitas atividades no momento'),
            'E' => $translator->trans('Outro motivo')
        );
        $output['reject_reason'] = $reject_reason;

        if (!$protocol or !in_array($protocol->getStatus(), array("I", "E", "H"))) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // echo "<pre>"; print_r($post_data); echo "</pre>"; die();

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
                $submission_upload->setSubmission($protocol->getMainSubmission());
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $protocol->getMainSubmission()->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($protocol->getMainSubmission());
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("File uploaded with success."));
                return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);

            }

            if(isset($post_data['delete-attachment-id']) and !empty($post_data['delete-attachment-id'])) {
                $submission_upload = $submission_upload_repository->find($post_data['delete-attachment-id']);
                if($submission_upload) {
                    $em->remove($submission_upload);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("File removed with success."));
                    return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);
                }
            }

            if(isset($post_data['send-to']) and $post_data['send-to'] == "button-save-and-wait-revisions") {

                // saving opinion required
                if(isset($post_data['opinion-required'])) {
                    $protocol->setOpinionRequired($post_data['opinion-required']);
                    $em->persist($protocol);
                    $em->flush();
                }

                // saving meeting
                if(isset($post_data['meeting'])) {
                    $meeting = $meeting_repository->find($post_data['meeting']);
                    $protocol->setMeeting($meeting);
                    $em->persist($protocol);
                    $em->flush();
                }

                $session->getFlashBag()->add('success', $translator->trans("Options have been saved with success!"));
                return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);
                
            }

            // check if form used is adding members
            if(isset($post_data['type-of-members'])) {

                foreach(array("select-members-of-committee", "select-members-ad-hoc") as $input_name) {
                    if(isset($post_data[$input_name])) {

                        if ( "select-members-of-committee" == $input_name ) {
                            $protocol_revision_repository = $protocol_committee_revision_repository;
                        } else {
                            $protocol_revision_repository = $protocol_adhoc_revision_repository;
                        }

                        foreach($post_data[$input_name] as $member) {
                            $member = $user_repository->findOneById($member);

                            $revision = $protocol_revision_repository->findOneBy(array('member' => $member, "protocol" => $protocol));
                            if(!$revision) {
                                if ( "select-members-of-committee" == $input_name ) {
                                    $revision = new ProtocolCommitteeRevision();
                                } else {
                                    $revision = new ProtocolAdhocRevision();
                                }
                                $revision->setMember($member);
                                $revision->setProtocol($protocol);
                                $em->persist($revision);
                                $em->flush();
                            }

                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            $home_url = $baseurl . $this->generateUrl('home');
                            $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                            if ( "select-members-of-committee" == $input_name ) {
                                $help = $help_repository->find(111);
                            } else {
                                $help = $help_repository->find(122);
                            }

                            $_locale = $request->getSession()->get('_locale');
                            $translations = $trans_repository->findTranslations($help);
                            $text = $translations[$_locale];
                            $body = $text['message'];
                            $body = str_replace("%home_url%", $home_url, $body);
                            $body = str_replace("%username%", $member->getName(), $body);
                            $body = str_replace("%journal_url%", $url, $body);
                            $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                            $body = str_replace("\r\n", "<br />", $body);
                            $body .= "<br /><br />";

                            $message = \Swift_Message::newInstance()
                            ->setSubject("[LILACS] " . $translator->trans("You were assigned to review a journal"))
                            ->setFrom($util->getConfiguration('committee.email'))
                            ->setTo($member->getEmail())
                            ->setBody(
                                $body
                                ,
                                'text/html'
                            );

                            $send = $this->get('mailer')->send($message);
                        }
                    }
                }

                $session->getFlashBag()->add('success', $translator->trans("Members added with success!"));
                return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);
            }

            // if form was to remove a member
            if(isset($post_data['remove-member']) and !empty($post_data['remove-member'])) {

                if ( "committee" == $post_data['member-type'] ) {
                    $protocol_revision_repository = $protocol_committee_revision_repository;
                } else {
                    $protocol_revision_repository = $protocol_adhoc_revision_repository;
                }

                $revision = $protocol_revision_repository->findOneById($post_data['remove-member']);
                if($revision) {
                    $em->remove($revision);
                    $em->flush();
                    
                    $reviewer = $user_repository->find($post_data['member-id']);
                    $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevisionEvaluation');
                    $protocol_adhoc_revision = $protocol_adhoc_revision_repository->findBy(array("protocol" => $protocol, "reviewer" => $reviewer));

                    if ( $protocol_adhoc_revision ) {
                        foreach ($protocol_adhoc_revision as $p) {
                            $em->remove($p);
                            $em->flush();
                        }
                    }

                    $session->getFlashBag()->add('success', $translator->trans("Member has been removed with success!"));
                    return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);
                }

            }

            // if form was to remove multiple members
            if(isset($post_data['bulk-remove-members']) and !empty($post_data['bulk-remove-members'])) {

                if ( "committee" == $post_data['member-type'] ) {
                    $protocol_revision_repository = $protocol_committee_revision_repository;
                } else {
                    $protocol_revision_repository = $protocol_adhoc_revision_repository;
                }

                $members = explode(',', $post_data['bulk-remove-members']);
                $revision = $protocol_revision_repository->findById($members);
                if($revision) {
                    foreach ($revision as $r) {
                        $em->remove($r);
                        $em->flush();

                        $member_ids = explode(',', $post_data['member-ids']);
                        if ( $member_ids ) {
                            foreach ($member_ids as $member_id) {
                                $reviewer = $user_repository->find($member_id);
                                $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevisionEvaluation');
                                $protocol_adhoc_revision = $protocol_adhoc_revision_repository->findBy(array("protocol" => $protocol, "reviewer" => $reviewer));

                                if ( $protocol_adhoc_revision ) {
                                    foreach ($protocol_adhoc_revision as $p) {
                                        $em->remove($p);
                                        $em->flush();
                                    }
                                }
                            }
                        }
                    }

                    $session->getFlashBag()->add('success', $translator->trans("Members has been removed with success!"));
                    return $this->redirectToRoute('protocol_initial_committee_review', array('protocol_id' => $protocol->getId()), 301);
                }

            }

            if(isset($post_data['send-to']) and $post_data['send-to'] == "button-save-and-send-to-meeting") {

                // checking required fields
                if(!isset($post_data['meeting']) or empty($post_data['meeting'])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field 'meeting' is required."));
                    return $output;
                }

                // checking required format attachment
                $upload_type = $upload_type_repository->findBy(array('slug' => 'format'));
                $upload_type_id = $upload_type[0]->getId();
                $format = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
                if( !$format or count($format) != 1 ) {
                    $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                    return $output;
                }

                // checking required endogeny attachment
                $upload_type = $upload_type_repository->findBy(array('slug' => 'endogeny'));
                $upload_type_id = $upload_type[0]->getId();
                $endogeny = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
                if( !$endogeny or count($endogeny) != 1 ) {
                    $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                    return $output;
                }
/*
                // checking required citation attachment
                $upload_type = $upload_type_repository->findBy(array('slug' => 'citation'));
                $upload_type_id = $upload_type[0]->getId();
                $citation = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
                if( !$citation or count($citation) != 1 ) {
                    $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                    return $output;
                }

                // checking required retrospective attachment
                $upload_type = $upload_type_repository->findBy(array('slug' => 'retrospective'));
                $upload_type_id = $upload_type[0]->getId();
                $retrospective = $submission_upload_repository->findBy(array('submission' => $protocol->getMainSubmission()->getId(), 'upload_type' => $upload_type_id));
                if( !$retrospective or count($retrospective) != 1 ) {
                    $session->getFlashBag()->add('error', $translator->trans("Please submit all required files."));
                    return $output;
                }
*/
                $meeting = $meeting_repository->find($post_data['meeting']);
                $protocol->setMeeting($meeting);

                // setting the Scheduled status
                $protocol->setStatus("H");
                $protocol->setRevisedIn(new \DateTime());

                $em->persist($protocol);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Meeting assigned with success!"));
                return $this->redirectToRoute('protocol_end_review', array('protocol_id' => $protocol->getId()), 301);
            }

            // if form was to remove a member
            if ( isset($post_data['protocol-review-invitation']) and !empty($post_data['protocol-review-invitation']) ) {
                if ( "committee" == $post_data['member-type'] ) {
                    $protocol_revision_repository = $protocol_committee_revision_repository;
                } else {
                    $protocol_revision_repository = $protocol_adhoc_revision_repository;
                }

                $revision = $protocol_revision_repository->findOneBy(array('member' => $user, "protocol" => $protocol));
                if($revision) {
                    if ( 'yes' == $post_data['protocol-review-invitation'] ) {
                        $subject = "Review invitation has been accepted";
                        $help = $help_repository->find(127);

                        $revision->setAccepted(true);
                        $em->persist($revision);
                        $em->flush();

                        $session->getFlashBag()->add('success', $translator->trans("Review has been accepted with success!"));
                    }

                    if ( 'no' == $post_data['protocol-review-invitation'] ) {
                        $subject = "Review invitation has been rejected";
                        $help = $help_repository->find(128);

                        $revision->setRejected(true);
                        $revision->setRejectReason($post_data['reject-reason']);
                        
                        if ( 'E' == $post_data['reject-reason'] and $post_data['other-reject-reason'] ) {
                            $revision->setOtherRejectReason($post_data['other-reject-reason']);
                        }

                        $em->persist($revision);
                        $em->flush();

                        $session->getFlashBag()->add('success', $translator->trans("Review has been rejected with success!"));
                    }
                    
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    $home_url = $baseurl . $this->generateUrl('home');
                    $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                    $_locale = $request->getSession()->get('_locale');
                    $translations = $trans_repository->findTranslations($help);
                    $text = $translations[$_locale];
                    $body = $text['message'];
                    $body = str_replace("%home_url%", $home_url, $body);
                    $body = str_replace("%journal_url%", $url, $body);
                    $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                    $body = str_replace("\r\n", "<br />", $body);
                    $body .= "<br /><br />";

                    $secretaries_emails = array();
                    foreach($user_repository->findAll() as $secretary) {
                        if(in_array("secretary", $secretary->getRolesSlug())) {
                            $secretaries_emails[] = $secretary->getEmail();
                        }
                    }

                    $message = \Swift_Message::newInstance()
                    ->setSubject("[LILACS] " . $translator->trans($subject))
                    ->setFrom($util->getConfiguration('committee.email'))
                    ->setTo($secretaries_emails)
                    ->setBody(
                        $body
                        ,
                        'text/html'
                    );
                    
                    return $this->redirectToRoute('crud_committee_protocol_list', array('protocol_id' => $protocol->getId()), 301);
                }
            }
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/initial-committee-review/revisor", name="protocol_initial_committee_review_revisor")
     * @Template()
     */
    public function initCommitteeReviewRevisorAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $role_repository = $em->getRepository('Proethos2ModelBundle:Role');

        $member = $request->query->get('member');
        if ( "committee" == $member ) {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolCommitteeRevision');
        } elseif ( "adhoc" == $member ) {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');
        } else {
            throw $this->createNotFoundException($translator->trans('You cannot edit this protocol'));
        }

        // getting the accept journal options
        $accept_journal = array(
            1 => $translator->trans('YES'),
            2 => $translator->trans('YES (with restrictions)'),
            3 => $translator->trans('NO')
        );
        $output['accept_journal'] = $accept_journal;

        // getting the journal concept options
        $journal_concept = array(
            1 => $translator->trans('Priority'),
            2 => $translator->trans('Important'),
            3 => $translator->trans('Relative importance'),
            4 => $translator->trans('Not relevant')
        );
        $output['journal_concept'] = $journal_concept;

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        if (!$protocol or !in_array($protocol->getStatus(), array("I", "E", "H"))) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // getting the protocol_revision
        $protocol_revision = $protocol_revision_repository->findOneBy(array("protocol" => $protocol, "member" => $user));
        $output['protocol_revision'] = $protocol_revision;

        if (!$protocol_revision) {
            throw $this->createNotFoundException($translator->trans('You cannot edit this protocol'));
        }

        $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevisionEvaluation');
        $protocol_adhoc_revision = $protocol_adhoc_revision_repository->findBy(array("protocol" => $protocol, "reviewer" => $user));
        if ( $protocol_adhoc_revision) {
            $output['protocol_adhoc_revision'] = $protocol_adhoc_revision;
            $action = 'update';
        } else {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');
            $protocol_adhoc_revision = $protocol_revision_repository->findBy(array("protocol" => $protocol, "answered" => true));
            $output['protocol_adhoc_revision'] = $protocol_adhoc_revision;
            $action = 'create';
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // echo "<pre>"; print_r($post_data); echo "</pre>"; die();

            // only change if is not final revision
            if(!$protocol_revision->getIsFinalRevision()) {
            	$accept_conditions = ( 'on' == $post_data['accept-conditions'] ) ? true : false;

                if ( "committee" == $post_data['member-type'] ) {
                    $protocol_revision->setPositiveAspects($post_data['positive-aspects']);
                    $protocol_revision->setNegativeAspects($post_data['negative-aspects']);
                    $protocol_revision->setOtherComments($post_data['other-comments']);
                    $protocol_revision->setAcceptJournal($post_data['accept-journal']);
                    $protocol_revision->setAcceptConditions($accept_conditions);
                    $protocol_revision->setUpdated(new \DateTime());

                    foreach ($post_data['revision'] as $member_id => $revision) {
                        $member = $user_repository->find($member_id);

                        if ( 'create' == $action ) {
                            $adhoc_revision = new ProtocolAdhocRevisionEvaluation();
                            $adhoc_revision->setMember($member);
                            $adhoc_revision->setReviewer($user);
                            $adhoc_revision->setProtocol($protocol);
                        } else {
                            $adhoc_revision = $protocol_adhoc_revision_repository->findOneBy(array("protocol" => $protocol, "member" => $member, "reviewer" => $user));
                        }

                        $adhoc_revision->setRelevance($revision['relevance']);
                        $adhoc_revision->setPertinence($revision['pertinence']);
                        $adhoc_revision->setClarity($revision['clarity']);

                        $total_rating = $revision['relevance'] + $revision['pertinence'] + $revision['clarity'];

                        if ( $total_rating == 0 ) {
                            $adhoc_revision->setIsValid(false);
                        } else {
                            $adhoc_revision->setIsValid(true);
                        }

                        $em->persist($adhoc_revision);
                        $em->flush();

                        $avg = $protocol_adhoc_revision_repository->createQueryBuilder('e')
                            ->select('SUM(e.relevance+e.pertinence+e.clarity)/3/COUNT(e.id) as average')
                            ->where('e.member = :member and e.is_valid=1')
                            ->setParameter('member', $member)
                            ->getQuery()
                            ->getResult();
                        
                        $member->setAverage($avg[0]['average']);

                        $em->persist($member);
                        $em->flush();
                    }
                } elseif ( "adhoc" == $post_data['member-type'] ) {
                    $protocol_revision->setEditorialTeam($post_data['editorial-team']);
                    $protocol_revision->setContentA($post_data['content-a']);
                    $protocol_revision->setContentB($post_data['content-b']);
                    $protocol_revision->setContentC($post_data['content-c']);
                    $protocol_revision->setContentD($post_data['content-d']);
                    $protocol_revision->setContentE($post_data['content-e']);
                    $protocol_revision->setContentF($post_data['content-f']);
                    $protocol_revision->setContentG($post_data['content-g']);
                    $protocol_revision->setContentH($post_data['content-h']);
                    $protocol_revision->setContentI($post_data['content-i']);
                    $protocol_revision->setContentJ($post_data['content-j']);
                    // $protocol_revision->setContentK($post_data['content-k']);
                    $protocol_revision->setContentL($post_data['content-l']);
                    $protocol_revision->setContentM($post_data['content-m']);
                    $protocol_revision->setPeerArbitrationA($post_data['peer-arbitration-a']);
                    $protocol_revision->setPeerArbitrationB($post_data['peer-arbitration-b']);
                    $protocol_revision->setPeerArbitrationC($post_data['peer-arbitration-c']);
                    $protocol_revision->setJournalConcept($post_data['journal-concept']);
                    // $protocol_revision->setJournalDensity($post_data['journal-density']);
                    $protocol_revision->setJournalRelevance($post_data['journal-relevance']);
                    $protocol_revision->setOtherComments($post_data['other-comments']);
                    $protocol_revision->setOtherJournals($post_data['other-journals']);
                    $protocol_revision->setAcceptConditions($accept_conditions);
                    $protocol_revision->setUpdated(new \DateTime());
                } else {
                    throw $this->createNotFoundException($translator->trans('You cannot edit this protocol'));
                }

                if($post_data['is-final-revision'] == "true") {
                    // checking required files
                    foreach($post_data as $key => $value) {
                        if(!isset($value) or empty($value)) {
                            $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $key)));
                            return $output;
                        }
                    }

                    if ( $post_data['accept-conditions'] == 'off' ) {
                        $session->getFlashBag()->add('error', $translator->trans("You must accept the conditions for sending submission."));
                        return $output;
                    }
                    
                    $protocol_revision->setIsFinalRevision(true);
                    $protocol_revision->setRevisedIn(new \DateTime());
                }

                $protocol_revision->setAnswered(true);

                $em->persist($protocol_revision);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Fields answered with success!"));
                return $this->redirectToRoute('protocol_initial_committee_review_revisor', array('protocol_id' => $protocol->getId(), 'member' => $post_data['member-type']), 301);
            }

        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/initial-committee-review/show-review/{protocol_revision_id}", name="protocol_initial_committee_review_show_review")
     * @Template()
     */
    public function showReviewAction($protocol_id, $protocol_revision_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the accept journal options
        $accept_journal = array(
            1 => $translator->trans('YES'),
            2 => $translator->trans('YES (with restrictions)'),
            3 => $translator->trans('NO')
        );
        $output['accept_journal'] = $accept_journal;

        // getting the journal concept options
        $journal_concept = array(
            1 => $translator->trans('Priority'),
            2 => $translator->trans('Important'),
            3 => $translator->trans('Relative importance'),
            4 => $translator->trans('Not relevant')
        );
        $output['journal_concept'] = $journal_concept;

        $member = $request->query->get('member');
        if ( "committee" == $member ) {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolCommitteeRevision');
        } elseif ( "adhoc" == $member ) {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');
        } else {
            throw $this->createNotFoundException($translator->trans('You cannot edit this protocol'));
        }

        $protocol_revision = $protocol_revision_repository->find($protocol_revision_id);
        $output['protocol_revision'] = $protocol_revision;

        if ( "committee" == $member ) {
            $user = $protocol_revision->getMember();
            $protocol = $protocol_revision->getProtocol();
            $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevisionEvaluation');
            $protocol_adhoc_revision = $protocol_adhoc_revision_repository->findBy(array("protocol" => $protocol, "reviewer" => $user));
            $output['protocol_adhoc_revision'] = $protocol_adhoc_revision;
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/end-review", name="protocol_end_review")
     * @Template()
     */
    public function endReviewAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');

        $finish_options = array(
            "A" => $translator->trans("Approved"),
            'N' => $translator->trans('Not approved'),
            'C' => $translator->trans('Approved with restrictions'),
        );
        $output['finish_options'] = $finish_options;

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$protocol or !in_array($protocol->getStatus(), array("I", "E", "H"))) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            $required_fields = array('final-decision');
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $file = $request->files->get('draft-opinion');
            if(empty($file)) {
                $session->getFlashBag()->add('error', $translator->trans("You have to upload a decision."));
                return $output;
            }

            // setting the Scheduled status
            $protocol->setStatus($post_data['final-decision']);
            $protocol->setMonitoringAction(NULL);

            // getting the upload type
            $upload_type = $upload_type_repository->findOneBy(array("slug" => "opinion"));

            // adding the file uploaded
            $submission_upload = new SubmissionUpload();
            $submission_upload->setSubmission($protocol->getMainSubmission());
            $submission_upload->setUploadType($upload_type);
            $submission_upload->setUser($user);
            $submission_upload->setFile($file);
            $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

            $attachment = \Swift_Attachment::fromPath($submission_upload->getFilepath());

            if(!empty($post_data['monitoring-period'])) {
                $monitoring_action_next_date = new \DateTime();
                $monitoring_action_next_date->modify('+'. $post_data['monitoring-period'] .' months');
                $protocol->setMonitoringActionNextDate($monitoring_action_next_date);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission_upload);
            $em->flush();

            $protocol_history = new ProtocolHistory();
            $protocol_history->setProtocol($protocol);
            $protocol_history->setMessage($translator->trans(
                'Journal finalized by secretary under option "%option%".',
                array(
                    '%user%' => $user->getUsername(),
                    '%option%' => $finish_options[$post_data['final-decision']],
                )
            ));
            $em->persist($protocol_history);
            $em->flush();

            $protocol->getMainSubmission()->addAttachment($submission_upload);
            $em = $this->getDoctrine()->getManager();
            $em->persist($protocol->getMainSubmission());
            $em->flush();

            $protocol->setDecisionIn(new \DateTime());
            $em->persist($protocol);
            $em->flush();

            $investigators = array();
            $investigators[] = $protocol->getMainSubmission()->getOwner();
            foreach($protocol->getMainSubmission()->getTeam() as $investigator) {
                $investigators[] = $investigator;
            }
            foreach($investigators as $investigator) {
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $home_url = $baseurl . $this->generateUrl('home');
                $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                $_locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(112);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$_locale];
                $body = $text['message'];
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("%username%", $investigator->getName(), $body);
                $body = str_replace("%journal_url%", $url, $body);
                $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";

                $message = \Swift_Message::newInstance()
                ->setSubject("[LILACS] " . $translator->trans("The journal review was finalized!"))
                ->setFrom($util->getConfiguration('committee.email'))
                ->setTo($investigator->getEmail())
                ->setBody(
                    $body
                    ,
                    'text/html'
                );

                if(!empty($file)) {
                    $message->attach($attachment);
                }

                $send = $this->get('mailer')->send($message);
            }

            $session->getFlashBag()->add('success', $translator->trans("Journal was finalized with success!"));
            return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/final-review", name="protocol_final_review")
     * @Template()
     */
    public function finalReviewAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');

        $finish_options = array(
            "A" => $translator->trans("Approved"),
            'N' => $translator->trans('Not approved'),
        );
        $output['finish_options'] = $finish_options;

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$protocol or !in_array($protocol->getStatus(), array("V"))) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            $required_fields = array('final-decision');
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $file = $request->files->get('draft-opinion');
            if(empty($file)) {
                $session->getFlashBag()->add('error', $translator->trans("You have to upload a decision."));
                return $output;
            }

            // setting the Scheduled status
            $protocol->setStatus($post_data['final-decision']);
            $protocol->setMonitoringAction(NULL);

            // getting the upload type
            $upload_type = $upload_type_repository->findOneBy(array("slug" => "opinion"));

            // adding the file uploaded
            $submission_upload = new SubmissionUpload();
            $submission_upload->setSubmission($protocol->getMainSubmission());
            $submission_upload->setUploadType($upload_type);
            $submission_upload->setUser($user);
            $submission_upload->setFile($file);
            $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

            $attachment = \Swift_Attachment::fromPath($submission_upload->getFilepath());

            if(!empty($post_data['monitoring-period'])) {
                $monitoring_action_next_date = new \DateTime();
                $monitoring_action_next_date->modify('+'. $post_data['monitoring-period'] .' months');
                $protocol->setMonitoringActionNextDate($monitoring_action_next_date);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission_upload);
            $em->flush();

            $protocol_history = new ProtocolHistory();
            $protocol_history->setProtocol($protocol);
            $protocol_history->setMessage($translator->trans(
                'Journal finalized by secretary under option "%option%".',
                array(
                    '%user%' => $user->getUsername(),
                    '%option%' => $finish_options[$post_data['final-decision']],
                )
            ));
            $em->persist($protocol_history);
            $em->flush();

            $protocol->getMainSubmission()->addAttachment($submission_upload);
            $em = $this->getDoctrine()->getManager();
            $em->persist($protocol->getMainSubmission());
            $em->flush();

            $protocol->setDecisionIn(new \DateTime());
            $em->persist($protocol);
            $em->flush();

            $investigators = array();
            $investigators[] = $protocol->getMainSubmission()->getOwner();
            foreach($protocol->getMainSubmission()->getTeam() as $investigator) {
                $investigators[] = $investigator;
            }
            foreach($investigators as $investigator) {
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $home_url = $baseurl . $this->generateUrl('home');
                $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                $_locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(112);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$_locale];
                $body = $text['message'];
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("%username%", $investigator->getName(), $body);
                $body = str_replace("%journal_url%", $url, $body);
                $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";

                $message = \Swift_Message::newInstance()
                ->setSubject("[LILACS] " . $translator->trans("The journal review was finalized!"))
                ->setFrom($util->getConfiguration('committee.email'))
                ->setTo($investigator->getEmail())
                ->setBody(
                    $body
                    ,
                    'text/html'
                );

                if(!empty($file)) {
                    $message->attach($attachment);
                }

                $send = $this->get('mailer')->send($message);
            }

            $session->getFlashBag()->add('success', $translator->trans("Journal was finalized with success!"));
            return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/recommendations", name="protocol_recommendations")
     * @Template()
     */
    public function recommendationsAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');
        $issue_repository = $em->getRepository('Proethos2ModelBundle:Issue');

        $util = new Util($this->container, $this->getDoctrine());

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        // checking required deployment report attachment
        $upload_type = $upload_type_repository->findOneBy(array("slug" => "deployment-report"));
        $upload_type_id = $upload_type->getId();
        $deploy_report = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
        $output['deploy_report'] = ( $deploy_report ) ? true : false;

        // checking required issues
        $issues = array();
        foreach ($submission->getIssue() as $issue) {
            if ( $issue->getCreated() > $protocol->getDecisionIn() ) {
                $issues[] = $issue;
            }
        }

        $total_issues = count($issues);
        $output['total_issues'] = $total_issues;

        if (!$protocol or !in_array($protocol->getStatus(), array("C")) or $user != $protocol->getOwner()) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

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
                return $this->redirectToRoute('protocol_recommendations', array('protocol_id' => $protocol->getId()), 301);

            }

            if(isset($post_data['delete-issue-id']) and !empty($post_data['delete-issue-id'])) {
                $issue = $issue_repository->find($post_data['delete-issue-id']);
                
                if($issue) {
                    $em->remove($issue);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("Issue removed with success."));
                    return $this->redirectToRoute('protocol_recommendations', array('protocol_id' => $protocol->getId()), 301);
                }
            }

            if ( !$deploy_report ) {
                $file = $request->files->get('deploy-report');
                if ( empty($file) ) {
                    $session->getFlashBag()->add('error', $translator->trans("You have to upload a deployment report."));
                    return $output;
                }

                // adding the file uploaded
                $submission_upload = new SubmissionUpload();
                $submission_upload->setSubmission($protocol->getMainSubmission());
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($protocol->getMainSubmission()->getNumber());

                // $attachment = \Swift_Attachment::fromPath($submission_upload->getFilepath());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $protocol->getMainSubmission()->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($protocol->getMainSubmission());
                $em->flush();

                // $output['deploy_report'] = true;

                $session->getFlashBag()->add('success', $translator->trans("Deployment report added with success."));
                return $this->redirectToRoute('protocol_recommendations', array('protocol_id' => $protocol->getId()), 301);
            }

            if( $total_issues < 2 or $total_issues > 4 ) {
                $session->getFlashBag()->add('error', $translator->trans("Please submit at least 2 and at most 4 additional issues."));
                return $output;
            }
            
            if ( isset($post_data['send-info']) and $post_data['send-info'] == "yes" ) {
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $home_url = $baseurl . $this->generateUrl('home');
                $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                $protocol_history = new ProtocolHistory();
                $protocol_history->setProtocol($protocol);
                $protocol_history->setMessage($translator->trans(
                    'The recommendations of the journal were sent by the publisher.',
                    array(
                        '%user%' => $user->getUsername(),
                    )
                ));
                $em->persist($protocol_history);
                $em->flush();

                // setting the Reviewed status
                $protocol->setStatus("V");
                $em->persist($protocol);
                $em->flush();

                $_locale = $request->getSession()->get('_locale');
                $help = $help_repository->find(113);
                $translations = $trans_repository->findTranslations($help);
                $text = $translations[$_locale];
                $body = $text['message'];
                $body = str_replace("%home_url%", $home_url, $body);
                $body = str_replace("%journal_url%", $url, $body);
                $body = str_replace("%journal_code%", $protocol->getCode(), $body);
                $body = str_replace("\r\n", "<br />", $body);
                $body .= "<br /><br />";

                $secretaries_emails = array();
                foreach($user_repository->findAll() as $secretary) {
                    if(in_array("secretary", $secretary->getRolesSlug())) {
                        $secretaries_emails[] = $secretary->getEmail();
                    }
                }

                $message = \Swift_Message::newInstance()
                ->setSubject("[LILACS] " . $translator->trans("The recommendations of the journal were sent"))
                ->setFrom($util->getConfiguration('committee.email'))
                ->setTo($secretaries_emails)
                ->setBody(
                    $body
                    ,
                    'text/html'
                );

                // if(!empty($file)) {
                //     $message->attach($attachment);
                // }

                $send = $this->get('mailer')->send($message);

                $session->getFlashBag()->add('success', $translator->trans("The recommendations were sent successfully!"));
                return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);
            }
        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/delete", name="protocol_delete")
     * @Template()
     */
    public function deleteAction($protocol_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);
        $submission = $protocol->getMainSubmission();
        $output['protocol'] = $protocol;

        if (!$protocol or !(in_array('administrator', $user->getRolesSlug()) or $user == $protocol->getOwner())) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            $required_fields = array('are-you-sure');
            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            if($post_data['are-you-sure'] == 'yes') {
                $em->remove($protocol);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("Journal was removed with success!"));
                if(in_array('administrator', $user->getRolesSlug())) {
                    return $this->redirectToRoute('crud_committee_protocol_list', array(), 301);
                }
                return $this->redirectToRoute('crud_investigator_protocol_list', array(), 301);
            }

        }

        return $output;
    }

    /**
     * @Route("/journal/{protocol_id}/xml/{language_code}", name="protocol_xml")
     * @Template()
     */
    public function XmlOutputAction($protocol_id, $language_code)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');

        // getting the current submission
        $protocol = $protocol_repository->find($protocol_id);

        if (!$protocol or !(in_array('administrator', $user->getRolesSlug()) or $user == $protocol->getOwner())) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        // finding translation
        $submission = NULL;
        if($protocol->getMainSubmission()->getLanguage() == $language_code) {
            $submission = $protocol->getMainSubmission();
        } else {
            foreach($protocol->getMainSubmission()->getTranslations() as $translation) {
                if($translation->getLanguage() == $language_code) {
                    $submission = $translation;
                }
            }
        }

        if(!$submission) {
            throw $this->createNotFoundException($translator->trans('No journal found'));
        }

        $xml = new \SimpleXMLElement('<trials><trial></trial></trials>');

        $utrn = "";
        if($submission->getClinicalTrial()) {
            $trials = $submission->getClinicalTrial();
            foreach($trials as $trial) {
                if($trial->getName()->getSlug() == "universal-trial-number") {
                    $utrn = $trial->getNumber();
                }
            }
        }

        $configuration_repository = $em->getRepository('Proethos2ModelBundle:Configuration');
        $configurations = $configuration_repository->findOneBy(array('key' => 'committee.name'));
        $reg_name = $configurations->getValue();

        // var_dump($xml);
        $main = $xml->addChild('main');
        $main->addChild('trial_id', $protocol->getCode());
        $main->addChild('utrn', $utrn);
        $main->addChild('reg_name', $reg_name);
        $main->addChild('date_registration', $protocol->getCreated()->format("Y-m-d"));
        $main->addChild('primary_sponsor', $submission->getPrimarySponsor());
        $main->addChild('public_title', $submission->getPublicTitle());
        $main->addChild('acronym', $submission->getTitleAcronym());
        $main->addChild('scientific_title', $submission->getScientificTitle());
        $main->addChild('scientific_acronym', $submission->getTitleAcronym());
        $main->addChild('date_enrolment', $protocol->getDateInformed()->format("Y-m-d"));
        $main->addChild('type_enrolment', "actual");
        $main->addChild('target_size', $submission->getSampleSize());
        $main->addChild('recruitment_status', $submission->getRecruitmentStatus()->getName());
        $main->addChild('url', $baseurl . $this->generateUrl('protocol_show_protocol', array('protocol_id' => $protocol->getId())));
        $main->addChild('study_type', "");
        $main->addChild('study_design', $submission->getStudyDesign());
        $main->addChild('phase', "N/A");
        $main->addChild('hc_freetext', $submission->getHealthCondition());
        $main->addChild('i_freetext', $submission->getInterventions());

        $contacts = $main->addChild('contacts');
        $contact = $contacts->addChild('contact');
        $contact->addChild('type', "");
        $contact->addChild('address', "");
        $contact->addChild('city', "");
        $contact->addChild('zip', "");
        $contact->addChild('telephone', "");
        $contact->addChild('email', $submission->getOwner()->getEmail());
        $contact->addChild('affiliation', "");

        if($submission->getOwner()->getCountry()) {
            $contact->addChild('country1', $submission->getOwner()->getCountry()->getName());
        }

        $name = explode(" ", $submission->getOwner()->getName());
        $contact->addChild('firstname', $name[0]);
        if(count($name) > 1) {
            $contact->addChild('middlename', $name[1]);
        }
        if(count($name) > 2) {
            $lastname = str_replace($name[0], "", $submission->getOwner()->getName());
            $lastname = str_replace($name[1], "", $lastname);
            $lastname = trim($lastname);
            $contact->addChild('lastname', $lastname);
        }

        // adicionando agora todo o time
        foreach($submission->getTeam() as $team) {
            $contact = $contacts->addChild('contact');
            $contact->addChild('type', "");
            $contact->addChild('address', "");
            $contact->addChild('city', "");
            $contact->addChild('zip', "");
            $contact->addChild('telephone', "");
            $contact->addChild('email', $team->getEmail());
            $contact->addChild('affiliation', "");

            if($team->getCountry()) {
                $contact->addAttribute('country1', $team->getCountry()->getName());
            }

            $name = explode(" ", $team->getName());
            $contact->addChild('firstname', $name[0]);
            if(count($name) > 1) {
                $contact->addChild('middlename', $name[1]);
            }
            if(count($name) > 2) {
                $lastname = str_replace($name[0], "", $team->getName());
                $lastname = str_replace($name[1], "", $lastname);
                $lastname = trim($lastname);
                $contact->addChild('lastname', $lastname);
            }
        }

        $criterias = $main->addChild('criterias');
        $criteria = $criterias->addChild('criteria');
        $criteria->addChild('inclusion_criteria', $submission->getInclusionCriteria());
        $criteria->addChild('agemin', $submission->getMinimumAge() . "Y");
        $criteria->addChild('agemax', $submission->getMaximumAge() . "Y");
        $criteria->addChild('gender', substr($submission->getGender()->getName(), 0, 1));
        $criteria->addChild('exclusion_criteria', $submission->getExclusionCriteria());

        $primary_outcome = $main->addChild('primary_outcome');
        $primary_outcome->addChild('prim_outcome', $submission->getPrimaryOutcome());

        $primary_sponsor = $main->addChild('primary_sponsor');
        $primary_sponsor->addChild('sponsor_name', $submission->getPrimarySponsor());

        $secondary_outcome = $main->addChild('secondary_outcome');
        $secondary_outcome->addChild('sec_outcome', $submission->getSecondaryOutcome());

        $secondary_sponsor = $main->addChild('secondary_sponsor');
        $secondary_sponsor->addChild('sponsor_name', $submission->getSecondarySponsor());

        return new Response(
            $xml->asXML(),
            200,
            array(
                'Content-Type' => 'application/xml'
            )
        );
    }

    /**
     * @Route("/journal/{protocol_id}/initial-committee-review/send-alert/{protocol_revision_id}", name="protocol_initial_committee_review_send_alert")
     * @Template()
     */
    public function sendAlertAction($protocol_id, $protocol_revision_id)
    {

        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $util = new Util($this->container, $this->getDoctrine());

        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');
        $protocol = $protocol_repository->find($protocol_id);

        $member = $request->query->get('member');
        if ( "committee" == $member ) {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolCommitteeRevision');
        } elseif ( "adhoc" == $member ) {
            $protocol_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');
        } else {
            throw $this->createNotFoundException($translator->trans('You cannot send alerts'));
        }

        $protocol_revision = $protocol_revision_repository->find($protocol_revision_id);
        $protocol_revision->setSendAlertDate(new \DateTime());
        $em->persist($protocol_revision);
        $em->flush();

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $member = $user_repository->find($protocol_revision->getMember()->getId());

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);
        
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $home_url = $baseurl . $this->generateUrl('home');
        $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

        $_locale = $request->getSession()->get('_locale');
        $help = $help_repository->find(120);
        $translations = $trans_repository->findTranslations($help);
        $text = $translations[$_locale];
        $body = $text['message'];
        $body = str_replace("%home_url%", $home_url, $body);
        $body = str_replace("%username%", $member->getName(), $body);
        $body = str_replace("%journal_url%", $url, $body);
        $body = str_replace("%journal_code%", $protocol->getCode(), $body);
        $body = str_replace("\r\n", "<br />", $body);
        $body .= "<br /><br />";

        $message = \Swift_Message::newInstance()
        ->setSubject("[LILACS] " . $translator->trans("Review Remind"))
        ->setFrom($util->getConfiguration('committee.email'))
        ->setTo($member->getEmail())
        ->setBody(
            $body
            ,
            'text/html'
        );

        $send = $this->get('mailer')->send($message);

        if ( $send ) {
            $session->getFlashBag()->add('success', $translator->trans("Alert message were sent successfully!"));
        } else {
            $session->getFlashBag()->add('error', $translator->trans("Error sending alert message"));
        }

        if ( $protocol->getStatus() == "H" ) {
            $route = "protocol_end_review";
        } else {
            $route = "protocol_initial_committee_review";
        }

        return $this->redirectToRoute($route, array('protocol_id' => $protocol->getId()), 301);
    }

    /**
     * @Route("/adhoc/revision/history/{user_id}", name="adhoc_revision_history")
     * @Template()
     */
    public function showAdhocRevisionHistory($user_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $protocol_adhoc_revision_repository = $em->getRepository('Proethos2ModelBundle:ProtocolAdhocRevision');

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $user = $user_repository->find($user_id);
        $output['user'] = $user;
                
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

        $output['adhoc_history'] = array(
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

        return $output;
    }
}

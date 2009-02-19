<?php
//
// Definition of eZInformationType class
//
// Copyright (C) Lukasz Serwatka <ls@ez.no>.
//
// This file may be distributed and/or modified under the terms of the
// 'GNU General Public License' version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The 'GNU General Public License' (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//

class eZInformationType extends eZWorkflowEventType
{

    const WORKFLOW_TYPE_STRING = 'ezinformation';

    function __construct()
    {
        parent::__construct( self::WORKFLOW_TYPE_STRING, ezi18n( 'kernel/workflow/event', 'Information' ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array ( 'after' ) ) ) );
    }

    function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $object = eZContentObject::fetch( $parameters['object_id'] );

        require_once( 'kernel/common/template.php' );

        $ini = eZINI::instance();
        $informationINI = eZINI::instance( 'ezinformation.ini' );

        $contentClassIDArray = $informationINI->variable( 'InformationSettings', 'ContentClassID' );

        foreach ( $contentClassIDArray as $classID )
        {
            if ( $classID == $object->attribute( 'contentclass_id' ) )
            {
                $mail = new eZMail();
                $tpl = templateInit();

                $tpl->setVariable( 'object', $object );

                $hostname = eZSys::hostname();
                $tpl->setVariable( 'hostname', $hostname );

                $sitename = $ini->variable( 'SiteSettings','SiteURL' );
                	
                $emailSender = $informationINI->variable( 'MailSettings', 'EmailSender' );

                if ( !$emailSender ) {
                    $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
                }

                if ( !$emailSender ) {
                    $emailSender = $ini->variable( "MailSettings", "AdminEmail" );
                }

                $receiver = $informationINI->variable( 'MailSettings', 'EmailReceiver' );
                
                if ( !$receiver ) {
                    $receiver = $ini->variable( 'MailSettings', 'AdminEmail' );
                }

                $ccs = $informationINI->variable( 'MailSettings', 'EmailCc' );

                foreach($ccs as $cc) {
                  list($email, $name) = explode(';', $cc);
                  $mail->addCc($email, $name);
                }

                $bccs = $informationINI->variable( 'MailSettings', 'EmailBcc' );

                foreach($bccs as $bcc) {
                  list($email, $name) = explode(';', $bcc);
                  $mail->addBcc($email, $name);
                }
                
                $mail->setReceiver( $receiver );
                $mail->setSender( $emailSender );

                $body = $tpl->fetch( 'design:ezinformation/ezinformationmail.tpl' );
                $subject = $tpl->variable( 'subject' );

                $mail->setSubject( $subject );
                $mail->setBody( $body );

                $mailResult = eZMailTransport::send( $mail );
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( eZInformationType::WORKFLOW_TYPE_STRING, 'eZInformationType' );

?>

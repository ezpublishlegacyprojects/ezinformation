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

include_once( 'kernel/classes/ezworkflowtype.php' );

define( 'EZ_WORKFLOW_TYPE_INFORMATION', 'ezinformation' );

class eZInformationType extends eZWorkflowEventType
{

    function eZInformationType()
    {
        $this->eZWorkflowEventType( EZ_WORKFLOW_TYPE_INFORMATION, ezi18n( 'kernel/workflow/event', 'Information' ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array ( 'after' ) ) ) );
    }

    function execute( &$process, &$event )
    {
        $parameters = $process->attribute( 'parameter_list' );
		$versionID =& $parameters['version'];
        $object =& eZContentObject::fetch( $parameters['object_id'] );
        
		include_once( 'lib/ezutils/classes/ezini.php' );
		include_once( 'kernel/common/template.php' );
		
		$ini =& eZINI::instance(); 
        $informationINI =& eZINI::instance( 'ezinformation.ini' );
        
        $contentClassIDArray = $informationINI->variable( 'InformationSettings', 'ContentClassID' );
		
        foreach ( $contentClassIDArray as $classID ) 
        {
        	if ( $classID == $object->attribute( 'contentclass_id' ) ) 
        	{
        		include_once( 'lib/ezutils/classes/ezmail.php' );
				include_once( 'lib/ezutils/classes/ezmailtransport.php' );
		
        		$mail = new eZMail();
        		$tpl =& templateInit();
       			
        		$tpl->setVariable( 'object', $object );
        		
        		$hostname = eZSys::hostname();
            	$tpl->setVariable( 'hostname', $hostname );
        		
            	$sitename = $ini->variable( 'SiteSettings','SiteURL' );
            	
        		$emailSender = $informationINI->variable( 'MailSettings', 'EmailSender' );
        		
        		if ( !$emailSender )
            		$emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
        		if ( !$emailSender )
            		$emailSender = $ini->variable( "MailSettings", "AdminEmail" );

            	$receiver = $informationINI->variable( 'MailSettings', 'EmailReceiver' );
            	
            	if ( !$receiver )
            		$receiver = $ini->variable( 'MailSettings', 'AdminEmail' );
            		
        		$mail->setReceiver( $receiver );
        		$mail->setSender( $emailSender );
        		
        		$body = $tpl->fetch( 'design:ezinformation/ezinformationmail.tpl' );
        		$subject = $tpl->variable( 'subject' );
        		
				$mail->setSubject( $subject );
				$mail->setBody( $body );
		
				$mailResult = eZMailTransport::send( $mail );
        	}
        }

        return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerType( EZ_WORKFLOW_TYPE_INFORMATION, 'ezinformationtype' );

?>

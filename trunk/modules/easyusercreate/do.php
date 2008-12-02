<?php

include_once( "extension/easyusercreate/classes/usercreate.php" );
include_once( "kernel/common/template.php" );

// iniate objects
$userCreate = new userCreate;
$http 		=& eZHTTPTool::instance();
$tpl 		=& templateInit();

// get form data
$email 				= $http->postVariable('EmailAddress');
$userGroupNodeID	= $http->postVariable('UserGroupNodeID');

// create user from email address
$userCreate->createFromEmail($email, $userGroupNodeID);

// set variables in template
$tpl->setVariable( 'email', $email );

$Result['content'] =& $tpl->fetch( "design:easyusercreate/done.tpl" );
$Result['path'] = array( array( 'text' => "Nyhetsbrev", 'url' => false ) );

?>

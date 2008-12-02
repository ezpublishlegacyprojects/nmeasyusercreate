<?php

include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
include_once( "lib/ezutils/classes/ezini.php" );

class userCreate
{
	function userCreate()
	{
		// initate objects
		$this->ini =& eZINI::instance( 'usercreate.ini' );
	
		$this->settings['first_name'] 			= $this->ini->variable( 'Defaults', 'FirstName' );
		$this->settings['last_name'] 			= $this->ini->variable( 'Defaults', 'LastName' );
		$this->settings['user_group_node_id']	= $this->ini->variable( 'Defaults', 'UserGroupNodeID' );
	}
	function createFromEmail(&$email, $userGroupNodeID = false)
	{
		// initiate objects
		$ini =& eZINI::instance();
	
		// is a user group node id is specified
		if($userGroupNodeID AND $userGroupNodeID != '')
		{
			// check to make sure that the user is allowed to self register into this user group
			if($this->canSelfRegisterInUserGroup($userGroupNodeID))
			{
				$defaultUserPlacement = $userGroupNodeID;
			}
			else
			{
				$defaultUserPlacement = $this->settings['user_group_node_id'];	
			}
		}
		
		// if a user group node is is not specified
		else
		{
			$defaultUserPlacement = $this->settings['user_group_node_id'];	
		}
	
		// default user data
		$userClassID = $ini->variable( "UserSettings", "UserClassID" );
    	$class = eZContentClass::fetch( $userClassID );
		$userCreatorID = $ini->variable( "UserSettings", "UserCreatorID" );
    	$defaultSectionID = $ini->variable( "UserSettings", "DefaultSectionID" );
		
		// create content object
		$contentObject = $class->instantiate( $userCreatorID, $defaultSectionID );
		
		// assign main node
		$nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObject->attribute( 'id' ),
														   'contentobject_version' => 1,
														   'parent_node' => $defaultUserPlacement,
														   'is_main' => 1 ) );
    	$nodeAssignment->store();
		
		// get attributes
		$contentObjectAttributes =& $contentObject->contentObjectAttributes();
		
		// first name
		$contentObjectAttributes[0]->setAttribute( 'data_text', $this->settings['first_name'] );
		$contentObjectAttributes[0]->store();
		
		// last name
		$contentObjectAttributes[1]->setAttribute( 'data_text', $this->settings['last_name'] );
		$contentObjectAttributes[1]->store();
		
		// user account
		$user =& eZUser::fetch( $contentObject->attribute( 'id' ));
		$user->setAttribute('email', $email );
		$user->store();
		
		// store content object
		$contentObject->store();
		
		// publish object
		$operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' 	=> $contentObject->attribute( 'id' ),
                                                                                     'version' 		=> 1 ) );
	}
	
	function canSelfRegisterInUserGroup($userGroupNodeID)
	{
		// set default
		$canSelfRegister = false;
		
		// get array of usre groups the user can self register in
		if($this->ini->variable( 'AllowedSelfRegisterUserGroups', 'GroupID' ) as $nodeID)
		{
			if($userGroupNodeID == $nodeID)
			{
				$canSelfRegister = true;
			}
		}
		
		return $canSelfRegister;
	}
}

?>

<?php
include('include/config.inc.php');

$appEngine->checkUserAuthentication(true, ACL_MOD_HOOKS, ACL_ACTION_ADD);

// Action handling.
// Form request to create the user
$create = check_request_var('create');
if( $create )
{
    $appEngine->handleAction('create_hook');
}

// View template.
$appTemplate->loadFromFile( new \IF_File('templates/hookcreate.html') );
$appTemplate->processTemplate();
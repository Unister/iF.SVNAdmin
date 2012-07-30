<?php
include("include/config.inc.php");

$appEngine->checkUserAuthentication(true, ACL_MOD_HOOKS, ACL_ACTION_VIEW);

$delete = check_request_var("delete");
if($delete)
{
      $appEngine->handleAction('delete_hook');
}


$hook = new \svnadmin\core\entities\Hook();
$hookList = $hook->getHookList();
if (empty($hookList['precommit'])) {
    $hookList = $hookList['postcommit'];
} elseif (empty($hookList['postcommit'])) {
    $hookList = $hookList['precommit'];
} else {
    $hookList = array_merge($hookList['precommit'], $hookList['postcommit']);
}
$appTemplate->addReplacement( "hooks", $hookList );

// Load the group list template file and add the array of users.
$appTemplate->loadFromFile( new \IF_File("templates/hooklist.html") );
$appTemplate->processTemplate();
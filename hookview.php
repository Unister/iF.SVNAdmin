<?php
include("include/config.inc.php");

$appEngine->checkUserAuthentication(true, ACL_MOD_HOOKS, ACL_ACTION_VIEW);

$id = get_request_var("h");
$update = check_request_var("update");
$hook = new \svnadmin\core\entities\Hook($id);

if ($update) {
    $appEngine->checkUserAuthentication(true, ACL_MOD_HOOKS, ACL_ACTION_ADD);
    if ($hook->update(get_request_var("content"))) {
        $appTemplate->addDefine('INFO');
        $appTemplate->addReplacement('INFOMSG',$appTR->tr('Hook "' . $hook->getTitle() . '" updated!'));
    }
}

// Load the group list template file and add the array of users.
$appTemplate->addReplacement("hookid", $hook->id);
$appTemplate->addReplacement("hooktitle", $hook->getTitle());
$appTemplate->addReplacement("hookcontent", $hook->content);
$appTemplate->loadFromFile( new \IF_File("templates/hookview.html") );
$appTemplate->processTemplate();
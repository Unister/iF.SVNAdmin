<?php
/**
 * iF.SVNAdmin
 * Copyright (c) 2010 by Manuel Freiholz
 * http://www.insanefactory.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 */
if( !defined('ACTION_HANDLING') ) {
  die("HaHa!");
}
// Check module.
$appEngine->forwardInvalidModule( false);

// Get required variables.
$hookType = get_request_var("type");
$hookfilename = get_request_var("hookfilename");
$hookcontent = get_request_var("hookcontent");

if( ($hookfilename == NULL) || ($hookcontent == NULL) || ($hookType == NULL) ) {
    //Warning invalide input
    $appTemplate->addDefine("WARNING");
    $appTemplate->addReplacement("WARNINGMSG",$appTR->tr('Hook Type, Name and Content must be set'));

} else {
    //Create a new Hook and save it
    $hook = new \svnadmin\core\entities\Hook();
    $hook->filename     = $hookfilename;
    $hook->content  = $hookcontent;
    $hook->type = $hookType;

    if (!$hook->create()) {
        $appTemplate->addDefine('ERROR');
        $appTemplate->addReplacement('ERRORMSG',$appTR->tr('Hook with the name "' . $hook->getTitle() . '" already exist'));
    } else {
        $appTemplate->addDefine('INFO');
        $appTemplate->addReplacement('INFOMSG',$appTR->tr('Hook "' . $hook->getTitle() . '" created!'));
    }
}
?>
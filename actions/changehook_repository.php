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

$varRepoEnc = get_request_var("r");
$repoName = rawurldecode($varRepoEnc);
$selectedHooks = get_request_var('selected_hooks');

$repo = new \svnadmin\core\entities\Repository();
$repo->name = $repoName;

if (empty($selectedHooks)) {
    $appTemplate->addDefine('WARNING');
    $appTemplate->addReplacement('WARNINGMSG', $appTR->tr('Plase select at least one Hook'));
} else {
    $info = array();
    $error = array();
    if ($repo->addHooks($selectedHooks, true)) {
        $info[] = 'Done writing hooks on "' . $repo->name . '"';
    } else {
        $error[] = 'Fail to write hooks on "'. $repo->name . '"';
    }

    if (!empty($info)) {
        $appTemplate->addDefine('INFO');
        $appTemplate->addReplacement('INFOMSG', $appTR->tr(implode('<br/>', $info)));
    }

    if (!empty($error)) {
        $appTemplate->addDefine('ERROR');
        $appTemplate->addReplacement('ERRORMSG', $appTR->tr(implode('<br/>', $error)));
    }
}


?>
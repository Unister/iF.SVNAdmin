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

$selectedHooks = get_request_var('selected_hooks');
$selectedRepos = get_request_var('selected_repos');
$selectedRepo = get_request_var('r');

if ($selectedRepo) {
    $selectedRepos = array($selectedRepo);
}

if (!empty($selectedRepos) && !empty($selectedHooks)) {

    $repo = new svnadmin\core\entities\Repository();
    $info = array();
    $error = array();

    if (!empty($selectedHooks)) {
        foreach ($selectedRepos as $currentRepo) {
            $repo->setName($currentRepo);

            if ($repo->addHooks($selectedHooks)) {
                $info[] = 'Done writing Hook on "' . $currentRepo . '"';
            } else {
                $error[] = 'Error while writing in Repository "' . $currentRepo . '"';
            }
        }
    }

    if (!empty($error)) {
        $appTemplate->addDefine('ERROR');
        $appTemplate->addReplacement('ERRORMSG', $appTR->tr(implode('<br/>', $error)));
    }

    if (!empty($info)) {
        $appTemplate->addDefine('INFO');
        $appTemplate->addReplacement('INFOMSG', $appTR->tr(implode('<br/>', $info)));
    }
} else {
    $appTemplate->addDefine('WARNING');
    $appTemplate->addReplacement('WARNINGMSG', $appTR->tr('Please select hook and repository'));
}

?>
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

$selHooks = get_request_var( "selected_hook" );

if( $selHooks == null )
{
    $appTemplate->addDefine("WARNING");
    $appTemplate->addReplacement("WARNINGMSG",$appTR->tr("You have to select at least one Hook."));

} else {

    $repoList = $appEngine->getRepositoryViewProvider()->getRepositories();
    $msg = array();

    foreach ($selHooks as $hookId) {

        $hook = new \svnadmin\core\entities\Hook($hookId);
        if ($hook->delete()) {

            foreach ($repoList as $repo) {
                $repoMsg = $repo->removeHook($hook);
                if ($repoMsg) {
                    $msg[] = $repoMsg;
                }
            }

            $msg[] = 'Hook "' . $hook->title . '" deleted!';
        }
    }

    $msg = implode('<br/>', $msg);
    $appTemplate->addDefine("INFO");
    $appTemplate->addReplacement("INFOMSG", $appTR->tr($msg));
}

?>
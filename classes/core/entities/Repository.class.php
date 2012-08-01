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
namespace svnadmin\core\entities;

include_once './classes/core/entities/Hook.class.php';
use svnadmin\core\Engine;

class Repository
{
    const TYPE_PRECOMMIT = 'precommit';

    const TYPE_POSTCOMMIT = 'postcommit';

    const MOD_HOOKFILE = 0755;

    /**
     * The name of the repository.
     * @var string
     */
    public $name;

    /**
     * The parent identifier of the repository.
     * (Association to an SVNParentPath)
     * @var string/int
     */
    public $parentIdentifier;

    /**
     * Absolute path to svn (needed for hook managment)
     * @var sting
     */
    public $svnParentPath = '';

    private $_parentHookPath = '';
    /**
     * Constructor.
     *
     * @param string $name
     * @param string $parentIdentifier
     */
    public function __construct($name = null, $parentIdentifier = null)
    {
        $this->name = $name;
        $this->parentIdentifier = $parentIdentifier;
        $this->svnParentPath = Engine::getInstance()->getConfig()->getValue('Repositories:svnclient', 'SVNParentPath');
        $this->_parentHookPath = Engine::getInstance()->getConfig()->getValue('Repositories:svnclient', 'SvnHookpath');
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    public function getEncodedName()
    {
        return rawurlencode($this->name);
    }

    public function getEncodedParentIdentifier()
    {
        return rawurlencode($this->parentIdentifier);
    }

    /**
     * Add hooks to repository
     *
     * @param array $hooks
     * @param boolean $overwrite
     * @return boolean
     */
    public function addHooks($hooks, $overwrite = false)
    {
        $repoHookPath = $this->svnParentPath . '/' . $this->name . '/hooks';

        $successPost = true;
        $successPre = true;
        $filenamesPost = array();
        $filenamesPre = array();

        foreach ($hooks as $hookId) {
            $hook =  new \svnadmin\core\entities\Hook($hookId);
            if ($hook->type == self::TYPE_POSTCOMMIT) {
                $filenamesPost[] = $hook->getFilename();
            } else {
                $filenamesPre[] = $hook->getFilename();
            }
        }
        if (!$overwrite) {
            foreach ($this->getLoadedHooks() as $hook) {
                if ($hook->type == self::TYPE_POSTCOMMIT) {
                    $filenamesPost[] = $hook->getFilename();
                } else {
                    $filenamesPre[] = $hook->getFilename();
                }
            }
        }

        $filenamesPost = array_unique($filenamesPost);
        $filenamesPre = array_unique($filenamesPre);

        if (count($filenamesPost) > 0) {
            $content = $this->_createPostContent($filenamesPost, $repoHookPath);
            $filename = $repoHookPath . '/post-commit';
            if (file_exists($filename)) {
                if (!$this->_isAutomaticHook($filename)) {
                    return false;
                }
                unlink($filename);
            }
            $successPost =  file_put_contents($filename, $content);
            if ($successPost) {
                $successPost = chmod($filename, self::MOD_HOOKFILE);
            }
        }

        if (count($filenamesPre) > 0) {
            $content = $this->_createPreContent($filenamesPre, $repoHookPath);
            $filename = $repoHookPath . '/pre-commit';
            if (file_exists($filename)) {
                if (!$this->_isAutomaticHook($filename)) {
                    return false;
                }
                unlink($filename);
            }
            $successPre =  file_put_contents($filename, $content);
            if ($successPre) {
                $successPre = chmod($filename, self::MOD_HOOKFILE);
            }
        }

        return $successPost && $successPre;
    }

    /**
     * Add post-commit header and footer to hook content
     *
     * @param array $hooks
     * @param string $repoHookPath
     * @return string
     */
    private function _createPostContent($hooks, $repoHookPath)
    {
        $content = "#!/bin/bash\n";
        $content .= "#@automatic generated\n\n";

        $content .= "REPOS=\"$1\"\n";
        $content .= "REV=\"$2\"\n";
        $content .= "RETURN=()\n\n";

        foreach ($hooks as $i => $hook) {
            $content .= $hook . ' "$REPOS" "$REV"' . "\n";
            $content .= 'RETURN['.$i.']=$?' . "\n";

        }

        $content .= "\n" . 'IFS=$\'\n\'' . "\n";
        $content .= 'echo "${RETURN[*]}" | grep -v \'^0$\'' . "\n";
        $content .= '[ $? != 0 ]' . "\n";
        $content .= 'exit $?' . "\n";

        return $content;
    }

    /**
     * Add pre-commit header and footer to hook content
     *
     * @param array $hooks
     * @param string $repoHookPath
     * @return string
     */
    private function _createPreContent($hooks, $repoHookPath)
    {
        $content = "#!/bin/bash\n";
        $content .= "#@automatic generated\n\n";

        $content .= "REPOS=\"$1\"\n";
        $content .= "TXN=\"$2\"\n";
        $content .= "RETURN=()\n\n";

        foreach ($hooks as $i => $hook) {
            $content .= $hook . ' "$REPOS" "$TXN"' . "\n";
            $content .= 'RETURN[' . $i . ']=$?' . "\n";
        }

        $content .= "\n" . 'IFS=$\'\n\'' . "\n";
        $content .= 'echo "${RETURN[*]}" | grep -v \'^0$\'' . "\n";
        $content .= '[ $? != 0 ]' . "\n";
        $content .= 'exit $?' . "\n";

        return $content;
    }

    /**
     * Delete hooks from repository
     *
     * @param array $hooks (hookIds)
     * @return multitype:Ambigous <string, boolean>
     */
    public function deleteHooks($hooks)
    {
        $result = array();

        foreach ($hooks as $hookId) {
            $hook =  new \svnadmin\core\entities\Hook($hookId);
            $result[] = $this->removeHook($hook);
        }

        return $result;
    }

    /**
     * Check whether hook is automatic generated or not
     *
     * @param string $filename
     * @return boolean
     */
    protected function _isAutomaticHook($filename)
    {
        if (!file_exists($filename)) {
            return true;
        }
        $content = file_get_contents($filename);
        if (strpos($content, '#@automatic generated') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Remove one hook from repository
     *
     * @param \svnadmin\core\entities\Hook $hook
     * @return string|boolean
     */
    public function removeHook($hook)
    {
        $repoHookPath = $this->svnParentPath . '/' . $this->name . '/hooks';
        $parentHookPath = realpath($this->_parentHookPath);

        if ($hook->type == self::TYPE_PRECOMMIT) {
            $hookPath = realpath($repoHookPath . '/pre-commit');
        } else {
            $hookPath = realpath($repoHookPath . '/post-commit');
        }

        if (!file_exists($hookPath)) {
            return 'Hook for repository "' . $this->name . '" already removed.';
        }
        if (!$this->_isAutomaticHook($hookPath)) {
            return false;
        }

        $hooks = array();
        $handle = fopen($hookPath, 'r');
        while (!feof($handle)) {
            $line = fgets($handle);
            if (strpos($line, $parentHookPath) !== false) {
                list($otherHook) = explode(' ', $line);
                if ($hook->filename != basename($otherHook)) {
                    $hooks[] = $hook->type . '_' . basename($otherHook);
                }
            }
        }
        fclose($handle);

        if (count($hooks) > 0) {
            if ($this->addHooks($hooks, true)) {
                return 'Repository "' . $this->name . '" updated! Hook "' . $hook->getTitle()  . '" removed!';
            }
        } else {
            unlink($hookPath);
            return 'Repository "' . $this->name . '" updated! Hook deleted!';
        }

        return false;
    }

    /**
     * Get a string of loaded hooks seperated by ','
     * @return string
     */
    public function getHookList()
    {
        $hooks = array();

        $path = $this->svnParentPath . '/' . $this->name . '/hooks/';
        if (!$this->_isAutomaticHook($path . 'pre-commit')) {
            $hooks[] = '<b>pre-commit customized</b>';
        }
        if (!$this->_isAutomaticHook($path . 'post-commit')) {
            $hooks[] = '<b>post-commit customized</b>';
        }

        $loadedHooks = $this->getLoadedHooks();
        foreach ($loadedHooks as $hook) {
            $hooks[] = $hook->getTitle();
        }
        return implode(', ', $hooks);
    }

    /**
     * Get list of loaded hooks as array
     *
     * @return boolean|array
     */
    public function getLoadedHooks()
    {
        $repoHookPath = $this->svnParentPath . '/' . $this->name . '/hooks';
        if (!is_dir($repoHookPath)) {
            return false;
        }

        $preCommitPath = $repoHookPath . '/pre-commit';
        $postCommitPath = $repoHookPath . '/post-commit';

        $hooks = array();
        if (is_file($preCommitPath) && $this->_isAutomaticHook($preCommitPath)) {
            $handle = fopen($preCommitPath, 'r');
            while (!feof($handle)) {
                $line = fgets($handle);
                if (strpos($line, realpath($this->_parentHookPath)) !== false) {
                    list($hook) = explode(' ', $line);
                    $hooks[] = new Hook('precommit_' . basename($hook));
                }
            }
            fclose($handle);
        }

        if (is_file($postCommitPath) && $this->_isAutomaticHook($postCommitPath)) {
            $handle = fopen($postCommitPath, 'r');
            while (!feof($handle)) {
                $line = fgets($handle);
                if (strpos($line, realpath($this->_parentHookPath)) !== false) {
                    list($hook) = explode(' ', $line);
                    $hooks[] = new Hook('postcommit_' . basename($hook));
                }
            }
            fclose($handle);
        }

        return $hooks;
    }

    /**
     * Update hook list of this repository
     *
     * @param unknown_type $hookList
     * @return boolean
     */
    public function updateHooks($hookList)
    {
        foreach ($this->getLoadedHooks() as $loadedHook) {
            $this->removeHook($loadedHook);
        }

        return $this->addHooks($hookList);
    }

    public static function compare( $o1, $o2 )
    {
        if ($o1->name == $o2->name) {
            return 0;
        }
        return ($o1->name > $o2->name) ? +1 : -1;
    }
}
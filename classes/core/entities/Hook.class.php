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
namespace svnadmin\core\entities
{
use svnadmin\core\Engine;
  class Hook
  {

    const TYPE_PRECOMMIT = 'precommit';
    const TYPE_POSTCOMMIT = 'postcommit';

    protected $_properties = array(
        'author',
        'title'
    );

    /**
     * Name der HookDatei
     * @var string
     */
    public $id;
    public $filename;

    public $author;
    public $title;

    public $checked = false;

    /**
     * Inhalt der Hookdatei
     * @var string
     */
    public $content;

    public $type = null;

    /**
     * Name des Hook-Verzeichnises
     * @var string
     */
    public $hookpath;

    public function __construct($id = null)
    {
        $this->hookpath = Engine::getInstance()->getConfig()->getValue('Repositories:svnclient', 'SvnHookpath');

        if ($id != null) {
            $this->id = $id;
            list($this->type, $this->filename) = explode('_', $id);

            $this->content = file_get_contents($this->getFilename());
            $this->_parseMeta($this->content);
        }
    }

    protected function _parseMeta($content)
    {
        foreach ($this->_properties as $property) {
            $match = array();
            if (preg_match('~@'. $property . ' (.+)~', $content, $match)) {
                $this->$property = $match[1];
            }
        }
    }

    /**
     * Get the name of a Hook
     * @return string
     */
    public function getTitle()
    {
        if ($this->title == null) {
            return $this->filename;
        }
        return $this->title;
    }

    public function getAuthor()
    {
      return $this->author;
    }

    public function getId()
    {
        return $this->type . '_' . $this->filename;
    }

    /**
     * Get content of a hook
     * @return string
     */
    public function getContent()
    {
      return $this->content;
    }

    /**
     * Create a Hookfile in a given absolute path with Hookname + .hk as filename
     * @throws \Exception
     * @return boolean|number
     */
    public function create()
    {
      if (!file_exists($this->hookpath))
        throw new \Exception("The hook parent path doesn't exists: " . $this->hookpath);

      if (!file_exists($this->hookpath . '/' . self::TYPE_PRECOMMIT . '/')) {
          mkdir($this->hookpath . '/' . self::TYPE_PRECOMMIT . '/');
      }
      if (!file_exists($this->hookpath . '/' . self::TYPE_POSTCOMMIT . '/')) {
          mkdir($this->hookpath . '/' . self::TYPE_POSTCOMMIT . '/');
      }

      // Create absolute path.
      $filename = $this->getFilename();
      if (file_exists($filename)) {
          return false;
      }

      return $this->update($this->content);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getHookList()
    {
        $preHookList = glob($this->hookpath . '/'.self::TYPE_PRECOMMIT.'/*');
        $postHookList = glob($this->hookpath . '/'.self::TYPE_POSTCOMMIT.'/*');

        $hooks = array();
        foreach ($preHookList as $hook) {
            $hookObj = new Hook(self::TYPE_PRECOMMIT . '_' . basename($hook));
            $hooks['precommit'][] =  $hookObj;
        }
        foreach ($postHookList as $hook) {
            $hookObj = new Hook(self::TYPE_POSTCOMMIT . '_'. basename($hook));
            $hooks['postcommit'][] =  $hookObj;
        }

        return $hooks;
    }

    public function getFilename()
    {
        return realpath($this->hookpath . DIRECTORY_SEPARATOR . $this->type) . DIRECTORY_SEPARATOR . $this->filename;
    }

    public function update($data)
    {
        $filename = $this->getFilename();

        if (file_exists($filename)) {
            unlink($filename);
        }

        $data = str_replace("\r", "", $data);
        if (file_put_contents($filename, $data)) {
            $this->content = $data;
            chmod($filename, 0755);
            return true;
        }

        return false;
    }

    public function delete()
    {
        return unlink($this->getFilename());
    }

    public function getChecked()
    {
        if ($this->checked) {
            return 'checked="checked"';
        }
        return null;
    }

    public static function compare( $o1, $o2 )
    {
      if( $o1->name == $o2->name )
      {
        return 0;
      }
      return ($o1->name > $o2->name) ? +1 : -1;
    }
  }
}
?>

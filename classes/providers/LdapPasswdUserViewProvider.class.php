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

namespace svnadmin\providers
{

    include_once( "./classes/providers/ldap/LdapUserViewProvider.class.php" );
    include_once( "./classes/providers/PasswdUserProvider.class.php" );

  class LdapPasswdUserViewProvider implements \svnadmin\core\interfaces\IUserViewProvider
  {
    private $m_passwd = NULL;
    private $m_ldap = NULL;

    private static $m_instance = NULL;

    public static function getInstance()
    {
      if( self::$m_instance == NULL )
      {
        self::$m_instance = new LdapPasswdUserViewProvider;
      }
      return self::$m_instance;
    }

    public function __construct()
    {
        $this->m_passwd = \svnadmin\providers\ldap\LdapUserViewProvider::getInstance();
        $this->m_ldap = \svnadmin\providers\PasswdUserProvider::getInstance();
    }

    //////////////////////////////////////////////////////////////////////////////
    // -- Base interface implementations ----------------------------------------
    //////////////////////////////////////////////////////////////////////////////

    public function init()
    {
        if (!$this->m_passwd->init()) {
            return false;
        }
        return $this->m_ldap->init();
    }

    public function isUpdateable()
    {
      return false;
    }

    public function update()
    {
      return true;
    }

    //////////////////////////////////////////////////////////////////////////////
    // -- IUserViewProvider ------------------------------------------------------
    //////////////////////////////////////////////////////////////////////////////

    public function getUsers($withStarUser=true)
    {
        $list = $this->m_passwd->getUsers($withStarUser);
        return array_merge($list, $this->m_ldap->getUsers(false));
    }

    public function userExists( $objUser )
    {
        if ($this->m_passwd->userExists($objUser)) {
            return true;
        }
        return $this->m_ldap->userExists($objUser);
    }

    public function authenticate( $objUser, $password )
    {
        if ($this->m_passwd->authenticate($objUser, $password)) {
            return true;
        }
        return $this->m_ldap->authenticate($objUser, $password);
    }
  }
}
?>
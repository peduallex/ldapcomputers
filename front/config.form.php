<?php
/*
 -------------------------------------------------------------------------
 LDAP computers plugin for GLPI
 Copyright (C) 2019 by the ldapcomputers Development Team.

 https://github.com/pluginsGLPI/ldapcomputers
 -------------------------------------------------------------------------

 LICENSE

 This file is part of LDAP computers.

 LDAP computers is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 LDAP computers is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with LDAP computers. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------------

   @package   Plugin LDAP Computers
   @author    Aleksey Kotryakhov
   @co-author
   @copyright Copyright (c) 2009-2016 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/akm77/ldapcomputers
   @since     2019


 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkRight("plugin_ldapcomputers_config", UPDATE);

$config_ldap = new PluginLdapcomputersConfig();

if (!isset($_GET['id'])) {
   $_GET['id'] = "";
}

//LDAP Server add/update/delete
if (isset($_POST["update"])) {
   $config_ldap->update($_POST);
   Html::back();

} else if (isset($_POST["add"])) {
   //If no name has been given to this configuration, then go back to the page without adding
   if ($_POST["name"] != "") {
      if ($newID = $config_ldap->add($_POST)) {
         if (PluginLdapcomputersLdap::testLDAPConnection($newID)) {
            Session::addMessageAfterRedirect(__('Test successful'));
         } else {
            Session::addMessageAfterRedirect(__('Test failed'), false, ERROR);
            GlpiNetwork::addErrorMessageAfterRedirect();
         }
         Html::redirect($CFG_GLPI["root_doc"] . "/plugins/ldapcomputers/front/config.php?next=ext_ldap&id=".$newID);
      }
   }
   Html::back();

} else if (isset($_POST["purge"])) {
   $config_ldap->delete($_POST, 1);
   $config_ldap->redirectToList();

} else if (isset($_POST["test_ldap"])) {
   $config_ldap->getFromDB($_POST["id"]);

   if (PluginLdapcomputersLdap::testLDAPConnection($_POST["id"])) {
                                       //TRANS: %s is the description of the test
      $_SESSION["LDAP_TEST_MESSAGE"] = sprintf(__('Test successful: %s'),
                                               //TRANS: %s is the name of the LDAP main server
                                               sprintf(__('Main server %s'), $config_ldap->fields["name"]));
   } else {
                                       //TRANS: %s is the description of the test
      $_SESSION["LDAP_TEST_MESSAGE"] = sprintf(__('Test failed: %s'),
                                               //TRANS: %s is the name of the LDAP main server
                                               sprintf(__('Main server %s'), $config_ldap->fields["name"]));
      GLPINetwork::addErrorMessageAfterRedirect();
   }
   Html::back();

} else if (isset($_POST["test_ldap_backup"])) {
   $backup_ldap = new PluginLdapcomputersLdapbackup();
   $backup_ldap->getFromDB($_POST["ldap_backup_id"]);

   if (PluginLdapcomputersLdap::testLDAPConnection($_POST["id"], $_POST["ldap_backup_id"])) {
                                       //TRANS: %s is the description of the test
      $_SESSION["LDAP_TEST_MESSAGE"] = sprintf(__('Test successful: %s'),
                                               //TRANS: %s is the name of the LDAP replica server
                                               sprintf(__('Replicate %s'), $backup_ldap->fields["name"]));
   } else {
                                        //TRANS: %s is the description of the test
      $_SESSION["LDAP_TEST_MESSAGE"] = sprintf(__('Test failed: %s'),
                                               //TRANS: %s is the name of the LDAP replica server
                                               sprintf(__('Replicate %s'), $backup_ldap->fields["name"]));
      GLPINetwork::addErrorMessageAfterRedirect();
   }
   Html::back();

} else if (isset($_POST["add_backup_ldap"])) {
   $backup_ldap = new PluginLdapcomputersLdapbackup();
   unset($_POST["next"]);
   unset($_POST["id"]);
   $backup_ldap->add($_POST);
   Html::back();
}

Html::header(PluginLdapcomputersConfig::getTypeName(1), $_SERVER['PHP_SELF'], 'config', 'PluginLdapcomputersConfigmenu', 'ldapcomputersconfig');
$config_ldap->display($_GET);

Html::footer();

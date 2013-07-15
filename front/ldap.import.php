<?php
/*
 * @version $Id: ldap.import.php 20130 2013-02-04 16:55:15Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '..');
   include (GLPI_ROOT . "/inc/includes.php");
}

Session::checkRight("import_externalauth_users", 'w');

AuthLdap::manageValuesInSession($_REQUEST);

if (isset($_SESSION['ldap_import']['popup']) && $_SESSION['ldap_import']['popup']) {
   Html::popHeader($LANG['setup'][3], $_SERVER['PHP_SELF']);

} else {
   Html::header($LANG['setup'][3], $_SERVER['PHP_SELF'], "admin", "user", "ldap");
}

if (isset($_GET['start'])) {
   $_SESSION['ldap_import']['start'] = $_GET['start'];
}
if (isset($_GET['order'])) {
   $_SESSION['ldap_import']['order'] = $_GET['order'];
}

if ($_SESSION['ldap_import']['action'] == 'show') {
   $_REQUEST['target'] = $_SERVER['PHP_SELF'];

   $authldap = new AuthLDAP();
   $authldap->getFromDB($_SESSION['ldap_import']['authldaps_id']);

   AuthLdap::showUserImportForm($authldap);

   if (isset($_SESSION['ldap_import']['authldaps_id'])
       && $_SESSION['ldap_import']['authldaps_id'] != NOT_AVAILABLE
       && isset($_SESSION['ldap_import']['criterias'])
       && !empty($_SESSION['ldap_import']['criterias'])) {

      echo "<br />";
      AuthLdap::searchUser($authldap);
   }

} else {
   if (isset($_SESSION["ldap_process"])) {
      if ($count = count($_SESSION["ldap_process"])) {
         $percent = min(100,round(100*($_SESSION["ldap_process_count"]-$count)/
                                  $_SESSION["ldap_process_count"], 0));

         Html::displayProgressBar(400, $percent);
         $key = array_pop($_SESSION["ldap_process"]);
         AuthLdap::ldapImportUserByServerId(array('method' => AuthLDAP::IDENTIFIER_LOGIN,
                                                  'value'  => $key),
                                            $_SESSION['ldap_import']["mode"],
                                            $_SESSION['ldap_import']["authldaps_id"],
                                            true);
         Html::redirect($_SERVER['PHP_SELF']);

      } else {
         unset($_SESSION["ldap_process"]);
         Html::displayProgressBar(400, 100);

         echo "<div class='center b'>".$LANG['ldap'][10]."<br>";
         echo "<a href='".$_SERVER['PHP_SELF']."'>".$LANG['buttons'][13]."</a></div>";
         unset($_SESSION["authldaps_id"]);
         unset($_SESSION["mode"]);
         unset($_SESSION["interface"]);
         $_SESSION['ldap_import']['action'] = 'show';
         Ajax::refreshDropdownPopupInMainWindow();
      }

   } else {
      if (count($_POST['toprocess']) >0) {
         $_SESSION["ldap_process_count"] = 0;
         $_SESSION["authldaps_id"] = $_SESSION['ldap_import']['authldaps_id'];

         foreach ($_POST['toprocess'] as $key => $val) {
            if ($val == "on") {
               $_SESSION["ldap_process"][] = $key;
               $_SESSION["ldap_process_count"]++;
            }
         }
      }
      Html::redirect($_SERVER['PHP_SELF']);
   }
}

if (isset($_SESSION['ldap_import']['popup']) && $_SESSION['ldap_import']['popup']) {
   Html::ajaxFooter();
} else {
   Html::footer();
}
?>
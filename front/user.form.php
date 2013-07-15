<?php
/*
 * @version $Id: user.form.php 20130 2013-02-04 16:55:15Z moyo $
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

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

if (!isset($_GET["start"])) {
   $_GET["start"] = 0;
}

if (!isset($_GET["sort"])) {
   $_GET["sort"]="";
}
if (!isset($_GET["order"])) {
   $_GET["order"] = "";
}

$user = new User();
$groupuser = new Group_User();
//print_r($_POST);exit();
if (empty($_GET["id"]) && isset($_GET["name"])) {

   $user->getFromDBbyName($_GET["name"]);
   Html::redirect($CFG_GLPI["root_doc"]."/front/user.form.php?id=".$user->fields['id']);
}

if (empty($_GET["name"])) {
   $_GET["name"] = "";
}

if (isset($_REQUEST['getvcard'])) {
   if (empty($_GET["id"])) {
      Html::redirect($CFG_GLPI["root_doc"]."/front/user.php");
   }
   $user->check($_GET['id'], 'r');
   $user->generateVcard($_GET["id"]);

} else if (isset($_POST["add"])) {
   $user->check(-1, 'w', $_POST);

   // Pas de nom pas d'ajout
   if (!empty($_POST["name"]) && $newID=$user->add($_POST)) {
      Event::log($newID, "users", 4, "setup",
                 $_SESSION["glpiname"]." ".$LANG['log'][20]." ".$_POST["name"].".");
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $user->check($_POST['id'], 'w');
   $user->delete($_POST);
   Event::log(0, "users", 4, "setup",
              $_SESSION["glpiname"]." ".$LANG['log'][22]." ".$_POST["id"].".");
   $user->redirectToList();

} else if (isset($_POST["restore"])) {
   $user->check($_POST['id'], 'w');
   $user->restore($_POST);
   Event::log($_POST["id"], "users", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][23]);
   $user->redirectToList();

} else if (isset($_POST["purge"])) {
   $user->check($_POST['id'], 'w');
   $user->delete($_POST, 1);
   Event::log($_POST["id"], "users", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][24]);
   $user->redirectToList();

} else if (isset($_POST["force_ldap_resynch"])) {
   Session::checkRight('user_authtype', 'w');

   $user->getFromDB($_POST["id"]);
   AuthLdap::ldapImportUserByServerId(array('method' => AuthLDAP::IDENTIFIER_LOGIN,
                                            'value'  => $user->fields["name"]),
                                      true, $user->fields["auths_id"], true);
   Html::back();

} else if (isset($_POST["update"])) {
   $user->check($_POST['id'], 'w');
   $user->update($_POST);
   Event::log(0, "users", 5, "setup",
              $_SESSION["glpiname"]."  ".$LANG['log'][21]."  ".$user->fields["name"].".");
   Html::back();

} else if (isset($_POST["addgroup"])) {
   $groupuser->check(-1,'w',$_POST);
   if ($groupuser->add($_POST)) {
      Event::log($_POST["users_id"], "users", 4, "setup",
                 $_SESSION["glpiname"]." ".$LANG['log'][48]);
   }
   Html::back();

} else if (isset($_POST["deletegroup"])) {
   if (count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key,'w')) {
            $groupuser->delete(array('id' => $key));
         }
      }
   }
   Event::log($_POST["users_id"], "users", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][49]);
   Html::back();

} else if (isset($_POST["change_auth_method"])) {
   Session::checkRight('user_authtype', 'w');

   if (isset($_POST["auths_id"])) {
      User::changeAuthMethod(array($_POST["id"]), $_POST["authtype"], $_POST["auths_id"]);
   }
   Html::back();

} else {
   if (!isset($_REQUEST["ext_auth"])) {
      Session::checkRight("user", "r");
      Html::header($LANG['title'][13], '', "admin", "user");
      $user->showForm($_GET["id"]);
      Html::footer();

   } else {
      Session::checkRight("import_externalauth_users", "w");

      if (isset($_POST['add_ext_auth_ldap'])) {
         if (isset($_POST['login']) && !empty($_POST['login'])) {
            AuthLdap::importUserFromServers(array('name' => $_POST['login']));
         }
         Html::back();
      }
      if (isset($_POST['add_ext_auth_simple'])) {
         if (isset($_POST['login']) && !empty($_POST['login'])) {
            $input = array('name'     => $_POST['login'],
                           '_extauth' => 1,
                           'add'      => 1);
            $user->check(-1, 'w', $input);
            $newID = $user->add($input);
            Event::log($newID, "users", 4, "setup",
                       $_SESSION["glpiname"]." ".$LANG['log'][20]." ".$_POST['login'].".");
         }
         Html::back();
      }

      Html::header($LANG['title'][13], '', "admin", "user");
      User::showAddExtAuthForm();
      Html::footer();
   }
}
?>
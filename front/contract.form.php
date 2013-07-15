<?php
/*
 * @version $Id: contract.form.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------


define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkRight("contract", "r");

if (!isset($_GET["id"])) {
   $_GET["id"] = -1;
}

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$contract         = new Contract();
$contractitem     = new Contract_Item();
$contractsupplier = new Contract_Supplier();

if (isset($_POST["add"])) {
   $contract->check(-1,'w',$_POST);

   if ($newID = $contract->add($_POST)) {
      Event::log($newID, "contracts", 4, "financial",
               $_SESSION["glpiname"]." ".$LANG['log'][20]." ".$_POST["num"].".");
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $contract->check($_POST['id'],'w');

   if ($contract->delete($_POST)) {
      Event::log($_POST["id"], "contracts", 4, "financial", $_SESSION["glpiname"]." ".$LANG['log'][22]);
   }
   $contract->redirectToList();

} else if (isset($_POST["restore"])) {
   $contract->check($_POST['id'],'w');

   if ($contract->restore($_POST)) {
      Event::log($_POST["id"], "contracts", 4, "financial", $_SESSION["glpiname"]." ".$LANG['log'][23]);
   }
   $contract->redirectToList();

} else if (isset($_REQUEST["purge"])) {
   $contract->check($_REQUEST['id'],'d');

   if ($contract->delete($_REQUEST,1)) {
      Event::log($_REQUEST["id"], "contracts", 4, "financial", $_SESSION["glpiname"]." ".$LANG['log'][24]);
   }
   $contract->redirectToList();

} else if (isset($_POST["update"])) {
   $contract->check($_POST['id'],'w');

   if ($contract->update($_POST)) {
      Event::log($_POST["id"], "contracts", 4, "financial", $_SESSION["glpiname"]." ".$LANG['log'][21]);
   }
   Html::back();

} else if (isset($_POST["additem"])) {
   $contractitem->check(-1,'w',$_POST);

   if ($contractitem->add($_POST)) {
      Event::log($_POST["contracts_id"], "contracts", 4, "financial",
               $_SESSION["glpiname"]." ".$LANG['log'][32]);
   }
   Html::back();

} else if (isset($_POST["deleteitem"])) {
   if (count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($contractitem->can($key,'w')) {
            $contractitem->delete(array('id' => $key));
         }
      }
   }
   Event::log($_POST["contracts_id"], "contracts", 4, "financial",
            $_SESSION["glpiname"]." ".$LANG['log'][33]);
   Html::back();

} else if (isset($_GET["deleteitem"])) {
   $contractitem->check($_GET["id"], 'w');

   if ($contractitem->delete($_GET)) {
      Event::log($_GET["contracts_id"], "contracts", 4, "financial",
               $_SESSION["glpiname"]." ".$LANG['log'][33]);
   }
   Html::back();

} else if (isset($_POST["addcontractsupplier"])) {
   $contractsupplier->check(-1,'w',$POST);

   if ($contractsupplier->add($_POST)) {
      Event::log($_POST["contracts_id"], "contracts", 4, "financial",
               $_SESSION["glpiname"]." ".$LANG['log'][34]);
   }
   Html::back();

} else if (isset($_GET["deletecontractsupplier"])) {
   $contractsupplier->check($_GET['id'],'w');

   if ($contractsupplier->delete($_GET)) {
      Event::log($_GET["contracts_id"], "contracts", 4, "financial",
               $_SESSION["glpiname"]." ".$LANG['log'][35]);
   }
   Html::back();

} else {
   Html::header($LANG['Menu'][25],$_SERVER['PHP_SELF'],"financial","contract");
   $contract->showForm($_GET["id"], array('withtemplate' => $_GET["withtemplate"]));
   Html::footer();
}
?>
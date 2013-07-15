<?php
/*
 * @version $Id: cartridgeitem.form.php 20130 2013-02-04 16:55:15Z moyo $
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

Session::checkRight("cartridge", "r");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$cartype = new CartridgeItem();

if (isset($_POST["add"])) {
   $cartype->check(-1,'w',$_POST);

   if ($newID = $cartype->add($_POST)) {
      Event::log($newID, "cartridges", 4, "inventory",
               $_SESSION["glpiname"]." ".$LANG['log'][20]." ".$_POST["name"].".");
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $cartype->check($_POST["id"],'w');

   if ($cartype->delete($_POST)) {
      Event::log($_POST["id"], "cartridges", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][22]);
   }
   $cartype->redirectToList();

} else if (isset($_POST["restore"])) {
   $cartype->check($_POST["id"],'w');

   if ($cartype->restore($_POST)) {
      Event::log($_POST["id"], "cartridges", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][23]);
   }
   $cartype->redirectToList();

} else if (isset($_POST["purge"])) {
   $cartype->check($_POST["id"],'w');

   if ($cartype->delete($_POST,1)) {
      Event::log($_POST["id"], "cartridges", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][24]);
   }
   $cartype->redirectToList();

} else if (isset($_POST["update"])) {
   $cartype->check($_POST["id"],'w');

   if ($cartype->update($_POST)) {
      Event::log($_POST["id"], "cartridges", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][21]);
   }
   Html::back();

} else if (isset($_POST["addtype"])) {
   $cartype->check($_POST["tID"],'w');

   if ($cartype->addCompatibleType($_POST["tID"],$_POST["printermodels_id"])) {
      Event::log($_POST["tID"], "cartridges", 4, "inventory",
               $_SESSION["glpiname"]." ".$LANG['log'][30]);
   }
   Html::back();

} else if (isset($_GET["deletetype"])) {
   $cartype->check($_GET["tID"],'w');

   if ($cartype->deleteCompatibleType($_GET["id"])) {
      Event::log($_GET["tID"], "cartridges", 4, "inventory",
               $_SESSION["glpiname"]." ".$LANG['log'][31]);
   }
   Html::back();

} else {
   Html::header($LANG['Menu'][21],$_SERVER['PHP_SELF'],"inventory","cartridge");
   $cartype->showForm($_GET["id"]);
   Html::footer();
}
?>
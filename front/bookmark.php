<?php
/*
 * @version $Id: bookmark.php 21104 2013-06-11 06:16:36Z moyo $
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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

if (!isset($_GET["type"])) {
   $_GET["type"] = -1;
}

if (!isset($_GET["itemtype"])) {
   $_GET["itemtype"] = -1;
}

if (!isset($_GET["url"])) {
   $_GET["url"] = "";
}

$bookmark = new Bookmark();

if (isset($_POST["add"])) {
   $bookmark->check(-1, 'w', $_POST);

   $bookmark->add($_POST);
   $_GET["action"] = "load";

} else if (isset($_POST["update"])) {
   $bookmark->check($_POST["id"], 'w');   // Right to update the bookmark
   $bookmark->check(-1, 'w', $_POST);     // Right when entity change

   $bookmark->update($_POST);
   $_GET["action"] = "load";

} else if ($_GET["action"] == "edit" && isset($_GET['mark_default']) && isset($_GET["id"])) {
   $bookmark->check($_GET["id"], 'r');

   if ($_GET["mark_default"] >0) {
      $bookmark->mark_default($_GET["id"]);
   } else if ($_GET["mark_default"]==0) {
      $bookmark->unmark_default($_GET["id"]);
   }
   $_GET["action"] = "load";

} else if ($_GET["action"] == "load" && isset($_GET["id"]) && $_GET["id"]>0) {
   $bookmark->check($_GET["id"], 'r');
   $bookmark->load($_GET["id"]);

} else if (isset($_POST["delete"])) {
   $bookmark->check($_POST["id"], 'w');

   $bookmark->delete($_POST);
   $_GET["action"] = "load";

} else if (isset($_POST["delete_several"])) {
   if (isset($_POST["bookmark"])) {
      foreach ($_POST["bookmark"] as $ID=>$value) {
         if ($bookmark->can($ID, 'w')) {
            $bookmark->delete(array("id" => $ID));
         }
      }
   }
   $_GET["action"] = "load";
}

if ($_GET["action"] == "edit") {

   if (isset($_GET['id']) && $_GET['id']>0) {
      // Modify
      $bookmark->check($_GET["id"], 'w');
      $bookmark->showForm($_GET['id']);
   } else {
      // Create
      $bookmark->check(-1, 'w');
      $bookmark->showForm(0, array('type'     => $_GET["type"],
                                   'url'      => rawurldecode($_GET["url"]),
                                   'itemtype' => $_GET["itemtype"]));
   }
} else {
   echo '<br>';

   $bookmark->showTabs();
   echo "<div id='tabcontent'>&nbsp;</div>";
   echo "<script type='text/javascript'>loadDefaultTab();</script>";
}
?>
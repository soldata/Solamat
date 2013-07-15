<?php


/*
 * @version $Id: search.php 20130 2013-02-04 16:55:15Z moyo $
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

Session::checkCentralAccess();
Html::header($LANG['search'][0],$_SERVER['PHP_SELF']);

if (!$CFG_GLPI['allow_search_global']) {
   Html::displayRightError();
}
if (isset($_GET["globalsearch"])) {
   $searchtext=$_GET["globalsearch"];

   foreach ($CFG_GLPI["globalsearch_types"] as $itemtype) {
      $item = new $itemtype();
      if ($item->canView()) {
         $_GET["reset"] = 'reset';
         $_GET["display_type"] = GLOBAL_SEARCH;

         Search::manageGetValues($itemtype,false,true);

         if (!isset($_GET["field"][0])) {
            // From global search only
            $_GET["field"][0] = 'view';
         }

         if ($_GET["field"][0] =='view') {
            $_GET["contains"][0]   = $searchtext;
            $_GET["searchtype"][0] = 'contains';
            $_SESSION["glpisearchcount"][$itemtype] = 1;

         } else {
            $_GET["field"][1] = 'view';
            $_GET["contains"][1]   = $searchtext;
            $_GET["searchtype"][1] = 'contains';
            $_SESSION["glpisearchcount"][$itemtype] = 2;
         }
         Search::showList($itemtype,$_GET);
         unset($_GET["contains"]);
         unset($_GET["searchtype"]);
         echo "<hr>";
         $_GET = array();
      }
   }
}

Html::footer();
?>
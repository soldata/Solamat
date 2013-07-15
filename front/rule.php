<?php
/*
 * @version $Id: rule.php 20130 2013-02-04 16:55:15Z moyo $
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

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkCentralAccess();

Html::header($LANG['common'][12], $_SERVER['PHP_SELF'], "admin", "rule", -1);

echo "<table class='tab_cadre'>";
echo "<tr><th>" . $LANG['rulesengine'][24] . "</th></tr>";

foreach ($CFG_GLPI["rulecollections_types"] as $rulecollectionclass) {
   $rulecollection = new $rulecollectionclass();
   if ($rulecollection->canList()) {
      if ($plug = isPluginItemType($rulecollectionclass)) {
         $function = 'plugin_version_'.strtolower($plug['plugin']);
         $plugname = $function();
         $title = $plugname['name'].' - ';
      } else {
         $title = '';
      }
      $title .= $rulecollection->getTitle();
      echo "<tr class='tab_bg_1'><td class='center b'>";
      echo "<a href='".Toolbox::getItemTypeSearchURL($rulecollection->getRuleClassName())."'>";
      echo $title."</a></td></tr>";
   }
}

if (Session::haveRight("transfer","r" ) && Session::isMultiEntitiesMode()) {
   echo "<tr class='tab_bg_1'><td class='center b'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/front/transfer.php'>".$LANG['transfer'][1]."</a>";
   echo "</td></tr>";
}

echo "</table>";

Html::footer();
?>
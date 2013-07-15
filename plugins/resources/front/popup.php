<?php
/*
 * @version $Id: popup.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if (isset($_GET["popup"])) {
   $_SESSION["glpipopup"]["name"] = $_GET["popup"];
}

if (isset($_SESSION["glpipopup"]["name"])) {
   switch ($_SESSION["glpipopup"]["name"]) {
      case "test_rule" :
         Html::popHeader($LANG['buttons'][50], $_SERVER['PHP_SELF']);
         include GLPI_ROOT . "/front/rule.test.php";
         break;

      case "test_all_rules" :
         Html::popHeader($LANG['rulesengine'][84], $_SERVER['PHP_SELF']);
         include GLPI_ROOT . "/front/rulesengine.test.php";
         break;

      case "show_cache" :
         Html::popHeader($LANG['rulesengine'][100], $_SERVER['PHP_SELF']);
         include GLPI_ROOT . "/front/rule.cache.php";
         break;
   }
   echo "<div class='center'><br><a href='javascript:window.close()'>".$LANG['buttons'][60]."</a>";
   echo "</div>";
   Html::popFooter();
}

?>
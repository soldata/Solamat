<?php
/*
 * @version $Id: rule.common.php 20130 2013-02-04 16:55:15Z moyo $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

$rule = $rulecollection->getRuleClass();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$rulecollection->checkGlobal('r');

if (isset($_GET["action"])) {
   $rulecollection->checkGlobal('w');
   $rulecollection->changeRuleOrder($_GET["id"],$_GET["action"]);
   Html::back();

} else if (isset($_POST["action"])) {
   $rulecollection->checkGlobal('w');

   // Use massive action system
   switch ($_POST["action"]) {
      case "delete" :
         if (isset($_POST["item"]) && count($_POST["item"])) {
            foreach ($_POST["item"] as $key => $val) {
               $rule->getFromDB($key);
               $input["id"] = $key;
               $rulecollection->deleteRuleOrder($rule->fields["ranking"]);
               $rule->delete(array('id' => $key));
            }
            Event::log(0, "rules", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][22]);
            Html::back();
         }
         break;

      case "move_rule" :
         if (isset($_POST["item"]) && count($_POST["item"])) {
            foreach ($_POST["item"] as $key => $val) {
               $rule->getFromDB($key);
               $rulecollection->moveRule($key, $_POST['ranking'], $_POST['move_type']);
            }
         }
         break;

      case "activate_rule" :
         if (isset($_POST["item"])) {
            $rule = new Rule();
            foreach ($_POST["item"] as $key => $val) {
               if ($val == 1) {
                  $input['id'] = $key;
                  $input['is_active'] = $_POST["activate_rule"];
                  $rule->update($input);
               }
            }
         }
         break;
   }

} else if (isset($_POST["replay_rule"]) || isset($_GET["replay_rule"])) {
   $rulecollection->checkGlobal('w');

   // Current time
   $start = explode(" ",microtime());
   $start = $start[0]+$start[1];

   // Limit computed from current time
   $max = get_cfg_var("max_execution_time");
   $max = $start + ($max>0 ? $max/2.0 : 30.0);

   Html::header($LANG['rulesengine'][17], $_SERVER['PHP_SELF'], "admin", $rulecollection->menu_type,
                $rulecollection->menu_option);

   if (!(isset($_POST['replay_confirm']) || isset($_GET['offset']))
       && $rulecollection->warningBeforeReplayRulesOnExistingDB($_SERVER['PHP_SELF'])) {
      Html::footer();
      exit();
   }

   echo "<table class='tab_cadrehov'>";

   echo "<tr><th><div class='relative b'>" .$rulecollection->getTitle(). " - " .
         $LANG['rulesengine'][76]. "</div></th></tr>\n";
   echo "<tr><td class='center'>";
   Html::createProgressBar($LANG['rulesengine'][90]);
   echo "</td></tr>\n";
   echo "</table>";

   if (!isset($_GET['offset'])) {
      // First run
      $offset       = $rulecollection->replayRulesOnExistingDB(0, $max, array(), $_POST);
      $manufacturer = (isset($_POST["manufacturer"]) ? $_POST["manufacturer"] : 0);

   } else {
      // Next run
      $offset       = $rulecollection->replayRulesOnExistingDB($_GET['offset'], $max, array(),
                                                               $_GET);
      $manufacturer = $_GET["manufacturer"];

      // global start for stat
      $start = $_GET["start"];
   }

   if ($offset < 0) {
      // Work ended
      $end   = explode(" ",microtime());
      $duree = round($end[0]+$end[1]-$start);
      Html::changeProgressBarMessage($LANG['rulesengine'][91]." (".Html::timestampToString($duree).")");
      echo "<a href='".$_SERVER['PHP_SELF']."'>".$LANG['buttons'][13]."</a>";

   } else {
      // Need more work
      Html::redirect($_SERVER['PHP_SELF']."?start=$start&replay_rule=1&offset=$offset&manufacturer=".
                   "$manufacturer");
   }

   Html::footer(true);
   exit();
}

Html::header($LANG['rulesengine'][17], $_SERVER['PHP_SELF'], 'admin', $rulecollection->menu_type,
             $rulecollection->menu_option);

$rulecollection->showTabs();
echo "<div id='tabcontent'>&nbsp;</div>";
echo "<script type='text/javascript'>loadDefaultTab();</script>";
Html::footer();
?>
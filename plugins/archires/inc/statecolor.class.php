<?php
/*
 * @version $Id: statecolor.class.php 174 2012-07-04 15:09:18Z yllen $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2011 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginArchiresStateColor extends CommonDBTM {


   function canCreate() {
      return plugin_archires_haveRight('archires', 'w');
   }


   function canView() {
      return plugin_archires_haveRight('archires', 'r');
   }


   function getFromDBbyState($state) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `states_id` = '$state'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }


   function addStateColor($state,$color) {
      global $DB;

      if ($state!='-1') {
         if ($this->GetfromDBbyState($state)) {
            $this->update(array('id'    => $this->fields['id'],
                                'color' => $color));
         } else {
            $this->add(array('states_id' => $state,
                             'color'     => $color));
         }
      } else {
         $query  = "SELECT *
                    FROM `glpi_states`";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         $i      = 0;

         while ($i < $number) {
            $state_table = $DB->result($result, $i, "id");
            if ($this->GetfromDBbyState($state_table)) {
               $this->update(array('id'    => $this->fields['id'],
                                   'color' => $color));
            } else {
               $this->add(array('states_id' => $state_table,
                                'color'     => $color));
            }
            $i++;
         }
      }
   }


   function deleteStateColor($ID) {
      $this->delete(array('id' => $ID));
   }


   function showConfigForm($canupdate=false) {
      global $DB, $LANG, $CFG_GLPI;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                ORDER BY `states_id` ASC;";
      $i    = 0;
      $used = array();

      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);

         if ($canupdate) {
            echo "<form method='post' name='massiveaction_form_state_color' id='".
                  "massiveaction_form_state_color' action='./config.form.php'>";
         }
         if ($number != 0) {
            echo "<div id='liste_color'>";
            echo "<table class='tab_cadre' cellpadding='5'>";
            echo "<tr>";
            echo "<th class='left'>".$LANG['plugin_archires'][27]."</th>";
            echo "<th class='left'>".$LANG['plugin_archires'][20]."</th><th></th>";
            if ($number > 1) {
               echo "<th class='left'>".$LANG['plugin_archires'][27]."</th>";
               echo "<th class='left'>".$LANG['plugin_archires'][20]."</th><th></th>";
            }
            echo "</tr>";

            while ($ligne= mysql_fetch_array($result)) {
               $ID        = $ligne["id"];
               $states_id = $ligne["states_id"];
               $used[]    = $states_id;
               if ($i % 2==0 && $number>1) {
                  echo "<tr class='tab_bg_1'>";
               }
               if ($number == 1) {
                  echo "<tr class='tab_bg_1'>";
               }
               echo "<td>".Dropdown::getDropdownName("glpi_states",$ligne["states_id"])."</td>";
               echo "<td bgcolor='".$ligne["color"]."'>".$ligne["color"]."</td>";
               echo "<td><input type='hidden' name='id' value='$ID'>";
               if ($canupdate) {
                  echo "<input type='checkbox' name='item_color[$ID]' value='1'>";
               }
               echo "</td>";

               $i++;
               if (($i == $number) && ($number  % 2 !=0) && $number>1) {
                  echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
               }
            }

            if ($canupdate) {
               echo "<tr class='tab_bg_1'>";
               if ($number > 1)
                  echo "<td colspan='8' class='center'>";
               else
                  echo "<td colspan='4' class='center'>";

               echo "<a onclick= \"if (markCheckboxes ('massiveaction_form_state_color')) ".
                     "return false;\" href='#'>".
                     $LANG['buttons'][18]."</a>";
               echo " - <a onclick= \"if (unMarkCheckboxes ('massiveaction_form_state_color')) ".
                     "return false;\" href='#'>".
                     $LANG['buttons'][19]."</a> ";
               Html::closeArrowMassives(array('delete_color_state' => $LANG['buttons'][6]));
            } else {
               echo "</table>";
            }
            echo "</div>";
         }

         if ($canupdate) {
            echo "<table class='tab_cadre' cellpadding='5'>";
            echo "<tr><th colspan='3'>".$LANG['plugin_archires']['setup'][19]." : </th></tr>";
            echo "<tr class='tab_bg_1'><td>";
            $this->dropdownState($used);
            echo "</td>";
            echo "<td><input type='text' name='color'>";
            echo "&nbsp;";
            Html::showToolTip(nl2br($LANG['plugin_archires']['setup'][12]),
                              array('link'       => 'http://www.graphviz.org/doc/info/colors.html',
                                    'linktarget' => '_blank'));
            echo "<td class='center'>";
            Html::closeArrowMassives(array('add_color_state' => $LANG['buttons'][2]));
            Html::closeForm();
         }
      }
   }


   function dropdownState($used=array()) {
      global $DB, $LANG, $CFG_GLPI;

      $limit = $_SESSION["glpidropdown_chars_limit"];
      $where = "";

      if (count($used)) {
         $where = "WHERE `id` NOT IN (0";
         foreach ($used as $ID) {
            $where .= ", $ID";
         }
         $where .= ")";
      }

      $query = "SELECT *
                FROM `glpi_states`
                $where
                ORDER BY `name`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number !="0") {
         echo "<select name='states_id'>\n";
         echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
         echo "<option value='-1'>".$LANG['plugin_archires'][15]."</option>\n";
         while ($data= mysql_fetch_array($result)) {
            $output = $data["name"];
            if (Toolbox::strlen($output) > $limit) {
               $output = Toolbox::substr($output,0,$limit)."&hellip;";
            }
            echo "<option value='".$data["id"]."'>".$output."</option>";
         }
         echo "</select>";
      }
   }


   function displayColorState($device) {
      global $CFG_GLPI,$DB,$LANG;

      $graph ="";
      $query_state = "SELECT *
                      FROM `".$this->getTable()."`
                      WHERE `states_id`= '".$device["states_id"]."'";
      $result_state = $DB->query($query_state);
      $number_state = $DB->numrows($result_state);

      if ($number_state != 0 && $device["states_id"] > 0) {
         $color_state = $DB->result($result_state,0,"color");
         $graph ="<font color=\"$color_state\">".Dropdown::getDropdownName("glpi_states",
                                                                           $device["states_id"])
                ."</font>";
      } else if ($number_state == 0 && $device["states_id"] > 0) {
         $graph = Dropdown::getDropdownName("glpi_states",$device["states_id"]);
      }
      return $graph;
   }

}
?>
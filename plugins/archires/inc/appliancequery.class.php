<?php
/*
 * @version $Id: appliancequery.class.php 166 2011-11-15 13:28:09Z remi $
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

class PluginArchiresApplianceQuery extends CommonDBTM {

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_archires']['title'][8];
      }
      return $LANG['plugin_archires']['title'][8];
   }


   function canCreate() {
      return plugin_archires_haveRight('archires', 'w');
   }


   function canView() {
      return plugin_archires_haveRight('archires', 'r');
   }


   function cleanDBonPurge() {

      $querytype = new PluginArchiresQueryType;
      $querytype->deleteByCriteria(array('plugin_archires_queries_id' => $this->fields['id']));
   }


  function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_archires']['title'][8];

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['plugin_archires']['search'][1];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();

      $tab[2]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[2]['field']     = 'name';
      $tab[2]['name']      = $LANG['plugin_archires']['search'][8];

      $tab[3]['table']     = 'glpi_networks';
      $tab[3]['field']     = 'name';
      $tab[3]['name']      = $LANG['plugin_archires']['search'][4];

      $tab[4]['table']     = 'glpi_states';
      $tab[4]['field']     = 'name';
      $tab[4]['name']      = $LANG['plugin_archires']['search'][5];

      $tab[5]['table']     = 'glpi_groups';
      $tab[5]['field']     = 'completename';
      $tab[5]['name']      = $LANG['common'][35];

      $tab[6]['table']     = 'glpi_vlans';
      $tab[6]['field']     = 'name';
      $tab[6]['name']      = $LANG['networking'][56];

      $tab[7]['table']     = 'glpi_plugin_archires_views';
      $tab[7]['field']     = 'name';
      $tab[7]['name']      = $LANG['plugin_archires']['setup'][20];

      $tab[30]['table']    = $this->getTable();
      $tab[30]['field']    = 'id';
      $tab[30]['name']     = $LANG['common'][2];

      $tab[80]['table']    = 'glpi_entities';
      $tab[80]['field']    = 'completename';
      $tab[80]['name']     = $LANG['entity'][0];

   return $tab;
   }


   function prepareInputForAdd($input) {
      global $LANG;

      if (!isset ($input["plugin_archires_views_id"]) || $input["plugin_archires_views_id"] == 0) {
         Session::addMessageAfterRedirect($LANG['plugin_archires'][4], false, ERROR);
         return array ();
      }
      return $input;
   }


   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab('PluginArchiresQueryType', $ong, $options);
      $this->addStandardTab('PluginArchiresView', $ong, $options);
      $this->addStandardTab('PluginArchiresPrototype', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      return $ong;
   }


   function showForm ($ID, $options=array()) {
      global $CFG_GLPI,$DB,$LANG;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_archires']['search'][1]." : </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      echo "<td>".$LANG['common'][35]." : </td><td>";
      Dropdown::show('Group', array('name'   => "groups_id",
                                    'value'  => $this->fields["groups_id"],
                                    'entity' => $this->fields["entities_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_archires']['search'][8]." : </td><td>";
      Dropdown::show('PluginAppliancesAppliance', array('name'   => "appliances_id",
                                                        'value'  => $this->fields["appliances_id"],
                                                        'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".$LANG['networking'][56]." : </td><td>";
      Dropdown::show('Vlan', array('name'  => "vlans_id",
                                   'value' => $this->fields["vlans_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_archires']['search'][4]." : </td><td>";
      Dropdown::show('Network', array('name'  => "networks_id",
                                      'value' => $this->fields["networks_id"]));
      echo "</td>";
      echo "<td>".$LANG['plugin_archires']['setup'][20]." : </td><td>";
      //View
      $PluginArchiresView = new PluginArchiresView();
      $PluginArchiresView->dropdownView($this,-1);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_archires']['search'][5]." : </td><td colspan='3'>";
      Dropdown::show('State', array('name' => "states_id"));
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   function Query ($ID,$PluginArchiresView,$for) {
      global $DB,$CFG_GLPI,$LANG;

      $this->getFromDB($ID);

      $types   = array();
      $devices = array();
      $ports   = array();

      if ($PluginArchiresView->fields["computer"]!=0) {
         $types[] = 'Computer';
      }
      if ($PluginArchiresView->fields["printer"]!=0) {
         $types[] = 'Printer';
      }
      if ($PluginArchiresView->fields["peripheral"]!=0) {
         $types[] = 'Peripheral';
      }
      if ($PluginArchiresView->fields["phone"]!=0) {
         $types[] = 'Phone';
      }
      if ($PluginArchiresView->fields["networking"]!=0) {
         $types[] = 'NetworkEquipment';
      }
      foreach ($types as $key => $val) {
         $fieldsnp = "`np`.`id`, `np`.`items_id`, `np`.`logical_number`, `np`.`networkinterfaces_id`,
                      `np`.`ip`,`np`.`netmask`, `np`.`name` AS namep";

         $itemtable = getTableForItemType($val);
         $query = "SELECT `$itemtable`.`id` AS idc, $fieldsnp , `$itemtable`.`name`,
                          `$itemtable`.`".getForeignKeyFieldForTable(getTableForItemType($val."Type"))."`
                              AS `type`,
                          `$itemtable`.`users_id`, `$itemtable`.`groups_id`, `$itemtable`.`contact`,
                          `$itemtable`.`states_id`, `$itemtable`.`entities_id`,
                          `$itemtable`.`locations_id`
                   FROM `glpi_networkports` np, `$itemtable` ";

         if ($this->fields["vlans_id"] > "0") {
            $query .= ", `glpi_networkports_vlans` nv";
         }

         $query .= ", `glpi_plugin_appliances_appliances_items` app
                   WHERE `np`.`itemtype` = '$val'
                         AND `np`.`items_id` = `$itemtable`.`id`
                         AND `app`.`items_id` = `$itemtable`.`id`
                         AND `$itemtable`.`is_deleted` = '0'
                         AND `$itemtable`.`is_template` = '0'".
                         getEntitiesRestrictRequest(" AND",$itemtable);

         if ($this->fields["vlans_id"] > "0") {
            $query .= " AND `nv`.`networkports_id` = `np`.`id`
                        AND `vlans_id` = '".$this->fields["vlans_id"]."'";
         }
         if ($this->fields["networks_id"] > "0" && $val != 'Phone' && $val != 'Peripheral') {
            $query .= " AND `$itemtable`.`networks_id` = '".$this->fields["networks_id"]."'";
         }
         if ($this->fields["states_id"] > "0") {
            $query .= " AND `$itemtable`.`states_id` = '".$this->fields["states_id"]."'";
         }
         if ($this->fields["groups_id"] > "0") {
            $query .= " AND `$itemtable`.`groups_id` = '".$this->fields["groups_id"]."'";
         }

         $query .= " AND `app`.`plugin_appliances_appliances_id` = '" . $this->fields["appliances_id"] . "'
                     AND `app`.`itemtype` = '$val' ";

         $PluginArchiresQueryType = new PluginArchiresQueryType();
         $query .= $PluginArchiresQueryType->queryTypeCheck($this->getType(), $ID, $val);

         $query .= "ORDER BY `np`.`ip` ASC ";

         if ($result = $DB->query($query)) {
            while ($data = $DB->fetch_array($result)) {

               if ($PluginArchiresView->fields["display_state"]!=0) {
                  $devices[$val][$data["items_id"]]["states_id"] = $data["states_id"];
               }
               $devices[$val][$data["items_id"]]["type"]         = $data["type"];
               $devices[$val][$data["items_id"]]["name"]         = $data["name"];
               $devices[$val][$data["items_id"]]["users_id"]     = $data["users_id"];
               $devices[$val][$data["items_id"]]["groups_id"]    = $data["groups_id"];
               $devices[$val][$data["items_id"]]["contact"]      = $data["contact"];
               $devices[$val][$data["items_id"]]["entity"]       = $data["entities_id"];
               $devices[$val][$data["items_id"]]["locations_id"] = $data["locations_id"];

               if ($data["ip"]) {
                  if (!empty($devices[$val][$data["items_id"]]["ip"])) {
                     $devices[$val][$data["items_id"]]["ip"]  .= " - ";
                     $devices[$val][$data["items_id"]]["ip"]  .= $data["ip"];
                  } else {
                     $devices[$val][$data["items_id"]]["ip"]  = $data["ip"];
                  }
               }

               $ports[$data["id"]]["items_id"]             = $data["items_id"];
               $ports[$data["id"]]["logical_number"]       = $data["logical_number"];
               $ports[$data["id"]]["networkinterfaces_id"] = $data["networkinterfaces_id"];
               $ports[$data["id"]]["ip"]                   = $data["ip"];
               $ports[$data["id"]]["netmask"]              = $data["netmask"];
               $ports[$data["id"]]["namep"]                = $data["namep"];
               $ports[$data["id"]]["idp"]                  = $data["id"];
               $ports[$data["id"]]["itemtype"]             = $val;
            }
         }
      }
      if ($for) {
         return $devices;
      }
      return $ports;
   }

}
?>
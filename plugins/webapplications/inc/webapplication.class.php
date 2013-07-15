<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginWebapplicationsWebapplication extends CommonDBTM {

   public $dohistory=true;
   
   static $types = array('Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone',
                            'Printer', 'Software', 'Entity');

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_webapplications'][4];
      }
      return $LANG['plugin_webapplications'][8];
   }


   function canCreate() {
      return plugin_webapplications_haveRight('webapplications', 'w');
   }


   function canView() {
      return plugin_webapplications_haveRight('webapplications', 'r');
   }


   //clean if webapplications are deleted
   function cleanDBonPurge() {

      $temp = new PluginWebapplicationsWebapplication_Item();
      $temp->deleteByCriteria(array('plugin_webapplications_webapplications_id' => $this->fields['id']));
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Supplier') {
         PluginWebapplicationsWebapplication_Item::showPluginFromSupplier($item);
      }
      return true;
   }
   
   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_webapplications_webapplications',
                                  "`suppliers_id` = '".$item->getID()."'");
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_webapplications'][4];

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['plugin_webapplications'][0];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['massiveaction'] =  false;

      $tab[2]['table']  = 'glpi_plugin_webapplications_webapplicationtypes';
      $tab[2]['field']  = 'name';
      $tab[2]['name']   = $LANG['plugin_webapplications']['setup'][1];

      $tab[3]['table']     = $this->getTable();
      $tab[3]['field']     = 'address';
      $tab[3]['name']      = $LANG['plugin_webapplications'][1];
      $tab[3]['datatype']  = 'weblink';

      $tab[4]['table']  = 'glpi_plugin_webapplications_webapplicationservertypes';
      $tab[4]['field']  = 'name';
      $tab[4]['name']   = $LANG['plugin_webapplications']['setup'][11];

      $tab[5]['table']  = 'glpi_plugin_webapplications_webapplicationtechnics';
      $tab[5]['field']  = 'name';
      $tab[5]['name']   = $LANG['plugin_webapplications']['setup'][12];

      $tab[6]['table']  = 'glpi_locations';
      $tab[6]['field']  = 'completename';
      $tab[6]['name']   = $LANG['plugin_webapplications'][25];

      $tab[7]['table']         = 'glpi_suppliers';
      $tab[7]['field']         = 'name';
      $tab[7]['name']          = $LANG['plugin_webapplications']['setup'][14];
      $tab[7]['datatype']      = 'itemlink';
      $tab[7]['itemlink_type'] = 'Supplier';
      $tab[7]['forcegroupby']  = true;

      $tab[8]['table']  = $this->getTable();
      $tab[8]['field']  = 'version';
      $tab[8]['name']   = $LANG['plugin_webapplications'][12];

      $tab[9]['table']  = 'glpi_users';
      $tab[9]['field']  = 'name';
      $tab[9]['linkfield'] = 'users_id_tech';
      $tab[9]['name']   = $LANG['plugin_webapplications'][3];

      $tab[10]['table'] = 'glpi_groups';
      $tab[10]['field'] = 'name';
      $tab[10]['linkfield'] = 'groups_id_tech';
      $tab[10]['name']  = $LANG['common'][109];

      $tab[11]['table']    = $this->getTable();
      $tab[11]['field']    = 'backoffice';
      $tab[11]['name']     = $LANG['plugin_webapplications'][7];
      $tab[11]['datatype'] = 'weblink';

      $tab[13]['table']          = 'glpi_plugin_webapplications_webapplications_items';
      $tab[13]['field']          = 'items_id';
      $tab[13]['nosearch']       = true;
      $tab[13]['name']           = $LANG['plugin_webapplications']['setup'][15];
      $tab[13]['forcegroupby']   = true;
      $tab[13]['joinparams']    = array('jointype' => 'child');
      
      $tab[14]['table'] = 'glpi_manufacturers';
      $tab[14]['field'] = 'name';
      $tab[14]['name']  = $LANG['plugin_webapplications']['setup'][28];

      $tab[15]['table']    = $this->getTable();
      $tab[15]['field']    = 'is_recursive';
      $tab[15]['name']     = $LANG['entity'][9];
      $tab[15]['datatype'] = 'bool';

      $tab[16]['table']    = $this->getTable();
      $tab[16]['field']    = 'comment';
      $tab[16]['name']     = $LANG['plugin_webapplications'][2];
      $tab[16]['datatype'] = 'text';

      $tab[17]['table']          = $this->getTable();
      $tab[17]['field']          = 'date_mod';
      $tab[17]['name']           = $LANG['common'][26];
      $tab[17]['datatype']       = 'datetime';
      $tab[17]['massiveaction']  = false;

      $tab[18]['table']    = $this->getTable();
      $tab[18]['field']    = 'is_helpdesk_visible';
      $tab[18]['name']     = $LANG['software'][46];
      $tab[18]['datatype'] = 'bool';

      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = $LANG['common'][2];
      $tab[30]['massiveaction']  = false;

      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name']  = $LANG['entity'][0];

      return $tab;
   }


   //define header form
   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab('PluginWebapplicationsWebapplication_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }


   /**
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
   **/
   function getSelectLinkedItem () {

      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_webapplications_webapplications_items`
              WHERE `plugin_webapplications_webapplications_id`='" . $this->fields['id']."'";
   }


   function showForm($ID, $options=array()) {
      global $CFG_GLPI,$LANG;

      if (!$this->canView()) {
        return false;
      }

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
      //name of webapplications
      echo "<td>".$LANG['plugin_webapplications'][0]." : </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      //version of webapplications
      echo "<td>".$LANG['plugin_webapplications'][12]." : </td>";
      echo "<td>";
      Html::autocompletionTextField($this, "version", array('size' => "15"));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //type of webapplications
      echo "<td>".$LANG['plugin_webapplications']['setup'][1]." : </td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationType',
                     array('value'  => $this->fields["plugin_webapplications_webapplicationtypes_id"],
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      //server type of webapplications
      echo "<td>".$LANG['plugin_webapplications'][14].": </td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationServerType',
                     array('value' => $this->fields["plugin_webapplications_webapplicationservertypes_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //location of webapplications
      echo "<td>".$LANG['plugin_webapplications'][25].": </td>";
      echo "<td>";
      Dropdown::show('Location', array('value'  => $this->fields["locations_id"],
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      //language of webapplications
      echo "<td>".$LANG['plugin_webapplications'][13].": </td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationTechnic',
                     array('value' => $this->fields["plugin_webapplications_webapplicationtechnics_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //users
      echo "<td>".$LANG['plugin_webapplications'][3].": </td><td>";
      User::dropdown(array('name' => "users_id_tech",
                           'value'  => $this->fields["users_id_tech"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'interface'));
      echo "</td>";
      //supplier of webapplications
      echo "<td>".$LANG['plugin_webapplications']['setup'][14].": </td>";
      echo "<td>";
      Dropdown::show('Supplier', array('value'  => $this->fields["suppliers_id"],
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //groups
      echo "<td>".$LANG['common'][109].": </td><td>";
      Dropdown::show('Group', array('name' => "groups_id_tech",
                                    'value'  => $this->fields["groups_id_tech"],
                                    'entity' => $this->fields["entities_id"],
                                    'condition' => '`is_assign`'));
      echo "</td>";

      //manufacturer of webapplications
      echo "<td>".$LANG['plugin_webapplications']['setup'][28].": </td>";
      echo "<td>";
      Dropdown::show('Manufacturer', array('value'  => $this->fields["manufacturers_id"],
                                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //url of webapplications
      echo "<td>".$LANG['plugin_webapplications'][1].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this, "address", array('size' => "65"));
      echo "</td>";
      //is_helpdesk_visible
      echo "<td>" . $LANG['software'][46] . ":</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //backoffice of webapplications
      echo "<td>".$LANG['plugin_webapplications'][7].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this, "backoffice", array('size' => "65"));
      echo "</td>";

      echo "<td>".$LANG['common'][26].": </td>";
      echo "<td>";
      echo Html::convDateTime($this->fields["date_mod"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //comments of webapplications
      echo "<td class='top center' colspan='4'>".$LANG['plugin_webapplications'][2].":	</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td class='top center' colspan='4'><textarea cols='125' rows='3' name='comment' >".
            $this->fields["comment"]."</textarea>";
      echo "</tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


	//webapplications dropdown selection
   function dropdownWebApplications($myname,$entity_restrict='',$used=array()) {
      global $DB,$LANG,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `".$this->getTable()."`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND",$this->getTable(),'',$entity_restrict,true);
      if (count($used)) {
         $where .= " AND id NOT IN (0";
         foreach ($used as $ID)
            $where .= ",$ID";
         $where .= ")";
      }

      $query = "SELECT *
                FROM `glpi_plugin_webapplications_webapplicationtypes`
                WHERE `id` IN (SELECT DISTINCT `plugin_webapplications_webapplicationtypes_id`
                               FROM `".$this->getTable()."`
                               $where)
                GROUP BY `name`
                ORDER BY `name` ";
      $result = $DB->query($query);

      echo "<select name='_type' id='plugin_webapplications_webapplicationtypes_id'>\n";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
      while ($data=$DB->fetch_assoc($result)) {
         echo "<option value='".$data['id']."'>".$data['name']."</option>\n";
      }
      echo "</select>\n";

      $params = array('plugin_webapplications_webapplicationtypes_id' => '__VALUE__',
                      'entity_restrict'                               => $entity_restrict,
                      'rand'                                          => $rand,
                      'myname'                                        => $myname,
                      'used'                                          => $used);

      Ajax::updateItemOnSelectEvent("plugin_webapplications_webapplicationtypes_id",
                                    "show_$myname$rand",
                                    $CFG_GLPI["root_doc"].
                                       "/plugins/webapplications/ajax/dropdownTypeWebApplications.php",
                                    $params);

      echo "<span id='show_$myname$rand'>";
      $_POST["entity_restrict"]                                = $entity_restrict;
      $_POST["plugin_webapplications_webapplicationtypes_id"]  = 0;
      $_POST["myname"]                                         = $myname;
      $_POST["rand"]                                           = $rand;
      $_POST["used"]                                           = $used;
      include (GLPI_ROOT."/plugins/webapplications/ajax/dropdownTypeWebApplications.php");
      echo "</span>\n";

      return $rand;
   }


   /**
    * Show for PDF an webapplications
    *
    * @param $pdf object for the output
    * @param $ID of the webapplications
   **/
   function show_PDF($pdf) {
      global $LANG, $DB;

      $pdf->setColumnsSize(50,50);
      $col1 = '<b>'.$LANG["common"][2].' '.$this->fields['id'].'</b>';
      if (isset($this->fields["date_mod"])) {
         $col2 = $LANG["common"][26].' : '.Html::convDateTime($this->fields["date_mod"]);
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_webapplications'][0].' :</i></b> '.$this->fields['name'],
         '<b><i>'.$LANG['plugin_webapplications']['setup'][1].' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationtypes',
                                                    $this->fields['plugin_webapplications_webapplicationtypes_id'])));
      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_webapplications'][3].' :</i></b> '.getUserName($this->fields['users_id_tech']),
         '<b><i>'.$LANG["common"][109].' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                               $this->fields['groups_id_tech'])));
      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_webapplications'][25].' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_locations', $this->fields['locations_id'])),
         '<b><i>'.$LANG['plugin_webapplications'][14].' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationservertypes',
                                                    $this->fields["plugin_webapplications_webapplicationservertypes_id"])));
      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_webapplications'][13].' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationtechnics',
                                                    $this->fields['plugin_webapplications_webapplicationtechnics_id'])),
         '<b><i>'.$LANG['plugin_webapplications'][12].' :</i></b> '.$this->fields['version']);

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_webapplications']['setup'][14].' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_suppliers', $this->fields['suppliers_id'])),
         '<b><i>'.$LANG['plugin_webapplications']['setup'][28].' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_manufacturers',
                                                    $this->fields["manufacturers_id"])));

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_webapplications'][0].' :</i></b> '.$this->fields['address'], '');

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.$LANG['plugin_webapplications'][2].' :</i></b>', $this->fields['comment']);

      $pdf->displaySpace();
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
}

?>
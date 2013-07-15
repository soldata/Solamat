<?php
/*
 * @version $Id: cost.class.php 480 2012-11-09 tynet $
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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginResourcesCost extends CommonDropdown {

   static function getTypeName($nb=0) {
      global $LANG;

      return $LANG['plugin_resources'][88];
   }

   function canCreate() {
      if (Session::haveRight('entity_dropdown','w')
         && plugin_resources_haveRight('dropdown_public', 'w')){
         return true;
      }
      return false;
   }

   function canView() {
      if (plugin_resources_haveRight('dropdown_public', 'r')){
         return true;
      }
      return false;
   }

   /**
    * allow to control data before adding in bdd
    *
    * @param datas $input
    * @return array|datas|the
    */
   function prepareInputForAdd($input) {
      global $LANG;

      if (!isset ($input["plugin_resources_professions_id"])
         || $input["plugin_resources_professions_id"] == '0') {
         Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][30], false, ERROR);
         return array ();
      }

      return $input;
   }

   /**
    * allow to control data before updating in bdd
    *
    * @param datas $input
    * @return array|datas|the
    */
   function prepareInputForUpdate($input) {
      global $LANG;

      if (!isset ($input["plugin_resources_professions_id"])
         || $input["plugin_resources_professions_id"] == '0') {
         Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][30], false, ERROR);
         return array ();
      }

      return $input;
   }

   function getAdditionalFields() {
      global $LANG;

      return array(array('name' => 'plugin_resources_professions_id',
                        'label' => $LANG['plugin_resources'][80],
                        'type'  => 'dropdownValue',
                        'list'  => true),
                  array('name'  => 'plugin_resources_ranks_id',
                        'label' => $LANG['plugin_resources'][77],
                        'type'  => 'dropdownValue',
                        'list'  => true),
                  array('name'  => 'begin_date',
                        'label' => $LANG['plugin_resources'][34],
                        'type'  => 'date',
                        'list'  => false),
                  array('name'  => 'end_date',
                        'label' => $LANG['plugin_resources'][35],
                        'type'  => 'date',
                        'list'  => false),
                  array('name'  => 'cost',
                        'label' => $LANG['plugin_resources'][88],
                        'type'  => 'decimal',
                        'list'  => false),
      );
   }

   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();

      $tab[14]['table']         = 'glpi_plugin_resources_professions';
      $tab[14]['field']         = 'name';
      $tab[14]['name']          = $LANG['plugin_resources'][80];

      $tab[15]['table']         = 'glpi_plugin_resources_ranks';
      $tab[15]['field']         = 'name';
      $tab[15]['name']          = $LANG['plugin_resources'][77];

      $tab[17]['table']         = $this->getTable();
      $tab[17]['field']         = 'begin_date';
      $tab[17]['name']          = $LANG['plugin_resources'][34];
      $tab[17]['datatype']      = 'date';

      $tab[18]['table']         = $this->getTable();
      $tab[18]['field']         = 'end_date';
      $tab[18]['name']          = $LANG['plugin_resources'][35];
      $tab[18]['datatype']      = 'date';

      $tab[19]['table']         = $this->getTable();
      $tab[19]['field']         = 'cost';
      $tab[19]['name']          = $LANG['plugin_resources'][88];
      $tab[19]['datatype']      = 'decimal';

      return $tab;
   }


   /**
    * Display the cost's form
    *
    * @param $ID
    * @param array $options
    * @return bool
    */
   function showForm($ID, $options=array("")) {
      global $LANG, $CFG_GLPI;

      //validation des droits
      if (!$this->isNewID($ID)) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      $this->showTabs($options);
      $this->showFormHeader($options);

      $fields = $this->getAdditionalFields();
      $nb = count($fields);

      echo "<tr class='tab_bg_1'><td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";

      echo "<td rowspan='".($nb+1)."'>";
      echo $LANG['common'][25]."&nbsp;:</td>";
      echo "<td rowspan='".($nb+1)."'>
            <textarea cols='45' rows='".($nb+2)."' name='comment' >".$this->fields["comment"];
      echo "</textarea></td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][80]."</td>";
      echo "<td>";
      $params = array('name' => 'plugin_resources_professions_id',
                    'value' => $this->fields['plugin_resources_professions_id'],
                    'entity' => $this->fields["entities_id"],
                    'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownRank.php",
                    'span' => 'span_rank',
                     'sort' => false
                  );
      PluginResourcesResource::showGenericDropdown('PluginResourcesProfession',$params);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][77]."</td><td>";
      echo "<span id='span_rank' name='span_rank'>";
      if ($this->fields["plugin_resources_ranks_id"]>0) {
         echo Dropdown::getDropdownName('glpi_plugin_resources_ranks',
            $this->fields["plugin_resources_ranks_id"]);
      } else {
         echo $LANG['common'][49];
      }
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][34]."</td>";
      echo "<td>";
      Html::showDateFormItem("begin_date",$this->fields["begin_date"],true,true);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][35]."</td>";
      echo "<td>";
      Html::showDateFormItem("end_date",$this->fields["end_date"],true,true);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][88]."</td>";
      echo "<td>";
      echo "<input type='text' name='cost' value='".Html::formatNumber($this->fields["cost"], true).
         "' size='14'></td></tr>";

      if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
         $options['candel'] = false;
      }
      $this->showFormButtons($options);
      $this->addDivForTabs();
      return true;

   }

   /**
    * During rank or profession transfer
    *
    * @static
    * @param $ID
    * @param $entity
    * @return ID|int|the
    */
   static function transfer($ID, $entity) {
      global $DB;

      if ($ID>0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `glpi_plugin_resources_costs`
                   WHERE `id` = '$ID'";

         if ($result=$DB->query($query)) {
            if ($DB->numrows($result)) {
               $data = $DB->fetch_assoc($result);
               $data = Toolbox::addslashes_deep($data);
               $input['name'] = $data['name'];
               $input['entities_id']  = $entity;
               $temp = new self();
               $newID    = $temp->getID($input);

               if ($newID<0) {
                  $newID = $temp->import($input);
               }

               return $newID;
            }
         }
      }
      return 0;
   }
}

?>
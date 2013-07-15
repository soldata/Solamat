<?php
/*
 * @version $Id: budget.class.php 480 2012-11-09 tynet $
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

class PluginResourcesBudget extends CommonDBTM {

   // From CommonDBTM
	public $dohistory=true;
   
   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_resources']['title'][8];
      }
      return $LANG['plugin_resources']['title'][9];
   }
   
   function canCreate() {
      return plugin_resources_haveRight('budget', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('budget', 'r');
   }

   /**
   * Display Tab for each budget
   **/
   function defineTabs($options=array()) {

      $ong = array();

      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);

      return $ong;
   }

   /**
   * Actions done when a budget is deleted from the database
   *
   * @return nothing
   **/
	function cleanDBonPurge() {

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

   /**
    * allow search management
    */
   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['plugin_resources']['title'][9];

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['plugin_resources']['title'][9]." - ".$LANG['plugin_resources'][8];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['massiveaction'] = false;

      $tab[2]['table']         = $this->getTable();
      $tab[2]['field']         = 'id';
      $tab[2]['name']          = $LANG['common'][2];
      $tab[2]['massiveaction'] = false;

      $tab[3]['table']='glpi_plugin_resources_ranks';
      $tab[3]['field']='name';
      $tab[3]['name']=$LANG['plugin_resources'][77];
      $tab[3]['massiveaction'] = false;

      $tab[4]['table']='glpi_plugin_resources_professions';
      $tab[4]['field']='name';
      $tab[4]['name']=$LANG['plugin_resources'][80];
      $tab[4]['massiveaction'] = false;

      $tab[5]['table']='glpi_plugin_resources_budgettypes';
      $tab[5]['field']='name';
      $tab[5]['name']=$LANG['plugin_resources'][84];

      $tab[6]['table']=$this->getTable();
      $tab[6]['field']='begin_date';
      $tab[6]['name']=$LANG['plugin_resources'][34];
      $tab[6]['datatype']='date';

      $tab[7]['table']=$this->getTable();
      $tab[7]['field']='end_date';
      $tab[7]['name']=$LANG['plugin_resources'][35];
      $tab[7]['datatype']='date';

      $tab[8]['table']=$this->getTable();
      $tab[8]['field']='volume';
      $tab[8]['name']=$LANG['plugin_resources'][85];

      $tab[9]['table']='glpi_plugin_resources_budgetvolumes';
      $tab[9]['field']='name';
      $tab[9]['name']=$LANG['plugin_resources'][87];

      $tab[10]['table']=$this->getTable();
      $tab[10]['field']='date_mod';
      $tab[10]['name']=$LANG['common'][26];
      $tab[10]['datatype']='datetime';
      $tab[10]['massiveaction'] = false;

      $tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['name']=$LANG['entity'][0];

      return $tab;
   }

   /**
    * Display the budget form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    *@return boolean item found
    **/
   function showForm($ID, $options=array("")) {
      global $LANG, $CFG_GLPI;

      //validation des droits
      if (!$this->canView()) return false;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][8]."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name",array('value' => $this->fields["name"]));
      echo "</td>";

      echo "<td>".$LANG['plugin_resources'][84]."</td>";
      echo "<td>";
      Dropdown::show('PluginResourcesBudgetType',
         array('value'  => $this->fields["plugin_resources_budgettypes_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][80]."</td>";
      echo "<td>";
      $params = array('name' => 'plugin_resources_professions_id',
                    'value' => $this->fields['plugin_resources_professions_id'],
                    'entityt' => $this->fields["entities_id"],
                    'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownRank.php",
                    'span' => 'span_rank',
                    'sort' => true
                  );
      PluginResourcesResource::showGenericDropdown('PluginResourcesProfession',$params);

      echo "</td>";
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
      echo "<td>".$LANG['plugin_resources'][85]."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, 'volume');
      echo "</td><td>".$LANG['plugin_resources'][87]."</td><td>";
      Dropdown::show('PluginResourcesBudgetVolume',
         array('value' => $this->fields["plugin_resources_budgetvolumes_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_resources'][34]."</td>";
      echo "<td>";
      Html::showDateFormItem("begin_date",$this->fields["begin_date"],true,true);
      echo "</td>";
      echo "<td>".$LANG['plugin_resources'][35]."</td>";
      echo "<td>";
      Html::showDateFormItem("end_date",$this->fields["end_date"],true,true);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='6'>";
      $datestring = $LANG['common'][26].": ";
      $date = Html::convDateTime($this->fields["date_mod"]);
      echo $datestring.$date."</td>";
      echo "</tr>";


      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $options['candel'] = false;
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }
}

?>
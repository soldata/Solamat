<?php
/*
 * @version $Id: rulecontracttype.class.php 480 2012-11-09 tynet $
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


/**
* Rule class store all informations about a GLPI rule :
*   - description
*   - criterias
*   - actions
*
**/
class PluginResourcesRuleContracttype extends Rule {

   // From Rule
   public $right='entity_rule_ticket';
   public $can_sort=true;


   function getTitle() {
      global $LANG;

      return $LANG['plugin_resources']['title'][0]." - ".$LANG['plugin_resources'][40];
   }
   
   
   function canCreate() {
      return plugin_resources_haveRight('resources', 'w');
   }


   function canView() {
      return plugin_resources_haveRight('resources', 'w');
   }
   
   function maybeRecursive() {
      return true;
   }


   function isEntityAssign() {
      return true;
   }


   function canUnrecurs() {
      return true;
   }
   
   
   function maxCriteriasCount() {
      return 1;
   }
   
   
   function maxActionsCount() {
      return count($this->getActions());
   }
   
   
   function addSpecificParamsForPreview($params) {

      if (!isset($params["entities_id"])) {
         $params["entities_id"] = $_SESSION["glpiactive_entity"];
      }
      return $params;
   }


   /**
    * Function used to display type specific criterias during rule's preview
    *
    * @param $fields fields values
   **/
   function showSpecificCriteriasForPreview($fields) {

      $entity_as_criteria = false;
      foreach ($this->criterias as $criteria) {
         if ($criteria->fields['criteria'] == 'entities_id') {
            $entity_as_criteria = true;
            break;
         }
      }
      if (!$entity_as_criteria) {
         echo "<input type='hidden' name='entities_id' value='".$_SESSION["glpiactive_entity"]."'>";
      }
   }
   
   
   function getCriterias() {
      global $LANG;
      
      $criterias = array();
      
      $criterias['plugin_resources_contracttypes_id']['name']  = $LANG['plugin_resources'][20];
      $criterias['plugin_resources_contracttypes_id']['type']  = 'dropdownContractType';
      $criterias['plugin_resources_contracttypes_id']['allow_condition'] = array(Rule::PATTERN_IS, Rule::PATTERN_IS_NOT);
      
      return $criterias;
   }
   
   function displayCriteriaSelectPattern($name, $ID, $condition, $value="", $test=false) {
      
      $PluginResourcesContractType = new PluginResourcesContractType();

      $crit    = $this->getCriteria($ID);
      $display = false;
      if (isset($crit['type'])
          && ($test||$condition==Rule::PATTERN_IS || $condition==Rule::PATTERN_IS_NOT)) {

         switch ($crit['type']) {
            case "dropdownContractType" :
               $PluginResourcesContractType->dropdownContractType($name);
               $display = true;
               break;
         }
      }
   }
   
   /**
    * Return a value associated with a pattern associated to a criteria to display it
    *
    * @param $ID the given criteria
    * @param $condition condition used
    * @param $pattern the pattern
   **/
   function getCriteriaDisplayPattern($ID, $condition, $pattern) {
      global $LANG;

      if (($condition==Rule::PATTERN_IS || $condition==Rule::PATTERN_IS_NOT)) {
         $crit = $this->getCriteria($ID);
         if (isset($crit['type'])) {

            switch ($crit['type']) {
               case "dropdownContractType" :
                  $PluginResourcesContractType = new PluginResourcesContractType();
                  return $PluginResourcesContractType->getContractTypeName($pattern);
            }
         }
      }
      return $pattern;
   }

   function getActions() {
      global $LANG;
      $actions = array();
      
      $actions['requiredfields_name']['name']  = $LANG['plugin_resources'][8];
      $actions['requiredfields_name']['type']  = "yesonly";
      $actions['requiredfields_name']['force_actions'] = array('assign');
      $actions['requiredfields_name']['type']  = "yesonly";

      $actions['requiredfields_firstname']['name']  = $LANG['plugin_resources'][18];
      $actions['requiredfields_firstname']['type']  = "yesonly";
      $actions['requiredfields_firstname']['force_actions'] = array('assign');
      
      $actions['requiredfields_locations_id']['name']  = $LANG['plugin_resources'][5];
      $actions['requiredfields_locations_id']['type']  = "yesonly";
      $actions['requiredfields_locations_id']['force_actions'] = array('assign');
      
      $actions['requiredfields_users_id']['name']  = $LANG['plugin_resources'][47];
      $actions['requiredfields_users_id']['type']  = "yesonly";
      $actions['requiredfields_users_id']['force_actions'] = array('assign');
      
      $actions['requiredfields_plugin_resources_departments_id']['name']  = $LANG['plugin_resources'][14];
      $actions['requiredfields_plugin_resources_departments_id']['type']  = "yesonly";
      $actions['requiredfields_plugin_resources_departments_id']['force_actions'] = array('assign');
      
      $actions['requiredfields_date_begin']['name']  = $LANG['plugin_resources'][11];
      $actions['requiredfields_date_begin']['type']  = "yesonly";
      $actions['requiredfields_date_begin']['force_actions'] = array('assign');
      
      $actions['requiredfields_date_end']['name']  = $LANG['plugin_resources'][13];
      $actions['requiredfields_date_end']['type']  = "yesonly";
      $actions['requiredfields_date_end']['force_actions'] = array('assign');

      $actions['requiredfields_quota']['name']  = $LANG['plugin_resources'][4];
      $actions['requiredfields_quota']['type']  = "yesonly";
      $actions['requiredfields_quota']['force_actions'] = array('assign');

      if (plugin_resources_haveRight('dropdown_public', 'w')){

         $actions['requiredfields_plugin_resources_resourcesituations_id']['name']  = $LANG['plugin_resources'][75];
         $actions['requiredfields_plugin_resources_resourcesituations_id']['type']  = "yesonly";
         $actions['requiredfields_plugin_resources_resourcesituations_id']['force_actions'] = array('assign');

         $actions['requiredfields_plugin_resources_ranks_id']['name']  = $LANG['plugin_resources'][77];
         $actions['requiredfields_plugin_resources_ranks_id']['type']  = "yesonly";
         $actions['requiredfields_plugin_resources_ranks_id']['force_actions'] = array('assign');
      }

      return $actions;
   }
}

?>
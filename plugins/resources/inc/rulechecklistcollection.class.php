<?php
/*
 * @version $Id: rulechecklistcollection.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesRuleChecklistCollection extends RuleCollection {

   // From RuleCollection
   //public $use_output_rule_process_as_next_input=true;
   public $right='entity_rule_ticket';
   public $menu_option='checklists';

   function getTitle() {
      global $LANG;

      return $LANG['plugin_resources']['checklists'][0];
   }
   
   function __construct($entity=0) {
      $this->entity = $entity;
   }
   
   function showInheritedTab() {
      return plugin_resources_haveRight('resources', 'w') && ($this->entity);
   }

   function showChildrensTab() {
      return plugin_resources_haveRight('resources', 'w') && (count($_SESSION['glpiactiveentities']) > 1);
   }
   
   /**
    * Process all the rules collection
    *
    * @param input the input data used to check criterias
    * @param output the initial ouput array used to be manipulate by actions
    * @param params parameters for all internal functions
    *
    * @return the output array updated by actions
   **/
   function processAllRules($input=array() ,$output=array(), $params=array()) {

      // Get Collection datas
      $this->getCollectionDatas(1,1);
      $input = $this->prepareInputDataForProcess($input, $params);
      $output["_no_rule_matches"] = true;
      $checklists = array();

      if (count($this->RuleList->list)) {
         foreach ($this->RuleList->list as $rule) {
            //If the rule is active, process it

            if ($rule->fields["is_active"]) {
               $output["_rule_process"] = false;
               $rule->process($input, $output, $params);
               
               if ($output["_rule_process"]==1)
                  $checklists[]=$output["checklists_id"];
            }

            if ($this->use_output_rule_process_as_next_input) {
               $input = $output;
            }
         }
      }

      return $checklists;
   }
   
   function showTestResults($rule, $output, $global_result) {
      global $LANG;

      $actions = $rule->getActions();
      /*echo "<table class='tab_cadrehov'>";
      echo "<tr><th colspan='2'>" . $LANG['rulesengine'][81] . "</th></tr>\n";
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='center'>".$LANG['rulesengine'][41]."&nbsp;:&nbsp;<strong> ".
             Dropdown::getYesNo($global_result)."</strong></td>";

      $output = $this->preProcessPreviewResults($output);

      foreach ($output as $criteria => $value) {
         if (isset($actions[$criteria])) {
            echo "<tr class='tab_bg_2'>";
            echo "<td>".$actions[$criteria]["name"]."</td>";
            echo "<td>".$rule->getActionValue($criteria, $value)."</td>";
            echo "</tr>\n";
         }
      }
      echo "</tr></table>\n";*/
   }
}

?>
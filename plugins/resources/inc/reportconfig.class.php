<?php
/*
 * @version $Id: reportconfig.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesReportConfig extends CommonDBTM {
   
	static function getTypeName() {
      global $LANG;

      return $LANG['plugin_resources']['setup'][2];
   }
   
   function canCreate() {
      return plugin_resources_haveRight('resources', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('resources', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='PluginResourcesResource' && $this->canView()) {
            return $LANG['plugin_resources'][29];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='PluginResourcesResource') {
         $ID = $item->getField('id');
         self::showReports($ID,$withtemplate);
         if ($item->can($ID,'w') && !self::checkIfReportsExist($ID)) {
            $self = new self();
            $self->showForm("", array('plugin_resources_resources_id' => $ID,
                                      'target' => $CFG_GLPI['root_doc']."/plugins/resources/front/reportconfig.form.php"));
         }
         if ($item->can($ID,'w') && self::checkIfReportsExist($ID) && !$withtemplate) { 
            PluginResourcesResource::showReportForm(array('id' => $ID,
                                      'target' => $CFG_GLPI['root_doc']."/plugins/resources/front/resource.form.php"));
         }
      }
      return true;
   }

   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_resources_resources_id']) || $input['plugin_resources_resources_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   static function checkIfReportsExist($ID) {
      
      $restrict = "`plugin_resources_resources_id` = '".$ID."'";

      $reports = getAllDatasFromTable("glpi_plugin_resources_reportconfigs",$restrict);

      if (!empty($reports)) {
         foreach ($reports as $report) {
            return $report["id"];
         }
      } else {
         return false;
      }
   }
   
    function getFromDBByResource($plugin_resources_resources_id) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."`
					WHERE `plugin_resources_resources_id` = '" . $plugin_resources_resources_id . "' ";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
   
   function showForm ($ID, $options=array()) {
		global $CFG_GLPI, $LANG;
	
		if (!$this->canview()) return false;
      
      $plugin_resources_resources_id = -1;
      if (isset($options['plugin_resources_resources_id'])) {
         $plugin_resources_resources_id = $options['plugin_resources_resources_id'];
      }
      
      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         $resource = new PluginResourcesResource();
         $resource->getFromDB($plugin_resources_resources_id);
         // Create item
         $input=array('plugin_resources_resources_id'=>$plugin_resources_resources_id);
         $this->check(-1,'w',$input);
      }
      
      $options["colspan"] = 1;
      //$this->showTabs($options);
      $this->showFormHeader($options);
      
      echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo $LANG['common'][25];
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='100' rows='6' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo $LANG['plugin_resources']['mailing'][30];
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='100' rows='6' name='information' >".$this->fields["information"]."</textarea>";
      echo "</td></tr>";
      
      $options['candel'] = false;
      $this->showFormButtons($options);

      return true;
	}
	
	static function showReports($ID,$withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;
    
      $rand=mt_rand();
      $resource = new PluginResourcesResource();
      $resource->getFromDB($ID);
      $canedit = $resource->can($ID,'w');
      
      Session::initNavigateListItems("PluginResourcesReportConfig",$LANG['plugin_resources']['title'][1] ." = ". $resource->fields["name"]);
      
      $query = "SELECT `glpi_plugin_resources_reportconfigs`.`id`,
               `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id`,
                `glpi_plugin_resources_reportconfigs`.`information`, 
                `glpi_plugin_resources_reportconfigs`.`comment`
                 FROM `glpi_plugin_resources_reportconfigs` ";
      $query.= " LEFT JOIN `glpi_plugin_resources_resources` ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id`)";
      $query.= " WHERE `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id` = '$ID'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;
      $row_num=1;
      if ($number !="0") {
         if ($withtemplate < 2) 
            echo "<form method='post' name='form_reports$rand' id='form_reports$rand' action=\"./reportconfig.form.php\">";
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='5'>".$LANG['plugin_resources']['setup'][2].":</th></tr>";
         $sel="";
         if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";

         echo "<tr>";
         if (plugin_resources_haveRight('resources', 'w') && $canedit && $withtemplate < 2)
            echo "<th>&nbsp;</th>";
         echo "<th>".$LANG['common'][25]."</th>";
         echo "<th>".$LANG['plugin_resources']['mailing'][30]."</th>";
         if ($withtemplate < 2)
            echo "<th>&nbsp;</th>";
         echo "</tr>";

         while ($data=$DB->fetch_array($result)) {
            
            Session::addToNavigateListItems("PluginResourcesReportConfig",$data['id']);
            
            $i++;
            $row_num++;
            echo "<tr class='tab_bg_1 center'>";
            
            if (plugin_resources_haveRight('resources', 'w') && $canedit && $withtemplate < 2) {
               echo "<td width='10'>";
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
               echo "</td>";
            }
            
            echo "<td class='left'>".nl2br($data["comment"])."</td>";
            echo "<td class='left'>".nl2br($data["information"])."</td>";
            if ($withtemplate < 2) {
               echo "<td class='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/reportconfig.form.php?id=".$data["id"]."&amp;plugin_resources_resources_id=".$data["plugin_resources_resources_id"]."'>";
               echo $LANG['buttons'][14];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
                  echo "</a></td>";
            }
            echo "</tr>";

         }
      
         echo "</table></div>";
      
         if ($number && $canedit && $withtemplate < 2) {
            if (plugin_resources_haveRight('resources', 'w')) {
               Html::openArrowMassives("form_reports$rand", true);
               Html::closeArrowMassives(array('delete_report' => $LANG['buttons'][6]));
            }
         }
         if ($withtemplate < 2) {
            Html::closeForm();
         }
      }
   }
}

?>
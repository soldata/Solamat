<?php
/*
 * @version $Id: employee.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesEmployee extends CommonDBTM {
	
	/**
   * Clean object veryfing criteria (when a relation is deleted)
   *
   * @param $crit array of criteria (should be an index)
   */
   public function clean ($crit) {
      global $DB;
      
      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }
   
   function canCreate() {
      return plugin_resources_haveRight('employee', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('employee', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='PluginResourcesResource' && $this->canView()) {
            return $LANG['plugin_resources'][61];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='PluginResourcesResource') {
         $ID = $item->getField('id');
         $self = new self();
         $self->showForm($ID,0,$withtemplate);
      }
      return true;
   }
   
   function getFromDBbyResources($plugin_resources_resources_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `plugin_resources_resources_id` = '$plugin_resources_resources_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
         return false;
      }
      return false;
   }
   
   function prepareInputForAdd($input) {
      // Not attached to resource -> not added
      if (!isset($input['plugin_resources_resources_id']) || $input['plugin_resources_resources_id'] <= 0) {
         return false;
      }
      if ($this->getFromDBbyResources($input['plugin_resources_resources_id'])) {
         return false;
      }
      return $input;
   }
	
	function showForm ($plugin_resources_resources_id,$users_id,$withtemplate='') {
		global $CFG_GLPI, $LANG;
		
		if (!$this->canView()) return false;

		$employee_spotted=false;
		$resource=new PluginResourcesResource();
		
		$restrict = "`plugin_resources_resources_id` = '$plugin_resources_resources_id'";
      $employees = getAllDatasFromTable($this->getTable(),$restrict);
      
      $canedit = $resource->can($plugin_resources_resources_id, 'w');
      
      $ID = 0;
		if (!empty($employees)) {
			foreach ($employees as $employer)
				$ID=$employer["id"];
		}
		if (empty($ID)) {
         if($this->getEmpty()) $employee_spotted = true;
		} else {
			if($this->getfromDB($ID)) $employee_spotted = true;
		}
		if ($employee_spotted) {
		
			echo "<div align='center'>";
			if ($withtemplate<2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php\">";
			if (!empty($plugin_resources_resources_id)) {
				$resource->getFromDB($plugin_resources_resources_id);
				$entity=$resource->fields["entities_id"];
			} else {
				$entity=$_SESSION["glpiactive_entity"];
			}
			
			echo "<table class='tab_cadre_fixe'>";
			echo "<tr><th colspan='4'>".$LANG['plugin_resources'][61]." :</th></tr>";
			if (empty($plugin_resources_resources_id)) {
            echo "<tr class='tab_bg_1'><td colspan='4' class='center'>".$LANG['plugin_resources'][67];
            echo "</td></tr>";
			}
			echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
			
			echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id'>"; 

			echo $LANG['plugin_resources'][62]."</td>";
			echo "<td colspan='2'>";
			         
         $params = array('name' => 'plugin_resources_employers_id',
            'value' => $this->fields['plugin_resources_employers_id'],
            'entity' => $entity,
            'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownLocation.php",
            'span' => 'span_location'
         );
         PluginResourcesResource::showGenericDropdown('PluginResourcesEmployer',$params);
			echo "</td></tr>";

         $locationId = 0;
         if ($this->fields["plugin_resources_employers_id"]>0) {
            $employer = new PluginResourcesEmployer();
            $employer->getFromDB($this->fields["plugin_resources_employers_id"]);
            $locationId = $employer->fields["locations_id"];
         }

         echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
         echo $LANG['plugin_resources'][86];
         echo "</td><td>";
         echo "<span id='span_location' name='span_location'>";
         if ($locationId>0) {
            echo Dropdown::getDropdownName('glpi_locations', $locationId);
         } else {
            echo $LANG['job'][32];
         }
         echo "</span>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
			echo $LANG['plugin_resources'][63]."</td>";
			echo "<td colspan='2'>";
			Dropdown::show('PluginResourcesClient',
                     array('value'  => $this->fields["plugin_resources_clients_id"],
                           'entity' => $entity));
			echo "</td></tr>";
					
			echo "<tr>";
			echo "<td class='tab_bg_2 top' colspan='4'>";
			if ($withtemplate<2) {
				if (empty($ID)) {
					if ($this->canCreate() && $canedit) {
						echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$plugin_resources_resources_id."\">";
						if (!empty($plugin_resources_resources_id)) {
							echo "<div align='center'>";
							echo "<input type='submit' name='addemployee' value=\"".$LANG['buttons'][8]."\" class='submit'>";
							echo "</div>";
						} else {
                     echo "<div align='center'>";
                     $resource->dropdownTemplate("templates_id", $_SESSION["glpiactive_entity"]);
							echo "<input type='hidden' name='users_id' value='$users_id'>"; 
							echo "&nbsp;<input type='submit' name='addressourceandemployee' value=\"".$LANG['buttons'][8]."\" class='submit'>";
							echo "</div>";
                  }
					}
				} else {
		
					if ($this->canCreate() && $canedit) {
		
						echo "<input type='hidden' name='id' value=\"$ID\">";
						echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$this->fields["plugin_resources_resources_id"]."\">";
						echo "<div align='center'>";
						echo "<input type='submit' name='updateemployee' value=\"".$LANG['buttons'][7]."\" class='submit' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='deleteemployee' value=\"".$LANG['buttons'][6]."\" class='submit'>";
						echo "</div>";
						
					}
				}
			}
			echo "</td>";
			echo "</tr>";
			echo "</table>";
			if ($withtemplate<2) {
            Html::closeForm();
			}
			echo "</div>";
		}
	}
	
	function wizardThirdForm ($plugin_resources_resources_id) {
      global $LANG;
      
      if (!$this->canView()) return false;

		$employee_spotted=false;
		
		$resource=new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);
			
		$restrict = "`plugin_resources_resources_id` = '$plugin_resources_resources_id'";
      $employees = getAllDatasFromTable($this->getTable(),$restrict);
      
      $ID = 0;
		if (!empty($employees)) {
			foreach ($employees as $employer)
				$ID=$employer["id"];
		}
		if (empty($ID)) {
         if($this->getEmpty()) $employee_spotted = true;
		} else {
			if($this->getfromDB($ID)) $employee_spotted = true;
		}
      
      if ($employee_spotted && $plugin_resources_resources_id) {
      
         echo "<div align='center'>";

         echo "<form action='./wizard.form.php' method='post'>";
         echo "<table class='plugin_resources_wizard'>";
         echo "<tr>";
         echo "<td class='plugin_resources_wizard_left_area' valign='top'>";
         echo "</td>";

         echo "<td class='plugin_resources_wizard_right_area' valign='top'>";
         
         echo "<div class='plugin_resources_wizard_title'><p>";
         echo "<img class='plugin_resource_wizard_img' src='../pics/newresource.png' alt='newresource'/>&nbsp;";
         echo $LANG['plugin_resources']['wizard'][7]."</p></div>";
         
         echo "<div class='center'>";

         $entity=$resource->fields["entities_id"];

         echo "<table class='plugin_resources_wizard_table'>";
         
         echo "<tr class='plugin_resources_wizard_right_area'><td colspan='2'>";
         echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id'>";
         echo $LANG['plugin_resources'][62]."</td>";
         echo "<td colspan='2'>";
         Dropdown::show('PluginResourcesEmployer', array('name' => "plugin_resources_employers_id",
                                                         'value' => $this->fields["plugin_resources_employers_id"],
                                                         'entity' => $entity));	
         echo "</td></tr>";
         
         echo "<tr class='plugin_resources_wizard_explain'><td colspan='2'>";
         echo $LANG['plugin_resources'][63]."</td>";
         echo "<td colspan='2'>";
         Dropdown::show('PluginResourcesClient', array('name' => "plugin_resources_clients_id",
                                                      'value' => $this->fields["plugin_resources_clients_id"],
                                                      'entity' => $entity));
         echo "</td></tr>";
         
         echo "</table>";
         echo "</div></td>";
         echo "</tr>";	
      
         if($this->canCreate()) {
            echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
            echo "<div class='preview'>";
            echo "<input type='hidden' name='id' value=\"".$ID."\">";
            echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$plugin_resources_resources_id."\">";
            echo "<input type='hidden' name='withtemplate' value=\"0\">";
            echo "<input type='submit' name='undo_second_step' value='".$LANG['plugin_resources']['button'][2]."' class='submit' />";
            echo "</div>";
            echo "<div class='next'>";
            echo "<input type='submit' name='third_step' value='".$LANG['plugin_resources']['button'][1]."' class='submit' />";
            echo "</div>";
            echo "</td></tr>";
         }
         
         echo "</table>";
         Html::closeForm();

         echo "</div>";
      } 
	}
	
	function showFormHelpdesk ($plugin_resources_resources_id,$exist) {
		global $CFG_GLPI, $LANG;
		
		if (!$this->canView()) return false;

		$employee_spotted=false;
		
		$resource=new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);
			
		$restrict = "`plugin_resources_resources_id` = '$plugin_resources_resources_id'";
      $employees = getAllDatasFromTable($this->getTable(),$restrict);
      
      $ID = 0;
		if (!empty($employees)) {
			foreach ($employees as $employer)
				$ID=$employer["id"];
		}
		if (empty($ID)) {
         if($this->getEmpty()) $employee_spotted = true;
		} else {
			if($this->getfromDB($ID)) $employee_spotted = true;
		}
		if ($employee_spotted) {
		
			echo "<div align='center'><br>";
			if ($exist==0 || empty($ID))
				echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/employee.form.php\">";
			else
				echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php\">";
			
			$entity=$resource->fields["entities_id"];

			echo "<table class='tab_cadre_fixe'>";
			echo "<tr><th colspan='4'>".$LANG['plugin_resources'][61]." :</th></tr>";
			
			echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
			echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id'>";
			echo $LANG['plugin_resources'][62]."</td>";
			echo "<td colspan='2'>";
			Dropdown::show('PluginResourcesEmployer', array('name' => "plugin_resources_employers_id",
                                                         'value' => $this->fields["plugin_resources_employers_id"],
                                                         'entity' => $entity));	
			echo "</td></tr>";
			
			echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
			echo $LANG['plugin_resources'][63]."</td>";
			echo "<td colspan='2'>";
			Dropdown::show('PluginResourcesClient', array('name' => "plugin_resources_clients_id",
                                                      'value' => $this->fields["plugin_resources_clients_id"],
                                                      'entity' => $entity));
			echo "</td></tr>";

			if ($this->canCreate()) {
				if ($exist==0) {
					
					echo "<tr><td class='tab_bg_2 top' colspan='4'>";
					echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$plugin_resources_resources_id."\">";
					echo "<div align='center'><input type='submit' name='add_helpdesk_employee' value=\"".$LANG['plugin_resources']['helpdesk'][5]."\" class='submit'>";	
					echo "</td></tr>";
					
				} else if (empty($ID)) {
               
               echo "<tr><td class='tab_bg_2 top' colspan='4'>";
					echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$plugin_resources_resources_id."\">";
					echo "<div align='center'><input type='submit' name='add_helpdesk_employee' value=\"".$LANG['buttons'][8]."\" class='submit'>";
               echo "</td></tr>";
               
				} else {
					
					if ($resource->fields["is_leaving"]!=1) {
                  echo "<tr><td class='tab_bg_2 top' colspan='4'>";
                  echo "<input type='hidden' name='id' value=\"$ID\">";
                  echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$plugin_resources_resources_id."\">";
                  echo "<div align='center'><input type='submit' name='updateemployee' value=\"".$LANG['buttons'][7]."\" class='submit' >";
                  echo "</div>";
                  echo "</td></tr>";
               }
				}
			}
			
			echo "</table>";
			Html::closeForm();
			echo "</div>";
		}
	}
	
	static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginResourcesResource') {
         self::pdfForResource($pdf, $item);

      } else {
         return false;
      }
      return true;
   }
	/**
    * Show for PDF an resources : employee informations
    * 
    * @param $pdf object for the output
    * @param $appli PluginResourcesResource Class
    */
   static function pdfForResource(PluginPdfSimplePDF $pdf, PluginResourcesResource $appli) {
      global $DB,$LANG;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_resources_haveRight("resources","r")) {
         return false;
      }
      $query = "SELECT * 
               FROM `glpi_plugin_resources_employees` 
               WHERE `plugin_resources_resources_id` = '$ID'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $pdf->setColumnsSize(100);

      $pdf->displayTitle('<b>'.$LANG['plugin_resources'][61].'</b>');

      $pdf->setColumnsSize(33,33,34);
      $pdf->displayTitle('<b><i>'.
         $LANG['plugin_resources'][62],
         $LANG['plugin_resources'][63].'</i></b>'
         );
         
      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);						
      } else {
         for ($i=0 ; $i < $number ; $i++) {
            
            $employer=$DB->result($result, $i, "plugin_resources_employers_id");
            $client=$DB->result($result, $i, "plugin_resources_clients_id");
            
            $pdf->displayLine(
               Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_employers",$employer)),
               Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_clients",$client))
               );					
         }
      }
      
      $pdf->displaySpace();
   }
}

?>
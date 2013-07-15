<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
 
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginProjetTaskState extends CommonDropdown {

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_projet']['setup'][23];
   }
   
   function canCreate() {
     return plugin_projet_haveRight('projet', 'w');
   }

   function canView() {
      return plugin_projet_haveRight('projet', 'r');
   }
   
   function getAdditionalFields() {
      global $LANG;

      return  array(array('name'  => 'color',
                          'label' => $LANG['plugin_projet'][71],
                          'type'  => 'color',
                          'list'  => true),
                    array('name'  => 'for_dependency',
                          'label' => $LANG['plugin_projet'][66],
                          'type'  => 'bool',
                          'list'  => true),
                    array('name'  => 'for_planning',
                          'label' => $LANG['plugin_projet'][67],
                          'type'  => 'bool',
                          'list'  => true));
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();
      
      $tab[2303]['table']    = $this->getTable();
      $tab[2303]['field']    = 'color';
      $tab[2303]['name']     = $LANG['plugin_projet'][71];
      $tab[2303]['datatype'] = 'text';
      
      $tab[2304]['table']    = $this->getTable();
      $tab[2304]['field']    = 'for_dependency';
      $tab[2304]['name']     = $LANG['plugin_projet'][66];
      $tab[2304]['datatype'] = 'bool';
      
      $tab[2305]['table']    = $this->getTable();
      $tab[2305]['field']    = 'for_planning';
      $tab[2305]['name']     = $LANG['plugin_projet'][67];
      $tab[2305]['datatype'] = 'bool';
      
      return $tab;
   }
   
   function displaySpecificTypeField($ID, $field=array()) {
   
      switch ($field['type']) {
         case 'color' :
            echo "<input style=\"background-color:" . $this->fields['color'] . ";\" 
            type='text' name='color' size='7' value='".$this->fields['color']."'>";
            break;
      }
   }
   
   
   static function getStatusColor($ID) {
      
      $self = new self();
      if ($self->getFromDB($ID)) {
         if (!empty($self->fields['color'])) {
            return $self->fields['color'];
         }
      }
      return "#CCCCCC";
   }
   
   static function getStatusForPlanning() {
      global $DB;

      foreach ($DB->request('glpi_plugin_projet_taskstates', array('for_planning' => 1)) as $data) {
         return $data['id'];
      }
      return 0;
   }
   
   function post_addItem() {
      global $DB;

      if (isset($this->input["for_planning"]) && $this->input["for_planning"]) {
         $query = "UPDATE `".$this->getTable()."`
                   SET `for_planning` = '0'
                   WHERE `id` <> '".$this->fields['id']."'";
         $DB->query($query);
      }
   }


   function post_updateItem($history=1) {
      global $DB, $LANG;

      if (in_array('for_planning',$this->updates)) {

         if ($this->input["for_planning"]) {
            $query = "UPDATE `".$this->getTable()."`
                      SET `for_planning` = '0'
                      WHERE `id` <> '".$this->input['id']."'";
            $DB->query($query);

         } else {
            Session::addMessageAfterRedirect($LANG['setup'][313], true);
         }
      }
   }
}

?>
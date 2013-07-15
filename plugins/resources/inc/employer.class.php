<?php
/*
 * @version $Id: employer.class.php 480 2012-11-09 tynet $
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

class PluginResourcesEmployer extends CommonTreeDropdown {

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_resources'][62];
   }
   
   function canCreate() {
      return Session::haveRight('entity_dropdown','w');
   }

   function canView() {
      return plugin_resources_haveRight('resources', 'r');
   }

   function getAdditionalFields() {
      global $LANG;

      return array( array( 'name'  => $this->getForeignKeyField(),
                           'label' => $LANG['setup'][75],
                           'type'  => 'parent',
                           'list'  => false),
                     array('name'  => 'short_name',
                           'label' => $LANG['plugin_resources'][43],
                           'type'  => 'text',
                           'list'  => true),
                     array('name'  => 'locations_id',
                           'label' => $LANG['common'][15],
                           'type'  => 'dropdownValue',
                           'list'  => true));
   }

   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();

      $tab[14]['table']         = $this->getTable();
      $tab[14]['field']         = 'short_name';
      $tab[14]['name']          = $LANG['plugin_resources'][43];
      $tab[14]['datatype']      = 'text';

      $tab[16]['table']         = 'glpi_locations';
      $tab[16]['field']         = 'completename';
      $tab[16]['name']          = $LANG['plugin_resources'][5];

      return $tab;
   }


   static function transfer($ID, $entity) {
      global $DB;

      if ($ID>0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `glpi_plugin_resources_employers`
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
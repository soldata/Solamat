<?php
/*
 * @version $Id: profession.class.php 480 2012-11-09 tynet $
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

class PluginResourcesProfession extends CommonDropdown {

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_resources'][80];
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

   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  => 'code',
                         'label' => $LANG['plugin_resources'][30],
                         'type'  => 'text',
                         'list'  => true),
                  array('name'  => 'short_name',
                        'label' => $LANG['plugin_resources'][43],
                        'type'  => 'text',
                        'list'  => true),
                  array('name'  => 'plugin_resources_professionlines_id',
                        'label' => $LANG['plugin_resources'][81],
                        'type'  => 'dropdownValue',
                        'list'  => true),
                  array('name'  => 'plugin_resources_professioncategories_id',
                        'label' => $LANG['plugin_resources'][82],
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
                  array('name'  => 'is_active',
                        'label' => $LANG['common'][60],
                        'type'  => 'bool',
                        'list'  => true),
                  );
   }

   /**
    * During resource or employment transfer
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
                   FROM `glpi_plugin_resources_professions`
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

               //transfer of the linked line
               $line = PluginResourcesProfessionLine::transfer($temp->fields["plugin_resources_professionlines_id"], $entity);
               if ($line > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professionlines_id"] = $line;
                  $temp->update($values);
               }

               //transfer of the linked category
               $category = PluginResourcesProfessionCategory::transfer($temp->fields["plugin_resources_professioncategories_id"], $entity);
               if ($category > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professioncategories_id"] = $category;
                  $temp->update($values);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

   /**
    * When a profession is deleted -> deletion of the linked ranks
    *
    * @return nothing|void
    */
   function cleanDBonPurge(){

      $temp = new PluginResourcesRank();
      $temp->deleteByCriteria(array('plugin_resources_professions_id' => $this->fields['id']));

   }

   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();

      $tab[14]['table']         = $this->getTable();
      $tab[14]['field']         = 'code';
      $tab[14]['name']          = $LANG['plugin_resources'][30];
      $tab[14]['datatype']      = 'text';

      $tab[15]['table']         = $this->getTable();
      $tab[15]['field']         = 'short_name';
      $tab[15]['name']          = $LANG['plugin_resources'][43];
      $tab[15]['datatype']      = 'text';

      $tab[17]['table']         = 'glpi_plugin_resources_professionlines';
      $tab[17]['field']         = 'name';
      $tab[17]['name']          = $LANG['plugin_resources'][81];

      $tab[18]['table']         = 'glpi_plugin_resources_professioncategories';
      $tab[18]['field']         = 'name';
      $tab[18]['name']          = $LANG['plugin_resources'][82];

      $tab[19]['table']         = $this->getTable();
      $tab[19]['field']         = 'is_active';
      $tab[19]['name']          = $LANG['common'][60];
      $tab[19]['datatype']      = 'bool';

      $tab[20]['table']         = $this->getTable();
      $tab[20]['field']         = 'begin_date';
      $tab[20]['name']          = $LANG['plugin_resources'][34];
      $tab[20]['datatype']      = 'date';

      $tab[21]['table']         = $this->getTable();
      $tab[21]['field']         = 'end_date';
      $tab[21]['name']          = $LANG['plugin_resources'][35];
      $tab[21]['datatype']      = 'date';

      return $tab;
   }

   /**
    * is_active = 1 during a creation
    *
    * @return nothing|void
    */
   function post_getEmpty() {

      $this->fields['is_active'] = 1;
   }

}

?>
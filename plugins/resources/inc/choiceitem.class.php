<?php
/*
 * @version $Id: choiceitem.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesChoiceItem extends CommonTreeDropdown {

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_resources'][38];
   }
   
   function canCreate() {
      return Session::haveRight('entity_dropdown','w');
   }

   function canView() {
      return plugin_resources_haveRight('resources', 'r');
   }
   
   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  => $this->getForeignKeyField(),
                         'label' => $LANG['setup'][75],
                         'type'  => 'parent',
                         'list'  => false),
                     array('name'  => 'is_helpdesk_visible',
                         'label' => $LANG['tracking'][39],
                         'type'  => 'bool',
                         'list'  => true));
   }
   
   function getSearchOptions () {
      global $LANG;
      
      $tab = parent::getSearchOptions();
      
      $tab[11]['table'] = $this->getTable();
      $tab[11]['field'] = 'is_helpdesk_visible';
      $tab[11]['name']  = $LANG['tracking'][39];
      $tab[11]['datatype'] = 'bool';

      return $tab;
   }
}

?>
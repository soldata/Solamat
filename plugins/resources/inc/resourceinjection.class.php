<?php
/*
 * @version $Id: resourceinjection.class.php 480 2012-11-09 tsmr $
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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginResourcesResourceInjection extends PluginResourcesResource
   implements PluginDatainjectionInjectionInterface {

   function __construct() {
      //Needed for getSearchOptions !
      $this->table = getTableForItemType('PluginResourcesResource');
   }

   function isPrimaryType() {
      return true;
   }

   function connectedTo() {
      return array();
   }
   
   function getOptions($primary_type = '') {

      $tab = Search::getOptions(get_parent_class($this));

      //Specific to location
      $tab[12]['linkfield'] = 'locations_id';

      //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
      //Remove some options because some fields cannot be imported
      $notimportable = array(8,16,18,19,31,80);
      $options['ignore_fields'] = $notimportable;
      $options['displaytype'] = array("dropdown"       => array(3,11,12,17),
                                      "user"           => array(4,10,14),
                                      "multiline_text" => array(7),
                                      "date"           => array(5,6,9),
                                      "bool"           => array(13,15));

      $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

      return $tab;
   }

   /**
    * Standard method to delete an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    * @param fields fields to add into glpi
    * @param options options used during creation
    */
   function deleteObject($values=array(), $options=array()) {
      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }

   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    * @param values fields to add into glpi
    * @param options options used during creation
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    */
   function addOrUpdateObject($values=array(), $options=array()) {
      global $LANG;
      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

}

?>
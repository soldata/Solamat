<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderReference_Supplier extends CommonDBChild {
   
   public $itemtype  = 'PluginOrderReference';
   public $items_id  = 'plugin_order_references_id';
   public $dohistory = true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['reference'][5];
   }
   
   function canCreate() {
      return plugin_order_haveRight('reference', 'w');
   }

   function canView() {
      return plugin_order_haveRight('reference', 'r');
   }
   
   function getFromDBByReference($plugin_order_references_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `plugin_order_references_id` = '" . $plugin_order_references_id . "' ";
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
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['reference'][5];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'reference_code';
      $tab[1]['name'] = $LANG['plugin_order']['reference'][10];
      $tab[1]['datatype'] = 'text';

      $tab[2]['table']    = $this->getTable();
      $tab[2]['field']    = 'price_taxfree';
      $tab[2]['name']     = $LANG['plugin_order']['detail'][4];
      $tab[2]['datatype'] = 'decimal';

      $tab[3]['table'] = 'glpi_suppliers';
      $tab[3]['field'] = 'name';
      $tab[3]['name'] = $LANG['financial'][26];
      $tab[3]['datatype']='itemlink';
      $tab[3]['itemlink_type']='Supplier';
      $tab[3]['forcegroupby']=true;
      
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name']=$LANG['common'][2];

      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];
      
      return $tab;
   }
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_references_id']) || $input['plugin_order_references_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   function defineTabs($options=array()) {
      global $LANG;
      $ong = array();
      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);
      return $ong;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      if (get_class($item) == __CLASS__) {
         return array (1 => $LANG['title'][26]);
      } elseif (get_class($item) == 'PluginOrderReference') {
         return array(1 => $LANG['plugin_order'][4]);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $reference_supplier = new self();
      if ($item->getType()=='PluginOrderReference') {
         $reference_supplier->showReferenceManufacturers($item->getID());
         if ($item->can($item->getID(), 'w')) {
            $reference_supplier->showForm(0, array('plugin_order_references_id' => $item->getID()));
         }
      }
      
      return true;
   }
   
   function showForm ($ID, $options=array()) {
      global $LANG, $DB;
      
      if (!$this->canView()) {
         return false;
      }
      
      $plugin_order_references_id = -1;
      if (isset($options['plugin_order_references_id'])) {
         $plugin_order_references_id = $options['plugin_order_references_id'];
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input=array('plugin_order_references_id' => $options['plugin_order_references_id']);
         $this->check(-1,'w',$input);
      }
      
      if (strpos($_SERVER['PHP_SELF'], "reference_supplier")) {
         $this->showTabs($options);
      }
      $this->showFormHeader($options);
      
      $PluginOrderReference = new PluginOrderReference();
      $PluginOrderReference->getFromDB($plugin_order_references_id);
      echo "<input type='hidden' name='plugin_order_references_id' value='$plugin_order_references_id'>";
      echo "<input type='hidden' name='entities_id' value='".$PluginOrderReference->getEntityID()."'>";
      echo "<input type='hidden' name='is_recursive' value='".$PluginOrderReference->isRecursive()."'>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['financial'][26] . ": </td>";
      echo "<td>";

      if ($ID > 0) {
         $supplier = new Supplier();
         $supplier->getFromDB($this->fields['suppliers_id']);
         echo $supplier->getLink(Session::haveRight('supplier', 'r'));
      } else {
         $suppliers = array();
         $query = "SELECT `suppliers_id`
                     FROM `".$this->getTable()."`
                     WHERE `plugin_order_references_id` = '$plugin_order_references_id'";
         $result = $DB->query($query);
         while ($data = $DB->fetch_array($result))
            $suppliers[] = $data["suppliers_id"];

         Dropdown::show('Supplier',
                        array('name'   => 'suppliers_id',
                               'used'   => $suppliers,
                               'entity' => $PluginOrderReference->getEntityID()));
      }
      echo "</td>";

      echo "<td>" . $LANG['plugin_order']['reference'][10] . ": </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"reference_code");
      echo "</td></tr>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['plugin_order']['detail'][4] . ": </td>";
      echo "<td>";
      echo "<input type='text' name='price_taxfree' value=\"".
         Html::formatNumber($this->fields["price_taxfree"],true)."\" size='7'>";
      echo "</td>";
      
      echo "<td></td>";
      echo "<td></td>";
      
      echo "</tr>";
        
      $options['candel'] = false;
      $this->showFormButtons($options);
      
      if (strpos($_SERVER['PHP_SELF'], "reference_supplier")) {
         $this->addDivForTabs();
      }
      return true;
   }

   function showReferenceManufacturers($ID) {
      global $LANG, $DB, $CFG_GLPI;

      $ref = new PluginOrderReference();
      $ref->getFromDB($ID);
      
      $target = Toolbox::getItemTypeFormURL(__CLASS__);
      Session::initNavigateListItems($this->getType(),
                            $LANG['plugin_order']['reference'][1] ." = ". $ref->fields["name"]);

      $candelete = $ref->can($ID, 'w');
      $query     = "SELECT * FROM `".$this->getTable()."` WHERE `plugin_order_references_id` = '$ID' ";
      $query    .= getEntitiesRestrictRequest(" AND", $this->getTable(), "entities_id",
                                              $ref->fields['entities_id'],
                                              $ref->fields['is_recursive']);
      $result    = $DB->query($query);
      $rand      = mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_supplierref$rand' id='show_supplierref$rand' action=\"$target\">";
      echo "<input type='hidden' name='plugin_order_references_id' value='" . $ID . "'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='5'>".$LANG['plugin_order'][4]."</th></tr>";
      echo "<tr><th>&nbsp;</th>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
      echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {
         echo "<form method='post' name='show_ref_manu' action=\"$target\">";
         echo "<input type='hidden' name='plugin_order_references_id' value='" . $ID . "'>";

         while ($data = $DB->fetch_array($result)) {
            Session::addToNavigateListItems($this->getType(),$data['id']);
            echo "<input type='hidden' name='item[" . $data["id"] . "]' value='" . $ID . "'>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all') {
                  echo " checked ";
               }
               echo ">";
            }
            echo "</td>";
            
            $link=Toolbox::getItemTypeFormURL($this->getType());
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_references_id=".$ID."'>" .
               Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td>";
            echo $data["reference_code"];
            echo "</td>";
            echo "<td>";
            echo Html::formatNumber($data["price_taxfree"]);
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete) {
            echo "<div class='center'>";
            echo "<table width='900px' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td>";
            echo "<td class='center'>" .
                  "<a onclick= \"if ( markCheckboxes('show_supplierref$rand') ) " .
                     "return false;\" href='#'>".$LANG['buttons'][18]."</a></td>";

            echo "<td>/</td><td class='center'>" .
                  "<a onclick= \"if ( unMarkCheckboxes('show_supplierref$rand') ) " .
                     "return false;\" href='#'>".$LANG['buttons'][19]."</a>";
            echo "</td><td align='left' width='80%'>";
            echo "<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] .
                  "\" class='submit' >";
            echo "</td>";
            echo "</table>";
            echo "</div>";
         }
      } else {
         echo "</table>";
      }

      Html::closeForm();
      echo "</div>";
      
   }

   function getPriceByReferenceAndSupplier($plugin_order_references_id, $suppliers_id){
      global $DB;

      $query = "SELECT `price_taxfree`
                FROM `".$this->getTable()."`
                WHERE `plugin_order_references_id` = '$plugin_order_references_id'
                   AND `suppliers_id` = '$suppliers_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "price_taxfree");
      } else {
         return 0;
      }
   }
   
   function getReferenceCodeByReferenceAndSupplier($plugin_order_references_id, $suppliers_id){
      global $DB;

      $query = "SELECT `reference_code`
                FROM `".$this->getTable()."`
                WHERE `plugin_order_references_id` = '$plugin_order_references_id'
                   AND `suppliers_id` = '$suppliers_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "reference_code");
      } else {
         return 0;
      }
   }
   
  static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table) && !TableExists("glpi_plugin_order_references_manufacturers")) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references_suppliers` (
                     `id` int(11) NOT NULL auto_increment,
                     `entities_id` int(11) NOT NULL default '0',
                     `is_recursive` tinyint(1) NOT NULL default '0',
                     `plugin_order_references_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
                     `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                     `price_taxfree` decimal(20,4) NOT NULL DEFAULT '0.0000',
                     `reference_code` varchar(255) collate utf8_unicode_ci default NULL,
                     PRIMARY KEY  (`id`),
                     KEY `entities_id` (`entities_id`),
                     KEY `plugin_order_references_id` (`plugin_order_references_id`),
                     KEY `suppliers_id` (`suppliers_id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      } else {
         $migration->displayMessage("Upgrading $table");

         //1.1.0
         if (TableExists("glpi_plugin_order_references_manufacturers")) {
         $migration->addField("glpi_plugin_order_references_manufacturers", "reference_code",
                              "varchar(255) NOT NULL collate utf8_unicode_ci default ''");
         $migration->migrationOneTable("glpi_plugin_order_references_manufacturers");
         }

         //1.2.0
         $migration->renameTable("glpi_plugin_order_references_manufacturers", $table);
         $migration->addField($table, "is_recursive", "int(11) NOT NULL default '0'");
         $migration->addKey($table, "suppliers_id");
         $migration->addKey($table, "plugin_order_references_id");
         $migration->changeField($table, "ID", "id",
                                 "int(11) NOT NULL auto_increment");
         $migration->changeField($table, "FK_entities", "entities_id",
                                 "int(11) NOT NULL default '0'");
         $migration->changeField($table, "FK_reference", "plugin_order_references_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)'");
         $migration->changeField($table, "FK_enterprise", "suppliers_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'");
         $migration->changeField($table, "reference_code", "reference_code",
                                 "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->changeField($table, "price_taxfree", "price_taxfree",
                                 "decimal(20,4) NOT NULL DEFAULT '0.0000'");
         $migration->migrationOneTable($table);

         Plugin::migrateItemType(array(3152 => 'PluginOrderReference_Supplier'),
                                 array("glpi_bookmarks", "glpi_bookmarks_users",
                                       "glpi_displaypreferences", "glpi_documents_items",
                                       "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                                 array());

         //1.5.0
         $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_order_references` ";
         foreach ($DB->request($query) as $data) {
            $query = "UPDATE `glpi_plugin_order_references_suppliers`
                      SET `entities_id` = '".$data["entities_id"]."',`is_recursive` = '".$data["is_recursive"]."'
                      WHERE `plugin_order_references_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
   }
   
   static function uninstall() {
      global $DB;

      //Old table name
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_references_manufacturers`");
      
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".getTableForItemType(__CLASS__)."`");
   }

   static function showReferencesFromSupplier($ID){
      global $LANG, $DB, $CFG_GLPI;

      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }
      
      $query = "SELECT `gr`.`id`, `gr`.`manufacturers_id`, `gr`.`entities_id`, `gr`.`itemtype`,
                       `gr`.`name`, `grm`.`price_taxfree`, `grm`.`reference_code`
               FROM `glpi_plugin_order_references_suppliers` AS grm, `glpi_plugin_order_references` AS gr
               WHERE `grm`.`suppliers_id` = '$ID'
                  AND `grm`.`plugin_order_references_id` = `gr`.`id`"
               .getEntitiesRestrictRequest(" AND ", "gr", '', '', true);
      $query_limit = $query." LIMIT ".intval($start)."," . intval($_SESSION['glpilist_limit']);
      $result = $DB->query($query);
      $nb     = $DB->numrows($result);
      echo "<div class='center'>";

      if ($nb) {

         $result = $DB->query($query_limit);
         Html::printAjaxPager($LANG['plugin_order']['reference'][3], $start, $nb);
         
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['common'][5]."</th>";
         echo "<th>".$LANG['plugin_order']['reference'][1]."</th>";
         echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
         echo "<th>".$LANG['plugin_order']['reference'][1]."</th>";
         echo "<th>".$LANG['plugin_order']['detail'][4]."</th></tr>";
         
         
         while ($data = $DB->fetch_array($result)) {
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities", $data["entities_id"]);
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_manufacturers", $data["manufacturers_id"]);
            echo "</td>";

            echo "<td>";
            $PluginOrderReference = new PluginOrderReference();
            echo $PluginOrderReference->getReceptionReferenceLink($data);
            echo "</td>";
            
            echo "<td>";
            $item = new $data["itemtype"]();
            echo $item->getTypeName();
            echo "</td>";
            
            echo "<td>";
            echo $data['reference_code'];
            echo "</td>";
            
            echo "<td>";
            echo $data["price_taxfree"];
            echo "</td>";
            echo "</tr>";
         }
      }
      echo "</table>";
      echo "</div>";
   }
}

?>
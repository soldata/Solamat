<?php
/*
 * @version $Id: migration.class.php 20130 2013-02-04 16:55:15Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// class Central
class Migration {

   private $change    = array();
   private $version;
   private $deb;


   function __construct($ver) {
      global $LANG;
      
      $this->deb = time();
      $this->setVersion($ver);
   }

   function setVersion($ver) {
      global $LANG;
      // begin of global message
      echo "<div id='migration_message_$ver'>
            <p class='center'>".$LANG['rulesengine'][90]."</p></div>";

      $this->version = $ver;
   }


   /**
    * Additional message in global message
    *
    * @param $msg text to display
   **/

   function displayMessage ($msg) {
      global $LANG;

      $fin = time();
      $tps = Html::timestampToString($fin-$this->deb);
      echo "<script type='text/javascript'>document.getElementById('migration_message_".
             $this->version."').innerHTML=\"<p class='center'>$msg ($tps)</p>\";</script>\n";

      Html::glpi_flush();
   }


   /**
    * Display a title
    *
    * @param $title string
   **/
   function displayTitle($title) {

      echo "<h3>".htmlentities($title, ENT_COMPAT, "UTF-8")."</h3>";
   }


   /**
    * Display a Warning
    *
    * @param $msg string
    * @param $red boolean
   **/
   function displayWarning($msg, $red=false) {

      echo ($red ? "<div class='red'><p>" : "<p><span class='b'>") .
            htmlentities($msg, ENT_COMPAT, "UTF-8") . ($red ? "</p></div>" : "</span></p>");
   }


   /**
    * Define field's format
    *
    * @param $type : can be bool, string, integer, date, datatime, text, longtext, autoincrement
    * @param $default_value new field's default value, if a specific default value needs to be used
   **/
   private function fieldFormat($type, $default_value) {

      $format = '';
      switch ($type) {
         case 'bool' :
            $format = "TINYINT(1) NOT NULL";
            if (is_null($default_value)) {
               $format .= " DEFAULT '0'";
            } else if (in_array($default_value, array('0', '1'))) {
               $format .= " DEFAULT '$default_value'";
            } else {
               trigger_error("default_value must be 0 or 1", E_USER_ERROR);
            }
            break;

         case 'char' :
            $format = "CHAR(1)";
            if (is_null($default_value)) {
                $format .= " DEFAULT NULL";
            } else {
               $format .= " DEFAULT '$default_value'";
            }
            break;

         case 'string' :
            $format = "VARCHAR(255) COLLATE utf8_unicode_ci";
            if (is_null($default_value)) {
                $format .= " DEFAULT NULL";
            } else {
               $format .= " DEFAULT '$default_value'";
            }
            break;

         case 'integer' :
            $format = "INT(11) NOT NULL";
            if (is_null($default_value)) {
               $format .= " DEFAULT '0'";
            } else if (is_numeric($default_value)) {
               $format .= " DEFAULT '$default_value'";
            } else {
               trigger_error("default_value must be numeric", E_USER_ERROR);
            }
            break;

         case 'date' :
            $format = "DATE";
            if (is_null($default_value)) {
                $format.= " DEFAULT NULL";
            } else {
               $format.= " DEFAULT '$default_value'";
            }
            break;

         case 'datetime' :
            $format = "DATETIME";
            if (is_null($default_value)) {
                $format.= " DEFAULT NULL";
            } else {
               $format.= " DEFAULT '$default_value'";
            }
            break;

         case 'text' :
            $format = "TEXT COLLATE utf8_unicode_ci";
            if (is_null($default_value)) {
                $format.= " DEFAULT NULL";
            } else {
               $format.= " DEFAULT '$default_value'";
            }
            break;

         case 'longtext' :
            $format = "LONGTEXT COLLATE utf8_unicode_ci";
            if (is_null($default_value)) {
                $format .= " DEFAULT NULL";
            } else {
               $format .= " DEFAULT '$default_value'";
            }
            break;

         // for plugins
         case 'autoincrement' :
            $format = "INT(11) NOT NULL AUTO_INCREMENT";
            break;

         default :
            // for compatibility with old 0.80 migrations
            $format = $type;
            break;
      }
      return $format;
   }


   /**
    * Add a new GLPI normalized field
    *
    * @param $table
    * @param $field to add
    * @param $type : can be bool, string, integer, date, datatime, text, longtext, autoincrement
    * @param $options array
    *    - update if not empty = value of $field (must be protected)
    *    - condition if needed
    *    - value default_value new field's default value, if a specific default value needs to be used
    *    - comment comment to be added during field creation
    *    - after where adding the new field
   **/
   function addField($table, $field, $type, $options=array()) {
      global $DB, $LANG;

      $params['update']    = '';
      $params['condition'] = '';
      $params['value']     = NULL;
      $params['comment']   = '';
      $params['after']     = '';

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $format = $this->fieldFormat($type, $params['value']);

      if ($params['comment']) {
         $params['comment'] = " COMMENT '".addslashes($params['comment'])."'";
      }

      if ($params['after']) {
         $params['after'] = " AFTER `".$params['after']."`";
      }

      if ($format) {
         if (!FieldExists($table, $field, false)) {
            $this->change[$table][] = "ADD `$field` $format ".$params['comment'] ." ".
                                           $params['after']."";

            if ($params['update']) {
               $this->migrationOneTable($table);
               $query = "UPDATE `$table`
                         SET `$field` = ".$params['update']." ".
                         $params['condition']."";
               $DB->query($query)
               or die($this->version." set $field in $table " . $LANG['update'][90] . $DB->error());
            }
            return true;
         }
         return false;
      }
   }


   /**
    * Modify field for migration
    *
    * @param $table
    * @param $oldfield : old name of the field
    * @param $newfield : new name of the field
    * @param $type : can be bool, string, integer, date, datatime, text, longtext, autoincrement
    * @param $options array
    *    - default_value new field's default value, if a specific default value needs to be used
    *    - comment comment to be added during field creation
   **/
   function changeField($table, $oldfield, $newfield, $type, $options=array()) {

      $params['value']     = NULL;
      $params['comment']   = '';

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $format = $this->fieldFormat($type, $params['value']);

      if ($params['comment']) {
         $params['comment'] = " COMMENT '".addslashes($params['comment'])."'";
      }


      if (FieldExists($table, $oldfield, false)) {
         // in order the function to be replayed
         // Drop new field if name changed
         if ($oldfield != $newfield && FieldExists($table, $newfield)) {
            $this->change[$table][] = "DROP `$newfield` ";
         }

         if ($format) {
            $this->change[$table][] = "CHANGE `$oldfield` `$newfield` $format ".$params['comment']."";
         }
         return true;
      }

      return false;
   }


   /**
    * Drop field for migration
    *
    * @param $table
    * @param $field to drop
   **/
   function dropField($table, $field) {

      if (FieldExists($table, $field, false)) {
         $this->change[$table][] = "DROP `$field`";
      }
   }


   /**
    * Drop immediatly a table if it exists
    *
    * @param table
   **/
   function dropTable($table) {
      global $DB;

      if (TableExists($table)) {
         $DB->query("DROP TABLE `$table`");
      }
   }


   /**
    * Add index for migration
    *
    * @param $table
    * @param $fields : string or array
    * @param $indexname : if empty =$fields
    * @param $type : index or unique
    * @param $len : integer for field length
   **/
   function addKey($table, $fields, $indexname='', $type='INDEX', $len=0) {

      // si pas de nom d'index, on prend celui du ou des champs
      if (!$indexname) {
         if (is_array($fields)) {
            $indexname = implode($fields, "_");
         } else {
            $indexname = $fields;
         }
      }

      if (!isIndex($table,$indexname)) {
         if (is_array($fields)) {
            if ($len) {
               $fields = "`".implode($fields, "`($len), `")."`($len)";
            } else {
               $fields = "`".implode($fields, "`, `")."`";
            }
         } else if ($len) {
            $fields = "`$fields`($len)";
         } else {
            $fields = "`$fields`";
         }

         $this->change[$table][] = "ADD $type `$indexname` ($fields)";
      }
   }


   /**
    * Drop index for migration
    *
    * @param $table
    * @param $indexname
   **/
   function dropKey($table, $indexname) {

      if (isIndex($table,$indexname)) {
         $this->change[$table][] = "DROP INDEX `$indexname`";
      }
   }


   /**
    * Rename table for migration
    *
    * @param $oldtable
    * @param $newtable
   **/
   function renameTable($oldtable, $newtable) {
      global $LANG, $DB;

      if (!TableExists("$newtable") && TableExists("$oldtable")) {
         $query = "RENAME TABLE `$oldtable` TO `$newtable`";
         $DB->query($query)
         or die($this->version." rename $oldtable " . $LANG['update'][90] . $DB->error());
      }
   }



   /**
    * Execute migration for only one table
    *
    * @param $table
   **/

   function migrationOneTable($table) {
      global $DB, $LANG;

      if (isset($this->change[$table])) {
         $query = "ALTER TABLE `$table` ".implode($this->change[$table], " ,\n")." ";
         $this->displayMessage( $LANG['update'][141] . ' - '.$table);
         $DB->query($query)
         or die($this->version." multiple alter in $table " . $LANG['update'][90] . $DB->error());

         unset($this->change[$table]);
      }
   }


   /**
    * Execute global migration
   **/

   function executeMigration() {
      global $LANG;

      foreach ($this->change as $table => $tab) {
         $this->migrationOneTable($table);
      }

      // end of global message
      $this->displayMessage($LANG['rulesengine'][91]);
   }

}

?>

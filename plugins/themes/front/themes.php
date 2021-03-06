<?php
/*
 *
 -------------------------------------------------------------------------
 Themes
 Copyright (C) 2012 by iizno.

 https://forge.indepnet.net/projects/themes
 -------------------------------------------------------------------------

 LICENSE

 This file is part of themes.

 themes is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 themes is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with themes. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: J�r�me Ansel <jerome@ansel.im>
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

$t = new PluginThemesTheme();
if(isset($_POST['add_theme'])) {

   if(!isset($_FILES['themeFile'])) {
      Session::addMessageAfterRedirect($LANG['plugin_themes']['error']['no_file'], false, ERROR);
      header('location:'.GLPI_ROOT.'/plugins/themes/front/themes.php');
      die();
   } 

   $zip = zip_open($_FILES["themeFile"]["tmp_name"]);
   if(is_resource($zip)) {
      zip_close($zip);
      $isZipFile = true;
   } else {
      $isZipFile = false;
   }

   if(!$isZipFile) {
      Session::addMessageAfterRedirect($LANG['plugin_themes']['error']['not_zip'], false, ERROR);
      header('location:'.GLPI_ROOT.'/plugins/themes/front/themes.php');
      die();
   }

   $filenameArray = explode('.',$_FILES['themeFile']['name']);
   $filename = $filenameArray[0];

   if (!is_dir(PLUGIN_THEMES_UPLOAD_DIR.'/'.$filename)) {

      $uploads_dir = GLPI_DOC_DIR.'/_uploads/';
      $tmp_name = $_FILES["themeFile"]["tmp_name"];
      if(!move_uploaded_file($tmp_name, "$uploads_dir/{$_FILES['themeFile']['name']}")) {

         Session::addMessageAfterRedirect(
            "Error while uploading.", 
            false, 
            INFO);  

      } else {

         if(!chmod("$uploads_dir/{$_FILES['themeFile']['name']}", 0777)) {
          die("boooo");
         }
         $result = PluginThemesTheme::installThemes("$uploads_dir/", $_FILES['themeFile']['name']);
         if($result === true) {
            
            Session::addMessageAfterRedirect(
               $LANG['plugin_themes']['theme_installed'], 
               false, 
               INFO);

         } else {
            
            Session::addMessageAfterRedirect(
               $result, 
               false, 
               ERROR);

         }
         unlink("$uploads_dir/{$_FILES['themeFile']['name']}");

      }
        
   } else {

      Session::addMessageAfterRedirect($LANG['plugin_themes']['error']['already_exist'],
                                       false, ERROR);
   }
   header('location:'.GLPI_ROOT.'/plugins/themes/front/themes.php');
   die();
}

if(isset($_GET['activate']) && $t->canCreate()) {
   $t->getFromDB($_GET['activate']);
   PluginThemesTheme::resetActiveTheme();
   $values = array('id' => $_GET['activate'], 'active_theme' => '1');
   $t->update($values);
   header('location:'.GLPI_ROOT.'/plugins/themes/front/themes.php');
}

if(isset($_GET['delete']) && $t->canCreate()) {
   if(!$t->getFromDB($_GET['delete'])) {
      Session::addMessageAfterRedirect($LANG['plugin_themes']['cant_delete'],false, ERROR);
      header('location:'.GLPI_ROOT.'/plugins/themes/front/themes.php');   
   }

   if($t->fields['active_theme'] == "1") {
      $DB->query("UPDATE glpi_plugin_themes_themes SET active_theme = 1 WHERE name = 'GLPI'");
   }
   
   if (is_dir(PLUGIN_THEMES_UPLOAD_DIR."/".$t->fields['name'])) {
      Toolbox::deleteDir(PLUGIN_THEMES_UPLOAD_DIR."/".$t->fields['name']);
   }

   $DB->query("DELETE FROM glpi_plugin_themes_per_user WHERE theme_id = {$t->fields['id']};");
   $DB->query("DELETE FROM glpi_plugin_themes_themes WHERE id = {$_GET['delete']};");

   Session::addMessageAfterRedirect($LANG['plugin_themes']['is_delete'],false, INFO);
   header('location:'.GLPI_ROOT.'/plugins/themes/front/themes.php');
}

$plugin = new Plugin();
Html::header($LANG['plugin_themes']['title'],'',"plugins","themes");

if (plugin_themes_haveRight('themes', 'r')) {
   PluginThemesTheme::showAllThemes();

} else {
   Html::displayRightError();
}

Html::footer();
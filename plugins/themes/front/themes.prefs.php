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

// Original Author of file: Jérôme Ansel <jerome@ansel.im>
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

if(!Session::getLoginUserID()) {
   die('You need to be logged to use this file.');
} else {

   if(!isset($_POST['theme_user_selection']) || $_POST['plugin_themes_themes_id'] == 0) {
      Session::addMessageAfterRedirect($LANG['plugin_themes']['error']['no_theme_chosen'], false, ERROR);
   } else {
      $choosenTheme = new PluginThemesTheme();
      $choosenTheme->getFromDB($_POST['plugin_themes_themes_id']);   
      if($choosenTheme->setAsUserTheme()) {
         Session::addMessageAfterRedirect($LANG['plugin_themes']['error']['theme_chosen'], false, INFO);            
      } else {
         Session::addMessageAfterRedirect($LANG['plugin_themes']['error']['no_theme_chosen'], false, ERROR);         
      }
      
   }
   
   Html::back();
   
}
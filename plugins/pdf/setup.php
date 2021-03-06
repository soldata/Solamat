<?php
/*
 * @version $Id: setup.php 326 2012-07-03 16:13:19Z remi $
 -------------------------------------------------------------------------
 pdf - Export to PDF plugin for GLPI
 Copyright (C) 2003-2011 by the pdf Development Team.

 https://forge.indepnet.net/projects/pdf
 -------------------------------------------------------------------------

 LICENSE

 This file is part of pdf.

 pdf is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 pdf is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with pdf. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: BALPE Dévi
// Purpose of file:
// ----------------------------------------------------------------------

function plugin_init_pdf() {
   global $PLUGIN_HOOKS,$LANG;

   $PLUGIN_HOOKS['csrf_compliant']['pdf'] = true;

   Plugin::registerClass('PluginPdfProfile',    array('addtabon' => 'Profile'));
   Plugin::registerClass('PluginPdfPreference', array('addtabon' => 'Preference'));

   $PLUGIN_HOOKS['change_profile']['pdf'] = array('PluginPdfProfile','changeprofile');
   $PLUGIN_HOOKS['pre_item_purge']['pdf'] = array('Profile' => array('PluginPdfProfile','cleanProfiles'));

   if (isset($_SESSION["glpi_plugin_pdf_profile"])
       && $_SESSION["glpi_plugin_pdf_profile"]["use"]) {

      $PLUGIN_HOOKS['use_massive_action']['pdf']   = 1;


      // Define the type for which we know how to generate PDF :
      $PLUGIN_HOOKS['plugin_pdf']['Computer']         = 'PluginPdfComputer';
      $PLUGIN_HOOKS['plugin_pdf']['Group']            = 'PluginPdfGroup';
      $PLUGIN_HOOKS['plugin_pdf']['KnowbaseItem']     = 'PluginPdfKnowbaseItem';
      $PLUGIN_HOOKS['plugin_pdf']['Monitor']          = 'PluginPdfMonitor';
      $PLUGIN_HOOKS['plugin_pdf']['NetworkEquipment'] = 'PluginPdfNetworkEquipment';
      $PLUGIN_HOOKS['plugin_pdf']['Peripheral']       = 'PluginPdfPeripheral';
      $PLUGIN_HOOKS['plugin_pdf']['Phone']            = 'PluginPdfPhone';
      $PLUGIN_HOOKS['plugin_pdf']['Printer']          = 'PluginPdfPrinter';
      $PLUGIN_HOOKS['plugin_pdf']['Software']         = 'PluginPdfSoftware';
      $PLUGIN_HOOKS['plugin_pdf']['SoftwareLicense']  = 'PluginPdfSoftwareLicense';
      $PLUGIN_HOOKS['plugin_pdf']['SoftwareVersion']  = 'PluginPdfSoftwareVersion';
      $PLUGIN_HOOKS['plugin_pdf']['Ticket']           = 'PluginPdfTicket';

      // End init, when all types are registered by all plugins
      $PLUGIN_HOOKS['post_init']['pdf'] = 'plugin_pdf_postinit';
   }
}


function plugin_version_pdf() {
   global $LANG;

   return array('name'           => $LANG['plugin_pdf']['title'][1],
                'version'        => '0.83.3',
                'author'         => 'Dévi Balpe, Remi Collet, Nelly Mahu-Lasson, Walid Nouh',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/pdf',
                'minGlpiVersion' => '0.83.3');
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_pdf_check_prerequisites(){

   if (version_compare(GLPI_VERSION,'0.83.3','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI 0.83.3";
      return false;
   }
   return true;
}


// Config process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_pdf_check_config(){
   return TableExists("glpi_plugin_pdf_profiles");
}

?>
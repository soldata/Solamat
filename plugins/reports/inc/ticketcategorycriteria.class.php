<?php

/*
 * @version $Id: ticketcategorycriteria.class.php 212 2012-01-02 14:47:47Z yllen $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Ticket category selection criteria
 */
class PluginReportsTicketCategoryCriteria extends PluginReportsDropdownCriteria {


   function __construct($report, $name='itilcategories_id', $label='', $type='') {
      global $LANG;

      parent::__construct($report, $name, 'glpi_itilcategories',
                          ($label ? $label : $LANG['setup'][79]), $type);
   }

}
?>
<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Accounts plugin for GLPI
 Copyright (C) 2003-2011 by the accounts Development Team.

 https://forge.indepnet.net/projects/accounts
 -------------------------------------------------------------------------

 LICENSE

 This file is part of accounts.

 accounts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 accounts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with accounts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

$LANG['plugin_accounts']['title'][1] = "Accounts";

$LANG['plugin_accounts'][1] = "Cuentas";
$LANG['plugin_accounts'][2] = "Login";
$LANG['plugin_accounts'][3] = "Contraseña";
$LANG['plugin_accounts'][5] = "La antigua o la nueva clave de cifrado no pueden estar vacías";
$LANG['plugin_accounts'][6] = "Elemento(s) asociado(s)";
$LANG['plugin_accounts'][7] = "Nombre";
$LANG['plugin_accounts'][8] = "Cuentas(s) asociada(s)";
$LANG['plugin_accounts'][9] = "Estado";
$LANG['plugin_accounts'][10] = "Comentarios";
$LANG['plugin_accounts'][11] = "Linked accounts list";
$LANG['plugin_accounts'][12] = "Tipo";
$LANG['plugin_accounts'][13] = "Fecha de caducidad";
$LANG['plugin_accounts'][14] = "No caduca nunca";
$LANG['plugin_accounts'][15] = "Vacío para infinito";
$LANG['plugin_accounts'][16] = "Otros";
$LANG['plugin_accounts'][17] = "Fecha de creacíon";
$LANG['plugin_accounts'][18] = "Usuario en cuestión";
$LANG['plugin_accounts'][19] = "Grupo en cuestión";
$LANG['plugin_accounts'][20] = "Descifrar";
$LANG['plugin_accounts'][21] = "Hash";
$LANG['plugin_accounts'][22] = "Clave de cifrado errónea";
$LANG['plugin_accounts'][23] = "Clave de cifrado";
$LANG['plugin_accounts'][24] = "Por favor, introduzca la contraseña y la clave de cifrado";
$LANG['plugin_accounts'][25] = "La contraseña no será modificada";
$LANG['plugin_accounts'][26] = "WARNING : a encrypted key already exist for this entity";
$LANG['plugin_accounts'][27] = "Generar el hash a partir de esta clave de cifrado";
$LANG['plugin_accounts'][28] = "El hash a introducir en el siguiente campo para crear el cifrado es : ";
$LANG['plugin_accounts'][29] = "Por favor, introduzca la clave de cifrado";
$LANG['plugin_accounts'][30] = "CUIDADO : Si cambias el hash actual, las cuentas antiguas seguirán usando la clave de descifrado antigua";
$LANG['plugin_accounts'][31] = "Generar";
$LANG['plugin_accounts'][32] = "Crear una cuenta nueva";
$LANG['plugin_accounts'][33] = "Cuidado : salvar la clave de encriptación es un agujero de seguridad";
$LANG['plugin_accounts'][34] = "Descifrar";
$LANG['plugin_accounts'][35] = "There is no encrypted key for this entity";
$LANG['plugin_accounts'][36] = "Modificación de la clave de cifrado para todas las contraseñas";
$LANG['plugin_accounts'][37] = "Clave de cifrado nueva";
$LANG['plugin_accounts'][38] = "Clave de cifrado antigua";
$LANG['plugin_accounts'][39] = "¿Quiere modificar la clave : ";
$LANG['plugin_accounts'][40] = " por la clave? : ";
$LANG['plugin_accounts'][41] = "ATENCION : si se equivoca en la introducción de la antigua o la nueva clave, no podrá volver a descifrar sus contraseñas. OBLIGATORIO realizar una copia de la BD antes de proceder.";
$LANG['plugin_accounts'][42] = "Encrypted keys";
$LANG['plugin_accounts'][43] = "Encrypted key modified";
$LANG['plugin_accounts'][44] = "Save the encrypted key";

$LANG['plugin_accounts']['upgrade'][0] = "Actualiza";
$LANG['plugin_accounts']['upgrade'][1] = "1. Define la clave de cifrado y crear el hash";
$LANG['plugin_accounts']['upgrade'][2] = "2. Migrar las cuentas";
$LANG['plugin_accounts']['upgrade'][3] = "Actualiza";
$LANG['plugin_accounts']['upgrade'][4] = "Nombres de cuentas";
$LANG['plugin_accounts']['upgrade'][5] = "Contraseña descifrada";
$LANG['plugin_accounts']['upgrade'][6] = "3. Si todas las cuentas fueron migradas, la actualización ha terminado";

$LANG['plugin_accounts']['profile'][0] = "Permisos de uso";
$LANG['plugin_accounts']['profile'][7] = "Ver todas las cuentas";
$LANG['plugin_accounts']['profile'][8] = "Ver las cuentas de mis grupos";

$LANG['plugin_accounts']['mailing'][0] = "Cuentas caducadas";
$LANG['plugin_accounts']['mailing'][1] = "Cuentas caducadas o que van a caducar";
$LANG['plugin_accounts']['mailing'][2] = "Cuentas que van a caducar";
$LANG['plugin_accounts']['mailing'][3] = "Cuentas caducadas desde hace más de";
$LANG['plugin_accounts']['mailing'][4] = "Nueva cuenta";
$LANG['plugin_accounts']['mailing'][5] = "Se ha creado una cuenta nueva";
$LANG['plugin_accounts']['mailing'][6] = "Enlace a la nueva cuenta creada";
$LANG['plugin_accounts']['mailing'][7] = "Cuentas que van a caducar en menos de";

$LANG['plugin_accounts']['setup'][1] = "Configuración del plugin";
$LANG['plugin_accounts']['setup'][2] = "Tipo de cuentas";
$LANG['plugin_accounts']['setup'][3] = "Vista por tipo";
$LANG['plugin_accounts']['setup'][4] = "Seleccione el tipo de cuenta deseado";
$LANG['plugin_accounts']['setup'][5] = "Please do not use special characters like / \ ' \" & in encryption keys, or you cannot change it after.";
$LANG['plugin_accounts']['setup'][9] = "Asociar";
$LANG['plugin_accounts']['setup'][10] = "Disociar";
$LANG['plugin_accounts']['setup'][11] = "Periodo de comprobación de validez de las cuentas";
$LANG['plugin_accounts']['setup'][12] = "días";
$LANG['plugin_accounts']['setup'][18] = "Asociar a la cuenta";
$LANG['plugin_accounts']['setup'][19] = "Estados no utilizados <br> en el envío de correos de caducidad";
$LANG['plugin_accounts']['setup'][20] = "Añadir un estado no utilizado <br> en el envío de correos de caducidad";

?>
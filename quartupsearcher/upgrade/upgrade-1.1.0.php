<?php
/**
* 2009-2016 Webimpacto
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@webimpacto.es so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Webimpacto to newer
* versions in the future. If you wish to customize Webimpacto for your
* needs please refer to http://www.webimpacto.es for more information.
*
*  @author    Webimpacto Consulting S.L. <info@webimpacto.es>
*  @copyright 2009-2016 Webimpacto Consulting S.L.
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Webimpacto Consulting S.L.
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_1_0($module)
{
    /**
     * Do everything you want right there,
     * You could add a column in one of your module's tables
     */

    return true;
}

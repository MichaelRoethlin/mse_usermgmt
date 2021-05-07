<?php
// This file is part of MSE User Management plugin for Moodle - http://moodle.org/
//
// MSE User Management is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// MSE User Management is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MSE User Management.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main form for MSE User Management.
 *
 * @package    local_mse_usermgmt
 * @copyright  2021 BFH-TI
 * @author     Michael Röthlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code for the MSE User Management local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 * @return bool result
 */
function xmldb_local_mse_usermgmt_upgrade($oldversion) {

    // Moodle v2.6.0 release upgrade line.
    // Upgrade steps below.

    return true;
}

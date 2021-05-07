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
 * Adds Test link to the Site Administration > Server menu. There are no settings for this plugin.
 *
 * @package    local_mse_usermgmt
 * @copyright  2021 BFH-TI
 * @author     Michael Röthlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    if ($CFG->branch >= 32) { // Moodle 3.2 and later.
        $section = 'accounts';
    } else { // Up to and including Moodle 3.1.x .
        $section = 'server';
    }
    $ADMIN->add($section, new admin_externalpage('local_mse_usermgmt',
            get_string('pluginname', 'local_mse_usermgmt'),
            new moodle_url('/local/mse_usermgmt/')
    ));
}

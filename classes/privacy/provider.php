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
 * Privacy Subsystem implementation for local_mse_usermgmt.
 *
 * @package    local_mse_usermgmt
 * @copyright  2021 BFH-TI
 * @author     Michael Röthlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mse_usermgmt\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for local_mse_usermgmt implementing null_provider.
 *
 * @copyright  2018 TNG Consulting Inc. <www.tngconsulting.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}

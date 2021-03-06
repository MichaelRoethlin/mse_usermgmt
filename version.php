<?php
// This file is part of the MSE User Management plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information for MSE User Management.
 *
 * @package    local_mse_usermgmt
 * @copyright  2021 BFH-TI
 * @author     Michael Röthlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_mse_usermgmt';  // To check on upgrade, that module sits in correct place
$plugin->version = 2021050701;        // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2013040500;        // Requires Moodle version 2.5.
$plugin->release = '1.1.0 (2020090300)';
$plugin->maturity = MATURITY_STABLE;
$plugin->cron = 0;

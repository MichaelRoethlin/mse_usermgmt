<?php
// This file is part of MailTest for Moodle - http://moodle.org/
//
// MailTest is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// MailTest is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MailTest.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Displays the form and processes the form submission.
 *
 * @package    local_mse_usermgmt
 * @copyright  2015-2019 TNG Consulting Inc. - www.tngconsulting.ca
 * @author     Michael Milette
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Include our function library.
$pluginname = 'mse_usermgmt';
require_once($CFG->dirroot . '/local/' . $pluginname . '/locallib.php');

// Globals.
global $CFG, $OUTPUT, $USER, $SITE, $PAGE, $DB;

define('CODEWORD', 'doit');

// Ensure only administrators have access.
$homeurl = new moodle_url('/');
require_login();
if (!is_siteadmin()) {
    redirect($homeurl, "This feature is only available for site administrators.", 5);
}

// URL Parameters.
// There are none.

// Include form.
require_once(dirname(__FILE__) . '/classes/' . $pluginname . '_form.php');

// Heading ==========================================================.

$title = get_string('pluginname', 'local_' . $pluginname);
$heading = get_string('heading', 'local_' . $pluginname);
$url = new moodle_url('/local/' . $pluginname . '/');
if ($CFG->branch >= 25) { // Moodle 2.5+.
    $context = context_system::instance();
} else {
    $context = get_system_context();
}

// Set debug level to a minimum of NORMAL: Show errors, warnings and notices.
if ($CFG->debug < 15) {
    $CFG->debug = 15;
}

$actions = [
    ['key' => 'home', 'label' => 'Home'],
    ['key' => 'viewNotusedAccounts', 'label' => 'View (and update) all accounts with old logins'],
    ['key' => 'viewStatusAllOldOSTaccounts', 'label' => 'View all active FHSG, HSR, NTB accounts (old OST)'],
    ['key' => 'viewStatusAllNewOSTaccounts', 'label' => 'View all active OST accounts'],
    ['key' => 'viewStatusUpdateToOSTaccounts', 'label' => 'Update ALL active old FHSG, HSR and NTB accounts to OST'],
    ['key' => 'viewStatusUpdateToOSTaccountsSNG', 'label' => 'Update SINGLE active old FHSG, HSR and NTB accounts to OST'],
    ['key' => 'viewStatusAllBFHaccounts', 'label' => 'View all active BFH accounts'],
];

$actionkeys = [];
$actionlabels = [];

// Transform KV pairs into linear string arrays
foreach ($actions as $act) {
    $actionkeys[] = $act['key'];
    $actionlabels[] = $act['label'];
}

// An action as per URL GET parameter
$action = optional_param('action', $actionkeys[0], PARAM_ALPHAEXT);
$confirmation = optional_param('confirmation', '1234', PARAM_ALPHAEXT);
$checkedusers = optional_param_array('checkedusers', null, PARAM_RAW);

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($heading);
admin_externalpage_setup('local_' . $pluginname); // Sets the navbar & expands navmenu.

// Setup the form.

$CFG->noreplyaddress = empty($CFG->noreplyaddress) ? 'noreply@' . get_host_from_url($CFG->wwwroot) : $CFG->noreplyaddress;

if (!empty($CFG->emailonlyfromnoreplyaddress) || $CFG->branch >= 32) { // Always send from no reply address.
    // Use primary administrator's name if support name has not been configured.
    $primaryadmin = get_admin();
    $CFG->supportname = empty($CFG->supportname) ? fullname($primaryadmin, true) : $CFG->supportname;
    // Use noreply address.
    $fromemail = local_mse_usermgmt_generate_email_user($CFG->noreplyaddress, format_string($CFG->supportname));
    $fromdefault = $CFG->noreplyaddress;
} else { // Otherwise defaults to send from primary admin user.
    $fromemail = get_admin();
    $fromdefault = $fromemail->email;
}

$form = new mse_usermgmt_form(null, array('fromdefault' => $fromdefault));

if ($form->is_cancelled()) {
    redirect($homeurl);
}

echo $OUTPUT->header();

$msg = '';

switch ($action) {
    // Home: list all possible actions
    case $actionkeys[0]:
        for ($a = 1, $aMax = count($actions); $a < $aMax; $a++) {
            local_mse_usermgmt_msgbox($actionlabels[$a] . ' [' . $actionkeys[$a] . ']', $heading = null, $level = 1,
                'alert alert-warning alert-block fade in',
                $link = '?action=' . $actionkeys[$a], $id = $actionkeys[$a]);
        }
        break;
    case $actionkeys[1]:
        $newTimestamp = strtotime('-2 years', time());
        $msg = get_user_list_with_last_login_before($newTimestamp);
        break;
    case $actionkeys[2]:
        $msg = get_user_list_from_identity(['fhsg.ch', 'hsr.ch', 'ntb.ch']);
        break;
    case $actionkeys[3]:
        $msg = get_user_list_from_identity(['ost.ch']);
        break;
    case $actionkeys[4]:
        $msg = get_user_list_from_identity(['fhsg.ch', 'hsr.ch', 'ntb.ch'], true, '?action=updateOldOSTusers',true);
        break;
    case $actionkeys[5]:
        $msg = get_user_list_from_identity(['fhsg.ch', 'hsr.ch', 'ntb.ch'], true, '?action=updateOldOSTusers',false);
        break;
    case $actionkeys[6]:
        $msg = get_user_list_from_identity(['bfh.ch']);
        break;
    case 'updateOldOSTusers':
        if (isset($checkedusers)) {
            set_users_to_ost($checkedusers, ($confirmation === CODEWORD));
        } else {
            print_error('Illegal parameters');
        }
        break;
    case 'deleteusers':
        if (isset($checkedusers) && ($confirmation === CODEWORD)) {
            set_users_inactive($checkedusers);
        } else {
            print_error('Illegal parameters');
        }
        break;
    default:
        local_mse_usermgmt_msgbox('Cannot handle action: ' . $action, 'Error', 3, 'alert alert-warning alert-block fade in');
        phpinfo();
        break;
}

if (trim($msg)) {
    local_mse_usermgmt_msgbox($msg, 'Result', 3, 'alert alert-warning alert-block fade in');
    //    local_mse_usermgmt_msgbox($actionlabels[0], $heading = null, $level = 1, 'alert alert-success alert-block fade in',
    //        $link = '?action=' . $actionkeys[0], $id = $actionkeys[0]);
}

// Footing  =========================================================.

echo $OUTPUT->footer();

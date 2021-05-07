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
 * Library of functions for MSE User Management.
 *
 * @package    local_mse_usermgmt
 * @copyright  2021 BFH-TI
 * @author     Michael Röthlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
const STANDARD_USERLIST = "u.email,
    u.username,
    u.suspended,
    u.id,
    u.lastname,
    u.firstname,
    DATE_FORMAT(FROM_UNIXTIME(u.lastaccess), '%Y-%m-%d %h:%i:%s') as lastaccess,
    DATE_FORMAT(FROM_UNIXTIME(u.firstaccess), '%Y-%m-%d %h:%i:%s') as firstaccess";

/**
 * Generate a user info object based on provided parameters.
 *
 * @param string $email plain text email address.
 * @param string $name (optional) plain text real name.
 * @param int $id (optional) user ID
 *
 * @return     object  user info.
 */
function local_mse_usermgmt_generate_email_user(string $email, string $name = '', int $id = -99) {
    $emailuser = new stdClass();
    $emailuser->email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailuser->email = '';
    }
    $name = format_text($name, FORMAT_HTML, array('trusted' => false, 'noclean' => false));
    $emailuser->firstname = trim(filter_var($name, FILTER_SANITIZE_STRING));
    $emailuser->lastname = '';
    $emailuser->maildisplay = true;
    $emailuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML emails.
    $emailuser->id = $id;
    $emailuser->firstnamephonetic = '';
    $emailuser->lastnamephonetic = '';
    $emailuser->middlename = '';
    $emailuser->alternatename = '';
    return $emailuser;
}

/**
 * Outputs a message box.
 *
 * @param string $text The text of the message.
 * @param string|null $heading (optional) The text of the heading.
 * @param int $level (optional) The level of importance of the
 *                               heading. Default: 2.
 * @param string|null $classes (optional) A space-separated list of CSS
 *                               classes.
 * @param string|null $link (optional) The link where you want the Continue
 *                               button to take the user. Only displays the
 *                               continue button if the link URL was specified.
 * @param string|null $id (optional) An optional ID. Is applied to body
 *                               instead of heading if no heading.
 * @return     string  the HTML to output.
 */
function local_mse_usermgmt_msgbox(string $text, string $heading = null, int $level = 2, string $classes = null,
    string $link = null, string $id = null): string {
    global $OUTPUT;
    echo $OUTPUT->box_start(trim('box ' . $classes));
    if (!is_null($heading)) {
        echo $OUTPUT->heading($heading, $level, $id);
        echo "<p>$text</p>" . PHP_EOL;
    } else {
        echo "<p id=\"$id\">$text</p>" . PHP_EOL;
    }
    if (!is_null($link)) {
        echo $OUTPUT->continue_button($link);
    }
    echo $OUTPUT->box_end();
}

/**
 * Get the user's public or private IP address.
 *
 * @return     string  Public IP address or the private IP address if the public address cannot be identified.
 */
function local_mse_usermgmt_getuserip(): string {
    $fieldlist = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',
        'REMOTE_ADDR', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_CLUSTER_CLIENT_IP');

    // Public range first.
    $filterlist = array(
        FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );

    foreach ($filterlist as $filter) {
        foreach ($fieldlist as $field) {

            if (!array_key_exists($field, $_SERVER) || empty($_SERVER[$field])) {
                continue;
            }

            $iplist = explode(',', $_SERVER[$field]);
            foreach ($iplist as $ip) {

                // Strips off port number if it exists.
                if (substr_count($ip, ':') === 1) {
                    // IPv4 with a port.
                    list($ip) = explode(':', $ip);
                } else if ($start = (($ip[0] === '[') && $end = strpos($ip, ']:') !== false)) {
                    // IPv6 with a port.
                    $ip = substr($ip, $start + 1, $end - 2);
                }
                // Sanitize so that we only get public addresses.
                $lastip = $ip; // But save other address just in case.
                $ip = filter_var(trim($ip), FILTER_VALIDATE_IP, $filter);
                if ($ip !== false) {
                    return ($ip);
                }
            }
        }
    }
    // Private or restricted range.
    return $lastip;
}

/**
 * This function returns the HTML table for an array with users
 *
 * @param $users
 * @param bool $formcheckbox
 * @param string $formtarget
 * @return string
 */
function arrayToHTML($users, bool $formcheckbox = false, string $formtarget = '', $fill = true): string {
    $msg = '';

    if (false) {
        echo '<pre>';
        print_r($users);
        die();
    }

    if ($formcheckbox) {
        $msg .= '<form method="post" action="' . $formtarget . '">';
    }

    $keys = [];
    foreach ($users as $email => $user) {
        if (empty($keys)) {
            $keys = array_keys((array) $user);
            break;
        }
    }

    $msg .= '
<table>
<tbody>
<tr>' . PHP_EOL;

    if ($formcheckbox) {
        $msg .= '<th>';
    }

    foreach ($keys as $key) {
        $msg .= '<th>' . (($key !== 'id') ? get_string($key) : 'ID') . '</th>' . PHP_EOL;
    }

    $msg .= '</tr>';

    foreach ($users as $email => $user) {
        $msg .= '<tr>' . PHP_EOL;
        if ($formcheckbox) {
            if ($fill) {
                $msg .= '<td width="20"><input type="checkbox" id="' . $user->id . '" value="' . $user->id .
                    '" name="checkedusers[]" checked="checked"></td>';
            } else {
                $msg .= '<td width="20"><input type="checkbox" id="' . $user->id . '" value="' . $user->id .
                    '" name="checkedusers[]" ></td>';
            }
        }

        foreach ($keys as $key) {
            $msg .= '<td>' . $user->{$key} . '</td>' . PHP_EOL;
        }
        $msg .= '</tr>' . PHP_EOL;
    }

    $msg .=
        '</tbody>
</table>';

    if ($formcheckbox) {
        $msg .= '<br/><p align="center">Confirmation key: <input type="text" name="confirmation"></inputbutton></p>';
        $msg .= '<br/><p align="center"><button type="submit" class="btn btn-danger" name="save">Update Users</button>';
        $msg .= '&nbsp;<a href="?action=home" class="btn btn-info" role="button">Cancel</a></p>';
        $msg .= '</form>';
    }

    return $msg;
}

/**
 * Get the list of all users belonging to certain identities
 *
 * @param $identities Array of xyz.ch AAI identities
 * @param null $formcheckbox
 * @param null $formtarget
 * @param bool $fill
 * @return string
 */
function get_user_list_from_identity(array $identities, $formcheckbox = null, $formtarget = null, $fill = true): string {
    global $DB;

    if (count($identities) === 0) {
        return '';
    }

    $userlike = "u.username LIKE '%@" . $identities[0] . "'";

    if (count($identities) > 1) {
        for ($i = 1, $iMax = count($identities); $i < $iMax; $i++) {
            $userlike .= " or u.username LIKE '%@" . $identities[$i] . "' ";
        }
    }

    $sql = "
    SELECT " . STANDARD_USERLIST . "
    FROM mdl_user as u
    WHERE u.suspended = 0 and (" . $userlike . ") and u.emailstop = 0
    ORDER BY u.lastname,u.firstname ASC
";

    $users = $DB->get_records_sql($sql);
    return arrayToHTML($users, $formcheckbox, $formtarget, $fill);
}

/**
 * Get the list of all users having logged in the last time before a given time
 *
 * @param $cutofftime
 * @return string
 */
function get_user_list_with_last_login_before($cutofftime) {
    global $DB;

    $sql = "
    SELECT " . STANDARD_USERLIST . "
    FROM mdl_user as u
    WHERE u.suspended = 0 AND u.deleted = 0 AND (u.lastaccess < " . $cutofftime . ")
    ORDER BY u.lastaccess DESC
";

    $users = $DB->get_records_sql($sql);
    return arrayToHTML($users, true, '?action=deleteusers');
}

/**
 * Set a number of user accounts to a given state
 *
 * @param $userlist
 */
function set_users_inactive($userlist) {
    global $DB, $USER;

    foreach ($userlist as $us) {
        $user = $DB->get_record('user', ['id' => $us]);
        $user->suspended = 1;
        $user->description .= '<br />Deactivated by script MSE User Management by user ' . $USER->username . ' on ' .
            date('l jS \of F Y h:i:s A');
        $DB->update_record('user', $user, false);
    }

    redirect('?action=home', 'Done: ' . count($userlist) . ' users suspended', 3);
}

/**
 * Set a number of user accounts to a given state
 *
 * @param array $userlist List of users to be processed
 * @param bool $reallydoit Execute the DB updates
 */
function set_users_to_ost(array $userlist, bool $reallydoit = false) {
    global $DB, $USER;

    $stratresults = [];
    $stratresults[0] = 0;
    $stratresults[1] = 0;
    $stratresults[2] = 0;
    $stratresults[3] = 0;
    $stratresults[4] = 0;

    if (!$reallydoit) {
        echo '<h3>Dry run only</h3>';
    } else {
        echo '<h3>Success</h3>';
    }

    foreach ($userlist as $us) {
        $olduser = $DB->get_record('user', ['id' => $us]);
        $oldusername = $olduser->username;
        $newusercourses = null;
        $newusername = null;
        $newuser = null;

        $parts = explode('@', $oldusername);

        if (count($parts) > 1) {
            $newusername = $parts[0] . '@ost.ch';
            $newuser = $DB->get_record('user', ['username' => $newusername]);
            if ($newuser) {
                $newusercourses = count(enrol_get_all_users_courses($newuser->id, true, null, 'visible DESC, sortorder ASC'));
            }
        }

        $oldusercourses = count(enrol_get_all_users_courses($olduser->id, true, null, 'visible DESC, sortorder ASC'));

        $updated_text =
            '<br />Updated for FHSG/HSR/NTB to OST Migration by script  MSE User Management by user ' . $USER->username . ' on ' .
            date('l jS \of F Y h:i:s A');

        if (!$newuser) {
            $result = 'Strategy 1: Replace old by new username in existing user record, as no @ost.ch user record exists';

            if ($reallydoit) {
                $olduser->username = $newusername;
                $olduser->description .= $updated_text;
                $DB->update_record('user', $olduser, false);
            }

            $stratresults[1]++;
        } else if ($newusercourses === 0) {
            $result =
                'Strategy 2: Desactivate new @ost.ch user record as no courses are linked, then replace old by new username in existing user record';

            if ($reallydoit) {
                // First rename the new (and not to be used) user record
                $newuser->username = 'migr-' . $newuser->username;
                $newuser->email = 'migr-' . $newuser->email;
                $newuser->firstname = 'INVALID ACCOUNT: ' . $newuser->firstname;
                $newuser->suspended = 1;
                $DB->update_record('user', $newuser, false);

                // Then update the old (and to be used new) user record
                $olduser->username = $newusername;
                $olduser->description .= $updated_text;
                $DB->update_record('user', $olduser, false);
            }

            $stratresults[2]++;
        } else {
            if (($oldusercourses === 0) && ($newusercourses > 0)) {
                $result = 'Strategy 3: Deactivate old non- @ost.ch user record as no courses are linked';

                if ($reallydoit) {
                    // Simply deactivate the old (and not to be used) user record
                    $olduser->suspended = 1;
                    $olduser->firstname = 'INVALID ACCOUNT: ' . $olduser->firstname;
                    $DB->update_record('user', $olduser, false);
                }

                $stratresults[3]++;
            } else {
                $result = 'Strategy 4: Manually merge accounts ...';
                $stratresults[4]++;
            }
        }

        echo 'User ' . $oldusername . ' (' . $olduser->email . '): <br /> - ' . $result . '<br />';

    }

    redirect('?action=home',
        'Done: ' . count($userlist) . ' users checked, strategies' .
        ' 1: ' . $stratresults[1] .
        ', 2: ' . $stratresults[2] .
        ', 3: ' . $stratresults[3] .
        ', 4: ' . $stratresults[4],
        60);
}

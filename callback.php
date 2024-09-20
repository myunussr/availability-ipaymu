<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

use enrol_ipaymu\ipaymu_mathematical_constants;
use enrol_ipaymu\ipaymu_status_codes;
use enrol_ipaymu\ipaymu_helper;

// This script does not require login.
require("../../../config.php"); // phpcs:ignore
require_once("lib.php");
require_once("{$CFG->libdir}/enrollib.php");
require_once("{$CFG->libdir}/filelib.php");

// Gets all response parameter from ipaymu callback.
$merchantorderid = required_param('merchantOrderId', PARAM_TEXT);
$status = required_param('status', PARAM_TEXT);
$trx_id = required_param('trx_id', PARAM_TEXT);
$sid = required_param('sid', PARAM_TEXT);


// Making sure that merchant order id is in the correct format.
$custom = explode('-', $merchantorderid);

$ipaymuhelper = new ipaymu_helper();
$requestdata = $ipaymuhelper->check_transaction($trx_id);


if (isset($requestdata['res']['Status'])) {
    if ($requestdata['res']['Data']['PaidStatus'] != 'paid') {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Payment Failed');
    }
} else {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Wrong Callback Payment');
}

$data = new stdClass();
$data->userid = (int)$custom[1];
$data->contextid = (int)$custom[2];
$data->sectionid = (int)$custom[3];


$user = $DB->get_record("user", ["id" => $data->userid], "*", MUST_EXIST);
$section = $DB->get_record("course_sections", ["id" => $data->sectionid], "course", MUST_EXIST);
$course = $DB->get_record("course", ["id" => $section->course], "*", MUST_EXIST);

// Set the course context.
$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);

// Add to log that callback has been received and student enrolled.
$eventarray = [
    'context' => $context,
    'relateduserid' => (int)$custom[1],
    'other' => [
        'Log Details' => get_string('log_callback', 'enrol_ipaymu'),
        'merchantOrderId' => $merchantorderid,
        'reference' => $sid
    ]
];
$ipaymuhelper->log_request($eventarray);

$admin = get_admin(); // Only 1 MAIN admin can exist at a time.

$params = [
    'userid' => (int)$custom[1],
    'contextid' => (int)$custom[2],
    'sectionid' => (int)$custom[3],
    'reference' => $sid
];

$sql = 'SELECT * FROM {availability_ipaymu_trx} WHERE userid = :userid AND contextid = :contextid AND sectionid = :sectionid AND reference = :reference ORDER BY {availability_ipaymu_trx}.timestamp DESC';
$existingdata = $DB->get_record_sql($sql, $params, 1);

$data->id = $existingdata->id;
$data->payment_status = 'Success';
$data->pending_reason = get_string('log_callback', 'enrol_ipaymu');
$data->timeupdated = round(microtime(true) * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS);

$DB->update_record('availability_ipaymu_trx', $data);

return "Success";

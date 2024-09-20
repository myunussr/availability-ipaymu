<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

use enrol_ipaymu\ipaymu_status_codes;
use enrol_ipaymu\ipaymu_mathematical_constants;
use enrol_ipaymu\ipaymu_helper;

require("../../../config.php");

require_login();

$expiryperiod = get_config('enrol_ipaymu', 'expiry');
$currenttimestamp = round(microtime(true) * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS); // In milisecond.

$environment = required_param('environment', PARAM_TEXT);
$paymentamount = required_param('amount', PARAM_INT);

$merchantorderid = required_param('orderId', PARAM_TEXT);
$customervaname = required_param('customerVa', PARAM_TEXT);
$productdetails = required_param('item_name', PARAM_TEXT);
$email = required_param('email', PARAM_TEXT);
$callbackurl = required_param('notify_url', PARAM_TEXT);

$custom = explode('-', $merchantorderid);
$userid = (int)$custom[1];
$contextid = (int)$custom[2];
$sectionid = (int)$custom[3];

$phonenumber = empty($USER->phone1) === true ? "" : $USER->phone1;

$admin = get_admin(); // Only 1 MAIN admin can exist at a time.

// Check if the user has not made a transaction before.
$params = [
    'userid'      => $userid,
    'contextid'   => $contextid,
    'sectionid'   => $sectionid,
];

$sql = 'SELECT * FROM {availability_ipaymu_trx} WHERE userid = :userid AND contextid = :contextid AND sectionid = :sectionid ORDER BY {availability_ipaymu_trx}.timestamp DESC';

$existingdata = $DB->get_record_sql($sql, $params, 1); // Will return exactly 1 row. The newest transaction that was saved.

$data = new stdClass();
$data->userid = $USER->id;
$data->contextid = $contextid;
$data->sectionid = $sectionid;
$data->timestamp = $currenttimestamp;
$data->merchant_order_id = $merchantorderid;
$data->receiver_id = $admin->id;
$data->receiver_email = $admin->email;
$data->payment_status = ipaymu_status_codes::CHECK_STATUS_PENDING;
$data->pending_reason = get_string('pending_message', 'enrol_ipaymu');
$data->expiryperiod = $currenttimestamp + ($expiryperiod * ipaymu_mathematical_constants::MINUTE_IN_SECONDS * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS);

$data->reference = $request->reference; // Reference only received after successful request transaction.
$data->timeupdated = round(microtime(true) * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS); // In milisecond.

$product[] = $productdetails;
$price[] = $paymentamount;
$qty[] = 1;
$name = $USER->firstname . ' ' . $USER->lastname;
$email = $USER->email;
$phone = $phonenumber;

$returnurl = "$CFG->wwwroot/course/view.php?id=$courseid";

function createLink($product, $qty, $price, $name, $phone, $email, $returnurl, $callbackurl)
{
    $ipaymuhelper = new ipaymu_helper();
    $createLink = $ipaymuhelper->create($product, $qty, $price, $name, $phone, $email, $returnurl, $callbackurl);

    if (!empty($createLink['err'])) {
        throw new Exception('Invalid Response from iPaymu. Please contact support@ipaymu.com');
        exit;
    }

    if (empty($createLink['res'])) {
        throw new Exception('Request Failed: Invalid Response from iPaymu. Please contact support@ipaymu.com');
        exit;
    }

    if (empty($createLink['res']['Data']['Url'])) {
        throw new Exception('Invalid request. Response iPaymu: ' . $createLink['res']['Message']);
        exit;
    }

    return $createLink;
}

if (empty($existingdata)) {

    $createLink = createLink($product, $qty, $price, $name, $phone, $email, $returnurl, $callbackurl);

    $url = $createLink['res']['Data']['Url'];

    $data->reference = $createLink['res']['Data']['SessionID']; // Reference only received after successful request transaction.
    $data->referenceurl = $url; // Link payment iPaymu
    $data->timeupdated = round(microtime(true) * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS); // In milisecond.
    $DB->insert_record('availability_ipaymu_trx', $data);

    header('location: ' . $url);
    die;
}

if ($existingdata->expiryperiod < $currenttimestamp) {

    $createLink = createLink($product, $qty, $price, $name, $phone, $email, $returnurl, $callbackurl);

    $url = $createLink['res']['Data']['Url'];

    $sql = 'SELECT * FROM {availability_ipaymu_trx} WHERE reference = :reference ORDER BY {availability_ipaymu_trx}.timestamp DESC';
    $dtExitst = $DB->get_record_sql($sql, ['reference' => $existingdata->reference], 1);

    $data = new stdClass();
    $data->id = $dtExitst->id;
    $data->expiryperiod = $currenttimestamp + ($expiryperiod * ipaymu_mathematical_constants::MINUTE_IN_SECONDS * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS);
    $data->timeupdated = round(microtime(true) * ipaymu_mathematical_constants::SECOND_IN_MILLISECONDS); // In milisecond.
    $data->reference = $createLink['res']['Data']['SessionID']; // Reference only received after successful request transaction.
    $data->referenceurl = $url; // Link payment iPaymu
    $DB->update_record('availability_ipaymu_trx', $data);

    header('location: ' . $url);
    die;
}

if ($existingdata->payment_status === ipaymu_status_codes::CHECK_STATUS_PENDING) {
    header('location: ' . $existingdata->referenceurl);
    die;
}

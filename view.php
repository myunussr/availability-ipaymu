<?php

require_once('../../../config.php');
require_once($CFG->dirroot . '/availability/condition/ipaymu/lib.php');

$cmid = optional_param('cmid', 0, PARAM_INT);
$sectionid = optional_param('sectionid', 0, PARAM_INT);
$paymentid = optional_param('paymentid', null, PARAM_ALPHANUM);

if (!$cmid && !$sectionid) {
    print_error('invalidparam');
}

if ($cmid) {
    $availability = $DB->get_record('course_modules', ['id' => $cmid], 'course, availability', MUST_EXIST);
    $contextid = $DB->get_field('context', 'id', ['contextlevel' => CONTEXT_MODULE, 'instanceid' => $cmid]);
    $urlparams = ['cmid' => $cmid];
} else {
    $availability = $DB->get_record('course_sections', ['id' => $sectionid], 'course, availability', MUST_EXIST);
    $contextid = $DB->get_field('context', 'id', ['contextlevel' => CONTEXT_COURSE, 'instanceid' => $availability->course]);
    $urlparams = ['sectionid' => $sectionid];
}

$conditions = json_decode($availability->availability);
$ipaymu = availability_ipaymu_find_condition($conditions);

if (is_null($ipaymu)) {
    print_error('no ipaymu condition for this context.');
}

$course = $DB->get_record('course', ['id' => $availability->course]);

require_login($course);

$context = \context::instance_by_id($contextid);
$rxparams = ['userid' => $USER->id, 'contextid' => $contextid, 'sectionid' => $sectionid];

if ($DB->record_exists('availability_ipaymu_trx', $rxparams + ['payment_status' => 'Completed'])) {
    unset($SESSION->availability_ipaymu->paymentid);
    redirect($context->get_url(), get_string('paymentcompleted', 'availability_ipaymu'));
}

// Get the most recent transaction record to see if it is a pending one.
$paymentrxs = $DB->get_records('availability_ipaymu_trx', $rxparams, 'timeupdated DESC, id DESC', '*', 0, 1);
$paymentrx = reset($paymentrxs);

$PAGE->set_url('/availability/condition/ipaymu/view.php', $urlparams);
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);

$PAGE->navbar->add($ipaymu->itemname);

echo $OUTPUT->header(),
$OUTPUT->heading($ipaymu->itemname);

if ($paymentrx && ($paymentrx->payment_status == 'Pending')) {
    echo get_string('paymentpending', 'availability_ipaymu');
    echo $OUTPUT->continue_button($context->get_url(), 'get');
} else if ($paymentid !== null && $paymentid === ($SESSION->availability_ipaymu->paymentid ?? null)) {
    // The users returned from ipaymu before the IPN was processed.
    echo get_string('paymentpending', 'availability_ipaymu');
    echo $OUTPUT->continue_button($context->get_url(), 'get');
} else {

    // Calculate localised and "." cost, make sure we send ipaymu the same value,
    // please note ipaymu expects amount with 2 decimal places and "." separator.
    $localisedcost = format_float($ipaymu->cost, 2, true);
    $cost = format_float($ipaymu->cost, 2, false);

    if (isguestuser()) { // Force login only for guest user, not real users with guest role.
        if (empty($CFG->loginhttps)) {
            $wwwroot = $CFG->wwwroot;
        } else {
            // This actually is not so secure ;-), 'cause we're in unencrypted connection...
            $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
        }
        echo '<div class="mdl-align"><p>' . get_string('paymentrequired', 'availability_ipaymu') . '</p>';
        echo '<div class="mdl-align"><p>' . get_string('paymentwaitremider', 'availability_ipaymu') . '</p>';
        echo '<p><b>' . get_string('cost') . ": $instance->currency $localisedcost" . '</b></p>';
        echo '<p><a href="' . $wwwroot . '/login/">' . get_string('loginsite') . '</a></p>';
        echo '</div>';
    } else {
        // Sanitise some fields before building the ipaymu form.
        $userfullname    = fullname($USER);
        $userfirstname   = $USER->firstname;
        $userlastname    = $USER->lastname;
        $useraddress     = $USER->address;
        $usercity        = $USER->city;
?>
        <p><?php print_string("paymentrequired", 'availability_ipaymu') ?></p>
        <p><b><?php echo get_string("cost") . ": {$ipaymu->currency} {$localisedcost}"; ?></b></p>
        <p><img alt="<?php print_string('ipaymuaccepted', 'availability_ipaymu') ?>" title="<?php print_string('ipaymuaccepted', 'availability_ipaymu') ?>" src="https://www.ipaymu.com/en_US/i/logo/ipaymu_mark_60x38.gif" /></p>
        <p><?php print_string("paymentinstant", 'availability_ipaymu') ?></p>
        <?php
        if (empty($CFG->useipaymusandbox)) {
            $ipaymuurl = 'https://www.ipaymu.com/cgi-bin/webscr';
        } else {
            $ipaymuurl = 'https://www.sandbox.ipaymu.com/cgi-bin/webscr';
        }

        // Add a helper parameter for us to see that we just returned from ipaymu.
        $SESSION->availability_ipaymu = $SESSION->availability_ipaymu ?? (object) [];
        $SESSION->availability_ipaymu->paymentid = clean_param(uniqid(), PARAM_ALPHANUM);
        $returnurl = new moodle_url($PAGE->url, ['paymentid' => $SESSION->availability_ipaymu->paymentid]);

        $orderId = time() . '-' . $USER->id . '-' . $contextid . '-' . $sectionid;

        ?>
        <form action="<?php p($CFG->wwwroot . '/availability/condition/ipaymu/call.php') ?>" method="post">

            <input type="hidden" name="environment" value="<?php get_config('environment') ?>" />
            <input type="hidden" name="orderId" value="<?php echo $orderId ?>" />
            <input type="hidden" name="customerVa" value="<?php echo 'va-' . $orderId ?>" />

            <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="charset" value="utf-8" />
            <input type="hidden" name="business" value="<?php p($ipaymu->businessemail) ?>" />
            <input type="hidden" name="item_name" value="<?php p($ipaymu->itemname) ?>" />
            <input type="hidden" name="item_number" value="<?php p($ipaymu->itemnumber) ?>" />
            <input type="hidden" name="quantity" value="1" />
            <input type="hidden" name="on0" value="<?php print_string("user") ?>" />
            <input type="hidden" name="os0" value="<?php p($userfullname) ?>" />
            <input type="hidden" name="custom" value="<?php echo "availability_ipaymu-{$USER->id}-{$contextid}-{$sectionid}" ?>" />

            <input type="hidden" name="currency_code" value="<?php p($ipaymu->currency) ?>" />
            <input type="hidden" name="amount" value="<?php p($cost) ?>" />

            <input type="hidden" name="for_auction" value="false" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="no_shipping" value="1" />
            <input type="hidden" name="notify_url" value="<?php echo "{$CFG->wwwroot}/availability/condition/ipaymu/callback.php?merchantOrderId=$orderId" ?>" />
            <input type="hidden" name="return" value="<?php echo $CFG->wwwroot ?>" />
            <input type="hidden" name="cancel_return" value="<?php echo $CFG->wwwroot ?>" />
            <input type="hidden" name="rm" value="2" />
            <input type="hidden" name="cbt" value="<?php print_string("continue", 'availability_ipaymu') ?>" />

            <input type="hidden" name="first_name" value="<?php p($userfirstname) ?>" />
            <input type="hidden" name="last_name" value="<?php p($userlastname) ?>" />
            <input type="hidden" name="address" value="<?php p($useraddress) ?>" />
            <input type="hidden" name="city" value="<?php p($usercity) ?>" />
            <input type="hidden" name="email" value="<?php p($USER->email) ?>" />
            <input type="hidden" name="country" value="<?php p($USER->country) ?>" />

            <input type="submit" class="btn btn-primary" value="<?php print_string("sendpaymentbutton", "availability_ipaymu") ?>" />
        </form>
<?php
    }
}
echo $OUTPUT->footer();

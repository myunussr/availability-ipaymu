<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/tablelib.php');

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);

$PAGE->set_url(new moodle_url('/availability/condition/ipaymu/transactions.php', [
    'courseid' => $courseid,
    'perpage' => $perpage,
]));

$PAGE->navbar->add(get_string('transactionsreport', 'availability_ipaymu'), $PAGE->url);

require_login($courseid);
require_capability('availability/ipaymu:managetransactions', context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('transactionsreport', 'availability_ipaymu'));

$table = new \availability_ipaymu\transactions_table();
$table->out($perpage, true);

$options = [];

foreach ([25, 50, 100, 500, TABLE_SHOW_ALL_PAGE_SIZE] as $showperpage) {
    $options[$showperpage] = get_string('showperpage', 'core', $showperpage);
}

if ($table->totalrows) {
    echo html_writer::start_div('my-3');
    echo $OUTPUT->single_select($PAGE->url, 'perpage', $options, $perpage);
    echo html_writer::end_div();
}

echo $OUTPUT->footer();

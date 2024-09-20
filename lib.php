<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

function availability_ipaymu_find_condition($conditions)
{
    foreach ($conditions->c as $cond) {
        if (isset($cond->c)) {
            return availability_ipaymu_find_condition($cond);
        } else if ($cond->type == 'ipaymu') {
            return $cond;
        }
    }
    return null;
    // TODO: handle more than one ipaymu in same context.
}

/**
 * Extend course navigation to add a link to the transactions report.
 *
 * @param navigation_node $parentnode
 * @param stdClass $course
 * @param context_course $context
 */
function availability_ipaymu_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context)
{

    if (has_capability('availability/ipaymu:managetransactions', context_system::instance())) {
        $parentnode->add(
            get_string('transactionsreport', 'availability_ipaymu'),
            new moodle_url('/availability/condition/ipaymu/transactions.php', ['courseid' => $course->id]),
            null,
            null,
            null,
            new pix_icon('i/payment', '')
        );
    }
}

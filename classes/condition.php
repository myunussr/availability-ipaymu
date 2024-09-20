<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

namespace availability_ipaymu;

defined('MOODLE_INTERNAL') || die();

class condition extends \core_availability\condition
{
    public function __construct($structure)
    {
        if (isset($structure->currency)) {
            $this->currency = $structure->currency;
        }
        if (isset($structure->cost)) {
            $this->cost = $structure->cost;
        }
        if (isset($structure->itemname)) {
            $this->itemname = $structure->itemname;
        }
    }

    public function save()
    {
        $result = (object)array('type' => 'ipaymu');
        if ($this->currency) {
            $result->currency = $this->currency;
        }
        if ($this->cost) {
            $result->cost = $this->cost;
        }
        if ($this->itemname) {
            $result->itemname = $this->itemname;
        }
        return $result;
    }

    public static function get_json($currency, $cost)
    {
        return (object)array('type' => 'ipaymu', 'currency' => $currency, 'cost' => $cost);
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid)
    {
        global $DB;
        $allow = false;
        if (is_a($info, '\\core_availability\\info_module')) {
            $context = $info->get_context();
            $allow = $DB->record_exists(
                'availability_ipaymu_trx',
                array(
                    'userid' => $userid,
                    'contextid' => $context->id,
                    'payment_status' => 'Success'
                )
            );
        } else if (is_a($info, '\\core_availability\\info_section')) {
            $section = $info->get_section();
            $allow = $DB->record_exists(
                'availability_ipaymu_trx',
                array(
                    'userid' => $userid,
                    'sectionid' => $section->id,
                    'payment_status' => 'Success'
                )
            );
        }
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info)
    {
        return $this->get_either_description($not, false, $info);
    }

    protected function get_either_description($not, $standalone, $info)
    {
        if (is_callable([$info, 'get_section'])) {
            $params = ['sectionid' => $info->get_section()->id];
        } else {
            $cm = $info->get_course_module();
            $params = ['cmid' => $cm->id];
        }
        $url = new \moodle_url('/availability/condition/ipaymu/view.php', $params);
        if ($not) {
            return get_string('notdescription', 'availability_ipaymu', $url->out());
        } else {
            return get_string('eitherdescription', 'availability_ipaymu', $url->out());
        }
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name)
    {
        $dateoffset = \core_availability\info::get_restore_date_offset($restoreid);
        if ($dateoffset) {
            $this->time += $dateoffset;
            return true;
        }
        return false;
    }

    protected function get_debug_string()
    {
        return gmdate('Y-m-d H:i:s');
    }
}

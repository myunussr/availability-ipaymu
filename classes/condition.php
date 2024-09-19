<?php

namespace availability_ipaymu;

defined('MOODLE_INTERNAL') || die();

/**
 * ipaymu condition.
 *
 * @package availability_ipaymu
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition
{

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure)
    {
        if (isset($structure->businessemail)) {
            $this->businessemail = $structure->businessemail;
        }
        if (isset($structure->currency)) {
            $this->currency = $structure->currency;
        }
        if (isset($structure->cost)) {
            $this->cost = $structure->cost;
        }
        if (isset($structure->itemname)) {
            $this->itemname = $structure->itemname;
        }
        if (isset($structure->itemnumber)) {
            $this->itemnumber = $structure->itemnumber;
        }
    }

    /**
     * Returns info to be saved.
     * @return stdClass
     */
    public function save()
    {
        $result = (object)array('type' => 'ipaymu');
        if ($this->businessemail) {
            $result->businessemail = $this->businessemail;
        }
        if ($this->currency) {
            $result->currency = $this->currency;
        }
        if ($this->cost) {
            $result->cost = $this->cost;
        }
        if ($this->itemname) {
            $result->itemname = $this->itemname;
        }
        if ($this->itemnumber) {
            $result->itemnumber = $this->itemnumber;
        }
        return $result;
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $businessemail The email of ipaymu to be credited
     * @param string $currency      The currency to charge the user
     * @param string $cost          The cost to charge the user
     * @return stdClass Object representing condition
     */
    public static function get_json($businessemail, $currency, $cost)
    {
        return (object)array('type' => 'ipaymu', 'businessemail' => $businessemail, 'currency' => $currency, 'cost' => $cost);
    }

    /**
     * Returns true if the user can access the context, false otherwise
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
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

    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not  True if NOT is in force
     * @param \core_availability\info $info Information about the availability condition and module context
     * @return string    The string about the condition and it's status
     */
    public function get_description($full, $not, \core_availability\info $info)
    {
        return $this->get_either_description($not, false, $info);
    }
    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not        True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     * @param bool $info       Information about the availability condition and module context
     * @return string          The string about the condition and it's status
     */
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

    /**
     * Function used by backup restore
     *
     * @param int $restoreid
     * @param int $courseid
     * @param \base_logger $logger
     * @param string $name
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name)
    {
        // Update the date, if restoring with changed date.
        $dateoffset = \core_availability\info::get_restore_date_offset($restoreid);
        if ($dateoffset) {
            $this->time += $dateoffset;
            return true;
        }
        return false;
    }

    /**
     * Returns a string to debug
     * @return string
     */
    protected function get_debug_string()
    {
        return gmdate('Y-m-d H:i:s');
    }
}

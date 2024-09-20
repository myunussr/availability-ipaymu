<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

namespace availability_ipaymu;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend
{
    protected function get_javascript_strings()
    {
        return array('ajaxerror', 'currency', 'cost', 'itemname');
    }

    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null)
    {
        return true;
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null)
    {
        return array(\get_string_manager()->get_list_of_currencies());
    }
}

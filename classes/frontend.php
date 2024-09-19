<?php

namespace availability_ipaymu;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend {

    /**
     * Return list if string indexes used by javascript
     *
     * @return array
     */
    protected function get_javascript_strings() {
        return array('ajaxerror', 'businessemail', 'currency', 'cost', 'itemname', 'itemnumber');
    }

    /**
     * Return true always - should be if user can add the condition
     *
     * @param stdClass $course
     * @param \cm_info $cm
     * @param \section_info $section
     * @return bool
     */
    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {
        return true;
    }

    /**
     * Gets additional parameters for the plugin's initInner function.
     *
     * Default returns no parameters.
     *
     * @param \stdClass $course Course object
     * @param \cm_info $cm Course-module currently being edited (null if none)
     * @param \section_info $section Section currently being edited (null if none)
     * @return array Array of parameters for the JavaScript function
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
        return array(\get_string_manager()->get_list_of_currencies());
    }
}

<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_availability_ipaymu_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020022000) {

        $table = new xmldb_table('availability_ipaymu_trx');
        $field = new xmldb_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'contextid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020022000, 'availability', 'ipaymu');
    }

    return true;
}

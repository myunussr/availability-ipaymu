<?php

defined('MOODLE_INTERNAL') || die();

/**
 * upgrade this availability condition
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_availability_ipaymu_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020022000) {

        // Define field sectionid to be added to availability_ipaymu_trx.
        $table = new xmldb_table('availability_ipaymu_trx');
        $field = new xmldb_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'contextid');

        // Conditionally launch add field sectionid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ipaymu savepoint reached.
        upgrade_plugin_savepoint(true, 2020022000, 'availability', 'ipaymu');
    }

    return true;
}

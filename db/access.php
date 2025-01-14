<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'availability/ipaymu:receivenotifications' => [
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
        'clonepermissionsfrom' => 'moodle/site:config',
    ],

    'availability/ipaymu:managetransactions' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
        'clonepermissionsfrom' => 'moodle/site:config',
    ],
];

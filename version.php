<?php

/**
 * @package   availability_ipaymu
 * @copyright 2024 Muhammad Yunus <myunusrukmana@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version      = 2022092007;
$plugin->requires     = 2021051700;
$plugin->release      = 18;
$plugin->maturity     = MATURITY_STABLE;
$plugin->component    = 'availability_ipaymu';
$plugin->dependencies = array(
    'enrol_ipaymu' => 2024031300
);

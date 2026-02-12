<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings page registration for Plugin Replacer.
 *
 * @package    local_pluginreplace
 * @copyright  2026 OpenRanger S. A. de C.V. (https://openranger.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Melvyn Gomez - OpenRanger (melvyng@openranger.com)
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add(
    'tools',
    new admin_externalpage(
        'local_pluginreplace',
        get_string('pluginname', 'local_pluginreplace'),
        new moodle_url('/local/pluginreplace/index.php'),
        'local/pluginreplace:replace'
    )
);

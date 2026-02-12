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
 * Validates uploaded plugin ZIP and detects component metadata.
 *
 * @package    local_pluginreplace
 * @copyright  2026 OpenRanger S. A. de C.V. (https://openranger.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Melvyn Gomez - OpenRanger (melvyng@openranger.com)
 */

namespace local_pluginreplace;

/**
 * Validates uploaded plugin ZIP and detects component metadata.
 *
 * @package    local_pluginreplace
 */
class validator {
    /**
     * Validate uploaded ZIP file and extract plugin metadata.
     *
     * @param string $zipfile Path to uploaded ZIP file.
     * @return array Detected plugin information.
     */
    public static function validate(string $zipfile): array {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $tempdir = make_temp_directory('pluginreplace/extracted');
        fulldelete($tempdir);
        mkdir($tempdir, $CFG->directorypermissions, true);

        $zipper = new \zip_packer();
        $zipper->extract_to_pathname($zipfile, $tempdir);

        $found = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempdir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() !== 'version.php') {
                continue;
            }

            $versionfile = $file->getPathname();

            $plugin = (function ($file) {
                $plugin = new \stdClass();
                include($file);
                return $plugin ?? null;
            })($versionfile);

            if (empty($plugin->component)) {
                continue;
            }

            if (!preg_match('/^([a-z]+)_([a-z0-9_]+)$/', $plugin->component, $matches)) {
                continue;
            }

            $type = $matches[1];
            $name = $matches[2];

            $found[] = [
                'component' => $plugin->component,
                'type'      => $type,
                'name'      => $name,
                'source'    => dirname($versionfile),
            ];
        }

        if (empty($found)) {
            throw new \moodle_exception('invalidplugin', 'local_pluginreplace');
        }

        if (count($found) > 1) {
            throw new \moodle_exception('Multiple plugins detected in ZIP');
        }

        $plugin = $found[0];

        $plugininfo = \core_plugin_manager::instance()
            ->get_plugin_info($plugin['component']);

        $installed = $plugininfo ? true : false;

        $target = $CFG->dirroot . '/' . $plugin['type'] . '/' . $plugin['name'];

        return [
            'type'      => $plugin['type'],
            'name'      => $plugin['name'],
            'component' => $plugin['component'],
            'source'    => $plugin['source'],
            'target'    => $target,
            'installed' => $installed,
        ];
    }
}

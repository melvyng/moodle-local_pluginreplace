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
 * Generates file difference preview between installed and uploaded plugin.
 *
 * @package    local_pluginreplace
 * @copyright  2026 OpenRanger S. A. de C.V. (https://openranger.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Melvyn Gomez - OpenRanger (melvyng@openranger.com)
 */

namespace local_pluginreplace;

/**
 * Generates file difference preview between installed and uploaded plugin.
 *
 * @package    local_pluginreplace
 */
class differ {
    /**
     * Generate file difference preview.
     *
     * @param string $new Path to new plugin directory.
     * @param string $old Path to installed plugin directory.
     * @return array List of file difference descriptions.
     */
    public static function diff(string $new, string $old): array {

        $files = [];

        // NEW files.
        $newit = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($new, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($newit as $file) {
            $rel = substr($file->getPathname(), strlen($new) + 1);
            $files[$rel] = 'NEW';
        }

        // If old directory does not exist, it is a fresh install.
        if (!is_dir($old)) {
            $output = [];
            foreach ($files as $file => $status) {
                $output[] = sprintf('[%s] %s', $status, $file);
            }
            sort($output);
            return $output;
        }

        // Compare with existing files.
        $oldit = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($old, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($oldit as $file) {
            $rel = substr($file->getPathname(), strlen($old) + 1);

            if (!isset($files[$rel])) {
                $files[$rel] = 'REMOVED';
            } else {
                $files[$rel] = 'MODIFIED';
            }
        }

        $output = [];
        foreach ($files as $file => $status) {
            $output[] = sprintf('[%s] %s', $status, $file);
        }

        sort($output);
        return $output;
    }
}

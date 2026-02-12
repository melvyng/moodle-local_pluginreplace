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
 * Plugin replacer class.
 *
 * @package    local_pluginreplace
 * @copyright  2026 OpenRanger S. A. de C.V. (https://openranger.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Melvyn Gomez - OpenRanger (melvyng@openranger.com)
 */

namespace local_pluginreplace;

/**
 * Handles plugin installation and replacement logic.
 *
 * @package local_pluginreplace
 */
class replacer {
    /**
     * Install or replace a plugin from a ZIP file.
     *
     * @param string $zipfile Path to uploaded ZIP file.
     * @return void
     */
    public static function replace(string $zipfile): void {
        global $CFG;

        $info = validator::validate($zipfile);

        $typedirectory = $CFG->dirroot . '/' . $info['type'];
        $target = $info['target'];

        if (!is_dir($typedirectory)) {
            mkdir($typedirectory, $CFG->directorypermissions, true);
        }

        // Backup if installed.
        if ($info['installed'] && is_dir($target)) {
            $backupdir = make_temp_directory(
                'pluginreplace_backup/' . $info['component']
            );

            self::recursive_copy($target, $backupdir);
            self::recursive_delete($target);
        }

        // Copy new plugin.
        self::recursive_copy($info['source'], $target);
    }

    /**
     * Recursively copy a directory.
     *
     * @param string $src Source directory path.
     * @param string $dst Destination directory path.
     * @return void
     */
    private static function recursive_copy(string $src, string $dst): void {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcpath = $src . '/' . $file;
            $dstpath = $dst . '/' . $file;

            if (is_dir($srcpath)) {
                self::recursive_copy($srcpath, $dstpath);
            } else {
                copy($srcpath, $dstpath);
            }
        }

        closedir($dir);
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir Directory path to delete.
     * @return void
     */
    private static function recursive_delete(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                self::recursive_delete($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}

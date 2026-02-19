<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upload and preview interface.
 *
 * @package    local_pluginreplace
 * @author     Melvyn Gomez - OpenRanger (melvyng@openranger.com)
 * @copyright  2025 Melvyn Gomez (https://openranger.com/)
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_capability('local/pluginreplace:replace', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/pluginreplace/index.php');
$PAGE->set_title(get_string('pluginname', 'local_pluginreplace'));
$PAGE->set_heading(get_string('pluginname', 'local_pluginreplace'));

echo $OUTPUT->header();

if (!empty($_FILES['pluginzip'])) {
    require_sesskey();

    $tempdir = make_temp_directory('pluginreplace');
    $zipfile = $tempdir . '/upload.zip';

    move_uploaded_file($_FILES['pluginzip']['tmp_name'], $zipfile);

    $result = \local_pluginreplace\validator::validate($zipfile);

    echo html_writer::tag('h3', get_string('diff', 'local_pluginreplace'));

    $diff = \local_pluginreplace\differ::diff(
        $result['source'],
        $result['target']
    );

    echo html_writer::start_tag('pre');
    foreach ($diff as $line) {
        echo s($line) . "\n";
    }
    echo html_writer::end_tag('pre');

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => 'replace.php']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'zip', 'value' => basename($zipfile)]);
    echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('replace', 'local_pluginreplace')]);
    echo html_writer::end_tag('form');
} else {
    echo html_writer::start_tag('form', [
        'method' => 'post',
        'enctype' => 'multipart/form-data',
    ]);

    echo html_writer::empty_tag('input', [
        'type' => 'file',
        'name' => 'pluginzip',
        'accept' => '.zip',
        'required' => 'required',
    ]);

    echo html_writer::empty_tag('br');
    echo html_writer::empty_tag('br');

    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey(),
    ]);

    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'value' => get_string('preview', 'local_pluginreplace'),
    ]);

    echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();

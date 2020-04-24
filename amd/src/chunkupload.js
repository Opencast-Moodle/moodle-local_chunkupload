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
 * Manage the upload in chunks.
 *
 * @package    local_chunkupload
 * @copyright  2020 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import {get_strings} from 'core/str';
import notification from 'core/notification';

let wwwRoot,
    chunkSize;

/**
 * Init function
 */
export function init(elementid, acceptedTypes, maxBytes, wwwroot, chunksize) {
    const fileinput = $('#' + elementid + "_file");
    wwwRoot = wwwroot;
    chunkSize = chunksize;
    fileinput.change(() => {
        let file = fileinput.get(0).files[0];
        let fileextension = ".";
        if (file.name.indexOf(".") !== -1) {
            let splits = file.name.split(".");
            fileextension = splits[splits.length - 1];
        }
        if (!(acceptedTypes === '*' ||
            (acceptedTypes instanceof Array && acceptedTypes.indexOf(fileextension) !== -1))) {
            fileinput.val(null);
            notifyError({key: 'invalidfiletype', component: 'core_repository', param: fileextension});
            return;
        } else if (file.size > maxBytes) {
            fileinput.val(null);
            notifyError({key: 'errorpostmaxsize', component: 'core_repository'});
            return;
        }
        let result = startUpload(file);
    });
}

function startUpload(file) {
    let end = chunkSize < file.size ? chunkSize : file.size;
    let params = {
        start: 0,
        end: end,
        length: file.size
    };
    let fileReader = new FileReader();
    let slice = file.slice(0, end);
    fileReader.readAsText(slice);
    fileReader.addEventListener('loadend', () => {
        $.post(wwwRoot + "/local/chunkupload/startupload_ajax.php?" + $.param(params), fileReader.result,
            (a, b) => {
                console.log(a, b);
            }
        );
    });
}

function notifyError(errorstring) {
    get_strings([
        {key: 'error'},
        errorstring,
        {key: 'ok'},
    ]).done(function(s) {
            notification.alert(s[0], s[1], s[2]);
        }
    ).fail(notification.exception);
}
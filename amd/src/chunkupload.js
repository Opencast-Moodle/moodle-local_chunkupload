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

/**
 * Init
 * @param {String} elementid string The id of the input element
 * @param {String|String[]} acceptedTypes The accepted Types
 * @param {int} maxBytes The maximal allowed amount of bytes
 * @param {string} wwwroot The wwwroot
 * @param {int} chunksize The chunksize in bytes
 * @param {string} browsetext Text to display when no file is uploaded.
 */
export function init(elementid, acceptedTypes, maxBytes, wwwroot, chunksize, browsetext) {
    let wwwRoot,
        chunkSize;

    let fileinput, filename, progress, progressicon, deleteicon;

    let token;

    fileinput = $('#' + elementid + "_file");
    token = $('#' + elementid).val();
    let parentelem = fileinput.next();
    filename = parentelem.find('.chunkupload-filename');
    progress = parentelem.find('.chunkupload-progress');
    progressicon = parentelem.find('.chunkupload-icon');
    deleteicon = parentelem.next();
    wwwRoot = wwwroot;
    chunkSize = chunksize;
    fileinput.change(() => {
        reset();
        let file = fileinput.get(0).files[0];
        let fileextension = ".";
        if (file.name.indexOf(".") !== -1) {
            let splits = file.name.split(".");
            fileextension = "." + splits[splits.length - 1];
        }
        if (!(acceptedTypes === '*' ||
            acceptedTypes instanceof Array && (acceptedTypes.indexOf(fileextension) !== -1) || acceptedTypes.indexOf('*') !== 1)) {
            fileinput.val(null);
            notifyError({key: 'invalidfiletype', component: 'core_repository', param: fileextension});
            return;
        } else if (maxBytes !== -1 && file.size > maxBytes) {
            fileinput.val(null);
            notifyError({key: 'errorpostmaxsize', component: 'core_repository'});
            return;
        }
        filename.text(file.name);
        startUpload(file);
    });

    deleteicon.on('click', (event) => {
        reset();
        let params = {
            id: token
        };
        let xhr = new XMLHttpRequest();
        xhr.open('post', wwwRoot + "/local/chunkupload/deleteupload_ajax.php?" + $.param(params));
        xhr.send(null);
        filename.text(browsetext);
        fileinput.val(null);
        event.stopPropagation();
    });

    /**
     * Start the Upload
     * @param {File} file The File to upload.
     */
    function startUpload(file) {
        let end = chunkSize < file.size ? chunkSize : file.size;
        let params = {
            start: 0,
            end: end,
            length: file.size,
            filename: file.name,
            id: token
        };
        let slice = file.slice(0, end);
        let xhr = new XMLHttpRequest();
        xhr.open('post', wwwRoot + "/local/chunkupload/startupload_ajax.php?" + $.param(params));
        xhr.upload.onprogress = (e) => {
            setProgress(e.loaded, file.size);
        };
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    if (response.error !== undefined) {
                        notifyError(response.error);
                    } else {
                        if (end < file.size) {
                            proceedUpload(file, chunkSize);
                        }
                    }
                }
            }
        };
        xhr.setRequestHeader('Content-Type', 'application/octet-stream');
        xhr.send(slice);
    }

    /**
     * Proceed the upload
     * @param {File} file
     * @param {int} start from where to proceed the upload.
     */
    function proceedUpload(file, start) {
        let end = start + chunkSize < file.size ? start + chunkSize : file.size;
        let params = {
            start: start,
            end: end,
            id: token
        };
        let slice = file.slice(start, end);
        let xhr = new XMLHttpRequest();
        xhr.open('post', wwwRoot + "/local/chunkupload/proceedupload_ajax.php?" + $.param(params));
        xhr.upload.onprogress = (e) => {
            setProgress(e.loaded + start, file.size);
        };
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    if (response.error !== undefined) {
                        notifyError(response.error);
                    } else {
                        if (end < file.size) {
                            proceedUpload(file, end);
                        }
                    }
                }
            }
        };
        xhr.onerror = () => {
            reset();
            // Doesn't make sense to try to fetch strings when having internet problems.
            notification.alert("Error", "Failure while uploading!", "Ok");
        };
        xhr.setRequestHeader('Content-Type', 'application/octet-stream');
        xhr.send(slice);
    }

    /**
     * Resets the Progress and the Filepicker name.
     */
    function reset() {
        setProgress(0, 1);
        filename.text("");
    }

    /**
     * Sets the progressbar
     * @param {int} loaded
     * @param {int} total
     */
    function setProgress(loaded, total) {
        if (loaded === total) {
            // Hide progressbar on finish.
            progress.css('width', '0');
        } else {
            progress.css('width', loaded * 100 / total + "%");
        }
        progressicon.prop('hidden', loaded !== total);
        deleteicon.prop('hidden', loaded !== total);
    }

    /**
     * Notify error
     * @param {object|string} errorstring Either Object as accepted by get_string, or a string, to describe the error.
     */
    function notifyError(errorstring) {
        reset();
        if (typeof errorstring === "string") {
            get_strings([
                {key: 'error'},
                {key: 'ok'},
            ]).done(function(s) {
                    notification.alert(s[0], errorstring, s[1]);
                }
            ).fail(notification.exception);
        } else {
            get_strings([
                {key: 'error'},
                errorstring,
                {key: 'ok'},
            ]).done(function(s) {
                    notification.alert(s[0], s[1], s[2]);
                }
            ).fail(notification.exception);
        }
    }
}
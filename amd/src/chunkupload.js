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
    chunkSize,
    elementId;

let fileinput, filename, progress, progressicon;
/**
 * Init function
 */
export function init(elementid, acceptedTypes, maxBytes, wwwroot, chunksize) {
    fileinput = $('#' + elementid + "_file");
    let parent = fileinput.next();
    filename = parent.find('.chunkupload-filename');
    progress = parent.find('.chunkupload-progress');
    progressicon = parent.find('.chunkupload-icon');
    wwwRoot = wwwroot;
    chunkSize = chunksize;
    elementId = elementid;
    fileinput.change(() => {
        let file = fileinput.get(0).files[0];
        let fileextension = ".";
        if (file.name.indexOf(".") !== -1) {
            let splits = file.name.split(".");
            fileextension = "." + splits[splits.length - 1];
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
        filename.text(file.name);
        startUpload(file);
    });
}

function startUpload(file) {
    let end = chunkSize < file.size ? chunkSize : file.size;
    let params = {
        start: 0,
        end: end,
        length: file.size,
        filename: file.name
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
                        proceedUpload(file, chunkSize, response.fid, response.continuetoken);
                    } else {
                        setFileId(response.fid);
                    }
                }
            }
        }
    };
    xhr.setRequestHeader('Content-Type', 'application/octet-stream');
    xhr.send(slice);
}

function proceedUpload(file, start, fileid, continuetoken) {
    let end = start + chunkSize < file.size ? start + chunkSize : file.size;
    let params = {
        start: start,
        end: end,
        continuetoken: continuetoken,
        fileid: fileid
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
                        proceedUpload(file, end, fileid, response.continuetoken);
                    } else {
                        setFileId(fileid);
                    }
                }
            }
        }
    };
    xhr.setRequestHeader('Content-Type', 'application/octet-stream');
    xhr.send(slice);
}

function reset() {
    setProgress(0, 1);
    setFileId(null);
    filename.text("");
}

function setProgress(loaded, total) {
    progress.css('width', loaded * 100 / total + "%");
    progressicon.prop('hidden', loaded !== total);
}

function setFileId(fileId) {
    $('#' + elementId).val(fileId);
}

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
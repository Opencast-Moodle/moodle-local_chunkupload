Chunk upload moodleform-element (moodle-local_chunkupload)
=====================

This plugin offers a new form element for moodle forms.
The form element is a file upload field, which uploads one file as multiple small chunks.
While uploading the form element shows a progress bar to the user.
The advantages of this form element is, that the maximum php upload limit (`post_max_size` and `upload_max_filesize`), 
specified within the php.ini, do no restrict uploads with this form element.
Also the upload is not processed within only one transaction, but multiple small ones.
Second, the form element is not directly coupled to the moodle file size limits, which are a good thing in general,
but can not be circumvented for special cases.

The main purpose of this development is the usage within the plugin [block_opencast](https://github.com/unirz-tu-ilmenau/moodle-block_opencast).
The plugin allows the teachers of a course to upload large video files to moodle, which are later transfered to the opencast video platform.
Since moodle only serves as a temporary storage and the files are very large, it is required, that the general upload size limits should not apply.

![image](https://user-images.githubusercontent.com/9437254/92386238-b0235680-f113-11ea-80ea-885387008936.png)
Picture of the chunkupload while uploading a file.    

Installation
------------

To install the plugin you have to place the content of this repository into local/chunkupload.

`git clone https://github.com/learnweb/moodle-local_chunkupload.git local/chunkupload.`
 
Usage
-----

To use the form element within one of your plugins, you first have to register the form element as a quickform element:

```php
\MoodleQuickForm::registerElementType('chunkupload',
    "$CFG->dirroot/local/chunkupload/classes/chunkupload_form_element.php",
    'local_chunkupload\chunkupload_form_element');
```

Afterwards you can use the specified element name to create a form element of the chunkupload:

```php
$mform->addElement('chunkupload', 'video_chunk', get_string('myvideo', 'myplugin'), null,
        array('maxbytes' => $maxuploadsize, 'accepted_types' => $videotypes));
```

The form element takes in two parameters, which can specify the maximum upload size (`maxbytes`) and the accepted filetypes (`accepted_types`). 

## License ##

This plugin is developed in cooperation with the TU Ilmenau and the WWU Münster.

It is based on 2017 Andreas Wagner, SYNERGY LEARNING

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.

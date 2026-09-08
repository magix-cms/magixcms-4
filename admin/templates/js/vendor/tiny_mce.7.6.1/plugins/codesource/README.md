Plugin CodeSource (CodeMirror 6) for TinyMCE 7
======================

Replace the default TinyMCE source code modal with a powerful, modern CodeMirror 6 editor. Includes automatic HTML beautifier, syntax highlighting, OneDark theme, and Shadow DOM isolation to prevent CSS conflicts.

### version
[![release](https://img.shields.io/github/release/gtraxx/tinymce-codesource.svg)](https://github.com/magix-cms/tinymce-plugin-codesource/releases/latest)
![Version TinyMCE](https://img.shields.io/badge/TinyMCE-7-blue)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Statut](https://img.shields.io/badge/projet-Open%20Source-orange)

## Soutenir le projet

Si vous souhaitez soutenir le développement, vous pouvez faire un don via PayPal :

[![Faire un don](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://paypal.me/aurelienstireg)

Authors
-------

* Gerits Aurelien (Author-Developer) aurelien[at]magix-cms[point]com

### Features

* **CodeMirror 6 Engine**: Lightweight and blazing fast rendering.
* **OneDark Theme**: Elegant dark mode for syntax highlighting (HTML, CSS, JS inline).
* **Auto-Beautifier**: Automatically formats and indents TinyMCE's minified HTML output.
* **Shadow DOM Isolation**: Protects the editor's syntax colors from aggressive TinyMCE modal CSS resets.

### Screenshot

<img width="1208" height="661" alt="Image" src="https://github.com/user-attachments/assets/db449e3d-f7c2-48ac-9b3d-f28173b942c0" />

### Installation
* Download the `dist/codesource.zip` archive.
* Unzip archive in tinyMCE plugin directory (`tiny_mce/plugins/codesource/`).
* Ensure `codemirror.bundle.js` and `plugin.min.js` are present in the directory.

### Configuration
```html
<script type="text/javascript">
    tinymce.init({
        selector: "textarea",
        // Do not include the default 'code' plugin, use 'codesource' instead
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks fullscreen",
            "insertdatetime media table contextmenu paste codesource"
        ],
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | codesource",
    });
</script>
```

### Licence
<pre>
This file is part of tinyMCE.
YouTube for tinyMCE
Copyright (C) 2011 - 2026  Gerits Aurelien <aurelien[at]magix-cms[dot]com>

Redistributions of files must retain the above copyright notice.
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see .

####DISCLAIMER

Do not edit or add to this file if you wish to upgrade jimagine to newer
versions in the future. If you wish to customize jimagine for your
needs please refer to magix-dev.be for more information.
</pre>

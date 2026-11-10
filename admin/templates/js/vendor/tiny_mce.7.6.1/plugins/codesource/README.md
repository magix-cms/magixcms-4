Plugin CodeSource (CodeMirror 6) for TinyMCE 7
======================

Replace the default TinyMCE source code modal with a powerful, modern CodeMirror 6 editor. Includes safe automatic HTML beautifier, syntax highlighting on a clean light theme, integrated search, and Shadow DOM isolation to prevent CSS conflicts.

### version
[![release](https://img.shields.io/github/release/gtraxx/tinymce-codesource.svg)](https://github.com/gtraxx/tinymce-codesource/releases/latest)
![Version TinyMCE](https://img.shields.io/badge/TinyMCE-7-blue)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Statut](https://img.shields.io/badge/projet-Open%20Source-orange)

<img width="1200" height="741" alt="Image" src="https://github.com/user-attachments/assets/3ba8c1a4-3b05-4ceb-bea8-91fdd5099ff8" />

## Soutenir le projet

Si vous souhaitez soutenir le développement, vous pouvez faire un don via PayPal :

[![Faire un don](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://paypal.me/aurelienstireg)

Authors
-------

* Gerits Aurelien (Author-Developer) aurelien[at]magix-cms[point]com

### Key Features

* **CodeMirror 6 Engine**: Lightweight, modular, and blazing-fast performance.
* **Clean Light Theme**: High-contrast syntax highlighting on a pure white background with styled line-number gutters, matching modern CMS administration interfaces.
* **Safe HTML Auto-Beautifier**: Automatically indents standard markup while strictly preserving whitespace and formatting inside sensitive blocks (`<pre>`, `<code>`, `codesample`, `<script>`, `<style>`, `<textarea>`) and self-closing tags (`<path />`, `<source />`, etc.).
* **Integrated Search & Replace**: Instant panel access via `Ctrl + F` (`Cmd + F`) or the dedicated dialog search action button.
* **Shadow DOM Isolation**: Shields the CodeMirror 6 canvas from TinyMCE 7's aggressive modal CSS resets.
* **Cross-Platform & Fullscreen Stability**: Absolute viewport container prevents top clipping on Windows and guarantees independent vertical scrolling even with large documents or full-screen editing.

### Screenshot

<img width="1244" height="696" alt="Plugin codesource tinyMce" src="https://github.com/user-attachments/assets/4a610d5e-c716-43fb-a095-ae556432345c" />

### Installation
* Download the `dist/codesource.zip` archive or clone this repository.
* Extract the folder into your TinyMCE plugins directory (`tinymce/plugins/codesource/`).
* Ensure `codemirror.bundle.js` and `plugin.min.js` (or `plugin.js`) are present in the directory.

### Configuration
```html
<script type="text/javascript">
    tinymce.init({
        selector: "textarea",
        // Replace the default 'code' plugin with 'codesource'
        plugins: [
            "advlist autolink lists link image charmap preview anchor",
            "searchreplace visualblocks fullscreen",
            "insertdatetime media table codesource"
        ],
        toolbar: "undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image | codesource",
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

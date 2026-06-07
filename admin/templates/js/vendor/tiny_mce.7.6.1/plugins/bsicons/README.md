# Bootstrap Icons Fetcher for TinyMCE 7

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![TinyMCE](https://img.shields.io/badge/TinyMCE-7.x-brightgreen.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap_Icons-1.11.3+-purple.svg)
![License](https://img.shields.io/badge/license-GPLv3-lightgrey.svg)

A lightweight, standalone, and agnostic TinyMCE 7 plugin that allows users to browse, search, and insert Bootstrap Icons directly within the editor interface. It automatically fetches the full collection of icons dynamically from a robust PHP backend bridge with built-in local caching.

This plugin is designed to be plug-and-play and works seamlessly across any platform (WordPress, custom applications, and natively integrated within Magix CMS 4).

## Features

- **Native TinyMCE 7 UI:** Uses TinyMCE's native `windowManager` dialogs, ensuring perfect visual blending with both light and dark editor skins without breaking or conflicting with host CSS.
- **Dynamic CSS Auto-Injection:** Automatically injects the Bootstrap Icons CSS into the TinyMCE iframe context and the main document head upon initialization.
- **PHP Dynamic Bridge:** Automatically parses the official Bootstrap Icons repository to fetch all available classes prefixing with `bi-`. No manual updates required when new icons are released!
- **Intelligent Local Cache:** Caches the parsed icon collection locally in a JSON file to minimize bandwidth and guarantee ultra-fast modal rendering.
- **Multi-Instance Safe:** Implements a global event delegation pattern to prevent click duplications, even when multiple language tabs or multiple editor instances are open on the same page.
- **Professional SVG Branding:** Custom brand icon replaces TinyMCE's fallback icon with the official Bootstrap logo.
- **i18n Support:** Fully translatable using TinyMCE's native language pack structure.

## Directory Structure

```text
tinymce/
└── plugins/
    └── bsicons/
        ├── langs/
        │   └── fr_FR.js
        ├── plugin.js
        └── bridge.php
```

## Installation

### 1. Deploy Files
Copy the `bsicons` folder containing `plugin.js`, `bridge.php`, and the `langs/` directory into your TinyMCE installation's `plugins` directory.

### 2. Configure Directory Permissions
Ensure that the `bsicons` directory is writeable by your web server (`chmod 755` or `775` depending on your server environment). The `bridge.php` script requires write access to generate the local `icons_cache.json` cache file.

### 3. Initialize TinyMCE

Add `bsicons` to your `plugins` array and toolbar configuration.

> **Crucial Security Rule:** TinyMCE 7 strips empty semantic elements like `<i class="bi bi-star"></i>` by default. You **MUST** add the `extended_valid_elements` property shown below to protect your icons from being wiped out when toggling source code views or saving content.

```javascript
tinymce.init({
    selector: '.mceEditor',
    language: 'fr_FR',
    
    // 1. Register the plugin
    plugins: 'code link image lists bsicons',
    
    // 2. Place the button in your toolbar configuration
    toolbar: 'undo redo | blocks styles | bold italic | bsicons | code',
    
    // 3. CRUCIAL Configuration: Prevent TinyMCE from deleting empty icon tags
    extended_valid_elements: 'i[class|id|style|title|data-*],span[class|id|style|title|data-*]',
    
    // Optional layout constraints
    toolbar_mode: 'sliding'
});
```

## Translations (i18n)

The plugin uses native `editor.translate()`. To add a new language, simply create a `{language_code}.js` file inside the `langs/` directory.

Example for `langs/fr_FR.js`:
```javascript
tinymce.addI18n('fr_FR', {
    'Insert Bootstrap Icons': 'Insérer une icône Bootstrap',
    'Loading icons...': 'Chargement des icônes en cours...',
    'No icon found or loading...': 'Aucune icône trouvée ou en cours de chargement...',
    'Search (e.g., star, check, user)...': 'Rechercher (ex: star, check, user)...',
    'Close': 'Fermer',
    'Bootstrap Icons': 'Icônes Bootstrap'
});
```

## Configuration & Updates

The PHP bridge script (`bridge.php`) connects to the official JSDelivr CDN to look up the current layout of Bootstrap Icons.

- **Updating the Bootstrap Icons Version:** Open `bridge.php` and update the `$cssUrl` string to target a newer version (e.g., changing `@1.11.3` to a newer tag release).
- **Clearing Cache:** To force a re-sync of the icons list, simply delete the generated `icons_cache.json` file inside the plugin folder. The bridge will automatically regenerate it on the next user click.

## License & Copyright

La licence est GPLv3.
Le copyright est (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
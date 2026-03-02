/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 # (Ton bloc de licence conservé)
 # -- END LICENSE BLOCK -----------------------------------
 */

// On s'assure que le pack de langue est chargé (s'il existe)
tinymce.PluginManager.requireLangPack('mc_cat');

/**
 * MAGIX CMS - Category Linker
 * Version 2.0.0 - Compatible TinyMCE 7 (Vanilla JS)
 */
tinymce.PluginManager.add('mc_cat', function(editor, url) {

    const _ = (text) => editor.translate(text);

    // 1. LE FIX EST ICI : SVG en dur (Inline)
    // Remplace le contenu du 'd="..."' par celui de ton icône "category" dans mc_icons.svg
    const categorySvg = `<svg width="24" height="24" viewBox="0 0 24 24">
        <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" fill-rule="nonzero"/>
    </svg>`;

    // Enregistrement de l'icône dans le registre de l'interface
    editor.ui.registry.addIcon('category', categorySvg);

    /* 2. Dialogue pour choisir une catégorie */
    const showDialog = () => {
        // En TinyMCE 7, openUrl est parfait pour les iframes personnalisées
        editor.windowManager.openUrl({
            title: _('mc_cat Title') || 'Insérer une catégorie',
            // url: utilise dynamiquement baseadmin si dispo, sinon le chemin relatif
            url: (typeof baseadmin !== 'undefined')
                ? '/' + baseadmin + '/plugins/mc_cat/cat.php'
                : url + '/cat.php',
            width: 800,
            height: 550
        });
    };

    // 3. Bouton barre d'outils
    editor.ui.registry.addButton('mc_cat', {
        icon: 'category',
        tooltip: _('mc_cat Tooltip') || 'Lien vers une catégorie',
        onAction: showDialog,
        // onSetup est parfait ici pour gérer l'état activé/désactivé
        onSetup: (api) => {
            const nodeChangeHandler = (e) => {
                // Par exemple, on désactive le bouton si on sélectionne une image
                api.setEnabled(e.element.nodeName !== 'IMG');
            };
            editor.on('NodeChange', nodeChangeHandler);
            return () => editor.off('NodeChange', nodeChangeHandler);
        }
    });

    // 4. Menu "Insertion"
    editor.ui.registry.addMenuItem('mc_cat', {
        icon: 'category',
        text: _('mc_cat Title') || 'Catégorie',
        onAction: showDialog
    });
});
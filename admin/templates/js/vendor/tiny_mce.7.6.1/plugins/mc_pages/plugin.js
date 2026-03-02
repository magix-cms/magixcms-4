/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 # (Ton bloc de licence conservé)
 # -- END LICENSE BLOCK -----------------------------------
 */

tinymce.PluginManager.requireLangPack('mc_pages');

/**
 * MAGIX CMS - Pages Linker
 * Version 2.0.0 - Compatible TinyMCE 7 (Vanilla JS)
 */
tinymce.PluginManager.add('mc_pages', function(editor, url) {

    const _ = (text) => editor.translate(text);

    // 1. LE FIX SVG INLINE :
    // Remplace le <path> par celui de ton id="page" dans mc_icons.svg
    const pageSvg = `<svg width="24" height="24" viewBox="0 0 24 24">
        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="currentColor"/>
    </svg>`;

    // Enregistrement de l'icône dans le registre
    editor.ui.registry.addIcon('page', pageSvg);

    /* 2. Ouverture du dialogue */
    const showDialog = () => {
        editor.windowManager.openUrl({
            title: _('mc_pages Title') || 'Insérer une page',
            // Utilisation de baseadmin au lieu de tinymce.baseURL
            url: (typeof baseadmin !== 'undefined')
                ? '/' + baseadmin + '/plugins/mc_pages/pages.php'
                : url + '/pages.php',
            width: 800,
            height: 550
        });
    };

    // 3. Bouton de barre d'outils
    editor.ui.registry.addButton('mc_pages', {
        icon: 'page',
        tooltip: _('mc_pages Tooltip') || 'Lien vers une page',
        onAction: showDialog,
        onSetup: (buttonApi) => {
            // Désactiver le bouton si une image est sélectionnée
            const nodeChangeHandler = (eventApi) => {
                buttonApi.setEnabled(eventApi.element.nodeName !== 'IMG');
            };
            editor.on('NodeChange', nodeChangeHandler);
            return () => editor.off('NodeChange', nodeChangeHandler);
        }
    });

    // 4. Ajout au menu Insertion
    editor.ui.registry.addMenuItem('mc_pages', {
        icon: 'page',
        text: _('mc_pages Title') || 'Page interne',
        onAction: showDialog
    });

    // 5. Metadata de ton plugin (très bien vu !)
    return {
        getMetadata: () => ({
            name: "Magix CMS Pages",
            author: "Gerits Aurelien",
            version: "2.0.0"
        })
    };
});
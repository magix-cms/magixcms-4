/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 # (Ton bloc de licence conservé)
 # -- END LICENSE BLOCK -----------------------------------
 */

tinymce.PluginManager.requireLangPack('mc_news');

/**
 * MAGIX CMS - News Linker
 * Version 2.0.0 - Compatible TinyMCE 7 (Vanilla JS)
 */
tinymce.PluginManager.add('mc_news', function(editor, url) {

    const _ = (text) => editor.translate(text);

    // 1. LE FIX SVG INLINE :
    // Remplace le contenu de <path> par celui de ton id="news" dans mc_icons.svg
    const newsSvg = `<svg width="24" height="24" viewBox="0 0 24 24">
        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" fill="currentColor"/>
    </svg>`;

    // Enregistrement de l'icône dans le registre
    editor.ui.registry.addIcon('news', newsSvg);

    /* 2. Fonction pour ouvrir le sélecteur d'actualités */
    const showDialog = () => {
        editor.windowManager.openUrl({
            title: _('mc_news Title') || 'Insérer une actualité',
            // Utilisation sécurisée de baseadmin pour cibler le bon dossier
            url: (typeof baseadmin !== 'undefined')
                ? '/' + baseadmin + '/plugins/mc_news/news.php'
                : url + '/news.php',
            width: 800,
            height: 550
        });
    };

    // 3. Bouton de barre d'outils
    editor.ui.registry.addButton('mc_news', {
        icon: 'news',
        tooltip: _('mc_news Tooltip') || 'Lien vers une actualité',
        onAction: showDialog,
        onSetup: (buttonApi) => {
            // Désactivation si sélection d'image
            const nodeChangeHandler = (eventApi) => {
                buttonApi.setEnabled(eventApi.element.nodeName !== 'IMG');
            };
            editor.on('NodeChange', nodeChangeHandler);
            return () => editor.off('NodeChange', nodeChangeHandler);
        }
    });

    // 4. Option dans le menu "Insertion"
    editor.ui.registry.addMenuItem('mc_news', {
        icon: 'news',
        text: _('mc_news Title') || 'Actualité',
        onAction: showDialog
    });
});
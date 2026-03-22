/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 # ...
 # -- END LICENSE BLOCK -----------------------------------
 */

tinymce.PluginManager.requireLangPack('mc_news');

tinymce.PluginManager.add('mc_news', function(editor, url) {

    const _ = (text) => editor.translate(text);

    const newsSvg = `<svg width="24" height="24" viewBox="0 0 24 24">
        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" fill="currentColor"/>
    </svg>`;

    editor.ui.registry.addIcon('news', newsSvg);

    const showDialog = () => {
        // 🟢 1. EXTRACTION DE L'ID DE LANGUE
        // editor.id renvoie par exemple "content_2". On le coupe pour récupérer "2"
        const parts = editor.id.split('_');
        const langId = parts.length > 1 ? parts[parts.length - 1] : '';
        const langParam = langId ? '&lang_id=' + langId : '';

        // 🟢 2. AJOUT DU PARAMÈTRE DANS L'URL
        const popupUrl = (typeof baseadmin !== 'undefined')
            ? '/' + baseadmin + '/index.php?controller=News&action=tinymcePopup' + langParam
            : '/admin/index.php?controller=News&action=tinymcePopup' + langParam;

        editor.windowManager.openUrl({
            title: _('mc_news Title') || 'Insérer une actualité',
            url: popupUrl,
            width: 800,
            height: 550
        });
    };

    editor.ui.registry.addButton('mc_news', {
        icon: 'news',
        tooltip: _('mc_news Tooltip') || 'Lien vers une actualité',
        onAction: showDialog,
        onSetup: (buttonApi) => {
            const nodeChangeHandler = (eventApi) => {
                buttonApi.setEnabled(eventApi.element.nodeName !== 'IMG');
            };
            editor.on('NodeChange', nodeChangeHandler);
            return () => editor.off('NodeChange', nodeChangeHandler);
        }
    });

    editor.ui.registry.addMenuItem('mc_news', {
        icon: 'news',
        text: _('mc_news Title') || 'Actualité',
        onAction: showDialog
    });
});
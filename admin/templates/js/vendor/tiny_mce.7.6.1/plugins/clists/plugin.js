/**
 * TinyMCE clists Plugin (Custom Lists)
 * Version 3.0.0 - Compatible TinyMCE 7 & Options API
 */
(function () {
    'use strict';
    tinymce.PluginManager.requireLangPack("clists");
    tinymce.PluginManager.add('clists', function (editor) {

        const _ = (text) => editor.translate(text);

        // 1. Enregistrement de l'option "cbullet_styles" pour la lire depuis tinymce.init
        editor.options.register('cbullet_styles', {
            processor: 'array',
            default: [
                {title: 'Default', style: 'disc'},
                {title: 'Circle', style: 'circle'},
                {title: 'Square', style: 'square'},
                {title: 'Bullet List', classes: 'bullet-list'},
                {title: 'Circle List', classes: 'circle-list'},
                {title: 'Square List', classes: 'square-list'},
                {title: 'Arrow List', classes: 'arrow-list'},
                {title: 'Label List', classes: 'label-list'}
            ]
        });

        // Fonction pour récupérer la configuration active
        const getBulletStyles = () => editor.options.get('cbullet_styles');

        // Récupère uniquement les classes CSS pour pouvoir les nettoyer
        const getAllClasses = () => {
            return getBulletStyles()
                .filter(s => typeof s === 'object' && s.classes)
                .map(s => s.classes);
        };

        // Logique d'application
        const applyFormat = (value) => {
            const styles = getBulletStyles();
            let format = styles.find(s => s.title === value);
            if (!format) format = { style: 'disc' };

            // Exécute la commande native pour forcer la création du <ul>
            editor.execCommand('InsertUnorderedList', false, format.style ? { 'list-style-type': format.style } : null);

            // Cible l'élément <ul> actif
            const listElm = editor.dom.getParent(editor.selection.getNode(), 'ul');

            if (listElm) {
                // Nettoyage de TOUTES les classes gérées par le plugin
                getAllClasses().forEach(cls => {
                    if (cls) editor.dom.removeClass(listElm, cls);
                });

                // Si on applique un format "Classe CSS" (ex: Arrow List)
                if (format.classes) {
                    editor.dom.addClass(listElm, format.classes);
                    editor.dom.setStyle(listElm, 'list-style-type', ''); // Retire le style natif pour laisser CSS agir
                }
                // Si on applique un format "Natif" (ex: Circle)
                else if (format.style) {
                    editor.dom.setStyle(listElm, 'list-style-type', format.style);
                }
            }
        };

        // 2. Création du bouton avec le nom "cbullist" (Custom Bullet List)
        editor.ui.registry.addSplitButton('cbullist', {
            icon: 'unordered-list',
            tooltip: _('Bullet list'),
            onAction: () => editor.execCommand('InsertUnorderedList'),
            onItemAction: (api, value) => applyFormat(value),
            fetch: (callback) => {
                const styles = getBulletStyles();
                const menuItems = styles.map(s => ({
                    type: 'choiceitem',
                    value: s.title,
                    text: _(s.title)
                }));
                callback(menuItems);
            },
            onSetup: (api) => {
                const nodeChangeHandler = () => {
                    const listElm = editor.dom.getParent(editor.selection.getNode(), 'ul');
                    api.setActive(!!listElm);
                };
                editor.on('NodeChange', nodeChangeHandler);
                return () => editor.off('NodeChange', nodeChangeHandler);
            }
        });

        return { getMetadata: () => ({ name: "Clists", version: "3.0.0" }) };
    });
})();
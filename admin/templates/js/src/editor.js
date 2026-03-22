/**
 * Configuration TinyMCE 7 pour MagixCMS 4
 * Version Vanilla JS (Sans jQuery)
 */
(function (window, document) {
    // 1. Gestion de la langue
    let tinyLanguage;
    const currentIso = (typeof iso !== 'undefined') ? iso : 'en';

    switch(currentIso){
        case 'fr': tinyLanguage = 'fr_FR'; break;
        case 'en': tinyLanguage = 'en_US'; break;
        default : tinyLanguage = currentIso; break;
    }

    // 2. Plugins standards et Magix
    let magixPlugins = [
        'advlist', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
        'searchreplace', 'visualblocks', 'code', 'fullscreen', 'wordcount', 'directionality',
        'media', 'table', 'codesample', 'accordion'
    ];

    // Plugins externes spécifiques à Magix
    // Note : TinyMCE 7 gère mieux certains plugins en 'external'
    const magixCustomPlugins = [
        'youtube', 'loremipsum', /*'responsivefilemanager',*/ 'mc_pages',
        'mc_cat', 'mc_news', 'mc_product', 'lazyloadimage', 'cryptmail',
        'tabpanel', 'snippets', 'advreplace', 'mc_history'
    ];
    magixPlugins = magixPlugins.concat(magixCustomPlugins);

    // 3. Toolbar
    let magixToolbar = 'undo redo | link unlink image code advreplace | blocks | '
        +'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | '
        +'bullist numlist | blockquote | removeformat forecolor | mc_pages mc_cat mc_news mc_product | fullscreen';

    // 4. Menus
    let magixMenu = {
        view   : {title : 'View'  , items : 'code | visualaid visualblocks | preview fullscreen'},
        edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall | searchreplace'},
        insert : {title : 'Insert', items : 'link anchor | snippets | image media youtube | tabpanel | table | hr | loremipsum | codesample'},
        format : {title : 'Format', items : 'styles | lazyloadimage cryptmail'},
        table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'},
        tools  : {title : 'Tools' , items : 'code advreplace mc_history'}
    };

    // 5. Intégration AI Gemini si activé
    if (window.MagixCMS && window.MagixCMS.ai_enabled) {
        magixPlugins.push('mc_ai_gemini');
        magixToolbar += ' | mc_ai_gemini';
        magixMenu.insert.items += ' | mc_ai_gemini';
        magixMenu.tools.items += ' mc_ai_gemini';
    }

    // 6. INITIALISATION VANILLA
    tinymce.init({
        selector: '.mceEditor', // Cible tous les textarea avec cette classe
        license_key: 'gpl',
        promotion: false,
        branding: false,
        language : tinyLanguage,
        min_height: 500,
        autoresize_bottom_margin: 20,
        relative_urls: false,
        remove_script_host: true,
        entity_encoding : "raw",
        schema: "html5",

        plugins: magixPlugins,
        toolbar: magixToolbar,
        menu: magixMenu,
        menubar: 'view edit insert format table tools',
        toolbar_mode: 'sliding',

        // Configuration Responsive Filemanager
        /*external_filemanager_path: `/${baseadmin}/template/js/vendor/filemanager/`,
        filemanager_title: "Responsive Filemanager",
        external_plugins: {
            "responsivefilemanager" : `/${baseadmin}/template/js/vendor/filemanager/plugin.min.js`,
            "filemanager" : `/${baseadmin}/template/js/vendor/filemanager/plugin.min.js`
        },*/
        // Activation du bouton "Parcourir" pour les images, médias et liens
        file_picker_types: 'file image media',

        // Le callback qui ouvre elFinder
        file_picker_callback: function (callback, value, meta) {

            const elfinderUrl = '/' + baseadmin + '/templates/js/vendor/elfinder/elfinder.html';

            const elfinderDialog = tinymce.activeEditor.windowManager.openUrl({
                title: 'MagixMedia',
                url: elfinderUrl,
                width: 1200,
                height: 700,
                resizable: true,
                onMessage: function (dialogApi, details) {
                    if (details.mceAction === 'insertFile') {
                        callback(details.content);
                        dialogApi.close();
                    }
                }
            });

            // --- LE FIX SÉCURITÉ ---
            // On cible l'iframe de la modal TinyMCE pour lui donner les droits
            setTimeout(() => {
                const iframe = document.querySelector('.tox-dialog__body-iframe iframe');
                if (iframe) {
                    // Autorisation ancienne génération
                    iframe.setAttribute('allowfullscreen', 'true');
                    // Autorisation moderne (Politique de permissions)
                    iframe.setAttribute('allow', 'fullscreen');
                }
            }, 300); // Un délai court suffit
        },
        // Snippets
        snippets_url: '/'+baseadmin+'/index.php?controller=Snippet&action=tinymce',

        // Design & Styles Bootstrap 5
        table_default_attributes: { class: 'table' },
        image_advtab: true,
        image_dimensions: true,

        style_formats: [
            {title: 'Link', items: [
                    {title: 'TargetBlank', selector: 'a', classes: 'targetblank'}
                ]},
            {title: 'Buttons', items: [
                    {title: 'Btn Main', selector: 'a', classes: 'btn btn-main'},
                    {title: 'Btn Outline', selector: 'a', classes: 'btn btn-main-outline'},
                    {title: 'Btn Glass', selector: 'a', classes: 'btn btn-main-glass'}
                ]},
            {title: 'Image', items: [
                    {title: 'Image Responsive', selector: 'img', classes: 'img-fluid'},
                    {title: 'Image Rounded', selector: 'img', classes: 'rounded'},
                    {title: 'Image Circle', selector: 'img', classes: 'rounded-circle'},
                    {title: 'Image Thumbnail', selector: 'img', classes: 'img-thumbnail'}
                ]},
            {title: 'Alerts', items: [
                    {title: 'Alert Success', block: 'div', classes: 'alert alert-success'},
                    {title: 'Alert Info', block: 'div', classes: 'alert alert-info'},
                    {title: 'Alert Warning', block: 'div', classes: 'alert alert-warning'},
                    {title: 'Alert Danger', block: 'div', classes: 'alert alert-danger'}
                ]}
        ],

        // Nettoyage des styles invalides (Bootstrap gère via les classes)
        invalid_styles: {
            'table': 'width height border border-collapse border-width',
            'tr' : 'width height',
            'th' : 'width height',
            'td' : 'width height'
        },

        // Sécurité éléments HTML
        extended_valid_elements: "+img[class|src|srcset|sizes|alt|title|hspace|vspace|width|height|align|name|loading],+svg[*],+g[*],+path[*],+span[*],+i[*],+div[*],+ul[*],+li[*],+iframe[*],+strong[*]",

        // CSS de contenu (Frontend)
        content_css : (typeof contentCSS !== 'undefined') ? contentCSS : '',

        // Synchronisation avec MagixForms
        fullscreen_native: true, // Utilise l'API Fullscreen du navigateur si possible
        sticky_toolbar: true,
        toolbar_sticky_offset: 0,
        setup: function (editor) {
            // On synchronise à chaque modification (frappe, copier-coller, etc.)
            editor.on('change input undo redo', function () {
                editor.save();
            });

            // Gestion du scroll lors du plein écran
            editor.on('FullscreenStateChanged', function (e) {
                if (e.state) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });

})(window, document);
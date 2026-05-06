/**
 * Configuration TinyMCE 7 pour MagixCMS 4
 * Version Vanilla JS (Sans jQuery) - Compatible Bootstrap 5 & GLightbox
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

    const magixCustomPlugins = [
        'youtube', 'loremipsum', 'mc_pages',
        'mc_cat', 'mc_news', 'mc_product', 'lazyloadimage', 'cryptmail',
        'tabpanel', 'snippets', 'advreplace', 'mc_history', 'clists'
    ];
    magixPlugins = magixPlugins.concat(magixCustomPlugins);

    // 3. Toolbar
    let magixToolbar = 'undo redo | link unlink image code advreplace | blocks | '
        +'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | '
        +'cbullist numlist | blockquote | removeformat forecolor | mc_pages mc_cat mc_news mc_product | fullscreen';

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
        selector: '.mceEditor',
        license_key: 'gpl',
        promotion: false,
        branding: false,
        language : tinyLanguage,
        min_height: 500,
        autoresize_bottom_margin: 20,
        relative_urls: false,
        remove_script_host: true,
        convert_urls: false,
        entity_encoding : "raw",
        schema: "html5",

        plugins: magixPlugins,
        toolbar: magixToolbar,
        menu: magixMenu,
        menubar: 'view edit insert format table tools',
        toolbar_mode: 'sliding',
        image_title: true,
        // Fichiers et médias
        file_picker_types: 'file image media',
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

                        //  FIX ULTIME : Interception et suppression du dossier fantôme "/a/"
                        let finalUrl = details.content;
                        // On cherche "/media/a/" et on le remplace strictement par "/media/"
                        finalUrl = finalUrl.replace(/(\/media\/)a\//i, '$1');

                        //  FIX DU NaN : On force les champs à "vide"
                        callback(finalUrl, { alt: '', width: '', height: '' });
                        dialogApi.close();
                    }
                }
            });
            setTimeout(() => {
                const iframe = document.querySelector('.tox-dialog__body-iframe iframe');
                if (iframe) {
                    iframe.setAttribute('allowfullscreen', 'true');
                    iframe.setAttribute('allow', 'fullscreen');
                }
            }, 300);
        },

        // Snippets
        snippets_url: '/'+baseadmin+'/index.php?controller=Snippet&action=tinymce',

        // ALIGNEMENTS BOOTSTRAP 5
        formats: {
            underline: {inline : 'u'},
            strikethrough: {inline: 'del'},
            alignleft: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-start'},
            aligncenter: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-center'},
            alignright: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-end'},
            alignjustify: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-justify'}
        },

        // SÉCURITÉ : Bloque les styles inline polluants
        invalid_styles: {
            'table': 'width height border border-collapse border-width',
            'tr' : 'width height',
            'th' : 'width height',
            'td' : 'width height'
        },

        // LISTES DE CLASSES POUR LES DIALOGUES NATIFS
        image_dimensions: true,
        image_advtab: true,
        image_class_list: [
            {title: 'None', value: ''},
            {title: 'Image Fluid (Responsive)', value: 'img-fluid'},
            {title: 'Image Rounded', value: 'rounded'},
            {title: 'Image Circle', value: 'rounded-circle'},
            {title: 'Image Thumbnail', value: 'img-thumbnail'},
            {title: 'Float Left', value: 'float-start'},
            {title: 'Float Right', value: 'float-end'}
        ],

        link_class_list: [
            {title: '--- Basique ---', value: ''},
            {title: 'Lien simple (TargetBlank)', value: 'targetblank'},
            {title: 'Lien Lire la suite', value: 'btn btn-link readmore'},
            {title: 'Lien Flèche (Arrow)', value: 'link-arrow'},

            {title: '--- Boutons MAIN ---', value: ''},
            {title: 'Main Standard', value: 'btn btn-main'},
            {title: 'Main Gradient', value: 'btn btn-main-gradient'},
            {title: 'Main Outline', value: 'btn btn-main-outline'},
            {title: 'Main Invert', value: 'btn btn-main-invert'},
            {title: 'Main White', value: 'btn btn-main-white'},
            {title: 'Main Invert Transparent', value: 'btn btn-main-invert-transparent'},
            {title: 'Main Glass', value: 'btn btn-main-glass'},
            {title: 'Main Ghost Slide', value: 'btn btn-main-ghost-slide'},
            {title: 'Main Ghost Curtain', value: 'btn btn-main-ghost-curtain'},
            {title: 'Main Ghost Reveal', value: 'btn btn-main-ghost-reveal'},

            {title: '--- Boutons SD ---', value: ''},
            {title: 'SD Standard', value: 'btn btn-sd'},
            {title: 'SD Gradient', value: 'btn btn-sd-gradient'},
            {title: 'SD Outline', value: 'btn btn-sd-outline'},
            {title: 'SD Invert', value: 'btn btn-sd-invert'},
            {title: 'SD Glass', value: 'btn btn-sd-glass'},
            {title: 'SD Ghost Slide', value: 'btn btn-sd-ghost-slide'},

            {title: '--- Boutons DARK ---', value: ''},
            {title: 'Dark Standard', value: 'btn btn-dark'},
            {title: 'Dark Outline', value: 'btn btn-dark-outline'},
            {title: 'Dark Invert', value: 'btn btn-dark-invert'},
            {title: 'Dark Ghost Reveal', value: 'btn btn-dark-ghost-reveal'},

            {title: '--- Boutons GREEN ---', value: ''},
            {title: 'Green Standard', value: 'btn btn-green'},
            {title: 'Green Gradient', value: 'btn btn-green-gradient'},
            {title: 'Green Outline', value: 'btn btn-green-outline'},

            {title: '--- Boutons WHITE ---', value: ''},
            {title: 'White Standard', value: 'btn btn-white'},
            {title: 'White Invert', value: 'btn btn-white-invert'}
        ],

        table_default_attributes: { class: 'table' },
        table_use_colgroups: false,
        table_class_list: [
            {title: 'Table Default', value: 'table'},
            {title: 'Table Small (Condensed)', value: 'table table-sm'},
            {title: 'Table Bordered', value: 'table table-bordered'},
            {title: 'Table Borderless', value: 'table table-borderless'},
            {title: 'Table Hover', value: 'table table-hover'},
            {title: 'Table Striped', value: 'table table-striped'}
        ],

        codesample_languages: [
            {text: 'HTML/XML', value: 'markup'},
            {text: 'JavaScript', value: 'javascript'},
            {text: 'json', value: 'json'},
            {text: 'CSS', value: 'css'},
            {text: 'PHP', value: 'php'},
            {text: 'Smarty', value: 'smarty'},
            {text: 'Sass/Scss', value: 'sass'}
        ],

        // STYLE FORMATS
        style_formats: [
            {title: 'Link', items: [
                    {title: 'TargetBlank', selector: 'a', classes: 'targetblank'}
                ]},
            {title: 'Buttons', items: [
                    {title: 'Main (Bleu)', items: [
                            {title: 'Standard', selector: 'a', classes: 'btn btn-main'},
                            {title: 'Gradient', selector: 'a', classes: 'btn btn-main-gradient'},
                            {title: 'Outline', selector: 'a', classes: 'btn btn-main-outline'},
                            {title: 'Invert', selector: 'a', classes: 'btn btn-main-invert'},
                            {title: 'White', selector: 'a', classes: 'btn btn-main-white'},
                            {title: 'Invert Transp.', selector: 'a', classes: 'btn btn-main-invert-transparent'},
                            {title: 'Glass', selector: 'a', classes: 'btn btn-main-glass'},
                            {title: 'Ghost Slide', selector: 'a', classes: 'btn btn-main-ghost-slide'},
                            {title: 'Ghost Curtain', selector: 'a', classes: 'btn btn-main-ghost-curtain'},
                            {title: 'Ghost Reveal', selector: 'a', classes: 'btn btn-main-ghost-reveal'}
                        ]},
                    {title: 'SD (Gris)', items: [
                            {title: 'Standard', selector: 'a', classes: 'btn btn-sd'},
                            {title: 'Gradient', selector: 'a', classes: 'btn btn-sd-gradient'},
                            {title: 'Outline', selector: 'a', classes: 'btn btn-sd-outline'},
                            {title: 'Invert', selector: 'a', classes: 'btn btn-sd-invert'},
                            {title: 'White', selector: 'a', classes: 'btn btn-sd-white'},
                            {title: 'Invert Transp.', selector: 'a', classes: 'btn btn-sd-invert-transparent'},
                            {title: 'Glass', selector: 'a', classes: 'btn btn-sd-glass'},
                            {title: 'Ghost Slide', selector: 'a', classes: 'btn btn-sd-ghost-slide'},
                            {title: 'Ghost Curtain', selector: 'a', classes: 'btn btn-sd-ghost-curtain'},
                            {title: 'Ghost Reveal', selector: 'a', classes: 'btn btn-sd-ghost-reveal'}
                        ]},
                    {title: 'Dark (Noir)', items: [
                            {title: 'Standard', selector: 'a', classes: 'btn btn-dark'},
                            {title: 'Gradient', selector: 'a', classes: 'btn btn-dark-gradient'},
                            {title: 'Outline', selector: 'a', classes: 'btn btn-dark-outline'},
                            {title: 'Invert', selector: 'a', classes: 'btn btn-dark-invert'},
                            {title: 'White', selector: 'a', classes: 'btn btn-dark-white'},
                            {title: 'Glass', selector: 'a', classes: 'btn btn-dark-glass'},
                            {title: 'Ghost Slide', selector: 'a', classes: 'btn btn-dark-ghost-slide'},
                            {title: 'Ghost Curtain', selector: 'a', classes: 'btn btn-dark-ghost-curtain'},
                            {title: 'Ghost Reveal', selector: 'a', classes: 'btn btn-dark-ghost-reveal'}
                        ]},
                    {title: 'Green (Vert)', items: [
                            {title: 'Standard', selector: 'a', classes: 'btn btn-green'},
                            {title: 'Gradient', selector: 'a', classes: 'btn btn-green-gradient'},
                            {title: 'Outline', selector: 'a', classes: 'btn btn-green-outline'},
                            {title: 'Invert', selector: 'a', classes: 'btn btn-green-invert'},
                            {title: 'Glass', selector: 'a', classes: 'btn btn-green-glass'},
                            {title: 'Ghost Slide', selector: 'a', classes: 'btn btn-green-ghost-slide'}
                        ]},
                    {title: 'White (Blanc)', items: [
                            {title: 'Standard', selector: 'a', classes: 'btn btn-white'},
                            {title: 'Outline', selector: 'a', classes: 'btn btn-white-outline'},
                            {title: 'Invert', selector: 'a', classes: 'btn btn-white-invert'},
                            {title: 'Glass', selector: 'a', classes: 'btn btn-white-glass'}
                        ]}
                ]},
            {title: 'Image', items: [
                    //  ADAPTATION POUR GLIGHTBOX ICI
                    {title: 'GLightbox Simple', selector: 'a', classes: 'glightbox'},
                    {title: 'GLightbox Galerie', selector: 'a', classes: 'glightbox', attributes: {'data-gallery': 'gallery'}},
                    // ----------------------------------
                    {title: 'Image Fluid (Responsive)', selector: 'img', classes: 'img-fluid'},
                    {title: 'Image Rounded', selector: 'img', classes: 'rounded'},
                    {title: 'Image Circle', selector: 'img', classes: 'rounded-circle'},
                    {title: 'Image Thumbnail', selector: 'img', classes: 'img-thumbnail'}
                ]},
            {title: 'Table', items: [
                    {title: 'Table', selector: 'table', classes: 'table'},
                    {title: 'Table Small', selector: 'table', classes: 'table-sm'},
                    {title: 'Table Bordered', selector: 'table', classes: 'table-bordered'},
                    {title: 'Table Hover', selector: 'table', classes: 'table-hover'},
                    {title: 'Table Striped', selector: 'table', classes: 'table-striped'},
                    {title: 'TR (Lignes)', items: [
                            {title : 'Active', selector : 'tr', classes : 'table-active'},
                            {title : 'Success', selector : 'tr', classes : 'table-success'},
                            {title : 'Warning', selector : 'tr', classes : 'table-warning'},
                            {title : 'Danger', selector : 'tr', classes : 'table-danger'},
                            {title : 'Info', selector : 'tr', classes : 'table-info'}
                        ]},
                    {title: 'TD (Cellules)', items: [
                            {title : 'Active', selector : 'td', classes : 'table-active'},
                            {title : 'Success', selector : 'td', classes : 'table-success'},
                            {title : 'Warning', selector : 'td', classes : 'table-warning'},
                            {title : 'Danger', selector : 'td', classes : 'table-danger'},
                            {title : 'Info', selector : 'td', classes : 'table-info'}
                        ]},
                    {title: "Blocks", items: [
                            {title: "Div responsive", block: "div", classes: 'table-responsive'}
                        ]}
                ]},
            {title: 'Helper classes', items: [
                    {title: "Blocks", items: [
                            {title: "Div center (mx-auto)", block: "div", classes: 'mx-auto text-center'}
                        ]},
                    {title: "Header", items: [
                            {title: "Title 1", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h1'},
                            {title: "Title 2", selector: "h2,h1,h3,h4,h5,h6,p", classes: 'h2'},
                            {title: "Title 3", selector: "h3,h1,h2,h4,h5,h6,p", classes: 'h3'},
                            {title: "Title 4", selector: "h4,h1,h2,h3,h5,h6,p", classes: 'h4'},
                            {title: "Title 5", selector: "h5,h1,h2,h3,h4,h6,p", classes: 'h5'},
                            {title: "Title 6", selector: "h6,h1,h2,h3,h4,h5,p", classes: 'h6'}
                        ]},
                    {title: "Paragraph", items: [
                            {title: "Text Muted", block: "p", classes: 'text-muted'},
                            {title: "Text Primary", block: "p", classes: 'text-primary'},
                            {title: "Text Success", block: "p", classes: 'text-success'},
                            {title: "Text Info", block: "p", classes: 'text-info'},
                            {title: "Text Warning", block: "p", classes: 'text-warning'},
                            {title: "Text Danger", block: "p", classes: 'text-danger'}
                        ]},
                    {title: "List", items: [
                            {title: "Bullet list", block: "ul", classes: 'bullet-list'},
                            {title: 'Circle List', block: "ul", classes: 'circle-list'},
                            {title: 'Square List', block: "ul", classes: 'square-list'},
                            {title: 'Arrow List', block: "ul", classes: 'arrow-list'},
                            {title: 'Label List', block: "ul", classes: 'label-list'}
                        ]}
                ]},
            {title: 'Alerts', items: [
                    {title: "Blocks", items: [
                            {title: "Alert success", block: "div", classes: 'alert alert-success'},
                            {title: "Alert info", block: "div", classes: 'alert alert-info'},
                            {title: "Alert warning", block: "div", classes: 'alert alert-warning'},
                            {title: "Alert danger", block: "div", classes: 'alert alert-danger'}
                        ]},
                    {title: "Link", items: [
                            {title: 'Alert link', selector: 'a', classes: 'alert-link'}
                        ]}
                ]},
            {title: 'Embed', items: [
                    {title: "Blocks", items: [
                            {title: "Ratio 16:9", block: "div", classes: 'ratio ratio-16x9'},
                            {title: "Ratio 4:3", block: "div", classes: 'ratio ratio-4x3'},
                            {title: "Ratio 1:1", block: "div", classes: 'ratio ratio-1x1'}
                        ]}
                ]}
        ],

        cbullet_styles: [
            {title: 'Default', style: 'disc'},
            {title: 'Circle', style: 'circle'},
            {title: 'Square', style: 'square'},
            {title: 'Bullet List', classes: 'bullet-list'},
            {title: 'Circle List', classes: 'circle-list'},
            {title: 'Square List', classes: 'square-list'},
            {title: 'Arrow List', classes: 'arrow-list'},
            {title: 'Label List', classes: 'label-list'}
        ],

        // Sécurité éléments HTML
        extended_valid_elements: "+img[class|src|srcset|sizes|alt|title|hspace|vspace|width|height|align|name|loading],+svg[*],+g[*],+path[*],+span[*],+i[*],+div[*],+ul[*],+li[*],+iframe[*],+strong[*]",

        // CSS de contenu (Frontend)
        content_css : (typeof contentCSS !== 'undefined') ? contentCSS : '',
        // Synchronisation avec MagixForms
        fullscreen_native: true,
        sticky_toolbar: true,
        toolbar_sticky_offset: 0,
        setup: function (editor) {
            editor.on('BeforeSetContent', function (e) {
                e.content = e.content.replace(/width="NaN"/gi, '');
                e.content = e.content.replace(/height="NaN"/gi, '');
            });

            editor.on('change input undo redo', function () {
                editor.save();
            });

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
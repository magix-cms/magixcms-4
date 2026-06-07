/**
 * Configuration TinyMCE 7 pour MagixCMS 4
 * Version Vanilla JS (Sans jQuery) - Compatible Bootstrap 5.3 & GLightbox
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
        'media', 'table', 'codesample', 'accordion', 'magix_bs_grid', 'quickbars' // <-- Quickbars intégré
    ];

    const magixCustomPlugins = [
        'youtube', 'loremipsum', 'mc_pages',
        'mc_cat', 'mc_news', 'mc_product', 'lazyloadimage', 'cryptmail',
        'tabpanel', 'snippets', 'advreplace', 'mc_history', 'clists', 'bsicons'
    ];
    magixPlugins = magixPlugins.concat(magixCustomPlugins);

    // 3. Toolbar
    let magixToolbar = 'undo redo | link unlink image bs_grid code advreplace | blocks styles | '
        +'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | '
        +'cbullist numlist | blockquote| bsicons | removeformat forecolor | mc_pages mc_cat mc_news mc_product | fullscreen';

    // 4. Menus
    let magixMenu = {
        view   : {title : 'View'  , items : 'code | visualaid visualblocks | preview fullscreen'},
        edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall | searchreplace'},
        insert : {title : 'Insert', items : 'link anchor | snippets | image media youtube | tabpanel | table | hr | loremipsum | codesample | bsicons'},
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

        // ========================================================
        // ✨ QUICKBARS (Menus flottants intelligents)
        // ========================================================
        quickbars_selection_toolbar: 'bold italic | styles | link blockquote',
        quickbars_insert_toolbar: 'bs_grid image media table',

        // Fichiers et médias via Elfinder
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
                        let finalUrl = details.content;
                        finalUrl = finalUrl.replace(/(\/media\/)a\//i, '$1');
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

        // Snippets Magix
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

        invalid_styles: {
            'table': 'width height border border-collapse border-width',
            'tr' : 'width height',
            'th' : 'width height',
            'td' : 'width height'
        },

        image_dimensions: true,
        image_advtab: true,
        image_class_list: [
            {title: 'Aucune', value: ''},
            {title: 'Image Responsive (S\'adapte)', value: 'img-fluid'},
            {title: 'Coins Arrondis', value: 'rounded'},
            {title: 'Cercle Parfait', value: 'rounded-circle'},
            {title: 'Miniature (Bordure)', value: 'img-thumbnail'},
            {title: 'Flotter à Gauche', value: 'float-start'},
            {title: 'Flotter à Droite', value: 'float-end'}
        ],

        table_default_attributes: { class: 'table' },
        table_use_colgroups: false,
        table_class_list: [
            {title: 'Tableau Classique', value: 'table'},
            {title: 'Tableau Compact', value: 'table table-sm'},
            {title: 'Tableau avec Bordures', value: 'table table-bordered'},
            {title: 'Tableau sans Bordures', value: 'table table-borderless'},
            {title: 'Tableau Lignes Zébrées', value: 'table table-striped'},
            {title: 'Tableau Survolable', value: 'table table-hover'}
        ],

        codesample_languages: [
            {text: 'HTML/XML', value: 'markup'},
            {text: 'JavaScript', value: 'javascript'},
            {text: 'CSS', value: 'css'},
            {text: 'PHP', value: 'php'},
            {text: 'Smarty', value: 'smarty'},
            {text: 'Sass/Scss', value: 'sass'}
        ],

        // ========================================================
        // 🎨 STYLE FORMATS (Fidèle à ton thème & Bootstrap 5.3)
        // ========================================================
        style_formats: [
            {title: 'Liens Spéciaux', items: [
                    {title: 'Lien (TargetBlank)', selector: 'a', classes: 'targetblank'},
                    {title: 'Lien Lire la suite', selector: 'a', classes: 'btn btn-link readmore'},
                    {title: 'Lien Flèche (Arrow)', selector: 'a', classes: 'link-arrow'}
                ]},

            {title: 'Boutons Magix', items: [
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

            {title: 'Typographie & Textes', items: [
                    {title: 'Titres Géants (Display)', items: [
                            {title: "Display 1", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'display-1'},
                            {title: "Display 2", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'display-2'},
                            {title: "Display 3", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'display-3'},
                            {title: "Display 4", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'display-4'},
                            {title: "Display 5", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'display-5'},
                            {title: "Display 6", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'display-6'}
                        ]},
                    {title: 'Titres standards', items: [
                            {title: "Style H1", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h1'},
                            {title: "Style H2", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h2'},
                            {title: "Style H3", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h3'},
                            {title: "Style H4", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h4'},
                            {title: "Style H5", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h5'},
                            {title: "Style H6", selector: "h1,h2,h3,h4,h5,h6,p", classes: 'h6'}
                        ]},
                    {title: 'Graisse (Poids)', items: [
                            {title: 'Gras (fw-bold)', inline: 'span', classes: 'fw-bold'},
                            {title: 'Normal (fw-normal)', inline: 'span', classes: 'fw-normal'},
                            {title: 'Fin (fw-light)', inline: 'span', classes: 'fw-light'},
                            {title: 'Italique (fst-italic)', inline: 'span', classes: 'fst-italic'}
                        ]},
                    {title: 'Casse (Majuscules)', items: [
                            {title: 'MAJUSCULES', inline: 'span', classes: 'text-uppercase'},
                            {title: 'minuscules', inline: 'span', classes: 'text-lowercase'},
                            {title: 'Première Lettre', inline: 'span', classes: 'text-capitalize'}
                        ]},
                    {title: 'Phrase d\'accroche (Lead)', block: 'p', classes: 'lead'},
                    {title: 'Texte Discret (Gris)', inline: 'span', classes: 'text-body-secondary'},
                    {title: 'Texte Petit (Small)', inline: 'small', classes: 'small'}
                ]},

            {title: 'Couleurs de texte', items: [
                    {title: 'Primaire (Bleu)', inline: 'span', classes: 'text-primary'},
                    {title: 'Succès (Vert)', inline: 'span', classes: 'text-success'},
                    {title: 'Information (Bleu clair)', inline: 'span', classes: 'text-info'},
                    {title: 'Danger (Rouge)', inline: 'span', classes: 'text-danger'},
                    {title: 'Attention (Jaune)', inline: 'span', classes: 'text-warning'}
                ]},

            {title: 'Images & Médias', items: [
                    {title: 'Activer Zoom (GLightbox)', selector: 'a, img', classes: 'glightbox'},
                    {title: 'Activer Galerie (GLightbox)', selector: 'a, img', classes: 'glightbox', attributes: {'data-gallery': 'gallery'}},
                    {title: 'Ajouter une Ombre', selector: 'img, div', classes: 'shadow-sm'},
                    {title: 'Vidéo Responsive 16:9', block: 'div', classes: 'ratio ratio-16x9'},
                    {title: 'Vidéo Responsive 4:3', block: 'div', classes: 'ratio ratio-4x3'},
                    {title: 'Vidéo Responsive Carrée', block: 'div', classes: 'ratio ratio-1x1'}
                ]},

            {title: 'Alertes / Bannières', items: [
                    {title: 'Information (Bleu)', block: 'div', classes: 'alert alert-info'},
                    {title: 'Succès (Vert)', block: 'div', classes: 'alert alert-success'},
                    {title: 'Attention (Jaune)', block: 'div', classes: 'alert alert-warning'},
                    {title: 'Danger (Rouge)', block: 'div', classes: 'alert alert-danger'},
                    {title: 'Lien d\'alerte', selector: 'a', classes: 'alert-link'}
                ]},

            {title: 'Utilitaires (Bootstrap 5)', items: [
                    {title: 'Centrer le bloc (mx-auto)', selector: 'div, p, img, table, a', classes: 'mx-auto d-block'},
                    {title: 'Ajouter Ombre (Shadow)', selector: '*', classes: 'shadow'},
                    {title: 'Arrondir les angles', selector: '*', classes: 'rounded'},

                    {title: 'Marges externes (Margins)', items: [
                            {title: 'Marge Haut (mt-3)', selector: '*', classes: 'mt-3'},
                            {title: 'Marge Haut Grosse (mt-5)', selector: '*', classes: 'mt-5'},
                            {title: 'Marge Bas (mb-3)', selector: '*', classes: 'mb-3'},
                            {title: 'Marge Bas Grosse (mb-5)', selector: '*', classes: 'mb-5'},
                        ]},

                    {title: 'Espacements internes (Paddings)', items: [
                            {title: 'Padding Global (p-3)', selector: '*', classes: 'p-3'},
                            {title: 'Padding Global Gros (p-5)', selector: '*', classes: 'p-5'},
                            {title: 'Padding Vertical (py-4)', selector: '*', classes: 'py-4'},
                            {title: 'Padding Horizontal (px-4)', selector: '*', classes: 'px-4'}
                        ]},

                    {title: 'Layout Flexbox (Avancé)', items: [
                            {title: 'Activer Flexbox (d-flex)', selector: 'div, section', classes: 'd-flex'},
                            {title: 'Empiler (flex-column)', selector: 'div, section', classes: 'flex-column'},
                            {title: 'Ligne sur PC (flex-md-row)', selector: 'div, section', classes: 'flex-md-row'},
                            {title: 'Centrer Vertical (align-items-center)', selector: 'div, section', classes: 'align-items-center'},
                            {title: 'Centrer Horizontal (justify-content-center)', selector: 'div, section', classes: 'justify-content-center'},
                            {title: 'Écarter éléments (gap-4)', selector: 'div, section', classes: 'gap-4'}
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

        extended_valid_elements: "+img[class|src|srcset|sizes|alt|title|hspace|vspace|width|height|align|name|loading],+svg[*],+g[*],+path[*],+span[*],+i[*],+div[*],+ul[*],+li[*],+iframe[*],+strong[*]",

        content_css : (typeof contentCSS !== 'undefined') ? contentCSS : '',
        fullscreen_native: true,
        sticky_toolbar: true,
        toolbar_sticky_offset: 0,

        // ========================================================
        // 🛠️ SETUP & LOGIQUE D'ÉVÉNEMENTS
        // ========================================================
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

            // 🧹 GESTION INTELLIGENTE DES CLASSES BOOTSTRAP (Remplacement propre)
            editor.on('FormatApply', function (e) {

                // 1. CORRECTION ICI : Récupérer l'élément de manière 100% fiable
                let elm = e.node || editor.selection.getNode();
                if (!elm) return;

                // Sécurité : si on tombe sur un noeud de texte pur, on remonte à sa balise HTML
                if (elm.nodeType !== 1) {
                    elm = elm.parentNode;
                }

                let formats = editor.formatter.get(e.format);
                if (!formats || formats.length === 0) return;

                let format = formats[0];
                if (!format.classes) return;

                // 2. Si le format exige une balise précise (comme 'a' pour les boutons)
                if (format.selector) {
                    let parentMatch = editor.dom.getParent(elm, format.selector);
                    if (parentMatch) elm = parentMatch;
                }

                if (!elm || !elm.classList) return;

                // 3. Les classes que TinyMCE vient juste d'ajouter
                let appliedClasses = Array.isArray(format.classes) ? format.classes : format.classes.split(' ');

                // 🎯 Familles exclusives (On ne touche JAMAIS au mot 'btn' seul)
                const exclusiveRegexes = [
                    /^btn-(main|sd|dark|green|white)(?:-[a-zA-Z0-9]+)*$/, // La famille des boutons
                    /^text-(primary|success|info|warning|danger|body-secondary)$/, // Famille des couleurs
                    /^display-[1-6]$/, // Famille des gros titres
                    /^h[1-6]$/, // Famille des titres classiques
                    /^fw-(bold|normal|light)$/, // Famille des graisses
                    /^text-(uppercase|lowercase|capitalize)$/, // Famille des casses
                    /^alert-(info|success|warning|danger)$/ // Famille des alertes
                ];

                appliedClasses.forEach(function(appliedClass) {
                    exclusiveRegexes.forEach(function(regex) {
                        if (regex.test(appliedClass)) {
                            // On parcourt les classes existantes sur le DOM...
                            let existingClasses = Array.from(elm.classList);
                            existingClasses.forEach(function(existingClass) {
                                // On supprime l'ancienne classe de la même famille (sans toucher à 'btn' ou à la nouvelle)
                                if (existingClass !== appliedClass && regex.test(existingClass) && existingClass !== 'btn') {
                                    elm.classList.remove(existingClass);
                                }
                            });

                            // 🔒 SÉCURITÉ BOOTSTRAP : On s'assure que la classe de base 'btn' est toujours là !
                            if (appliedClass.startsWith('btn-') && !elm.classList.contains('btn')) {
                                elm.classList.add('btn');
                            }
                        }
                    });
                });
            });
        },

        // Embellit l'affichage du menu déroulant
        preview_styles: 'font-family font-weight font-style text-decoration text-transform color background-color border border-radius outline text-shadow'
    });

})(window, document);
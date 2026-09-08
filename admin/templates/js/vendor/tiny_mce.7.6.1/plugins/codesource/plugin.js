/**
 * Plugin CodeSource pour TinyMCE 7
 * Éditeur de code source avec coloration syntaxique CodeMirror 6
 *
 * @copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)
 * @license GPL-3.0-or-later
 */
(function () {
    'use strict';

    tinymce.PluginManager.add('codesource', function (editor, url) {
        let cmInstance = null;

        // 1. Formateur HTML (Beautifier) ultra-léger
        const formatHTML = function (html) {
            let indentLevel = 0;
            return html
                .replace(/>\s*</g, '>\n<') // Casse les balises collées
                .split('\n')
                .map(function (line) {
                    line = line.trim();
                    if (!line) return '';

                    if (line.match(/^<\/(?!html)/)) {
                        indentLevel = Math.max(0, indentLevel - 1);
                    }

                    const formatted = '  '.repeat(indentLevel) + line;

                    if (line.match(/^<(?!!--|img|br|hr|input|meta|link|area|base|col|param)[^\/][^>]*>$/)) {
                        indentLevel++;
                    }
                    return formatted;
                })
                .join('\n');
        };

        // 2. Injection du CSS de base
        const injectStyles = function () {
            const styleId = 'cm6-tinymce-modal-style';
            if (!document.getElementById(styleId)) {
                const style = document.createElement('style');
                style.id = styleId;
                style.textContent = `
          .tox-dialog__body-content:has(#cm-editor-modal) {
            padding: 0 !important;
          }
          #cm-editor-modal {
            height: 68vh;
            min-height: 400px;
            width: 100%;
            display: flex;
            flex-direction: column;
            text-align: left;
            /* Plus de background forcé ici, oneDark s'en charge ! */
          }
        `;
                document.head.appendChild(style);
            }
        };

        // 3. Chargement autonome du bundle CM6
        const ensureDependencies = function (callback) {
            if (window.CM6 && window.CM6.openSearchPanel) { // Sécurité supplémentaire
                callback();
                return;
            }
            const script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = url + '/codemirror.bundle.js?v=1.1';
            script.onload = callback;
            script.onerror = function () {
                editor.notificationManager.open({ text: 'Échec du chargement de CodeMirror 6', type: 'error' });
            };
            document.head.appendChild(script);
        };

        // 4. Ouverture de la modale TinyMCE 7
        const openEditorDialog = function () {
            ensureDependencies(function () {
                injectStyles();

                const { EditorView, EditorState, basicSetup, html, oneDark, openSearchPanel } = window.CM6;

                editor.windowManager.open({
                    title: 'Code source HTML',
                    size: 'large',
                    body: {
                        type: 'panel',
                        items: [
                            {
                                type: 'htmlpanel',
                                html: '<div id="cm-editor-modal"></div>'
                            }
                        ]
                    },
                    buttons: [
                        { type: 'cancel', name: 'cancel', text: 'Annuler' },
                        { type: 'custom', name: 'search', text: 'Rechercher', icon: 'search' },
                        { type: 'submit', name: 'save', text: 'Enregistrer', primary: true }
                    ],
                    onAction: function (api, details) {
                        if (details.name === 'search' && cmInstance) {
                            openSearchPanel(cmInstance);
                        }
                    },
                    onSubmit: function (api) {
                        if (cmInstance) {
                            const updatedContent = cmInstance.state.doc.toString();
                            editor.setContent(updatedContent);
                            cmInstance.destroy();
                            cmInstance = null;
                        }
                        api.close();
                    },
                    onClose: function () {
                        if (cmInstance) {
                            cmInstance.destroy();
                            cmInstance = null;
                        }
                    }
                });

                // 5. Initialisation de CodeMirror
                setTimeout(function () {
                    try {
                        const container = document.getElementById('cm-editor-modal');
                        if (!container) return;

                        // 👉 LA SOLUTION MAGIQUE : LE SHADOW DOM
                        // On crée un DOM isolé pour bloquer l'interférence du CSS de TinyMCE
                        const shadowRoot = container.attachShadow({ mode: 'open' });

                        const rawCode = editor.getContent({ source_view: true });
                        const beautifulCode = formatHTML(rawCode);

                        // Structure visuelle confinée dans le Shadow DOM
                        const customTheme = EditorView.theme({
                            "&": {
                                height: "100%",
                                fontSize: "14px",
                                fontFamily: "Menlo, Monaco, Consolas, 'Courier New', monospace"
                            },
                            ".cm-scroller": { overflow: "auto" },
                            "&.cm-focused": { outline: "none" }
                        });

                        cmInstance = new EditorView({
                            state: EditorState.create({
                                doc: beautifulCode,
                                extensions: [
                                    basicSetup,
                                    html(),
                                    oneDark,
                                    customTheme,
                                    EditorView.lineWrapping
                                ]
                            }),
                            // 👇 On accroche l'éditeur au Shadow DOM au lieu du conteneur classique
                            parent: shadowRoot
                        });

                        setTimeout(() => cmInstance.focus(), 50);
                    } catch (e) {
                        console.error("CodeSource ERREUR FATALE :", e);
                    }
                }, 50);

            });
        };

        // 6. Enregistrement TinyMCE
        editor.addCommand('mceCodeSource', openEditorDialog);

        editor.ui.registry.addButton('codesource', {
            icon: 'sourcecode',
            tooltip: 'Code source (CodeMirror 6)',
            onAction: () => editor.execCommand('mceCodeSource')
        });

        editor.ui.registry.addMenuItem('codesource', {
            icon: 'sourcecode',
            text: 'Code source...',
            onAction: () => editor.execCommand('mceCodeSource')
        });

        return {
            getMetadata: function () {
                return {
                    name: 'CodeSource CodeMirror 6',
                    url: 'https://github.com/gtraxx/tinymce-codesource',
                    author: 'Aurélien Gérits (Magix CMS)'
                };
            }
        };
    });
})();
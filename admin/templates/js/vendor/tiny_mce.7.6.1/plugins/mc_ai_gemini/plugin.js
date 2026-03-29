/**
 * Magix AI Gemini - TinyMCE Plugin
 * License: GPLv3
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)
 */
tinymce.PluginManager.add('mc_ai_gemini', function(editor, url) {

    // 1. SÉCURITÉ : Vérification de l'activation via MagixCMS (Optionnel selon votre config globale)
    if (typeof window.MagixCMS !== 'undefined' && window.MagixCMS.ai_enabled === false) {
        return;
    }

    // 2. ICÔNE : SVG "Sparkles" aux couleurs de Google/Gemini
    editor.ui.registry.addIcon('gemini-icon', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z" fill="#4285F4"/></svg>');

    // 3. LOGIQUE : Dialogue Assistant IA
    const openAiDialog = function() {
        // On récupère le texte sélectionné EN HTML pour préserver la structure lors d'une traduction/correction
        const selectedText = editor.selection.getContent({ format: 'html' });

        const dialog = editor.windowManager.open({
            title: 'Assistant Rédactionnel Magix AI',
            size: 'large',
            body: {
                type: 'panel',
                items: [
                    {
                        type: 'grid',
                        columns: 2,
                        items: [
                            {
                                type: 'selectbox',
                                name: 'action_type',
                                label: 'Action',
                                items: [
                                    { text: 'Rédaction / Correction', value: 'write' },
                                    { text: 'Traduire vers...', value: 'translate' }
                                ]
                            },
                            {
                                type: 'selectbox',
                                name: 'language',
                                label: 'Langue cible',
                                items: [
                                    { text: 'Français', value: 'français' },
                                    { text: 'Anglais', value: 'anglais' },
                                    { text: 'Néerlandais', value: 'néerlandais' },
                                    { text: 'Allemand', value: 'allemand' }
                                ]
                            },
                            {
                                type: 'selectbox',
                                name: 'tone',
                                label: 'Ton du texte',
                                items: [
                                    { text: 'Professionnel', value: 'professionnel' },
                                    { text: 'Amical', value: 'amical' },
                                    { text: 'Marketing', value: 'marketing' }
                                ]
                            },
                            {
                                type: 'selectbox',
                                name: 'length',
                                label: 'Longueur',
                                items: [
                                    { text: 'Standard', value: 'standard' },
                                    { text: 'Court', value: 'court' },
                                    { text: 'Détaillé', value: 'long' }
                                ]
                            }
                        ]
                    },
                    {
                        type: 'textarea',
                        name: 'prompt',
                        label: 'Instruction pour l\'IA',
                        placeholder: 'Ex: Rédige un paragraphe sur le référencement, traduis ce texte...'
                    },
                    {
                        type: 'htmlpanel',
                        html: '<div style="margin: 10px 0; border-top: 1px solid #ddd;"></div>'
                    },
                    {
                        type: 'textarea',
                        name: 'result',
                        label: 'Contenu généré (HTML)',
                        placeholder: 'Le résultat s\'affichera ici...',
                        flex: true
                    }
                ]
            },
            buttons: [
                {
                    type: 'custom',
                    name: 'generate_btn',
                    text: 'Générer',
                    primary: true
                },
                {
                    type: 'submit',
                    name: 'insert_btn',
                    text: 'Insérer dans la page',
                    enabled: false
                },
                {
                    type: 'cancel',
                    text: 'Annuler'
                }
            ],
            onAction: function (api, details) {
                if (details.name === 'generate_btn') {
                    const data = api.getData();

                    // On exige soit une instruction, soit un texte sélectionné
                    if (!data.prompt && !selectedText) {
                        editor.notificationManager.open({ text: 'Veuillez saisir une instruction ou sélectionner du texte.', type: 'warning' });
                        return;
                    }

                    api.block('L\'IA Magix prépare votre contenu...');

                    // 🟢 APPEL AU CONTRÔLEUR PHP (Allégé : le backend valide déjà la session admin)
                    fetch('index.php?controller=GeminiAI&action=generate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            prompt: data.prompt,
                            context: selectedText,
                            options: {
                                action_type: data.action_type,
                                language: data.language,
                                tone: data.tone,
                                length: data.length
                            }
                        })
                    })
                        .then(res => res.json())
                        .then(resData => {
                            api.unblock();

                            // 🟢 GESTION DE LA RÉPONSE
                            if (resData.status && resData.content) {
                                api.setData({ result: resData.content });
                                api.setEnabled('insert_btn', true); // On active le bouton d'insertion
                            } else {
                                // Affichage du message d'erreur renvoyé par le PHP
                                const errorMsg = resData.message || 'Erreur inconnue retournée par l\'API.';
                                editor.notificationManager.open({ text: errorMsg, type: 'error' });
                            }
                        })
                        .catch(err => {
                            api.unblock();
                            console.error('GeminiAI Fetch Error:', err);
                            editor.notificationManager.open({ text: 'Erreur de communication avec le serveur Magix CMS.', type: 'error' });
                        });
                }
            },
            onSubmit: function(api) {
                const data = api.getData();
                if (data.result) {
                    // On insère le contenu et on crée un point de restauration (Undo) pour pouvoir faire "Ctrl+Z"
                    editor.undoManager.transact(function () {
                        // Remplacera la sélection initiale si elle existait, ou insérera là où se trouve le curseur
                        editor.insertContent(data.result);
                    });
                    api.close();
                }
            }
        });
    };

    // 4. ENREGISTREMENT UI (Bouton Barre d'outils + Menu)
    editor.ui.registry.addButton('mc_ai_gemini', {
        icon: 'gemini-icon',
        tooltip: 'Assistant IA Magix',
        onAction: openAiDialog
    });

    editor.ui.registry.addMenuItem('mc_ai_gemini', {
        text: 'Assistant IA Magix',
        icon: 'gemini-icon',
        onAction: openAiDialog
    });

});
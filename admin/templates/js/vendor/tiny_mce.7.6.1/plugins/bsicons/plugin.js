/**
 * Plugin TinyMCE 7 : Bootstrap Icons (bsicons)
 * Version: 1.1.0
 * Licence: GPLv3
 * Copyright: (C) 2008 - 2026 Gerits Aurelien (Magix CMS)
 */
(function () {
    'use strict';

    tinymce.PluginManager.requireLangPack('bsicons');

    const BS_ICONS_CSS = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';
    const BATCH_SIZE = 240;

    let iconsData = [];
    let loadingPromise = null;

    /**
     * Injecte une seule fois la feuille de style avec une spécificité supérieure au reset Oxide (.tox :not(svg):not(rect))
     */
    function ensureUiStyles() {
        const styleId = 'mc-bsicons-ui-style';
        const existing = document.getElementById(styleId);
        if (existing) {
            existing.remove();
        }

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .tox .tox-dialog .mc-bsi-wrap,
            .tox .tox-dialog .mc-bsi-wrap * {
                box-sizing: border-box;
            }
            .tox .tox-dialog .mc-bsi-wrap {
                font-family: system-ui, -apple-system, sans-serif;
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-topbar {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
                width: 100%;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-search {
                flex: 1 1 240px;
                height: 34px;
                padding: 6px 10px;
                font-size: 13px;
                line-height: 1.4;
                border: 1px solid #cbd5e1;
                border-radius: 5px;
                background: #fff;
                color: #1e293b;
                outline: none;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-search:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-size {
                height: 34px;
                padding: 4px 10px;
                font-size: 12px;
                border: 1px solid #cbd5e1;
                border-radius: 5px;
                background: #fff;
                color: #1e293b;
                cursor: pointer;
                appearance: auto;
                -webkit-appearance: menulist;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-subbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
                width: 100%;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-pills {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-pill {
                cursor: pointer;
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #334155;
                border-radius: 999px;
                padding: 4px 12px;
                font-size: 12px;
                font-weight: 500;
                line-height: 1.2;
                transition: all 0.15s ease;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-pill:hover {
                border-color: #94a3b8;
                background: #f1f5f9;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-pill.is-active {
                background: #0d6efd;
                border-color: #0d6efd;
                color: #fff;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-count {
                font-size: 12px;
                color: #64748b;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(46px, 1fr));
                gap: 6px;
                width: 100%;
                height: min(58vh, 520px);
                min-height: 380px;
                align-content: start;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 10px;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                background: #f8fafc;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-btn {
                width: 46px;
                height: 46px;
                font-size: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                color: #1e293b;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                cursor: pointer;
                padding: 0;
                transition: border-color 0.12s ease, background-color 0.12s ease, color 0.12s ease, transform 0.1s ease;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-btn:hover,
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-btn:focus-visible {
                border-color: #0d6efd;
                background: #eff6ff;
                color: #0d6efd;
                transform: translateY(-1px);
                outline: none;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-btn i {
                pointer-events: none;
                line-height: 1;
                font-size: 20px;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-empty {
                grid-column: 1 / -1;
                text-align: center;
                color: #64748b;
                font-size: 13px;
                padding: 36px 12px;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                min-height: 32px;
                padding: 6px 10px;
                border-radius: 5px;
                background: #f1f5f9;
                font-size: 12px;
                color: #475569;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-preview {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-preview i {
                font-size: 16px;
                color: #0d6efd;
            }
            .tox .tox-dialog .mc-bsi-wrap .mc-bsi-code {
                font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                font-size: 11px;
                color: #0f172a;
                background: #e2e8f0;
                padding: 2px 6px;
                border-radius: 4px;
            }
        `;
        document.head.appendChild(style);
    }

    tinymce.PluginManager.add('bsicons', function (editor, url) {
        const _ = function (key, fallback, replacements) {
            let translated = editor.translate(key);
            if (translated === key && fallback) {
                translated = fallback;
            }
            if (Array.isArray(replacements)) {
                replacements.forEach(function (val, index) {
                    translated = translated.replace('{' + index + '}', String(val));
                });
            }
            return translated;
        };

        /**
         * Charge (une seule fois) et indexe les icônes depuis bridge.php
         */
        const fetchIconsData = function () {
            if (iconsData.length > 0) {
                return Promise.resolve(iconsData);
            }

            if (loadingPromise) {
                return loadingPromise;
            }

            loadingPromise = fetch(url + '/bridge.php', {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (rawList) {
                    if (!Array.isArray(rawList) || rawList.length === 0) {
                        throw new Error('Empty icon list');
                    }

                    iconsData = rawList.map(function (item) {
                        const icon = String(item.icon || '');
                        const name = String(item.name || '');
                        return {
                            icon: icon,
                            name: name,
                            isFill: icon.endsWith('-fill'),
                            searchText: (name + ' ' + icon).toLowerCase()
                        };
                    });

                    return iconsData;
                })
                .catch(function (error) {
                    loadingPromise = null;
                    console.error(_('bsicons_error_load', 'Error loading icons via bridge.php:'), error);
                    throw error;
                });

            return loadingPromise;
        };

        // Préchargement silencieux au démarrage de l'éditeur
        fetchIconsData().catch(function () {});

        /**
         * Monte l'interface interactive dans le conteneur htmlpanel
         */
        function mountIconsUI(hostId, dialogApi) {
            const host = document.getElementById(hostId);
            if (!host) return;

            ensureUiStyles();

            let activeStyle = 'all';
            let searchQuery = '';
            let selectedSize = '';
            let filteredList = iconsData;
            let renderedCount = 0;

            host.innerHTML = '';

            const wrap = document.createElement('div');
            wrap.className = 'mc-bsi-wrap';

            // 1. Barre de recherche + taille Bootstrap optionnelle
            const topbar = document.createElement('div');
            topbar.className = 'mc-bsi-topbar';

            const searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.className = 'mc-bsi-search';
            searchInput.placeholder = _('bsicons_search_placeholder', 'Search (e.g., star, check, user)...');
            searchInput.setAttribute('aria-label', _('bsicons_search_placeholder', 'Search (e.g., star, check, user)...'));

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            const sizeSelect = document.createElement('select');
            sizeSelect.className = 'mc-bsi-size';
            sizeSelect.setAttribute('aria-label', _('bsicons_size_label', 'Icon size'));

            const sizeOptions = [
                { value: '', label: _('bsicons_size_default', 'Default size') },
                { value: 'fs-6', label: 'fs-6' },
                { value: 'fs-5', label: 'fs-5' },
                { value: 'fs-4', label: 'fs-4' },
                { value: 'fs-3', label: 'fs-3' },
                { value: 'fs-2', label: 'fs-2' },
                { value: 'fs-1', label: 'fs-1' }
            ];
            sizeOptions.forEach(function (opt) {
                const optionEl = document.createElement('option');
                optionEl.value = opt.value;
                optionEl.textContent = opt.label;
                sizeSelect.appendChild(optionEl);
            });

            sizeSelect.addEventListener('change', function () {
                selectedSize = sizeSelect.value;
            });

            topbar.appendChild(searchInput);
            topbar.appendChild(sizeSelect);
            wrap.appendChild(topbar);

            // 2. Filtres rapides (Toutes / Contour / Plein) + compteur
            const subbar = document.createElement('div');
            subbar.className = 'mc-bsi-subbar';

            const pills = document.createElement('div');
            pills.className = 'mc-bsi-pills';

            const styleFilters = [
                { key: 'all', label: _('bsicons_filter_all', 'All') },
                { key: 'outline', label: _('bsicons_filter_outline', 'Outline') },
                { key: 'fill', label: _('bsicons_filter_fill', 'Fill') }
            ];

            styleFilters.forEach(function (item) {
                const pill = document.createElement('button');
                pill.type = 'button';
                pill.className = 'mc-bsi-pill' + (item.key === activeStyle ? ' is-active' : '');
                pill.dataset.style = item.key;
                pill.textContent = item.label;

                pill.addEventListener('click', function () {
                    activeStyle = item.key;
                    pills.querySelectorAll('.mc-bsi-pill').forEach(function (p) {
                        p.classList.toggle('is-active', p.dataset.style === activeStyle);
                    });
                    applyFilter();
                });

                pills.appendChild(pill);
            });

            const countEl = document.createElement('span');
            countEl.className = 'mc-bsi-count';

            subbar.appendChild(pills);
            subbar.appendChild(countEl);
            wrap.appendChild(subbar);

            // 3. Grille scrollable avec rendu progressif
            const grid = document.createElement('div');
            grid.className = 'mc-bsi-grid';
            wrap.appendChild(grid);

            // 4. Barre d'aperçu au survol
            const footer = document.createElement('div');
            footer.className = 'mc-bsi-footer';

            const previewBox = document.createElement('span');
            previewBox.className = 'mc-bsi-preview';
            previewBox.textContent = _('bsicons_preview_hint', 'Click an icon to insert it.');

            const codeBox = document.createElement('span');
            codeBox.className = 'mc-bsi-code';
            codeBox.style.display = 'none';

            footer.appendChild(previewBox);
            footer.appendChild(codeBox);
            wrap.appendChild(footer);

            host.appendChild(wrap);

            function renderNextBatch() {
                if (renderedCount >= filteredList.length) {
                    return;
                }

                const end = Math.min(renderedCount + BATCH_SIZE, filteredList.length);
                const fragment = document.createDocumentFragment();

                for (let i = renderedCount; i < end; i++) {
                    const item = filteredList[i];
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'mc-bsi-btn';
                    btn.dataset.icon = item.icon;
                    btn.dataset.name = item.name;
                    btn.title = item.name + ' (.' + item.icon + ')';
                    btn.setAttribute('aria-label', item.name);

                    const iconEl = document.createElement('i');
                    iconEl.className = 'bi ' + item.icon;
                    btn.appendChild(iconEl);

                    fragment.appendChild(btn);
                }

                grid.appendChild(fragment);
                renderedCount = end;
            }

            /**
             * S'assure que le contenu dépasse toujours la hauteur visible pour activer la barre de scroll
             */
            function fillVisibleArea() {
                if (grid.clientHeight === 0) {
                    return;
                }
                while (renderedCount < filteredList.length && grid.scrollHeight <= grid.clientHeight + 120) {
                    renderNextBatch();
                }
            }

            function applyFilter() {
                const terms = searchQuery.trim().toLowerCase().split(/\s+/).filter(Boolean);

                filteredList = iconsData.filter(function (item) {
                    if (activeStyle === 'fill' && !item.isFill) return false;
                    if (activeStyle === 'outline' && item.isFill) return false;
                    if (terms.length === 0) return true;
                    return terms.every(function (term) {
                        return item.searchText.includes(term);
                    });
                });

                grid.innerHTML = '';
                grid.scrollTop = 0;
                renderedCount = 0;

                if (filteredList.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'mc-bsi-empty';
                    empty.textContent = _('bsicons_no_results', 'No icon found or loading...');
                    grid.appendChild(empty);
                } else {
                    renderNextBatch();
                    requestAnimationFrame(fillVisibleArea);
                }

                countEl.textContent = (activeStyle === 'all' && terms.length === 0)
                    ? _('bsicons_count_total', '{0} icons', [iconsData.length])
                    : _('bsicons_count_filtered', '{0} / {1} icons', [filteredList.length, iconsData.length]);
            }

            // Chargement automatique du lot suivant au défilement
            grid.addEventListener('scroll', function () {
                if (grid.scrollTop + grid.clientHeight >= grid.scrollHeight - 160) {
                    renderNextBatch();
                }
            });

            // Mise à jour de la barre d'aperçu au survol
            grid.addEventListener('mouseover', function (e) {
                const btn = e.target.closest('.mc-bsi-btn');
                if (!btn) return;
                const iconClass = btn.dataset.icon;
                const iconName = btn.dataset.name;
                previewBox.innerHTML = `<i class="bi ${iconClass}"></i> <span>${editor.dom.encode(iconName)}</span>`;
                codeBox.textContent = '.' + iconClass;
                codeBox.style.display = 'inline-block';
            });

            // Insertion propre sur l'instance d'éditeur courante
            grid.addEventListener('click', function (e) {
                const btn = e.target.closest('.mc-bsi-btn');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                const classes = ['bi', btn.dataset.icon];
                if (selectedSize) {
                    classes.push(selectedSize);
                }

                editor.undoManager.transact(function () {
                    editor.insertContent(`<i class="${classes.join(' ')}"></i>&nbsp;`);
                });

                dialogApi.close();
            });

            searchInput.addEventListener('input', function () {
                searchQuery = searchInput.value;
                applyFilter();
            });

            applyFilter();

            requestAnimationFrame(function () {
                searchInput.focus();
            });
        }

        const openDialog = async function () {
            if (iconsData.length === 0) {
                try {
                    await fetchIconsData();
                } catch (err) {
                    editor.notificationManager.open({
                        text: _('bsicons_error_load', 'Error loading icons via bridge.php:') + ' ' + err.message,
                        type: 'error',
                        timeout: 4000
                    });
                    return;
                }
            }

            const hostId = 'mc-bsicons-host-' + Date.now();

            const dialogApi = editor.windowManager.open({
                title: _('bsicons_title', 'Insert Bootstrap Icons'),
                size: 'large',
                body: {
                    type: 'panel',
                    items: [
                        {
                            type: 'htmlpanel',
                            html: `<div id="${hostId}" style="width:100%;"></div>`
                        }
                    ]
                },
                buttons: [{ type: 'cancel', name: 'close', text: _('bsicons_close', 'Close') }]
            });

            requestAnimationFrame(function () {
                mountIconsUI(hostId, dialogApi);
            });
        };

        editor.ui.registry.addIcon(
            'bootstrap',
            '<svg width="24" height="24" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">' +
            '<path fill="currentColor" fill-rule="evenodd" d="M5.062 12h3.475c1.804 0 2.888-.908 2.888-2.396 0-1.102-.761-1.916-1.904-2.034v-.1c.832-.14 1.482-.93 1.482-1.816 0-1.3-.955-2.11-2.542-2.11H5.062v8.456zm.925-5.156v-3.15h1.258c1.156 0 1.815.484 1.815 1.582 0 1.05-.658 1.568-1.745 1.568H5.987zm0 4.113V7.781h1.53c1.205 0 1.915.54 1.915 1.582 0 1.054-.705 1.594-1.92 1.594H5.987z"/>' +
            '<path fill="currentColor" fill-rule="evenodd" d="M0 4a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4V4zm4-3a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V4a3 3 0 0 0-3-3H4z"/>' +
            '</svg>'
        );

        editor.ui.registry.addButton('bsicons', {
            icon: 'bootstrap',
            tooltip: _('bsicons_tooltip', 'Bootstrap Icons'),
            onAction: openDialog
        });

        editor.ui.registry.addMenuItem('bsicons', {
            icon: 'bootstrap',
            text: _('bsicons_menu_item', 'Insert Bootstrap Icons'),
            onAction: openDialog
        });

        editor.on('PreInit', function () {
            if (Array.isArray(editor.contentCSS) && !editor.contentCSS.includes(BS_ICONS_CSS)) {
                editor.contentCSS.push(BS_ICONS_CSS);
            }

            if (!document.querySelector(`link[href="${BS_ICONS_CSS}"]`)) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = BS_ICONS_CSS;
                document.head.appendChild(link);
            }
        });

        return {
            getMetadata: function () {
                return {
                    name: 'Bootstrap Icons Fetcher for TinyMCE 7',
                    author: 'Gerits Aurelien',
                    version: '1.1.1',
                    url: 'https://www.magix-cms.com'
                };
            }
        };
    });
})();
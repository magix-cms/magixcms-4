/**
 * Magix CMS 4 - TinyMCE 7 Button Selector Plugin (mcbuttons)
 * (C) 2008 - 2026 Gerits Aurelien (Magix CMS)
 * Licence : GPLv3
 */
(function () {
    'use strict';

    tinymce.PluginManager.requireLangPack('mcbuttons');

    let cachedButtonsData = null;

    tinymce.PluginManager.add('mcbuttons', function (editor) {
        /**
         * Traduction avec fallback anglais et substitution {0}, {1}...
         */
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

        const buildScaleItems = function (prefix) {
            const items = [{ text: _('mcbuttons_option_default', 'Default'), value: '' }];
            for (let i = 0; i <= 5; i++) {
                items.push({ text: `${prefix}-${i}`, value: `${prefix}-${i}` });
            }
            return items;
        };

        const getRoundedItems = function () {
            return [
                { text: _('mcbuttons_option_default', 'Default'), value: '' },
                { text: _('mcbuttons_rounded_0', 'Square (rounded-0)'), value: 'rounded-0' },
                { text: _('mcbuttons_rounded_1', 'Light (rounded-1)'), value: 'rounded-1' },
                { text: _('mcbuttons_rounded_2', 'Standard (rounded-2)'), value: 'rounded-2' },
                { text: _('mcbuttons_rounded_3', 'Medium (rounded-3)'), value: 'rounded-3' },
                { text: _('mcbuttons_rounded_4', 'Pronounced (rounded-4)'), value: 'rounded-4' },
                { text: _('mcbuttons_rounded_5', 'Strong (rounded-5)'), value: 'rounded-5' },
                { text: _('mcbuttons_rounded_pill', 'Pill (rounded-pill)'), value: 'rounded-pill' },
                { text: _('mcbuttons_rounded_circle', 'Circle (rounded-circle)'), value: 'rounded-circle' }
            ];
        };

        const defaultEndpoint = (typeof window.baseadmin !== 'undefined' && window.baseadmin)
            ? '/' + window.baseadmin + '/index.php?controller=setting&action=getButtons'
            : 'index.php?controller=setting&action=getButtons';

        // API officielle TinyMCE 7
        editor.options.register('mcbuttons_endpoint', {
            processor: 'string',
            default: defaultEndpoint
        });

        const endpoint = editor.options.get('mcbuttons_endpoint');

        // Icône SVG en fill pur (standard Oxide TinyMCE 7)
        editor.ui.registry.addIcon(
            'mc-button-icon',
            '<svg width="24" height="24" viewBox="0 0 24 24">' +
            '<path d="M5 6h14a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3Zm0 2a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H5Zm3 3h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Z" fill="currentColor"/>' +
            '</svg>'
        );

        /**
         * Charge (et met en cache) les classes extraites par ButtonScanner
         */
        async function loadButtonsData() {
            if (cachedButtonsData) {
                return cachedButtonsData;
            }

            const response = await fetch(endpoint, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const json = await response.json();
            if (!json.status) {
                throw new Error(json.message || _('mcbuttons_error_generic', 'Error while loading buttons.'));
            }

            cachedButtonsData = {
                cssUrl: json.cssUrl || '',
                variants: Array.isArray(json.variants) ? json.variants : [],
                sizes: Array.isArray(json.sizes) ? json.sizes : []
            };

            return cachedButtonsData;
        }

        /**
         * Inspecte la sélection courante (édition en place + conservation des classes & icônes)
         */
        function inspectSelection(data) {
            const node = editor.selection.getNode();
            const anchor = node && node.nodeType === 1 ? node.closest('a') : null;

            const knownVariants = new Set(data.variants.map(v => v.class));
            const knownSizes = new Set(data.sizes.map(s => s.class));

            let activeVariant = data.variants.length > 0 ? data.variants[0].class : '';
            let activeSize = '';
            let px = '';
            let py = '';
            let rounded = '';
            const preservedClasses = [];

            let iconBeforeHtml = '';
            let iconAfterHtml = '';

            if (anchor) {
                Array.from(anchor.classList).forEach(cls => {
                    if (cls === 'btn') return;
                    if (knownVariants.has(cls)) {
                        activeVariant = cls;
                    } else if (knownSizes.has(cls)) {
                        activeSize = cls;
                    } else if (/^px-[0-5]$/.test(cls)) {
                        px = cls;
                    } else if (/^py-[0-5]$/.test(cls)) {
                        py = cls;
                    } else if (/^rounded-(0|1|2|3|4|5|pill|circle)$/.test(cls)) {
                        rounded = cls;
                    } else {
                        preservedClasses.push(cls);
                    }
                });

                // Conserve une éventuelle icône insérée via bsicons (avant ou après le texte)
                const children = Array.from(anchor.childNodes);
                const iconIndex = children.findIndex(
                    n => n.nodeType === 1 && (n.tagName === 'I' || n.tagName === 'SVG')
                );
                const textIndex = children.findIndex(
                    n => n.nodeType === 3 && n.textContent.trim() !== ''
                );

                if (iconIndex !== -1) {
                    const iconHtml = children[iconIndex].outerHTML;
                    if (textIndex !== -1 && iconIndex > textIndex) {
                        iconAfterHtml = ' ' + iconHtml;
                    } else {
                        iconBeforeHtml = iconHtml + ' ';
                    }
                }
            }

            return {
                anchor: anchor,
                text: anchor
                    ? (anchor.textContent || '').trim()
                    : editor.selection.getContent({ format: 'text' }).trim(),
                href: anchor ? (anchor.getAttribute('href') || '') : '',
                title: anchor ? (anchor.getAttribute('title') || '') : '',
                target: anchor ? (anchor.getAttribute('target') || '') : '',
                variant: activeVariant,
                size: activeSize,
                px: px,
                py: py,
                rounded: rounded,
                preservedClasses: preservedClasses,
                iconBeforeHtml: iconBeforeHtml,
                iconAfterHtml: iconAfterHtml
            };
        }

        /**
         * Extrait le groupe de couleur depuis une classe (.btn-main-outline -> "main")
         */
        function extractColorGroup(className) {
            const match = className.match(/^btn-([a-z0-9]+)/i);
            return match ? match[1].toLowerCase() : 'other';
        }

        /**
         * Formate le libellé affiché sur les pastilles de filtre
         */
        function formatGroupLabel(groupKey) {
            const labels = {
                main: 'Main',
                sd: 'SD',
                dark: 'Dark',
                green: 'Green',
                white: 'White'
            };
            return labels[groupKey] || (groupKey.charAt(0).toUpperCase() + groupKey.slice(1));
        }

        /**
         * Traduit les libellés des tailles standards (btn-sm, btn-lg)
         */
        function translateSizeLabel(sizeObj) {
            if (sizeObj.class === 'btn-sm') {
                return _('mcbuttons_size_sm', 'Small (btn-sm)');
            }
            if (sizeObj.class === 'btn-lg') {
                return _('mcbuttons_size_lg', 'Large (btn-lg)');
            }
            return sizeObj.label;
        }

        /**
         * Monte la grille d'aperçu visuel isolée dans un Shadow DOM
         */
        function mountShadowPreview(hostId, data, initialVariant, initialModifiers, isEditMode, onSelect) {
            const host = document.getElementById(hostId);
            if (!host) return null;

            const shadow = host.shadowRoot || host.attachShadow({ mode: 'open' });
            let currentVariant = initialVariant;
            let liveModifiers = Object.assign({ size: '', px: '', py: '', rounded: '' }, initialModifiers || {});
            let searchQuery = '';
            let isDarkBg = false;

            const uiStyle = document.createElement('style');
            uiStyle.textContent = `
                :host { display: block; font-family: system-ui, -apple-system, sans-serif; }
                .mc-btn-toolbar {
                    display: flex; justify-content: space-between; align-items: center;
                    gap: 8px; margin-bottom: 8px; font-size: 12px; color: #475569;
                }
                .mc-controls-right {
                    display: flex; align-items: center; gap: 6px;
                }
                .mc-search-input {
                    width: 170px; padding: 4px 8px; font-size: 12px;
                    border: 1px solid #cbd5e1; border-radius: 4px;
                    background: #fff; color: #1e293b; outline: none;
                }
                .mc-search-input:focus {
                    border-color: #0d6efd;
                    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
                }
                .mc-bg-toggle {
                    cursor: pointer; background: #e2e8f0; border: 1px solid #cbd5e1;
                    border-radius: 4px; padding: 4px 8px; font-size: 11px; color: #1e293b;
                    white-space: nowrap;
                }
                .mc-bg-toggle:hover { background: #cbd5e1; }
                .mc-filter-bar {
                    display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px;
                }
                .mc-filter-pill {
                    cursor: pointer; border: 1px solid #cbd5e1; background: #fff;
                    color: #334155; border-radius: 999px; padding: 3px 10px;
                    font-size: 11px; font-weight: 500; transition: all 0.15s ease;
                }
                .mc-filter-pill:hover { border-color: #94a3b8; background: #f1f5f9; }
                .mc-filter-pill.is-active {
                    background: #0d6efd; border-color: #0d6efd; color: #fff;
                }
                .mc-btn-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                    gap: 10px;
                    max-height: 210px;
                    overflow-y: auto;
                    padding: 12px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                    background: #f8fafc;
                    transition: background-color 0.15s ease;
                }
                .mc-btn-grid.is-dark { background: #1e293b; border-color: #334155; }
                .mc-btn-card {
                    display: flex; flex-direction: column; align-items: center;
                    justify-content: center; gap: 6px;
                    padding: 10px 8px;
                    border: 2px solid transparent;
                    border-radius: 6px;
                    cursor: pointer;
                    background: rgba(255, 255, 255, 0.5);
                    transition: border-color 0.15s ease;
                }
                .mc-btn-card.is-hidden { display: none; }
                .mc-btn-grid.is-dark .mc-btn-card { background: rgba(255, 255, 255, 0.06); }
                .mc-btn-card:hover { border-color: #94a3b8; }
                .mc-btn-card.is-selected {
                    border-color: #0d6efd;
                    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
                }

                /* Socle .btn Bootstrap 5.3 pour le Shadow DOM */
                .btn {
                    display: inline-block;
                    box-sizing: border-box;
                    padding: 0.375rem 0.75rem;
                    font-size: 0.875rem;
                    font-weight: 400;
                    line-height: 1.5;
                    text-align: center;
                    text-decoration: none;
                    vertical-align: middle;
                    border: 1px solid transparent;
                    border-radius: 0.375rem;
                    white-space: nowrap;
                    cursor: pointer;
                }
                .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 0.25rem; }
                .btn-lg { padding: 0.5rem 1rem; font-size: 1.0625rem; border-radius: 0.5rem; }

                .rounded-0 { border-radius: 0 !important; }
                .rounded-1 { border-radius: 0.25rem !important; }
                .rounded-2 { border-radius: 0.375rem !important; }
                .rounded-3 { border-radius: 0.5rem !important; }
                .rounded-4 { border-radius: 1rem !important; }
                .rounded-5 { border-radius: 2rem !important; }
                .rounded-pill { border-radius: 50rem !important; }
                .rounded-circle { border-radius: 50% !important; }

                .px-0 { padding-left: 0 !important; padding-right: 0 !important; }
                .px-1 { padding-left: 0.25rem !important; padding-right: 0.25rem !important; }
                .px-2 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                .px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }
                .px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
                .px-5 { padding-left: 3rem !important; padding-right: 3rem !important; }
                .py-0 { padding-top: 0 !important; padding-bottom: 0 !important; }
                .py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
                .py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
                .py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
                .py-4 { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
                .py-5 { padding-top: 3rem !important; padding-bottom: 3rem !important; }

                .mc-btn-card .btn {
                    max-width: 100%;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .mc-btn-meta {
                    font-size: 10px; color: #64748b; text-align: center;
                    word-break: break-all; line-height: 1.2;
                }
                .mc-btn-grid.is-dark .mc-btn-meta { color: #cbd5e1; }
                .mc-empty {
                    grid-column: 1 / -1; text-align: center;
                    color: #64748b; font-size: 12px; padding: 16px 8px;
                }
                .mc-btn-grid.is-dark .mc-empty { color: #cbd5e1; }
            `;

            shadow.innerHTML = '';

            // 1. Socle .btn, utilitaires Bootstrap 5.3 et styles UI en premier
            shadow.appendChild(uiStyle);

            // 2. CSS du thème ensuite pour que ses bordures/couleurs priment sur .btn
            let cssLink = null;
            if (data.cssUrl) {
                cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = data.cssUrl;
                shadow.appendChild(cssLink);
            }

            if (!data.variants.length) {
                const empty = document.createElement('div');
                empty.className = 'mc-empty';
                empty.textContent = _('mcbuttons_empty_css', 'No .btn-* class detected in the active theme buttons.css file.');
                shadow.appendChild(empty);
                return null;
            }

            const groups = [];
            data.variants.forEach(variant => {
                const g = extractColorGroup(variant.class);
                if (!groups.includes(g)) {
                    groups.push(g);
                }
            });

            // En mode édition, pré-active la pastille de couleur du bouton sélectionné
            let activeGroup = 'all';
            if (isEditMode && currentVariant && groups.length > 1) {
                const detectedGroup = extractColorGroup(currentVariant);
                if (groups.includes(detectedGroup)) {
                    activeGroup = detectedGroup;
                }
            }

            const toolbar = document.createElement('div');
            toolbar.className = 'mc-btn-toolbar';

            const countLabel = document.createElement('span');

            const controlsRight = document.createElement('div');
            controlsRight.className = 'mc-controls-right';

            const searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.className = 'mc-search-input';
            searchInput.placeholder = _('mcbuttons_search_placeholder', 'Filter (e.g. ghost, glass)...');
            searchInput.setAttribute('aria-label', _('mcbuttons_search_aria', 'Filter button styles'));

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            const bgToggle = document.createElement('button');
            bgToggle.type = 'button';
            bgToggle.className = 'mc-bg-toggle';
            bgToggle.textContent = _('mcbuttons_bg_dark', 'Dark background');

            controlsRight.appendChild(searchInput);
            controlsRight.appendChild(bgToggle);
            toolbar.appendChild(countLabel);
            toolbar.appendChild(controlsRight);
            shadow.appendChild(toolbar);

            let filterBar = null;
            if (groups.length > 1) {
                filterBar = document.createElement('div');
                filterBar.className = 'mc-filter-bar';

                const pillItems = [{ key: 'all', label: _('mcbuttons_filter_all', 'All') }].concat(
                    groups.map(g => ({ key: g, label: formatGroupLabel(g) }))
                );

                pillItems.forEach(item => {
                    const pill = document.createElement('button');
                    pill.type = 'button';
                    pill.className = 'mc-filter-pill' + (item.key === activeGroup ? ' is-active' : '');
                    pill.dataset.group = item.key;
                    pill.textContent = item.label;

                    pill.addEventListener('click', function () {
                        activeGroup = item.key;
                        filterBar.querySelectorAll('.mc-filter-pill').forEach(p => {
                            p.classList.toggle('is-active', p.dataset.group === activeGroup);
                        });
                        applyFilters();
                    });

                    filterBar.appendChild(pill);
                });

                shadow.appendChild(filterBar);
            }

            const grid = document.createElement('div');
            grid.className = 'mc-btn-grid';

            const noResultEl = document.createElement('div');
            noResultEl.className = 'mc-empty';
            noResultEl.textContent = _('mcbuttons_no_results', 'No button matches this filter.');
            noResultEl.style.display = 'none';

            const cards = [];
            let selectedCardEl = null;

            function applyPreviewClasses() {
                cards.forEach(item => {
                    const isSelected = item.variant === currentVariant;
                    item.card.classList.toggle('is-selected', isSelected);

                    if (isSelected) {
                        const cls = [
                            'btn',
                            item.variant,
                            liveModifiers.size,
                            liveModifiers.px,
                            liveModifiers.py,
                            liveModifiers.rounded
                        ].filter(Boolean);
                        item.previewBtn.className = cls.join(' ');
                    } else {
                        item.previewBtn.className = 'btn ' + item.variant;
                    }
                });
            }

            data.variants.forEach(variant => {
                const group = extractColorGroup(variant.class);
                const card = document.createElement('div');
                card.className = 'mc-btn-card';
                card.dataset.variant = variant.class;
                card.dataset.group = group;
                card.dataset.search = (variant.label + ' ' + variant.class).toLowerCase();

                if (variant.class === currentVariant) {
                    selectedCardEl = card;
                }

                const previewBtn = document.createElement('span');
                previewBtn.className = 'btn ' + variant.class;
                previewBtn.textContent = variant.label;

                const meta = document.createElement('span');
                meta.className = 'mc-btn-meta';
                meta.textContent = '.' + variant.class;

                card.appendChild(previewBtn);
                card.appendChild(meta);

                card.addEventListener('click', function () {
                    currentVariant = variant.class;
                    selectedCardEl = card;
                    applyPreviewClasses();
                    onSelect(currentVariant);
                });

                cards.push({ card: card, previewBtn: previewBtn, variant: variant.class });
                grid.appendChild(card);
            });

            grid.appendChild(noResultEl);
            shadow.appendChild(grid);

            function applyFilters() {
                const q = searchQuery.trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(item => {
                    const matchGroup = (activeGroup === 'all' || item.card.dataset.group === activeGroup);
                    const matchQuery = (!q || item.card.dataset.search.includes(q));
                    const isVisible = matchGroup && matchQuery;

                    item.card.classList.toggle('is-hidden', !isVisible);
                    if (isVisible) visibleCount++;
                });

                noResultEl.style.display = visibleCount === 0 ? 'block' : 'none';
                countLabel.textContent = (activeGroup === 'all' && !q)
                    ? _('mcbuttons_count_available', 'Button style ({0} available)', [data.variants.length])
                    : _('mcbuttons_count_filtered', 'Button style ({0} / {1})', [visibleCount, data.variants.length]);
            }

            searchInput.addEventListener('input', function () {
                searchQuery = searchInput.value;
                applyFilters();
            });

            bgToggle.addEventListener('click', function () {
                isDarkBg = !isDarkBg;
                grid.classList.toggle('is-dark', isDarkBg);
                bgToggle.textContent = isDarkBg
                    ? _('mcbuttons_bg_light', 'Light background')
                    : _('mcbuttons_bg_dark', 'Dark background');
            });

            applyPreviewClasses();
            applyFilters();

            // Positionnement automatique du scroll UNIQUEMENT en mode édition
            function scrollToSelected() {
                if (!isEditMode || !selectedCardEl || selectedCardEl.classList.contains('is-hidden')) {
                    return;
                }

                const gridRect = grid.getBoundingClientRect();
                const cardRect = selectedCardEl.getBoundingClientRect();

                if (gridRect.height === 0 || cardRect.height === 0) {
                    return;
                }

                const offsetWithinGrid = (cardRect.top - gridRect.top) + grid.scrollTop;
                const targetTop = offsetWithinGrid - (grid.clientHeight / 2) + (cardRect.height / 2);
                grid.scrollTop = Math.max(0, targetTop);
            }

            if (isEditMode && selectedCardEl) {
                requestAnimationFrame(function () {
                    requestAnimationFrame(scrollToSelected);
                });

                if (cssLink) {
                    cssLink.addEventListener('load', scrollToSelected, { once: true });
                }

                setTimeout(scrollToSelected, 120);
            }

            return {
                updateModifiers: function (newModifiers) {
                    liveModifiers = Object.assign({ size: '', px: '', py: '', rounded: '' }, newModifiers || {});
                    applyPreviewClasses();
                }
            };
        }

        /**
         * Ouvre la modale native TinyMCE 7
         */
        async function openDialog() {
            let data;
            try {
                data = await loadButtonsData();
            } catch (err) {
                editor.notificationManager.open({
                    text: _('mcbuttons_error_load', 'Unable to load button styles: {0}', [err.message]),
                    type: 'error',
                    timeout: 4000
                });
                return;
            }

            const state = inspectSelection(data);
            let selectedVariant = state.variant;
            let previewController = null;
            let currentModifiers = {
                size: state.size || '',
                px: state.px || '',
                py: state.py || '',
                rounded: state.rounded || ''
            };
            const hostId = 'mcbuttons-shadow-host-' + Date.now();

            const sizeItems = [{ text: _('mcbuttons_size_default', 'Normal (default)'), value: '' }].concat(
                data.sizes.map(s => ({ text: translateSizeLabel(s), value: s.class }))
            );

            editor.windowManager.open({
                title: state.anchor
                    ? _('mcbuttons_title_edit', 'Edit button')
                    : _('mcbuttons_title_insert', 'Insert button'),
                size: 'medium',
                initialData: {
                    text: state.text || _('mcbuttons_default_text', 'Learn more'),
                    href: state.href || '#',
                    title: state.title || '',
                    size: state.size || '',
                    target: state.target || '',
                    px: state.px || '',
                    py: state.py || '',
                    rounded: state.rounded || ''
                },
                body: {
                    type: 'panel',
                    items: [
                        {
                            type: 'htmlpanel',
                            html: `<div id="${hostId}" style="min-height:130px;"></div>`
                        },
                        {
                            type: 'grid',
                            columns: 2,
                            items: [
                                { type: 'input', name: 'text', label: _('mcbuttons_field_text', 'Button text') },
                                { type: 'input', name: 'href', label: _('mcbuttons_field_url', 'Link URL') }
                            ]
                        },
                        {
                            type: 'grid',
                            columns: 3,
                            items: [
                                { type: 'input', name: 'title', label: _('mcbuttons_field_title', 'Title attribute (SEO)') },
                                { type: 'selectbox', name: 'size', label: _('mcbuttons_field_size', 'Size'), items: sizeItems },
                                {
                                    type: 'selectbox',
                                    name: 'target',
                                    label: _('mcbuttons_field_target', 'Target'),
                                    items: [
                                        { text: _('mcbuttons_target_same', 'Same window'), value: '' },
                                        { text: _('mcbuttons_target_blank', 'New tab (_blank)'), value: '_blank' }
                                    ]
                                }
                            ]
                        },
                        {
                            type: 'grid',
                            columns: 3,
                            items: [
                                {
                                    type: 'selectbox',
                                    name: 'px',
                                    label: _('mcbuttons_field_px', 'Horizontal padding (px-*)'),
                                    items: buildScaleItems('px')
                                },
                                {
                                    type: 'selectbox',
                                    name: 'py',
                                    label: _('mcbuttons_field_py', 'Vertical padding (py-*)'),
                                    items: buildScaleItems('py')
                                },
                                {
                                    type: 'selectbox',
                                    name: 'rounded',
                                    label: _('mcbuttons_field_rounded', 'Border radius (rounded-*)'),
                                    items: getRoundedItems()
                                }
                            ]
                        }
                    ]
                },
                buttons: [
                    { type: 'cancel', text: _('mcbuttons_btn_cancel', 'Cancel') },
                    {
                        type: 'submit',
                        text: state.anchor
                            ? _('mcbuttons_btn_update', 'Update')
                            : _('mcbuttons_btn_insert', 'Insert'),
                        primary: true
                    }
                ],
                onChange: function (api, details) {
                    if (['size', 'rounded', 'px', 'py'].includes(details.name)) {
                        const form = api.getData();
                        currentModifiers = {
                            size: form.size || '',
                            px: form.px || '',
                            py: form.py || '',
                            rounded: form.rounded || ''
                        };
                        if (previewController) {
                            previewController.updateModifiers(currentModifiers);
                        }
                    }
                },
                onSubmit: function (api) {
                    const form = api.getData();
                    const text = (form.text || '').trim();
                    const href = (form.href || '#').trim();

                    if (!text) {
                        editor.notificationManager.open({
                            text: _('mcbuttons_warn_text_required', 'Please enter the button text.'),
                            type: 'warning',
                            timeout: 3000
                        });
                        return;
                    }

                    const classes = ['btn'];
                    if (selectedVariant) classes.push(selectedVariant);
                    if (form.size) classes.push(form.size);
                    if (form.px) classes.push(form.px);
                    if (form.py) classes.push(form.py);
                    if (form.rounded) classes.push(form.rounded);

                    state.preservedClasses.forEach(cls => {
                        if (!classes.includes(cls)) classes.push(cls);
                    });

                    const attrs = {
                        href: href,
                        'class': classes.join(' '),
                        title: form.title ? form.title.trim() : null,
                        target: form.target === '_blank' ? '_blank' : null,
                        rel: form.target === '_blank' ? 'noopener noreferrer' : null
                    };

                    const innerHtml = state.iconBeforeHtml + editor.dom.encode(text) + state.iconAfterHtml;

                    editor.undoManager.transact(function () {
                        if (state.anchor) {
                            editor.dom.setAttribs(state.anchor, attrs);
                            state.anchor.innerHTML = innerHtml;
                        } else {
                            const html = editor.dom.createHTML('a', attrs, innerHtml);
                            editor.insertContent(html);
                        }
                    });

                    api.close();
                }
            });

            requestAnimationFrame(function () {
                previewController = mountShadowPreview(
                    hostId,
                    data,
                    selectedVariant,
                    currentModifiers,
                    Boolean(state.anchor),
                    function (newVariant) {
                        selectedVariant = newVariant;
                    }
                );
            });
        }

        editor.ui.registry.addToggleButton('mcbuttons', {
            icon: 'mc-button-icon',
            tooltip: _('mcbuttons_tooltip', 'Insert / edit button'),
            onAction: openDialog,
            onSetup: function (buttonApi) {
                const nodeChangeHandler = function (e) {
                    const el = e.element;
                    const isBtn = !!(el && el.closest && el.closest('a.btn'));
                    buttonApi.setActive(isBtn);
                };
                editor.on('NodeChange', nodeChangeHandler);
                return function () {
                    editor.off('NodeChange', nodeChangeHandler);
                };
            }
        });

        editor.ui.registry.addMenuItem('mcbuttons', {
            icon: 'mc-button-icon',
            text: _('mcbuttons_menu_item', 'Theme button...'),
            onAction: openDialog
        });

        return {
            getMetadata: function () {
                return {
                    name: 'Magix CMS Buttons Selector',
                    author: 'Gerits Aurelien',
                    version: '1.0.0',
                    url: 'https://www.magix-cms.com'
                };
            }
        };
    });
})();
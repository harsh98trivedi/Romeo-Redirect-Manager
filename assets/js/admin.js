document.addEventListener('DOMContentLoaded', () => {
    
    // Core custom select functionality
    function initCustomSelects() {
        const selects = document.querySelectorAll('select.rr-select:not(.rr-select-native), select.rr-input:not(.rr-select-native)');
        
        selects.forEach(select => {
            if (select.closest('.rr-select-custom')) return; // Already initialized

            // Wrap and hide native select
            const wrapper = document.createElement('div');
            wrapper.className = 'rr-select-custom';
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            select.classList.add('rr-select-native');

            // Create trigger
            const trigger = document.createElement('div');
            trigger.className = 'rr-select-trigger';
            const initialText = select.options[select.selectedIndex]?.text || 'Select...';
            trigger.innerHTML = `<span class="rr-selected-text">${initialText}</span><span class="dashicons dashicons-arrow-down-alt2"></span>`;
            wrapper.appendChild(trigger);

            // Create dropdown menu
            const menu = document.createElement('div');
            menu.className = 'rr-select-dropdown';
            Array.from(select.options).forEach((option, idx) => {
                const optDiv = document.createElement('div');
                optDiv.className = 'rr-select-option' + (select.selectedIndex === idx ? ' selected' : '');
                optDiv.textContent = option.text;
                optDiv.dataset.value = option.value;
                optDiv.addEventListener('click', (e) => {
                    e.stopPropagation();
                    select.value = option.value;
                    trigger.querySelector('.rr-selected-text').textContent = option.text;
                    menu.classList.remove('show');
                    trigger.classList.remove('active');
                    
                    // Highlight selected
                    menu.querySelectorAll('.rr-select-option').forEach(o => o.classList.remove('selected'));
                    optDiv.classList.add('selected');
                    
                    // Dispatch change event to native select
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
                menu.appendChild(optDiv);
            });
            wrapper.appendChild(menu);

            // Toggle logic
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                
                // Close all other dropdowns
                document.querySelectorAll('.rr-select-dropdown.show').forEach(openMenu => {
                    if (openMenu !== menu) {
                        openMenu.classList.remove('show');
                        openMenu.previousSibling.classList.remove('active');
                    }
                });

                menu.classList.toggle('show');
                trigger.classList.toggle('active');
            });
        });
    }

    // Global click listener to close dropdowns
    document.addEventListener('click', () => {
        document.querySelectorAll('.rr-select-dropdown.show').forEach(menu => {
            menu.classList.remove('show');
            menu.previousSibling.classList.remove('active');
        });
    });

    function syncCustomSelects() {
        document.querySelectorAll('.rr-select-custom').forEach(wrapper => {
            const select = wrapper.querySelector('select');
            const triggerText = wrapper.querySelector('.rr-selected-text');
            const options = wrapper.querySelectorAll('.rr-select-option');
            
            if (select && triggerText) {
                const selectedOption = select.options[select.selectedIndex];
                triggerText.textContent = selectedOption ? selectedOption.text : 'Select...';
                
                options.forEach(opt => {
                    opt.classList.toggle('selected', opt.dataset.value === select.value);
                });
            }
        });
    }

    initCustomSelects();
    
    const dom = {
        btnNew: document.getElementById('rr-btn-new'),
        panel: document.getElementById('rr-creator-panel'),
        btnCancel: document.getElementById('rr-cancel'),
        form: document.getElementById('rr-form'),
        targetType: document.getElementById('rr-target-type'),
        groupUrl: document.getElementById('rr-group-url'),
        groupPost: document.getElementById('rr-group-post'),
        postSearchInput: document.getElementById('rr-post-search-input'),
        searchResults: document.getElementById('rr-search-results'),
        targetPostId: document.getElementById('rr-target-post-id'),
        selectedPost: document.getElementById('rr-selected-post'),
        modalTitle: document.getElementById('rr-modal-title'),
        btnSave: document.getElementById('rr-save-btn'),
        conflictWarning: document.getElementById('rr-conflict-warning'),
        overrideCheck: document.getElementById('rr-override-check'),
        slugInput: document.querySelector('[name="slug"]')
    };

    let isEditing = false;
    let editId = null;

    // Toggle Panel
    if (dom.btnNew) {
        dom.btnNew.addEventListener('click', () => {
            resetForm();
            dom.panel.classList.remove('hidden');
            dom.modalTitle.textContent = 'Create New Redirect';
            dom.btnSave.textContent = 'Save Redirect';
        });
    }

    if (dom.btnCancel) {
        dom.btnCancel.addEventListener('click', () => {
            dom.panel.classList.add('hidden');
            resetForm();
        });
    }

    // ── Auto-open creator panel when ?rr_open=new is in the URL ──
    // (used by the dashboard widget "Create Redirect" button)
    (function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('rr_open') === 'new' && dom.panel && dom.btnNew) {
            resetForm();
            dom.panel.classList.remove('hidden');
            dom.modalTitle.textContent = 'Create New Redirect';
            dom.btnSave.textContent = 'Save Redirect';
            setTimeout(() => {
                dom.panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => dom.form.querySelector('[name="slug"]')?.focus(), 400);
            }, 100);
            // Clean URL without reloading
            const clean = new URL(window.location.href);
            clean.searchParams.delete('rr_open');
            window.history.replaceState({}, '', clean.toString());
        }
    })();


    function resetForm() {
        dom.form.reset();
        isEditing = false;
        editId = null;
        dom.targetType.value = 'url';
        dom.targetType.dispatchEvent(new Event('change'));
        
        // Reset Search
        const cardSearch = document.getElementById('rr-card-search');
        if(cardSearch) cardSearch.value = '';

        dom.targetPostId.value = '';
        dom.selectedPost.classList.add('hidden');
        dom.postSearchInput.classList.remove('hidden');
        dom.searchResults.innerHTML = '';
        dom.searchResults.classList.add('hidden');
        if (dom.conflictWarning) dom.conflictWarning.classList.add('hidden');
        if (dom.overrideCheck) {
            dom.overrideCheck.checked = false;
            updateOverrideCheckboxState(dom.overrideCheck);
        }
        syncCustomSelects();
    }

    // Toggle Types
    if (dom.targetType) {
        dom.targetType.addEventListener('change', (e) => {
            if(e.target.value === 'url') {
                dom.groupUrl.classList.remove('hidden');
                dom.groupPost.classList.add('hidden');
                document.querySelector('[name="target_url"]').setAttribute('required', 'required');
                dom.targetPostId.removeAttribute('required');
            } else {
                dom.groupUrl.classList.add('hidden');
                dom.groupPost.classList.remove('hidden');
                document.querySelector('[name="target_url"]').removeAttribute('required');
            }
        });
    }

    // Card Search (Client Side) - Enhanced
    const cardSearch = document.getElementById('rr-card-search');
    const cardGrid = document.getElementById('rr-card-grid');

    if(cardSearch) {
        let noResultsMsg = document.getElementById('rr-no-results-msg');
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'rr-no-results-msg';
            noResultsMsg.className = 'rr-no-results hidden';
            noResultsMsg.innerHTML = '<div class="rr-empty-state"><span class="dashicons dashicons-search" style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 16px;"></span><h3>No redirects found</h3><p>Try adjusting your search terms.</p></div>';
            cardGrid.parentNode.insertBefore(noResultsMsg, cardGrid.nextSibling);
        }

        cardSearch.addEventListener('input', (e) => {
            const terms = e.target.value.toLowerCase().split(' ').filter(t => t.length > 0);
            const cards = cardGrid.getElementsByClassName('rr-card');
            let visibleCount = 0;

            Array.from(cards).forEach(card => {
                const slug = (card.getAttribute('data-slug') || '').toLowerCase();
                const target = (card.getAttribute('data-target') || '').toLowerCase();
                const code = (card.getAttribute('data-code') || '').toLowerCase();
                const combined = slug + ' ' + target + ' ' + code;
                
                // Check if ALL terms are present in the combined string (AND logic)
                const match = terms.every(term => combined.includes(term));

                if (match) {
                    card.style.display = '';
                    // Use a timeout to allow display change before opacity transition if needed
                    // card.style.opacity = '1'; // Removed explicit opacity to rely on CSS default
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0 && terms.length > 0) {
                noResultsMsg.classList.remove('hidden');
            } else {
                noResultsMsg.classList.add('hidden');
            }
            updateReportSummary();
        });
    }

    // Filters and View Toggles
    const filterBtns = document.querySelectorAll('.rr-filter-btn');
    const viewBtns = document.querySelectorAll('.rr-view-btn');
    const reportSummary = document.getElementById('rr-report-summary');
    let currentFilter = 'all';

    function updateReportSummary() {
        if (!reportSummary) return;
        const cards = document.querySelectorAll('.rr-card');
        let total = 0, counts = { '301': 0, '302': 0, '307': 0, '308': 0 };
        cards.forEach(card => {
            if (card.style.display !== 'none') {
                total++;
                const code = card.getAttribute('data-code');
                if (counts[code] !== undefined) counts[code]++;
            }
        });
        
        reportSummary.innerHTML = `Showing <strong>${total}</strong> redirects ` + 
            `<span class="rr-summary-chips">` +
            `<span class="rr-summary-chip" data-filter="301" style="--chip-color: #3b82f6;">301: <strong>${counts['301']}</strong></span>` +
            `<span class="rr-summary-chip" data-filter="302" style="--chip-color: #f59e0b;">302: <strong>${counts['302']}</strong></span>` +
            `<span class="rr-summary-chip" data-filter="307" style="--chip-color: #8b5cf6;">307: <strong>${counts['307']}</strong></span>` +
            `<span class="rr-summary-chip" data-filter="308" style="--chip-color: #ec4899;">308: <strong>${counts['308']}</strong></span>` +
            `</span>`;
    }

    // Interactive chips click to filter
    if (reportSummary) {
        reportSummary.addEventListener('click', (e) => {
            const chip = e.target.closest('.rr-summary-chip');
            if (chip) {
                const filterValue = chip.getAttribute('data-filter');
                const targetBtn = document.querySelector(`.rr-filter-btn[data-filter="${filterValue}"]`);
                if (targetBtn) targetBtn.click();
            }
        });
    }

    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                filterCards();
            });
        });
    }

    function filterCards() {
        const cards = document.querySelectorAll('.rr-card');
        const searchInput = document.getElementById('rr-card-search');
        let terms = [];
        if (searchInput && searchInput.value) {
            terms = searchInput.value.toLowerCase().split(' ').filter(t => t.length > 0);
        }

        let visibleCount = 0;
        cards.forEach(card => {
            let showByFilter = (currentFilter === 'all' || card.getAttribute('data-code') === currentFilter);
            let showBySearch = true;

            if (terms.length > 0) {
                const slug = (card.getAttribute('data-slug') || '').toLowerCase();
                const target = (card.getAttribute('data-target') || '').toLowerCase();
                const code = (card.getAttribute('data-code') || '').toLowerCase();
                const combined = slug + ' ' + target + ' ' + code;
                showBySearch = terms.every(term => combined.includes(term));
            }

            if (showByFilter && showBySearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle No Results Message
        const noResults = document.getElementById('rr-no-results');
        if (noResults) {
            if (visibleCount === 0) {
                const h3 = noResults.querySelector('h3');
                if (h3) {
                    if (currentFilter !== 'all') {
                        h3.textContent = 'No redirects found for ' + currentFilter;
                    } else if (searchInput && searchInput.value) {
                        h3.textContent = 'No results for "' + searchInput.value + '"';
                    } else {
                        h3.textContent = 'No redirects found';
                    }
                }
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        updateReportSummary();
    }

    // Sorting 
    const sortSelect = document.getElementById('rr-sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            sortCards();
        });
    }

    function sortCards() {
        if (!cardGrid) return;
        const cards = Array.from(cardGrid.querySelectorAll('.rr-card'));
        const sortVal = sortSelect ? sortSelect.value : 'date-desc';
        
        cards.sort((a, b) => {
            if (sortVal === 'name-asc') {
                return (a.getAttribute('data-slug') || '').localeCompare(b.getAttribute('data-slug') || '');
            } else if (sortVal === 'hits-desc') {
                const hA = parseInt(a.getAttribute('data-hits')) || 0;
                const hB = parseInt(b.getAttribute('data-hits')) || 0;
                if (hB !== hA) return hB - hA;
            } else if (sortVal.startsWith('type-')) {
                const tTarget = sortVal.split('-')[1]; // page, post, url
                const tA = a.getAttribute('data-ptype') || '';
                const tB = b.getAttribute('data-ptype') || '';
                if (tA === tTarget && tB !== tTarget) return -1;
                if (tB === tTarget && tA !== tTarget) return 1;
            }
            // fallback: date (timestamp)
            const dA = parseInt(a.getAttribute('data-added')) || 0;
            const dB = parseInt(b.getAttribute('data-added')) || 0;
            return dB - dA;
        });
        
        cards.forEach(card => cardGrid.appendChild(card));
    }

    if (viewBtns.length > 0 && cardGrid) {
        // Load saved view from cookie
        const savedView = getCookie('rr_preferred_view');
        if (savedView === 'list') {
            cardGrid.classList.remove('card-view');
            cardGrid.classList.add('list-view');
            document.querySelector('.rr-view-btn[data-view="list"]')?.classList.add('active');
            document.querySelector('.rr-view-btn[data-view="card"]')?.classList.remove('active');
        }

        viewBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                viewBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const view = btn.getAttribute('data-view');
                
                if (view === 'list') {
                    cardGrid.classList.remove('card-view');
                    cardGrid.classList.add('list-view');
                } else {
                    cardGrid.classList.remove('list-view');
                    cardGrid.classList.add('card-view');
                }
                
                // Save to cookie (30 days)
                setCookie('rr_preferred_view', view, 30);
            });
        });
    }
    
    // Initial summary
    updateReportSummary();



    // Slug Conflict Check (Debounced)
    let slugTimeout = null;
    if (dom.slugInput) {
        dom.slugInput.addEventListener('input', (e) => {
             clearTimeout(slugTimeout);
             const slug = e.target.value.trim();
             
             if (slug.length < 1) {
                 dom.conflictWarning.classList.add('hidden');
                 return;
             }

             slugTimeout = setTimeout(() => {
                 const formData = new FormData();
                 formData.append('action', 'romerema_check_conflict');
                 formData.append('slug', slug);
                 formData.append('nonce', romerema_vars.check_nonce);
                 
                 fetch(ajaxurl, { method: 'POST', body: formData })
                 .then(res => res.json())
                 .then(res => {
                     if (res.success && res.data.exists) {
                         dom.conflictWarning.classList.remove('hidden');
                         // Ensure checkbox visual state is correct when warning appears
                         if (dom.overrideCheck) updateOverrideCheckboxState(dom.overrideCheck);
                         
                         const link = document.getElementById('rr-conflict-link');
                         if(link && res.data.url) {
                             link.href = res.data.url;
                             link.textContent = res.data.url;
                             link.classList.remove('hidden');
                         } else if (link) {
                             link.classList.add('hidden');
                         }
                     } else {
                         dom.conflictWarning.classList.add('hidden');
                     }
                 });
             }, 300);
        });
    }

    // Internal Post Search (Debounced)
    let searchTimeout = null;
    if (dom.postSearchInput) {
        dom.postSearchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const term = e.target.value;
            if(term.length < 2) {
                dom.searchResults.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(ajaxurl + '?action=romerema_search_posts&nonce=' + romerema_vars.nonce + '&term=' + term)
                .then(res => res.json())
                .then(data => {
                    dom.searchResults.innerHTML = '';
                    if(data.success && data.data.length > 0) {
                        data.data.forEach(post => {
                            const div = document.createElement('div');
                            div.className = 'rr-result-item';
                            div.innerHTML = `<span>${post.title}</span> <span class="rr-badge" style="background:#f1f5f9">${post.type}</span>`;
                            div.onclick = () => selectPost(post);
                            dom.searchResults.appendChild(div);
                        });
                        dom.searchResults.classList.remove('hidden');
                    } else {
                        dom.searchResults.innerHTML = '<div style="padding:10px;text-align:center;color:#666">No results found</div>';
                        dom.searchResults.classList.remove('hidden');
                    }
                });
            }, 300);
        });
    }

    function selectPost(post) {
        dom.targetPostId.value = post.id;
        dom.selectedPost.querySelector('.text').textContent = post.title;
        dom.selectedPost.classList.remove('hidden');
        dom.postSearchInput.classList.add('hidden');
        dom.searchResults.classList.add('hidden');
    }

    const removeSelectionBtn = document.querySelector('.rr-remove-selection');
    if (removeSelectionBtn) {
        removeSelectionBtn.addEventListener('click', () => {
            dom.targetPostId.value = '';
            dom.selectedPost.classList.add('hidden');
            dom.postSearchInput.classList.remove('hidden');
            dom.postSearchInput.value = '';
            dom.postSearchInput.focus();
        });
    }

    // Save Form
    if (dom.form) {
        dom.form.addEventListener('submit', (e) => {
            handleFormSubmit(e);
        });
        
        // Keyboard Shortcuts
        dom.form.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Enter anywhere in form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                handleFormSubmit(e);
            }
        });
    }

    // Enter on Target URL input
    if (dom.form) {
        const targetUrlInput = dom.form.querySelector('[name="target_url"]');
        if (targetUrlInput) {
            targetUrlInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleFormSubmit(e);
                }
            });
        }
    }

    // ── Build a redirect card HTML from data ──
    function buildRedirectCard(r, siteUrl, dateFmt) {
        const src  = siteUrl + r.slug;
        const tgt  = r.type === 'post' ? '#' : r.target;
        const tgtDisplay = r.type === 'post' ? ('Post #' + r.target) : r.target;
        const typeLabel  = r.type === 'post' ? 'PAGE REDIRECT' : 'URL REDIRECT';
        const override   = r.override ? '1' : '0';
        const hits = r.hits || 0;
        const ptype = r.ptype || (r.type === 'post' ? 'post' : 'url');
        const addedTs = Math.floor(Date.now() / 1000);
        const trash = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
        return `<div class="rr-card" id="card-${r.id}"
                    data-slug="${r.slug.toLowerCase()}"
                    data-target="${tgtDisplay.toLowerCase()}"
                    data-code="${r.code}"
                    data-hits="${hits}"
                    data-ptype="${ptype}"
                    data-added="${addedTs}"
                    style="position:relative;opacity:0;transform:translateY(16px) scale(0.98)">
            <div class="rr-card-slug-wrap">
                <div class="rr-card-slug" title="/${r.slug}" data-copy="${src}">
                    <span class="slash">/</span><span class="rr-slug-text">${r.slug}</span>
                </div>
                <button class="rr-slug-copy rr-copy-btn" data-copy="${src}" title="Copy source URL">
                    <span class="dashicons dashicons-admin-page"></span>
                </button>
            </div>
            <div class="rr-card-info">
                <div class="rr-card-info-inner">
                    <span class="rr-info-label">${typeLabel}</span>
                    <span class="rr-info-value" title="${tgt}" data-copy="${tgt}">${tgtDisplay}</span>
                </div>
                <button class="rr-inline-copy rr-copy-btn" data-copy="${tgt}" title="Copy target URL">
                    <span class="dashicons dashicons-admin-page"></span>
                </button>
            </div>
            <div class="rr-card-footer">
                <div class="rr-status-block">
                    <div class="rr-status-dot code-${r.code}"></div>
                    <span class="rr-status-label">${r.code} Redirect</span>
                </div>
                <div class="rr-hits-badge">
                    <span class="rr-hits-num">0</span>
                    <span class="rr-hits-lbl">HITS</span>
                </div>
                <div class="rr-date-badge">${dateFmt}</div>
            </div>
            <div class="rr-card-bottom">
                <label class="rr-checkbox-wrapper rr-card-select" title="Select">
                    <input type="checkbox" class="rr-bulk-checkbox" value="${r.id}">
                    <span class="rr-checkbox-style"></span>
                </label>
                <div class="rr-card-actions-group">
                    <a href="${src}" target="_blank" class="rr-action-btn" title="Open source URL">
                        <span class="dashicons dashicons-external"></span>
                    </a>
                    <button class="rr-action-btn rr-edit-btn" title="Edit"
                        data-id="${r.id}" data-slug="${r.slug}" data-type="${r.type}"
                        data-target="${r.target}" data-code="${r.code}" data-override="${override}">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                    <button onclick="rrDelete('${r.id}')" class="rr-action-btn rr-delete-action-btn" title="Delete">
                        ${trash}
                    </button>
                </div>
            </div>
        </div>`;
    }

    // ── Toast notification ──
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.rr-toast');
        if (existing) existing.remove();
        const t = document.createElement('div');
        t.className = `rr-toast rr-toast-${type}`;
        t.textContent = message;
        document.body.appendChild(t);
        requestAnimationFrame(() => t.classList.add('rr-toast-show'));
        setTimeout(() => { t.classList.remove('rr-toast-show'); setTimeout(() => t.remove(), 300); }, 2800);
    }

    function handleFormSubmit(e) {
        if(e) e.preventDefault();
        const formData = new FormData(dom.form);
        formData.append('action', 'romerema_save_redirect');
        formData.append('nonce', romerema_vars.nonce);
        
        if (isEditing && editId) {
            formData.append('id', editId);
        }

        if (dom.overrideCheck && dom.overrideCheck.checked) {
            formData.append('override', 'true');
        } else {
            formData.append('override', 'false');
        }

        // Validation for internal posts
        if(dom.targetType.value === 'post' && !dom.targetPostId.value) {
            alert('Please select a post');
            return;
        }

        // ── Spinner animation on button ──
        const originalHTML = dom.btnSave.innerHTML;
        dom.btnSave.innerHTML = `<span class="rr-btn-spinner"></span> Saving…`;
        dom.btnSave.classList.add('rr-btn-loading');
        dom.btnSave.disabled = true;

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                const d = res.data;
                const r = d.redirect;
                const wasEdit = d.is_edit;

                // ── Success button flash ──
                dom.btnSave.innerHTML = `<span style="font-size:16px;">✓</span> ${wasEdit ? 'Updated!' : 'Saved!'}`;
                dom.btnSave.classList.remove('rr-btn-loading');
                dom.btnSave.classList.add('rr-btn-saved');

                setTimeout(() => {
                    // Close & reset panel
                    dom.panel.classList.add('hidden');
                    resetForm();
                    dom.btnSave.innerHTML = originalHTML;
                    dom.btnSave.classList.remove('rr-btn-saved');
                    dom.btnSave.disabled = false;

                    const grid = document.getElementById('rr-card-grid');
                    const noResults = document.getElementById('rr-no-results');

                    if (wasEdit) {
                        // ── Update existing card in place ──
                        const existing = document.getElementById('card-' + r.id);
                        if (existing) {
                            const tmp = document.createElement('div');
                            tmp.innerHTML = buildRedirectCard(r, d.site_url, d.date_fmt);
                            const newCard = tmp.firstElementChild;
                            // keep stagger state clean by stripping inline overrides
                            newCard.style.opacity = '';
                            newCard.style.transform = '';
                            newCard.style.transition = 'box-shadow 0.2s ease';
                            existing.replaceWith(newCard);
                            // pulse highlight
                            newCard.style.boxShadow = '0 0 0 3px rgba(250,60,52,0.25)';
                            setTimeout(() => {
                                newCard.style.boxShadow = '';
                                newCard.style.transition = ''; // Cleanup so CSS hovers work
                            }, 900);
                        }
                        sortCards(); // Ensure updated card sits at right spot
                        showToast('Redirect updated ✓');
                    } else {
                        // ── Prepend new card with fly-in ──
                        if (noResults) noResults.classList.add('hidden');
                        const tmp = document.createElement('div');
                        tmp.innerHTML = buildRedirectCard(r, d.site_url, d.date_fmt);
                        const newCard = tmp.firstElementChild;
                        if (grid) grid.prepend(newCard);
                        sortCards(); // Ensure it takes its correct place if sort is active
                        // Animate in
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                newCard.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(0.16,1,0.3,1)';
                                newCard.style.opacity = '1';
                                newCard.style.transform = 'translateY(0) scale(1)';
                                
                                // Clean up inline overrides after animation completes
                                setTimeout(() => {
                                    newCard.style.transition = '';
                                    newCard.style.transform = '';
                                    if(newCard.style.opacity === '1') newCard.style.opacity = '';
                                }, 450);
                            });
                        });
                        // Update the "Showing N redirects" counter if present
                        const countEl = document.querySelector('.rr-count-num');
                        if (countEl) countEl.textContent = parseInt(countEl.textContent || '0', 10) + 1;
                        showToast('Redirect created ✓');
                    }
                }, 700);
            } else {
                alert(res.data);
                dom.btnSave.innerHTML = originalHTML;
                dom.btnSave.classList.remove('rr-btn-loading');
                dom.btnSave.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred');
            dom.btnSave.innerHTML = originalHTML;
            dom.btnSave.classList.remove('rr-btn-loading');
            dom.btnSave.disabled = false;
        });
    }

    // Global Actions (Edit/Delete)
    window.rrDelete = function(id) {

        if(!confirm('Are you sure you want to delete this redirect?')) return;
        
        const card = document.getElementById('card-' + id);
        if (!card) return;

        // Cancel any pending entry animation and clear inline styles
        if (card._rrAnimTimer) clearTimeout(card._rrAnimTimer);
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.pointerEvents = 'none';
        card.style.opacity = '0.4';

        const formData = new FormData();
        formData.append('action', 'romerema_delete_redirect');
        formData.append('id', id);
        formData.append('nonce', romerema_vars.delete_nonce);

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                card.style.transform = 'scale(0.88) translateY(8px)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 320);
            } else {
                alert('Error deleting');
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        });
    }

    window.rrEdit = function(data) {

        
        // Open Panel
        dom.panel.classList.remove('hidden');
        dom.modalTitle.textContent = 'Edit Redirect';
        dom.btnSave.textContent = 'Update Redirect';
        isEditing = true;
        editId = data.id;

        // Scroll into view & focus input
        dom.panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(() => { dom.form.querySelector('[name="slug"]').focus(); }, 400);

        // Populate Form
        dom.form.querySelector('[name="slug"]').value = data.slug;
        dom.form.querySelector('[name="code"]').value = data.code;
        
        if (data.override === '1') {
            dom.overrideCheck.checked = true;
            // Update visual state
            updateOverrideCheckboxState(dom.overrideCheck);
            // Trigger conflict check
            dom.slugInput.dispatchEvent(new Event('input'));
        } else {
            dom.overrideCheck.checked = false;
            // Update visual state
            updateOverrideCheckboxState(dom.overrideCheck);
            dom.slugInput.dispatchEvent(new Event('input')); 
        }
        
        dom.targetType.value = data.type;
        // Trigger change to toggle views
        dom.targetType.dispatchEvent(new Event('change'));

        if(data.type === 'url') {
            dom.form.querySelector('[name="target_url"]').value = data.target;
        } else {
            if (data.target_title) {
                dom.targetPostId.value = data.target;
                dom.selectedPost.querySelector('.text').textContent = data.target_title;
                dom.selectedPost.classList.remove('hidden');
                dom.postSearchInput.classList.add('hidden');
            } else {
                 // Fallback if title missing (deleted post)
                 dom.targetPostId.value = data.target;
                 dom.selectedPost.querySelector('.text').textContent = "Post #" + data.target;
                 dom.selectedPost.classList.remove('hidden');
                 dom.postSearchInput.classList.add('hidden');
            }
        }
        syncCustomSelects();
    };

    // Event delegation for edit buttons
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.rr-edit-btn');
        if (editBtn) {
            e.preventDefault();
            const data = {
                id: editBtn.dataset.id,
                slug: editBtn.dataset.slug,
                type: editBtn.dataset.type,
                target: editBtn.dataset.target,
                code: editBtn.dataset.code,
                target: editBtn.dataset.target,
                code: editBtn.dataset.code,
                override: editBtn.dataset.override,
                target_title: editBtn.dataset.targetTitle || null
            };
            window.rrEdit(data);
        }
    });

    // 404 Settings Logic
    const form404 = document.getElementById('rr-404-form');
    if (form404) {
        const dom404 = {
            inputType: document.getElementById('rr-input-type'),
            viewUrl: document.getElementById('rr-view-url'),
            viewPost: document.getElementById('rr-view-post'),
            btns: document.querySelectorAll('.rr-segment-btn'),
            searchInput: document.getElementById('rr-404-post-search-input'),
            searchResults: document.getElementById('rr-404-search-results'),
            targetPostId: document.getElementById('rr-404-target-post-id'),
            selectedPost: document.getElementById('rr-404-selected-post'),
            removeSelection: document.querySelector('.rr-404-remove-selection')
        };

        // Tab Switching Logic (Delegation)
        form404.addEventListener('click', (e) => {
            const btn = e.target.closest('.rr-segment-btn');
            if (btn) {
                // Update Active State
                const allBtns = form404.querySelectorAll('.rr-segment-btn');
                allBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Get Elements Dynamically
                const inputType = document.getElementById('rr-input-type');
                const viewUrl = document.getElementById('rr-view-url');
                const viewPost = document.getElementById('rr-view-post');
                const viewHome = document.getElementById('rr-view-home');

                // Update Value
                const val = btn.dataset.value;
                if(inputType) inputType.value = val;

                // Reset All Views
                if (viewUrl) viewUrl.classList.add('hidden');
                if (viewPost) viewPost.classList.add('hidden');
                if (viewHome) viewHome.classList.add('hidden');

                // Toggle logic
                if (val === 'url') {
                    if (viewUrl) viewUrl.classList.remove('hidden');
                } else if (val === 'post') {
                    if (viewPost) viewPost.classList.remove('hidden');
                } else if (val === 'home') {
                    if (viewHome) viewHome.classList.remove('hidden');
                }
            }
        });

        // Search & Suggestions
        let searchTimeout404 = null;
        if (dom404.searchInput) {
            
            // Intelligent Defaults (on click/focus)
            dom404.searchInput.addEventListener('click', () => {
                 if(dom404.searchInput.value.length === 0) {
                     fetchSuggestions(''); 
                 }
            });

            dom404.searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout404);
                const term = e.target.value;
                searchTimeout404 = setTimeout(() => {
                    fetchSuggestions(term);
                }, 300);
            });

            function fetchSuggestions(term) {
                 const queryTerm = term || ''; // Empty string fetches defaults
                 
                 fetch(ajaxurl + '?action=romerema_search_posts&nonce=' + romerema_vars.nonce + '&term=' + queryTerm)
                    .then(res => res.json())
                    .then(data => {
                        dom404.searchResults.innerHTML = '';
                        if(data.success && data.data.length > 0) {
                            // Header for defaults
                            if(queryTerm === '') {
                                const header = document.createElement('div');
                                header.style.padding = '8px 12px';
                                header.style.fontSize = '11px';
                                header.style.color = '#94a3b8';
                                header.style.fontWeight = '600';
                                header.style.textTransform = 'uppercase';
                                header.textContent = 'Recent Pages';
                                dom404.searchResults.appendChild(header);
                            }

                            data.data.forEach(post => {
                                const div = document.createElement('div');
                                div.className = 'rr-result-item';
                                div.innerHTML = `<span>${post.title}</span> <span class="rr-badge" style="background:#f1f5f9">${post.type}</span>`;
                                div.onclick = () => {
                                    dom404.targetPostId.value = post.id;
                                    dom404.selectedPost.querySelector('.text').textContent = post.title;
                                    dom404.selectedPost.classList.remove('hidden');
                                    dom404.searchInput.classList.add('hidden');
                                    dom404.searchResults.classList.add('hidden');
                                };
                                dom404.searchResults.appendChild(div);
                            });
                            dom404.searchResults.classList.remove('hidden');
                        } else {
                            if (queryTerm.length > 0) {
                                dom404.searchResults.innerHTML = '<div style="padding:10px;text-align:center;color:#666">No results found</div>';
                                dom404.searchResults.classList.remove('hidden');
                            }
                        }
                    });
            }
        }

        // Remove Selection
        if (dom404.removeSelection) {
            dom404.removeSelection.addEventListener('click', () => {
                dom404.targetPostId.value = '';
                dom404.selectedPost.classList.add('hidden');
                dom404.searchInput.classList.remove('hidden');
                dom404.searchInput.value = '';
                dom404.searchInput.focus();
            });
        }

        // Save
        form404.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = document.getElementById('rr-save-404-btn');
            
            // Sync Type Input from Active Tab to ensure correctness
            const activeBtn = form404.querySelector('.rr-segment-btn.active');
            const typeInput = document.getElementById('rr-input-type');
            let currentTypeValue = '';

            if(activeBtn && typeInput) {
                currentTypeValue = activeBtn.dataset.value;
                typeInput.value = currentTypeValue;
            }

            // Validation
            if (currentTypeValue === 'post') {
                const postId = document.getElementsByName('target_post_id')[0]?.value;
                if (!postId || postId === '0') {
                    alert('Please select a target page for the 404 redirect.');
                    return;
                }
            } else if (currentTypeValue === 'url') {
                const urlInput = document.getElementsByName('url_404')[0];
                if (!urlInput || !urlInput.value.trim()) {
                    alert('Please enter a destination URL for the 404 redirect.');
                    urlInput?.focus();
                    return;
                }
            }

            const originalText = btn.textContent;
            btn.textContent = 'Saving...';
            btn.disabled = true;

            const formData = new FormData(form404);
            formData.append('action', 'romerema_save_404');
            formData.append('nonce', romerema_vars.save_404_nonce);

            fetch(ajaxurl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert('Settings saved!');
                } else {
                    alert('Error saving settings.');
                }
            })
            .catch(err => alert('Error saving settings.'))
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    }

    // ── 404 Enable/Disable toggle (404 Settings page) ──
    const toggle404 = document.getElementById('rr-404-enabled-check');
    if (toggle404) {
        toggle404.addEventListener('change', () => {
            const enabled = toggle404.checked;
            const desc = document.getElementById('rr-404-toggle-desc');
            const row  = document.getElementById('rr-404-toggle-row');

            const fd = new FormData();
            fd.append('action', 'romerema_toggle_404');
            fd.append('nonce', toggle404.dataset.nonce);
            fd.append('enabled', enabled ? 'true' : 'false');

            fetch(ajaxurl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(r => {
                if (r.success) {
                    if (desc) desc.textContent = enabled
                        ? 'Active — 404s are being redirected'
                        : 'Inactive — 404s load normally';
                    if (row) {
                        row.classList.toggle('rr-404-toggle-active', enabled);
                        row.classList.toggle('rr-404-toggle-inactive', !enabled);
                    }
                } else {
                    toggle404.checked = !enabled; // revert
                }
            })
            .catch(() => { toggle404.checked = !enabled; });
        });
    }

    // ==========================================
    // BULK ACTIONS LOGIC
    // ==========================================
    const bulkBar = document.getElementById('rr-bulk-bar');

    const selectedCount = document.getElementById('rr-selected-count');
    const bulkDeleteBtn = document.getElementById('rr-bulk-delete-btn');
    const bulkClearBtn = document.getElementById('rr-bulk-clear-btn');
    let selectedIds = new Set();

    // Toggle Checkbox
    document.addEventListener('change', (e) => {
        // Bulk Selection Logic
        if (e.target.classList.contains('rr-bulk-checkbox')) {
            const id = e.target.value;
            if (e.target.checked) {
                selectedIds.add(id);
                // Visual feedback on card
                e.target.closest('.rr-card').style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--rr-primary');
                e.target.closest('.rr-card').style.backgroundColor = '#fff1f2'; // Very light pink
                e.target.closest('.rr-card').classList.add('is-selected');
            } else {
                selectedIds.delete(id);
                e.target.closest('.rr-card').style.borderColor = 'transparent'; // Reset
                e.target.closest('.rr-card').style.backgroundColor = 'white';
                e.target.closest('.rr-card').classList.remove('is-selected');
            }
            updateBulkUI();
        }
    });
    
    // Function to update override checkbox visual state (for edit form only)
    function updateOverrideCheckboxState(checkbox) {
        const checkboxBox = checkbox.nextElementSibling;
        
        if (checkboxBox) {
            if (checkbox.checked) {
                checkboxBox.style.backgroundColor = '#fb923c'; // Strong orange fill
                checkboxBox.style.borderColor = '#ea580c';     // Solid darker orange border
                checkboxBox.style.boxShadow = 'none';          // Ensure flat
            } else {
                checkboxBox.style.backgroundColor = 'white';   // White background
                checkboxBox.style.borderColor = '#fb923c';     // Orange border
                checkboxBox.style.boxShadow = 'none';          // Ensure flat
            }
        }
    }
    
    // Initialize edit form override checkbox
    const editFormOverride = document.getElementById('rr-override-check');
    if (editFormOverride) {
        updateOverrideCheckboxState(editFormOverride);
        editFormOverride.addEventListener('change', () => {
            updateOverrideCheckboxState(editFormOverride);
            
            // If we're editing a redirect, update it in real-time
            if (isEditing && editId) {
                const isChecked = editFormOverride.checked;
                
                // Send AJAX request to update override state
                const formData = new FormData();
                formData.append('action', 'romerema_toggle_override');
                formData.append('id', editId);
                formData.append('state', isChecked);
                formData.append('nonce', romerema_vars.nonce);

                fetch(ajaxurl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if(res.success) {
                        // Update the card's override indicator if it exists
                        const card = document.querySelector(`[data-id="${editId}"]`)?.closest('.rr-card');
                        if (card) {
                            const indicator = card.querySelector('.rr-override-indicator');
                            if (isChecked) {
                                // Show indicator if it doesn't exist
                                if (!indicator) {
                                    const footer = card.querySelector('.rr-card-footer');
                                    if (footer) {
                                        const newIndicator = document.createElement('div');
                                        newIndicator.className = 'rr-override-indicator';
                                        newIndicator.style.cssText = 'display: inline-flex; align-items: center; gap: 8px;';
                                        newIndicator.innerHTML = `
                                            <div style="width: 6px; height: 6px; border-radius: 50%; background: #f97316; flex-shrink: 0;"></div>
                                            <span style="font-size: 13px; font-weight: 600; color: #ea580c;">Overridden</span>
                                        `;
                                        footer.appendChild(newIndicator);
                                    }
                                }
                            } else {
                                // Hide/remove indicator
                                if (indicator) {
                                    indicator.remove();
                                }
                            }
                        }
                    } else {
                        // Revert on failure
                        editFormOverride.checked = !isChecked;
                        updateOverrideCheckboxState(editFormOverride);
                        alert('Failed to update override setting.');
                    }
                })
                .catch(err => {
                    editFormOverride.checked = !isChecked;
                    updateOverrideCheckboxState(editFormOverride);
                    alert('Network error.');
                });
            }
        });
    }

    function updateBulkUI() {
        selectedCount.textContent = selectedIds.size;
        
        if (selectedIds.size > 0) {
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    // Clear Action
    if (bulkClearBtn) {
        bulkClearBtn.addEventListener('click', () => {
            selectedIds.forEach(id => {
                const checkedBox = document.querySelector(`.rr-bulk-checkbox[value="${id}"]`);
                if (checkedBox) {
                    checkedBox.checked = false;
                    const card = checkedBox.closest('.rr-card');
                    if (card) {
                        card.style.borderColor = 'transparent';
                        card.style.backgroundColor = 'white';
                        card.classList.remove('is-selected');
                    }
                }
            });
            selectedIds.clear();
            updateBulkUI();
        });
    }

    // Bulk Delete Action
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
            if (selectedIds.size === 0) return;

            if (!confirm(`Are you sure you want to delete ${selectedIds.size} redirects?`)) {
                return;
            }

            const btnText = bulkDeleteBtn.innerHTML;
            bulkDeleteBtn.textContent = 'Deleting...';
            bulkDeleteBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'romerema_bulk_delete');
            formData.append('nonce', romerema_vars.delete_nonce);
            
            // Append IDs as array
            selectedIds.forEach(id => formData.append('ids[]', id));

            fetch(ajaxurl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // Remove cards from DOM
                    selectedIds.forEach(id => {
                        const card = document.getElementById('card-' + id);
                        if (card) {
                            // Cancel any pending stagger animation
                            if (card._rrAnimTimer) clearTimeout(card._rrAnimTimer);
                            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            card.style.transform = 'scale(0.88) translateY(8px)';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 320);
                        }
                    });

                    // Reset
                    selectedIds.clear();
                    updateBulkUI();
                    
                    // Reset button
                    bulkDeleteBtn.innerHTML = btnText;
                    bulkDeleteBtn.disabled = false;
                } else {
                    alert('Error deleting items');
                    bulkDeleteBtn.innerHTML = btnText;
                    bulkDeleteBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed');
                bulkDeleteBtn.innerHTML = btnText;
                bulkDeleteBtn.disabled = false;
            });
        });
    }
    // ==========================================
    // EXPORT / IMPORT LOGIC
    // ==========================================
    
    // Export
    const btnExport = document.getElementById('rr-btn-export');
    if(btnExport) { 
        btnExport.addEventListener('click', (e) => {
            e.preventDefault();
            const originalText = btnExport.innerHTML;
            btnExport.innerHTML = '<span class="dashicons dashicons-update" style="animation:spin 2s linear infinite;"></span> Exporting...';
            btnExport.style.pointerEvents = 'none';

            fetch(ajaxurl + '?action=romerema_export_redirects&nonce=' + romerema_vars.export_nonce)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const dataStr = JSON.stringify(res.data, null, 2);
                    const blob = new Blob([dataStr], { type: "application/json" });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    const date = new Date();
                    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = months[date.getMonth()];
                    const year = date.getFullYear();
                    const dateStr = `${day}-${month}-${year}`;
                    a.download = `romeo-redirects-backup-${dateStr}.json`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    alert('Export failed: ' + (res.data || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Export error');
            })
            .finally(() => {
                btnExport.innerHTML = originalText;
                btnExport.style.pointerEvents = 'auto';
            });
        });
    }

    // Import
    const btnImport = document.getElementById('rr-btn-import');
    const fileInput = document.getElementById('rr-import-file');
    const importModal = document.getElementById('rr-import-modal');
    const btnCloseImport = document.getElementById('rr-btn-close-import');
    const btnMerge = document.getElementById('rr-btn-merge');
    const btnOverwrite = document.getElementById('rr-btn-overwrite');
    const importUpdateCheckbox = document.getElementById('rr-import-update');
    
    let pendingImportData = null;

    if(btnImport && fileInput) {
        btnImport.addEventListener('click', () => {
            fileInput.value = ''; // Reset
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if(!file) return;

            const reader = new FileReader();
            reader.onload = (ev) => {
                pendingImportData = ev.target.result;

                // Parse import and detect conflicts against existing slugs
                let importedRedirects = [];
                try {
                    importedRedirects = JSON.parse(pendingImportData);
                    if (!Array.isArray(importedRedirects)) importedRedirects = [];
                } catch(err) {
                    alert('Invalid JSON file.');
                    return;
                }

                // Get existing slugs from the DOM cards
                const existingSlugs = new Set();
                document.querySelectorAll('.rr-card[data-slug]').forEach(card => {
                    existingSlugs.add(card.dataset.slug.toLowerCase());
                });

                const conflicts = importedRedirects.filter(r => r.slug && existingSlugs.has(r.slug.toLowerCase()));
                const conflictCount = conflicts.length;

                // Update modal with conflict info
                const conflictSection = document.getElementById('rr-import-conflict-section');
                const conflictCountEl = document.getElementById('rr-import-conflict-count');
                const noConflictSection = document.getElementById('rr-import-no-conflict-section');

                if (conflictCountEl) conflictCountEl.textContent = conflictCount;

                if (conflictCount > 0) {
                    if (conflictSection) conflictSection.classList.remove('hidden');
                    if (noConflictSection) noConflictSection.classList.add('hidden');
                } else {
                    if (conflictSection) conflictSection.classList.add('hidden');
                    if (noConflictSection) noConflictSection.classList.remove('hidden');
                }

                if(importModal) importModal.classList.remove('hidden');
            };
            reader.readAsText(file);
        });
    }

    if(btnCloseImport) {
        btnCloseImport.addEventListener('click', () => {
            if(importModal) importModal.classList.add('hidden');
            pendingImportData = null;
        });
    }

    function doImport(mode) {
        if(!pendingImportData) return;
        
        const btn = mode === 'merge' ? btnMerge : btnOverwrite;
        const originalText = btn.textContent;
        btn.textContent = 'Importing...';
        btn.disabled = true;
        
        // Disable other button too
        const otherBtn = mode === 'merge' ? btnOverwrite : btnMerge;
        if(otherBtn) otherBtn.disabled = true;

        const updateExisting = importUpdateCheckbox ? importUpdateCheckbox.checked : false;

        const formData = new FormData();
        formData.append('action', 'romerema_import_redirects');
        formData.append('nonce', romerema_vars.import_nonce);
        formData.append('data', pendingImportData);
        formData.append('mode', mode);
        formData.append('update_existing', updateExisting);

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                // Show logs modal
                const logsStr = res.data.logs.join("\n");
                const importStatusText = document.getElementById('rr-import-status-text');
                const importLogsContent = document.getElementById('rr-import-logs-content');
                if (importStatusText && importLogsContent) {
                    importStatusText.textContent = `Completed processing. Success: ${res.data.success_count} | Failed: ${res.data.failed_count}`;
                    importLogsContent.textContent = logsStr;
                    
                    if (importModal) importModal.classList.add('hidden');
                    const logsModal = document.getElementById('rr-import-logs-modal');
                    if (logsModal) logsModal.classList.remove('hidden');
                } else {
                    alert('Successfully imported ' + res.data.success_count + ' redirects.');
                    location.reload();
                }
            } else {
                alert('Import failed: ' + (res.data || 'Unknown error'));
                btn.textContent = originalText;
                btn.disabled = false;
                if(otherBtn) otherBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Import error');
            btn.textContent = originalText;
            btn.disabled = false;
            if(otherBtn) otherBtn.disabled = false;
        });
    }

    const btnCloseLogs = document.getElementById('rr-btn-close-logs');
    const btnCloseLogsIcon = document.getElementById('rr-btn-close-logs-icon');
    if (btnCloseLogs) btnCloseLogs.addEventListener('click', () => location.reload());
    if (btnCloseLogsIcon) btnCloseLogsIcon.addEventListener('click', () => location.reload());

    if(btnMerge) btnMerge.addEventListener('click', () => doImport('merge'));
    if(btnOverwrite) {
        btnOverwrite.addEventListener('click', () => {
             if(confirm('Are you sure you want to OVERWRITE? This will delete all existing redirects!')) {
                 doImport('overwrite');
             }
        });
    }

    // Select All Logic
    const btnSelectAll = document.getElementById('rr-bulk-select-all-btn');
    if(btnSelectAll) {
        btnSelectAll.addEventListener('click', () => {
             const allCheckboxes = document.querySelectorAll('.rr-bulk-checkbox');
             
             allCheckboxes.forEach(cb => {
                 const card = cb.closest('.rr-card');
                 // Only select if card is visible (respects search)
                 if(card && card.style.display !== 'none') {
                     if(!cb.checked) {
                         cb.checked = true;
                         selectedIds.add(cb.value);
                         card.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--rr-primary');
                         card.style.backgroundColor = '#fff1f2';
                         card.classList.add('is-selected');
                     }
                 }
             });
             updateBulkUI();
        });
    }

// ==========================================
// DRAG SELECTION LOGIC (with auto-scroll)
// ==========================================
 const grid = document.getElementById('rr-card-grid');
 if (grid) {
     let isDragging = false;
     let startX, startY;
     let box = null;
     let lastMouseX = 0, lastMouseY = 0;
     let scrollAnimFrame = null;
     const SCROLL_ZONE = 60;  // px from edge to trigger scroll
     const SCROLL_SPEED = 12; // px per frame

     function autoScroll() {
         if (!isDragging || !box) return;

         const vw = window.innerWidth;
         const vh = window.innerHeight;
         let scrolled = false;

         if (lastMouseY < SCROLL_ZONE) {
             window.scrollBy(0, -SCROLL_SPEED);
             scrolled = true;
         } else if (lastMouseY > vh - SCROLL_ZONE) {
             window.scrollBy(0, SCROLL_SPEED);
             scrolled = true;
         }

         if (lastMouseX < SCROLL_ZONE) {
             window.scrollBy(-SCROLL_SPEED, 0);
             scrolled = true;
         } else if (lastMouseX > vw - SCROLL_ZONE) {
             window.scrollBy(SCROLL_SPEED, 0);
             scrolled = true;
         }

         if (scrolled) {
             // Re-calculate box position after scroll
             updateDragBox(lastMouseX, lastMouseY);
         }

         scrollAnimFrame = requestAnimationFrame(autoScroll);
     }

     function updateDragBox(currentX, currentY) {
         if (!box) return;
         // Use pageX/pageY equivalent: clientX + scrollX
         const scrollX = window.scrollX || window.pageXOffset;
         const scrollY = window.scrollY || window.pageYOffset;

         const absCurrentX = currentX + scrollX;
         const absCurrentY = currentY + scrollY;
         const absStartX = startX;
         const absStartY = startY;

         const width  = Math.abs(absCurrentX - absStartX);
         const height = Math.abs(absCurrentY - absStartY);
         const left   = Math.min(absCurrentX, absStartX);
         const top    = Math.min(absCurrentY, absStartY);

         box.style.width  = width  + 'px';
         box.style.height = height + 'px';
         box.style.left   = left   + 'px';
         box.style.top    = top    + 'px';

         // PREVIEW PHASE: show subtle border on cards inside the drag box
         const boxRect = box.getBoundingClientRect();
         document.querySelectorAll('.rr-card').forEach(card => {
             if (card.style.display === 'none') return;
             if (card.classList.contains('is-selected')) return;
             const r = card.getBoundingClientRect();
             const hit = !(boxRect.right < r.left || boxRect.left > r.right || boxRect.bottom < r.top || boxRect.top > r.bottom);
             card.classList.toggle('drag-preview', hit);
         });
     }

     document.addEventListener('mousedown', (e) => {
         // Only allow drag selection in CARD VIEW
         if (!grid.classList.contains('card-view')) return;

         // Check validity: must be inside card view section, but NOT on search or filters
         const mainArea = e.target.closest('[data-view="card"]');
         if (!mainArea) return;
         if (e.target.closest('.rr-search-container') || e.target.closest('.rr-filters')) return;
         if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input') || e.target.closest('select') || e.target.closest('.rr-modal-overlay')) return;
         // Do NOT start drag if user clicks directly on a card (that's for checkbox only)
         if (e.target.closest('.rr-card')) return;

         isDragging = true;
         const scrollX = window.scrollX || window.pageXOffset;
         const scrollY = window.scrollY || window.pageYOffset;
         startX = e.clientX + scrollX;
         startY = e.clientY + scrollY;
         lastMouseX = e.clientX;
         lastMouseY = e.clientY;

         box = document.createElement('div');
         box.className = 'rr-drag-box';
         box.style.position = 'absolute'; // absolute so it moves with page scroll
         document.body.appendChild(box);

         // Disable text selection globally
         document.body.style.userSelect = 'none';
         document.body.style.webkitUserSelect = 'none';

         // Start auto-scroll loop
         scrollAnimFrame = requestAnimationFrame(autoScroll);
     });

     document.addEventListener('mousemove', (e) => {
         if (!isDragging || !box) return;
         e.preventDefault();

         lastMouseX = e.clientX;
         lastMouseY = e.clientY;

         updateDragBox(e.clientX, e.clientY);
     });

     document.addEventListener('mouseup', () => {
         if (isDragging && box) {
             cancelAnimationFrame(scrollAnimFrame);
             // COMMIT PHASE: turn all previewed cards into fully selected
             document.querySelectorAll('.rr-card.drag-preview').forEach(card => {
                 card.classList.remove('drag-preview');
                 const cb = card.querySelector('.rr-bulk-checkbox');
                 if (cb && !cb.checked) {
                     cb.checked = true;
                     if (selectedIds && !selectedIds.has(cb.value)) {
                         selectedIds.add(cb.value);
                         card.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--rr-primary');
                         card.style.backgroundColor = '#fff1f2';
                         card.classList.add('is-selected');
                     }
                 }
             });
             box.remove();
             box = null;
             updateBulkUI();
         }
         isDragging = false;
         document.body.style.userSelect = '';
         document.body.style.webkitUserSelect = '';
     });
 }

 // ==========================================
 // COPY FUNCTIONALITY
 // ==========================================
  document.addEventListener('click', (e) => {
      const btn = e.target.closest('.rr-copy-btn');
      const textElement = e.target.closest('.rr-card-slug, .rr-info-value');
      
      if(btn || textElement) {
          e.preventDefault();
          e.stopPropagation();
          
          const target = btn || textElement;
          const textToCopy = target.dataset.copy;
          
          if(textToCopy) {
              fallbackCopyTextToClipboard(textToCopy);
              
              // Find associated button for feedback if text was clicked
              let feedbackBtn = btn;
              if (textElement) {
                  feedbackBtn = textElement.closest('.rr-card-slug-wrap, .rr-card-info')?.querySelector('.rr-copy-btn');
              }
              
              if (feedbackBtn) showCopyFeedback(feedbackBtn);
          }
      }
  });

 function fallbackCopyTextToClipboard(text) {
     if (!navigator.clipboard) {
         const textArea = document.createElement("textarea");
         textArea.value = text;
         
         // Avoid scrolling to bottom
         textArea.style.top = "0";
         textArea.style.left = "0";
         textArea.style.position = "fixed";
         
         document.body.appendChild(textArea);
         textArea.focus();
         textArea.select();
         
         try {
             document.execCommand('copy');
         } catch (err) {
             console.error('Fallback: Oops, unable to copy', err);
         }
         
         document.body.removeChild(textArea);
         return;
     }
     navigator.clipboard.writeText(text).then(function() {
         // Success
     }, function(err) {
         console.error('Async: Could not copy text: ', err);
     });
 }

 function showCopyFeedback(btn) {
     const icon = btn.querySelector('.dashicons');
     if(icon) {
         const originalClass = icon.className;
         icon.className = 'dashicons dashicons-yes';
         icon.style.color = '#10b981'; // Green
         btn.style.backgroundColor = '#dcfce7'; // Light Green Bg
         
         setTimeout(() => {
              icon.className = originalClass;
              icon.style.color = '';
              btn.style.backgroundColor = '';
         }, 1500);
     }
 }

  // Cookie Helpers
  function setCookie(name, value, days) {
      let expires = "";
      if (days) {
          let date = new Date();
          date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
          expires = "; expires=" + date.toUTCString();
      }
      document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }

  function getCookie(name) {
      let nameEQ = name + "=";
      let ca = document.cookie.split(';');
      for (let i = 0; i < ca.length; i++) {
          let c = ca[i];
          while (c.charAt(0) == ' ') c = c.substring(1, c.length);
          if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
  }

  // ── Staggered card entry animation on page load ──
  // Store timer IDs on each card element so delete can cancel them
  (function animateCardsIn() {
      const cards = document.querySelectorAll('.rr-card');
      cards.forEach((card, i) => {
          card.style.opacity = '0';
          card.style.transform = 'translateY(18px) scale(0.98)';
          card.style.transition = 'none';
          const tid = setTimeout(() => {
              card._rrAnimTimer = null;
              card.style.transition = 'opacity 0.35s ease, transform 0.35s cubic-bezier(0.16,1,0.3,1)';
              card.style.opacity = '1';
              card.style.transform = 'translateY(0) scale(1)';
          }, 40 + i * 35); // 35ms stagger per card
          card._rrAnimTimer = tid;
      });
  })();

});

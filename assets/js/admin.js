document.addEventListener('DOMContentLoaded', () => {
    
    // DOM Elements
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
                const combined = slug + ' ' + target;
                
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
        });
    }



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

        const originalText = dom.btnSave.textContent;
        dom.btnSave.textContent = 'Saving...';
        dom.btnSave.disabled = true;

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                location.reload();
            } else {
                alert(res.data);
                dom.btnSave.textContent = originalText;
                dom.btnSave.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred');
            dom.btnSave.textContent = originalText;
            dom.btnSave.disabled = false;
        });
    }

    // Global Actions (Edit/Delete)
    window.rrDelete = function(id) {
        if(!confirm('Are you sure you want to delete this redirect?')) return;
        
        const card = document.getElementById('card-' + id);
        // Optimistic UI interaction
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('action', 'romerema_delete_redirect');
        formData.append('id', id);
        formData.append('nonce', romerema_vars.delete_nonce);

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                card.style.transform = 'scale(0.9)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
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
            if(activeBtn && typeInput) {
                typeInput.value = activeBtn.dataset.value;
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
                            card.style.transform = 'scale(0.9)';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
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
            reader.onload = (e) => {
                pendingImportData = e.target.result;
                // Show Modal
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
                alert('Successfully imported ' + res.data + ' redirects.');
                location.reload();
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
// DRAG SELECTION LOGIC
// ==========================================
 const grid = document.getElementById('rr-card-grid');
 if (grid) {
     let isDragging = false;
     let startX, startY;
     let box = null;
     
     // Throttling for performance
     let ticking = false;

     document.addEventListener('mousedown', (e) => {
          // Check validity: must be inside wrapper, but NOT on interactive elements
          if (!e.target.closest('.rr-wrapper')) return;
          if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input') || e.target.closest('select') || e.target.closest('.rr-modal-overlay')) return;
          
          isDragging = true;
          startX = e.clientX;
          startY = e.clientY;
          
          box = document.createElement('div');
          box.className = 'rr-drag-box';
          document.body.appendChild(box);
          
          // Disable text selection globally
          document.body.style.userSelect = 'none';
          document.body.style.webkitUserSelect = 'none';
     });

     document.addEventListener('mousemove', (e) => {
         if (!isDragging || !box) return;
         e.preventDefault(); // Stop text selection

         const currentX = e.clientX;
         const currentY = e.clientY;
         
         const width = Math.abs(currentX - startX);
         const height = Math.abs(currentY - startY);
         const left = Math.min(currentX, startX);
         const top = Math.min(currentY, startY);
         
         box.style.width = width + 'px';
         box.style.height = height + 'px';
         box.style.left = left + 'px';
         box.style.top = top + 'px';
         
         if(!ticking) {
             window.requestAnimationFrame(() => {
                 checkIntersections({ left, top, right: left+width, bottom: top+height });
                 ticking = false;
             });
             ticking = true;
         }
     });

     document.addEventListener('mouseup', () => {
          if (isDragging && box) {
              box.remove();
              box = null;
          }
          isDragging = false;
          // Re-enable text selection
          document.body.style.userSelect = '';
          document.body.style.webkitUserSelect = '';
     });

     function checkIntersections(boxRect) {
         const cards = document.querySelectorAll('.rr-card');
         cards.forEach(card => {
             // Skip if hidden
             if(card.style.display === 'none') return;
             
             const cardRect = card.getBoundingClientRect();
             const isInter = !(
                 boxRect.right < cardRect.left || 
                 boxRect.left > cardRect.right || 
                 boxRect.bottom < cardRect.top || 
                 boxRect.top > cardRect.bottom
             );
             
             if (isInter) {
                 const cb = card.querySelector('.rr-bulk-checkbox');
                 if (cb && !cb.checked) {
                     cb.checked = true;
                     // Trigger existing change logic
                     // We manually trigger the logic because native 'change' isn't fired by JS
                     if(selectedIds && !selectedIds.has(cb.value)) {
                         selectedIds.add(cb.value);
                         card.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--rr-primary');
                         card.style.backgroundColor = '#fff1f2';
                         card.classList.add('is-selected');
                     }
                 }
             }
         });
         updateBulkUI();
     }
 }

 // ==========================================
 // COPY FUNCTIONALITY
 // ==========================================
 document.addEventListener('click', (e) => {
     const btn = e.target.closest('.rr-copy-btn');
     if(btn) {
         e.preventDefault();
         e.stopPropagation();
         const textToCopy = btn.dataset.copy;
         if(textToCopy) {
             fallbackCopyTextToClipboard(textToCopy);
             showCopyFeedback(btn);
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


});

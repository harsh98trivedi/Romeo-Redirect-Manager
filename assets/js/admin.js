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
        btnSave: document.getElementById('rr-save-btn')
    };

    let isEditing = false;
    let editId = null;

    // Toggle Panel
    dom.btnNew.addEventListener('click', () => {
        resetForm();
        dom.panel.classList.remove('hidden');
        dom.modalTitle.textContent = 'Create New Redirect';
        dom.btnSave.textContent = 'Save Redirect';
    });

    dom.btnCancel.addEventListener('click', () => {
        dom.panel.classList.add('hidden');
        resetForm();
    });

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
    }

    // Toggle Types
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

    // Internal Post Search (Debounced)
    let searchTimeout = null;
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

    function selectPost(post) {
        dom.targetPostId.value = post.id;
        dom.selectedPost.querySelector('.text').textContent = post.title;
        dom.selectedPost.classList.remove('hidden');
        dom.postSearchInput.classList.add('hidden');
        dom.searchResults.classList.add('hidden');
    }

    document.querySelector('.rr-remove-selection').addEventListener('click', () => {
        dom.targetPostId.value = '';
        dom.selectedPost.classList.add('hidden');
        dom.postSearchInput.classList.remove('hidden');
        dom.postSearchInput.value = '';
        dom.postSearchInput.focus();
    });

    // Save Form
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

    // Enter on Target URL input
    const targetUrlInput = dom.form.querySelector('[name="target_url"]');
    if (targetUrlInput) {
        targetUrlInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleFormSubmit(e);
            }
        });
    }

    function handleFormSubmit(e) {
        if(e) e.preventDefault();
        const formData = new FormData(dom.form);
        formData.append('action', 'romerema_save_redirect');
        formData.append('nonce', romerema_vars.nonce);
        
        if (isEditing && editId) {
            formData.append('id', editId);
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
    }

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
                target_title: editBtn.dataset.targetTitle || null
            };
            window.rrEdit(data);
        }
    });

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
        if (e.target.classList.contains('rr-bulk-checkbox')) {
            const id = e.target.value;
            if (e.target.checked) {
                selectedIds.add(id);
                // Visual feedback on card
                e.target.closest('.rr-card').style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--rr-primary');
                e.target.closest('.rr-card').style.backgroundColor = '#fff1f2'; // Very light pink
            } else {
                selectedIds.delete(id);
                e.target.closest('.rr-card').style.borderColor = 'transparent'; // Reset
                e.target.closest('.rr-card').style.backgroundColor = 'white';
            }
            updateBulkUI();
        }
    });

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
                     }
                 }
             });
             updateBulkUI();
        });
    }
});

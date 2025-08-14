/**
 * Settings Export/Import Manager
 * Handles all functionality related to exporting and importing user settings
 */

class SettingsExportManager {
    constructor() {
        this.currentFile = null;
        this.currentPreview = null;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        this.endpoints = {
            export: '/settings-export/export',
            preview: '/settings-export/preview',
            import: '/settings-export/import',
            reset: '/settings-export/reset',
            resolveConflicts: '/settings-export/resolve-conflicts'
        };
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeComponents();
    }

    bindEvents() {
        // Export button
        const exportBtn = document.getElementById('exportButton');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => this.handleExport(e));
        }

        // Preview button
        const previewBtn = document.getElementById('previewButton');
        if (previewBtn) {
            previewBtn.addEventListener('click', (e) => this.handlePreview(e));
        }

        // Import button
        const importBtn = document.getElementById('confirmImportButton');
        if (importBtn) {
            importBtn.addEventListener('click', (e) => this.handleImport(e));
        }

        // Cancel import button
        const cancelBtn = document.getElementById('cancelImportButton');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => this.cancelImport(e));
        }

        // Reset button
        const resetBtn = document.getElementById('resetButton');
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => this.handleReset(e));
        }
    }

    initializeComponents() {
        this.initCategorySelector();
        this.initFormatSelector();
        this.initDropzone();
        this.initMergeStrategy();
    }

    initCategorySelector() {
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.type !== 'checkbox') {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                }
                
                item.classList.toggle('selected', item.querySelector('input[type="checkbox"]').checked);
            });
        });
    }

    initFormatSelector() {
        document.querySelectorAll('.format-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.format-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                option.querySelector('input[type="radio"]').checked = true;
            });
        });
    }

    initDropzone() {
        const dropzone = document.getElementById('importDropzone');
        const fileInput = document.getElementById('importFile');

        if (!dropzone || !fileInput) return;

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileSelect(e.target.files[0]);
            }
        });
    }

    initMergeStrategy() {
        document.querySelectorAll('.merge-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.merge-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                option.querySelector('input[type="radio"]').checked = true;
            });
        });
    }

    handleFileSelect(file) {
        if (!this.validateFile(file)) {
            return;
        }

        this.currentFile = file;
        const reader = new FileReader();
        
        reader.onload = (e) => {
            try {
                const data = JSON.parse(e.target.result);
                this.displayFileInfo(file, data);
                this.enablePreview(true);
            } catch (error) {
                this.showStatus('error', 'Invalid JSON file format.');
                this.enablePreview(false);
            }
        };
        
        reader.onerror = () => {
            this.showStatus('error', 'Failed to read file.');
            this.enablePreview(false);
        };
        
        reader.readAsText(file);
    }

    validateFile(file) {
        // Check file type
        if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
            this.showStatus('error', 'Please select a JSON file.');
            return false;
        }

        // Check file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            this.showStatus('error', 'File size must be less than 2MB.');
            return false;
        }

        return true;
    }

    displayFileInfo(file, data) {
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileCategories = document.getElementById('fileCategories');
        const fileInfo = document.getElementById('fileInfo');
        const dropzone = document.getElementById('importDropzone');

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = this.formatFileSize(file.size);
        
        if (fileCategories) {
            const categories = data.meta?.categories || Object.keys(data.data || {});
            fileCategories.textContent = categories.join(', ');
        }
        
        if (fileInfo) fileInfo.classList.add('show');
        if (dropzone) dropzone.classList.add('has-file');
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    enablePreview(enabled) {
        const previewBtn = document.getElementById('previewButton');
        if (previewBtn) {
            previewBtn.disabled = !enabled;
        }
    }

    async handleExport(e) {
        e.preventDefault();
        
        const button = e.target.closest('button');
        const categories = this.getSelectedCategories('export_');
        const format = this.getSelectedFormat();

        if (categories.length === 0) {
            this.showStatus('error', 'Please select at least one category to export.');
            return;
        }

        this.setButtonLoading(button, true);

        try {
            const formData = new FormData();
            categories.forEach(category => formData.append('categories[]', category));
            formData.append('format', format);

            const response = await this.makeRequest(this.endpoints.export, formData);

            if (response.ok) {
                await this.downloadFile(response, format);
                this.showStatus('success', 'Settings exported successfully!');
            } else {
                const errorData = await response.json();
                this.showStatus('error', errorData.message || 'Export failed.');
            }
        } catch (error) {
            this.showStatus('error', 'Export failed: ' + error.message);
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    async handlePreview(e) {
        e.preventDefault();
        
        const button = e.target.closest('button');
        const mergeStrategy = this.getSelectedMergeStrategy();

        if (!this.currentFile) {
            this.showStatus('error', 'Please select a file to import.');
            return;
        }

        this.setButtonLoading(button, true);

        try {
            const formData = new FormData();
            formData.append('import_file', this.currentFile);
            formData.append('merge_strategy', mergeStrategy);

            const response = await this.makeRequest(this.endpoints.preview, formData);
            const data = await response.json();

            if (data.success) {
                this.currentPreview = data.preview;
                this.displayPreview(data.preview);
                this.showPreviewContainer(true);
            } else {
                this.showStatus('error', data.message || 'Preview failed.');
            }
        } catch (error) {
            this.showStatus('error', 'Preview failed: ' + error.message);
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    async handleImport(e) {
        e.preventDefault();
        
        const button = e.target.closest('button');
        const mergeStrategy = this.getSelectedMergeStrategy();
        const selectedCategories = this.getSelectedCategories('import_');

        this.setButtonLoading(button, true);
        this.showProgress(true);

        try {
            const formData = new FormData();
            formData.append('import_file', this.currentFile);
            formData.append('merge_strategy', mergeStrategy);
            formData.append('preview_confirmed', 'true');
            
            if (selectedCategories.length > 0) {
                selectedCategories.forEach(category => formData.append('categories[]', category));
            }

            const response = await this.makeRequest(this.endpoints.import, formData);
            const data = await response.json();

            if (data.success) {
                this.showStatus('success', `Settings imported successfully! ${data.result.imported_count} items imported.`);
                this.resetImportForm();
                
                // Handle conflicts if any
                if (data.result.conflicts && data.result.conflicts.length > 0) {
                    this.displayConflicts(data.result.conflicts);
                }
            } else {
                this.showStatus('error', data.message || 'Import failed.');
            }
        } catch (error) {
            this.showStatus('error', 'Import failed: ' + error.message);
        } finally {
            this.setButtonLoading(button, false);
            this.showProgress(false);
        }
    }

    cancelImport(e) {
        e.preventDefault();
        this.showPreviewContainer(false);
        this.currentPreview = null;
    }

    async handleReset(e) {
        e.preventDefault();
        
        const button = e.target.closest('button');
        const categories = this.getSelectedCategories('reset_');
        const createBackup = document.getElementById('createBackup')?.checked || false;

        if (categories.length === 0) {
            this.showStatus('error', 'Please select at least one category to reset.');
            return;
        }

        if (!confirm('Are you sure you want to reset the selected settings? This action cannot be undone.')) {
            return;
        }

        this.setButtonLoading(button, true);

        try {
            const formData = new FormData();
            categories.forEach(category => formData.append('categories[]', category));
            formData.append('create_backup', createBackup ? '1' : '0');
            formData.append('confirm_reset', '1');

            const response = await this.makeRequest(this.endpoints.reset, formData);
            const data = await response.json();

            if (data.success) {
                let message = data.message;
                if (data.backup_file) {
                    message += ' Backup created.';
                }
                this.showStatus('success', message);
            } else {
                this.showStatus('error', data.message || 'Reset failed.');
            }
        } catch (error) {
            this.showStatus('error', 'Reset failed: ' + error.message);
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    getSelectedCategories(prefix) {
        return Array.from(document.querySelectorAll(`input[id^="${prefix}"]:checked`))
                   .map(checkbox => checkbox.value);
    }

    getSelectedFormat() {
        return document.querySelector('input[name="export_format"]:checked')?.value || 'json';
    }

    getSelectedMergeStrategy() {
        return document.querySelector('input[name="merge_strategy"]:checked')?.value || 'merge';
    }

    async makeRequest(url, formData) {
        const headers = {};
        
        if (this.csrfToken) {
            headers['X-CSRF-TOKEN'] = this.csrfToken;
        }

        return fetch(url, {
            method: 'POST',
            body: formData,
            headers
        });
    }

    async downloadFile(response, format) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        
        a.style.display = 'none';
        a.href = url;
        
        // Get filename from response headers or generate one
        const contentDisposition = response.headers.get('content-disposition');
        const filename = contentDisposition ? 
            contentDisposition.split('filename=')[1].replace(/"/g, '') :
            `hdtickets-settings-${Date.now()}.${format}`;
        
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    displayPreview(preview) {
        const container = document.getElementById('previewContent');
        if (!container) return;

        let html = '';

        if (preview.total_changes === 0) {
            html = '<p>No changes will be made. All imported settings match your current settings.</p>';
        } else {
            html += `<p><strong>Total changes:</strong> ${preview.total_changes}</p>`;

            // Display changes by category
            Object.entries(preview.changes).forEach(([category, changes]) => {
                if (changes.length > 0) {
                    html += this.renderCategoryChanges(category, changes);
                }
            });

            // Display new items
            Object.entries(preview.new_items).forEach(([category, items]) => {
                if (items.length > 0) {
                    html += this.renderNewItems(category, items);
                }
            });

            // Display conflicts
            if (preview.conflicts.length > 0) {
                html += this.renderConflicts(preview.conflicts);
            }
        }

        container.innerHTML = html;
    }

    renderCategoryChanges(category, changes) {
        let html = `<div class="preview-section">`;
        html += `<h4>${this.capitalizeFirst(category)} Changes</h4>`;
        
        changes.forEach(change => {
            html += `<div class="preview-item">`;
            html += `<strong>${change.type}:</strong> ${change.key || change.team || change.venue || change.preference || change.channel}<br>`;
            
            if (change.from !== undefined && change.to !== undefined) {
                html += `<small>From: ${this.formatValue(change.from)} → To: ${this.formatValue(change.to)}</small>`;
            }
            
            html += `</div>`;
        });
        
        html += `</div>`;
        return html;
    }

    renderNewItems(category, items) {
        let html = `<div class="preview-section">`;
        html += `<h4>New ${this.capitalizeFirst(category)}</h4>`;
        
        items.forEach(item => {
            html += `<div class="preview-item new">`;
            html += `<strong>New ${item.type}:</strong> ${item.key || item.data?.team_name || item.data?.venue_name || item.data?.preference_name || item.channel}`;
            html += `</div>`;
        });
        
        html += `</div>`;
        return html;
    }

    renderConflicts(conflicts) {
        let html = `<div class="preview-section">`;
        html += `<h4>Conflicts (${conflicts.length})</h4>`;
        
        conflicts.forEach(conflict => {
            html += `<div class="preview-item conflict">`;
            html += `<strong>Conflict:</strong> ${conflict.id}<br>`;
            html += `<small>Existing: ${this.formatValue(conflict.existing)}<br>`;
            html += `Import: ${this.formatValue(conflict.import)}</small>`;
            html += `</div>`;
        });
        
        html += `</div>`;
        return html;
    }

    displayConflicts(conflicts) {
        // Implementation for displaying conflict resolution UI
        console.log('Conflicts to resolve:', conflicts);
        // This would show a modal or section for users to resolve conflicts
    }

    resetImportForm() {
        const fileInput = document.getElementById('importFile');
        const fileInfo = document.getElementById('fileInfo');
        const dropzone = document.getElementById('importDropzone');
        const previewBtn = document.getElementById('previewButton');

        if (fileInput) fileInput.value = '';
        if (fileInfo) fileInfo.classList.remove('show');
        if (dropzone) dropzone.classList.remove('has-file');
        if (previewBtn) previewBtn.disabled = true;

        this.showPreviewContainer(false);
        this.currentFile = null;
        this.currentPreview = null;
    }

    showPreviewContainer(show) {
        const container = document.getElementById('previewContainer');
        if (container) {
            container.classList.toggle('show', show);
        }
    }

    setButtonLoading(button, loading) {
        if (!button) return;

        const text = button.querySelector('.button-text');
        const spinner = button.querySelector('.loading-spinner');
        
        button.disabled = loading;
        
        if (text) {
            text.style.opacity = loading ? '0.7' : '1';
        }
        
        if (spinner) {
            spinner.style.display = loading ? 'inline-block' : 'none';
        }
    }

    showProgress(show) {
        const progressBar = document.getElementById('importProgress');
        if (!progressBar) return;

        if (show) {
            progressBar.classList.add('show');
            // Animate progress bar
            setTimeout(() => {
                const fill = progressBar.querySelector('.progress-fill');
                if (fill) fill.style.width = '100%';
            }, 100);
        } else {
            progressBar.classList.remove('show');
            const fill = progressBar.querySelector('.progress-fill');
            if (fill) fill.style.width = '0%';
        }
    }

    showStatus(type, message) {
        const statusMessage = document.getElementById('statusMessage');
        if (!statusMessage) {
            console.log(`[${type.toUpperCase()}] ${message}`);
            return;
        }

        statusMessage.className = `status-message show ${type}`;
        statusMessage.textContent = message;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            statusMessage.classList.remove('show');
        }, 5000);
    }

    formatValue(value) {
        if (typeof value === 'object') {
            return JSON.stringify(value);
        }
        return String(value);
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('exportButton') || document.getElementById('importDropzone')) {
            window.settingsExportManager = new SettingsExportManager();
        }
    });
} else {
    if (document.getElementById('exportButton') || document.getElementById('importDropzone')) {
        window.settingsExportManager = new SettingsExportManager();
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SettingsExportManager;
}

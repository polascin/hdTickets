/**
 * Profile Picture Manager
 * Handles profile picture upload, cropping, and management functionality
 */
class ProfilePictureManager {
    constructor(options = {}) {
        this.options = {
            uploadUrl: '/profile/picture/upload',
            cropUrl: '/profile/picture/crop',
            deleteUrl: '/profile/picture/delete',
            infoUrl: '/profile/picture/info',
            limitsUrl: '/profile/picture/limits',
            ...options
        };

        this.currentFile = null;
        this.cropper = null;
        this.uploadLimits = {
            max_file_size: 5242880, // 5MB default
            allowed_formats: ['jpg', 'jpeg', 'png', 'webp']
        };

        this.init();
    }

    async init() {
        try {
            // Load upload limits from server
            await this.loadUploadLimits();
            
            // Initialize event listeners
            this.bindEvents();
            
            // Load current profile picture info
            await this.loadProfileInfo();
        } catch (error) {
            console.error('Failed to initialize ProfilePictureManager:', error);
        }
    }

    async loadUploadLimits() {
        try {
            const response = await fetch(this.options.limitsUrl);
            if (response.ok) {
                const data = await response.json();
                this.uploadLimits = data.data;
            }
        } catch (error) {
            console.error('Failed to load upload limits:', error);
        }
    }

    async loadProfileInfo() {
        try {
            const response = await fetch(this.options.infoUrl);
            if (response.ok) {
                const data = await response.json();
                this.updateProfileDisplay(data.data);
            }
        } catch (error) {
            console.error('Failed to load profile info:', error);
        }
    }

    bindEvents() {
        // File input change
        const fileInput = document.getElementById('profile-picture-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        // Upload buttons
        const uploadBtn = document.getElementById('profile-picture-upload-btn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => this.triggerFileSelect());
        }

        // Delete button
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteProfilePicture());
        }

        // Crop modal events
        const cropModal = document.getElementById('crop-modal');
        if (cropModal) {
            const applyCropBtn = document.getElementById('apply-crop-btn');
            const cancelCropBtn = document.getElementById('cancel-crop-btn');
            
            if (applyCropBtn) {
                applyCropBtn.addEventListener('click', () => this.applyCrop());
            }
            
            if (cancelCropBtn) {
                cancelCropBtn.addEventListener('click', () => this.cancelCrop());
            }
        }

        // Drag and drop functionality
        this.initializeDragDrop();
    }

    initializeDragDrop() {
        const dropZone = document.getElementById('profile-picture-drop-zone');
        if (!dropZone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('drag-active');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('drag-active');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        });
    }

    triggerFileSelect() {
        const fileInput = document.getElementById('profile-picture-input');
        if (fileInput) {
            fileInput.click();
        }
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.processFile(file);
        }
    }

    processFile(file) {
        // Validate file
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showError(validation.message);
            return;
        }

        this.currentFile = file;
        
        // Show crop modal with preview
        this.showCropModal(file);
    }

    validateFile(file) {
        // Check file size
        if (file.size > this.uploadLimits.max_file_size) {
            return {
                valid: false,
                message: `File size must be less than ${this.formatFileSize(this.uploadLimits.max_file_size)}`
            };
        }

        // Check file type
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!this.uploadLimits.allowed_formats.includes(fileExtension)) {
            return {
                valid: false,
                message: `Only ${this.uploadLimits.allowed_formats.join(', ').toUpperCase()} files are allowed`
            };
        }

        return { valid: true };
    }

    showCropModal(file) {
        const modal = document.getElementById('crop-modal');
        const preview = document.getElementById('crop-preview');
        
        if (!modal || !preview) {
            // No crop modal available, upload directly
            this.uploadFile(file);
            return;
        }

        // Create image URL
        const imageUrl = URL.createObjectURL(file);
        
        // Set preview image
        preview.src = imageUrl;
        preview.onload = () => {
            // Initialize cropper
            if (this.cropper) {
                this.cropper.destroy();
            }
            
            this.cropper = new Cropper(preview, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        };

        // Show modal
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    }

    async applyCrop() {
        if (!this.cropper) {
            await this.uploadFile(this.currentFile);
            this.closeCropModal();
            return;
        }

        const cropData = this.cropper.getData();
        
        // Round crop data to avoid floating point issues
        const roundedCropData = {
            x: Math.round(cropData.x),
            y: Math.round(cropData.y),
            width: Math.round(cropData.width),
            height: Math.round(cropData.height)
        };

        await this.uploadFile(this.currentFile, roundedCropData);
        this.closeCropModal();
    }

    cancelCrop() {
        this.closeCropModal();
    }

    closeCropModal() {
        const modal = document.getElementById('crop-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }

        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }

        // Clean up object URLs
        const preview = document.getElementById('crop-preview');
        if (preview && preview.src) {
            URL.revokeObjectURL(preview.src);
            preview.src = '';
        }

        // Reset file input
        const fileInput = document.getElementById('profile-picture-input');
        if (fileInput) {
            fileInput.value = '';
        }
    }

async uploadFile(file, cropData = null) {
        const formData = new FormData();
        formData.append('profile_picture', file);
        
        if (cropData) {
            formData.append('crop_data', JSON.stringify(cropData));
        }

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        try {
            this.showLoading(true);
            
            const response = await fetch(this.options.uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            let data;
            try {
                data = await response.json();
            } catch (parseError) {
                throw new Error('Invalid server response. Please try again.');
            }

            if (!response.ok) {
                // Handle HTTP errors
                if (response.status === 422) {
                    // Validation errors
                    const errors = data.errors || data.message || 'Validation failed';
                    if (typeof errors === 'object') {
                        const errorMessages = Object.values(errors).flat();
                        this.showError(errorMessages.join(', '));
                    } else {
                        this.showError(errors);
                    }
                } else if (response.status === 413) {
                    this.showError('File is too large. Please choose a smaller image.');
                } else if (response.status === 429) {
                    this.showError('Too many requests. Please wait a moment and try again.');
                } else if (response.status >= 500) {
                    this.showError('Server error. Please try again later.');
                } else {
                    this.showError(data.message || `Request failed (${response.status})`);
                }
                return;
            }

            if (data.success) {
                this.showSuccess(data.message || 'Profile picture updated successfully!');
                this.updateProfileDisplay(data.data.user);
                
                // Trigger profile updated event
                this.dispatchEvent('profilePictureUpdated', data.data);
                
                // Clear file input
                const fileInput = document.getElementById('profile-picture-input');
                if (fileInput) {
                    fileInput.value = '';
                }
            } else {
                this.showError(data.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            
            // Network or other errors
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                this.showError('Network error. Please check your connection and try again.');
            } else if (error.name === 'AbortError') {
                this.showError('Upload was cancelled.');
            } else {
                this.showError(error.message || 'An unexpected error occurred during upload');
            }
        } finally {
            this.showLoading(false);
        }
    }

    async deleteProfilePicture() {
        if (!confirm('Are you sure you want to delete your profile picture?')) {
            return;
        }

        try {
            this.showLoading(true);
            
            const response = await fetch(this.options.deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                this.updateProfileDisplay(data.data.user);
                
                // Trigger profile updated event
                this.dispatchEvent('profilePictureDeleted', data.data);
            } else {
                this.showError(data.message || 'Delete failed');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showError('An error occurred during deletion');
        } finally {
            this.showLoading(false);
        }
    }

    updateProfileDisplay(profileData) {
        // Update profile picture in various locations
        const selectors = [
            '.profile-picture',
            '.user-avatar',
            '#profile-picture-preview',
            '[data-profile-picture]'
        ];

        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                if (profileData.picture_url) {
                    if (element.tagName === 'IMG') {
                        element.src = profileData.picture_url + '?t=' + Date.now(); // Cache bust
                        element.alt = profileData.full_name;
                        element.style.display = 'block';
                    } else {
                        element.style.backgroundImage = `url(${profileData.picture_url}?t=${Date.now()})`;
                    }
                    
                    // Hide initials if showing
                    const initialsElement = element.querySelector('.user-initials');
                    if (initialsElement) {
                        initialsElement.style.display = 'none';
                    }
                } else {
                    // Show initials instead
                    if (element.tagName === 'IMG') {
                        element.style.display = 'none';
                    } else {
                        element.style.backgroundImage = 'none';
                    }
                    
                    // Show initials
                    let initialsElement = element.querySelector('.user-initials');
                    if (!initialsElement) {
                        initialsElement = document.createElement('span');
                        initialsElement.className = 'user-initials';
                        element.appendChild(initialsElement);
                    }
                    initialsElement.textContent = profileData.initials;
                    initialsElement.style.display = 'flex';
                }
            });
        });

        // Update delete button visibility
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        if (deleteBtn) {
            deleteBtn.style.display = profileData.has_picture ? 'block' : 'none';
        }
    }

    showLoading(show) {
        const loadingElements = document.querySelectorAll('.profile-picture-loading');
        loadingElements.forEach(element => {
            element.style.display = show ? 'block' : 'none';
        });

        // Disable upload buttons during loading
        const uploadBtn = document.getElementById('profile-picture-upload-btn');
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        
        if (uploadBtn) uploadBtn.disabled = show;
        if (deleteBtn) deleteBtn.disabled = show;
    }

    showError(message) {
        this.showMessage(message, 'error');
    }

    showSuccess(message) {
        this.showMessage(message, 'success');
    }

    showMessage(message, type = 'info') {
        // Try to use existing notification system
        if (window.showNotification) {
            window.showNotification(message, type);
            return;
        }

        // Fallback to simple alert or create toast
        if (type === 'error') {
            alert('Error: ' + message);
        } else {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 12px 24px;
                border-radius: 4px;
                z-index: 10000;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;

            document.body.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
            });

            // Remove after delay
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    dispatchEvent(eventName, data) {
        const event = new CustomEvent(eventName, {
            detail: data,
            bubbles: true
        });
        document.dispatchEvent(event);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if profile picture elements exist
    if (document.querySelector('#profile-picture-upload-btn, .profile-picture-manager')) {
        window.profilePictureManager = new ProfilePictureManager();
    }
});

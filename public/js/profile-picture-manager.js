/**
 * Profile Picture Manager
 * Handles profile picture upload, cropping, and management
 */

class ProfilePictureManager {
    constructor() {
        this.cropper = null;
        this.currentFile = null;
        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        
        this.init();
    }

    init() {
        this.bindEvents();
        console.log('Profile Picture Manager initialized');
    }

    bindEvents() {
        // File input change
        const fileInput = document.getElementById('profile-picture-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        // Upload button click
        const uploadBtn = document.getElementById('profile-picture-upload-btn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => this.triggerFileSelect());
        }

        // Delete button click
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteProfilePicture());
        }

        // Drop zone events
        const dropZone = document.getElementById('profile-picture-drop-zone');
        if (dropZone) {
            dropZone.addEventListener('click', () => this.triggerFileSelect());
            dropZone.addEventListener('dragover', (e) => this.handleDragOver(e));
            dropZone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
            dropZone.addEventListener('drop', (e) => this.handleFileDrop(e));
        }

        // Crop modal buttons
        const cancelCropBtn = document.getElementById('cancel-crop-btn');
        const applyCropBtn = document.getElementById('apply-crop-btn');

        if (cancelCropBtn) {
            cancelCropBtn.addEventListener('click', () => this.cancelCrop());
        }

        if (applyCropBtn) {
            applyCropBtn.addEventListener('click', () => this.applyCrop());
        }
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

    handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.add('dragover');
    }

    handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('dragover');
    }

    handleFileDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('dragover');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    }

    processFile(file) {
        // Validate file
        if (!this.validateFile(file)) {
            return;
        }

        this.currentFile = file;
        this.showCropModal(file);
    }

    validateFile(file) {
        // Check file type
        if (!this.allowedTypes.includes(file.type)) {
            this.showError('Please select a valid image file (JPEG, PNG, WebP, or GIF)');
            return false;
        }

        // Check file size
        if (file.size > this.maxFileSize) {
            this.showError('File size must be less than 5MB');
            return false;
        }

        return true;
    }

    showCropModal(file) {
        const modal = document.getElementById('crop-modal');
        const cropPreview = document.getElementById('crop-preview');

        if (!modal || !cropPreview) {
            console.error('Crop modal elements not found');
            return;
        }

        // Create URL for the image
        const imageUrl = URL.createObjectURL(file);
        cropPreview.src = imageUrl;

        // Show modal
        modal.style.display = 'flex';

        // Initialize cropper when image loads
        cropPreview.onload = () => {
            this.initializeCropper(cropPreview);
        };
    }

    initializeCropper(imageElement) {
        // Destroy existing cropper if it exists
        if (this.cropper) {
            this.cropper.destroy();
        }

        // Initialize new cropper
        this.cropper = new Cropper(imageElement, {
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
    }

    cancelCrop() {
        this.closeCropModal();
        this.resetFileInput();
    }

    async applyCrop() {
        if (!this.cropper || !this.currentFile) {
            return;
        }

        try {
            this.showLoading(true);

            // Get cropped canvas
            const canvas = this.cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                minWidth: 100,
                minHeight: 100,
                maxWidth: 800,
                maxHeight: 800,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Convert to blob
            canvas.toBlob(async (blob) => {
                if (blob) {
                    await this.uploadProfilePicture(blob);
                } else {
                    this.showError('Failed to process image');
                    this.showLoading(false);
                }
            }, 'image/jpeg', 0.9);

        } catch (error) {
            console.error('Crop error:', error);
            this.showError('Failed to crop image');
            this.showLoading(false);
        }
    }

    async uploadProfilePicture(blob) {
        try {
            const formData = new FormData();
            formData.append('profile_picture', blob, 'profile-picture.jpg');

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.content);
            }

            const response = await fetch('/profile/picture/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.handleUploadSuccess(result);
            } else {
                this.showError(result.message || 'Upload failed');
            }

        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Network error occurred');
        } finally {
            this.showLoading(false);
            this.closeCropModal();
        }
    }

    async deleteProfilePicture() {
        if (!confirm('Are you sure you want to remove your profile picture?')) {
            return;
        }

        try {
            this.showLoading(true);

            const response = await fetch('/profile/picture/delete', {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.handleDeleteSuccess();
            } else {
                this.showError(result.message || 'Delete failed');
            }

        } catch (error) {
            console.error('Delete error:', error);
            this.showError('Network error occurred');
        } finally {
            this.showLoading(false);
        }
    }

    handleUploadSuccess(result) {
        // Update preview image
        const previewImage = document.querySelector('#profile-picture-preview img');
        const initialsSpan = document.querySelector('#profile-picture-preview .user-initials');
        
        if (result.data && result.data.user && result.data.user.picture_url) {
            const pictureUrl = result.data.user.picture_url;
            if (previewImage) {
                previewImage.src = pictureUrl + '?v=' + Date.now();
            } else {
                // Create new image element if it doesn't exist
                const profilePicture = document.getElementById('profile-picture-preview');
                if (profilePicture) {
                    profilePicture.innerHTML = `<img src="${pictureUrl}?v=${Date.now()}" alt="Profile Picture" />`;
                }
            }

            // Hide initials if they exist
            if (initialsSpan) {
                initialsSpan.style.display = 'none';
            }

            // Show delete button if it doesn't exist
            this.showDeleteButton();
        }

        this.showSuccess('Profile picture updated successfully!');
        this.resetFileInput();
    }

    handleDeleteSuccess() {
        // Update preview to show initials
        const profilePicture = document.getElementById('profile-picture-preview');
        if (profilePicture) {
            const initials = result.data && result.data.user && result.data.user.initials 
                ? result.data.user.initials 
                : profilePicture.dataset.initials || 'U';
            profilePicture.innerHTML = `<span class="user-initials">${initials}</span>`;
        }

        // Hide delete button
        this.hideDeleteButton();

        this.showSuccess('Profile picture removed successfully!');
    }

    showDeleteButton() {
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        if (deleteBtn) {
            deleteBtn.style.display = 'inline-flex';
        }
    }

    hideDeleteButton() {
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        if (deleteBtn) {
            deleteBtn.style.display = 'none';
        }
    }

    closeCropModal() {
        const modal = document.getElementById('crop-modal');
        if (modal) {
            modal.style.display = 'none';
        }

        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }

        // Clean up object URL
        const cropPreview = document.getElementById('crop-preview');
        if (cropPreview && cropPreview.src) {
            URL.revokeObjectURL(cropPreview.src);
        }
    }

    resetFileInput() {
        const fileInput = document.getElementById('profile-picture-input');
        if (fileInput) {
            fileInput.value = '';
        }
        this.currentFile = null;
    }

    showLoading(show) {
        const loadingElement = document.querySelector('.profile-picture-loading');
        if (loadingElement) {
            if (show) {
                loadingElement.classList.add('active');
            } else {
                loadingElement.classList.remove('active');
            }
        }

        // Disable/enable buttons
        const buttons = document.querySelectorAll('.profile-picture-buttons button');
        buttons.forEach(button => {
            button.disabled = show;
        });
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

        // Fallback to simple alert
        const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
        
        // Create temporary notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            background: ${type === 'error' ? '#fee' : type === 'success' ? '#efe' : '#eef'};
            border: 1px solid ${type === 'error' ? '#fcc' : type === 'success' ? '#cfc' : '#ccf'};
            color: ${type === 'error' ? '#c33' : type === 'success' ? '#3c3' : '#33c'};
            z-index: 10001;
            font-size: 0.875rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 300px;
        `;
        notification.textContent = `${icon} ${message}`;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);

        // Click to dismiss
        notification.addEventListener('click', () => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.profilePictureManager = new ProfilePictureManager();
});

// Export for potential external use
window.ProfilePictureManager = ProfilePictureManager;

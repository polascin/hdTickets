export default function confirmDialog() {
    return {
        show: false,
        title: 'Confirm Action',
        message: 'Are you sure?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        onConfirm: null,
        
        open(options = {}) {
            this.title = options.title || this.title;
            this.message = options.message || this.message;
            this.confirmText = options.confirmText || this.confirmText;
            this.cancelText = options.cancelText || this.cancelText;
            this.onConfirm = options.onConfirm || null;
            this.show = true;
        },
        
        confirm() {
            if (this.onConfirm && typeof this.onConfirm === 'function') {
                this.onConfirm();
            }
            this.close();
        },
        
        close() {
            this.show = false;
        }
    };
}

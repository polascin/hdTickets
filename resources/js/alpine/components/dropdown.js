export default function dropdown() {
    return {
        open: false,
        
        init() {
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.open = false;
                }
            });
            
            // Close dropdown on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open) {
                    this.open = false;
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            this.$el.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        },
        
        toggle() {
            this.open = !this.open;
        },
        
        close() {
            this.open = false;
        },
        
        closeOnItemClick() {
            // Close dropdown after a small delay to allow navigation
            setTimeout(() => {
                this.open = false;
            }, 100);
        }
    };
}

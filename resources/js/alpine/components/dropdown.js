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
        },
        
        toggle() {
            this.open = !this.open;
        },
        
        close() {
            this.open = false;
        }
    };
}

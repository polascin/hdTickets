export default function accordion() {
    return {
        activeItems: new Set(),
        allowMultiple: false,
        
        init() {
            this.allowMultiple = this.$el.hasAttribute('data-multiple');
        },
        
        toggle(itemId) {
            if (this.activeItems.has(itemId)) {
                this.activeItems.delete(itemId);
            } else {
                if (!this.allowMultiple) {
                    this.activeItems.clear();
                }
                this.activeItems.add(itemId);
            }
        },
        
        isActive(itemId) {
            return this.activeItems.has(itemId);
        },
        
        closeAll() {
            this.activeItems.clear();
        }
    };
}

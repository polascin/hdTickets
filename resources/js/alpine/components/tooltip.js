export default function tooltip() {
    return {
        show: false,
        content: '',
        position: 'top',
        
        init() {
            this.content = this.$el.getAttribute('title') || this.$el.dataset.tooltip || '';
            this.$el.removeAttribute('title');
        },
        
        showTooltip() {
            this.show = true;
        },
        
        hideTooltip() {
            this.show = false;
        }
    };
}

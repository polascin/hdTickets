/**
 * Tabs Alpine.js Component
 * Handles tab navigation and content switching
 */
export default function tabs() {
    return {
        activeTab: '',
        tabs: [],
        
        init() {
            // Get all tab elements
            const tabElements = this.$el.querySelectorAll('[x-tab]');
            this.tabs = Array.from(tabElements).map(el => ({
                id: el.getAttribute('x-tab'),
                label: el.textContent.trim(),
                disabled: el.hasAttribute('disabled')
            }));
            
            // Set initial active tab
            const urlHash = window.location.hash.slice(1);
            const initialTab = urlHash && this.tabs.find(tab => tab.id === urlHash) 
                ? urlHash 
                : this.tabs.find(tab => !tab.disabled)?.id;
                
            if (initialTab) {
                this.activeTab = initialTab;
            }
            
            // Listen for hash changes
            window.addEventListener('hashchange', () => {
                const hash = window.location.hash.slice(1);
                if (hash && this.tabs.find(tab => tab.id === hash)) {
                    this.activeTab = hash;
                }
            });
        },
        
        setActiveTab(tabId) {
            if (this.isTabDisabled(tabId)) return;
            
            this.activeTab = tabId;
            window.location.hash = tabId;
            
            // Emit tab change event
            this.$dispatch('tab-changed', { activeTab: tabId });
        },
        
        isTabActive(tabId) {
            return this.activeTab === tabId;
        },
        
        isTabDisabled(tabId) {
            const tab = this.tabs.find(t => t.id === tabId);
            return tab?.disabled || false;
        },
        
        getTabIndex(tabId) {
            return this.tabs.findIndex(t => t.id === tabId);
        },
        
        nextTab() {
            const currentIndex = this.getTabIndex(this.activeTab);
            const nextIndex = (currentIndex + 1) % this.tabs.length;
            const nextTab = this.tabs[nextIndex];
            
            if (!nextTab.disabled) {
                this.setActiveTab(nextTab.id);
            }
        },
        
        prevTab() {
            const currentIndex = this.getTabIndex(this.activeTab);
            const prevIndex = currentIndex === 0 ? this.tabs.length - 1 : currentIndex - 1;
            const prevTab = this.tabs[prevIndex];
            
            if (!prevTab.disabled) {
                this.setActiveTab(prevTab.id);
            }
        }
    };
}

/**
 * Ticket Comparison Component
 * 
 * Enables side-by-side comparison of multiple sports tickets with
 * advanced filtering, sorting, and export capabilities.
 */
class TicketComparison {
    constructor(options = {}) {
        this.options = {
            maxCompare: 6,
            storageKey: 'ticket_comparison',
            enableExport: true,
            enableSharing: true,
            enableNotifications: true,
            autoSave: true,
            ...options
        };
        
        this.compareList = new Map();
        this.compareModal = null;
        this.isVisible = false;
        
        this.init();
    }
    
    init() {
        this.loadComparison();
        this.createUI();
        this.setupEventListeners();
        this.updateCompareIndicators();
        
        console.log('TicketComparison initialized');
    }
    
    createUI() {
        this.createCompareButton();
        this.createCompareModal();
        this.createCompareIndicators();
    }
    
    createCompareButton() {
        // Create floating compare button
        const compareButton = document.createElement('button');
        compareButton.id = 'compare-tickets-btn';
        compareButton.className = 'fixed bottom-4 right-4 z-40 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg transition-all duration-300 transform translate-y-16 opacity-0';
        compareButton.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>Compare (<span id="compare-count">0</span>)</span>
            </div>
        `;
        
        compareButton.addEventListener('click', () => {
            this.showComparison();
        });
        
        document.body.appendChild(compareButton);
    }
    
    createCompareModal() {
        const modal = document.createElement('div');
        modal.id = 'compare-modal';
        modal.className = 'fixed inset-0 z-50 hidden overflow-y-auto';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 modal-backdrop"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block w-full max-w-7xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <h3 class="text-lg font-semibold text-gray-900">Compare Tickets</h3>
                            <span id="modal-compare-count" class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">0 selected</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <!-- View Toggle -->
                            <div class="flex bg-gray-100 rounded-lg p-1">
                                <button id="table-view-btn" class="px-3 py-1 text-sm font-medium rounded-md bg-white text-gray-900 shadow-sm">Table</button>
                                <button id="card-view-btn" class="px-3 py-1 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900">Cards</button>
                            </div>
                            
                            <!-- Actions -->
                            <button id="export-comparison" class="px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800">Export</button>
                            <button id="share-comparison" class="px-3 py-2 text-sm font-medium text-green-600 hover:text-green-800">Share</button>
                            <button id="clear-comparison" class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800">Clear All</button>
                            <button id="close-comparison" class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Comparison Filters -->
                    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="flex flex-wrap items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Sort by:</label>
                                <select id="compare-sort" class="text-sm border border-gray-300 rounded-md px-2 py-1">
                                    <option value="price_asc">Price (Low to High)</option>
                                    <option value="price_desc">Price (High to Low)</option>
                                    <option value="rating_desc">Rating (High to Low)</option>
                                    <option value="date_desc">Event Date (Newest)</option>
                                    <option value="date_asc">Event Date (Oldest)</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Highlight:</label>
                                <select id="compare-highlight" class="text-sm border border-gray-300 rounded-md px-2 py-1">
                                    <option value="">None</option>
                                    <option value="best_price">Best Price</option>
                                    <option value="best_value">Best Value</option>
                                    <option value="highest_rating">Highest Rating</option>
                                    <option value="closest_date">Closest Date</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="show-differences" class="rounded border-gray-300">
                                <label for="show-differences" class="text-sm font-medium text-gray-700">Show only differences</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Content -->
                    <div class="px-6 py-4">
                        <div id="comparison-content">
                            <div class="text-center py-12 text-gray-500">
                                <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <p class="mt-2 text-sm">Select tickets to compare by clicking the compare button on ticket cards</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.compareModal = modal;
        
        // Setup modal event listeners
        this.setupModalEventListeners();
    }
    
    createCompareIndicators() {
        // Add compare checkboxes to ticket cards
        document.querySelectorAll('.ticket-card').forEach(card => {
            const ticketId = card.dataset.ticketId;
            if (!ticketId) return;
            
            const indicator = document.createElement('div');
            indicator.className = 'compare-indicator absolute top-2 right-2';
            indicator.innerHTML = `
                <button class="compare-checkbox w-8 h-8 rounded-full border-2 border-white bg-black bg-opacity-20 hover:bg-opacity-40 text-white transition-all duration-200 flex items-center justify-center"
                        data-ticket-id="${ticketId}"
                        title="Add to comparison">
                    <svg class="w-4 h-4 hidden compare-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg class="w-4 h-4 compare-plus" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
            `;
            
            card.style.position = 'relative';
            card.appendChild(indicator);
            
            // Event listener for compare button
            const button = indicator.querySelector('.compare-checkbox');
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleCompare(ticketId);
            });
        });
    }
    
    setupEventListeners() {
        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Shift + C to toggle comparison
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
                e.preventDefault();
                if (this.compareList.size > 0) {
                    this.showComparison();
                }
            }
            
            // Escape to close comparison
            if (e.key === 'Escape' && this.isVisible) {
                this.hideComparison();
            }
        });
    }
    
    setupModalEventListeners() {
        if (!this.compareModal) return;
        
        // Close buttons
        this.compareModal.querySelector('#close-comparison').addEventListener('click', () => {
            this.hideComparison();
        });
        
        this.compareModal.querySelector('.modal-backdrop').addEventListener('click', () => {
            this.hideComparison();
        });
        
        // Clear all
        this.compareModal.querySelector('#clear-comparison').addEventListener('click', () => {
            this.clearAll();
        });
        
        // Export
        this.compareModal.querySelector('#export-comparison').addEventListener('click', () => {
            this.exportComparison();
        });
        
        // Share
        this.compareModal.querySelector('#share-comparison').addEventListener('click', () => {
            this.shareComparison();
        });
        
        // View toggle
        this.compareModal.querySelector('#table-view-btn').addEventListener('click', () => {
            this.setViewMode('table');
        });
        
        this.compareModal.querySelector('#card-view-btn').addEventListener('click', () => {
            this.setViewMode('card');
        });
        
        // Comparison controls
        this.compareModal.querySelector('#compare-sort').addEventListener('change', (e) => {
            this.sortComparison(e.target.value);
        });
        
        this.compareModal.querySelector('#compare-highlight').addEventListener('change', (e) => {
            this.highlightBest(e.target.value);
        });
        
        this.compareModal.querySelector('#show-differences').addEventListener('change', (e) => {
            this.toggleDifferencesOnly(e.target.checked);
        });
    }
    
    // Public API Methods
    
    toggleCompare(ticketId) {
        if (this.compareList.has(ticketId)) {
            this.removeFromCompare(ticketId);
        } else {
            this.addToCompare(ticketId);
        }
    }
    
    addToCompare(ticketId) {
        if (this.compareList.size >= this.options.maxCompare) {
            this.showNotification(`Maximum ${this.options.maxCompare} tickets can be compared`, 'warning');
            return false;
        }
        
        // Get ticket data
        const ticketData = this.getTicketData(ticketId);
        if (!ticketData) {
            this.showNotification('Unable to load ticket data', 'error');
            return false;
        }
        
        this.compareList.set(ticketId, ticketData);
        this.updateUI();
        this.saveComparison();
        
        this.showNotification('Ticket added to comparison', 'success');
        return true;
    }
    
    removeFromCompare(ticketId) {
        if (this.compareList.has(ticketId)) {
            this.compareList.delete(ticketId);
            this.updateUI();
            this.saveComparison();
            
            this.showNotification('Ticket removed from comparison', 'info');
            return true;
        }
        return false;
    }
    
    clearAll() {
        this.compareList.clear();
        this.updateUI();
        this.saveComparison();
        this.showNotification('Comparison cleared', 'info');
    }
    
    showComparison() {
        if (this.compareList.size === 0) {
            this.showNotification('Select tickets to compare first', 'warning');
            return;
        }
        
        this.renderComparison();
        this.compareModal.classList.remove('hidden');
        this.isVisible = true;
        
        // Prevent body scroll
        document.body.classList.add('overflow-hidden');
    }
    
    hideComparison() {
        this.compareModal.classList.add('hidden');
        this.isVisible = false;
        
        // Restore body scroll
        document.body.classList.remove('overflow-hidden');
    }
    
    // Data Management
    
    getTicketData(ticketId) {
        const ticketElement = document.querySelector(`[data-ticket-id="${ticketId}"]`);
        if (!ticketElement) return null;
        
        const data = {
            id: ticketId,
            title: ticketElement.querySelector('.ticket-title')?.textContent || 'Unknown Event',
            price: this.extractPrice(ticketElement),
            venue: ticketElement.querySelector('.ticket-venue')?.textContent || 'Unknown Venue',
            date: ticketElement.querySelector('.ticket-date')?.textContent || 'Unknown Date',
            category: ticketElement.querySelector('.ticket-category')?.textContent || 'General',
            availability: ticketElement.querySelector('.ticket-availability')?.textContent || 'Unknown',
            platform: ticketElement.querySelector('.ticket-platform')?.textContent || 'Unknown',
            rating: this.extractRating(ticketElement),
            image: ticketElement.querySelector('.ticket-image img')?.src || '/images/default-ticket.jpg',
            url: ticketElement.querySelector('a')?.href || '#',
            features: this.extractFeatures(ticketElement)
        };
        
        return data;
    }
    
    extractPrice(element) {
        const priceText = element.querySelector('.ticket-price')?.textContent || '$0';
        const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));
        return isNaN(price) ? 0 : price;
    }
    
    extractRating(element) {
        const ratingElement = element.querySelector('.ticket-rating');
        if (ratingElement) {
            const ratingText = ratingElement.textContent;
            const rating = parseFloat(ratingText.match(/[0-9.]+/)?.[0] || '0');
            return isNaN(rating) ? 0 : rating;
        }
        return 0;
    }
    
    extractFeatures(element) {
        const features = [];
        element.querySelectorAll('.ticket-feature').forEach(feature => {
            features.push(feature.textContent.trim());
        });
        return features;
    }
    
    // UI Updates
    
    updateUI() {
        this.updateCompareButton();
        this.updateCompareIndicators();
        
        if (this.isVisible) {
            this.renderComparison();
        }
    }
    
    updateCompareButton() {
        const button = document.getElementById('compare-tickets-btn');
        const count = document.getElementById('compare-count');
        
        if (button && count) {
            count.textContent = this.compareList.size;
            
            if (this.compareList.size > 0) {
                button.classList.remove('translate-y-16', 'opacity-0');
                button.classList.add('translate-y-0', 'opacity-100');
            } else {
                button.classList.add('translate-y-16', 'opacity-0');
                button.classList.remove('translate-y-0', 'opacity-100');
            }
        }
    }
    
    updateCompareIndicators() {
        document.querySelectorAll('.compare-checkbox').forEach(button => {
            const ticketId = button.dataset.ticketId;
            const isSelected = this.compareList.has(ticketId);
            
            const checkIcon = button.querySelector('.compare-check');
            const plusIcon = button.querySelector('.compare-plus');
            
            if (isSelected) {
                button.classList.add('bg-blue-600', 'border-blue-600');
                button.classList.remove('bg-black', 'bg-opacity-20');
                checkIcon.classList.remove('hidden');
                plusIcon.classList.add('hidden');
                button.title = 'Remove from comparison';
            } else {
                button.classList.remove('bg-blue-600', 'border-blue-600');
                button.classList.add('bg-black', 'bg-opacity-20');
                checkIcon.classList.add('hidden');
                plusIcon.classList.remove('hidden');
                button.title = 'Add to comparison';
            }
        });
    }
    
    // Comparison Rendering
    
    renderComparison() {
        const content = this.compareModal.querySelector('#comparison-content');
        const modalCount = this.compareModal.querySelector('#modal-compare-count');
        
        modalCount.textContent = `${this.compareList.size} selected`;
        
        if (this.compareList.size === 0) {
            content.innerHTML = `
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="mt-2 text-sm">Select tickets to compare by clicking the compare button on ticket cards</p>
                </div>
            `;
            return;
        }
        
        // Get current view mode
        const isTableView = this.compareModal.querySelector('#table-view-btn').classList.contains('bg-white');
        
        if (isTableView) {
            this.renderTableView(content);
        } else {
            this.renderCardView(content);
        }
    }
    
    renderTableView(container) {
        const tickets = Array.from(this.compareList.values());
        
        const table = document.createElement('div');
        table.className = 'overflow-x-auto';
        table.innerHTML = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${tickets.map(ticket => this.renderTableRow(ticket)).join('')}
                </tbody>
            </table>
        `;
        
        container.innerHTML = '';
        container.appendChild(table);
    }
    
    renderTableRow(ticket) {
        return `
            <tr class="hover:bg-gray-50 ticket-comparison-row" data-ticket-id="${ticket.id}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img class="w-10 h-10 rounded-md object-cover mr-3" src="${ticket.image}" alt="${ticket.title}">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">${ticket.title}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-semibold text-gray-900">$${ticket.price.toFixed(2)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.venue}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.date}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.category}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.availability}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.platform}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${ticket.rating > 0 ? `
                        <div class="flex items-center">
                            <span class="text-sm text-gray-900">${ticket.rating.toFixed(1)}</span>
                            <svg class="ml-1 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                    ` : `<span class="text-sm text-gray-400">No rating</span>`}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <a href="${ticket.url}" class="text-blue-600 hover:text-blue-800" target="_blank">View</a>
                        <button class="text-red-600 hover:text-red-800" onclick="ticketComparison.removeFromCompare('${ticket.id}')">Remove</button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    renderCardView(container) {
        const tickets = Array.from(this.compareList.values());
        
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
        grid.innerHTML = tickets.map(ticket => this.renderComparisonCard(ticket)).join('');
        
        container.innerHTML = '';
        container.appendChild(grid);
    }
    
    renderComparisonCard(ticket) {
        return `
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow ticket-comparison-card" data-ticket-id="${ticket.id}">
                <div class="relative">
                    <img class="w-full h-48 object-cover rounded-t-lg" src="${ticket.image}" alt="${ticket.title}">
                    <div class="absolute top-2 right-2">
                        <button class="p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors" 
                                onclick="ticketComparison.removeFromCompare('${ticket.id}')" 
                                title="Remove from comparison">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">${ticket.title}</h3>
                    
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Price:</span>
                            <span class="font-semibold text-gray-900">$${ticket.price.toFixed(2)}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Venue:</span>
                            <span>${ticket.venue}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Date:</span>
                            <span>${ticket.date}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Category:</span>
                            <span>${ticket.category}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Platform:</span>
                            <span>${ticket.platform}</span>
                        </div>
                        
                        ${ticket.rating > 0 ? `
                            <div class="flex justify-between">
                                <span>Rating:</span>
                                <div class="flex items-center">
                                    <span>${ticket.rating.toFixed(1)}</span>
                                    <svg class="ml-1 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${ticket.features.length > 0 ? `
                        <div class="mt-3">
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Features:</h4>
                            <div class="flex flex-wrap gap-1">
                                ${ticket.features.map(feature => `
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${feature}</span>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="mt-4 flex space-x-2">
                        <a href="${ticket.url}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded-md text-sm font-medium transition-colors" target="_blank">
                            View Ticket
                        </a>
                    </div>
                </div>
            </div>
        `;
    }
    
    // View and Sorting Methods
    
    setViewMode(mode) {
        const tableBtn = this.compareModal.querySelector('#table-view-btn');
        const cardBtn = this.compareModal.querySelector('#card-view-btn');
        
        if (mode === 'table') {
            tableBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            tableBtn.classList.remove('text-gray-600');
            cardBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            cardBtn.classList.add('text-gray-600');
        } else {
            cardBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            cardBtn.classList.remove('text-gray-600');
            tableBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            tableBtn.classList.add('text-gray-600');
        }
        
        this.renderComparison();
    }
    
    sortComparison(sortBy) {
        const tickets = Array.from(this.compareList.values());
        
        tickets.sort((a, b) => {
            switch (sortBy) {
                case 'price_asc':
                    return a.price - b.price;
                case 'price_desc':
                    return b.price - a.price;
                case 'rating_desc':
                    return b.rating - a.rating;
                case 'date_desc':
                case 'date_asc':
                    // Would need proper date parsing
                    return sortBy === 'date_desc' ? -1 : 1;
                default:
                    return 0;
            }
        });
        
        // Update the Map with sorted order
        this.compareList.clear();
        tickets.forEach(ticket => {
            this.compareList.set(ticket.id, ticket);
        });
        
        this.renderComparison();
    }
    
    highlightBest(criteria) {
        // Remove existing highlights
        document.querySelectorAll('.ticket-comparison-row, .ticket-comparison-card').forEach(row => {
            row.classList.remove('bg-green-50', 'border-green-200');
        });
        
        if (!criteria) return;
        
        const tickets = Array.from(this.compareList.values());
        let bestTicket = null;
        
        switch (criteria) {
            case 'best_price':
                bestTicket = tickets.reduce((best, current) => 
                    current.price < best.price ? current : best
                );
                break;
            case 'highest_rating':
                bestTicket = tickets.reduce((best, current) => 
                    current.rating > best.rating ? current : best
                );
                break;
            // Add more criteria as needed
        }
        
        if (bestTicket) {
            const elements = document.querySelectorAll(`[data-ticket-id="${bestTicket.id}"]`);
            elements.forEach(el => {
                if (el.classList.contains('ticket-comparison-row') || el.classList.contains('ticket-comparison-card')) {
                    el.classList.add('bg-green-50', 'border-green-200');
                }
            });
        }
    }
    
    toggleDifferencesOnly(showDifferencesOnly) {
        // This would need more complex logic to hide similar rows/fields
        // For now, just store the preference
        this.showDifferencesOnly = showDifferencesOnly;
    }
    
    // Export and Sharing
    
    exportComparison() {
        const tickets = Array.from(this.compareList.values());
        const csvContent = this.generateCSV(tickets);
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `ticket-comparison-${Date.now()}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        this.showNotification('Comparison exported successfully', 'success');
    }
    
    generateCSV(tickets) {
        const headers = ['Title', 'Price', 'Venue', 'Date', 'Category', 'Platform', 'Rating', 'Availability'];
        const rows = tickets.map(ticket => [
            ticket.title,
            ticket.price,
            ticket.venue,
            ticket.date,
            ticket.category,
            ticket.platform,
            ticket.rating,
            ticket.availability
        ]);
        
        return [headers, ...rows]
            .map(row => row.map(cell => `"${cell}"`).join(','))
            .join('\n');
    }
    
    async shareComparison() {
        if (!navigator.share) {
            // Fallback: copy to clipboard
            const tickets = Array.from(this.compareList.values());
            const shareText = `Comparing ${tickets.length} tickets:\n\n` + 
                tickets.map(ticket => `${ticket.title} - $${ticket.price} at ${ticket.venue}`).join('\n');
            
            try {
                await navigator.clipboard.writeText(shareText);
                this.showNotification('Comparison copied to clipboard', 'success');
        } catch {
                this.showNotification('Unable to copy to clipboard', 'error');
            }
            return;
        }
        
        try {
            await navigator.share({
                title: 'Ticket Comparison',
                text: `Comparing ${this.compareList.size} sports tickets`,
                url: window.location.href
            });
        } catch (err) {
            // User cancelled or error occurred
            if (err.name !== 'AbortError') {
                this.showNotification('Unable to share', 'error');
            }
        }
    }
    
    // Persistence
    
    saveComparison() {
        if (!this.options.autoSave) return;
        
        const data = {
            tickets: Array.from(this.compareList.entries()),
            timestamp: Date.now()
        };
        
        try {
            localStorage.setItem(this.options.storageKey, JSON.stringify(data));
        } catch (error) {
            console.warn('Failed to save comparison:', error);
        }
    }
    
    loadComparison() {
        try {
            const saved = localStorage.getItem(this.options.storageKey);
            if (saved) {
                const data = JSON.parse(saved);
                
                // Check if data is not too old (24 hours)
                if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
                    data.tickets.forEach(([id, ticket]) => {
                        this.compareList.set(id, ticket);
                    });
                } else {
                    // Clear old data
                    localStorage.removeItem(this.options.storageKey);
                }
            }
        } catch (error) {
            console.warn('Failed to load comparison:', error);
        }
    }
    
    // Utility Methods
    
    showNotification(message, type = 'info') {
        if (!this.options.enableNotifications) return;
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => document.body.removeChild(notification), 300);
        }, 3000);
    }
    
    // Public API
    
    getCompareList() {
        return Array.from(this.compareList.values());
    }
    
    getCompareCount() {
        return this.compareList.size;
    }
    
    isInComparison(ticketId) {
        return this.compareList.has(ticketId);
    }
    
    destroy() {
        this.compareList.clear();
        
        if (this.compareModal) {
            this.compareModal.remove();
        }
        
        const compareButton = document.getElementById('compare-tickets-btn');
        if (compareButton) {
            compareButton.remove();
        }
        
        // Remove compare indicators
        document.querySelectorAll('.compare-indicator').forEach(indicator => {
            indicator.remove();
        });
    }
}

// Global instance
window.TicketComparison = TicketComparison;

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.ticketComparison = new TicketComparison();
    });
} else {
    window.ticketComparison = new TicketComparison();
}

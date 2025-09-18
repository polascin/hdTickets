const C="modulepreload",E=function(l){return"/build/"+l},x={},b=function(e,t,i){let s=Promise.resolve();if(t&&t.length>0){let r=function(a){return Promise.all(a.map(h=>Promise.resolve(h).then(u=>({status:"fulfilled",value:u}),u=>({status:"rejected",reason:u}))))};document.getElementsByTagName("link");const n=document.querySelector("meta[property=csp-nonce]"),c=n?.nonce||n?.getAttribute("nonce");s=r(t.map(a=>{if(a=E(a),a in x)return;x[a]=!0;const h=a.endsWith(".css"),u=h?'[rel="stylesheet"]':"";if(document.querySelector(`link[href="${a}"]${u}`))return;const d=document.createElement("link");if(d.rel=h?"stylesheet":C,h||(d.as="script"),d.crossOrigin="",d.href=a,c&&d.setAttribute("nonce",c),document.head.appendChild(d),h)return new Promise((w,k)=>{d.addEventListener("load",w),d.addEventListener("error",()=>k(new Error(`Unable to preload CSS for ${a}`)))})}))}function o(r){const n=new Event("vite:preloadError",{cancelable:!0});if(n.payload=r,window.dispatchEvent(n),!n.defaultPrevented)throw r}return s.then(r=>{for(const n of r||[])n.status==="rejected"&&o(n.reason);return e().catch(o)})};b(()=>Promise.resolve().then(()=>S),void 0);b(()=>Promise.resolve().then(()=>L),void 0);b(()=>Promise.resolve().then(()=>T),void 0);class v{constructor(e={}){this.options={enablePriceMonitoring:!0,enableComparison:!0,enableFiltering:!0,enableAnalytics:!0,debugMode:!1,...e},this.components={},this.isInitialized=!1,this.init()}init(){console.log("🎫 HD Tickets App initializing..."),this.setupGlobalErrorHandling(),this.initializeComponents(),this.setupGlobalEventListeners(),this.setupAnalytics(),this.isInitialized=!0,console.log("✅ HD Tickets App initialized successfully"),this.dispatchEvent("hdtickets:initialized",{components:Object.keys(this.components),version:"1.0.0"})}setupGlobalErrorHandling(){window.addEventListener("error",e=>{this.options.debugMode&&console.error("HDTickets Error:",e.error),this.trackError(e.error)}),window.addEventListener("unhandledrejection",e=>{this.options.debugMode&&console.error("HDTickets Unhandled Rejection:",e.reason),this.trackError(e.reason)})}initializeComponents(){if(this.options.enableFiltering&&window.TicketFilters)try{this.components.filters=new window.TicketFilters({formSelector:"#filters-form",resultsSelector:"#tickets-grid",loadingSelector:"#loading-indicator",enableUrlSync:!0,cacheEnabled:!0}),console.log("✅ Ticket filters initialized")}catch(e){console.error("❌ Failed to initialize ticket filters:",e)}if(this.options.enablePriceMonitoring&&window.PriceMonitor&&this.hasWebSocketSupport())try{this.components.priceMonitor=new window.PriceMonitor({enableNotifications:this.hasNotificationSupport(),enableSound:!0,priceThreshold:.05}),console.log("✅ Price monitoring initialized")}catch(e){console.error("❌ Failed to initialize price monitoring:",e)}if(this.options.enableComparison&&window.TicketComparison)try{this.components.comparison=new window.TicketComparison({maxCompare:6,enableExport:!0,enableSharing:this.hasSharingSupport(),autoSave:!0}),console.log("✅ Ticket comparison initialized")}catch(e){console.error("❌ Failed to initialize ticket comparison:",e)}this.initializeBookmarkSystem(),this.initializeShareButtons(),this.initializeSearchSuggestions(),this.initializeLazyLoading(),this.initializeProgressiveEnhancement()}initializeBookmarkSystem(){document.querySelectorAll(".bookmark-toggle").forEach(t=>{t.addEventListener("click",async i=>{i.preventDefault(),i.stopPropagation();const s=t.dataset.ticketId;if(s){t.classList.contains("bookmarked");try{t.disabled=!0,t.classList.add("loading");const r=await(await fetch("/tickets/scraping/bookmark-toggle",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({ticket_id:s})})).json();if(r.success)this.updateBookmarkButton(t,r.is_bookmarked),this.showNotification(r.is_bookmarked?"Ticket bookmarked!":"Bookmark removed","success"),this.trackEvent("ticket_bookmark",{ticket_id:s,action:r.is_bookmarked?"add":"remove"});else throw new Error(r.message||"Bookmark failed")}catch(o){console.error("Bookmark error:",o),this.showNotification("Failed to update bookmark","error")}finally{t.disabled=!1,t.classList.remove("loading")}}})})}updateBookmarkButton(e,t){const i=e.querySelector("svg"),s=e.querySelector(".bookmark-text");t?(e.classList.add("bookmarked"),i.classList.remove("text-gray-400"),i.classList.add("text-yellow-500"),s&&(s.textContent="Bookmarked")):(e.classList.remove("bookmarked"),i.classList.remove("text-yellow-500"),i.classList.add("text-gray-400"),s&&(s.textContent="Bookmark"))}initializeShareButtons(){document.querySelectorAll(".share-button").forEach(t=>{t.addEventListener("click",async i=>{i.preventDefault();const s=t.dataset.ticketId,o=t.dataset.title||"Check out this ticket",r=t.dataset.url||window.location.href;if(this.hasSharingSupport())try{await navigator.share({title:o,text:"Found this great ticket on HD Tickets",url:r}),this.trackEvent("ticket_share",{ticket_id:s,method:"native"})}catch(n){n.name!=="AbortError"&&this.fallbackShare(r,o)}else this.fallbackShare(r,o)})})}fallbackShare(e,t){navigator.clipboard.writeText(e).then(()=>{this.showNotification("Link copied to clipboard!","success")}).catch(()=>{this.showShareModal(e,t)})}showShareModal(e,t){const i=document.createElement("div");i.className="fixed inset-0 z-50 overflow-y-auto",i.innerHTML=`
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Share Ticket</h3>
                    <div class="flex space-x-2 mb-4">
                        <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(t)}&url=${encodeURIComponent(e)}" 
                           target="_blank" 
                           class="flex-1 bg-blue-500 text-white px-3 py-2 rounded text-center text-sm">Twitter</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(e)}" 
                           target="_blank" 
                           class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-center text-sm">Facebook</a>
                    </div>
                    <div class="flex items-center space-x-2 mb-4">
                        <input type="text" value="${e}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm">
                        <button class="copy-url-btn px-3 py-2 bg-gray-500 text-white rounded text-sm">Copy</button>
                    </div>
                    <button class="close-modal w-full bg-gray-200 text-gray-800 px-4 py-2 rounded text-sm">Close</button>
                </div>
            </div>
        `,document.body.appendChild(i),i.querySelector(".copy-url-btn").addEventListener("click",()=>{i.querySelector("input").select(),document.execCommand("copy"),this.showNotification("Link copied!","success")}),i.querySelector(".close-modal").addEventListener("click",()=>{document.body.removeChild(i)}),i.addEventListener("click",s=>{s.target===i&&document.body.removeChild(i)})}initializeSearchSuggestions(){document.querySelectorAll('input[name="keywords"]').forEach(t=>{let i,s;const o=document.createElement("div");o.className="absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto hidden",t.parentElement.style.position="relative",t.parentElement.appendChild(o),s=o,t.addEventListener("input",r=>{clearTimeout(i);const n=r.target.value.trim();n.length>=2?i=setTimeout(()=>{this.loadSearchSuggestions(n,s,t)},300):s.classList.add("hidden")}),t.addEventListener("blur",()=>{setTimeout(()=>{s.classList.add("hidden")},150)})})}async loadSearchSuggestions(e,t,i){try{const o=await(await fetch(`/tickets/scraping/search-suggestions?term=${encodeURIComponent(e)}`)).json();o.success&&o.suggestions.length>0?(t.innerHTML=o.suggestions.map(r=>`
                    <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer suggestion-item flex items-center"
                         data-value="${this.escapeHtml(r.value)}">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="flex-1">${this.escapeHtml(r.value)}</span>
                        <span class="text-xs text-gray-500 capitalize">${this.escapeHtml(r.type)}</span>
                    </div>
                `).join(""),t.querySelectorAll(".suggestion-item").forEach(r=>{r.addEventListener("click",()=>{i.value=r.dataset.value,t.classList.add("hidden"),this.components.filters&&this.components.filters.submitFilters()})}),t.classList.remove("hidden")):t.classList.add("hidden")}catch(s){console.error("Search suggestions error:",s),t.classList.add("hidden")}}initializeLazyLoading(){if("IntersectionObserver"in window){const e=document.querySelectorAll("img[data-src]"),t=new IntersectionObserver((i,s)=>{i.forEach(o=>{if(o.isIntersecting){const r=o.target;r.src=r.dataset.src,r.removeAttribute("data-src"),r.classList.remove("lazy"),s.unobserve(r)}})},{rootMargin:"50px 0px"});e.forEach(i=>t.observe(i))}}initializeProgressiveEnhancement(){document.documentElement.classList.add("js-enabled"),document.querySelectorAll("form[data-enhance]").forEach(t=>{t.addEventListener("submit",i=>{const s=t.querySelector('button[type="submit"]');s&&(s.disabled=!0,s.classList.add("loading"),setTimeout(()=>{s.disabled=!1,s.classList.remove("loading")},2e3))})}),document.querySelectorAll("button[data-loading-text]").forEach(t=>{t.addEventListener("click",()=>{const i=t.textContent,s=t.dataset.loadingText;t.textContent=s,t.disabled=!0,setTimeout(()=>{t.textContent=i,t.disabled=!1},2e3)})})}setupGlobalEventListeners(){document.addEventListener("keydown",e=>{if((e.ctrlKey||e.metaKey)&&e.key==="/"&&(e.preventDefault(),this.showKeyboardShortcuts()),e.altKey&&e.key.toLowerCase()==="f"){e.preventDefault();const t=document.getElementById("keywords");t&&t.focus()}}),document.addEventListener("filtersApplied",e=>{this.trackEvent("filters_applied",e.detail)}),document.addEventListener("visibilitychange",()=>{document.hidden?this.handlePageHidden():this.handlePageVisible()})}showKeyboardShortcuts(){const e=document.createElement("div");e.className="fixed inset-0 z-50 overflow-y-auto",e.innerHTML=`
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                <div class="inline-block px-6 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Keyboard Shortcuts</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl/⌘ + K</kbd></span>
                            <span class="text-gray-600">Focus search</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl/⌘ + ⇧ + C</kbd></span>
                            <span class="text-gray-600">Open comparison</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Alt + F</kbd></span>
                            <span class="text-gray-600">Focus filters</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Escape</kbd></span>
                            <span class="text-gray-600">Close modals</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl/⌘ + /</kbd></span>
                            <span class="text-gray-600">Show this help</span>
                        </div>
                    </div>
                    <button class="close-shortcuts-modal mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Close</button>
                </div>
            </div>
        `,document.body.appendChild(e),e.querySelector(".close-shortcuts-modal").addEventListener("click",()=>{document.body.removeChild(e)}),e.addEventListener("click",t=>{t.target===e&&document.body.removeChild(e)})}handlePageHidden(){this.components.priceMonitor&&this.components.priceMonitor.pauseMonitoring()}handlePageVisible(){this.components.priceMonitor&&this.components.priceMonitor.resumeMonitoring()}setupAnalytics(){this.options.enableAnalytics&&(this.trackEvent("page_view",{page:window.location.pathname,referrer:document.referrer}),document.addEventListener("click",e=>{const t=e.target.closest("[data-track]");t&&this.trackEvent("interaction",{element:t.dataset.track,page:window.location.pathname})}))}hasWebSocketSupport(){return"WebSocket"in window&&window.Echo}hasNotificationSupport(){return"Notification"in window}hasSharingSupport(){return"share"in navigator}escapeHtml(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}showNotification(e,t="info"){const i=document.createElement("div");i.className=`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${t==="success"?"bg-green-500 text-white":t==="warning"?"bg-yellow-500 text-white":t==="error"?"bg-red-500 text-white":"bg-blue-500 text-white"}`,i.innerHTML=`
            <div class="flex items-center space-x-3">
                <span>${e}</span>
                <button class="ml-auto text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `,document.body.appendChild(i),setTimeout(()=>{i.classList.remove("translate-x-full")},50),i.querySelector("button").addEventListener("click",()=>{this.removeNotification(i)}),setTimeout(()=>{this.removeNotification(i)},5e3)}removeNotification(e){e.parentElement&&(e.classList.add("translate-x-full"),setTimeout(()=>{e.parentElement&&e.parentElement.removeChild(e)},300))}dispatchEvent(e,t={}){const i=new CustomEvent(e,{detail:t});document.dispatchEvent(i)}trackEvent(e,t={}){this.options.enableAnalytics&&(typeof gtag<"u"&&gtag("event",e,t),this.options.analyticsEndpoint&&fetch(this.options.analyticsEndpoint,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({event:e,data:t,timestamp:Date.now(),url:window.location.href,userAgent:navigator.userAgent})}).catch(i=>{this.options.debugMode&&console.warn("Analytics tracking failed:",i)}),this.options.debugMode&&console.log("📊 Analytics Event:",e,t))}trackError(e){this.trackEvent("javascript_error",{message:e.message||e.toString(),stack:e.stack,url:window.location.href,userAgent:navigator.userAgent})}getComponent(e){return this.components[e]}isComponentEnabled(e){return!!this.components[e]}reload(){window.location.reload()}destroy(){Object.values(this.components).forEach(e=>{e.destroy&&e.destroy()}),this.components={},this.isInitialized=!1,console.log("🎫 HD Tickets App destroyed")}}let p=null;function m(l={}){return p&&p.destroy(),p=new v(l),p}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{m(window.hdTicketsConfig||{})}):m(window.hdTicketsConfig||{});window.HDTicketsApp=v;window.initializeHDTickets=m;const M=Object.freeze(Object.defineProperty({__proto__:null,default:v},Symbol.toStringTag,{value:"Module"}));class g{constructor(e={}){this.options={formSelector:"#filters-form",resultsSelector:"#main-content",loadingSelector:"#loading-indicator",debounceMs:300,maxRetries:3,cacheEnabled:!0,enableUrlSync:!0,...e},this.form=document.querySelector(this.options.formSelector),this.resultsContainer=document.querySelector(this.options.resultsSelector),this.loadingIndicator=document.querySelector(this.options.loadingSelector),this.cache=new Map,this.debounceTimer=null,this.requestController=null,this.retryCount=0,this.init()}init(){if(!this.form){console.warn("TicketFilters: Form not found");return}this.setupEventListeners(),this.setupUrlSync(),this.setupKeyboardShortcuts(),this.restoreFiltersFromUrl(),console.log("TicketFilters initialized")}setupEventListeners(){this.form.addEventListener("change",s=>{this.handleFilterChange(s)});const e=this.form.querySelector("#keywords");e&&(e.addEventListener("input",s=>{this.handleSearchInput(s)}),e.addEventListener("keydown",s=>{s.key==="Enter"&&(s.preventDefault(),this.clearDebounce(),this.submitFilters())})),document.querySelectorAll("[data-clear-filters]").forEach(s=>{s.addEventListener("click",()=>{this.clearAllFilters()})});const t=document.getElementById("advanced-filters-toggle");t&&t.addEventListener("click",()=>{this.toggleAdvancedFilters()});const i=document.getElementById("per-page-select");i&&i.addEventListener("change",()=>{this.handleFilterChange()})}setupUrlSync(){this.options.enableUrlSync&&window.addEventListener("popstate",e=>{e.state&&e.state.filters&&(this.restoreFilters(e.state.filters),this.submitFilters(!1))})}setupKeyboardShortcuts(){document.addEventListener("keydown",e=>{if((e.ctrlKey||e.metaKey)&&e.key==="k"){e.preventDefault();const t=document.getElementById("keywords");t&&(t.focus(),t.select())}if((e.ctrlKey||e.metaKey)&&e.key==="Enter"&&(e.preventDefault(),this.clearDebounce(),this.submitFilters()),e.key==="Escape"){const t=document.getElementById("keywords");t&&document.activeElement===t&&(t.value="",this.handleSearchInput())}})}handleFilterChange(e=null){if(this.clearDebounce(),e&&["sort_by","sort_dir","view","per_page"].includes(e.target.name)){this.submitFilters();return}this.debounceTimer=setTimeout(()=>{this.submitFilters()},this.options.debounceMs)}handleSearchInput(e=null){this.clearDebounce();const t=document.getElementById("keywords");if(!t)return;const i=t.value.trim(),s=document.getElementById("clear-search");s&&(i.length>0?s.classList.remove("hidden"):s.classList.add("hidden")),this.debounceTimer=setTimeout(()=>{this.submitFilters(),i.length>=2?this.loadSearchSuggestions(i):this.hideSuggestions()},this.options.debounceMs)}async loadSearchSuggestions(e){try{const i=await(await fetch(`/tickets/scraping/search-suggestions?term=${encodeURIComponent(e)}`)).json();i.success&&i.suggestions.length>0?this.displaySuggestions(i.suggestions):this.hideSuggestions()}catch(t){console.warn("Failed to load search suggestions:",t),this.hideSuggestions()}}displaySuggestions(e){const t=document.getElementById("search-suggestions"),i=document.getElementById("suggestions-list");!t||!i||(i.innerHTML=e.map(s=>`
            <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer suggestion-item flex items-center"
                 data-value="${this.escapeHtml(s.value)}"
                 data-type="${this.escapeHtml(s.type)}">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="flex-1">${this.escapeHtml(s.value)}</span>
                <span class="text-xs text-gray-500 capitalize">${this.escapeHtml(s.type)}</span>
            </div>
        `).join(""),i.querySelectorAll(".suggestion-item").forEach(s=>{s.addEventListener("click",()=>{this.applySuggestion(s.dataset.value)})}),t.classList.remove("hidden"))}hideSuggestions(){const e=document.getElementById("search-suggestions");e&&e.classList.add("hidden")}applySuggestion(e){const t=document.getElementById("keywords");t&&(t.value=e,this.hideSuggestions(),this.clearDebounce(),this.submitFilters())}async submitFilters(e=!0){const t=new FormData(this.form),i=this.formDataToObject(t),s=this.generateCacheKey(i);if(this.options.cacheEnabled&&this.cache.has(s)){const o=this.cache.get(s);this.updateResults(o);return}if(this.requestController&&this.requestController.abort(),this.requestController=new AbortController,e&&this.options.enableUrlSync){const o=this.buildUrlWithFilters(i),r={filters:i};history.pushState(r,"",o)}this.showLoading();try{const o=await fetch("/tickets/scraping/ajax-filter",{method:"POST",body:t,headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content},signal:this.requestController.signal});if(!o.ok)throw new Error(`HTTP ${o.status}: ${o.statusText}`);const r=await o.json();if(r.success){if(this.options.cacheEnabled&&(this.cache.set(s,r),this.cache.size>50)){const n=this.cache.keys().next().value;this.cache.delete(n)}this.updateResults(r),this.retryCount=0}else throw new Error(r.message||"Filter request failed")}catch(o){if(o.name==="AbortError")return;if(console.error("Filter error:",o),this.retryCount<this.options.maxRetries){this.retryCount++,console.log(`Retrying filter request (${this.retryCount}/${this.options.maxRetries})`),setTimeout(()=>this.submitFilters(e),1e3*this.retryCount);return}this.showError("Failed to load tickets. Please try again.")}finally{this.hideLoading(),this.requestController=null}}updateResults(e){this.resultsContainer&&(e.html&&(this.resultsContainer.innerHTML=e.html),this.updateStatistics(e.stats),this.updateActiveFilters(e.applied_filters),this.updatePagination(e.pagination),this.dispatchEvent("filtersApplied",{data:e}))}updateStatistics(e){if(!e)return;document.querySelectorAll('[data-stat="total-count"]').forEach(o=>{o.textContent=e.total_count?e.total_count.toLocaleString():"0"}),document.querySelectorAll('[data-stat="avg-price"]').forEach(o=>{o.textContent=e.avg_price?`$${e.avg_price.toFixed(2)}`:"N/A"}),document.querySelectorAll('[data-stat="available-count"]').forEach(o=>{o.textContent=e.available_count?e.available_count.toLocaleString():"0"})}updateActiveFilters(e){const t=document.getElementById("active-filters-list");!t||!e||(t.innerHTML=Object.entries(e).filter(([i,s])=>s&&s!==""&&s!==!1).map(([i,s])=>`
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                      data-filter="${i}">
                    ${this.formatFilterLabel(i)}: ${this.formatFilterValue(s)}
                    <button type="button" class="ml-1 text-blue-400 hover:text-blue-600"
                            onclick="ticketFilters.removeFilter('${i}')">×</button>
                </span>
            `).join(""))}updatePagination(e){if(!e)return;document.querySelectorAll("[data-pagination]").forEach(i=>{})}clearAllFilters(){this.form.reset(),this.hideSuggestions(),this.options.enableUrlSync&&history.pushState({},"",window.location.pathname),this.submitFilters()}removeFilter(e){const t=this.form.querySelector(`[name="${e}"]`);t&&(t.type==="checkbox"?t.checked=!1:t.value="",this.submitFilters())}toggleAdvancedFilters(){const e=document.getElementById("advanced-filters"),t=document.getElementById("advanced-icon"),i=document.getElementById("advanced-filters-toggle");if(!e||!t||!i)return;const s=i.getAttribute("aria-expanded")==="true";e.classList.toggle("hidden"),t.style.transform=s?"rotate(0deg)":"rotate(180deg)",i.setAttribute("aria-expanded",!s)}showLoading(){this.loadingIndicator&&this.loadingIndicator.classList.remove("hidden"),this.resultsContainer&&(this.resultsContainer.style.opacity="0.5")}hideLoading(){this.loadingIndicator&&this.loadingIndicator.classList.add("hidden"),this.resultsContainer&&(this.resultsContainer.style.opacity="1")}showError(e){const t=document.createElement("div");t.className="fixed top-4 right-4 z-50 px-4 py-2 bg-red-500 text-white rounded-lg shadow-lg transition-opacity duration-300",t.textContent=e,document.body.appendChild(t),setTimeout(()=>{t.style.opacity="0",setTimeout(()=>document.body.removeChild(t),300)},5e3)}clearDebounce(){this.debounceTimer&&(clearTimeout(this.debounceTimer),this.debounceTimer=null)}formDataToObject(e){const t={};for(const[i,s]of e.entries())t[i]=s;return t}generateCacheKey(e){return btoa(JSON.stringify(e))}buildUrlWithFilters(e){const t=new URL(window.location.href);return t.search="",Object.entries(e).forEach(([i,s])=>{s&&s!==""&&s!=="false"&&t.searchParams.set(i,s)}),t.toString()}restoreFiltersFromUrl(){const e=new URLSearchParams(window.location.search),t=Object.fromEntries(e.entries());Object.keys(t).length>0&&this.restoreFilters(t)}restoreFilters(e){Object.entries(e).forEach(([t,i])=>{const s=this.form.querySelector(`[name="${t}"]`);s&&(s.type==="checkbox"?s.checked=i==="1"||i==="true":s.value=i)})}formatFilterLabel(e){return e.replace(/_/g," ").replace(/\b\w/g,t=>t.toUpperCase())}formatFilterValue(e){return typeof e=="boolean"?e?"Yes":"No":e}escapeHtml(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}dispatchEvent(e,t={}){const i=new CustomEvent(e,{detail:t});document.dispatchEvent(i)}getFilters(){const e=new FormData(this.form);return this.formDataToObject(e)}setFilter(e,t){const i=this.form.querySelector(`[name="${e}"]`);i&&(i.type==="checkbox"?i.checked=!!t:i.value=t,this.submitFilters())}clearCache(){this.cache.clear(),console.log("Filter cache cleared")}destroy(){this.clearDebounce(),this.requestController&&this.requestController.abort(),this.cache.clear()}}window.TicketFilters=g;document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{window.ticketFilters=new g}):window.ticketFilters=new g;const S=Object.freeze(Object.defineProperty({__proto__:null},Symbol.toStringTag,{value:"Module"}));class f{constructor(e={}){this.options={echoConfig:{broadcaster:"pusher",key:window.pusherKey,cluster:window.pusherCluster||"mt1",forceTLS:!0},enableNotifications:!0,enableSound:!0,priceThreshold:.05,maxRetries:5,retryDelay:5e3,...e},this.echo=null,this.channels=new Map,this.retryCount=0,this.isConnected=!1,this.watchedTickets=new Set,this.priceHistory=new Map,this.notifications=[],this.alertSound=new Audio("/sounds/price-alert.mp3"),this.alertSound.volume=.3,this.init()}init(){this.setupEcho(),this.setupNotifications(),this.setupUI(),this.loadWatchedTickets(),document.addEventListener("visibilitychange",()=>{document.hidden?this.pauseMonitoring():this.resumeMonitoring()}),console.log("PriceMonitor initialized")}setupEcho(){if(!window.Echo){console.error("Laravel Echo not found");return}try{this.echo=new Echo(this.options.echoConfig),this.setupConnectionEvents(),this.isConnected=!0,this.retryCount=0}catch(e){console.error("Failed to initialize Echo:",e),this.handleConnectionError()}}setupConnectionEvents(){this.echo&&(this.echo.connector.pusher.connection.bind("connected",()=>{console.log("WebSocket connected"),this.isConnected=!0,this.retryCount=0,this.updateConnectionStatus("connected"),this.resubscribeChannels()}),this.echo.connector.pusher.connection.bind("disconnected",()=>{console.log("WebSocket disconnected"),this.isConnected=!1,this.updateConnectionStatus("disconnected")}),this.echo.connector.pusher.connection.bind("error",e=>{console.error("WebSocket error:",e),this.handleConnectionError()}),this.echo.connector.pusher.connection.bind("unavailable",()=>{console.warn("WebSocket unavailable"),this.handleConnectionError()}))}setupNotifications(){this.options.enableNotifications&&"Notification"in window&&Notification.permission==="default"&&Notification.requestPermission().then(e=>{console.log("Notification permission:",e)})}setupUI(){this.createStatusIndicator(),this.setupPriceAlertControls(),this.setupNotificationCenter()}createStatusIndicator(){const e=document.createElement("div");e.id="price-monitor-status",e.className="fixed bottom-4 left-4 z-50 flex items-center space-x-2 px-3 py-2 bg-white rounded-lg shadow-lg border",e.innerHTML=`
            <div id="status-dot" class="w-3 h-3 rounded-full bg-gray-400"></div>
            <span id="status-text" class="text-sm font-medium text-gray-600">Connecting...</span>
            <button id="toggle-monitoring" class="text-xs text-blue-600 hover:text-blue-800">Pause</button>
        `,document.body.appendChild(e),document.getElementById("toggle-monitoring").addEventListener("click",()=>{this.isConnected?this.pauseMonitoring():this.resumeMonitoring()})}setupPriceAlertControls(){document.querySelectorAll(".ticket-card").forEach(e=>{const t=e.dataset.ticketId;if(!t)return;const i=document.createElement("button");i.className="price-alert-toggle px-2 py-1 text-xs rounded border transition-colors",i.dataset.ticketId=t,this.updateAlertButton(i,this.watchedTickets.has(t)),i.addEventListener("click",o=>{o.preventDefault(),o.stopPropagation(),this.togglePriceAlert(t)});const s=e.querySelector(".ticket-actions");s&&s.appendChild(i)})}setupNotificationCenter(){const e=document.createElement("div");e.id="notification-center",e.className="fixed top-4 right-4 z-50 space-y-2",e.style.maxWidth="300px",document.body.appendChild(e)}subscribeToTicket(e){if(!this.echo||this.channels.has(e))return;const t=this.echo.channel(`ticket.${e}`);t.listen("TicketPriceChanged",i=>{this.handlePriceUpdate(i)}),t.listen("TicketAvailabilityChanged",i=>{this.handleAvailabilityUpdate(i)}),t.listen("TicketStatusChanged",i=>{this.handleStatusUpdate(i)}),this.channels.set(e,t),console.log(`Subscribed to ticket ${e}`)}unsubscribeFromTicket(e){const t=this.channels.get(e);t&&(t.stopListening("TicketPriceChanged"),t.stopListening("TicketAvailabilityChanged"),t.stopListening("TicketStatusChanged"),this.echo.leaveChannel(t.name),this.channels.delete(e),console.log(`Unsubscribed from ticket ${e}`))}resubscribeChannels(){const e=Array.from(this.watchedTickets);this.channels.clear(),e.forEach(t=>{this.subscribeToTicket(t)})}handlePriceUpdate(e){const{ticket_id:t,old_price:i,new_price:s,percentage_change:o,timestamp:r}=e;this.priceHistory.has(t)||this.priceHistory.set(t,[]);const n=this.priceHistory.get(t);n.push({price:s,timestamp:new Date(r),change:o}),n.length>100&&n.shift(),this.updateTicketPrice(t,i,s,o),Math.abs(o)>=this.options.priceThreshold&&this.sendPriceNotification(t,i,s,o),this.updatePriceChart(t)}handleAvailabilityUpdate(e){const{ticket_id:t,available_quantity:i,total_quantity:s,is_available:o}=e;this.updateTicketAvailability(t,i,s,o),o||this.sendAvailabilityNotification(t,"Ticket is now sold out!")}handleStatusUpdate(e){const{ticket_id:t,old_status:i,new_status:s,reason:o}=e;this.updateTicketStatus(t,s),(s==="inactive"||s==="removed")&&this.sendStatusNotification(t,`Ticket is now ${s}`,o)}updateTicketPrice(e,t,i,s){document.querySelectorAll(`[data-ticket-id="${e}"]`).forEach(r=>{const n=r.querySelector(".ticket-price"),c=r.querySelector(".price-change");if(n&&(n.textContent=`$${i.toFixed(2)}`,n.classList.add("price-updated"),setTimeout(()=>{n.classList.remove("price-updated")},1e3),s>0?(n.classList.add("text-red-600"),n.classList.remove("text-green-600")):s<0&&(n.classList.add("text-green-600"),n.classList.remove("text-red-600"))),c){const a=s>=0?"+":"";c.textContent=`${a}${s.toFixed(1)}%`,c.className=`price-change text-xs font-medium ${s>=0?"text-red-600":"text-green-600"}`}})}updateTicketAvailability(e,t,i,s){document.querySelectorAll(`[data-ticket-id="${e}"]`).forEach(r=>{const n=r.querySelector(".ticket-availability"),c=r.querySelector(".availability-status");n&&(n.textContent=`${t} available`),c&&(c.textContent=s?"Available":"Sold Out",c.className=`availability-status px-2 py-1 text-xs font-semibold rounded ${s?"bg-green-100 text-green-800":"bg-red-100 text-red-800"}`)})}updateTicketStatus(e,t){document.querySelectorAll(`[data-ticket-id="${e}"]`).forEach(s=>{const o=s.querySelector(".ticket-status");o&&(o.textContent=t.charAt(0).toUpperCase()+t.slice(1),o.className=`ticket-status px-2 py-1 text-xs font-semibold rounded ${this.getStatusColor(t)}`),(t==="inactive"||t==="removed")&&(s.style.opacity="0.5",s.classList.add("pointer-events-none"))})}updatePriceChart(e){const t=document.getElementById(`price-chart-${e}`);if(!t||!this.priceHistory.has(e))return;const i=this.priceHistory.get(e),s={labels:i.map(o=>o.timestamp.toLocaleTimeString()),datasets:[{label:"Price",data:i.map(o=>o.price),borderColor:"rgb(59, 130, 246)",backgroundColor:"rgba(59, 130, 246, 0.1)",tension:.1}]};window.Chart&&t.chart&&(t.chart.data=s,t.chart.update("none"))}updateConnectionStatus(e){const t=document.getElementById("status-dot"),i=document.getElementById("status-text"),s=document.getElementById("toggle-monitoring");if(!(!t||!i||!s))switch(e){case"connected":t.className="w-3 h-3 rounded-full bg-green-500",i.textContent="Live Monitoring",s.textContent="Pause";break;case"disconnected":t.className="w-3 h-3 rounded-full bg-red-500",i.textContent="Disconnected",s.textContent="Resume";break;case"connecting":t.className="w-3 h-3 rounded-full bg-yellow-500",i.textContent="Connecting...",s.textContent="Cancel";break}}togglePriceAlert(e){this.watchedTickets.has(e)?this.removePriceAlert(e):this.addPriceAlert(e)}addPriceAlert(e){this.watchedTickets.add(e),this.subscribeToTicket(e),this.updateAlertButtons(e,!0),this.saveWatchedTickets(),this.showNotification({title:"Price Alert Added",message:"You will be notified of price changes for this ticket.",type:"success"})}removePriceAlert(e){this.watchedTickets.delete(e),this.unsubscribeFromTicket(e),this.updateAlertButtons(e,!1),this.priceHistory.delete(e),this.saveWatchedTickets(),this.showNotification({title:"Price Alert Removed",message:"You will no longer receive notifications for this ticket.",type:"info"})}updateAlertButtons(e,t){document.querySelectorAll(`[data-ticket-id="${e}"] .price-alert-toggle`).forEach(s=>{this.updateAlertButton(s,t)})}updateAlertButton(e,t){t?(e.textContent="🔔 Watching",e.className="price-alert-toggle px-2 py-1 text-xs rounded border border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"):(e.textContent="🔕 Watch",e.className="price-alert-toggle px-2 py-1 text-xs rounded border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors")}showNotification(e){const t=Date.now()+Math.random();e.id=t,e.timestamp=new Date,this.notifications.unshift(e),this.notifications.length>50&&(this.notifications=this.notifications.slice(0,50)),this.renderNotification(e),e.persistent||setTimeout(()=>{this.removeNotification(t)},5e3)}renderNotification(e){const t=document.getElementById("notification-center");if(!t)return;const i=document.createElement("div");i.id=`notification-${e.id}`,i.className=`notification p-4 rounded-lg shadow-lg border-l-4 max-w-sm transform transition-all duration-300 translate-x-full ${this.getNotificationColor(e.type)}`,i.innerHTML=`
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-sm">${e.title}</h4>
                    <p class="text-xs text-gray-600 mt-1">${e.message}</p>
                    <p class="text-xs text-gray-400 mt-1">${e.timestamp.toLocaleTimeString()}</p>
                </div>
                <button class="ml-2 text-gray-400 hover:text-gray-600" onclick="priceMonitor.removeNotification(${e.id})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `,t.insertBefore(i,t.firstChild),setTimeout(()=>{i.classList.remove("translate-x-full")},50)}removeNotification(e){const t=document.getElementById(`notification-${e}`);t&&(t.classList.add("translate-x-full"),setTimeout(()=>{t.remove()},300)),this.notifications=this.notifications.filter(i=>i.id!==e)}handleConnectionError(){this.retryCount<this.options.maxRetries?(this.retryCount++,this.updateConnectionStatus("connecting"),setTimeout(()=>{console.log(`Attempting reconnection (${this.retryCount}/${this.options.maxRetries})`),this.setupEcho()},this.options.retryDelay*this.retryCount)):(console.error("Max reconnection attempts reached"),this.updateConnectionStatus("disconnected"))}pauseMonitoring(){this.echo&&(this.echo.disconnect(),this.isConnected=!1,this.updateConnectionStatus("disconnected"))}resumeMonitoring(){this.retryCount=0,this.setupEcho()}getStatusColor(e){return{active:"bg-green-100 text-green-800",inactive:"bg-yellow-100 text-yellow-800",removed:"bg-red-100 text-red-800",sold_out:"bg-gray-100 text-gray-800"}[e]||"bg-gray-100 text-gray-800"}getNotificationColor(e){const t={success:"border-green-500 bg-green-50",warning:"border-yellow-500 bg-yellow-50",error:"border-red-500 bg-red-50",info:"border-blue-500 bg-blue-50"};return t[e]||t.info}saveWatchedTickets(){const e=Array.from(this.watchedTickets);localStorage.setItem("watched_tickets",JSON.stringify(e))}loadWatchedTickets(){try{const e=localStorage.getItem("watched_tickets");if(e){const t=JSON.parse(e);t.forEach(i=>{this.watchedTickets.add(i),this.subscribeToTicket(i)}),setTimeout(()=>{t.forEach(i=>{this.updateAlertButtons(i,!0)})},1e3)}}catch(e){console.warn("Failed to load watched tickets:",e)}}isWatching(e){return this.watchedTickets.has(e)}getPriceHistory(e){return this.priceHistory.get(e)||[]}getNotifications(){return[...this.notifications]}clearNotifications(){this.notifications=[];const e=document.getElementById("notification-center");e&&(e.innerHTML="")}destroy(){this.echo&&this.echo.disconnect(),this.channels.clear(),this.watchedTickets.clear(),this.priceHistory.clear(),this.notifications=[];const e=document.getElementById("price-monitor-status");e&&e.remove();const t=document.getElementById("notification-center");t&&t.remove()}}window.PriceMonitor=f;document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{window.priceMonitor=new f}):window.priceMonitor=new f;const L=Object.freeze(Object.defineProperty({__proto__:null},Symbol.toStringTag,{value:"Module"}));class y{constructor(e={}){this.options={maxCompare:6,storageKey:"ticket_comparison",enableExport:!0,enableSharing:!0,enableNotifications:!0,autoSave:!0,...e},this.compareList=new Map,this.compareModal=null,this.isVisible=!1,this.init()}init(){this.loadComparison(),this.createUI(),this.setupEventListeners(),this.updateCompareIndicators(),console.log("TicketComparison initialized")}createUI(){this.createCompareButton(),this.createCompareModal(),this.createCompareIndicators()}createCompareButton(){const e=document.createElement("button");e.id="compare-tickets-btn",e.className="fixed bottom-4 right-4 z-40 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg transition-all duration-300 transform translate-y-16 opacity-0",e.innerHTML=`
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>Compare (<span id="compare-count">0</span>)</span>
            </div>
        `,e.addEventListener("click",()=>{this.showComparison()}),document.body.appendChild(e)}createCompareModal(){const e=document.createElement("div");e.id="compare-modal",e.className="fixed inset-0 z-50 hidden overflow-y-auto",e.innerHTML=`
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
                    <p class="mt-2 text-sm">Select up to 6 tickets to compare by clicking the comparison button on any ticket card</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,document.body.appendChild(e),this.compareModal=e,this.setupModalEventListeners()}createCompareIndicators(){document.querySelectorAll(".ticket-card").forEach(e=>{const t=e.dataset.ticketId;if(!t)return;const i=document.createElement("div");i.className="compare-indicator absolute top-2 right-2",i.innerHTML=`
                <button class="compare-checkbox w-8 h-8 rounded-full border-2 border-white bg-black bg-opacity-20 hover:bg-opacity-40 text-white transition-all duration-200 flex items-center justify-center"
                        data-ticket-id="${t}"
                        title="Add to comparison">
                    <svg class="w-4 h-4 hidden compare-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg class="w-4 h-4 compare-plus" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
            `,e.style.position="relative",e.appendChild(i),i.querySelector(".compare-checkbox").addEventListener("click",o=>{o.preventDefault(),o.stopPropagation(),this.toggleCompare(t)})})}setupEventListeners(){document.addEventListener("keydown",e=>{(e.ctrlKey||e.metaKey)&&e.shiftKey&&e.key==="C"&&(e.preventDefault(),this.compareList.size>0&&this.showComparison()),e.key==="Escape"&&this.isVisible&&this.hideComparison()})}setupModalEventListeners(){this.compareModal&&(this.compareModal.querySelector("#close-comparison").addEventListener("click",()=>{this.hideComparison()}),this.compareModal.querySelector(".modal-backdrop").addEventListener("click",()=>{this.hideComparison()}),this.compareModal.querySelector("#clear-comparison").addEventListener("click",()=>{this.clearAll()}),this.compareModal.querySelector("#export-comparison").addEventListener("click",()=>{this.exportComparison()}),this.compareModal.querySelector("#share-comparison").addEventListener("click",()=>{this.shareComparison()}),this.compareModal.querySelector("#table-view-btn").addEventListener("click",()=>{this.setViewMode("table")}),this.compareModal.querySelector("#card-view-btn").addEventListener("click",()=>{this.setViewMode("card")}),this.compareModal.querySelector("#compare-sort").addEventListener("change",e=>{this.sortComparison(e.target.value)}),this.compareModal.querySelector("#compare-highlight").addEventListener("change",e=>{this.highlightBest(e.target.value)}),this.compareModal.querySelector("#show-differences").addEventListener("change",e=>{this.toggleDifferencesOnly(e.target.checked)}))}toggleCompare(e){this.compareList.has(e)?this.removeFromCompare(e):this.addToCompare(e)}addToCompare(e){if(this.compareList.size>=this.options.maxCompare)return this.showNotification(`Maximum ${this.options.maxCompare} tickets can be compared`,"warning"),!1;const t=this.getTicketData(e);return t?(this.compareList.set(e,t),this.updateUI(),this.saveComparison(),this.showNotification("Ticket added to comparison list","success"),!0):(this.showNotification("Unable to load ticket information. Please try again.","error"),!1)}removeFromCompare(e){return this.compareList.has(e)?(this.compareList.delete(e),this.updateUI(),this.saveComparison(),this.showNotification("Ticket removed from comparison list","info"),!0):!1}clearAll(){this.compareList.clear(),this.updateUI(),this.saveComparison(),this.showNotification("All tickets cleared from comparison","info")}showComparison(){if(this.compareList.size===0){this.showNotification("Please select at least one ticket to compare","warning");return}this.renderComparison(),this.compareModal.classList.remove("hidden"),this.isVisible=!0,document.body.classList.add("overflow-hidden")}hideComparison(){this.compareModal.classList.add("hidden"),this.isVisible=!1,document.body.classList.remove("overflow-hidden")}getTicketData(e){const t=document.querySelector(`[data-ticket-id="${e}"]`);return t?{id:e,title:t.querySelector(".ticket-title")?.textContent||"Event Details Unavailable",price:this.extractPrice(t),venue:t.querySelector(".ticket-venue")?.textContent||"Venue TBD",date:t.querySelector(".ticket-date")?.textContent||"Date TBD",category:t.querySelector(".ticket-category")?.textContent||"General Admission",availability:t.querySelector(".ticket-availability")?.textContent||"Check Platform",platform:t.querySelector(".ticket-platform")?.textContent||"Multiple Platforms",rating:this.extractRating(t),image:t.querySelector(".ticket-image img")?.src||"/images/default-ticket.jpg",url:t.querySelector("a")?.href||"#",features:this.extractFeatures(t)}:null}extractPrice(e){const t=e.querySelector(".ticket-price")?.textContent||"$0",i=parseFloat(t.replace(/[^0-9.]/g,""));return isNaN(i)?0:i}extractRating(e){const t=e.querySelector(".ticket-rating");if(t){const i=t.textContent,s=parseFloat(i.match(/[0-9.]+/)?.[0]||"0");return isNaN(s)?0:s}return 0}extractFeatures(e){const t=[];return e.querySelectorAll(".ticket-feature").forEach(i=>{t.push(i.textContent.trim())}),t}updateUI(){this.updateCompareButton(),this.updateCompareIndicators(),this.isVisible&&this.renderComparison()}updateCompareButton(){const e=document.getElementById("compare-tickets-btn"),t=document.getElementById("compare-count");e&&t&&(t.textContent=this.compareList.size,this.compareList.size>0?(e.classList.remove("translate-y-16","opacity-0"),e.classList.add("translate-y-0","opacity-100")):(e.classList.add("translate-y-16","opacity-0"),e.classList.remove("translate-y-0","opacity-100")))}updateCompareIndicators(){document.querySelectorAll(".compare-checkbox").forEach(e=>{const t=e.dataset.ticketId,i=this.compareList.has(t),s=e.querySelector(".compare-check"),o=e.querySelector(".compare-plus");i?(e.classList.add("bg-blue-600","border-blue-600"),e.classList.remove("bg-black","bg-opacity-20"),s.classList.remove("hidden"),o.classList.add("hidden"),e.title="Remove from comparison"):(e.classList.remove("bg-blue-600","border-blue-600"),e.classList.add("bg-black","bg-opacity-20"),s.classList.add("hidden"),o.classList.remove("hidden"),e.title="Add to comparison")})}renderComparison(){const e=this.compareModal.querySelector("#comparison-content"),t=this.compareModal.querySelector("#modal-compare-count");if(t.textContent=`${this.compareList.size} selected`,this.compareList.size===0){e.innerHTML=`
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="mt-2 text-sm">Select up to 6 tickets to compare by clicking the comparison button on any ticket card</p>
                </div>
            `;return}this.compareModal.querySelector("#table-view-btn").classList.contains("bg-white")?this.renderTableView(e):this.renderCardView(e)}renderTableView(e){const t=Array.from(this.compareList.values()),i=document.createElement("div");i.className="overflow-x-auto",i.innerHTML=`
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
                    ${t.map(s=>this.renderTableRow(s)).join("")}
                </tbody>
            </table>
        `,e.innerHTML="",e.appendChild(i)}renderTableRow(e){return`
            <tr class="hover:bg-gray-50 ticket-comparison-row" data-ticket-id="${e.id}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img class="w-10 h-10 rounded-md object-cover mr-3" src="${e.image}" alt="${e.title}">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">${e.title}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-semibold text-gray-900">$${e.price.toFixed(2)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${e.venue}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${e.date}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${e.category}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${e.availability}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${e.platform}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${e.rating>0?`
                        <div class="flex items-center">
                            <span class="text-sm text-gray-900">${e.rating.toFixed(1)}</span>
                            <svg class="ml-1 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                    `:'<span class="text-sm text-gray-400">No rating</span>'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <a href="${e.url}" class="text-blue-600 hover:text-blue-800" target="_blank">View</a>
                        <button class="text-red-600 hover:text-red-800" onclick="ticketComparison.removeFromCompare('${e.id}')">Remove</button>
                    </div>
                </td>
            </tr>
        `}renderCardView(e){const t=Array.from(this.compareList.values()),i=document.createElement("div");i.className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6",i.innerHTML=t.map(s=>this.renderComparisonCard(s)).join(""),e.innerHTML="",e.appendChild(i)}renderComparisonCard(e){return`
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow ticket-comparison-card" data-ticket-id="${e.id}">
                <div class="relative">
                    <img class="w-full h-48 object-cover rounded-t-lg" src="${e.image}" alt="${e.title}">
                    <div class="absolute top-2 right-2">
                        <button class="p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors" 
                                onclick="ticketComparison.removeFromCompare('${e.id}')" 
                                title="Remove from comparison">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">${e.title}</h3>
                    
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Price:</span>
                            <span class="font-semibold text-gray-900">$${e.price.toFixed(2)}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Venue:</span>
                            <span>${e.venue}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Date:</span>
                            <span>${e.date}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Category:</span>
                            <span>${e.category}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Platform:</span>
                            <span>${e.platform}</span>
                        </div>
                        
                        ${e.rating>0?`
                            <div class="flex justify-between">
                                <span>Rating:</span>
                                <div class="flex items-center">
                                    <span>${e.rating.toFixed(1)}</span>
                                    <svg class="ml-1 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                        `:""}
                    </div>
                    
                    ${e.features.length>0?`
                        <div class="mt-3">
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Features:</h4>
                            <div class="flex flex-wrap gap-1">
                                ${e.features.map(t=>`
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${t}</span>
                                `).join("")}
                            </div>
                        </div>
                    `:""}
                    
                    <div class="mt-4 flex space-x-2">
                        <a href="${e.url}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded-md text-sm font-medium transition-colors" target="_blank">
                            View Ticket
                        </a>
                    </div>
                </div>
            </div>
        `}setViewMode(e){const t=this.compareModal.querySelector("#table-view-btn"),i=this.compareModal.querySelector("#card-view-btn");e==="table"?(t.classList.add("bg-white","text-gray-900","shadow-sm"),t.classList.remove("text-gray-600"),i.classList.remove("bg-white","text-gray-900","shadow-sm"),i.classList.add("text-gray-600")):(i.classList.add("bg-white","text-gray-900","shadow-sm"),i.classList.remove("text-gray-600"),t.classList.remove("bg-white","text-gray-900","shadow-sm"),t.classList.add("text-gray-600")),this.renderComparison()}sortComparison(e){const t=Array.from(this.compareList.values());t.sort((i,s)=>{switch(e){case"price_asc":return i.price-s.price;case"price_desc":return s.price-i.price;case"rating_desc":return s.rating-i.rating;case"date_desc":case"date_asc":return e==="date_desc"?-1:1;default:return 0}}),this.compareList.clear(),t.forEach(i=>{this.compareList.set(i.id,i)}),this.renderComparison()}highlightBest(e){if(document.querySelectorAll(".ticket-comparison-row, .ticket-comparison-card").forEach(s=>{s.classList.remove("bg-green-50","border-green-200")}),!e)return;const t=Array.from(this.compareList.values());let i=null;switch(e){case"best_price":i=t.reduce((s,o)=>o.price<s.price?o:s);break;case"highest_rating":i=t.reduce((s,o)=>o.rating>s.rating?o:s);break}i&&document.querySelectorAll(`[data-ticket-id="${i.id}"]`).forEach(o=>{(o.classList.contains("ticket-comparison-row")||o.classList.contains("ticket-comparison-card"))&&o.classList.add("bg-green-50","border-green-200")})}toggleDifferencesOnly(e){this.showDifferencesOnly=e}exportComparison(){const e=Array.from(this.compareList.values()),t=this.generateCSV(e),i=new Blob([t],{type:"text/csv"}),s=window.URL.createObjectURL(i),o=document.createElement("a");o.href=s,o.download=`ticket-comparison-${Date.now()}.csv`,o.click(),window.URL.revokeObjectURL(s),this.showNotification("Comparison exported successfully","success")}generateCSV(e){const t=["Title","Price","Venue","Date","Category","Platform","Rating","Availability"],i=e.map(s=>[s.title,s.price,s.venue,s.date,s.category,s.platform,s.rating,s.availability]);return[t,...i].map(s=>s.map(o=>`"${o}"`).join(",")).join(`
`)}async shareComparison(){if(!navigator.share){const e=Array.from(this.compareList.values()),t=`Comparing ${e.length} tickets:

`+e.map(i=>`${i.title} - $${i.price} at ${i.venue}`).join(`
`);try{await navigator.clipboard.writeText(t),this.showNotification("Comparison copied to clipboard","success")}catch{this.showNotification("Unable to copy to clipboard","error")}return}try{await navigator.share({title:"Ticket Comparison",text:`Comparing ${this.compareList.size} sports tickets`,url:window.location.href})}catch(e){e.name!=="AbortError"&&this.showNotification("Unable to share","error")}}saveComparison(){if(!this.options.autoSave)return;const e={tickets:Array.from(this.compareList.entries()),timestamp:Date.now()};try{localStorage.setItem(this.options.storageKey,JSON.stringify(e))}catch(t){console.warn("Failed to save comparison:",t)}}loadComparison(){try{const e=localStorage.getItem(this.options.storageKey);if(e){const t=JSON.parse(e);Date.now()-t.timestamp<1440*60*1e3?t.tickets.forEach(([i,s])=>{this.compareList.set(i,s)}):localStorage.removeItem(this.options.storageKey)}}catch(e){console.warn("Failed to load comparison:",e)}}showNotification(e,t="info"){if(!this.options.enableNotifications)return;const i=document.createElement("div");i.className=`fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 rounded-lg shadow-lg transition-all duration-300 ${t==="success"?"bg-green-500 text-white":t==="warning"?"bg-yellow-500 text-white":t==="error"?"bg-red-500 text-white":"bg-blue-500 text-white"}`,i.textContent=e,document.body.appendChild(i),setTimeout(()=>{i.style.opacity="0",setTimeout(()=>document.body.removeChild(i),300)},3e3)}getCompareList(){return Array.from(this.compareList.values())}getCompareCount(){return this.compareList.size}isInComparison(e){return this.compareList.has(e)}destroy(){this.compareList.clear(),this.compareModal&&this.compareModal.remove();const e=document.getElementById("compare-tickets-btn");e&&e.remove(),document.querySelectorAll(".compare-indicator").forEach(t=>{t.remove()})}}window.TicketComparison=y;document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{window.ticketComparison=new y}):window.ticketComparison=new y;const T=Object.freeze(Object.defineProperty({__proto__:null},Symbol.toStringTag,{value:"Module"}));export{b as _,M as i};

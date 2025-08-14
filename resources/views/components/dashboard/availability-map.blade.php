@props([
    'venue' => null,
    'sections' => [],
    'isLoading' => false,
    'title' => 'Seat Availability',
    'showLegend' => true,
    'interactive' => true
])

<div class="availability-map-widget bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-500 to-blue-600 text-white">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
        </div>
        
        @if($venue)
            <div class="text-right">
                <div class="text-sm font-medium">{{ $venue['name'] ?? 'Stadium' }}</div>
                <div class="text-xs opacity-80">{{ $venue['capacity'] ?? 'N/A' }} seats</div>
            </div>
        @endif
    </div>

    @if($isLoading)
        <!-- Loading State -->
        <div class="p-6">
            <div class="flex flex-col items-center justify-center space-y-4" style="height: 300px;">
                <div class="loading-shimmer w-48 h-48 rounded-lg"></div>
                <div class="loading-shimmer w-32 h-4 rounded"></div>
                <div class="flex space-x-2">
                    <div class="loading-shimmer w-16 h-6 rounded"></div>
                    <div class="loading-shimmer w-16 h-6 rounded"></div>
                    <div class="loading-shimmer w-16 h-6 rounded"></div>
                </div>
            </div>
        </div>
    @else
        <div class="p-6">
            <!-- Stadium Map -->
            <div class="stadium-map-container relative mb-6" style="height: 400px;">
                <svg class="w-full h-full" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                    <!-- Stadium Bowl Background -->
                    <defs>
                        <radialGradient id="stadiumGradient" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" style="stop-color:#f3f4f6;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#e5e7eb;stop-opacity:1" />
                        </radialGradient>
                        
                        <!-- Section availability gradients -->
                        <linearGradient id="availableGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
                        </linearGradient>
                        
                        <linearGradient id="limitedGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
                        </linearGradient>
                        
                        <linearGradient id="soldOutGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
                        </linearGradient>
                    </defs>

                    <!-- Stadium Field/Court -->
                    <rect x="120" y="120" width="160" height="60" rx="8" fill="#22c55e" stroke="#16a34a" stroke-width="2"/>
                    <text x="200" y="155" text-anchor="middle" fill="white" font-size="12" font-weight="bold">FIELD</text>

                    @if(count($sections) > 0)
                        @foreach($sections as $index => $section)
                            @php
                                $sectionId = $section['id'] ?? "section-{$index}";
                                $availability = $section['availability'] ?? 0;
                                $sectionName = $section['name'] ?? "Section " . ($index + 1);
                                $price = $section['price'] ?? 0;
                                
                                // Determine section position based on index (simplified stadium layout)
                                $positions = [
                                    // Lower Bowl
                                    ['x' => 50, 'y' => 50, 'width' => 80, 'height' => 40],   // Section 1 - Upper Left
                                    ['x' => 270, 'y' => 50, 'width' => 80, 'height' => 40],  // Section 2 - Upper Right
                                    ['x' => 50, 'y' => 210, 'width' => 80, 'height' => 40],  // Section 3 - Lower Left
                                    ['x' => 270, 'y' => 210, 'width' => 80, 'height' => 40], // Section 4 - Lower Right
                                    
                                    // Upper Deck
                                    ['x' => 20, 'y' => 20, 'width' => 60, 'height' => 30],   // Section 5 - Upper Deck Left
                                    ['x' => 320, 'y' => 20, 'width' => 60, 'height' => 30],  // Section 6 - Upper Deck Right
                                    ['x' => 20, 'y' => 250, 'width' => 60, 'height' => 30],  // Section 7 - Upper Deck Left
                                    ['x' => 320, 'y' => 250, 'width' => 60, 'height' => 30], // Section 8 - Upper Deck Right
                                    
                                    // Side Sections
                                    ['x' => 140, 'y' => 20, 'width' => 120, 'height' => 25], // Section 9 - Upper Center
                                    ['x' => 140, 'y' => 255, 'width' => 120, 'height' => 25], // Section 10 - Lower Center
                                ];
                                
                                $position = $positions[$index] ?? $positions[0];
                                
                                // Color based on availability
                                if ($availability >= 70) {
                                    $fillColor = 'url(#availableGradient)';
                                    $statusClass = 'available';
                                } elseif ($availability >= 30) {
                                    $fillColor = 'url(#limitedGradient)';
                                    $statusClass = 'limited';
                                } else {
                                    $fillColor = 'url(#soldOutGradient)';
                                    $statusClass = 'sold-out';
                                }
                            @endphp

                            <!-- Section Rectangle -->
                            <rect x="{{ $position['x'] }}" 
                                  y="{{ $position['y'] }}" 
                                  width="{{ $position['width'] }}" 
                                  height="{{ $position['height'] }}" 
                                  rx="4" 
                                  fill="{{ $fillColor }}"
                                  stroke="#374151" 
                                  stroke-width="1"
                                  class="stadium-section {{ $statusClass }} {{ $interactive ? 'cursor-pointer hover:opacity-80' : '' }} transition-all"
                                  data-section-id="{{ $sectionId }}"
                                  data-section-name="{{ $sectionName }}"
                                  data-availability="{{ $availability }}"
                                  data-price="{{ $price }}">
                            </rect>

                            <!-- Section Label -->
                            <text x="{{ $position['x'] + $position['width']/2 }}" 
                                  y="{{ $position['y'] + $position['height']/2 + 3 }}" 
                                  text-anchor="middle" 
                                  fill="white" 
                                  font-size="10" 
                                  font-weight="bold"
                                  class="pointer-events-none">
                                {{ $sectionName }}
                            </text>

                            <!-- Availability Percentage -->
                            <text x="{{ $position['x'] + $position['width']/2 }}" 
                                  y="{{ $position['y'] + $position['height']/2 + 15 }}" 
                                  text-anchor="middle" 
                                  fill="white" 
                                  font-size="8"
                                  class="pointer-events-none">
                                {{ $availability }}%
                            </text>
                        @endforeach
                    @else
                        <!-- Default stadium sections when no data -->
                        @for($i = 0; $i < 8; $i++)
                            @php
                                $positions = [
                                    ['x' => 50, 'y' => 50, 'width' => 80, 'height' => 40],
                                    ['x' => 270, 'y' => 50, 'width' => 80, 'height' => 40],
                                    ['x' => 50, 'y' => 210, 'width' => 80, 'height' => 40],
                                    ['x' => 270, 'y' => 210, 'width' => 80, 'height' => 40],
                                    ['x' => 20, 'y' => 20, 'width' => 60, 'height' => 30],
                                    ['x' => 320, 'y' => 20, 'width' => 60, 'height' => 30],
                                    ['x' => 20, 'y' => 250, 'width' => 60, 'height' => 30],
                                    ['x' => 320, 'y' => 250, 'width' => 60, 'height' => 30],
                                ];
                                $position = $positions[$i];
                            @endphp
                            
                            <rect x="{{ $position['x'] }}" 
                                  y="{{ $position['y'] }}" 
                                  width="{{ $position['width'] }}" 
                                  height="{{ $position['height'] }}" 
                                  rx="4" 
                                  fill="#9ca3af" 
                                  stroke="#6b7280" 
                                  stroke-width="1">
                            </rect>
                            <text x="{{ $position['x'] + $position['width']/2 }}" 
                                  y="{{ $position['y'] + $position['height']/2 + 3 }}" 
                                  text-anchor="middle" 
                                  fill="white" 
                                  font-size="10" 
                                  font-weight="bold">
                                Section {{ $i + 1 }}
                            </text>
                            <text x="{{ $position['x'] + $position['width']/2 }}" 
                                  y="{{ $position['y'] + $position['height']/2 + 15 }}" 
                                  text-anchor="middle" 
                                  fill="white" 
                                  font-size="8">
                                N/A
                            </text>
                        @endfor
                    @endif
                </svg>

                <!-- Section Tooltip -->
                <div id="section-tooltip" class="absolute bg-gray-800 text-white text-sm rounded-lg px-3 py-2 pointer-events-none opacity-0 transition-opacity z-10">
                    <div class="font-semibold" id="tooltip-section-name"></div>
                    <div class="text-xs" id="tooltip-availability"></div>
                    <div class="text-xs" id="tooltip-price"></div>
                </div>
            </div>

            @if($showLegend)
                <!-- Legend -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Availability Legend</h4>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded bg-gradient-to-br from-green-500 to-green-600"></div>
                            <span class="text-gray-700">Available (70%+)</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded bg-gradient-to-br from-yellow-500 to-yellow-600"></div>
                            <span class="text-gray-700">Limited (30-69%)</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded bg-gradient-to-br from-red-500 to-red-600"></div>
                            <span class="text-gray-700">Sold Out (0-29%)</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded bg-gray-400"></div>
                            <span class="text-gray-700">No Data</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Summary Stats -->
            @if(count($sections) > 0)
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ collect($sections)->where('availability', '>=', 70)->count() }}
                            </div>
                            <div class="text-xs text-gray-500">Available Sections</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ collect($sections)->where('availability', '>=', 30)->where('availability', '<', 70)->count() }}
                            </div>
                            <div class="text-xs text-gray-500">Limited Sections</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600">
                                {{ collect($sections)->where('availability', '<', 30)->count() }}
                            </div>
                            <div class="text-xs text-gray-500">Sold Out Sections</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">
                                ${{ number_format(collect($sections)->avg('price') ?? 0, 0) }}
                            </div>
                            <div class="text-xs text-gray-500">Average Price</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Footer -->
    @if(!$isLoading && count($sections) > 0)
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">
                    Last updated: <span class="font-medium">{{ now()->format('g:i A') }}</span>
                </span>
                <button class="text-purple-600 hover:text-purple-800 font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    View Full Map
                </button>
            </div>
        </div>
    @endif
</div>

@if($interactive && !$isLoading)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.stadium-section');
    const tooltip = document.getElementById('section-tooltip');
    const tooltipSectionName = document.getElementById('tooltip-section-name');
    const tooltipAvailability = document.getElementById('tooltip-availability');
    const tooltipPrice = document.getElementById('tooltip-price');

    sections.forEach(section => {
        section.addEventListener('mouseenter', function(e) {
            const sectionName = this.dataset.sectionName;
            const availability = this.dataset.availability;
            const price = this.dataset.price;
            
            tooltipSectionName.textContent = sectionName;
            tooltipAvailability.textContent = `${availability}% Available`;
            tooltipPrice.textContent = price > 0 ? `From $${parseFloat(price).toFixed(2)}` : 'Price TBD';
            
            tooltip.style.opacity = '1';
        });
        
        section.addEventListener('mousemove', function(e) {
            const rect = document.querySelector('.stadium-map-container').getBoundingClientRect();
            tooltip.style.left = (e.clientX - rect.left + 10) + 'px';
            tooltip.style.top = (e.clientY - rect.top - 10) + 'px';
        });
        
        section.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
        });
        
        section.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;
            const sectionName = this.dataset.sectionName;
            const availability = this.dataset.availability;
            
            // Flash the section
            this.style.opacity = '0.6';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 200);
            
            // In a real implementation, this would open a detailed view or booking interface
            console.log(`Selected section: ${sectionName} (${availability}% available)`);
        });
    });
});
</script>
@endif

<style>
.loading-shimmer {
    background: linear-gradient(90deg, var(--gray-200) 0px, var(--gray-100) 40px, var(--gray-200) 80px);
    background-size: 200px;
    animation: shimmer 1.5s infinite;
}

.stadium-section {
    transition: all 0.2s ease-in-out;
}

.stadium-section:hover {
    transform: scale(1.05);
    filter: brightness(1.1);
}

.stadium-section.available:hover {
    filter: brightness(1.2);
}

.stadium-section.limited:hover {
    filter: brightness(1.2);
}

.stadium-section.sold-out:hover {
    filter: brightness(1.1);
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .stadium-map-container {
        height: 250px !important;
    }
    
    .stadium-map-container text {
        font-size: 8px !important;
    }
    
    .stadium-map-container text:last-child {
        font-size: 6px !important;
    }
}
</style>

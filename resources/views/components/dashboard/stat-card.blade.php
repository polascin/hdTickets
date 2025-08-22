@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'neutral', // positive, negative, neutral
    'icon' => null,
    'color' => 'primary', // primary, secondary, success, warning, error, info
    'size' => 'default', // sm, default, lg
    'loading' => false,
    'href' => null,
    'description' => null,
    'trend' => null, // array of values for sparkline
])

@php
    $cardId = 'stat-card-' . uniqid();
    
    // Card classes using design system
    $cardClasses = collect([
        'hd-stat-card',
        "hd-stat-card--{$color}",
        "hd-stat-card--{$size}",
        $loading ? 'hd-stat-card--loading' : '',
        $href ? 'hd-stat-card--interactive' : ''
    ])->filter()->implode(' ');
    
    // Change indicator classes
    $changeClasses = collect([
        'hd-stat-card__change',
        "hd-stat-card__change--{$changeType}"
    ])->filter()->implode(' ');
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $cardClasses }}" id="{{ $cardId }}">
@else
    <div class="{{ $cardClasses }}" id="{{ $cardId }}">
@endif
    @if($loading)
        <!-- Loading State -->
        <div class="hd-stat-card__loading">
            <div class="hd-stat-card__icon-skeleton"></div>
            <div class="hd-stat-card__content-skeleton">
                <div class="hd-stat-card__title-skeleton"></div>
                <div class="hd-stat-card__value-skeleton"></div>
                <div class="hd-stat-card__description-skeleton"></div>
            </div>
        </div>
    @else
        <!-- Card Header -->
        <div class="hd-stat-card__header">
            @if($icon)
                <div class="hd-stat-card__icon">
                    @if(is_string($icon))
                        <i class="{{ $icon }}" aria-hidden="true"></i>
                    @else
                        {!! $icon !!}
                    @endif
                </div>
            @endif
            
            <div class="hd-stat-card__meta">
                @if($change !== null)
                    <div class="{{ $changeClasses }}">
                        @if($changeType === 'positive')
                            <svg class="hd-stat-card__change-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L10 4.414 4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($changeType === 'negative')
                            <svg class="hd-stat-card__change-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L10 15.586l5.293-5.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        <span class="hd-stat-card__change-value">{{ is_numeric($change) ? number_format(abs($change), 1) . '%' : $change }}</span>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Card Content -->
        <div class="hd-stat-card__content">
            <div class="hd-stat-card__title">{{ $title }}</div>
            
            <div class="hd-stat-card__value" 
                 @if(is_numeric(str_replace(['$', ',', '%'], '', $value))) 
                     data-counter="{{ str_replace(['$', ',', '%'], '', $value) }}" 
                 @endif>
                {{ $value }}
            </div>
            
            @if($description)
                <div class="hd-stat-card__description">{{ $description }}</div>
            @endif
        </div>
        
        <!-- Trend Chart (if provided) -->
        @if($trend && is_array($trend))
            <div class="hd-stat-card__trend">
                <svg class="hd-stat-card__sparkline" viewBox="0 0 100 20" preserveAspectRatio="none">
                    @php
                        $max = max($trend);
                        $min = min($trend);
                        $range = $max - $min;
                        $points = [];
                        
                        foreach ($trend as $index => $value) {
                            $x = ($index / (count($trend) - 1)) * 100;
                            $y = $range > 0 ? 20 - (($value - $min) / $range) * 20 : 10;
                            $points[] = "$x,$y";
                        }
                        
                        $pathData = 'M' . implode(' L', $points);
                    @endphp
                    
                    <path 
                        d="{{ $pathData }}" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="1.5"
                        vector-effect="non-scaling-stroke"
                    />
                    
                    <!-- Optional: Add dots for each point -->
                    @foreach($trend as $index => $value)
                        @php
                            $x = ($index / (count($trend) - 1)) * 100;
                            $y = $range > 0 ? 20 - (($value - $min) / $range) * 20 : 10;
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="1" fill="currentColor"/>
                    @endforeach
                </svg>
            </div>
        @endif
        
        <!-- Interactive indicator -->
        @if($href)
            <div class="hd-stat-card__action">
                <svg class="hd-stat-card__action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        @endif
    @endif
    
@if($href)
    </a>
@else
    </div>
@endif

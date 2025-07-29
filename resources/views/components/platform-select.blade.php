{{--
    HD Tickets Platform Select Component
    @author Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
    @version 2025.07.v4.0
--}}

@props([
    'name' => 'platform',
    'id' => null,
    'value' => '',
    'showAll' => true,
    'allText' => 'All Platforms',
    'class' => 'form-select',
    'required' => false,
    'disabled' => false,
    'includeOnly' => [], // Only include specific platforms
    'excludePlatforms' => [], // Exclude specific platforms
])

@php
    $platforms = collect(config('platforms.display_order'))
        ->sortBy('order');
    
    // Filter platforms if includeOnly is specified
    if (!empty($includeOnly)) {
        $platforms = $platforms->filter(function($platform) use ($includeOnly) {
            return in_array($platform['key'], $includeOnly);
        });
    }
    
    // Exclude specific platforms if specified
    if (!empty($excludePlatforms)) {
        $platforms = $platforms->filter(function($platform) use ($excludePlatforms) {
            return !in_array($platform['key'], $excludePlatforms);
        });
    }
    
    $selectId = $id ?? $name;
@endphp

<select 
    name="{{ $name }}" 
    id="{{ $selectId }}" 
    class="{{ $class }}"
    @if($required) required @endif
    @if($disabled) disabled @endif
    {{ $attributes }}
>
    @if($showAll)
        <option value="">{{ $allText }}</option>
    @endif
    
    @foreach($platforms as $platform)
        <option 
            value="{{ $platform['key'] }}" 
            @if($value === $platform['key']) selected @endif
        >
            {{ $platform['display_name'] }}
        </option>
    @endforeach
</select>

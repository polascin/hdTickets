@props([
    'score' => 0,
    'minExcellent' => 80,
    'minGood' => 60,
    'improveRoute' => null, // route name for improvement CTA
])

@php
  $badgeColor = $score >= $minExcellent ? 'success' : ($score >= $minGood ? 'warning' : 'danger');
  $statusLabel = $score >= $minExcellent ? 'Excellent' : ($score >= $minGood ? 'Good' : 'Needs Improvement');
  $circumference = 2 * pi() * 40; // r=40 matches original ring
@endphp

<article class="card stats-card h-100 border-0 shadow-sm" x-data="{ animateSecurity: false }" x-intersect="animateSecurity = true">
  <div class="card-body text-center">
    <h3 class="card-title text-muted mb-3">
      <i class="fas fa-shield-alt text-success me-2" aria-hidden="true"></i>
      Security Score
    </h3>

    <!-- Progress Ring -->
    <div class="position-relative d-inline-block mb-3" role="progressbar"
      :aria-valuenow="animateSecurity ? {{ $score }} : 0" aria-valuemin="0" aria-valuemax="100"
      :aria-label="`Security score: ${animateSecurity ? {{ $score }} : 0} out of 100`">
      <svg class="progress-ring" width="100" height="100" aria-hidden="true">
        <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8" fill="transparent" />
        <circle cx="50" cy="50" r="40" stroke="#10b981" stroke-width="8" fill="transparent"
          stroke-dasharray="{{ $circumference }}"
          :stroke-dashoffset="animateSecurity ? {{ $circumference * (1 - $score / 100) }} : {{ $circumference }}"
          stroke-linecap="round" transform="rotate(-90 50 50)" style="transition: stroke-dashoffset 2s ease-in-out" />
      </svg>
      <div class="position-absolute top-50 start-50 translate-middle">
        <span class="h4 fw-bold text-success progress-text"
          x-text="animateSecurity ? '{{ $score }}' : '0'">{{ $score }}</span>
      </div>
    </div>

    <p class="text-muted mb-2">Account Security Level</p>

    <span class="badge bg-{{ $badgeColor }}" role="status">{{ $statusLabel }}</span>

    @if ($score < $minExcellent && $improveRoute)
      <div class="mt-3">
        <a href="{{ route($improveRoute) }}" class="btn btn-sm btn-outline-success"
          aria-label="Improve your security settings">
          <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
          Improve Security
        </a>
      </div>
    @endif
  </div>
</article>

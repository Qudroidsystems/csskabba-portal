{{--
    resources/views/studentreports/partials/promotion_badge.blade.php

    Usage in studentresult.blade.php:
        @include('studentreports.partials.promotion_badge', ['promotionResult' => $promotion_result])

    Usage in class_results_pdf.blade.php (inside the per-student loop):
        @include('studentreports.partials.promotion_badge', ['promotionResult' => $studentData['promotion_result'] ?? [], 'isPdf' => true])
--}}

@php
    $pr          = $promotionResult ?? [];
    $status      = $pr['status']              ?? 'awaiting';
    $isPromoTerm = $pr['is_promotional_term'] ?? false;
    $failed      = $pr['failed_compulsory']   ?? [];
    $avgFailed   = $pr['average_failed']      ?? false;
    $reqAvg      = $pr['required_average']    ?? null;
    $actAvg      = $pr['actual_average']      ?? null;
    $total       = $pr['compulsory_count']    ?? 0;
    $passed      = $pr['passed_compulsory']   ?? 0;
    $isPdf       = $isPdf ?? false;
@endphp

@if (!$isPromoTerm)
    {{-- Non-promotional term: subtle informational chip --}}
    <div class="{{ $isPdf ? 'promo-badge promo-awaiting-pdf' : 'promo-badge promo-awaiting' }}">
        <span class="promo-icon">⏳</span>
        <span class="promo-label">Awaiting Final Term</span>
        <span class="promo-sub">Promotion assessed at end of academic year</span>
    </div>
@elseif ($status === 'promoted')
    <div class="{{ $isPdf ? 'promo-badge promo-promoted-pdf' : 'promo-badge promo-promoted' }}">
        <span class="promo-icon">🎓</span>
        <span class="promo-label">PROMOTED</span>
        @if ($total > 0)
            <span class="promo-sub">Passed {{ $passed }}/{{ $total }} compulsory subject(s)</span>
        @endif
        @if ($reqAvg !== null && $actAvg !== null)
            <span class="promo-sub">Overall average: {{ number_format($actAvg, 1) }}% (required {{ number_format($reqAvg, 1) }}%)</span>
        @endif
    </div>
@else
    {{-- Repeated --}}
    <div class="{{ $isPdf ? 'promo-badge promo-repeated-pdf' : 'promo-badge promo-repeated' }}">
        <span class="promo-icon">⚠️</span>
        <span class="promo-label">NOT PROMOTED</span>

        @if (!empty($failed))
            <span class="promo-sub">
                Failed compulsory: {{ collect($failed)->pluck('subject')->filter()->implode(', ') ?: count($failed) . ' subject(s)' }}
            </span>
        @endif

        @if ($avgFailed && $reqAvg !== null && $actAvg !== null)
            <span class="promo-sub">
                Average {{ number_format($actAvg, 1) }}% below required {{ number_format($reqAvg, 1) }}%
            </span>
        @endif
    </div>
@endif

{{-- ── Styles (injected once; harmless to repeat in Blade, stripped in PDF) ── --}}
@if (!$isPdf)
<style>
.promo-badge {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    border-radius: 12px;
    padding: 12px 18px;
    margin: 12px 0;
    font-family: inherit;
    max-width: 480px;
}
.promo-icon  { font-size: 22px; line-height: 1; }
.promo-label { font-size: 15px; font-weight: 800; letter-spacing: .5px; }
.promo-sub   { font-size: 12px; opacity: .85; }

.promo-promoted {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 2px solid #16a34a;
    color: #14532d;
    box-shadow: 0 2px 10px rgba(22,163,74,.15);
}
.promo-repeated {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 2px solid #dc2626;
    color: #7f1d1d;
    box-shadow: 0 2px 10px rgba(220,38,38,.15);
}
.promo-awaiting {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 2px solid #94a3b8;
    color: #475569;
}
</style>
@endif

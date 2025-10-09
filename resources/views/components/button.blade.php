@php
    $isIcon = isset($icon);
    $isDisabled = isset($disabled) && $disabled;
    $baseClass = $isIcon ? 'btn btn-icon' : 'btn btn-sm';
    $finalClass = $baseClass . ($isDisabled ? ' btn-disabled' : '');

    // Ambil style existing jika ada, lalu merge dengan width jika diberikan
    $customStyle = $attributes->get('style');
    $widthStyle = isset($width) ? "width: $width;" : '';
    $mergedStyle = trim($customStyle . ' ' . $widthStyle);

    // Ambil alignment jika dikirim (misal: text-start, text-center, text-end, d-flex justify-content-center, etc.)
    $alignClass = $align ?? '';
@endphp

<div class="{{ $alignClass }}">
    @if (isset($url))
        <a
            {!! $attributes->merge([
                'class' => $finalClass,
                'style' => $mergedStyle
            ]) !!}
            href="{{ $url }}"
            @if($isDisabled) aria-disabled="true" tabindex="-1" @endif
        >
            @if($icon)
                <span class="tf-icons bx {{ $icon }} bx-flashing-hover"></span>
            @endif
            <span @if ($attributes->has('id')) id="{{ $attributes->get('id') }}-label" @endif>{{ $label }}</span>
        </a>
    @else
        <button
            type="button"
            {!! $attributes->merge([
                'class' => $finalClass,
                'style' => $mergedStyle
            ]) !!}
            @if($isDisabled) disabled @endif
        >
            @if($icon)
                <span class="tf-icons bx {{ $icon }} bx-flashing-hover"></span>
            @endif
            <span @if ($attributes->has('id')) id="{{ $attributes->get('id') }}-label" @endif>{{ $label }}</span>
        </button>
    @endif
</div>

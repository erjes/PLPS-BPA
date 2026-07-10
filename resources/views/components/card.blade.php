<div {{ $attributes->merge(['class' => 'card']) }}>
    @if(isset($title))
        <div class="chart-title">
            @if(isset($icon))
                <i class="{{ $icon }}" style="color:#7B1113"></i>
            @endif
            {{ $title }}
        </div>
    @endif
    {{ $slot }}
</div>

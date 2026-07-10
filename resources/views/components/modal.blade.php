@props([
    'id',
    'title' => null,
    'icon' => null,
    'show' => false,
    'maxWidth' => '520px',
    'titleColor' => '#1e293b',
])

<div class="edit-modal-overlay {{ $show ? 'show' : '' }}" id="{{ $id }}" onmousedown="if(event.target===this)this.dataset.close='1';" onmouseup="if(this.dataset.close==='1' && event.target===this){ document.getElementById('{{ $id }}').classList.remove('show'); } this.dataset.close='0';">
    <div class="edit-modal" style="max-width: {{ $maxWidth }}">
        @if(isset($header))
            {{ $header }}
        @else
            <div class="edit-modal-header">
                <h3 style="color: {{ $titleColor }}">
                    @if($icon)
                        <i class="{{ $icon }}" style="color:#7B1113"></i>
                    @endif
                    {{ $title }}
                </h3>
                <button class="edit-modal-close" onclick="document.getElementById('{{ $id }}').classList.remove('show')">&times;</button>
            </div>
        @endif
        
        <div class="edit-modal-body">
            {{ $slot }}
        </div>
        
        @if(isset($footer))
            <div class="edit-modal-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>

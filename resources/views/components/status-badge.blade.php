<div class="badge badge-{{ $color }}">
    @if(function_exists('svg'))
        {!! svg($icon, 'w-[10px] h-[10px]')->toHtml() !!}
    @endif
    {{ $label }}
</div>

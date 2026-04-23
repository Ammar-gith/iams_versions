{{-- Breadcrumb Navigation --}}
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach ($items as $item)
            @if ($loop->first)
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] }}" class="custom-tooltip">
                        {!! $item['label'] !!}
                        <span class="tooltip-text">View Dashboard</span>
                    </a>
                </li>
            @elseif ($loop->last)
                <li class="breadcrumb-item active" aria-current="page">{!! $item['label'] !!}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] }}">{!! $item['label'] !!}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>

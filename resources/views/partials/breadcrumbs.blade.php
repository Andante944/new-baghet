<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol>
        @foreach($breadcrumbs as $i => $crumb)
            <li>
                @if($i < count($breadcrumbs) - 1)
                    <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                @else
                    <span aria-current="page">{{ $crumb['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

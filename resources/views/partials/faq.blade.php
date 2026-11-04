@php
    $faqItems = $faq ?? [];
@endphp

@if($faqItems !== [])
    <div class="faq-list">
        @foreach($faqItems as $index => $item)
            <details class="faq-item" @if($index === 0) open @endif>
                <summary>{{ $item['q'] }}</summary>
                <p>{{ $item['a'] }}</p>
            </details>
        @endforeach
    </div>
@endif

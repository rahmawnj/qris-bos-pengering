@props(['items' => [], 'title' => '', 'subtitle' => ''])

<ol class="breadcrumb">
    @foreach ($items as $index => $item)
        @if ($index !== array_key_last($items))
            <li class="breadcrumb-item">{{ $item }}</li>
        @else
            <li class="breadcrumb-item active">{{ $item }}</li>
        @endif
    @endforeach
</ol>

<h1 class="page-header">
    {{ $title }} <small>{{ $subtitle }}</small>
</h1>

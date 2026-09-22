@extends('workbench::layout')

@section('content')
    <div class="card">
        <livewire:advanced-filters::panel :model="\Workbench\App\Models\Product::class" />
    </div>

    <livewire:product-results />
@endsection

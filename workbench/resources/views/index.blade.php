@extends('workbench::layout')

@section('content')
    <div class="card">
        <x-advanced-filters::panel
            :fields="$filterFields"
            :active="$activeFilters"
            :base-url="route('products.index')"
        />
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th><th>SKU</th><th>Category</th><th>Status</th>
                <th>Price</th><th>Stock</th><th>Released</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->sku }}</td>
                    <td>{{ $p->category }}</td>
                    <td>{{ $p->status }}</td>
                    <td>{{ number_format($p->price, 2) }}</td>
                    <td>{{ $p->stock }}</td>
                    <td>{{ $p->released_at }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#94a3b8">No products match the current filters.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $products->links() }}</div>
    <p style="color:#94a3b8;font-size:.8125rem">Total matched: {{ $products->total() }}</p>
@endsection

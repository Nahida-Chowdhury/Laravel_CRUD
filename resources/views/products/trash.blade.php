<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trash Bin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Trash Bin</h2>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Products</a>
        </div>
        
        @if (Session::has('success'))
            <div class="alert alert-success mt-3">{{ Session::get('success') }}</div>
        @endif

        <table class="table mt-3 table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>${{ $product->price }}</td>
                    <td>
                        <form action="{{ route('products.restore', $product->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Restore</button>
                        </form>

                        <form action="{{ route('products.forceDelete', $product->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete Permanently</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Trash is empty.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
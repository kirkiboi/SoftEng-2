@extends('main')
@section('mp', 'System 4')
@section('content')
@if (session('success'))
    <div class="my-alert alert-success">
        {{ session('success') }}
    </div>
@endif
@vite(['resources/css/mp.css'])
@vite(['resources/js/mp.js'])
    <div class="menu-pricing-parent-container">
        <div class="header-container">
            <div class="controls-container">
                <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                    <button class="filter-option" data-category="all">All</button>
                    <button class="filter-option" data-category="drinks">Drinks</button>
                    <button class="filter-option" data-category="snacks">Snacks</button>
                    <button class="filter-option" data-category="meals">Meals</button>
                </div>
                <form method="GET" action="{{ route('mp') }}">
                    <input 
                        type="text"
                        name="search"
                        class="search-input"
                        placeholder="Search"
                        value="{{ request('search') }}"
                    >
                </form>
                <div class="pagination-container">
                    {{ $products->links() }}
                </div>
            </div>
            <div class="add-item-container">
                <button class="add-item-button">+ Add Item</button>
            </div>
        </div>
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 50%">
                    <col style="width: 15%">
                    <col style="width: 15%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                </colgroup>
                <thead>
                    <tr class="">
                        <th class="th">Item Name</th>
                        <th class="th">Category</th>
                        <th class="th">Price</th>
                        <th class="th">Edit</th>
                        <th class="th">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="tr" data-category="{{ $product->category }}">
                            <td>
                                <div class="product-name-and-image">
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                        alt="{{ $product->name }}"
                                        class="product-image">
                                    <span>{{ $product->name }}</span>
                                </div>
                            </td>
                            <td>{{ ucfirst($product->category) }}</td>
                            <td>₱ {{ number_format($product->price, 2) }}</td>
                            <td>
                                <a href="{{ route('editMP', $product->id) }}">Edit</a>
                            </td>
                            <td>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="floating-add-item-container">
        <div class="floating-add-item">
            <span>Add Item</span>
        </div>
        <form method="POST"
            action="{{ route('products.store') }}"
            enctype="multipart/form-data"
            class="POST-class">
            @csrf
            <div class="floating-add-item-name-container">
                <label for="name">Item Name</label>
                <input type="text" class="input" name="name">
            </div>
            <div class="floating-add-item-category-container">
                <span>Item Category</span>
                <div class="category-input-wrapper">
                    <input type="radio" id="drinks" name="category" value="drinks" checked>
                    <label for="drinks">Drinks</label>
                    <input type="radio" id="snacks" name="category" value="snacks">
                    <label for="snacks">Snacks</label>
                    <input type="radio" id="meals" name="category" value="meals">
                    <label for="meals">Meals</label>
                </div>
            </div>
            <div class="floating-add-item-item-price-container">
                <label for="price">Price</label>
                <input type="text" name="price" class="input" placeholder="₱ 00.00">
            </div>
            <div class="floating-add-item-image-container">
                <label>Image</label>
                <div class="file-upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6h.1a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div class="upload-text">Click or drag file to this area to upload</div>
                    <div class="file-name" id="fileName"></div>
                    <input type="file" id="fileInput" name="image" accept="image/*">
                </div>
                <input type="file" id="fileInput" accept="image/*">
                <button type="button" class="choose-file-btn" id="chooseFileBtn">Choose File</button>
            </div>
            <div class="floating-add-item-options">
                <button type="button" class="cancel-button">Cancel</button>
                <button type="submit" class="add-button">Add</button>
            </div>
        </form>
    </div>
@endsection
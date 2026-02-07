@extends('main')
@section('menu and pricing system', 'System 4')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @if (session('success'))
        <div class="my-alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @vite(['resources/css/mp.css'])
    @vite(['resources/js/mp.js'])
    <div class="menu-pricing-parent-container">

        <div class="table-container">

            <div class="header-container">
                <div class="controls-container">
                    <div id="filter-button" class="filter-icon-container">
                        <i class="bi bi-funnel default-icon"></i>
                        <i class="bi bi-x-lg active-icon" style="display: none !important;"></i>
                    </div>
                    <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                        <button class="filter-option" data-category="all">All</button>
                        <button class="filter-option" data-category="drinks">Drinks</button>
                        <button class="filter-option" data-category="snacks">Snacks</button>
                        <button class="filter-option" data-category="meals">Meals</button>
                    </div>
                    <form method="GET" action="{{ route('mp') }}">
                        <input type="text" name="search" class="search-input" placeholder="Search"
                            value="{{ request('search') }}">
                    </form>
                    <div class="pagination-container">
                        {{ $products->links() }}
                    </div>
                    <div class="add-item-container">
                        <button class="add-item-button">+ Add Item</button>
                    </div>
                </div>
            </div>
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
                        <th class="th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="tr" data-category="{{ $product->category }}">
                            <td>
                                <div class="product-name-and-image">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                        class="product-image">
                                    <span>{{ $product->name }}</span>
                                </div>
                            </td>
                            <td>{{ ucfirst($product->category) }}</td>
                            <td>₱ {{ number_format($product->price, 2) }}</td>
                            <td class="td-actions">
                                <button class="edit-button" data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                    data-category="{{ $product->category }}" data-price="{{ $product->price }}"
                                    data-image="{{ asset('storage/' . $product->image) }}">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button"><i class="fa-solid fa-trash"></i></button>
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
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="POST-class">
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
                <input type="number" name="price" class="input" placeholder="₱ 00.00" step="0.01">
            </div>
            <div class="floating-add-item-image-container">
                <div class="image-wrapper">
                    <label>Image</label>
                    <div class="file-upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6h.1a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                        </div>
                        <div class="upload-text">Drag file to this area to upload or click "Choose File" button</div>
                        <div class="file-name" id="fileName"></div>
                    </div>
                </div>
                <input class="choose-file-button" type="file" id="fileInput" name="image" accept="image/*">
            </div>
            <div class="floating-add-item-options">
                <button type="button" class="cancel-button">Cancel</button>
                <button type="submit" class="add-button">Add</button>
            </div>
        </form>
    </div>

    <div class="floating-edit-item-container">
        <div class="floating-edit-item">
            <span>Update Item</span>
        </div>
        <form class="floating-edit-item-form" method="POST" id="productForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="_method" id="formMethod" value="PUT">
            <div class="floating-edit-item-name-container">
                <label for="name">Item Name</label>
                <input type="text" class="input" name="name" id="editName">
            </div>
            <div class="floating-edit-item-category-container">
                <span>Item Category</span>
                <div class="category-input-wrapper">
                    <input type="radio" id="editDrinks" name="category" value="drinks">
                    <label for="editDrinks">Drinks</label>
                    <input type="radio" id="editSnacks" name="category" value="snacks">
                    <label for="editSnacks">Snacks</label>
                    <input type="radio" id="editMeals" name="category" value="meals">
                    <label for="editMeals">Meals</label>
                </div>
            </div>
            <div class="floating-edit-item-item-price-container">
                <label for="price">Price</label>
                <input type="number" name="price" class="input" id="editPrice" placeholder="₱ 00.00" step="0.01">
            </div>
            <div class="floating-add-item-image-container">
                <div class="image-wrapper">
                    <label>Image</label>
                    <div class="file-upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6h.1a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                        </div>
                        <div class="upload-text">Drag file to this area to upload or click "Choose File" button</div>
                        <div class="file-name" id="fileName"></div>
                    </div>
                </div>
                <input class="choose-file-button" type="file" id="fileInput" name="image" accept="image/*">
            </div>
            <div class="floating-edit-item-options">
                <button type="button" class="cancel-button">Cancel</button>
                <button type="submit" class="save-button">Save</button>
            </div>
        </form>
    </div>

    <div class="floating-delete-item-container" id="deleteModal" style="display: none;">
        <div class="floating-delete-item-container-wrapper">
            <div class="remove-item-header">
                <h2>Remove Item</h2>
            </div>
            <div class="floating-delete-item">
                <p class="delete-message">Are you sure you want to delete this item? If you delete, it will be permanently
                    lost.</p>
            </div>
            <div class="floating-delete-item-options">
                <button type="button" id="cancelDelete" class="cancel-button">Cancel</button>
                <button type="button" id="confirmDelete" class="delete-confirm-button">Delete</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>
@endsection
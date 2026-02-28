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
        <!-- WHERE CONTROLS LAYER START -->
        <div class="header-container">
            <div class="controls-container">
                <div id="filter-button" class="filter-icon-container">
                    <i class="bi bi-funnel default-icon"></i>
                    <i class="bi bi-x-lg active-icon" style="display: none !important;"></i>
                </div>
                <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                    <a href="{{ route('mp', ['category' => 'all', 'search' => request('search')]) }}" class="filter-option" style="display:block; text-decoration:none; color:inherit; width:100%;">All</a>
                    <a href="{{ route('mp', ['category' => 'drinks', 'search' => request('search')]) }}" class="filter-option" style="display:block; text-decoration:none; color:inherit; width:100%;">Drinks</a>
                    <a href="{{ route('mp', ['category' => 'snacks', 'search' => request('search')]) }}" class="filter-option" style="display:block; text-decoration:none; color:inherit; width:100%;">Snacks</a>
                    <a href="{{ route('mp', ['category' => 'meals', 'search' => request('search')]) }}" class="filter-option" style="display:block; text-decoration:none; color:inherit; width:100%;">Meals</a>
                    <a href="{{ route('mp', ['category' => 'ready_made', 'search' => request('search')]) }}" class="filter-option" style="display:block; text-decoration:none; color:inherit; width:100%;">Ready Made</a>
                </div>
                <form method="GET" action="{{ route('mp') }}">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input type="text" name="search" class="search-input" placeholder="Search"
                        value="{{ request('search') }}">
                </form>

                <div class="add-item-container">
                    <button class="add-item-button">+ Add Product</button>
                </div>
            </div>

        </div>

        <!-- WHERE CONTROLS LAYER ENDS AND MAIN TABLE STARTS-->
        <div class="table-container">
            <table>
                <!-- TO ADJUST THE TABLE'S WIDTH DISTRIBUTION -->
                <colgroup>
                    <col style="width: 40%">
                    <col style="width: 15%">
                    <col style="width: 15%">
                    <col style="width: 15%">
                    <col style="width: 15%">
                </colgroup>
                <!-- TABLE HEADER -->
                <thead>
                    <tr class="">
                        <th class="th">Item Name</th>
                        <th class="th">Category</th>
                        <th class="th">Stock</th>
                        <th class="th">Price</th>
                        <th class="th">Actions</th>
                    </tr>
                </thead>
                <!-- TABLE BODY -->
                <tbody>
                    <!-- FOR LOOP TO RETRIEVE PRODUCTION INFORMATION (FUNCTIONALITY FOUND IN PRODUCT CONTROLLER) -->
                    @forelse ($products as $product)
                        <tr class="tr" data-category="{{ $product->category }}">
                            <td>
                                <div class="product-name-and-image">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                        class="product-image">
                                    <span>{{ $product->name }}</span>
                                    @if($product->recipes->isNotEmpty())
                                        <i class="bi bi-book" title="Is a cooked item (has recipe)" style="color: #6c5ce7; font-size: 0.9rem; margin-left: 0.5rem;"></i>
                                    @endif
                                </div>
                            </td>
                            </td>
                            <td>{{ ucfirst($product->category) }}</td>
                            <td style="font-weight: 700; color: {{ $product->stock <= 0 ? '#d63031' : ($product->stock <= 5 ? '#e17055' : '#2d3436') }}">
                                {{ $product->stock }}
                            </td>
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
                                <button type="button" class="waste-button" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Waste/Show Spoilage">
                                    <i class="fa-solid fa-ban" style="color: #d63031;"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- INFORMATION DISPLAYED IF NO PRODUCT IS PRESENT E.G. ALL PRODUCTS WERE DELETED -->
                    @empty
                        <tr>
                            <td colspan="5">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
    <div class="pagination-container">
        @include('components.pagination', ['paginator' => $products])
    </div>
</div>
    <!-- ADD ITEM MODAL STARTS HERE -->
    <div class="floating-add-item-container">
        <div class="floating-add-item">
            <span>Add Item</span>
        </div>
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="POST-class">
            @csrf
            <!-- ADD ITEM ITEM NAME -->
            <div class="floating-add-item-name-container">
                <label for="name">Item Name</label>
                <input type="text" class="input" name="name">
            </div>
            <!-- ADD ITEM CATEGORY OPTIONS -->
            <div class="floating-add-item-category-container">
                <span>Item Category</span>
                <div class="category-input-wrapper">
                    <input type="radio" id="drinks" name="category" value="drinks" checked>
                    <label for="drinks">Drinks</label>
                    <input type="radio" id="snacks" name="category" value="snacks">
                    <label for="snacks">Snacks</label>
                    <input type="radio" id="meals" name="category" value="meals">
                    <label for="meals">Meals</label>
                    <input type="radio" id="ready_made" name="category" value="ready_made">
                    <label for="ready_made">Ready Made</label>
                </div>
            </div>
            <!-- ADD ITEM PRICE INPUT -->
            <div class="floating-add-item-item-price-container">
                <label for="price">Price</label>
                <input type="number" name="price" class="input" placeholder="₱ 00.00" step="0.01" min="0.01" required oninput="this.setCustomValidity(this.value < 0.01 ? 'Price must be greater than 0' : '')">
            </div>
            <!-- ADD ITEM IMAGE UPLOADER -->
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
            <!-- ADD ITEM CONTROLS -->
            <div class="floating-add-item-options">
                <button type="button" class="cancel-button">Cancel</button>
                <button type="submit" class="add-button">Add</button>
            </div>
        </form>
    </div>
    <!-- ADD ITEM MODAL ENDS HERE, AND EDIT ITEM MODAL STARTS HERE -->
    <div class="floating-edit-item-container">
        <div class="floating-edit-item">
            <span>Update Item</span>
        </div>
        <form class="floating-edit-item-form" method="POST" id="productForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="_method" id="formMethod" value="PUT">
            <!-- EDIT ITEM NAME INPUT -->
            <div class="floating-edit-item-name-container">
                <label for="name">Item Name</label>
                <input type="text" class="input" name="name" id="editName">
            </div>
            <!-- EDIT ITEM CATEGORY OPTIONS-->
            <div class="floating-edit-item-category-container">
                <span>Item Category</span>
                <div class="category-input-wrapper">
                    <input type="radio" id="editDrinks" name="category" value="drinks">
                    <label for="editDrinks">Drinks</label>
                    <input type="radio" id="editSnacks" name="category" value="snacks">
                    <label for="editSnacks">Snacks</label>
                    <input type="radio" id="editMeals" name="category" value="meals">
                    <label for="editMeals">Meals</label>
                    <input type="radio" id="editReadyMade" name="category" value="ready_made">
                    <label for="editReadyMade">Ready Made</label>
                </div>
            </div>
            <!-- EDIT ITEM PRICE INPUT -->
            <div class="floating-edit-item-item-price-container">
                <label for="price">Price</label>
                <input type="number" name="price" class="input" id="editPrice" placeholder="₱ 00.00" step="0.01" min="0.01" required oninput="this.setCustomValidity(this.value < 0.01 ? 'Price must be greater than 0' : '')">
            </div>
            <!-- EDIT ITEM IMAGE UPLOADER -->
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
            <!-- EDIT ITEM CONTROLS -->
            <div class="floating-edit-item-options">
                <button type="button" class="cancel-button">Cancel</button>
                <button type="submit" class="save-button">Save</button>
            </div>
        </form>
    </div>
    <!-- EDIT ITEM MODAL ENDS HERE, AND DELETE PROMPT MESSAGE STARTS HERE -->
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
    <!-- DELETE PROMPT ENDS HERE -->
    <div class="overlay" id="overlay"></div>
    <!-- WASTE STOCK MODAL -->
    <div class="modal-container" id="wasteStockModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 2rem; border-radius: 10px; width: 400px; max-width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span style="font-weight: bold; font-size: 1.2rem; color: #d63031;">Waste Stock</span>
                <button class="modal-close" id="closeWasteStock" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form id="wasteStockForm" method="POST">
                @csrf
                <p style="margin-bottom: 1rem;">Mark stock as wasted for <strong id="wasteStockName"></strong>?</p>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem;">Quantity to Waste</label>
                    <input type="number" name="quantity" class="input" min="1" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 5px;" oninput="this.setCustomValidity(this.value < 1 ? 'Quantity must be at least 1' : '')">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem;">Reason</label>
                    <textarea name="reason" class="input" rows="3" required placeholder="e.g. Expired, Spoiled..." style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 5px;"></textarea>
                </div>
                <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="cancel-button" id="cancelWasteStock" style="padding: 0.5rem 1rem; border: 1px solid #ccc; background: white; border-radius: 5px; cursor: pointer;">Cancel</button>
                    <button type="submit" class="save-button" style="padding: 0.5rem 1rem; background: #d63031; color: white; border: none; border-radius: 5px; cursor: pointer;">Confirm Waste</button>
                </div>
            </form>
        </div>
    </div>
@endsection
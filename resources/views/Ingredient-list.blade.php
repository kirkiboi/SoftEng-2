@extends('main')
@section('ingredient inventory management', 'System 3')
@section('content')
@if (session('success'))
    <div class="my-alert alert-success">
        {{ session('success') }}
    </div>
@endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/ingredient-list.css'])
    @vite(['resources/js/ingredient-list.js'])
    <div class="main-container">
        <div class="parent-container">
            <div class="header-container">
                <div class="filter-and-search-container">
                    <!-- FILTER ICON -->
                    <div class="filter-icon">
                        <i class="bi bi-funnel"></i>
                    </div>
                    
                    <!-- FILTER MODAL -->
                    <form method="GET" action="{{ route('im') }}" class="filter-drop-down-modal">
                        <div class="filter-drop-down-modal-container">
                            <div class="filter-drop-down-wrapper">
                                <select name="filter-category">
                                    <option value="">All</option>
                                    <option value="meat">Meat</option>
                                    <option value="produce">Produce</option>
                                    <option value="condiments">Condiments</option>
                                    <option value="canned_goods">Canned Goods</option>
                                    <option value="spices">Spices</option>
                                    <option value="sweeteners">Sweeteners</option>
                                    <option value="oils">Oils</option>
                                    <option value="baking">Baking</option>
                                    <option value="thickeners">Thickeners</option>
                                    <option value="herb">Herb</option>
                                    <option value="dairy">Dairy</option>
                                    <option value="grains">Grains</option>
                                    <option value="others">Others</option>
                                </select>
                                <button class="apply-filter-button">Apply Filter</button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- SEARCH INPUT -->
                    <form method="GET" action="{{ route('im') }}" class="search-form">
                        <input 
                            type="text"
                            name="search"
                            class="search-input"
                            placeholder="Search by ingredient name"
                            value="{{ request('search') }}"
                        >
                    </form>
                </div>

                <!-- BUTTON CONTAINER -->
                <div class="button-container">
                    <button class="record-stock-in-button">Record Stock In</button>
                    <button class="record-product-stock-in-button">Product Stock In</button>
                    <button class="add-ingredient-button">Add Ingredient</button>
                </div>
            </div>
            <!-- BUTTONS ENDS HERE + MAIN BODY (TABLE) STARTS HERE -->
            <div class="table-container">
                <table>
                    <colgroup>
                        <col style="width: 25%">
                        <col style="width: 15%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                    </colgroup>
                    <thead>
                        <tr class="">
                            <th class="th">Ingredient Name</th>
                            <th class="th">Category</th>
                            <th class="th">Unit</th>
                            <th class="th">Stock</th>
                            <th class="th">Cost</th>
                            <th class="th">Threshold</th>
                            <th class="th">Status</th>
                            <th class="th">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ingredients as $ingredient)
                            <tr class="tr">
                                <td>
                                    <div class="product-name-and-image">
                                        <span>{{ $ingredient->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $ingredient->category }}</td>
                                <td>{{ $ingredient->unit }}</td>
                                <td>{{ $ingredient->stock }}{{ $ingredient->unit }}</td>
                                <td>₱{{ number_format($ingredient->cost_per_unit, 2) }}</td>
                                <td>{{ $ingredient->threshold ?? '-' }}{{ $ingredient->unit }}</td>
                                <td>
                                    @if($ingredient->stock <= $ingredient->threshold)
                                        <span class="status-low">Low</span>
                                    @else
                                        <span class="status-ok">OK</span>
                                    @endif
                                </td>
                                <td class="td-actions">
                                    <button 
                                        class="edit-ingredient-btn"
                                        data-id="{{ $ingredient->id }}"
                                        data-name="{{ $ingredient->name }}"
                                        data-category="{{ $ingredient->category }}"
                                        data-unit="{{ $ingredient->unit }}"
                                        data-cost="{{ $ingredient->cost_per_unit }}"
                                        data-threshold="{{ $ingredient->threshold }}"
                                    ><i class="fa-solid fa-pencil"></i></button>
                                    <form method="POST" action="{{ route('ingredients.destroy', $ingredient->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete-button"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- PAGINATION CONTROL STARTS HERE -->
            <div class="pagination-container">
                @include('components.pagination', ['paginator' => $ingredients])
            </div>
        </div>
    </div>
        </div>
    </div>
    <!-- TABLE ENDS HERE + ADD INGREDIENT MODAL STARTS HERE -->
    <div class="floating-add-ingredient-container">
        <div class="floating-add-ingredient-container-wrapper">
            <div class="floating-add-item">
                <span>Add new ingredient</span>
            </div>
            <form class="floating-add-ingredient-form" method="POST" action="{{ route('ingredients.store') }}">
                @csrf
                <div class="floating-add-item-name-container">
                    <label>Ingredient name</label>
                    <input type="text" class="input" name="name" required>
                </div>
                <div class="floating-add-item-category-container">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="meat">Meat</option>
                        <option value="produce">Produce</option>
                        <option value="condiments">Condiments</option>
                        <option value="canned_goods">Canned Goods</option>
                        <option value="spices">Spices</option>
                        <option value="sweeteners">Sweeteners</option>
                        <option value="oils">Oils</option>
                        <option value="baking">Baking</option>
                        <option value="thickeners">Thickeners</option>
                        <option value="herb">Herb</option>
                        <option value="dairy">Dairy</option>
                        <option value="grains">Grains</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <div class="floating-add-ingredient-unit">
                    <label>Unit</label>
                    <select name="unit" required>
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="pcs">pcs</option>
                        <option value="ml">ml</option>
                    </select>
                </div>
                <div class="floating-add-ingredient-cost-per-unit">
                    <label>Cost per unit</label>
                    <input type="number" step="0.01" name="cost_per_unit" required>
                </div>
                <div class="floating-add-ingredient-stock">
                    <label>Initial stock</label>
                    <input type="number" step="0.01" name="stock" value="0" required>
                </div>
                <div class="floating-add-ingredient-threshold">
                    <label>Threshold</label>
                    <input type="number" step="0.01" name="threshold" value="0" required>
                </div>
                <div class="floating-add-item-options">
                    <button type="button" class="cancel-button" id="add-ingredient-cancel-button">Cancel</button>
                    <button type="submit" class="add-button">Add</button>
                </div>
            </form>
        </div>
    </div>
    <!-- FLOATING EDIT INGREDIENT MODAL -->
    <div class="floating-edit-ingredient-container">
        <div class="floating-edit-ingredient-container-wrapper">
            <div class="floating-edit-item">
                <span>Edit ingredient</span>
            </div>
            <form method="POST" id="editIngredientForm" class="edit-ingredient-form">
                @csrf
                @method('PUT')
                <div class="floating-edit-item-name-container">
                    <label>Ingredient name</label>
                    <input type="text" class="input" name="name" required>
                </div>
                <div class="floating-edit-item-category-container">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="meat">Meat</option>
                        <option value="produce">Produce</option>
                        <option value="condiments">Condiments</option>
                        <option value="canned_goods">Canned Goods</option>
                        <option value="spices">Spices</option>
                        <option value="sweeteners">Sweeteners</option>
                        <option value="oils">Oils</option>
                        <option value="baking">Baking</option>
                        <option value="thickeners">Thickeners</option>
                        <option value="herb">Herb</option>
                        <option value="dairy">Dairy</option>
                        <option value="grains">Grains</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <div class="floating-edit-ingredient-unit">
                    <label>Unit</label>
                    <select name="unit" required>
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="pcs">pcs</option>
                        <option value="ml">ml</option>
                    </select>
                </div>
                <div class="floating-add-item-options">
                    <button type="button" class="edit-cancel-button">Cancel</button>
                    <button type="submit" class="add-button">Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- EDIT INGREDIENT MODAL ENDS HERE + RECORD STOCK IN MODAL STARTS HERE -->
    <div class="record-stock-in-container">
        <div class="record-stock-in-wrapper">
            <div class="floating-add-item">
                <span>Record Stock In</span>
            </div>
            <form method="POST" action="{{ route('ingredients.stockIn') }}" class="floating-add-ingredient-form">
                @csrf
                <div class="floating-add-item-name-container">
                    <label>Ingredient</label>
                    <select name="ingredient_id" required class="input">
                        <option value="">Select an ingredient</option>
                        @foreach(\App\Models\Ingredient::all() as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->stock }}{{ $ing->unit }} in stock)</option>
                        @endforeach
                    </select>
                </div>
                <div class="floating-add-ingredient-cost-per-unit">
                    <label>Quantity to add</label>
                    <input type="number" step="0.01" min="0.01" name="quantity" class="input" placeholder="Enter quantity" required>
                </div>
                <div class="floating-add-ingredient-cost-per-unit">
                    <label>Supplier (optional)</label>
                    <input type="text" name="supplier" class="input" placeholder="Enter supplier name">
                </div>
                <div class="floating-add-item-options">
                    <button type="button" class="cancel-button" id="stock-in-cancel-button">Cancel</button>
                    <button type="submit" class="add-button">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
    <!-- RECORD STOCK IN MODAL ENDS HERE + PRODUCT STOCK IN MODAL STARTS HERE -->
    <div class="record-product-stock-in-container">
        <div class="record-stock-in-wrapper">
            <div class="floating-add-item">
                <span>Product Stock In</span>
            </div>
            <form method="POST" action="{{ route('products.stockIn') }}" class="floating-add-ingredient-form">
                @csrf
                <div class="floating-add-item-name-container">
                    <label>Product (Drinks/Snacks)</label>
                    <select name="product_id" required class="input">
                        <option value="">Select a product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->stock }} in stock)</option>
                        @endforeach
                    </select>
                </div>
                <div class="floating-add-ingredient-cost-per-unit">
                    <label>Quantity to add</label>
                    <input type="number" step="1" min="1" name="quantity" class="input" placeholder="Enter quantity" required>
                </div>
                <!-- Removed supplier because Product model doesn't track it on audit log yet, keeping it simple -->
                <div class="floating-add-item-options">
                    <button type="button" class="cancel-button" id="product-stock-in-cancel-button">Cancel</button>
                    <button type="submit" class="add-button">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
    <!-- PRODUCT STOCK IN MODAL ENDS HERE + DELETE INGREDIENT CONFIRMATION NGA MODAL -->
    <div class="floating-delete-item-container" id="deleteModal" >
        <div class="floating-delete-item-container-wrapper">
            <div class="remove-item-header">
                <h2>Remove Item</h2>
            </div>
            <div class="floating-delete-item">
                <p class="delete-message">Are you sure you want to delete this item? If you delete, it will be permanently lost.</p>
            </div>
            <div class="floating-delete-item-options">
                <button type="button" id="cancelDelete" class="cancel-button">Cancel</button>
                <button type="button" id="confirmDelete" class="delete-confirm-button">Delete</button>
            </div>
        </div>
    </div>
<div class="overlay" id="overlay"></div>
@endsection
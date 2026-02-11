@extends('main')
@section('ingredient inventory management', 'System 3')
@section('content')
@if (session('success'))
    <div class="my-alert alert-success">
        {{ session('success') }}
    </div>
@endif
@vite(['resources/css/ingredient-list.css'])
@vite(['resources/js/ingredient-list.js'])
    <div class="main-container">
        <div class="parent-container">
            <div class="header-container">
                <div class="filter-and-search-container">
                    <!-- FILTER ICON STARTS HERE -->
                    <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22" class="filter-icon">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <!-- FILTER ICON ENDS HERE + FILTER MODAL STARTS HERE -->
                    <form method="GET" action="{{ route('im') }}" class="filter-drop-down-modal">
                        <div class="filter-drop-down-modal-container">
                            <div class="filter-drop-down-wrapper">
                                <select name="filter-category">
                                    <option value="">All</option>
                                    <option value="sweeteners">Sweeteners</option>
                                    <option value="spices">Spices</option>
                                    <option value="oils">Oils</option>
                                    <option value="baking">Baking</option>
                                    <option value="herbs">Herbs</option>
                                    <option value="acids">Acids</option>
                                    <option value="liquids">Liquids</option>
                                    <option value="thickners">Thickners</option>
                                    <option value="condiments">Condiments</option>
                                </select>
                                <button class="apply-filter-button">Apply Filter</button>
                            </div>
                        </div>
                    </form>
                    <!-- FILTER MODAL ENDS HERE + SEARCH INPUT STARTS HERE -->
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
                <!-- SEARCH INPUT ENDS HERE + PAGINATION CONTROL STARTS HERE -->
                <div class="pagination-container">
                    {{ $ingredients->links() }}
                </div>
                <!--  SEARCH INPUT ENDS HERE + BUTTON CONTAINER DIRI TONG STOCK IN AND ADD INGREDIENTS-->
                <div class="button-container">
                    <button class="record-stock-in-button">Record Stock In</button>
                    <button class="record-product-stock-in-button" style="background-color: #4a90e2; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;">Product Stock In</button>
                    <button class="add-ingredient-button">Add Ingredient</button>
                </div>
            </div>
            <!-- BUTTONS ENDS HERE + MAIN BODY (TABLE) STARTS HERE -->
            <div class="main-body-container">
                <table>
                    <colgroup>
                        <col style="width: 19%">
                        <col style="width: 12%">
                        <col style="width: 22%">
                        <col style="width: 15%">
                        <col style="width: 12%">
                        <col style="width: 11%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead>
                        <tr class="tr">
                            <th class="th">Ingredient Name</th>
                            <th class="th">Category</th>
                            <th class="th">Unit of Measurement</th>
                            <th class="th">Current Stock</th>
                            <th class="th">Unit Cost</th>
                            <th class="th">Threshold</th>
                            <th class="th">Status</th>
                            <th class="th">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ingredients as $ingredient)
                            <tr class="tr">
                                <td>{{ $ingredient->name }}</td>
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
                        <option value="sweeteners">sweeteners</option>
                        <option value="spices">spices</option>
                        <option value="oils">oils</option>
                        <option value="baking">baking</option>
                        <option value="herbs">herbs</option>
                        <option value="acids">acids</option>
                        <option value="liquids">liquids</option>
                        <option value="thickners">thickners</option>
                        <option value="condiments">condiments</option>
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
                        <option value="sweeteners">sweeteners</option>
                        <option value="spices">spices</option>
                        <option value="oils">oils</option>
                        <option value="baking">baking</option>
                        <option value="herbs">herbs</option>
                        <option value="acids">acids</option>
                        <option value="liquids">liquids</option>
                        <option value="thickners">thickners</option>
                        <option value="condiments">condiments</option>
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
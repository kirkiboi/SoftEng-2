@extends('main')
@section('kitchen production', 'System 2')
@section('content')
@vite(['resources/css/kitchen-system.css'])
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="kitchen-production-main-container">
    <div class="kitchen-production-parent-container">
        <div class="header-container">
            <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
            <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                <button class="filter-option" data-category="all">All</button>
                <button class="filter-option" data-category="drinks">Drinks</button>
                <button class="filter-option" data-category="snacks">Snacks</button>
                <button class="filter-option" data-category="meals">Meals</button>
            </div>
            <input type="text" name="search" class="search-input" placeholder="Search">
            <div class="add-item-container">
                <button class="add-item-button">+ Add Batch</button>
                <button class="manage-recipe-button">Manage Recipes</button>
            </div>

            {{-- ==================== RECIPE MANAGER MODAL ==================== --}}
            <div class="recipe-manager-modal">
                <div class="recipe-manager-modal-wrapper">
                    <div class="recipe-manager-header">
                        <h2>Recipe Manager</h2>
                        <button id="closeRecipeModal">X</button>
                    </div>
                    <div class="recipe-manager-body">
                        {{-- Product Selection --}}
                        <select id="productSelect">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>

                        {{-- Batch Size Selection (hardcoded: 10, 20, 30, 40, 50) --}}
                        <select id="batchSizeSelect">
                            <option value="">Select Batch Size</option>
                            <option value="10">10 servings</option>
                            <option value="20">20 servings</option>
                            <option value="30">30 servings</option>
                            <option value="40">40 servings</option>
                            <option value="50">50 servings</option>
                        </select>

                        {{-- Button to add an ingredient row --}}
                        <button id="addIngredientBtn">+ Add Ingredient</button>

                        {{-- Recipe ingredient list --}}
                        <div class="recipe-list" id="recipeList"></div>
                    </div>
                    <button id="saveRecipesBtn">Save All</button>
                </div>
            </div>

            {{-- ==================== ADD BATCH MODAL ==================== --}}
            <div class="add-batch-modal">
                <div class="add-batch-modal-wrapper">
                    <div class="add-batch-span"><span>Select a Product</span></div>
                    <div class="add-batch-input">
                        <input type="text" id="batchSearchInput" placeholder="Search Product Name">
                    </div>
                    <div class="product-card-main-container">
                        <div class="add-batch-results">
                            <button class="scroll-btn left">‹</button>
                            <div class="product-card-container">
                                @foreach($products as $product)
                                    <div class="product-card" data-product-id="{{ $product->id }}" data-category="{{ $product->category }}">
                                        <div class="product-name"><span>{{ $product->name }}</span></div>
                                        <div class="product-category"><span>{{ $product->category }}</span></div>
                                        {{-- Batch Size Selection --}}
                                        <div class="batch-select-container" style="margin: 5px 0;">
                                            <select class="batch-size-select" style="width: 100%; padding: 5px;">
                                                <option value="">Select Batch Size</option>
                                                <option value="10">10 servings</option>
                                                <option value="20">20 servings</option>
                                                <option value="30">30 servings</option>
                                                <option value="40">40 servings</option>
                                                <option value="50">50 servings</option>
                                            </select>
                                        </div>
                                        <div class="product-card-button"><button class="add-batch-btn">Add Batch</button></div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="scroll-btn right">›</button>
                        </div>
                    </div>
                    <div class="batch-quantity-container"><span>Quantity:</span><input type="number" min="1" value="1"></div>
                    <div class="batch-time-container"><span>Estimated Time (minutes):</span><input type="number" min="1" value="30"></div>
                </div>
            </div>
        </div>

        {{-- ==================== KANBAN BOARD ==================== --}}
        <div class="main-body-container">
            <div class="queued-container">
                <div class="header-wrapper"><span class="queue-icon"></span><h1>Queue</h1></div>
            </div>
            <div class="cooking-container">
                <div class="header-wrapper"><span class="cooking-icon"></span><h1>Cooking</h1></div>
            </div>
            <div class="done-container">
                <div class="header-wrapper"><span class="done-icon"></span><h1>Done</h1></div>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="overlay"></div>
@vite(['resources/js/kitchen-system.js'])
@endsection
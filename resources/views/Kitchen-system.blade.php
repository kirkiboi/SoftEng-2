@extends('main')
@section('kitchen production', 'System 2')
@section('content')
@vite(['resources/css/kitchen-system.css'])
@vite(['resources/js/kitchen-system.js'])
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
            <div class="recipe-manager-modal">
                <div class="recipe-manager-modal-wrapper">
                    <div class="recipe-manager-header">
                        <h2>Recipe Manager</h2>
                        <button id="closeRecipeModal">X</button>
                    </div>
                    <div class="recipe-manager-body">
                        <select id="productSelect">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <select id="ingredientSelect">
                            <option value="">Select Ingredient</option>
                            @foreach($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">
                                    {{ $ingredient->name }} ({{ $ingredient->unit }})
                                </option>
                            @endforeach
                        </select>
                        <button id="addIngredientBtn">+ Add Ingredient</button>
                        <div class="recipe-list"></div>
                    </div>
                    <button id="saveRecipesBtn">Save All</button>
                </div>
            </div>
            <div class="add-batch-modal">
                <div class="add-batch-modal-wrapper">
                    <div class="add-batch-span"><span>Search a batch</span></div>
                    <div class="add-batch-input">
                        <input type="text" placeholder="Search Batch Name">
                    </div>
                    <div class="product-card-main-container"> 
                        <div class="add-batch-results">
                            <button class="scroll-btn left">‹</button>
                            <div class="product-card-container">
                                @foreach($products as $product)
                                <div class="product-card">
                                    <div class="product-name"><span>{{ $product->name }}</span></div>
                                    <div class="product-category"><span>{{ $product->category }}</span></div>
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
@endsection
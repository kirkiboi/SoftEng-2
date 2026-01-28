@extends('main')
@section('im', 'System 3')
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
                <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <input 
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search"
                    value="{{ request('search') }}"
                >
                </div>
                <div class="pagination-container">
                    <span>1 - 8 of 52</span>
                    <span> < > </span>
                </div>
                <div class="button-container">
                    <button class="record-stock-in-button">Record Stock In</button>
                    <button class="add-ingredient-button">Add Ingredient</button>
                </div>
            </div>
            <div class="main-body-container">
                <table>
                    <colgroup>
                        <col style="width: 19%">
                        <col style="width: 20%">
                        <col style="width: 15%">
                        <col style="width: 13%">
                        <col style="width: 16%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead>
                        <tr class="tr">
                            <th class="th">Ingredient Name</th>
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
                                <td>
                                    <form method="POST" action="{{ route('ingredients.destroy', $ingredient->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete-button">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
                <input type="hidden" name="status" value="active">
                <div class="floating-add-item-options">
                    <button type="button" class="cancel-button">Cancel</button>
                    <button type="submit" class="add-button">Add</button>
                </div>
            </form>
        </div>
    </div>
@endsection
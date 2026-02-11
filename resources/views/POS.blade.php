@extends('main')
@section('pos', 'System 1')
@section('content')
@vite(['resources/css/new-transaction.css'])
@vite(['resources/js/pos.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="pos-wrapper">
    <!-- LEFT: PRODUCT GRID -->
    <div class="pos-products-panel">
        <div class="pos-products-header">
            <h2 class="pos-title">Menu</h2>
            <div class="pos-search-container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="posSearch" placeholder="Search items..." class="pos-search">
            </div>
        </div>
        <div class="category-tabs">
            <button class="cat-tab active" data-category="all">All</button>
            <button class="cat-tab" data-category="meals">Meals</button>
            <button class="cat-tab" data-category="drinks">Drinks</button>
            <button class="cat-tab" data-category="snacks">Snacks</button>
        </div>
        <div class="products-grid" id="productsGrid">
            @foreach($products as $product)
                <div class="pos-product-card {{ $product->stock <= 0 ? 'out-of-stock' : '' }}" 
                     data-id="{{ $product->id }}" 
                     data-name="{{ $product->name }}" 
                     data-price="{{ $product->price }}" 
                     data-category="{{ $product->category }}"
                     data-stock="{{ $product->stock }}">
                    
                    <div class="product-stock-badge {{ $product->stock <= 5 ? 'low-stock' : '' }}">
                        {{ $product->stock }} left
                    </div>

                    <div class="product-image-container">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" onerror="this.src='/public/photos/default-food.png'">
                    </div>
                    <div class="product-info">
                        <span class="product-card-name">{{ $product->name }}</span>
                        <span class="product-card-price">₱{{ number_format($product->price, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- RIGHT: TRANSACTION PANEL -->
    <div class="pos-transaction-panel">
        <div class="transaction-header">
            <h2 class="transaction-title">New Transaction</h2>
            <button class="clear-cart-btn" id="clearCart">
                <i class="fa-solid fa-trash"></i> Clear
            </button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="empty-cart" id="emptyCart">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>No items added yet</p>
            </div>
        </div>

        <div class="transaction-footer">
            <div class="transaction-summary">
                <div class="summary-row">
                    <span>Items</span>
                    <span id="totalItems">0</span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span id="totalAmount">₱0.00</span>
                </div>
            </div>
            <button class="checkout-btn" id="checkoutBtn" disabled>
                <i class="fa-solid fa-cash-register"></i> Proceed to Checkout
            </button>
        </div>
    </div>
</div>

<!-- CHECKOUT MODAL -->
<div class="modal-container" id="checkoutModal">
    <div class="modal-content">
        <div class="modal-header">
            <span>Payment</span>
            <button class="modal-close" id="closeCheckout">&times;</button>
        </div>
        <div class="modal-body">
            <div class="checkout-total">
                <span>Total Amount</span>
                <span class="checkout-amount" id="checkoutTotalDisplay">₱0.00</span>
            </div>
            <div class="payment-methods">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cash" checked>
                    <div class="payment-card">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Cash</span>
                    </div>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="gcash">
                    <div class="payment-card">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>GCash</span>
                    </div>
                </label>
            </div>
            <div class="form-group" id="amountPaidGroup">
                <label>Amount Tendered</label>
                <input type="number" step="0.01" min="0" id="amountPaid" class="input" placeholder="Enter amount">
                <div class="change-display" id="changeDisplay" style="display:none;">
                    Change: <strong id="changeAmount">₱0.00</strong>
                </div>
            </div>
            <div id="checkoutError" class="alert alert-error" style="display:none;"></div>
            <div class="form-actions">
                <button type="button" class="cancel-button" id="cancelCheckout">Cancel</button>
                <button type="button" class="add-button" id="confirmPayment">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- RECEIPT MODAL -->
<div class="modal-container" id="receiptModal">
    <div class="modal-content receipt-modal-content">
        <div class="receipt-header-section">
            <h3>UM Dining Center</h3>
            <p>University of Mindanao</p>
        </div>
        <div class="receipt-body" id="receiptBody">
            <!-- Filled by JS -->
        </div>
        <div class="receipt-footer-section">
            <button class="add-button" id="closeReceipt" style="width:100%;">Done</button>
        </div>
    </div>
</div>

<div class="overlay" id="overlay"></div>
@endsection
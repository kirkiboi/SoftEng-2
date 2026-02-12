@extends('main')
@section('pos', 'System 1')
@section('content')
@vite(['resources/css/new-transaction.css'])
@vite(['resources/js/pos.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="pos-wrapper">
    <!-- LEFT: PRODUCT GRID -->
    <!-- LEFT: PRODUCT GRID -->
    <div class="pos-products-panel">
        <div class="pos-products-header">
            <div>
                <h2 class="pos-title">Point of Sale</h2>
                <p class="pos-subtitle">Select items to add to cart</p>
            </div>
            <div class="pos-search-container">
                <form action="{{ route('pos') }}" method="GET" style="display:flex; align-items:center; width:100%;">
                    <input type="hidden" name="category" value="{{ request('category', 'all') }}">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." class="pos-search">
                </form>
            </div>
        </div>
        <div class="category-tabs">
            <a href="{{ route('pos', ['category' => 'all', 'search' => request('search')]) }}" class="cat-tab {{ request('category', 'all') == 'all' ? 'active' : '' }}">All</a>
            <a href="{{ route('pos', ['category' => 'meals', 'search' => request('search')]) }}" class="cat-tab {{ request('category') == 'meals' ? 'active' : '' }}">Meals</a>
            <a href="{{ route('pos', ['category' => 'drinks', 'search' => request('search')]) }}" class="cat-tab {{ request('category') == 'drinks' ? 'active' : '' }}">Drinks</a>
            <a href="{{ route('pos', ['category' => 'snacks', 'search' => request('search')]) }}" class="cat-tab {{ request('category') == 'snacks' ? 'active' : '' }}">Snacks</a>
            <a href="{{ route('pos', ['category' => 'ready_made', 'search' => request('search')]) }}" class="cat-tab {{ request('category') == 'ready_made' ? 'active' : '' }}">Ready Made</a>
        </div>
        <div class="products-grid" id="productsGrid">
            @forelse($products as $product)
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
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             onerror="this.style.display='none'; this.parentElement.classList.add('img-fallback'); this.parentElement.setAttribute('data-letter', '{{ substr($product->name, 0, 1) }}');">
                    </div>
                    <div class="product-info">
                        <span class="product-card-name">{{ $product->name }}</span>
                        <span class="product-card-price">₱{{ number_format($product->price, 2) }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                    <p>No products found.</p>
                </div>
            @endforelse
        </div>
        <div class="pos-pagination" id="posPagination">
            {{ $products->links('components.pagination') }}
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
        <div class="receipt-footer-section" style="display: flex; gap: 0.5rem;">
            <button class="cancel-button" onclick="window.print()" style="flex:1; background-color: #636e72; color: white;"><i class="fa-solid fa-print"></i> Print</button>
            <button class="add-button" id="closeReceipt" style="flex:1;">Done</button>
        </div>
    </div>
</div>

<div class="overlay" id="overlay"></div>
@endsection
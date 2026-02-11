document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const overlay = document.getElementById('overlay');
    const checkoutModal = document.getElementById('checkoutModal');
    const receiptModal = document.getElementById('receiptModal');

    let cart = []; // { id, name, price, quantity }

    // Helpers
    const openOverlay = () => overlay?.classList.add('show');
    const closeOverlay = () => overlay?.classList.remove('show');
    function closeAll() {
        checkoutModal?.classList.remove('active');
        receiptModal?.classList.remove('active');
        closeOverlay();
    }

    // ===== PRODUCT CARDS: Add to cart =====
    document.querySelectorAll('.pos-product-card').forEach(card => {
        card.addEventListener('click', () => {
            // Check stock first
            if (card.classList.contains('out-of-stock')) return;

            const id = parseInt(card.dataset.id);
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const stock = parseInt(card.dataset.stock);

            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.quantity + 1 > stock) {
                    alert(`Sorry, only ${stock} items available in stock.`);
                    return;
                }
                existing.quantity++;
            } else {
                if (1 > stock) {
                    alert('Item is out of stock.');
                    return;
                }
                cart.push({ id, name, price, quantity: 1, stock: stock });
            }
            card.classList.add('added');
            setTimeout(() => card.classList.remove('added'), 500);
            renderCart();
        });
    });

    // ... (Category Filter and Search remain unchanged) ...

    // ===== RENDER CART =====
    function renderCart() {
        const container = document.getElementById('cartItems');
        const emptyCart = document.getElementById('emptyCart');
        const totalItemsEl = document.getElementById('totalItems');
        const totalAmountEl = document.getElementById('totalAmount');
        const checkoutBtn = document.getElementById('checkoutBtn');

        if (cart.length === 0) {
            container.innerHTML = '<div class="empty-cart" id="emptyCart"><i class="fa-solid fa-cart-shopping"></i><p>No items added yet</p></div>';
            totalItemsEl.textContent = '0';
            totalAmountEl.textContent = '₱0.00';
            checkoutBtn.disabled = true;
            return;
        }

        let totalItems = 0;
        let totalAmount = 0;

        container.innerHTML = cart.map((item, index) => {
            const subtotal = item.price * item.quantity;
            totalItems += item.quantity;
            totalAmount += subtotal;
            return `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <span class="cart-item-name">${item.name}</span>
                        <span class="cart-item-price">₱${item.price.toFixed(2)} each</span>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn remove-btn" data-index="${index}" data-action="decrease">−</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn" data-index="${index}" data-action="increase">+</button>
                    </div>
                    <span class="cart-item-subtotal">₱${subtotal.toFixed(2)}</span>
                </div>
            `;
        }).join('');

        totalItemsEl.textContent = totalItems;
        totalAmountEl.textContent = `₱${totalAmount.toFixed(2)}`;
        checkoutBtn.disabled = false;

        // Bind qty buttons
        container.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                const action = this.dataset.action;
                if (action === 'increase') {
                    if (cart[index].quantity + 1 > cart[index].stock) {
                        alert(`Cannot add more. Only ${cart[index].stock} in stock.`);
                        return;
                    }
                    cart[index].quantity++;
                } else {
                    cart[index].quantity--;
                    if (cart[index].quantity <= 0) cart.splice(index, 1);
                }
                renderCart();
            });
        });
    }

    // ===== CLEAR CART =====
    document.getElementById('clearCart')?.addEventListener('click', () => {
        cart = [];
        renderCart();
    });

    // ===== CHECKOUT =====
    document.getElementById('checkoutBtn')?.addEventListener('click', () => {
        if (cart.length === 0) return;
        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        document.getElementById('checkoutTotalDisplay').textContent = `₱${total.toFixed(2)}`;
        document.getElementById('amountPaid').value = '';
        document.getElementById('changeDisplay').style.display = 'none';
        document.getElementById('checkoutError').style.display = 'none';
        checkoutModal.classList.add('active');
        openOverlay();
    });

    document.getElementById('closeCheckout')?.addEventListener('click', closeAll);
    document.getElementById('cancelCheckout')?.addEventListener('click', closeAll);

    // Amount tendered change calculation
    document.getElementById('amountPaid')?.addEventListener('input', function() {
        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const paid = parseFloat(this.value) || 0;
        const changeDiv = document.getElementById('changeDisplay');
        const changeAmt = document.getElementById('changeAmount');

        if (paid >= total) {
            changeDiv.style.display = 'block';
            changeAmt.textContent = `₱${(paid - total).toFixed(2)}`;
        } else {
            changeDiv.style.display = 'none';
        }
    });

    // Confirm payment
    document.getElementById('confirmPayment')?.addEventListener('click', async () => {
        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
        const errorDiv = document.getElementById('checkoutError');

        if (amountPaid < total) {
            errorDiv.textContent = 'Amount tendered is less than the total.';
            errorDiv.style.display = 'block';
            return;
        }

        errorDiv.style.display = 'none';

        try {
            const res = await fetch('/pos/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    items: cart.map(item => ({ product_id: item.id, quantity: item.quantity })),
                    payment_method: paymentMethod,
                    amount_paid: amountPaid,
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                closeAll();
                showReceipt(data);
                cart = [];
                renderCart();
            } else {
                errorDiv.textContent = data.error || 'Checkout failed.';
                errorDiv.style.display = 'block';
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.style.display = 'block';
        }
    });

    // ===== RECEIPT =====
    function showReceipt(data) {
        const body = document.getElementById('receiptBody');
        body.innerHTML = `
            <div class="receipt-info">
                <div class="receipt-info-row"><span>Order ID</span><span>${data.order_id}</span></div>
                <div class="receipt-info-row"><span>Date</span><span>${data.date}</span></div>
                <div class="receipt-info-row"><span>Cashier</span><span>${data.cashier}</span></div>
                <div class="receipt-info-row"><span>Payment</span><span>${data.payment_method.toUpperCase()}</span></div>
            </div>
            <hr class="receipt-divider">
            ${data.items.map(item => `
                <div class="receipt-item-row">
                    <span>${item.product_name}</span>
                    <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                </div>
                <div class="receipt-item-detail">${item.quantity}x @ ₱${parseFloat(item.unit_price).toFixed(2)}</div>
            `).join('')}
            <hr class="receipt-divider">
            <div class="receipt-total-row"><span>Total</span><span>₱${parseFloat(data.total_amount).toFixed(2)}</span></div>
            <div class="receipt-info-row"><span>Paid</span><span>₱${parseFloat(data.amount_paid).toFixed(2)}</span></div>
            <div class="receipt-info-row"><span>Change</span><span>₱${parseFloat(data.change_amount).toFixed(2)}</span></div>
        `;

        receiptModal.classList.add('active');
        openOverlay();
    }

    document.getElementById('closeReceipt')?.addEventListener('click', () => {
        closeAll();
    });

    // Overlay click
    overlay?.addEventListener('click', closeAll);
});

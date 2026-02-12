document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const overlay = document.getElementById('overlay');
    const recipeManagerModal = document.getElementById('recipeManagerModal');
    const addBatchModal = document.getElementById('addBatchModal');
    const wasteModal = document.getElementById('wasteModal');
    const closeKitchenModal = document.getElementById('closeKitchenModal');

    // Helpers
    const openOverlay = () => overlay?.classList.add('show');
    const closeOverlay = () => overlay?.classList.remove('show');
    function closeAll() {
        recipeManagerModal?.classList.remove('active');
        addBatchModal?.classList.remove('active');
        wasteModal?.classList.remove('active');
        closeKitchenModal?.classList.remove('active');
        closeOverlay();
    }

    // ===== RECIPE MANAGER =====
    document.getElementById('openRecipeManager')?.addEventListener('click', () => {
        closeAll();
        recipeManagerModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeRecipeManager')?.addEventListener('click', closeAll);

    // Load recipes when product is selected
    document.getElementById('recipeProductSelect')?.addEventListener('change', async function() {
        const productId = this.value;
        const section = document.getElementById('recipeIngredientsSection');
        const list = document.getElementById('currentRecipeList');

        if (!productId) { section.style.display = 'none'; return; }

        section.style.display = 'block';
        list.innerHTML = '<p style="color:#999;font-size:0.85rem;">Loading...</p>';

        try {
            const res = await fetch(`/kitchen/recipes/${productId}`);
            const recipes = await res.json();
            if (recipes.length === 0) {
                list.innerHTML = '<p style="color:#999;font-size:0.85rem;">No ingredients yet. Add some below.</p>';
            } else {
                list.innerHTML = recipes.map(r => {
                    let qty = parseFloat(r.quantity);
                    let unit = r.ingredient?.unit || '';
                    let displayQty = qty;
                    let displayUnit = unit;
                    let isConverted = false;

                    if (unit === 'kg' && qty < 1) {
                        displayQty = parseFloat((qty * 1000).toFixed(3));
                        displayUnit = 'g';
                        isConverted = true;
                    }

                    return `
                    <div class="recipe-item" data-recipe-id="${r.id}" data-converted="${isConverted}">
                        <span>${r.ingredient?.name || 'Unknown'} — <strong class="recipe-qty" contenteditable="true" data-original="${displayQty}">${displayQty}</strong> <span class="recipe-unit">${displayUnit}</span></span>
                        <div class="recipe-item-actions">
                            <button class="recipe-save-btn" data-id="${r.id}" title="Save" style="display:none;"><i class="fa-solid fa-check"></i></button>
                            <button class="recipe-delete-btn" data-id="${r.id}" title="Remove"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                `}).join('');

                // Show save button when quantity is edited
                list.querySelectorAll('.recipe-qty').forEach(qty => {
                    qty.addEventListener('input', function() {
                        const saveBtn = this.closest('.recipe-item').querySelector('.recipe-save-btn');
                        saveBtn.style.display = this.textContent.trim() !== this.dataset.original ? 'inline-flex' : 'none';
                    });
                });

                // Save edited quantity
                list.querySelectorAll('.recipe-save-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.dataset.id;
                        const item = this.closest('.recipe-item');
                        let qty = parseFloat(item.querySelector('.recipe-qty').textContent.trim());
                        const isConverted = item.dataset.converted === 'true';

                        if (isNaN(qty)) { alert('Invalid quantity'); return; }

                        if (isConverted) {
                            qty = qty / 1000; // Convert back to kg
                        }

                        try {
                            const res = await fetch(`/recipes/${id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                body: JSON.stringify({ quantity: qty })
                            });
                            if (res.ok) {
                                document.getElementById('recipeProductSelect').dispatchEvent(new Event('change'));
                            } else { alert('Failed to save.'); }
                        } catch (err) { alert('Error saving.'); }
                    });
                });

                // Delete ingredient
                list.querySelectorAll('.recipe-delete-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.dataset.id;
                        if (!confirm('Remove this ingredient from recipe?')) return;
                        try {
                            const res = await fetch(`/recipes/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                            });
                            if (res.ok) {
                                document.getElementById('recipeProductSelect').dispatchEvent(new Event('change'));
                            } else { alert('Failed to remove.'); }
                        } catch (err) { alert('Error removing ingredient.'); }
                    });
                });
            }
        } catch (err) {
            list.innerHTML = '<p style="color:#dc3545;">Failed to load recipes.</p>';
        }
    });

    // Add ingredient to recipe
    document.getElementById('addRecipeIngredientForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const productId = document.getElementById('recipeProductSelect').value;
        const ingredientId = document.getElementById('recipeIngredientSelect').value;
        const quantity = document.getElementById('recipeQuantity').value;

        if (!productId || !ingredientId || !quantity) {
            alert('Please fill all fields.');
            return;
        }

        try {
            const res = await fetch('/recipes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    ingredient_id: ingredientId,
                    quantity: quantity
                })
            });

            if (res.ok) {
                document.getElementById('recipeProductSelect').dispatchEvent(new Event('change'));
                document.getElementById('recipeQuantity').value = '';
                document.getElementById('recipeIngredientSelect').value = '';
            } else {
                const data = await res.json();
                alert(data.message || 'Failed to add ingredient.');
            }
        } catch (err) {
            alert('Error adding ingredient.');
        }
    });

    // ===== COOK BATCH =====
    document.getElementById('openAddBatch')?.addEventListener('click', () => {
        closeAll();
        addBatchModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeAddBatch')?.addEventListener('click', closeAll);
    document.getElementById('cancelBatch')?.addEventListener('click', closeAll);

    // Preview ingredients when product is selected for batch
    const batchProductSelect = document.getElementById('batchProductSelect');
    const batchTimesCooked = document.getElementById('batchTimesCooked');

    async function updateBatchPreview() {
        const productId = batchProductSelect?.value;
        const times = parseInt(batchTimesCooked?.value) || 1;
        const preview = document.getElementById('batchIngredientPreview');
        const list = document.getElementById('batchIngredientList');
        const errorDiv = document.getElementById('batchError');

        if (!productId) { preview.style.display = 'none'; return; }

        preview.style.display = 'block';
        errorDiv.style.display = 'none';
        list.innerHTML = '<p style="color:#999;font-size:0.85rem;">Loading...</p>';

        try {
            const res = await fetch(`/kitchen/recipes/${productId}`);
            const recipes = await res.json();

            if (recipes.length === 0) {
                list.innerHTML = '<p style="color:#dc3545;font-size:0.85rem;">No recipe found. Add ingredients via Recipe Manager first.</p>';
                return;
            }

            list.innerHTML = recipes.map(r => {
                let needed = r.quantity * times;
                let available = parseFloat(r.ingredient?.stock || 0);
                let unit = r.ingredient?.unit || '';
                
                // Convert to grams if unit is kg and needed amount < 1kg
                if (unit === 'kg' && needed < 1) {
                    needed = needed * 1000;
                    available = available * 1000; // Convert available too for comparison consistency logic (though display might be mixed if stock > 1kg? Let's keep simple)
                    // Actually, if stock is 5kg, showing 5000g is fine.
                    unit = 'g';
                }

                // Format numbers
                const neededStr = parseFloat(needed.toFixed(3));
                const availableStr = parseFloat(available.toFixed(3));

                const isInsufficient = available < needed; // Works because we converted both or neither
                
                return `
                    <div class="preview-item">
                        <span>${r.ingredient?.name || 'Unknown'}</span>
                        <span class="${isInsufficient ? 'insufficient' : ''}">${neededStr}${unit} needed (${availableStr}${unit} available)</span>
                    </div>
                `;
            }).join('');
        } catch (err) {
            list.innerHTML = '<p style="color:#dc3545;">Failed to load recipe.</p>';
        }
    }

    batchProductSelect?.addEventListener('change', updateBatchPreview);
    batchTimesCooked?.addEventListener('input', updateBatchPreview);

    // Confirm batch production
    document.getElementById('confirmBatch')?.addEventListener('click', async () => {
        const productId = batchProductSelect?.value;
        const times = parseInt(batchTimesCooked?.value) || 1;
        const errorDiv = document.getElementById('batchError');

        if (!productId) { alert('Please select a product.'); return; }

        errorDiv.style.display = 'none';

        try {
            const res = await fetch('/kitchen/start-production', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    times_cooked: times,
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                closeAll();
                window.location.reload();
            } else {
                errorDiv.textContent = data.error || 'Production failed.';
                errorDiv.style.display = 'block';
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.style.display = 'block';
        }
    });

    // ===== STATUS UPDATES (Start Cooking / Mark Done / Served) =====
    document.querySelectorAll('.status-btn:not(.waste-btn):not(.cancel-btn)').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const status = this.dataset.status;
            if (!status) return; // waste-btn has no data-status

            try {
                const res = await fetch(`/kitchen/update-status/${id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status })
                });

                if (res.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to update status.');
                }
            } catch (err) {
                alert('Network error.');
            }
        });
    });

    // ===== CANCEL BATCH (Queue only) =====
    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to cancel this batch? Ingredients will be refunded.')) return;

            const id = this.dataset.id;
            try {
                const res = await fetch(`/kitchen/cancel/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to cancel batch.');
                }
            } catch (err) {
                alert('Network error.');
            }
        });
    });

    // ===== WASTE BATCH =====
    let wasteTargetId = null;

    document.querySelectorAll('.waste-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            wasteTargetId = this.dataset.id;
            document.getElementById('wasteProductName').textContent = this.dataset.name;
            document.getElementById('wasteReason').value = '';
            closeAll();
            wasteModal.classList.add('active');
            openOverlay();
        });
    });

    document.getElementById('closeWaste')?.addEventListener('click', closeAll);
    document.getElementById('cancelWaste')?.addEventListener('click', closeAll);

    document.getElementById('confirmWaste')?.addEventListener('click', async () => {
        if (!wasteTargetId) return;
        const reason = document.getElementById('wasteReason').value.trim();

        try {
            const res = await fetch(`/kitchen/update-status/${wasteTargetId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    status: 'wasted',
                    waste_reason: reason || 'No reason provided'
                })
            });

            if (res.ok) {
                closeAll();
                window.location.reload();
            } else {
                alert('Failed to mark as wasted.');
            }
        } catch (err) {
            alert('Network error.');
        }
    });

    // ===== CLOSE KITCHEN (End of Day) =====
    document.getElementById('openCloseKitchen')?.addEventListener('click', () => {
        closeAll();
        closeKitchenModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeCloseKitchen')?.addEventListener('click', closeAll);
    document.getElementById('cancelCloseKitchen')?.addEventListener('click', closeAll);

    document.getElementById('confirmCloseKitchen')?.addEventListener('click', async () => {
        try {
            const res = await fetch('/kitchen/close', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeAll();
                window.location.reload();
            } else {
                alert(data.message || 'Failed to close kitchen.');
            }
        } catch (err) {
            alert('Network error.');
        }
    });

    // Overlay click to close
    overlay?.addEventListener('click', closeAll);
});

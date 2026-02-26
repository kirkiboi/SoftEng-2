document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const overlay = document.getElementById('overlay');
    const recipeManagerModal = document.getElementById('recipeManagerModal');
    const addBatchModal = document.getElementById('addBatchModal');
    const wasteModal = document.getElementById('wasteModal');

    // Helpers
    const openOverlay = () => overlay?.classList.add('show');
    const closeOverlay = () => overlay?.classList.remove('show');
    function closeAll() {
        recipeManagerModal?.classList.remove('active');
        addBatchModal?.classList.remove('active');
        wasteModal?.classList.remove('active');
        document.getElementById('startShiftModal')?.classList.remove('active');
        document.getElementById('endShiftModal')?.classList.remove('active');
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

    // Auto-set unit when ingredient is selected
    document.getElementById('recipeIngredientSelect')?.addEventListener('change', function() {
        const sel = this;
        const opt = sel.options[sel.selectedIndex];
        const unit = opt?.dataset?.unit || 'kg';
        const unitSelect = document.getElementById('recipeUnitSelect');
        if (unitSelect) {
            unitSelect.value = unit;
        }
    });

    // Add ingredient to recipe
    document.getElementById('addRecipeIngredientForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const productId = document.getElementById('recipeProductSelect').value;
        const ingredientId = document.getElementById('recipeIngredientSelect').value;
        let quantity = parseFloat(document.getElementById('recipeQuantity').value);
        const selectedUnit = document.getElementById('recipeUnitSelect')?.value || 'kg';

        if (!productId || !ingredientId || !quantity) {
            alert('Please fill all fields.');
            return;
        }

        // Get the ingredient's base unit from the option data attribute
        const ingOpt = document.getElementById('recipeIngredientSelect').options[document.getElementById('recipeIngredientSelect').selectedIndex];
        const baseUnit = ingOpt?.dataset?.unit || selectedUnit;

        // Convert to base unit if different
        if (selectedUnit === 'g' && baseUnit === 'kg') quantity = quantity / 1000;
        if (selectedUnit === 'kg' && baseUnit === 'g') quantity = quantity * 1000;
        if (selectedUnit === 'ml' && baseUnit === 'L') quantity = quantity / 1000;
        if (selectedUnit === 'L' && baseUnit === 'ml') quantity = quantity * 1000;

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
        const rawValue = batchTimesCooked?.value;
        const errorDiv = document.getElementById('batchError');

        if (!productId) { alert('Please select a product.'); return; }

        // Strict validation: must be a positive integer, reject '12-3', decimals, negatives
        const times = parseInt(rawValue);
        if (!rawValue || rawValue !== String(times) || times < 1 || !Number.isInteger(times)) {
            errorDiv.textContent = 'Please enter a valid positive whole number for "Times to Cook" (e.g. 1, 2, 3).';
            errorDiv.style.display = 'block';
            return;
        }

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

    // ===== START SHIFT (Bulk Stock-In) =====
    const startShiftModal = document.getElementById('startShiftModal');
    const endShiftModal = document.getElementById('endShiftModal');

    document.getElementById('openStartShift')?.addEventListener('click', () => {
        closeAll();
        startShiftModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeStartShift')?.addEventListener('click', closeAll);
    document.getElementById('cancelStartShift')?.addEventListener('click', closeAll);

    // Add more ingredient rows
    document.getElementById('addShiftRow')?.addEventListener('click', () => {
        const container = document.getElementById('shiftStockInRows');
        const firstRow = container.querySelector('.shift-stock-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.shift-ingredient-select').value = '';
        newRow.querySelector('.shift-quantity').value = '';
        newRow.querySelector('.shift-supplier').value = '';
        container.appendChild(newRow);
    });

    // Confirm start shift stock-in
    document.getElementById('confirmStartShift')?.addEventListener('click', async () => {
        const rows = document.querySelectorAll('.shift-stock-row');
        const errorDiv = document.getElementById('shiftError');
        const items = [];

        rows.forEach(row => {
            const ingredientId = row.querySelector('.shift-ingredient-select').value;
            const quantity = parseFloat(row.querySelector('.shift-quantity').value);
            const supplier = row.querySelector('.shift-supplier').value.trim();
            if (ingredientId && quantity > 0) {
                items.push({ ingredient_id: ingredientId, quantity, supplier });
            }
        });

        if (items.length === 0) {
            errorDiv.textContent = 'Please add at least one ingredient with a valid quantity.';
            errorDiv.style.display = 'block';
            return;
        }

        errorDiv.style.display = 'none';

        try {
            const res = await fetch('/kitchen/start-shift', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ items })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeAll();
                window.location.reload();
            } else {
                errorDiv.textContent = data.message || 'Failed to process stock-in.';
                errorDiv.style.display = 'block';
            }
        } catch (err) {
            errorDiv.textContent = 'Network error.';
            errorDiv.style.display = 'block';
        }
    });

    // ===== END SHIFT =====
    document.getElementById('openEndShift')?.addEventListener('click', () => {
        closeAll();
        endShiftModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeEndShift')?.addEventListener('click', closeAll);
    document.getElementById('cancelEndShift')?.addEventListener('click', closeAll);

    document.getElementById('confirmEndShift')?.addEventListener('click', async () => {
        try {
            const res = await fetch('/kitchen/end-shift', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeAll();
                window.location.reload();
            } else {
                alert(data.message || 'Failed to end shift.');
            }
        } catch (err) {
            alert('Network error.');
        }
    });

    // Overlay click to close
    overlay?.addEventListener('click', closeAll);
});


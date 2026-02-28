document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const overlay = document.getElementById('overlay');
    const recipeManagerModal = document.getElementById('recipeManagerModal');
    const addBatchModal = document.getElementById('addBatchModal');
    const wasteModal = document.getElementById('wasteModal');

    // ===== SEARCHABLE SELECTS =====
    function initSearchableSelects(container = document) {
        container.querySelectorAll('select.searchable-select').forEach(sel => {
            if (sel.dataset.sdInit) return;
            sel.dataset.sdInit = '1';
            sel.style.display = 'none';

            const wrapper = document.createElement('div');
            wrapper.className = 'searchable-dropdown';

            const display = document.createElement('div');
            display.className = 'sd-display placeholder';
            display.innerHTML = `<span>${sel.options[0]?.text || 'Select...'}</span><i class="fa-solid fa-chevron-down" style="font-size:0.7rem; color:#999;"></i>`;

            const panel = document.createElement('div');
            panel.className = 'sd-panel';

            const search = document.createElement('input');
            search.className = 'sd-search';
            search.placeholder = 'Type to search...';

            const optionsList = document.createElement('div');
            optionsList.className = 'sd-options';

            function buildOptions(filter = '') {
                optionsList.innerHTML = '';
                let count = 0;
                Array.from(sel.options).forEach((opt, i) => {
                    if (i === 0) return;
                    if (filter && !opt.text.toLowerCase().includes(filter.toLowerCase())) return;
                    count++;
                    const div = document.createElement('div');
                    div.className = 'sd-option' + (sel.value === opt.value ? ' selected' : '');
                    div.textContent = opt.text;
                    div.addEventListener('click', () => {
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        display.querySelector('span').textContent = opt.text;
                        display.classList.remove('placeholder');
                        panel.classList.remove('open');
                        search.value = '';
                    });
                    optionsList.appendChild(div);
                });
                if (count === 0) {
                    optionsList.innerHTML = '<div class="sd-no-results">No results found</div>';
                }
            }

            search.addEventListener('input', () => buildOptions(search.value));

            display.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.sd-panel.open').forEach(p => { if (p !== panel) p.classList.remove('open'); });
                panel.classList.toggle('open');
                if (panel.classList.contains('open')) {
                    buildOptions();
                    setTimeout(() => search.focus(), 50);
                }
            });

            panel.appendChild(search);
            panel.appendChild(optionsList);
            wrapper.appendChild(display);
            wrapper.appendChild(panel);
            sel.parentNode.insertBefore(wrapper, sel);
            wrapper.appendChild(sel);
        });
    }

    initSearchableSelects();

    // Close all dropdown panels on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.sd-panel.open').forEach(p => p.classList.remove('open'));
    });

    // ===== CUSTOM TOAST =====
    function showToast(message, type = 'error') {
        const toast = document.getElementById('kitchenToast');
        const msgEl = document.getElementById('kitchenToastMessage');
        if (!toast || !msgEl) return;
        toast.className = 'kitchen-toast' + (type === 'success' ? ' toast-success' : ' toast-error');
        msgEl.textContent = message;
        toast.style.display = 'flex';
        toast.style.animation = 'none';
        toast.offsetHeight;
        toast.style.animation = '';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    // ===== CUSTOM CONFIRM =====
    function showConfirm(message, title = 'Confirm Action') {
        return new Promise((resolve) => {
            const modal = document.getElementById('customConfirmModal');
            const msgEl = document.getElementById('customConfirmMessage');
            const titleEl = document.getElementById('customConfirmTitle');
            const okBtn = document.getElementById('customConfirmOk');
            const cancelBtn = document.getElementById('customConfirmCancel');
            const closeBtn = document.getElementById('customConfirmClose');
            if (!modal) { resolve(window.confirm(message)); return; }

            titleEl.textContent = title;
            msgEl.textContent = message;
            modal.classList.add('active');

            function cleanup(result) {
                modal.classList.remove('active');
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                closeBtn.removeEventListener('click', onCancel);
                resolve(result);
            }
            function onOk() { cleanup(true); }
            function onCancel() { cleanup(false); }

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            closeBtn.addEventListener('click', onCancel);
        });
    }

    // ===== HELPERS =====
    const openOverlay = () => overlay?.classList.add('show');
    const closeOverlay = () => overlay?.classList.remove('show');

    function closeAll() {
        recipeManagerModal?.classList.remove('active');
        addBatchModal?.classList.remove('active');
        wasteModal?.classList.remove('active');
        document.getElementById('startShiftModal')?.classList.remove('active');
        document.getElementById('endShiftModal')?.classList.remove('active');
        document.getElementById('customConfirmModal')?.classList.remove('active');
        closeOverlay();
    }

    // ===== RECIPE MANAGER =====
    document.getElementById('openRecipeManager')?.addEventListener('click', () => {
        closeAll();
        recipeManagerModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeRecipeManager')?.addEventListener('click', closeAll);

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

                list.querySelectorAll('.recipe-qty').forEach(qty => {
                    qty.addEventListener('input', function() {
                        const saveBtn = this.closest('.recipe-item').querySelector('.recipe-save-btn');
                        saveBtn.style.display = this.textContent.trim() !== this.dataset.original ? 'inline-flex' : 'none';
                    });
                });

                list.querySelectorAll('.recipe-save-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.dataset.id;
                        const item = this.closest('.recipe-item');
                        let qty = parseFloat(item.querySelector('.recipe-qty').textContent.trim());
                        const isConverted = item.dataset.converted === 'true';

                        if (isNaN(qty)) { showToast('Invalid quantity.'); return; }
                        if (isConverted) qty = qty / 1000;

                        try {
                            const res = await fetch(`/recipes/${id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                body: JSON.stringify({ quantity: qty })
                            });
                            if (res.ok) {
                                document.getElementById('recipeProductSelect').dispatchEvent(new Event('change'));
                            } else { showToast('Failed to save.'); }
                        } catch (err) { showToast('Error saving.'); }
                    });
                });

                list.querySelectorAll('.recipe-delete-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.dataset.id;
                        const confirmed = await showConfirm('Remove this ingredient from recipe?', 'Remove Ingredient');
                        if (!confirmed) return;
                        try {
                            const res = await fetch(`/recipes/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                            });
                            if (res.ok) {
                                document.getElementById('recipeProductSelect').dispatchEvent(new Event('change'));
                            } else { showToast('Failed to remove.'); }
                        } catch (err) { showToast('Error removing ingredient.'); }
                    });
                });
            }
        } catch (err) {
            list.innerHTML = '<p style="color:#dc3545;">Failed to load recipes.</p>';
        }
    });

    document.getElementById('recipeIngredientSelect')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const unit = opt?.dataset?.unit || 'kg';
        const unitSelect = document.getElementById('recipeUnitSelect');
        if (unitSelect) unitSelect.value = unit;
    });

    document.getElementById('addRecipeIngredientForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const productId = document.getElementById('recipeProductSelect').value;
        const ingredientId = document.getElementById('recipeIngredientSelect').value;
        let quantity = parseFloat(document.getElementById('recipeQuantity').value);
        const selectedUnit = document.getElementById('recipeUnitSelect')?.value || 'kg';

        if (!productId || !ingredientId || !quantity) { showToast('Please fill all fields.'); return; }

        const ingOpt = document.getElementById('recipeIngredientSelect').options[document.getElementById('recipeIngredientSelect').selectedIndex];
        const baseUnit = ingOpt?.dataset?.unit || selectedUnit;

        if (selectedUnit === 'g' && baseUnit === 'kg') quantity = quantity / 1000;
        if (selectedUnit === 'kg' && baseUnit === 'g') quantity = quantity * 1000;
        if (selectedUnit === 'ml' && baseUnit === 'L') quantity = quantity / 1000;
        if (selectedUnit === 'L' && baseUnit === 'ml') quantity = quantity * 1000;

        try {
            const res = await fetch('/recipes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId, ingredient_id: ingredientId, quantity })
            });

            if (res.ok) {
                document.getElementById('recipeProductSelect').dispatchEvent(new Event('change'));
                document.getElementById('recipeQuantity').value = '';
                document.getElementById('recipeIngredientSelect').value = '';
            } else {
                const data = await res.json();
                showToast(data.message || 'Failed to add ingredient.');
            }
        } catch (err) { showToast('Error adding ingredient.'); }
    });

    // ===== COOK BATCH =====
    document.getElementById('openAddBatch')?.addEventListener('click', () => {
        closeAll();
        addBatchModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeAddBatch')?.addEventListener('click', closeAll);
    document.getElementById('cancelBatch')?.addEventListener('click', closeAll);

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

                if (unit === 'kg' && needed < 1) {
                    needed = needed * 1000;
                    available = available * 1000;
                    unit = 'g';
                }

                const neededStr = parseFloat(needed.toFixed(3));
                const availableStr = parseFloat(available.toFixed(3));
                const isInsufficient = available < needed;

                return `
                    <div class="preview-item">
                        <span>${r.ingredient?.name || 'Unknown'}</span>
                        <span class="${isInsufficient ? 'insufficient' : ''}">${neededStr}${unit} needed (${availableStr}${unit} available)</span>
                    </div>
                `;
            }).join('');

            let totalCost = 0;
            recipes.forEach(r => {
                totalCost += (r.quantity * times) * parseFloat(r.ingredient?.cost_per_unit || 0);
            });
            const costValueEl = document.getElementById('batchCostValue');
            if (costValueEl) costValueEl.textContent = '₱' + totalCost.toFixed(2);

        } catch (err) {
            list.innerHTML = '<p style="color:#dc3545;">Failed to load recipe.</p>';
        }
    }

    batchProductSelect?.addEventListener('change', updateBatchPreview);
    batchTimesCooked?.addEventListener('input', updateBatchPreview);

    document.getElementById('confirmBatch')?.addEventListener('click', async () => {
        const productId = batchProductSelect?.value;
        const rawValue = batchTimesCooked?.value;
        const errorDiv = document.getElementById('batchError');

        if (!productId) { showToast('Please select a product.'); return; }

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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId, times_cooked: times })
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

    // ===== STATUS UPDATES =====
    document.querySelectorAll('.status-btn:not(.waste-btn):not(.cancel-btn)').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const status = this.dataset.status;
            if (!status) return;

            try {
                const res = await fetch(`/kitchen/update-status/${id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ status })
                });
                if (res.ok) { window.location.reload(); }
                else { showToast('Failed to update status.'); }
            } catch (err) { showToast('Network error.'); }
        });
    });

    // ===== CANCEL BATCH =====
    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const confirmed = await showConfirm('Are you sure you want to cancel this batch? Ingredients will be refunded.', 'Cancel Batch');
            if (!confirmed) return;

            const id = this.dataset.id;
            try {
                const res = await fetch(`/kitchen/cancel/${id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data.success) { window.location.reload(); }
                else { showToast(data.message || 'Failed to cancel batch.'); }
            } catch (err) { showToast('Network error.'); }
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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ status: 'wasted', waste_reason: reason || 'No reason provided' })
            });
            if (res.ok) { closeAll(); window.location.reload(); }
            else { showToast('Failed to mark as wasted.'); }
        } catch (err) { showToast('Network error.'); }
    });

    // ===== START SHIFT =====
    const startShiftModal = document.getElementById('startShiftModal');
    const endShiftModal = document.getElementById('endShiftModal');

    document.getElementById('openStartShift')?.addEventListener('click', () => {
        closeAll();
        startShiftModal.classList.add('active');
        openOverlay();
    });
    document.getElementById('closeStartShift')?.addEventListener('click', closeAll);
    document.getElementById('cancelStartShift')?.addEventListener('click', closeAll);

    // Add more ingredient rows + re-init searchable selects on new row
    document.getElementById('addShiftRow')?.addEventListener('click', () => {
        const container = document.getElementById('shiftStockInRows');
        const firstRow = container.querySelector('.shift-stock-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.shift-ingredient-select').value = '';
        newRow.querySelector('.shift-quantity').value = '';
        newRow.querySelector('.shift-supplier').value = '';
        container.appendChild(newRow);
        initSearchableSelects(container); // re-init for the new row's select
    });

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
            if (res.ok && data.success) { closeAll(); window.location.reload(); }
            else {
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
            if (res.ok && data.success) { closeAll(); window.location.reload(); }
            else { showToast(data.message || 'Failed to end shift.'); }
        } catch (err) { showToast('Network error.'); }
    });

    // ===== OVERLAY =====
    overlay?.addEventListener('click', closeAll);
});
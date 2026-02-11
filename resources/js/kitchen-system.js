document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const overlay = document.getElementById('overlay');
    const recipeManagerModal = document.getElementById('recipeManagerModal');
    const addBatchModal = document.getElementById('addBatchModal');

    // Helpers
    const openOverlay = () => overlay?.classList.add('show');
    const closeOverlay = () => overlay?.classList.remove('show');
    function closeAll() {
        recipeManagerModal?.classList.remove('active');
        addBatchModal?.classList.remove('active');
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
                list.innerHTML = recipes.map(r => `
                    <div class="recipe-item">
                        <span>${r.ingredient?.name || 'Unknown'} — ${r.quantity} ${r.ingredient?.unit || ''}</span>
                    </div>
                `).join('');
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
                    quantity: quantity,
                    batch_sizes_id: 1 // default batch size
                })
            });

            if (res.ok) {
                // Reload recipes
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
                const needed = (r.quantity * times).toFixed(2);
                const available = r.ingredient?.stock || 0;
                const unit = r.ingredient?.unit || '';
                const isInsufficient = available < needed;
                return `
                    <div class="preview-item">
                        <span>${r.ingredient?.name || 'Unknown'}</span>
                        <span class="${isInsufficient ? 'insufficient' : ''}">${needed}${unit} needed (${available}${unit} available)</span>
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

    // ===== STATUS UPDATES =====
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const status = this.dataset.status;

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

    // Overlay click to close
    overlay?.addEventListener('click', closeAll);
});

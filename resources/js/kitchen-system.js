document.addEventListener('DOMContentLoaded', () => {
    // ==================== DOM ELEMENTS ====================
    const addItemButton = document.querySelector('.add-item-button');
    const addBatchModal = document.querySelector('.add-batch-modal');
    const productCardContainer = document.querySelector('.product-card-container');
    const queuedContainer = document.querySelector('.queued-container');
    const cookingContainer = document.querySelector('.cooking-container');
    const doneContainer = document.querySelector('.done-container');
    const quantityInput = addBatchModal.querySelector('.batch-quantity-container input');
    const timeInput = addBatchModal.querySelector('.batch-time-container input');
    const recipeModal = document.querySelector('.recipe-manager-modal');
    const manageBtn = document.querySelector('.manage-recipe-button');
    const closeBtn = document.getElementById('closeRecipeModal');
    const overlay = document.getElementById('overlay');
    const batchSearchInput = document.getElementById('batchSearchInput');

    // Recipe manager elements
    const productSelect = document.getElementById('productSelect');
    const batchSizeSelect = document.getElementById('batchSizeSelect');
    const recipeList = document.getElementById('recipeList');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Cache all ingredients from the DB
    let allIngredients = [];
    fetch('/ingredients/all')
        .then(res => res.json())
        .then(data => allIngredients = data)
        .catch(err => console.error('Failed to load ingredients:', err));

    // ==================== MODAL OPEN/CLOSE ====================
    manageBtn.addEventListener('click', () => {
        recipeModal.classList.add('active');
        overlay.classList.add('show');
    });
    closeBtn.addEventListener('click', () => {
        recipeModal.classList.remove('active');
        overlay.classList.remove('show');
    });

    addItemButton.addEventListener('click', () => {
        addBatchModal.classList.add('active');
        overlay.classList.add('show');
        quantityInput.value = 1;
        timeInput.value = 30;
    });

    overlay.addEventListener('click', () => {
        addBatchModal.classList.remove('active');
        recipeModal.classList.remove('active');
        overlay.classList.remove('show');
    });

    // ==================== RECIPE MANAGER ====================

    // When product or batch size changes, load existing recipes from DB
    productSelect.addEventListener('change', loadRecipes);
    batchSizeSelect.addEventListener('change', loadRecipes);

    async function loadRecipes() {
        const productId = productSelect.value;
        const batchSize = batchSizeSelect.value;
        recipeList.innerHTML = '';

        if (!productId || !batchSize) return;

        try {
            const response = await fetch(`/recipes/${productId}?batch_size=${batchSize}`);
            const recipes = await response.json();

            recipes.forEach(recipe => {
                addIngredientRow(recipe.ingredient_id, recipe.quantity, recipe.id);
            });
        } catch (err) {
            console.error('Failed to load recipes:', err);
        }
    }

    // Add ingredient row (blank or pre-filled)
    function addIngredientRow(ingredientId = '', quantity = '0.01', recipeId = null) {
        const div = document.createElement('div');
        div.classList.add('recipe-item');
        if (recipeId) div.dataset.recipeId = recipeId;

        let options = '<option value="">Select Ingredient</option>';
        allIngredients.forEach(i => {
            const selected = (i.id == ingredientId) ? 'selected' : '';
            options += `<option value="${i.id}" ${selected}>${i.name} (${i.unit})</option>`;
        });

        div.innerHTML = `
            <select class="recipe-ingredient-select">${options}</select>
            <input type="number" class="recipe-quantity-input" min="0.01" step="0.01" value="${quantity}" placeholder="Qty">
            <button class="delete-recipe-btn">✕</button>
        `;
        recipeList.appendChild(div);
    }

    document.getElementById('addIngredientBtn').addEventListener('click', () => {
        addIngredientRow();
    });

    // Delete ingredient from recipe list
    recipeList.addEventListener('click', async (e) => {
        if (!e.target.classList.contains('delete-recipe-btn')) return;
        const row = e.target.closest('.recipe-item');
        const recipeId = row.dataset.recipeId;

        // If it's a saved recipe, delete from DB
        if (recipeId) {
            try {
                await fetch(`/recipes/${recipeId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
            } catch (err) {
                console.error('Failed to delete recipe:', err);
            }
        }
        row.remove();
    });

    // Save all recipe rows
    document.getElementById('saveRecipesBtn').addEventListener('click', async () => {
        const productId = productSelect.value;
        const batchSize = batchSizeSelect.value;

        if (!productId || !batchSize) {
            return alert('Please select a product and batch size first.');
        }

        const items = recipeList.querySelectorAll('.recipe-item');
        if (items.length === 0) {
            return alert('Please add at least one ingredient.');
        }

        let success = true;
        for (let item of items) {
            const ingredientId = item.querySelector('.recipe-ingredient-select')?.value;
            const quantity = item.querySelector('.recipe-quantity-input')?.value;
            if (!ingredientId || !quantity) continue;

            try {
                const response = await fetch('/recipes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        batch_size: parseInt(batchSize),
                        ingredient_id: ingredientId,
                        quantity: parseFloat(quantity)
                    })
                });

                if (!response.ok) {
                    const err = await response.json();
                    alert('Error saving recipe: ' + (err.message || 'Unknown error'));
                    success = false;
                    break;
                }
            } catch (err) {
                console.error('Error:', err);
                success = false;
                break;
            }
        }

        if (success) {
            alert('Recipes saved successfully!');
            loadRecipes(); // Reload to get recipe IDs
        }
    });

    // ==================== ADD BATCH (QUEUE) ====================

    // Search filter for product cards in Add Batch modal
    if (batchSearchInput) {
        batchSearchInput.addEventListener('input', () => {
            const query = batchSearchInput.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.querySelector('.product-name span').textContent.toLowerCase();
                card.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    // Click "Add Batch" on a product card
    productCardContainer.addEventListener('click', e => {
        if (!e.target.classList.contains('add-batch-btn')) return;
        const card = e.target.closest('.product-card');
        if (!card) return;

        const productId = card.dataset.productId;
        const productName = card.querySelector('.product-name span').textContent;
        const batchSelect = card.querySelector('.batch-size-select');
        const batchSize = batchSelect.value;
        const batchText = batchSelect.options[batchSelect.selectedIndex]?.text;
        const quantity = quantityInput.value || 1;
        const time = timeInput.value || 30;

        if (!batchSize) {
            alert('Please select a batch size first.');
            return;
        }

        // Create queue card
        const wrapper = document.createElement('div');
        wrapper.classList.add('wrapper');
        wrapper.dataset.productId = productId;
        wrapper.dataset.batchSize = batchSize;
        wrapper.dataset.quantity = quantity;

        wrapper.innerHTML = `
            <div class="product-name"><span>${productName}</span></div>
            <div class="batch-desc" style="font-size: 0.8em; color: #666;"><span>${batchText}</span></div>
            <div class="batch-amount"><span>${quantity} Batch${quantity > 1 ? 'es' : ''}</span></div>
            <div class="time"><span>${time} minute${time > 1 ? 's' : ''}</span></div>
            <div class="navigation-buttons">
                <button class="start-button">start</button>
                <button class="cancel-button">discard</button>
            </div>
        `;
        queuedContainer.appendChild(wrapper);

        // Reset
        quantityInput.value = 1;
        timeInput.value = 30;
    });

    // ==================== QUEUE → COOKING → DONE ====================

    // Queue: Start or Discard
    queuedContainer.addEventListener('click', async e => {
        const wrapper = e.target.closest('.wrapper');
        if (!wrapper) return;

        if (e.target.classList.contains('cancel-button')) {
            wrapper.remove();
            return;
        }

        if (e.target.classList.contains('start-button')) {
            const productId = wrapper.dataset.productId;
            const batchSize = wrapper.dataset.batchSize;
            const quantity = wrapper.dataset.quantity;

            // Call API to deduct ingredients
            try {
                const response = await fetch('/kitchen/produce', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        product_id: parseInt(productId),
                        batch_size: parseInt(batchSize),
                        quantity: parseInt(quantity)
                    })
                });

                const result = await response.json();

                if (!response.ok) {
                    alert('Error: ' + result.message);
                    return;
                }

                // Success — move to Cooking
                const productName = wrapper.querySelector('.product-name span').textContent;
                const batchDesc = wrapper.querySelector('.batch-desc span').textContent;
                const batchText = wrapper.querySelector('.batch-amount span').textContent;
                const timeText = wrapper.querySelector('.time span').textContent;
                let timeMinutes = parseInt(timeText) || 0;

                const cookingWrapper = document.createElement('div');
                cookingWrapper.classList.add('wrapper');
                cookingWrapper.innerHTML = `
                    <div class="product-name"><span>${productName}</span></div>
                    <div class="batch-desc" style="font-size: 0.8em; color: #666;"><span>${batchDesc}</span></div>
                    <div class="batch-amount"><span>${batchText}</span></div>
                    <div class="time"><span class="timer">${timeMinutes}:00</span></div>
                    <div class="navigation-buttons">
                        <button class="complete-button">complete</button>
                        <button class="cancel-button">discard</button>
                    </div>
                `;
                cookingContainer.appendChild(cookingWrapper);
                wrapper.remove();

                // Start countdown timer
                const timerSpan = cookingWrapper.querySelector('.timer');
                let totalSeconds = timeMinutes * 60;
                const interval = setInterval(() => {
                    if (totalSeconds <= 0) {
                        clearInterval(interval);
                        moveToDone(cookingWrapper, productName, batchDesc, batchText, timeMinutes);
                        return;
                    }
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;
                    timerSpan.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                    totalSeconds--;
                }, 1000);

            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while starting production.');
            }
        }
    });

    // Cooking: Complete or Discard
    cookingContainer.addEventListener('click', e => {
        const wrapper = e.target.closest('.wrapper');
        if (!wrapper) return;

        if (e.target.classList.contains('cancel-button')) {
            wrapper.remove();
            return;
        }

        if (e.target.classList.contains('complete-button')) {
            const productName = wrapper.querySelector('.product-name span').textContent;
            const batchDesc = wrapper.querySelector('.batch-desc span')?.textContent || '';
            const batchText = wrapper.querySelector('.batch-amount span').textContent;
            const timeText = wrapper.querySelector('.time span').textContent;
            moveToDone(wrapper, productName, batchDesc, batchText, parseInt(timeText) || 0);
        }
    });

    function moveToDone(cookingWrapper, productName, batchDesc, batchText, timeMinutes) {
        const doneWrapper = document.createElement('div');
        doneWrapper.classList.add('wrapper');
        doneWrapper.innerHTML = `
            <div class="product-name"><span>${productName}</span></div>
            <div class="batch-desc" style="font-size: 0.8em; color: #666;"><span>${batchDesc}</span></div>
            <div class="batch-amount"><span>${batchText}</span></div>
            <div class="time"><span>${timeMinutes} minute${timeMinutes > 1 ? 's' : ''}</span></div>
            <div class="navigation-buttons">
                <button class="serve-button">serve</button>
            </div>
        `;
        doneContainer.appendChild(doneWrapper);
        cookingWrapper.remove();
    }

    // Done: Serve (remove)
    doneContainer.addEventListener('click', e => {
        if (e.target.classList.contains('serve-button')) {
            e.target.closest('.wrapper')?.remove();
        }
    });
});
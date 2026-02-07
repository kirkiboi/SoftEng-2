document.addEventListener('DOMContentLoaded', () => {
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
    const productSelect = document.getElementById('productSelect');
    const recipeList = document.querySelector('.recipe-list');

    let allIngredients = [];

    fetch('/ingredients/all')
        .then(res => res.json())
        .then(data => allIngredients = data);

    manageBtn.addEventListener('click', () => recipeModal.style.display = 'block');
    closeBtn.addEventListener('click', () => recipeModal.style.display = 'none');

    addItemButton.addEventListener('click', () => {
        addBatchModal.classList.add('active');
        overlay.classList.add('show');
        quantityInput.value = 1;
        timeInput.value = 30;
    });

    overlay.addEventListener('click', () => {
        addBatchModal.classList.remove('active');
        overlay.classList.remove('show');
    });

    productSelect.addEventListener('change', async () => {
        const productId = productSelect.value;
        if (!productId) return;
        const res = await fetch(`/recipes/${productId}`);
        const recipes = await res.json();
        recipeList.innerHTML = '';
        recipes.forEach(r => {
            const div = document.createElement('div');
            div.classList.add('recipe-item');
            div.innerHTML = `
                <span>${r.ingredient.name}</span>
                <input type="number" value="${r.quantity}" min="0.01" step="0.01" data-ingredient-id="${r.ingredient.id}">
                <button class="delete-recipe-btn">Delete</button>
            `;
            recipeList.appendChild(div);
        });
    });

    document.getElementById('addIngredientBtn').addEventListener('click', () => {
        const div = document.createElement('div');
        div.classList.add('recipe-item');
        let options = `<option value="">Select Ingredient</option>`;
        allIngredients.forEach(i => options += `<option value="${i.id}">${i.name}</option>`);
        div.innerHTML = `
            <select class="new-ingredient-select">${options}</select>
            <input type="number" min="0.01" step="0.01" value="0.1">
            <button class="delete-recipe-btn">Delete</button>
        `;
        recipeList.appendChild(div);
    });

    recipeList.addEventListener('click', e => {
        if(e.target.classList.contains('delete-recipe-btn')) e.target.parentElement.remove();
    });

    document.getElementById('saveRecipesBtn').addEventListener('click', async () => {
        const productId = productSelect.value;
        if(!productId) return alert('Select a product first');
        const items = recipeList.querySelectorAll('.recipe-item');
        for(let item of items){
            let ingredientId = item.querySelector('select')?.value || item.querySelector('input')?.dataset.ingredientId;
            let quantity = item.querySelector('input').value;
            if(!ingredientId || !quantity) continue;
            await fetch('/recipes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({product_id: productId, ingredient_id: ingredientId, quantity})
            });
        }
        alert('Recipes saved!');
    });

    productCardContainer.addEventListener('click', e => {
        if(!e.target.classList.contains('add-batch-btn')) return;
        const card = e.target.closest('.product-card');
        const productName = card.querySelector('.product-name span').textContent;
        const quantity = quantityInput.value || 1;
        const time = timeInput.value || 0;

        const wrapper = document.createElement('div');
        wrapper.classList.add('wrapper');
        wrapper.innerHTML = `
            <div class="product-name"><span>${productName}</span></div>
            <div class="batch-amount"><span>${quantity} Batch${quantity>1?'es':''}</span></div>
            <div class="time"><span>${time} minute${time>1?'s':''}</span></div>
            <div class="navigation-buttons">
                <button class="start-button">start</button>
                <button class="cancel-button">discard</button>
            </div>
        `;
        queuedContainer.appendChild(wrapper);
        quantityInput.value=1; timeInput.value=30;
    });

    queuedContainer.addEventListener('click', e => {
        const wrapper = e.target.closest('.wrapper');
        if(!wrapper) return;
        if(e.target.classList.contains('cancel-button')) wrapper.remove();
        if(e.target.classList.contains('start-button')){
            const productName = wrapper.querySelector('.product-name span').textContent;
            const batchText = wrapper.querySelector('.batch-amount span').textContent;
            const timeText = wrapper.querySelector('.time span').textContent;
            let timeMinutes = parseInt(timeText) || 0;

            const cookingWrapper = document.createElement('div');
            cookingWrapper.classList.add('wrapper');
            cookingWrapper.innerHTML = `
                <div class="product-name"><span>${productName}</span></div>
                <div class="batch-amount"><span>${batchText}</span></div>
                <div class="time"><span class="timer">${timeMinutes}:00</span></div>
                <div class="navigation-buttons">
                    <button class="complete-button">complete</button>
                    <button class="cancel-button">discard</button>
                </div>
            `;
            cookingContainer.appendChild(cookingWrapper);
            wrapper.remove();

            const timerSpan = cookingWrapper.querySelector('.timer');
            let totalSeconds = timeMinutes*60;
            const interval = setInterval(()=>{
                if(totalSeconds<=0){
                    clearInterval(interval);
                    const doneWrapper=document.createElement('div');
                    doneWrapper.classList.add('wrapper');
                    doneWrapper.innerHTML = `
                        <div class="product-name"><span>${productName}</span></div>
                        <div class="batch-amount"><span>${batchText}</span></div>
                        <div class="time"><span>${timeMinutes} minute${timeMinutes>1?'s':''}</span></div>
                        <div class="navigation-buttons">
                            <button class="serve-button">serve</button>
                        </div>
                    `;
                    doneContainer.appendChild(doneWrapper);
                    cookingWrapper.remove();
                    return;
                }
                const minutes=Math.floor(totalSeconds/60);
                const seconds=totalSeconds%60;
                timerSpan.textContent=`${minutes}:${seconds<10?'0':''}${seconds}`;
                totalSeconds--;
            },1000);
        }
    });

});

document.addEventListener('DOMContentLoaded', () => {
const addIngredientButton = document.querySelector(".add-ingredient-button");
const addIngredientParentContainer = document.querySelector(".floating-add-ingredient-container");
const addIngredientCancel = document.querySelector(".cancel-button");

const recordStockInContainer = document.querySelector(".record-stock-in-container");
const recordStockInButton = document.querySelector(".record-stock-in-button");

    recordStockInButton.addEventListener("click", ()=>{
        recordStockInContainer.classList.toggle("active");
    });
    addIngredientButton.addEventListener("click", ()=>{
        addIngredientParentContainer.classList.toggle("active");        

    });
    addIngredientCancel.addEventListener("click", ()=>{
        addIngredientParentContainer.classList.toggle("active");        
    });
});
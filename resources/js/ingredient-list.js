document.addEventListener('DOMContentLoaded', () => {
const addIngredientButton = document.querySelector(".add-ingredient-button");
const addIngredientParentContainer = document.querySelector(".floating-add-ingredient-container");
const addIngredientCancel = document.querySelector(".cancel-button");

    addIngredientButton.addEventListener("click", ()=>{
        addIngredientParentContainer.classList.toggle("active");        

    });
    addIngredientCancel.addEventListener("click", ()=>{
        addIngredientParentContainer.classList.toggle("active");        
    });
});
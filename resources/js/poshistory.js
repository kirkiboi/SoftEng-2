const dateBtn = document.querySelector(".date-filter-button");
const dateInput = document.getElementById("dateInput");

const today = new Date().toISOString().split("T")[0];
dateInput.value = today;

dateBtn.addEventListener("click", () => {
  dateInput.showPicker(); 
});

dateInput.addEventListener("change", () => {
  if (dateInput.value === today) {
    dateBtn.textContent = "Today";
  } else {
    const selected = new Date(dateInput.value);
    dateBtn.textContent = selected.toLocaleDateString();
  }
});
// JavaScript for junior_opdracht_3

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("newsletterForm");
    const emailInput = form.querySelector("#email");
    const alertMessage = form.querySelector(".newsletter_alert");
  
    form.addEventListener("submit", function (event) {
      event.preventDefault(); // Standaard formulier submit blokkeren
  
      // Reguliere expressie voor email validatie
      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  
      if (!emailPattern.test(emailInput.value)) {
        emailInput.parentElement.classList.add("invalid"); // Rode rand aan form toevoegen
      } else {
        form.classList.remove("invalid"); // Rode rand weghalen
        alertMessage.style.display = "none"; // Alert verbergen
        alert("Valid email"); // Alert tonen
        form.submit(); // Formulier verzenden als correct
      }
    });
  });
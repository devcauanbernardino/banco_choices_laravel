/**
 * Estado visual das matérias selecionadas.
 */
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("materiasForm");
  if (!form) {
    return;
  }
  form.querySelectorAll(".signup-materias-card input[type=\"checkbox\"]").forEach(function (input) {
    var card = input.closest(".signup-materias-card");
    if (!card) {
      return;
    }
    card.classList.toggle("is-selected", input.checked);
    input.addEventListener("change", function () {
      card.classList.toggle("is-selected", input.checked);
    });
  });
});

window.BaghetFilters = (function () {
  function getChecked(name) {
    return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`)).map((i) => i.value);
  }

  function applyPlastic() {
    const widths = getChecked("width");
    const colors = getChecked("color");
    let visible = 0;
    document.querySelectorAll(".product-card[data-category='plastic']").forEach((card) => {
      const wOk = !widths.length || widths.includes(card.dataset.width);
      const cOk = !colors.length || colors.includes(card.dataset.color);
      const show = wOk && cOk;
      card.hidden = !show;
      if (show) visible += 1;
    });
    const countEl = document.querySelector("[data-results-count]");
    if (countEl) countEl.textContent = String(visible);
  }

  function applyHardware() {
    const types = getChecked("type");
    let visible = 0;
    document.querySelectorAll(".product-card[data-category='hardware']").forEach((card) => {
      const show = !types.length || types.includes(card.dataset.type);
      card.hidden = !show;
      if (show) visible += 1;
    });
    const countEl = document.querySelector("[data-results-count]");
    if (countEl) countEl.textContent = String(visible);
  }

  function bind(catalog) {
    const form = document.querySelector("[data-filters]");
    if (!form) return;
    if (!form.dataset.bound) {
      form.dataset.bound = "1";
      form.addEventListener("change", () => {
        const kind = document.body.dataset.catalog;
        if (kind === "plastic") applyPlastic();
        else applyHardware();
      });
    }
    if (catalog === "plastic") applyPlastic();
    else applyHardware();
  }

  return { bind, applyPlastic, applyHardware };
})();

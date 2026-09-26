(function () {
  const { t } = window.BaghetI18n;

  function formatPrice(n) {
    return Number(n).toFixed(2) + " $";
  }

  function stockBadge(stock) {
    if (stock > 0) {
      return `<span class="badge badge--ok">${t("in_stock")}</span>`;
    }
    return `<span class="badge badge--off">${t("out_stock")}</span>`;
  }

  function profileVisual(product) {
    const tone = product.tone || "#b08d57";
    return `
      <div class="product-visual" style="--tone:${tone}" aria-hidden="true">
        <div class="profile-bar profile-bar--outer"></div>
        <div class="profile-bar profile-bar--mid"></div>
        <div class="profile-bar profile-bar--inner"></div>
      </div>`;
  }

  function hardwareVisual(type) {
    return `
      <div class="product-visual product-visual--hw" data-hw="${type}" aria-hidden="true">
        <div class="hw-icon"></div>
      </div>`;
  }

  function renderPlasticCard(p) {
    return `
      <a class="product-card" href="product.html?id=${p.id}" data-category="plastic" data-width="${p.width}" data-color="${p.color}">
        ${profileVisual(p)}
        <div class="product-meta">
          <div class="product-meta__top">
            <span class="sku">${p.sku}</span>
            ${stockBadge(p.stock)}
          </div>
          <div class="product-meta__bottom">
            <span class="price">${formatPrice(p.price)}</span>
            <span class="unit">${t("price_m")}</span>
          </div>
        </div>
      </a>`;
  }

  function renderHardwareCard(p) {
    return `
      <a class="product-card" href="product.html?id=${p.id}" data-category="hardware" data-type="${p.type}">
        ${hardwareVisual(p.type)}
        <div class="product-meta">
          <div class="product-meta__top">
            <span class="sku">${t(p.nameKey)}</span>
            ${stockBadge(p.stock)}
          </div>
          <div class="product-meta__bottom">
            <span class="price">${formatPrice(p.price)}</span>
            <span class="unit">${t("price_pack")} · ${p.sku}</span>
          </div>
        </div>
      </a>`;
  }

  function renderCatalog(kind) {
    const grid = document.querySelector("[data-product-grid]");
    if (!grid || !window.BAGHET_PRODUCTS) return;
    const list = window.BAGHET_PRODUCTS[kind] || [];
    grid.innerHTML = list
      .map((p) => (kind === "plastic" ? renderPlasticCard(p) : renderHardwareCard(p)))
      .join("");
  }

  function renderProductPage() {
    const root = document.querySelector("[data-product-page]");
    if (!root) return;
    const id = new URLSearchParams(window.location.search).get("id");
    const product = id ? window.findProduct(id) : null;
    if (!product) {
      root.innerHTML = `<p class="empty">${t("back_catalog")}</p>`;
      return;
    }

    const isPlastic = Boolean(product.width);
    const backHref = isPlastic ? "plastic.html" : "hardware.html";
    const title = isPlastic ? `${t("product_plastic")}: ${product.sku}` : t(product.nameKey);
    const visual = isPlastic ? profileVisual(product) : hardwareVisual(product.type);
    const unit = isPlastic ? t("price_m") : t("price_pack");

    let details = "";
    if (isPlastic) {
      details = `
        <li><span>${t("sku")}</span><strong>${product.sku}</strong></li>
        <li><span>${t("width_label")}</span><strong>${t("w_" + product.width.replace("-", "_"))}</strong></li>
        <li><span>${t("color_label")}</span><strong>${t("color_" + product.color)}</strong></li>
        <li><span>${t("stock_label")}</span><strong>${product.stock} ${t("pcs")}</strong></li>`;
    } else {
      details = `
        <li><span>${t("sku")}</span><strong>${product.sku}</strong></li>
        <li><span>${t("type_label")}</span><strong>${t("type_" + product.type)}</strong></li>
        <li><span>${t("stock_label")}</span><strong>${product.stock} ${t("pcs")}</strong></li>`;
    }

    root.innerHTML = `
      <a class="back-link" href="${backHref}">← <span data-i18n="back_catalog">${t("back_catalog")}</span></a>
      <div class="product-detail">
        <div class="product-detail__media">${visual}</div>
        <div class="product-detail__info">
          <p class="eyebrow">${isPlastic ? t("product_plastic") : t("product_hardware")}</p>
          <h1>${title}</h1>
          <div class="product-detail__price-row">
            <span class="price price--lg">${formatPrice(product.price)}</span>
            <span class="unit">${unit}</span>
            ${stockBadge(product.stock)}
          </div>
          <ul class="spec-list">${details}</ul>
          <button type="button" class="btn btn--primary" data-add-request ${product.stock === 0 ? "disabled" : ""}>
            <span data-i18n="add_request">${t("add_request")}</span>
          </button>
          <p class="hint" data-i18n="add_request_hint">${t("add_request_hint")}</p>
        </div>
      </div>`;

    const btn = root.querySelector("[data-add-request]");
    if (btn) {
      btn.addEventListener("click", () => {
        btn.classList.add("is-pulse");
        alert(t("add_request_hint"));
        setTimeout(() => btn.classList.remove("is-pulse"), 400);
      });
    }
  }

  function bindNav() {
    const toggle = document.querySelector("[data-nav-toggle]");
    const nav = document.querySelector("[data-nav]");
    if (toggle && nav) {
      toggle.addEventListener("click", () => {
        const open = nav.classList.toggle("is-open");
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
      });
    }
    document.querySelectorAll(".lang-btn").forEach((btn) => {
      btn.addEventListener("click", () => window.BaghetI18n.setLang(btn.dataset.lang));
    });
  }

  function refreshDynamic() {
    const catalog = document.body.dataset.catalog;
    if (catalog === "plastic" || catalog === "hardware") {
      renderCatalog(catalog);
      window.BaghetFilters.bind(catalog);
    }
    if (document.querySelector("[data-product-page]")) {
      renderProductPage();
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    bindNav();
    refreshDynamic();
    window.BaghetI18n.apply();
  });

  document.addEventListener("baghet:lang", () => {
    refreshDynamic();
  });
})();

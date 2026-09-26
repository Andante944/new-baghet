window.BAGHET_PRODUCTS = {
  plastic: [
    { id: "p-56-01-03", sku: "56-01-03", width: "4-5", color: "white", price: 3.22, stock: 48, tone: "#f5f0e6" },
    { id: "p-51-02-05", sku: "51-02-05", width: "4-5", color: "gold", price: 2.85, stock: 32, tone: "#c9a227" },
    { id: "p-32-01-05", sku: "32-01-05", width: "1-3", color: "brown", price: 2.21, stock: 64, tone: "#6b4423" },
    { id: "p-55-03-03", sku: "55-03-03", width: "4-5", color: "brown", price: 3.59, stock: 18, tone: "#5c4033" },
    { id: "p-60-01-01", sku: "60-01-01", width: "6-7", color: "gold", price: 4.22, stock: 22, tone: "#b8860b" },
    { id: "p-40-01-02", sku: "40-01-02", width: "4-5", color: "silver", price: 1.65, stock: 90, tone: "#c0c0c0" },
    { id: "p-85-01-02", sku: "85-01-02", width: "8-10", color: "black", price: 5.06, stock: 12, tone: "#1a1a1a" },
    { id: "p-52-02-03", sku: "52-02-03", width: "4-5", color: "gold", price: 3.37, stock: 41, tone: "#d4af37" },
    { id: "p-15-02-14", sku: "15-02-14", width: "1-3", color: "white", price: 0.47, stock: 120, tone: "#faf8f5" },
    { id: "p-56-01-05", sku: "56-01-05", width: "4-5", color: "brown", price: 3.22, stock: 27, tone: "#8b6914" },
    { id: "p-40-02-01", sku: "40-02-01", width: "4-5", color: "black", price: 1.98, stock: 55, tone: "#2c2c2c" },
    { id: "p-28-02-08", sku: "28-02-08", width: "1-3", color: "silver", price: 1.44, stock: 0, tone: "#a8a8a8" },
    { id: "p-70-01-02", sku: "70-01-02", width: "6-7", color: "white", price: 4.80, stock: 15, tone: "#ebe6dc" },
    { id: "p-90-03-01", sku: "90-03-01", width: "8-10", color: "gold", price: 6.10, stock: 8, tone: "#daa520" },
    { id: "p-22-01-04", sku: "22-01-04", width: "1-3", color: "red", price: 1.12, stock: 33, tone: "#8b2942" },
    { id: "p-45-02-09", sku: "45-02-09", width: "4-5", color: "blue", price: 2.44, stock: 19, tone: "#3d5a80" }
  ],
  hardware: [
    { id: "h-v7", sku: "F-07", type: "staples", price: 20.9, stock: 14, nameKey: "hw_v_staple" },
    { id: "h-v10", sku: "F-10", type: "staples", price: 22.5, stock: 9, nameKey: "hw_v_staple_10" },
    { id: "h-hang-1", sku: "F-21", type: "hangers", price: 4.5, stock: 80, nameKey: "hw_hanger" },
    { id: "h-hang-2", sku: "F-22", type: "hangers", price: 6.2, stock: 45, nameKey: "hw_hanger_d" },
    { id: "h-corner-1", sku: "F-31", type: "corners", price: 3.1, stock: 60, nameKey: "hw_corner" },
    { id: "h-corner-2", sku: "F-32", type: "corners", price: 3.8, stock: 0, nameKey: "hw_corner_metal" },
    { id: "h-clip", sku: "F-41", type: "other", price: 2.4, stock: 100, nameKey: "hw_clip" },
    { id: "h-wire", sku: "F-42", type: "other", price: 5.0, stock: 28, nameKey: "hw_wire" }
  ]
};

window.findProduct = function (id) {
  const all = [...window.BAGHET_PRODUCTS.plastic, ...window.BAGHET_PRODUCTS.hardware];
  return all.find((p) => p.id === id) || null;
};

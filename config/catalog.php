<?php

return [
    // Наценка к оптовой цене для розницы (витрина показывает её второй строкой).
    'retail_markup' => (float) env('CATALOG_RETAIL_MARKUP', 0.30),
];

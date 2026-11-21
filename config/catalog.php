<?php

return [
    // Наценка к оптовой цене для розницы (витрина показывает её второй строкой).
    'retail_markup' => (float) env('CATALOG_RETAIL_MARKUP', 0.30),

    // Длина рейки багета, м (используется для пересчёта цена/м → цена за рейку).
    'stick_length_m' => (float) env('CATALOG_STICK_LENGTH_M', 2.9),
];

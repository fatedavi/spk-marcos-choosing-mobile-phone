<?php

return [
    'price_mode' => env('PRICE_MODE', 'score_benefit'),

    'modes' => [
        'score_benefit' => 'Skor Harga 1-5 diperlakukan benefit (direkomendasikan)',
        'raw_cost' => 'Harga rupiah asli diperlakukan cost',
        'proposal_compat' => 'Reproduksi tabel proposal (Harga = 1, cost)',
    ],
];

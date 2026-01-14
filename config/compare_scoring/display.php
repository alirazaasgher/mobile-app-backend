<?php
return [
    'label' => 'Display',
    'specs' => [
        'type' => [
            'label' => 'Display Type',
            'weight' => 30,
            'scale' => [
                'ltpo amoled' => 10,
                'ltpo oled' => 10,
                'amoled' => 9,
                'oled' => 9,
                'super amoled' => 9,
                'dynamic amoled' => 9,
                'p-oled' => 8,
                'super retina xdr' => 10,
                'ips lcd' => 6,
                'lcd' => 5,
                'tft' => 3,
                'pls' => 5,
            ],
            'default' => 4,
        ],
        'refresh_rate' => [
            'label' => 'Refresh Rate',
            'weight' => 20, // Reduced from 25
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',   // before | after
                'space' => false           // true => add space, false => no space
            ],
            'scale' => [
                185 => 10,
                165 => 10,
                144 => 10,
                120 => 9,
                90 => 7,
                60 => 5,
            ],
            'default' => 5, // 60Hz is standard, not bad
        ],
        'pixel_density' => [
            'label' => 'Pixel Density (PPI)',
            'weight' => 20,
            'unit' => [
                'value' => '(PPI)',
                'position' => 'after',   // before | after
                'space' => true           // true => add space, false => no space
            ],
            'ranges' => [
                ['min' => 500, 'score' => 10],
                ['min' => 450, 'score' => 9],
                ['min' => 400, 'score' => 8],
                ['min' => 350, 'score' => 7],
                ['min' => 300, 'score' => 6],
                ['min' => 250, 'score' => 5],
                ['min' => 200, 'score' => 4],
            ],
            'default' => 3,
        ],
        'brightness_(peak)' => [
            'label' => 'Brightness (nits)',
            'weight' => 15,
            'unit' => [
                'value' => 'nits',
                'position' => 'after',   // before | after
                'space' => true           // true => add space, false => no space
            ],
            'ranges' => [
                // Ultra-flagship (2024-2025 flagships)
                ['min' => 6000, 'score' => 10],
                ['min' => 5000, 'score' => 10],
                ['min' => 4000, 'score' => 9],
                ['min' => 3000, 'score' => 9],
                // Premium flagship (2023-2024)
                ['min' => 2500, 'score' => 8],
                ['min' => 2000, 'score' => 8],
                // Standard flagship / High-end mid-range
                ['min' => 1500, 'score' => 7],
                ['min' => 1200, 'score' => 7],
                // Mid-range
                ['min' => 1000, 'score' => 6],
                ['min' => 800, 'score' => 5],
                // Budget
                ['min' => 600, 'score' => 4],
                ['min' => 400, 'score' => 3],
            ],
            'default' => 2,
        ],
        'brightness_(typical)' => [
            'label' => 'Typical Brightness (nits)',
            'weight' => 10, // Reduce peak to 10%, add this at 10%
            'unit' => [
                'value' => 'nits',
                'position' => 'after',   // before | after
                'space' => true           // true => add space, false => no space
            ],
            'ranges' => [
                ['min' => 1500, 'score' => 10], // Excellent for outdoor use
                ['min' => 1200, 'score' => 9],
                ['min' => 1000, 'score' => 8],
                ['min' => 800, 'score' => 7],
                ['min' => 600, 'score' => 6],
                ['min' => 500, 'score' => 5],
                ['min' => 400, 'score' => 4],
                ['min' => 300, 'score' => 3],
            ],
            'default' => 2,
        ],
        'size' => [
            'label' => 'Screen Size',
            'weight' => 5,
            'unit' => [
                'value' => 'inch',
                'position' => 'after',   // before | after
                'space' => true           // true => add space, false => no space
            ],
            'ranges' => [
                // Modern standard sizes
                ['min' => 6.5, 'max' => 6.9, 'score' => 9],
                ['min' => 6.1, 'max' => 6.49, 'score' => 8],
                ['min' => 5.8, 'max' => 6.09, 'score' => 7],

                // Compact phones
                ['min' => 5.4, 'max' => 5.79, 'score' => 7],

                // Large phones
                ['min' => 7.0, 'max' => 7.5, 'score' => 8],

                // Small budget phones
                ['min' => 5.0, 'max' => 5.39, 'score' => 5],

                // Very small or very large (edge cases)
                ['min' => 4.5, 'max' => 4.99, 'score' => 4],
            ],
            'default' => 6,
        ],
        'glass_protection' => [
            'label' => 'Glass Protection',
            'weight' => 10, // Increased - it's important
            'scale' => [
                // Latest generation (2024-2025)
                'victus 3' => 10,
                'ceramic shield (latest)' => 10,
                'dragon crystal glass 3' => 10,
                'gorilla glass armor 2' => 10,
                'gorilla glass armor' => 10,

                // Recent flagship (2023-2024)
                'victus 2' => 9,
                'ceramic shield' => 9,
                'kunlun glass' => 9,
                'dragon crystal glass 2' => 9,
                'gorilla glass victus+' => 9,
                'panda king kong glass' => 9,

                // Mid-tier flagship (2022-2023)
                'victus' => 8,
                'gorilla glass 7' => 8,
                'victus+' => 8,
                'dragon crystal glass' => 8,
                'schott xensation up' => 8,

                // Older flagship / Modern mid-range (2020-2021)
                'gorilla glass 6' => 7,
                'dragontrail pro' => 7,
                'asahi glass' => 7,

                // Mid-range (2018-2019)
                'gorilla glass 5' => 6,
                'dragontail glass' => 6,
                'schott xensation' => 6,
                'agc dragontrail' => 6,

                // Budget / Older phones (2016-2017)
                'gorilla glass 4' => 5,
                'gorilla glass 3' => 5,
                'panda glass' => 5,
                'dragontrail x' => 5,

                // Entry-level / Very old
                'gorilla glass 2' => 4,
                'gorilla glass' => 4,
                'soda-lime glass' => 3,
                'tempered glass' => 3,
                'aluminosilicate glass' => 3,

                // Generic/Unknown
                'toughened glass' => 2,
                'reinforced glass' => 2,
                'standard glass' => 1,
            ],
            'default' => 2, // No protection or unknown
        ],
    ],
];

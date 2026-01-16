<?php
return [
    'label' => 'Display',
    'specs' => [
        'type' => [
            'label' => 'Display Type',
            'weight' => 25, // Reduced from 30
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
            'weight' => 15, // Reduced from 20
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            'scale' => [
                185 => 10,
                165 => 10,
                144 => 10,
                120 => 9,
                90 => 7,
                60 => 5,
            ],
            'default' => 5,
        ],
        'pixel_density' => [
            'label' => 'Pixel Density (PPI)',
            'weight' => 15, // Reduced from 20
            'unit' => [
                'value' => '(PPI)',
                'position' => 'after',
                'space' => true
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
            'label' => 'Peak Brightness',
            'weight' => 10, // Reduced from 15
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => true
            ],
            'ranges' => [
                ['min' => 4500, 'score' => 10],
                ['min' => 3500, 'score' => 9],
                ['min' => 2500, 'score' => 8],
                ['min' => 1800, 'score' => 7],
                ['min' => 1300, 'score' => 6],
                ['min' => 1000, 'score' => 5],
                ['min' => 800, 'score' => 4],
                ['min' => 600, 'score' => 3],
            ],
            'default' => 2,
        ],
        'brightness_(typical)' => [
            'label' => 'Typical Brightness',
            'weight' => 8, // New spec
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => true
            ],
            'ranges' => [
                ['min' => 1500, 'score' => 10],
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
        'touch_sampling_rate' => [
            'label' => 'Touch Sampling Rate',
            'weight' => 7, // New spec - important for gaming
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            'scale' => [
                720 => 10,
                480 => 9,
                360 => 8,
                300 => 7,
                240 => 6,
                180 => 5,
                120 => 4,
            ],
            'default' => 3,
        ],
        'hdr_support' => [
            'label' => 'HDR Support',
            'weight' => 5, // New spec
            'scale' => [
                // Triple HDR (Premium flagship)
                'hdr10+, dolby vision, hdr vivid' => 10,
                'hdr10+, dolby vision, hdr10' => 10,
                'dolby vision, hdr vivid, hdr10' => 10,

                // Dual HDR with Dolby Vision (Top tier)
                'dolby vision, hdr10' => 10,
                'hdr10+, dolby vision' => 10,
                'dolby vision, hdr vivid' => 10,

                // Dual HDR combinations (High tier)
                'hdr10+, hdr vivid' => 9,
                'hdr10+, hdr10' => 9,
                'hdr vivid, hdr10' => 9,

                // Single premium HDR (Mid-high tier)
                'dolby vision' => 9,
                'hdr10+' => 8,
                'hdr vivid' => 8,

                // Standard HDR (Mid tier)
                'hdr10' => 7,
                'hdr' => 6,

                // Basic HD support (Low tier)
                'hd' => 3,

                // No HDR/HD
                'no' => 0,
            ],
            'default' => 0,
        ],
        'screen_ratio' => [
            'label' => 'Screen-to-Body Ratio',
            'weight' => 5, // New spec
            'unit' => [
                'value' => '%',
                'position' => 'after',
                'space' => false
            ],
            'ranges' => [
                ['min' => 92, 'score' => 10],
                ['min' => 90, 'score' => 9],
                ['min' => 87, 'score' => 8],
                ['min' => 84, 'score' => 7],
                ['min' => 80, 'score' => 6],
                ['min' => 75, 'score' => 5],
                ['min' => 70, 'score' => 4],
            ],
            'default' => 3,
        ],
        'size' => [
            'label' => 'Screen Size',
            'weight' => 3, // Reduced from 5
            'unit' => [
                'value' => 'inch',
                'position' => 'after',
                'space' => true
            ],
            'ranges' => [
                ['min' => 6.5, 'max' => 6.9, 'score' => 9],
                ['min' => 6.1, 'max' => 6.49, 'score' => 8],
                ['min' => 5.8, 'max' => 6.09, 'score' => 7],
                ['min' => 5.4, 'max' => 5.79, 'score' => 7],
                ['min' => 7.0, 'max' => 7.5, 'score' => 8],
                ['min' => 5.0, 'max' => 5.39, 'score' => 5],
                ['min' => 4.5, 'max' => 4.99, 'score' => 4],
            ],
            'default' => 6,
        ],
        'glass_protection' => [
            'label' => 'Glass Protection',
            'weight' => 7, // Reduced from 10
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
                'gorilla glass 7i' => 8,
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
            'default' => 2,
        ],
    ],
];

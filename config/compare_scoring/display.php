<?php
return [
    'label' => 'Display',
    'weights' => [
        'panel_quality' => 33,
        'brightness'    => 25,
        'motion'        => 20,
        'sharpness'     => 12,
        'ergonomics'    => 10,
    ],

    'categories' => [
        'panel_quality' => [
            'type' => [
                'label' => 'Display Type',
                'weight' => 20,
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
            'color_depth' => [
                'label' => 'Color Depth',
                'weight' => 30,
                'scale' => [
                    // ELITE: 2026 Content Creator Tier
                    '12-bit (true)' => 10,  // Pro cinema sensors/displays
                    '12-bit' => 9.5, // Often 10-bit + FRC in marketing
                    '10-bit + frc' => 9.5, // Effective 12-bit experience

                    // TOP TIER: The 2026 Flagship Baseline
                    '10-bit (true)' => 9,   // Standard for S26/iPhone 18 Pro
                    '10-bit' => 8.5,

                    // MID TIER: 2023/2024 Legacy Flagship
                    '8-bit + frc' => 7.5, // Effective 10-bit (Standard Mid-range)

                    // BUDGET / LEGACY:
                    '8-bit' => 5,   // Obsolete for HDR content
                    '6-bit + frc' => 3,   // Very low-end panels
                    'none' => 0,
                ],
                'default' => 5,
            ],
            'pwm' => [
                'label' => 'PWM Dimming Frequency',
                'weight' => 0,
                'unit' => [
                    'value' => 'Hz',
                    'position' => 'after',
                    'space' => false
                ],
                'ranges' => [
                    ['min' => 5000, 'score' => 10], // "Risk-Free" Tier
                    ['min' => 4320, 'score' => 9.5],
                    ['min' => 3840, 'score' => 9],  // Flagship Minimum
                    ['min' => 2160, 'score' => 7],  // Mid-range High
                    ['min' => 1920, 'score' => 6],
                    ['min' => 480, 'score' => 2],  // Obsolete/High Strain
                ],
                'default' => 3,

            ],
            'pwm_score_master' => [
                'label' => 'PWM Dimming Frequency',
                'weight' => 35,
                'unit' => [
                    'value' => 'Hz',
                    'position' => 'after',
                    'space' => false
                ],
                'ranges' => [
                    ['min' => 5000, 'score' => 10], // "Risk-Free" Tier
                    ['min' => 4320, 'score' => 9.5],
                    ['min' => 3840, 'score' => 9],  // Flagship Minimum
                    ['min' => 2160, 'score' => 7],  // Mid-range High
                    ['min' => 1920, 'score' => 6],
                    ['min' => 480, 'score' => 2],  // Obsolete/High Strain
                ],
                'default' => 3,

            ],
            'contrast_ratio' => [
                'label' => 'Contrast Ratio',
                'weight' => 0,
                'unit' => [
                    'value' => ':1',
                    'position' => 'after',
                    'space' => false
                ],
                'ranges' => [
                    // ELITE: Tandem OLED / MLA+ / MicroLED
                    ['min' => 8000000, 'score' => 10, 'label' => 'Elite (Tandem OLED)'],

                    // FLAGSHIP: Standard High-End OLED
                    ['min' => 2000000, 'score' => 9.5, 'label' => 'Perfect Blacks'],

                    // ENTRY OLED / TOP MINI-LED:
                    ['min' => 1000000, 'score' => 9, 'label' => 'Excellent'],

                    // PREMIUM LCD: Mini-LED with High Zone Count
                    ['min' => 100000, 'score' => 7.5, 'label' => 'High Contrast LCD'],

                    // MID-RANGE: IPS Black Technology
                    ['min' => 2000, 'score' => 5, 'label' => 'Above Average LCD'],

                    // STANDARD: IPS / VA panels
                    ['min' => 1000, 'score' => 3, 'label' => 'Standard'],

                    // BUDGET:
                    ['min' => 0, 'score' => 1, 'label' => 'Poor'],
                ],
                'default' => 0,
                'info_text' => 'Contrast ratio defines the depth of blacks. 8,000,000:1 and above indicates cutting-edge OLED tech with virtually perfect black levels.',
            ],
            'contrast_score_master' => [
                'label' => 'Contrast (Internal)',
                'weight' => 15,
                'hidden' => true, // Flag for your UI loop
                'ranges' => [
                    // ELITE: Tandem OLED / MLA+ / MicroLED
                    ['min' => 8000000, 'score' => 10, 'label' => 'Elite (Tandem OLED)'],

                    // FLAGSHIP: Standard High-End OLED
                    ['min' => 2000000, 'score' => 9.5, 'label' => 'Perfect Blacks'],

                    // ENTRY OLED / TOP MINI-LED:
                    ['min' => 1000000, 'score' => 9, 'label' => 'Excellent'],

                    // PREMIUM LCD: Mini-LED with High Zone Count
                    ['min' => 100000, 'score' => 7.5, 'label' => 'High Contrast LCD'],

                    // MID-RANGE: IPS Black Technology
                    ['min' => 2000, 'score' => 5, 'label' => 'Above Average LCD'],

                    // STANDARD: IPS / VA panels
                    ['min' => 1000, 'score' => 3, 'label' => 'Standard'],

                    // BUDGET:
                    ['min' => 0, 'score' => 1, 'label' => 'Poor'],
                ],
            ],
        ],

        'brightness' => [
            'brightness' => [
                'label' => 'Brightness',
                'weight' => 95,
                'hidden' => true,
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
            'brightness_hbm'     => ['weight' => 0], // Most important for outdoors
            'brightness_peak' => [
                'label' => 'Peak Brightness',
                'weight' => 0,
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
            'brightness_typical' => [
                'label' => 'Typical Brightness',
                'weight' => 0, // New spec
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
                'inference' => [
                    'conditions' => [
                        'brightness_typical' => null,
                        'brightness_peak' => ['min' => 2000],
                        'hdr_support' => ['not_null' => true],
                    ],

                    // 👇 VALUE inference (not score)
                    'value_map' => [
                        ['min_peak' => 3000, 'value' => 1400],
                        ['min_peak' => 2500, 'value' => 1300],
                        ['min_peak' => 2000, 'value' => 1200],
                    ],

                    'reasoning' => 'Estimated from peak brightness + HDR capability',
                ],
                'default' => 2,
            ],
            'hdr_support' => [
                'label' => 'HDR Support',
                'weight' => 5,
                'scale' => [
                    // ELITE: Triple Dynamic Metadata + Ultra HDR (2026 Ultra Flagships)
                    'dolby vision 2, hdr10+ advanced, hdr vivid, ultra hdr' => 10,
                    'dolby vision, hdr10+, hdr vivid' => 10,

                    // TOP TIER: Dual Premium Formats (The "Universal" Flagship)
                    'dolby vision, hdr10+' => 10,
                    'dolby vision 2, hdr10' => 10,
                    'hdr10+ advanced, hdr vivid' => 9.5,

                    // HIGH TIER: Single Premium with Ultra HDR Support
                    'dolby vision, ultra hdr' => 9,
                    'hdr10+, ultra hdr' => 8.5,
                    'dolby vision' => 8,

                    // MID TIER: Open standards
                    'hdr10+, hdr10' => 7.5,
                    'hdr vivid' => 7,
                    'hdr10' => 6.5,

                    // BUDGET/LEGACY:
                    'hdr' => 5,
                    'hd' => 3,
                    'no' => 0,
                ],
                'default' => 0,
            ],
        ], // Total: 100

        'motion' => [
            'refresh_rate' => [
                'label' => 'Refresh Rate',
                'weight' => 45, // Reduced from 20
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
            'adaptive_refresh_rate' => [
                'label' => 'Adaptive Refresh Rate',
                'weight' => 40,
                'unit' => [
                    'value' => 'Hz',
                    'position' => 'after',
                    'space' => false
                ],
                'scale' => [
                    '0.5-165' => 10, // Ultra-Gaming LTPO 4.0
                    '1-165' => 9.5,
                    '1-144' => 9,   // High-end Performance
                    '1-120' => 8.5, // Standard Flagship (S26, iPhone 18)
                    '10-120' => 6,   // Mid-range (Step-based)
                    'fixed' => 1,   // Obsolete
                ],
                'default' => 1,

            ],
            'touch_sampling_rate' => [
                'label' => 'Touch Sampling Rate',
                'weight' => 15,
                'unit' => [
                    'value' => 'Hz',
                    'position' => 'after',
                    'space' => false
                ],
                'scale' => [
                    2000 => 10, // Elite/Gaming (e.g., Tecno Spark 40 Pro @ 2160Hz)
                    1200 => 9,  // Premium Flagship
                    720 => 8,  // Standard Flagship (S26 Ultra / iPhone 17 Pro)
                    480 => 7,
                    360 => 6,  // Mid-range Standard
                    240 => 5,  // Budget Standard
                    120 => 4,
                ],
                'default' => 2,
            ],
        ], // Total: 100

        'sharpness' => [
            'pixel_density' => [
                'label' => 'Pixel Density (PPI)',
                'weight' => 80,
                'unit' => [
                    'value' => '(PPI)',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    ['min' => 550, 'score' => 10], // Elite 2026 (e.g., Sony 4K/8K or Ultra QHD+)
                    ['min' => 500, 'score' => 9],  // Premium Flagship (S26 Ultra)
                    ['min' => 460, 'score' => 8],  // Standard Flagship (iPhone 17/18 Pro)
                    ['min' => 420, 'score' => 7],  // 2026 Upper Mid-range
                    ['min' => 380, 'score' => 6],  // 2026 Standard Mid-range
                    ['min' => 320, 'score' => 5],  // 2026 Budget (FHD+ 6.7")
                    ['min' => 280, 'score' => 4],
                ],
                'default' => 2,
            ],
            'screen_ratio' => [
                'label' => 'Screen-to-Body Ratio',
                'weight' => 20,
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
        ], // Total: 100

        'ergonomics' => [
            'glass_protection' => [
                'label' => 'Glass Protection',
                'weight' => 70,
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
            // 'special_features' => ['weight' => 30], // Anti-reflective coating, etc.
        ], // Total: 100
    ],
];

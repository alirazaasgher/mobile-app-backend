<?php
return [
    'label' => 'Display',
    'weights' => [
        'panel_quality' => 40,
        'brightness' => 25,
        'motion' => 25,
        'sharpness' => 10
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
            'colour_depth' => [
                'label' => 'Color Depth',
                'weight' => 0,
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
            'colour_depth_master' => [
                'label' => 'Color Depth',
                'weight' => 30,
                'hidden' => true,
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
                    ['min' => 5000, 'score' => 10],
                    ['min' => 4320, 'score' => 9.5],
                    ['min' => 3840, 'score' => 9],
                    ['min' => 2160, 'score' => 7],
                    ['min' => 1920, 'score' => 6.5],
                    ['min' => 1440, 'score' => 5.5],
                    ['min' => 960, 'score' => 4.5],
                    ['min' => 480, 'score' => 3],
                    ['min' => 0, 'score' => 1],
                ],
                'default' => 3,

            ],
            'pwm_score_master' => [
                'label' => 'PWM Dimming Frequency',
                'weight' => 35,
                'hidden' => true,
                'unit' => [
                    'value' => 'Hz',
                    'position' => 'after',
                    'space' => false
                ],
                'ranges' => [
                    ['min' => 5000, 'score' => 10],
                    ['min' => 4320, 'score' => 9.5],
                    ['min' => 3840, 'score' => 9],
                    ['min' => 2160, 'score' => 7],
                    ['min' => 1920, 'score' => 6.5],
                    ['min' => 1440, 'score' => 5.5],
                    ['min' => 960, 'score' => 4.5],
                    ['min' => 480, 'score' => 3],
                    ['min' => 0, 'score' => 1],
                ],
                'default' => 3,

            ],
            'contrast_ratio' => [
                'label' => 'Contrast Ratio',
                'weight' => 15,
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
            'brightness_hbm' => [
                'label' => 'HBM Brightness',
                'weight' => 0, // Metadata only
                'unit' => [
                    'value' => 'nits',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    ['min' => 2000, 'score' => 10], // Elite (e.g., Pixel 9 Pro XL, S25 Ultra)
                    ['min' => 1600, 'score' => 9],
                    ['min' => 1300, 'score' => 8],  // Standard Flagship
                    ['min' => 1100, 'score' => 7],
                    ['min' => 900, 'score' => 6],   // High Mid-range
                    ['min' => 700, 'score' => 5],
                    ['min' => 500, 'score' => 3],   // Entry/Budget
                ],
                'default' => 2,
            ],
            'brightness_peak' => [
                'label' => 'Peak Brightness',
                'weight' => 0,
                'unit' => [
                    'value' => 'nits',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    ['min' => 5000, 'score' => 10], // 2026 Cutting Edge (Vivo/Oppo/Xiaomi)
                    ['min' => 4000, 'score' => 9],
                    ['min' => 3000, 'score' => 8],  // Current Top-Tier (Your 3200 falls here)
                    ['min' => 2400, 'score' => 7],
                    ['min' => 1800, 'score' => 6],
                    ['min' => 1200, 'score' => 5],
                    ['min' => 800, 'score' => 3],
                ],
                'default' => 2,
            ],
            'brightness_typical' => [
                'label' => 'Typical Brightness',
                'weight' => 0,
                'unit' => [
                    'value' => 'nits',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    ['min' => 1200, 'score' => 10], // Extremely rare manual high-sustained
                    ['min' => 1000, 'score' => 9],
                    ['min' => 800, 'score' => 8],   // Standard Flagship Manual Max
                    ['min' => 600, 'score' => 7],
                    ['min' => 500, 'score' => 6],
                    ['min' => 400, 'score' => 5],
                    ['min' => 300, 'score' => 3],
                ],
                'inference' => [
                    'conditions' => [
                        'brightness_typical' => null,
                        'brightness_peak' => ['min' => 2000],
                        'hdr_support' => ['not_null' => true],
                    ],
                    'value_map' => [
                        ['min_peak' => 4500, 'value' => 1000],
                        ['min_peak' => 3000, 'value' => 800],  // Your 3200 nit phone gets 800 typical
                        ['min_peak' => 2000, 'value' => 600],
                    ],
                    'reasoning' => 'Typical indoor max estimated from HDR peak and panel efficiency',
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
                'weight' => 25, // Reduced from 20
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
                'weight' => 45,
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
                'weight' => 10,
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
            'instant_touch_sampling_rate' => [
                'label' => 'Instant Touch Sampling Rate',
                'weight' => 20,
                'unit' => [
                    'value' => 'Hz',
                    'position' => 'after',
                    'space' => false
                ],
                'scale' => [
                    2560 => 10,   // ELITE: Xiaomi 15T / Gaming Flagships
                    2160 => 9.5,  // FLAGSHIP KILLER: 2026 Pro-Midrange Standard
                    1440 => 9,    // HIGH-END: Standard Premium Flagships
                    1000 => 8.5,  // TOP-TIER: Apple/Samsung Peak Response
                    720 => 8,    // STANDARD: Mainstream OLED
                    480 => 6.5,  // MID-RANGE: High-quality LCD or Budget OLED
                    240 => 4,    // ENTRY: Standard LCD response
                    120 => 2,    // LEGACY: Old tech
                ],
                'default' => 1, // 240Hz is the safe "baseline" for any 2026 smartphone
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
        ],
    ],
];

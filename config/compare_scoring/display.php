<?php
return [
    'label' => 'Display',
    'specs' => [
        'refresh_rate' => [
            'label' => 'Refresh Rate',
            'weight' => 7,
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            '2023' => [
                'scale' => [
                    144 => 10, // Gaming/Elite
                    120 => 9,  // Flagship Standard
                    90 => 7,  // Mid-range
                    60 => 5,  // Budget/Base iPhone
                ],
                'default' => 5,
            ],
            '2025' => [
                'scale' => [
                    165 => 10,
                    144 => 9,
                    120 => 8,  // New Standard
                    90 => 6,
                    60 => 3,  // Penalty
                ],
                'default' => 5,
            ],
            '2026' => [
                'scale' => [
                    185 => 10, // Ultra-Gaming
                    165 => 10, // Pro-Gaming
                    144 => 9,  // Performance Flagship (Motorola Edge 70)
                    120 => 8,  // Universal Standard (S26, iPhone 17/18)
                    90 => 5,  // Legacy Budget
                    60 => 2,  // Obsolete
                ],
                'default' => 2,
            ],
        ],
        'pixel_density' => [
            'label' => 'Pixel Density (PPI)',
            'weight' => 10,
            'unit' => [
                'value' => '(PPI)',
                'position' => 'after',
                'space' => true
            ],
            '2023' => [
                'ranges' => [
                    ['min' => 500, 'score' => 10], // S23 Ultra (500), Sony 1 V (643)
                    ['min' => 450, 'score' => 9],  // iPhone 15 Pro (460)
                    ['min' => 400, 'score' => 8],  // Standard Flagship
                    ['min' => 350, 'score' => 7],  // Mid-range High
                    ['min' => 300, 'score' => 6],  // Mid-range Average
                    ['min' => 250, 'score' => 5],  // Budget Standard
                    ['min' => 200, 'score' => 4],
                ],
                'default' => 3,
            ],
            '2025' => [
                'ranges' => [
                    ['min' => 520, 'score' => 10], // Ultra Flagships
                    ['min' => 480, 'score' => 9],
                    ['min' => 440, 'score' => 8],  // 2025 Flagship Standard
                    ['min' => 400, 'score' => 7],
                    ['min' => 360, 'score' => 6],  // 2025 Mid-range
                    ['min' => 300, 'score' => 5],
                    ['min' => 250, 'score' => 4],
                ],
                'default' => 3,
            ],
            '2026' => [
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
            'default' => 3,
        ],
        'brightness_peak' => [
            'label' => 'Peak Brightness',
            'weight' => 10,
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => true
            ],
            '2023' => [
                'ranges' => [
                    ['min' => 2000, 'score' => 10], // iPhone 15 Pro, S23 Ultra
                    ['min' => 1750, 'score' => 9],
                    ['min' => 1500, 'score' => 8],
                    ['min' => 1200, 'score' => 7],
                    ['min' => 1000, 'score' => 6],  // High-end standard 2023
                    ['min' => 800, 'score' => 5],  // Mid-range 2023
                    ['min' => 600, 'score' => 4],
                    ['min' => 450, 'score' => 3],
                ],
            ],
            '2025' => [
                'ranges' => [
                    ['min' => 3000, 'score' => 10], // iPhone 17 Pro, Pixel 10 Pro
                    ['min' => 2600, 'score' => 9],
                    ['min' => 2200, 'score' => 8],
                    ['min' => 1800, 'score' => 7],
                    ['min' => 1500, 'score' => 6],  // High-end standard 2025
                    ['min' => 1200, 'score' => 5],  // Mid-range 2025
                    ['min' => 900, 'score' => 4],
                    ['min' => 700, 'score' => 3],
                ],
            ],
            '2026' => [
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
            ],
            'default' => 2,
        ],
        'brightness_typical' => [
            'label' => 'Typical Brightness',
            'weight' => 6, // New spec
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => true
            ],
            '2023' => [
                'ranges' => [
                    ['min' => 1200, 'score' => 10], // iPhone 15 Pro, S23 Ultra
                    ['min' => 1000, 'score' => 9],
                    ['min' => 800, 'score' => 8],
                    ['min' => 700, 'score' => 7],
                    ['min' => 600, 'score' => 6],  // 2023 Good Mid-range
                    ['min' => 500, 'score' => 5],  // 2023 Average
                    ['min' => 400, 'score' => 4],
                    ['min' => 300, 'score' => 3],
                ],
            ],
            '2025' => [
                'ranges' => [
                    ['min' => 1400, 'score' => 10], // iPhone 17 Pro, Pixel 10 Pro
                    ['min' => 1200, 'score' => 9],
                    ['min' => 1000, 'score' => 8],
                    ['min' => 850, 'score' => 7],
                    ['min' => 700, 'score' => 6],  // 2025 Good Mid-range
                    ['min' => 600, 'score' => 5],  // 2025 Average
                    ['min' => 500, 'score' => 4],
                    ['min' => 400, 'score' => 3],
                ],
            ],
            '2026' => [
                'ranges' => [
                    ['min' => 1600, 'score' => 10], // Elite 2026 (M14/M15 OLED Panels)
                    ['min' => 1400, 'score' => 9],
                    ['min' => 1200, 'score' => 8],
                    ['min' => 1000, 'score' => 7],
                    ['min' => 800, 'score' => 6],  // 2026 Good Mid-range
                    ['min' => 700, 'score' => 5],  // 2026 Average
                    ['min' => 600, 'score' => 4],
                    ['min' => 500, 'score' => 3],
                ],
            ],
            'default' => 2,
        ],
        'touch_sampling_rate' => [
            'label' => 'Touch Sampling Rate',
            'weight' => 5,
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            '2023' => [
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
            '2025' => [
                'scale' => [
                    1200 => 10, // New "Instant" Standard
                    720 => 9,
                    480 => 8,  // Flagship Standard
                    360 => 7,
                    240 => 6,  // Mid-range Standard
                    180 => 5,
                    120 => 4,
                ],
                'default' => 3,
            ],
            '2026' => [
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

            'default' => 2,
        ],
        'hdr_support' => [
            'label' => 'HDR Support',
            'weight' => 8,
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
        'glass_protection' => [
            'label' => 'Glass Protection',
            'weight' => 5,
            'scale' => [
                // ELITE: 2026 "No-Protector" Generation
                'gorilla glass ultra-high strength' => 10, // Rumored for S26 Ultra
                'gorilla armor 2' => 10, // S25 Ultra standard
                'ceramic shield 2' => 10, // iPhone 17/18 Pro
                'dragon crystal glass 2.0' => 10, // Xiaomi 15/16 series
                'kunlun glass 3' => 10, // Huawei Pura 80 / Mate 80

                // TOP TIER: 2024-2025 Flagship Standards
                'gorilla glass armor' => 9.5,
                'victus 3' => 9.5,
                'ceramic shield (latest)' => 9.5,
                'dragon crystal glass' => 9.5,
                'kunlun glass 2' => 9.5,

                // RECENT FLAGSHIP: 2023-2024
                'victus 2' => 9,
                'ceramic shield' => 9,
                'kunlun glass' => 9,
                'gorilla glass victus+' => 8.5,
                'panda king kong glass' => 8.5,

                // MID-TIER & LEGACY
                'victus' => 8,
                'gorilla glass 7i' => 7.5,
                'gorilla glass 6' => 7,
                'dragontrail pro' => 7,
                'gorilla glass 5' => 6,
                'panda glass' => 5,
                'soda-lime glass' => 3,
            ],
            'default' => 2,
        ],
        'reflectance_ratio' => [
            'label' => 'Reflectance Ratio',
            'weight' => 6,
            'unit' => [
                'value' => '%',
                'position' => 'after',
                'space' => false
            ],
            'ranges' => [
                // ELITE: Anti-Reflective Armor (75%+ reduction)
                ['max' => 1.2, 'score' => 10], // S26 Ultra / Armor 2 Standard
                ['max' => 1.5, 'score' => 9.5], // S25 Ultra / Elite AR Coatings
                ['max' => 2.0, 'score' => 9],   // iPhone 17 Pro / Early AR Glass

                // HIGH TIER: Premium Glass with basic AR
                ['max' => 3.0, 'score' => 8],   // Top-tier Chinese Flagships (Xiaomi/Vivo)
                ['max' => 4.0, 'score' => 7],   // Standard Flagship (Victus 2/3)

                // MID TIER: Standard Glossy Glass
                ['max' => 4.5, 'score' => 6],   // Most Mid-range phones
                ['max' => 5.5, 'score' => 5],   // Standard 2023-2024 glass

                // BUDGET/LEGACY: High Glare
                ['max' => 7.0, 'score' => 3],   // Budget panels with air gaps
                ['max' => 10.0, 'score' => 1],  // Highly reflective old panels
            ],
            'default' => 5,
        ],
        'sustained_brightness' => [
            'label' => 'Sustained Brightness',
            'weight' => 8,
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => false
            ],
            '2023' => [
                'ranges' => [
                    ['min' => 1500, 'score' => 10], // S23 Ultra / iPhone 15 Pro Max
                    ['min' => 1200, 'score' => 9],
                    ['min' => 1000, 'score' => 8],
                    ['min' => 800, 'score' => 7],  // Standard Flagship 2023
                    ['min' => 600, 'score' => 5],  // Average 2023
                    ['min' => 450, 'score' => 3],
                ],
                'default' => 2,
            ],
            '2025' => [
                'ranges' => [
                    ['min' => 1800, 'score' => 10], // iPhone 17 Pro / S25 Ultra
                    ['min' => 1600, 'score' => 9],
                    ['min' => 1400, 'score' => 8],
                    ['min' => 1200, 'score' => 7],
                    ['min' => 1000, 'score' => 5],  // Average 2025
                    ['min' => 700, 'score' => 3],
                ],
                'default' => 2,
            ],
            '2026' => [
                'ranges' => [
                    ['min' => 2200, 'score' => 10], // Elite 2026 (Tandem OLED / MLA+)
                    ['min' => 2000, 'score' => 9],  // Premium Flagship
                    ['min' => 1800, 'score' => 8],  // Standard Flagship
                    ['min' => 1500, 'score' => 7],
                    ['min' => 1200, 'score' => 5],  // Average 2026
                    ['min' => 800, 'score' => 3],
                ],
                'default' => 2,
            ]

        ],
        'adaptive_refresh_rate' => [
            'label' => 'Adaptive Refresh Rate',
            'weight' => 8,
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            '2023' => [
                'scale' => [
                    '1-120' => 10, // LTPO (S23 Ultra, iPhone 15 Pro)
                    '10-120' => 9,
                    '48-120' => 7,  // LTPS "Pseudo-adaptive"
                    'fixed' => 5,  // No adaptive
                ],
                'default' => 5,
            ],
            '2025' => [
                'scale' => [
                    '1-144' => 10, // High-speed LTPO
                    '1-120' => 9,  // Standard Flagship (iPhone 16/17 Pro)
                    '10-120' => 7,
                    'fixed' => 3,  // Penalty for fixed rates
                ],
                'default' => 3,
            ],
            '2026' => [
                'scale' => [
                    '0.5-165' => 10, // Ultra-Gaming LTPO 4.0
                    '1-165' => 9.5,
                    '1-144' => 9,   // High-end Performance
                    '1-120' => 8.5, // Standard Flagship (S26, iPhone 18)
                    '10-120' => 6,   // Mid-range (Step-based)
                    'fixed' => 1,   // Obsolete
                ],
                'default' => 1,
            ]

        ],
        'pwm_dimming_frequency' => [
            'label' => 'PWM Dimming Frequency',
            'weight' => 10,
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            '2023' => [
                'ranges' => [
                    ['min' => 2160, 'score' => 10], // Early Honor/Realme
                    ['min' => 1920, 'score' => 9],
                    ['min' => 1440, 'score' => 8],
                    ['min' => 480, 'score' => 6],  // Samsung/Apple standard
                    ['min' => 240, 'score' => 4],  // Lower end
                ],
                'default' => 4,
            ],
            '2025' => [
                'ranges' => [
                    ['min' => 4320, 'score' => 10], // Ultra Eye-Care
                    ['min' => 3840, 'score' => 9.5],
                    ['min' => 2160, 'score' => 8],  // New "Standard" Flagship
                    ['min' => 1440, 'score' => 7],
                    ['min' => 480, 'score' => 4],  // Penalty for low Hz
                ],
                'default' => 3,
            ],
            '2026' => [
                'ranges' => [
                    ['min' => 5000, 'score' => 10], // "Risk-Free" Tier
                    ['min' => 4320, 'score' => 9.5],
                    ['min' => 3840, 'score' => 9],  // Flagship Minimum
                    ['min' => 2160, 'score' => 7],  // Mid-range High
                    ['min' => 1920, 'score' => 6],
                    ['min' => 480, 'score' => 2],  // Obsolete/High Strain
                ],
                'default' => 2,
            ]

        ],
        'display_features' => [
            'label' => 'Display Features',
            'weight' => 4,
            'scale' => [
                // ELITE: 2026 Health & AI Bundle
                'circadian friendly, ai provisual engine, tuv 5-star eye comfort' => 10,
                'ai super resolution, flicker-free, 10-bit color' => 9.5,

                // FLAGSHIP: 2025-2026 Standard Bundle
                'tuv eye comfort 3.0, high-frequency pwm, anti-glare' => 9,
                'aqua touch 2.0, intelligent eye care 4.0' => 9, // OnePlus/Oppo 2025-2026
                'eye-safe display, dc dimming' => 8.5,

                // HIGH TIER: 2023-2024 Premium
                'low blue light (hardware), flicker-free' => 8,
                'hdr10 playback, 100% dci-p3' => 7.5,

                // MID TIER: Standard Certifications
                'tuv rheinland certified, eye comfort mode' => 6.5,
                'reading mode, sunlight mode' => 5.5,

                // BUDGET/LEGACY:
                'basic eye care' => 4,
                'none' => 0,
            ],
            'default' => 0,
        ],
        'contrast_ratio' => [
            'label' => 'Contrast Ratio',
            'weight' => 9,
            'scale' => [
                // ELITE: Tandem OLED / Micro-OLED / MLA+
                '5,000,000:1' => 10,
                'infinite' => 10, // Common marketing for OLED

                // TOP TIER: Advanced 2025/2026 OLED Panels
                '2,000,000:1' => 9.5,
                '1,500,000:1' => 9,

                // STANDARD FLAGSHIP: Baseline OLED
                '1,000,000:1' => 8.5,

                // HIGH-END LCD / MINI-LED:
                '100,000:1' => 7, // Dynamic Local Dimming
                '10,000:1' => 6, // High-end Mini-LED

                // MID-TIER LCD:
                '2,000:1' => 4, // IPS Black Tech
                '1,500:1' => 3, // Standard IPS
                '1,000:1' => 2, // Budget LCD

                'none' => 0,
            ],
            'default' => 0,
        ],
        'color_depth_bits' => [
            'label' => 'Color Depth',
            'weight' => 8,
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
        ]
    ],
];

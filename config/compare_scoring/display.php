<?php
return [
    'label' => 'Display',
    'specs' => [
        //SCREEN QUALITY Total Weight 19 points
        'pixel_density' => [
            'label' => 'Pixel Density (PPI)',
            'weight' => 9.62,
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
        'contrast_ratio' => [
            'label' => 'Contrast Ratio',
            'weight' => 8.65,
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
        // BRIGHTNESS & VISIBILITY Total Weight 24 points
        'brightness_peak' => [
            'label' => 'Peak Brightness',
            'weight' => 9.62,
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
            'weight' => 5.77, // New spec
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => true
            ],
            'ranges' => [
                ['min' => 1800, 'score' => 10],
                ['min' => 1600, 'score' => 9.5], // Elite 2026 (M14/M15 OLED Panels)
                ['min' => 1400, 'score' => 9],
                ['min' => 1200, 'score' => 8],
                ['min' => 1000, 'score' => 7],
                ['min' => 800, 'score' => 6],  // 2026 Good Mid-range
                ['min' => 700, 'score' => 5],  // 2026 Average
                ['min' => 600, 'score' => 4],
                ['min' => 500, 'score' => 3],
            ],
            'default' => 2,
        ],
        'sustained_brightness' => [
            'label' => 'Sustained Brightness',
            'weight' => 8,
            'unit' => [
                'value' => 'nits',
                'position' => 'after',
                'space' => false
            ],
            'ranges' => [
                ['min' => 2200, 'score' => 10], // Elite 2026 (Tandem OLED / MLA+)
                ['min' => 2000, 'score' => 9],  // Premium Flagship
                ['min' => 1800, 'score' => 8],  // Standard Flagship
                ['min' => 1500, 'score' => 7],
                ['min' => 1200, 'score' => 5],  // Average 2026
                ['min' => 800, 'score' => 3],
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

        //COLOR & HDR Total Weight 16 points
        'hdr_support' => [
            'label' => 'HDR Support',
            'weight' => 7.69,
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
        'color_depth_bits' => [
            'label' => 'Color Depth',
            'weight' => 7.69,
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
        'color_gamut_dci_p3' => [
            'label' => 'DCI-P3 Color Gamut',
            'weight' => 0, // Unweighted - niche professional feature
            'unit' => [
                'value' => '%',
                'position' => 'after',
                'space' => false
            ],
            'scale' => [
                100 => 10,  // Perfect coverage (iPhone 15 Pro, S24 Ultra)
                98 => 9,    // Excellent (Most flagships)
                95 => 8,    // Very Good
                90 => 7,    // Good (Premium mid-range)
                85 => 6,    // Above Average
                80 => 5,    // Average
                75 => 4,    // Below Average
                70 => 3,    // Poor
            ],
            'default' => 5,
            'info_text' => 'DCI-P3 is a wider color space used in HDR content. Higher % means more vivid colors.',
        ],

        'color_accuracy_delta_e' => [
            'label' => 'Color Accuracy (Delta E)',
            'weight' => 0, // Unweighted - professional/content creator feature
            'unit' => [
                'value' => 'ΔE',
                'position' => 'after',
                'space' => true
            ],
            'scale' => [
                0.5 => 10,  // Perfect (Professional monitors)
                1.0 => 9,   // Excellent (iPhone 15 Pro, S24 Ultra)
                1.5 => 8,   // Very Good (Pixel 9 Pro)
                2.0 => 7,   // Good (imperceptible to most)
                3.0 => 6,   // Above Average
                4.0 => 5,   // Average (noticeable to trained eyes)
                5.0 => 4,   // Below Average
                6.0 => 3,   // Poor
                8.0 => 2,   // Very Poor
            ],
            'default' => 5,
            'inverse' => true, // Lower is better
            'info_text' => 'Measures color accuracy. Lower is better. ΔE < 2 is imperceptible to human eye.',
        ],

        'response_time_ms' => [
            'label' => 'Response Time',
            'weight' => 0, // Unweighted - gaming-specific feature
            'unit' => [
                'value' => 'ms',
                'position' => 'after',
                'space' => false
            ],
            'scale' => [
                1 => 10,    // Elite Gaming (OLED)
                2 => 9,     // Excellent (Fast OLED)
                3 => 8,     // Very Good (Standard OLED)
                5 => 7,     // Good (Fast LCD)
                8 => 6,     // Above Average (LCD)
                12 => 5,    // Average (Standard LCD)
                16 => 4,    // Below Average
                20 => 3,    // Poor
                25 => 2,    // Very Poor (ghosting visible)
            ],
            'default' => 5,
            'inverse' => true, // Lower is better
            'info_text' => 'Time for pixels to change color. Lower is better for fast motion/gaming. OLED typically 1-3ms, LCD 5-20ms.',
        ],

        // PERFORMANCE & SMOOTHNESS Total Weight 22 points
        'refresh_rate' => [
            'label' => 'Refresh Rate',
            'weight' => 6.73,
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
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
        'adaptive_refresh_rate' => [
            'label' => 'Adaptive Refresh Rate',
            'weight' => 7.69,
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
            'weight' => 4.81,
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
        'min_refresh_rate' => [
            'label' => 'Minimum Refresh Rate',
            'weight' => 0, // Unweighted - used for contextual validation only
            'unit' => [
                'value' => 'Hz',
                'position' => 'after',
                'space' => false
            ],
            'scale' => [
                1 => 10,   // Premium LTPO (iPhone 15 Pro, S24 Ultra, Pixel 9 Pro)
                10 => 9,   // Excellent LTPO (OnePlus 12, Xiaomi 14 Pro)
                24 => 8,   // Good Adaptive (Find X7, Realme GT 6)
                30 => 7,   // Standard Adaptive
                48 => 6,   // Basic Adaptive
                60 => 5,   // Fake Adaptive (60-120Hz only)
                90 => 3,   // Poor Adaptive
                120 => 1,  // No Adaptive (fixed rate)
            ],
            'default' => 1,
            'info_text' => 'Lower is better for battery efficiency. Premium LTPO displays can drop to 1Hz.',
        ],

        //  EYE COMFORT & HEALTH Total Weight 7 points
        'pwm_dimming_frequency' => [
            'label' => 'PWM Dimming Frequency',
            'weight' => 10,
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
            'default' => 2,

        ],
        'dc_dimming' => [
            'label' => 'DC Dimming',
            'weight' => 0, // Unweighted - used for contextual validation with PWM
            'unit' => [
                'value' => '',
                'position' => 'after',
                'space' => false
            ],
            'scale' => [
                'yes' => 10,     // Has DC dimming (flicker-free)
                'optional' => 8, // User can toggle DC dimming on/off
                'no' => 5,       // No DC dimming (relies on PWM)
            ],
            'default' => 5,
            'info_text' => 'DC dimming eliminates PWM flicker at low brightness, reducing eye strain.',
        ],
        // PROTECTION & DURABILITY Total Weight 5 points
        'glass_protection' => [
            'label' => 'Glass Protection',
            'weight' => 4.81,
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
        //  FEATURES & EXTRAS Total Weight 7 points
        'display_features' => [
            'label' => 'Display Features',
            'weight' => 3.85,
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
    ],
];

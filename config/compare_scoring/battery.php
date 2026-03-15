<?php
return [
    'label' => 'Battery',
    'capacity' => [
        'label' => 'Battery Capacity (mAh)',
        'weight' => [
            'entry-level' => 65,
            'budget' => 53,
            'mid-range' => 45,
            'flagship' => 30,
        ],
        'unit' => [
            'value' => 'mAh',
            'position' => 'after',   // before | after
            'space' => true           // true => add space, false => no space
        ],
        'ranges' => [
            'ranges' => [
                // Ultra-High Capacity (Si-C Era Champions)
                ['min' => 9000, 'score' => 10],
                ['min' => 7500, 'score' => 10],

                // High-End Endurance (Gaming / Large Flagships)
                ['min' => 6500, 'score' => 9],
                ['min' => 6000, 'score' => 9],

                // Modern Standard (Standard Flagships / Mid-Range)
                ['min' => 5500, 'score' => 8],
                ['min' => 5000, 'score' => 8],

                // Average (Lower Mid-Range / Compacts)
                ['min' => 4500, 'score' => 7],
                ['min' => 4000, 'score' => 6],

                // Below Average (Budget / Small Compacts)
                ['min' => 3500, 'score' => 5],
                ['min' => 3000, 'score' => 4],

                // Legacy / Entry
                ['min' => 2000, 'score' => 2],
            ],
        ],
        'default' => 5,
    ],
    'wired' => [
        'label' => 'Wired Charging Speed (W)',
        'weight' => [
            'entry-level' => 30, // Charging speed is secondary
            'budget' => 35,
            'mid-range' => 30,
            'flagship' => 20,
        ],
        'unit' => [
            'value' => 'W',
            'position' => 'after',   // before | after
            'space' => false           // true => add space, false => no space
        ],
        'ranges' => [
            // Elite Tier (300W+ Technology)
            ['min' => 300, 'score' => 10],
            ['min' => 240, 'score' => 10],

            // Ultra-Fast (Top Chinese Flagships)
            ['min' => 150, 'score' => 9],
            ['min' => 120, 'score' => 9],
            ['min' => 100, 'score' => 8.5], // Optional decimal for granularity

            // Performance Mid-range / Flagship
            ['min' => 80, 'score' => 8],
            ['min' => 65, 'score' => 7],

            // The "Mainstream" Ceiling (Samsung/Google/High-end Apple)
            ['min' => 45, 'score' => 6],
            ['min' => 33, 'score' => 5],

            // Basic Fast Charging (Base iPhone / Budget)
            ['min' => 25, 'score' => 4],
            ['min' => 18, 'score' => 3],

            // Legacy / Slow
            ['min' => 10, 'score' => 2],
            ['min' => 5, 'score' => 1],
        ],
        'default' => 4,
    ],
    'wireless' => [
        'label' => 'Wireless Charging Speed (W)',
        'weight' => [
            'entry-level' => 0,   // Not expected. 0% impact on score.
            'budget' => 5,   // Rare bonus. Small impact.
            'mid-range' => 10,  // Becoming standard. Moderate impact.
            'flagship' => 20,  // Mandatory. High impact on the "Premium" feel.
        ],
        'unit' => [
            'value' => 'W',
            'position' => 'after',   // before | after
            'space' => false           // true => add space, false => no space
        ],
        'ranges' => [
            // Extreme / Proprietary (The 2026 Speed Kings)
            ['min' => 100, 'score' => 10],
            ['min' => 80, 'score' => 9.5],
            ['min' => 65, 'score' => 9],

            // Fast Performance (OnePlus / Xiaomi standard)
            ['min' => 50, 'score' => 8.5],
            ['min' => 40, 'score' => 8],

            // Premium Standard (Qi2 Peak / High-end Proprietary)
            ['min' => 30, 'score' => 7.5],
            ['min' => 25, 'score' => 7], // High-end MagSafe/Qi2

            // Mainstream Wireless (Standard iPhone/Samsung/Pixel)
            ['min' => 15, 'score' => 6], // Standard Qi2/MagSafe
            ['min' => 12, 'score' => 5],

            // Basic / Legacy Qi
            ['min' => 10, 'score' => 4],
            ['min' => 5, 'score' => 2],
        ],

        'default' => 0,  // No wireless charging
    ],
    'reverse' => [
        'label' => 'Reverse Wireless Charging',
        'weight' => [
            'entry-level' => 0,
            'budget' => 2,
            'mid-range' => 5,
            'flagship' => 10,
        ],
        'ranges' => [
            // Extreme (Acts as a portable wireless pad for other phones)
            ['min' => 25, 'score' => 10],
            ['min' => 20, 'score' => 9.5],

            // Excellent (Fast accessory top-ups)
            ['min' => 15, 'score' => 9],
            ['min' => 10, 'score' => 8],

            // Standard Flagship (The "PowerShare" sweet spot)
            ['min' => 7.5, 'score' => 7], // 2026 Premium Standard
            ['min' => 4.5, 'score' => 6], // Common Samsung/Pixel limit

            // Basic / Weak
            ['min' => 2.5, 'score' => 4],
            ['min' => 1, 'score' => 2],
        ],
        'default' => 0,
    ],
    'type' => [
        'label' => 'Battery Type',
        'weight' => [
            'entry-level' => 5,
            'budget' => 5,
            'mid-range' => 12,  // Increased weight: helps users identify "Premium" internals
            'flagship' => 18,   // High weight: Si-C is now a key premium metric
        ],
        'scale' => [
            'silicon-carbon' => 10,    // The 2026 gold standard (Xiaomi, Honor, Vivo)
            'graphene' => 10,          // The "Speed King" tech (RedMagic, Realme)
            'li-po' => 8,              // Solid, but no longer cutting-edge
            'lithium polymer' => 8,
            'li-ion' => 6,              // Usually implies a less space-efficient design
            'lithium ion' => 6,
        ],
        'default' => 8, // Assume Li-Po as the safe baseline
    ],
    'charging_time_0_to_100' => [
        'label' => 'Charging Time (0-100%)',
        'weight' => 0, // Reminder: This is often for display, as wattage covers the "score"
        'ranges' => [
            // The "Hyper" Tier
            ['max' => 12, 'score' => 10],
            ['max' => 18, 'score' => 9.5],

            // The "Flagship Killer" Tier
            ['max' => 25, 'score' => 9],
            ['max' => 35, 'score' => 8],

            // The "Mainstream Flagship" Tier (Samsung/Apple/Google)
            ['max' => 45, 'score' => 7],
            ['max' => 60, 'score' => 6],

            // The "Budget/Entry" Tier
            ['max' => 80, 'score' => 4.5],
            ['max' => 110, 'score' => 3],
            ['max' => 140, 'score' => 2],
            ['max' => 180, 'score' => 1],
        ],
        'default' => 3,
    ],
    'charging_time_0_to_50' => [
        'label' => 'Charging Time (0-50%)',
        'weight' => 0,
        'ranges' => [
            // The "Three-Minute" Revolution
            ['max' => 4, 'score' => 10],
            ['max' => 6, 'score' => 9.5],

            // High-Speed Standard
            ['max' => 9, 'score' => 9],
            ['max' => 13, 'score' => 8],

            // Above Average (Mid-range high speed / Premium conservative)
            ['max' => 18, 'score' => 7.5],
            ['max' => 24, 'score' => 7], // The "Premium West" sweet spot

            // The "Coffee Break" Tier
            ['max' => 35, 'score' => 5],
            ['max' => 45, 'score' => 4],

            // Slow
            ['max' => 60, 'score' => 3],
            ['max' => 80, 'score' => 2],
            ['max' => 100, 'score' => 1],
        ],
        'default' => 3,
    ],
];

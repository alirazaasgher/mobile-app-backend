<?php
return [
    'label' => 'Battery',
    'weights' => [
        'capacity' => 35,
        'charging_speed' => 25,
        'efficiency' => 20,
        'charging_features' => 10,
        'battery_technology' => 10,
    ],
    'categories' => [
        'capacity' => [
            'capacity' => [
                'label' => 'Battery Capacity (mAh)',
                'weight' => 100,
                'unit' => [
                    'value' => 'mAh',
                    'position' => 'after',   // before | after
                    'space' => true           // true => add space, false => no space
                ],
                'ranges' => [
                    // Ultra large (gaming phones, tablets)
                    ['min' => 7000, 'score' => 10],
                    ['min' => 6500, 'score' => 10],
                    ['min' => 6000, 'score' => 10],

                    // Large flagship
                    ['min' => 5500, 'score' => 9],
                    ['min' => 5000, 'score' => 9],

                    // Standard flagship / Large mid-range
                    ['min' => 4800, 'score' => 8],
                    ['min' => 4500, 'score' => 8],

                    // Average
                    ['min' => 4000, 'score' => 7],
                    ['min' => 3500, 'score' => 6],

                    // Compact flagship / Budget
                    ['min' => 3000, 'score' => 5],
                    ['min' => 2500, 'score' => 4],

                    // Very small
                    ['min' => 2000, 'score' => 3],
                    ['min' => 1500, 'score' => 2],
                ],
                'default' => 2,
            ]
        ],
        'charging_speed' => [
            'wired' => [
                'label' => 'Wired Charging Speed (W)',
                'weight' => 20,
                'unit' => [
                    'value' => 'W',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    // Ultra-fast (Chinese brands)
                    ['min' => 240, 'score' => 10],  // Realme GT Neo 5
                    ['min' => 200, 'score' => 10],
                    ['min' => 150, 'score' => 10],  // Xiaomi 13 Ultra
                    ['min' => 120, 'score' => 9],   // OnePlus, Xiaomi
                    ['min' => 100, 'score' => 9],

                    // Fast flagship
                    ['min' => 80, 'score' => 8],    // OPPO, Xiaomi
                    ['min' => 67, 'score' => 8],    // Common Chinese flagship
                    ['min' => 65, 'score' => 8],

                    // Standard flagship
                    ['min' => 45, 'score' => 7],    // Samsung flagship
                    ['min' => 40, 'score' => 7],
                    ['min' => 33, 'score' => 6],    // Common mid-range
                    ['min' => 30, 'score' => 6],

                    // Basic fast charging
                    ['min' => 25, 'score' => 5],    // iPhone, budget
                    ['min' => 20, 'score' => 5],
                    ['min' => 18, 'score' => 4],
                    ['min' => 15, 'score' => 4],

                    // Slow charging
                    ['min' => 10, 'score' => 3],
                    ['min' => 5, 'score' => 2],
                ],
                'default' => 3,
            ],
            'wirless' => [
                'label' => 'Wireless Charging Speed (W)',
                'weight' => 15,
                'unit' => [
                    'value' => 'W',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    // Extreme / Proprietary Wireless
                    ['min' => 80, 'score' => 10],   // Xiaomi, OPPO (proprietary)
                    ['min' => 60, 'score' => 9],

                    // Very Fast Wireless
                    ['min' => 50, 'score' => 9],    // Xiaomi, OnePlus
                    ['min' => 40, 'score' => 8],    // OPPO, Vivo

                    // Fast Wireless (Flagships)
                    ['min' => 30, 'score' => 8],    // Samsung Ultra
                    ['min' => 25, 'score' => 8],    // iPhone 15 series (MagSafe), Pixel Stand 2

                    // Standard Wireless
                    ['min' => 20, 'score' => 7],
                    ['min' => 15, 'score' => 6],    // iPhone (older MagSafe), Samsung

                    // Basic Qi
                    ['min' => 10, 'score' => 5],    // Qi phones
                    ['min' => 7.5, 'score' => 4],   // Older iPhones (Qi)
                    ['min' => 5, 'score' => 3],     // Entry-level Qi
                ],

                'default' => 2,  // No wireless charging
                'none_value' => 0, // Explicitly no wireless charging
            ],
            'reverce' => [
                'label' => 'Reverse Wireless Charging',
                'weight' => 10,
                'unit' => [
                    'value' => 'W',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 20, 'score' => 10], // Cutting-edge (Huawei Mate 60 Pro+, etc.)
                    ['min' => 15, 'score' => 9],  // Excellent (Huawei, Xiaomi flagships)
                    ['min' => 10, 'score' => 8],  // Great (Samsung flagships)
                    ['min' => 7, 'score' => 7],   // Good (Newer flagships)
                    ['min' => 5, 'score' => 6],   // Decent (Pixel, OnePlus)
                    ['min' => 3, 'score' => 5],   // Weak
                    ['min' => 1, 'score' => 4],   // Bare minimum
                ],
                'default' => 2,
            ],
            'charging_time_0_to_100' => [
                'label' => 'Charging Time (0-100%)',
                'weight' => 25,
                'unit' => [
                    'value' => 'min',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    ['max' => 15, 'score' => 10], // Ultra-fast flagship charging
                    ['max' => 20, 'score' => 9],  // Premium tier
                    ['max' => 30, 'score' => 8],  // Excellent
                    ['max' => 45, 'score' => 7],  // Very good
                    ['max' => 60, 'score' => 6],  // Good
                    ['max' => 75, 'score' => 5],  // Above average
                    ['max' => 90, 'score' => 4],  // Average
                    ['max' => 110, 'score' => 3],  // Below average
                    ['max' => 140, 'score' => 2],  // Slow
                    ['max' => 180, 'score' => 1],  // Very slow
                ],
                'default' => 0,
            ],
            'charging_time_0_to_50' => [
                'label' => 'Charging Time (0-50%)',
                'weight' => 30,
                'unit' => [
                    'value' => 'min',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    ['max' => 5, 'score' => 10], // Extremely fast burst charging
                    ['max' => 8, 'score' => 9],  // Ultra fast
                    ['max' => 12, 'score' => 8],  // Excellent
                    ['max' => 18, 'score' => 7],  // Very good
                    ['max' => 25, 'score' => 6],  // Good
                    ['max' => 35, 'score' => 5],  // Above average
                    ['max' => 45, 'score' => 4],  // Average
                    ['max' => 55, 'score' => 3],  // Below average
                    ['max' => 70, 'score' => 2],  // Slow
                    ['max' => 90, 'score' => 1],  // Very slow
                ],
                'default' => 0,
            ],
        ],
        'battery_technology' => [
            'type' => [
                'label' => 'Battery Type',
                'weight' => 65,
                'scale' => [
                    'li-po' => 9,              // Lithium Polymer (modern standard)
                    'lithium polymer' => 9,
                    'li-ion' => 8,             // Lithium Ion (older standard)
                    'lithium ion' => 8,
                    'graphene' => 10,          // Graphene (rare, cutting-edge)
                    'silicon-carbon' => 10,    // Silicon-carbon (OnePlus, Xiaomi)
                ],
                'default' => 8,
            ],
            'charging_technology' => [
                'label' => 'Charging Technology',
                'weight' => 35,
                'scale' => [
                    // Ultra-fast proprietary (200W+)
                    'hypercharge' => 10,              // Xiaomi 240W
                    'supervooc' => 10,                // OPPO/OnePlus 240W+
                    '240w supervooc' => 10,           // Realme/OPPO
                    '150w supervooc' => 9,            // Realme

                    // High-speed proprietary (100W+)
                    '120w hypercharge' => 9,          // Xiaomi
                    '100w supervooc' => 9,            // OnePlus/OPPO
                    'warp charge 65' => 9,            // OnePlus
                    'warp charge' => 8,               // OnePlus older
                    'vooc' => 8,                      // OPPO 65W
                    'dash charge' => 7,               // OnePlus older

                    // Mid-range proprietary (45W-100W)
                    'super fast charging 2.0' => 9,   // Samsung 45W
                    'flexcharge' => 8,                // Vivo 80W
                    'turbopower' => 7,                // Motorola 68W
                    'super fast charging' => 7,       // Samsung 25W
                    'pump express plus' => 7,         // MediaTek
                    'huawei supercharge' => 8,        // Huawei 66W/100W

                    // Standard fast charging (15W-45W)
                    'adaptive fast charging' => 6,    // Samsung 15W
                    'pump express' => 6,              // MediaTek older
                    'mcharge' => 6,                   // Meizu

                    // Universal standards
                    'usb power delivery 3.1' => 9,    // USB-PD 3.1 (240W capable)
                    'usb power delivery 3.0' => 8,    // USB-PD 3.0 (100W)
                    'usb power delivery' => 8,        // USB-PD generic
                    'usb-pd 3.1' => 9,
                    'usb-pd 3.0' => 8,
                    'usb-pd' => 8,
                    'pd 3.1' => 9,
                    'pd 3.0' => 8,
                    'pd' => 8,

                    // Qualcomm Quick Charge
                    'quick charge 5' => 9,            // QC5 (100W+)
                    'qc5' => 9,
                    'quick charge 4+' => 8,           // QC4+ (60W)
                    'qc4+' => 8,
                    'quick charge 4' => 8,            // QC4
                    'qc4' => 8,
                    'quick charge 3+' => 7,           // QC3+ (45W)
                    'qc3+' => 7,
                    'quick charge 3.0' => 6,          // QC3
                    'qc3' => 6,
                    'quick charge 2.0' => 5,          // QC2
                    'qc2' => 5,
                    'quick charge' => 4,              // QC1
                    'qc' => 4,

                    // Battery Cell Technology (bonus points)
                    'dual cell' => 1,                 // Faster charging capability
                    'gan charger' => 1,               // Efficient charging
                    'graphene' => 1,                  // Future tech

                    // Basic/Standard
                    'standard charging' => 3,
                    'basic charging' => 2,
                    'usb charging' => 2,
                    'no fast charging' => 1,
                ],
            ],
        ],
    ],

];

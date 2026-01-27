<?php
return [
    'label' => 'Battery',
    'specs' => [
        'capacity' => [
            'label' => 'Battery Capacity (mAh)',
            'weight' => 40,
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
        ],

        'Fast' => [
            'label' => 'Wired Charging Speed (W)',
            'weight' => 25,
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

        'Wirless' => [
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

        'charging_technology' => [
            'label' => 'Charging Technology',
            'weight' => 10,
            'scale' => [
                // Proprietary fast charging
                'hypercharge' => 10,         // Xiaomi 240W
                'supervooc' => 10,           // OPPO/OnePlus 240W+
                'vooc' => 9,                 // OPPO older
                'warp charge' => 9,          // OnePlus
                'dash charge' => 8,          // OnePlus older
                'super fast charging 2.0' => 9,  // Samsung 45W
                'super fast charging' => 8,  // Samsung 25W
                'adaptive fast charging' => 6,   // Samsung older
                'turbopower' => 7,           // Motorola
                'pump express' => 7,         // MediaTek
                'flexcharge' => 7,           // Vivo

                // Standard technologies
                'usb power delivery' => 8,   // USB-PD
                'usb-pd' => 8,
                'pd' => 8,
                'quick charge 5' => 9,       // Qualcomm QC5
                'quick charge 4+' => 8,      // Qualcomm QC4+
                'quick charge 4' => 8,       // Qualcomm QC4
                'quick charge 3+' => 7,      // Qualcomm QC3+
                'quick charge 3.0' => 7,     // Qualcomm QC3
                'quick charge 2.0' => 6,     // Qualcomm QC2
                'quick charge' => 5,         // Qualcomm QC1

                // Basic
                'standard charging' => 3,
                'basic' => 3,
            ],
            'default' => 3,
        ],

        'type' => [
            'label' => 'Battery Type',
            'weight' => 5,
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

        'Reverce' => [
            'label' => 'Reverse Wireless Charging',
            'weight' => 5,
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
    ],

];

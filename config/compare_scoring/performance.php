<?php
return [
    'label' => 'Performance',
    'weights' => [
        'core_processing' => 45,  // AnTuTu + Overall Speed (The "Muscle")
        'system_fluidity' => 20,  // RAM Type + Storage Speed (The "Flow")
        'ai_intelligence' => 20,  // NPU / AI TOPS (The "Brain")
        'thermal_control' => 15,  // Throttling + Cooling (The "Endurance")
    ],
    'categories' => [
        'core_processing' => [
            'antutu_score' => [
                'label' => 'AnTuTu Benchmark',
                'weight' => 85,
                'ranges' => [
                    // 2024-2025 Flagships
                    ['min' => 2000000, 'score' => 10],  // 2M+ (Snapdragon 8 Elite, A18 Pro)
                    ['min' => 1800000, 'score' => 10],
                    ['min' => 1600000, 'score' => 9],   // SD 8 Gen 3, Dimensity 9300
                    ['min' => 1400000, 'score' => 9],
                    ['min' => 1200000, 'score' => 8],   // SD 8 Gen 2
                    ['min' => 1000000, 'score' => 8],   // SD 8 Gen 1

                    // Mid-range to older flagship
                    ['min' => 800000, 'score' => 7],    // SD 888, SD 7+ Gen 3
                    ['min' => 600000, 'score' => 6],    // SD 870, Dimensity 8200
                    ['min' => 500000, 'score' => 5],    // SD 778G, Dimensity 7200
                    ['min' => 400000, 'score' => 5],    // Mid-range
                    ['min' => 300000, 'score' => 4],    // Budget
                    ['min' => 200000, 'score' => 3],    // Entry-level
                ],
                'default' => 2,
            ],
            'cpu_speed' => [
                'label' => 'CPU Speed',
                'weight' => 15,
                'scale' => [
                    // 2026 Elite Flagships (Qualcomm Snapdragon 8 Elite Gen 5)
                    '4.60' => 10,
                    '4.50' => 10,

                    // 2025/2026 Standard Flagships (Apple A19 Pro, Snapdragon 8 Elite Gen 1)
                    '4.32' => 9,
                    '4.26' => 9,  // Apple A19 Pro peak
                    '4.21' => 9,  // Dimensity 9500 peak
                    '4.00' => 9,

                    // High-End / Premium Mid-range (2025/2026)
                    '3.80' => 8,
                    '3.60' => 8,
                    '3.40' => 8,  // Snapdragon 8 Gen 3 / 8s Gen 4

                    // Mid-range Baseline
                    '3.20' => 7,
                    '3.00' => 7,
                    '2.80' => 6,
                    '2.60' => 6,

                    // Budget / Entry Level
                    '2.40' => 5,
                    '2.20' => 4,
                    '2.00' => 3,
                    '1.80' => 2,
                ],
                'default' => 5,
            ],


        ],
       
        "system_fluidity" => [
            'ram' => [
                'label' => 'RAM',
                'weight' => 25,
                'unit' => [
                    'value' => 'GB',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 24, 'score' => 10],  // Gaming phones
                    ['min' => 18, 'score' => 10],
                    ['min' => 16, 'score' => 9],   // High-end flagship
                    ['min' => 12, 'score' => 9],   // Standard flagship
                    ['min' => 10, 'score' => 8],
                    ['min' => 8, 'score' => 8],    // Mid-range/older flagship
                    ['min' => 6, 'score' => 6],    // Budget/entry mid-range
                    ['min' => 4, 'score' => 4],    // Entry-level
                    ['min' => 3, 'score' => 3],
                    ['min' => 2, 'score' => 2],
                ],
                'default' => 2,
            ],
            'ram_type' => [
                'label' => 'RAM Type',
                'weight' => 35,
                'scale' => [
                    // Latest generation (2024-2025)
                    'lpddr5x' => 10,
                    'lpddr5t' => 10,

                    // Current generation (2021-2024)
                    'lpddr5' => 9,

                    // Previous generation (2019-2021)
                    'lpddr4x' => 7,
                    'lpddr4' => 6,

                    // Older generation (2017-2019)
                    'lpddr3' => 4,

                    // Very old/budget (2014-2017)
                    'lpddr2' => 2,

                    // Ancient (pre-2014)
                    'lpddr' => 1,
                ],
                'default' => 5,
            ],
            'storage_type' => [
                'label' => 'Storage Type',
                'weight' => 40,
                'scale' => [
                    // NVMe (Apple uses this - fastest)
                    'nvme' => 10,
                    'nvme pcie 4.0' => 10,
                    'nvme pcie 3.0' => 10,

                    // UFS 4.x (Latest flagship Android - 2024-2025)
                    'ufs 4.1' => 10,
                    'ufs 4.0' => 10,

                    // UFS 3.x (Standard flagship - 2020-2024)
                    'ufs 3.1' => 9,
                    'ufs 3.0' => 8,

                    // UFS 2.x (Mid-range / Older flagship)
                    'ufs 2.2' => 7,
                    'ufs 2.1' => 6,
                    'ufs 2.0' => 5,

                    // eMMC (Budget phones)
                    'emmc 5.1' => 4,
                    'emmc 5.0' => 3,
                    'emmc 4.5' => 2,
                ],
                'default' => 3,
            ],
        ],
        "thermal_control" => [
            'cooling_type' => [
                'label' => 'Cooling System',
                'weight' => 0, // Hardware-side of thermal control
                'scale' => [
                    'Active Fan + Flowing Liquid' => 10,   // Elite Gaming (RedMagic 11 series)
                    'Active Fan + Vapor Chamber' => 9.5,  // Top-tier Gaming
                    'Vapor Chamber (Dual/Large)' => 8.5,  // Modern Flagships (S26 Ultra, iPhone 17 Pro)
                    'Vapor Chamber (Standard)' => 7.5,  // Standard Flagships / Upper Mid-range
                    'Graphite + Heat Pipe' => 6.0,  // Mid-range standard
                    'Graphite Sheets' => 4.0,  // Budget/Thin devices
                    'Standard/None' => 2.0,  // Legacy/Entry level
                ],
                'default' => 4.0,
            ],
            'cooling_type_master' => [
                'label' => 'Cooling System',
                'weight' => 30,
                'hidden' => true,
                'scale' => [
                    'Active Fan + Flowing Liquid' => 10,   // Elite Gaming (RedMagic 11 series)
                    'Active Fan + Vapor Chamber' => 9.5,  // Top-tier Gaming
                    'Vapor Chamber (Dual/Large)' => 8.5,  // Modern Flagships (S26 Ultra, iPhone 17 Pro)
                    'Vapor Chamber (Standard)' => 7.5,  // Standard Flagships / Upper Mid-range
                    'Graphite + Heat Pipe' => 6.0,  // Mid-range standard
                    'Graphite Sheets' => 4.0,  // Budget/Thin devices
                    'Standard/None' => 2.0,  // Legacy/Entry level
                ],
                'default' => 4.0,
            ],
            'throttling_rate' => [
                'label' => 'Sustained Performance (Stability)',
                'weight' => 70,
                'hidden' => true,
                'unit' => [
                    'value' => '%',
                    'position' => 'after',
                    'space' => false
                ],
                'ranges' => [
                    ['min' => 95, 'score' => 10],  // Perfect stability (usually with active cooling)
                    ['min' => 85, 'score' => 9],   // Excellent (Well-optimized flagships)
                    ['min' => 75, 'score' => 7.5], // Good (Standard high-end behavior)
                    ['min' => 65, 'score' => 5.5], // Average (Throttles to save battery/heat)
                    ['min' => 50, 'score' => 3],   // Poor (Heavy performance drop)
                    ['min' => 0, 'score' => 1],   // Extreme overheating
                ],
                'default' => 7.5, // Assume standard 75% stability if data is missing
            ],
        ],
        "ai_intelligence" => [
            'ai_capability' => [
                'label' => 'AI Performance (NPU)',
                'weight' => 100,
                'unit' => [
                    'value' => 'TOPS',
                    'position' => 'after',
                    'space' => true
                ],
                'ranges' => [
                    // 2026 "Ultra" Tier (Agentic AI Leaders)
                    ['min' => 90, 'score' => 10],  // A19 Pro (140), SD 8 Elite Gen 5/6 (100+), Dimensity 9500 (100+)

                    // 2025/26 Premium Tier
                    ['min' => 70, 'score' => 9],   // A18 Pro (75), Dimensity 9400, Tensor G5 (70+)

                    // High-end Baseline
                    ['min' => 45, 'score' => 8],   // Exynos 2600 (approx 55), SD 8 Gen 3 (45), Dimensity 9300

                    // Mid-range AI
                    ['min' => 25, 'score' => 6],   // Snapdragon 7+ Gen 3, Dimensity 8400 Ultra

                    // Entry AI & Legacy
                    ['min' => 10, 'score' => 4],   // Budget 2025/26 chips
                    ['min' => 0, 'score' => 1],    // Legacy chips (pre-2024 architecture)
                ],
                'default' => 4,
            ],
        ]

    ],

];

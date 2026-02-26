<?php
return [
    'label' => 'Performance',
    'weights' => [
        'core_processing' => 40,  // AnTuTu + Overall Speed (The "Muscle")
        'system_fluidity' => 20,  // RAM Type + Storage Speed (The "Flow")
        'ai_intelligence' => 25,  // NPU / AI TOPS (The "Brain")
        'thermal_control' => 15,  // Throttling + Cooling (The "Endurance")
    ],
    'categories' => [
        'core_processing' => [
            'antutu_score' => [
                'label' => 'AnTuTu Benchmark',
                'weight' => 50,
                'ranges' => [
                    // 2026 Elite Flagships (The 4M+ Club)
                    ['min' => 4000000, 'score' => 10], // Red Magic 11 Pro+, Vivo X300 Pro
                    ['min' => 3700000, 'score' => 9.8], // Standard Snapdragon 8 Elite Gen 5

                    // 2025/2026 High-End (SD 8 Elite Gen 1 / Exynos 2600)
                    ['min' => 3000000, 'score' => 9.2],
                    ['min' => 2500000, 'score' => 8.8],

                    // Mid-range (The new "Budget Flagships")
                    ['min' => 2000000, 'score' => 8], // Snapdragon 8 Gen 4 era
                    ['min' => 1500000, 'score' => 7], // Dimensity 8500 / SD 8s Gen 4

                    // Mainstream Mid-range
                    ['min' => 1000000, 'score' => 6], // Dimensity 8400
                    ['min' => 700000, 'score' => 5],

                    // Budget (Redmi 15C / Entry levels)
                    ['min' => 500000, 'score' => 4],
                    ['min' => 300000, 'score' => 3],
                    ['min' => 0, 'score' => 2],
                ],
                'default' => 2,
            ],
            'geekbench_single' => [
                'label' => 'Geekbench Single-Core',
                'weight' => 18,
                'ranges' => [
                    ['min' => 4000, 'score' => 10],  // Apple A19 Pro / Overclocked SD 8 Elite G5
                    ['min' => 3600, 'score' => 9.5], // Standard SD 8 Elite Gen 5
                    ['min' => 3200, 'score' => 9],   // 2025 Flagships (A18 Pro)
                    ['min' => 2800, 'score' => 8.5], // High-end Mid-range (SD 8 Gen 4)
                    ['min' => 2200, 'score' => 7.5], // Mid-range Baseline
                    ['min' => 1500, 'score' => 6],   // Budget Gaming
                    ['min' => 1000, 'score' => 4],   // Entry Mid-range
                    ['min' => 600, 'score' => 2],   // Legacy
                    ['min' => 0, 'score' => 1],
                ],
                'default' => 3,
            ],
            'geekbench_multi' => [
                'label' => 'Geekbench Multi-Core',
                'weight' => 17,
                'ranges' => [
                    ['min' => 12000, 'score' => 10],  // SD 8 Elite Gen 5 (All-Big-Core logic)
                    ['min' => 10500, 'score' => 9.5], // Dimensity 9500 / A19 Pro
                    ['min' => 9000, 'score' => 9],   // SD 8 Gen 4 / A18 Pro
                    ['min' => 7500, 'score' => 8],   // Premium Mid-range
                    ['min' => 5500, 'score' => 7],   // Standard Mid-range
                    ['min' => 4000, 'score' => 5.5], // Budget Gaming
                    ['min' => 2500, 'score' => 4],   // Entry Level
                    ['min' => 1000, 'score' => 2],   // Legacy
                    ['min' => 0, 'score' => 1],
                ],
                'default' => 3,
            ],
            'cpu_speed' => [
                'label' => 'CPU Speed',
                'weight' => 15,
                'scale' => [
                    '4.74' => 10,  // Snapdragon 8 Elite Gen 5 "For Galaxy"
                    '4.60' => 10,  // Snapdragon 8 Elite Gen 5 Standard
                    '4.21' => 9.8, // Dimensity 9500 (Massive performance despite clock)
                    '4.00' => 9.5, // Apple A19 Pro
                    '3.80' => 9,
                    '3.60' => 8.5, // High-end Midrange
                    '3.20' => 7.5,
                    '2.80' => 6.5, // Average 2026 Midrange
                    '2.40' => 5,
                    '2.00' => 4,   // The new "Budget" baseline
                    '1.80' => 2,
                ],
                'default' => 5,
            ],


        ],

        "system_fluidity" => [
            'ram' => [
                'label' => 'RAM',
                'weight' => 25,
                'ranges' => [
                    ['min' => 16, 'score' => 10], // 2026 High-end baseline
                    ['min' => 12, 'score' => 9.5],
                    ['min' => 8, 'score' => 8],  // Standard for most users
                    ['min' => 6, 'score' => 6],  // Minimum for a smooth 2026 experience
                    ['min' => 4, 'score' => 4],  // Entry-level
                    ['min' => 0, 'score' => 2],
                ],
            ],
            'ram_type' => [
                'label' => 'RAM Type',
                'weight' => 35,
                'scale' => [
                    'lpddr6' => 10,  // Just appearing in late 2026
                    'lpddr5x' => 9.5, // 2026 Flagship Standard
                    'lpddr5' => 8,   // Mid-range
                    'lpddr4x' => 6,   // Budget (Redmi 15C style)
                    'default' => 5,
                ],
            ],
            'storage_type' => [
                'label' => 'Storage Speed',
                'weight' => 40,
                'scale' => [
                    'ufs 5.0' => 10,  // Future-proofing
                    'nvme' => 10,  // Apple
                    'ufs 4.1' => 9.5, // Xiaomi 17 Ultra
                    'ufs 4.0' => 9,
                    'ufs 3.1' => 8,   // Great Mid-range
                    'ufs 2.2' => 6,   // Standard Budget
                    'emmc 5.1' => 3,   // Slow / Avoid
                ],
            ]
        ],
        "thermal_control" => [
            'cooling_type' => [
                'label' => 'Cooling System',
                'weight' => 30,
                'scale' => [
                    // Elite/Active Cooling (Gaming Phones)
                    'Active Fan + Flowing Liquid' => 10,   // RedMagic 11 Pro (Aqua Core)
                    'Active Fan + Vapor Chamber' => 9.5,   // Gaming Tier

                    // 2026 Flagship Standards
                    '3D Dual-Channel IceLoop' => 9.0,   // Xiaomi 17 Ultra (50% better conductivity)
                    'Vapor Chamber (Stainless Steel)' => 8.5, // iPhone 17 Pro / S26 Ultra (New VC standard)
                    'Vapor Chamber (Dual/Large)' => 8.0,

                    // Mid-range & Budget
                    'Vapor Chamber (Standard)' => 7.0,   // Upper Mid-range
                    'Graphite + Heat Pipe' => 5.5,   // Mid-range Baseline
                    'Graphite Sheets' => 4.0,   // Budget (Redmi 15C)
                    'Standard/None' => 2.0,   // Entry level
                ],
                'default' => 4.0,
            ],
            'throttling_rate' => [
                'label' => 'Sustained Performance (Stability)',
                'weight' => 70, // High importance for 2026 "Heat Crisis"
                'hidden' => true,
                'ranges' => [
                    // 10/10: Active Cooling / Fans (The only way to tame 2026 chips)
                    ['min' => 95, 'score' => 10],  // RedMagic 11 Pro (98% stability with fan)

                    // 9/10: Exceptional Passive Cooling (2nm efficiency + Vapor Chambers)
                    ['min' => 85, 'score' => 9],   // iPhone 17 Pro Max (A19 Pro @ 89% stability)

                    // 8/10: High-End Optimization (Exynos HPB / Xiaomi Vapor Systems)
                    ['min' => 75, 'score' => 8],   // Exynos 2600 (82%) / Xiaomi 17 Ultra (80%)

                    // 6/10: Mid-Range / Average Flagship Throttling
                    ['min' => 60, 'score' => 6],   // Snapdragon 8 Gen 5 (Balanced @ 62%)

                    // 4/10: Poor / Extreme "Peak-Only" Flagships
                    ['min' => 45, 'score' => 4],   // S26 Ultra (Often dips to 46-53% in loops)

                    // 1/10: Performance Collapse
                    ['min' => 0, 'score' => 1],   // Pixel 10 Pro (Tensor G5 aggressive drops < 40%)
                ],
                'default' => 7.5,
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
                    // 2026 Agentic Tier (Can run full LLMs locally without Cloud)
                    ['min' => 100, 'score' => 10], // SD 8 Elite Gen 5 (100+), Dimensity 9500 (100+), A19 Pro
                    ['min' => 85, 'score' => 9.5], // Xiaomi 17 Ultra optimized NPU

                    // 2025/26 Premium Tier (Heavy Generative AI / Magic Editor)
                    ['min' => 70, 'score' => 9],  // A18 Pro, Tensor G5 (Google's AI heavy-hitter)
                    ['min' => 55, 'score' => 8.5], // Exynos 2600 / SD 8 Gen 4

                    // Mid-range Baseline (2026 "Standard" for AI apps)
                    ['min' => 40, 'score' => 7],  // Minimum for decent on-device photo expansion
                    ['min' => 25, 'score' => 5],  // Standard Mid-range (SD 7 series)

                    // Budget / Legacy (Slow AI / Cloud-dependent)
                    ['min' => 10, 'score' => 3],  // Entry-level 2026 chips
                    ['min' => 0, 'score' => 1],  // Old chips that freeze during AI tasks
                ],
                'default' => 4,
            ],
        ]

    ],

];

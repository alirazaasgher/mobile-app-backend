<?php
return [
    'label' => 'Performance',
    'antutu_score' => [
        'label' => 'AnTuTu Benchmark',
        'weight' => 40,
        'hidden' => true,
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
    'antutu_score_v10' => [
        'label' => 'AnTuTu Benchmark',
        'weight' => 0,
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
    'antutu_score_v11' => [
        'label' => 'AnTuTu Benchmark v11',
        'weight' => 0,
        'ranges' => [
            // 2026 Elite Flagships (The 4M+ Club)
            ['min' => 4000000, 'score' => 10],  // Red Magic 11 Pro+, Vivo X300 Pro (Dimensity 9500)
            ['min' => 3700000, 'score' => 9.8], // iQOO 15, Snapdragon 8 Elite Gen 5 (Standard)

            // 2025/2026 High-End (SD 8 Elite Gen 1 / Dimensity 9400+)
            ['min' => 3000000, 'score' => 9.2], // Snapdragon 8 Elite, Dimensity 9400+
            ['min' => 2500000, 'score' => 8.8], // Optimized SD 8 Gen 3, Dimensity 9400

            // Upper Mid-range (The "Flagship Killers")
            ['min' => 2000000, 'score' => 8.2], // Snapdragon 8s Gen 4, Dimensity 8400-Ultra
            ['min' => 1500000, 'score' => 7.5], // Dimensity 8450, Snapdragon 8 Gen 2 leftovers

            // Mainstream Mid-range
            ['min' => 1000000, 'score' => 6.5], // Snapdragon 7 Gen 4, Snapdragon 7s Gen 4
            ['min' => 800000, 'score' => 5.5],  // Snapdragon 6 Gen 4

            // Budget / Entry levels
            ['min' => 500000, 'score' => 4.5], // Snapdragon 6 Gen 3, Helio G100+
            ['min' => 300000, 'score' => 3.5], // Entry-level 5G chips
            ['min' => 0, 'score' => 2],        // Legacy / Ultra-low budget
        ],
        'default' => 2,
    ],
    'geekbench_single_v6' => [
        'label' => 'Geekbench Single-Core',
        'weight' => 15,
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
    'geekbench_multi_v6' => [
        'label' => 'Geekbench Multi-Core',
        'weight' => 20,
        'ranges' => [
            ['min' => 12000, 'score' => 10],  // SD 8 Elite Gen 5
            ['min' => 10500, 'score' => 9.5], // Dimensity 9500 / A19 Pro
            ['min' => 9000, 'score' => 9],   // SD 8 Gen 4 / A18 Pro
            ['min' => 7500, 'score' => 8],   // Premium Mid-range
            ['min' => 5500, 'score' => 7],   // Standard Mid-range
            ['min' => 4500, 'score' => 6],   // ← NEW: fills gap
            ['min' => 4000, 'score' => 5.5], // Budget Gaming (4,190 lands here → 5.5)
            ['min' => 3000, 'score' => 4.5], // ← NEW: fills gap
            ['min' => 2500, 'score' => 4],   // Entry Level
            ['min' => 1000, 'score' => 2],   // Legacy
            ['min' => 0, 'score' => 1],
        ],
        'default' => 3,
    ],
    'cpu_speed' => [
        'label' => 'CPU Speed',
        'weight' => 0,
        'scale' => [
            '4.74' => 10,   // SD 8 Elite Gen 5 "For Galaxy"
            '4.60' => 9.8,  // SD 8 Elite Gen 5 Standard
            '4.21' => 9.5,  // Dimensity 9500 (Massive IPC)
            '4.00' => 9.2,  // Apple A19 Pro
            '3.80' => 8.8,  // Standard Flagship
            '3.20' => 8.0,  // High-end Midrange
            '2.80' => 7.2,  // Your Device (SD 7 Gen 4) - Solid Performance
            '2.40' => 6.0,  // Average 2026 Midrange
            '2.00' => 4.5,  // Modern Budget baseline
            '1.80' => 3.0,  // Low-end
        ],
        'default' => 5,
    ],

    'ai_capability' => [
        'label' => 'AI Performance (NPU)',
        'weight' => 10,
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
    'process_node' => [
        'label' => 'Manufacturing Process',
        'weight' => 0, // Informational only (Progress Bar)
        'unit' => [
            'value' => 'nm',
            'position' => 'after',
            'space' => false
        ],
        'ranges' => [
            ['max' => 2, 'score' => 10],
            ['max' => 3, 'score' => 9.5],
            ['max' => 4, 'score' => 8.5],
            ['max' => 5, 'score' => 7.5],
            ['max' => 6, 'score' => 6.0],
            ['max' => 8, 'score' => 4.0],
            ['max' => 14, 'score' => 2.0],
            ['max' => 99, 'score' => 1.0],
        ],
        'default' => 5,
    ],
    'ram' => [
        'label' => 'RAM',
        'weight' => 5,
        'unit' => [
            'value' => 'GB',
            'position' => 'after',
            'space' => true
        ],
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
        'weight' => 0,
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
        'weight' => 10,
        'scale' => [
            'ufs 5.0' => 10,  // Future-proofing
            'nvme' => 10,  // Apple
            'ufs 4.1' => 9.5, // Xiaomi 17 Ultra
            'ufs 4.0' => 9,
            'ufs 3.1' => 8,   // Great Mid-range
            'ufs 2.2' => 6,   // Standard Budget
            'emmc 5.1' => 3,   // Slow / Avoid
        ],
    ],
    'storage_capacity' => [
        'label' => 'Storage Capacity',
        'weight' => 0, // Informational (Progress Bar only)
        'unit' => [
            'value' => 'GB',
            'position' => 'after',
            'space' => true
        ],
        'ranges' => [
            // 2026 Ultra Tier (Pro Creators / Heavy Media)
            ['min' => 1024, 'score' => 10], // 1TB - The Gold Standard for Flagships
            ['min' => 512, 'score' => 9.5], // 512GB - High-end Sweet Spot

            // 2026 Standard Tier
            ['min' => 256, 'score' => 8.5], // 256GB - The new "Minimum" for Premium phones

            // Mid-range / Budget Tier
            ['min' => 128, 'score' => 6.5], // 128GB - Starting to feel tight for AI/4K video
            ['min' => 64, 'score' => 4.0], // 64GB - Entry-level / Budget only
            ['min' => 0, 'score' => 1.0], // Obsolete for 2026
        ],
        'default' => 6,
    ],
];

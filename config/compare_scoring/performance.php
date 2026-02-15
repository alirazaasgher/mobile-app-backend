<?php
return [
    'label' => 'Performance',
    'weights' => [
        'chipset_power' => 35,  // CPU + overall processing power
        'gpu_performance' => 20,  // Graphics & gaming
        'ram_management' => 15,  // Multitasking efficiency
        'storage_management' => 10,  // UFS version / read-write speed
        'thermal_control' => 10,  // Heating & throttling
        'software_optimization' => 10,  // OS optimization & smoothness
    ],
    'categories' => [
        'chipset_power' => [
            'chipset' => [
                'label' => 'Chipset',
                'weight' => 30,
                'scale' => [
                    // Apple (2024-2025)
                    'apple a19 pro' => 10,
                    'apple a18 pro' => 10,
                    'apple a18' => 10,
                    'apple a17 pro' => 10,
                    'apple a17' => 9,
                    'apple a16 bionic' => 9,
                    'apple a15 bionic' => 8,
                    'apple a14 bionic' => 7,

                    // Qualcomm Snapdragon (Flagship 2024-2025)
                    'snapdragon 8 elite' => 10,
                    'snapdragon 8s elite' => 10,
                    'snapdragon 8 gen 5' => 10,
                    'snapdragon 8 gen 4' => 10,
                    'snapdragon 8 gen 3' => 10,
                    'snapdragon 8s gen 3' => 9,
                    'snapdragon 8 gen 2' => 9,
                    'snapdragon 8+ gen 1' => 8,
                    'snapdragon 8 gen 1' => 8,
                    'snapdragon 888' => 7,
                    'snapdragon 870' => 7,
                    'snapdragon 865' => 6,

                    // Qualcomm Snapdragon (Mid-range)
                    'snapdragon 7s gen 4' => 9,
                    'snapdragon 7s gen 3' => 8,
                    'snapdragon 7+ gen 3' => 8,
                    'snapdragon 7 gen 4' => 8,
                    'snapdragon 7 gen 3' => 7,
                    'snapdragon 7+ gen 2' => 7,
                    'snapdragon 7 gen 1' => 6,
                    'snapdragon 778g' => 6,
                    'snapdragon 780g' => 6,
                    'snapdragon 732g' => 5,
                    'snapdragon 730g' => 5,
                    'snapdragon 695' => 5,
                    'snapdragon 6 gen 3' => 5,
                    'snapdragon 6 gen 2' => 4.5,
                    'snapdragon 6 gen 1' => 4,
                    'snapdragon 690' => 4,
                    'snapdragon 685' => 4,
                    'snapdragon 680' => 4,
                    'snapdragon 662' => 3,
                    'snapdragon 460' => 3,

                    // MediaTek Dimensity (Flagship)
                    'dimensity 9400' => 10,
                    'dimensity 9300+' => 10,
                    'dimensity 9300' => 9,
                    'dimensity 9200+' => 9,
                    'dimensity 9200' => 9,
                    'dimensity 9000' => 8,
                    'dimensity 8450' => 8,
                    'dimensity 8300' => 8,
                    'dimensity 8200' => 8,
                    'dimensity 8100' => 7,

                    // MediaTek Dimensity (Mid-range)
                    'dimensity 8000' => 7,
                    'dimensity 7300' => 7,
                    'dimensity 7200' => 6,
                    'dimensity 7050' => 6,
                    'dimensity 6300' => 5,
                    'dimensity 6100+' => 5,
                    'dimensity 6020' => 4,
                    'dimensity 700' => 4,

                    // Google Tensor
                    'google tensor g4' => 9,
                    'google tensor g3' => 8,
                    'google tensor g2' => 7,
                    'google tensor' => 6,

                    // Samsung Exynos
                    'exynos 2500' => 9,
                    'exynos 2400' => 9,
                    'exynos 2200' => 8,
                    'exynos 2100' => 7,
                    'exynos 1680' => 7, // added
                    'exynos 1580' => 6, // Galaxy A55
                    'exynos 1280' => 6, // Galaxy A53 / A33 (very common)
                    'exynos 990' => 6, // still widely used (S20 series)
                    'exynos 1480' => 6,
                    'exynos 1380' => 6,
                    'exynos 1330' => 5,
                    'exynos 980' => 5,
                    'exynos 1200' => 5,
                    'exynos 1080' => 5,
                    'exynos 9611' => 4,
                    'exynos 9610' => 4,
                    'exynos 850' => 3,
                    'exynos 7904' => 3,
                    'exynos 7885' => 3,
                    'exynos 7870' => 2,

                    // Huawei Kirin
                    'kirin 9000s' => 9,
                    'kirin 9030 Pro' => 8,
                    'kirin 9000' => 8,
                    'kirin 990' => 7,

                    // Budget chipsets
                    'helio g99' => 4,
                    'helio g96' => 4,
                    'helio g88' => 3,
                    'helio g85' => 3,
                    'helio p35' => 2,
                    'unisoc t616' => 3,
                    'unisoc t606' => 2,
                ],
                'default' => 2,
            ],
            'cpu' => [
                'label' => 'CPU Cores',
                'weight' => 25,
                'scale' => [
                    // 12+ cores (2024-2025 flagships)
                    '12-core' => 10,
                    'dodeca-core' => 10,

                    // 10 cores (recent flagships)
                    'deca-core' => 9.5,
                    '10-core' => 9.5,

                    // 9 cores (some MediaTek Dimensity/Exynos)
                    'nona-core' => 9,
                    '9-core' => 9,

                    // 8 cores (standard for most phones 2018-2024)
                    'octa-core' => 8.5,
                    '8-core' => 8.5,

                    // 6 cores (efficiency-focused/mid-range)
                    'hexa-core' => 7,
                    '6-core' => 7,

                    // 4 cores (older/budget)
                    'quad-core' => 5,
                    '4-core' => 5,

                    // 2 cores (very old/entry)
                    'dual-core' => 3,
                    '2-core' => 3,
                ],
                'default' => 5,
            ],
            'antutu_score' => [
                'label' => 'AnTuTu Benchmark',
                'weight' => 25,
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
            'ai_capability' => [
                'label' => 'AI Performance (NPU)',
                'weight' => 20,
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

        ],
        'gpu_performance' => [
            'gpu' => [
                'label' => 'GPU',
                'weight' => 70, // Don't score separately (included in chipset)
                'scale' => [
                    // Qualcomm Adreno (2024-2025 Flagship)
                    'adreno 840' => 10,
                    'adreno 830' => 10,
                    'adreno 825' => 10,
                    'adreno 810' => 10,
                    'adreno 750' => 10,
                    'adreno 740' => 9,
                    'adreno 735' => 9,
                    'adreno 730' => 8,
                    'adreno 725' => 8,
                    'adreno 722' => 8,
                    'adreno 720' => 8,

                    // Qualcomm Adreno (Mid-range & Older Flagship)
                    'adreno 710' => 7,
                    'adreno 702' => 7,
                    'adreno 690' => 7,
                    'adreno 680' => 6,
                    'adreno 660' => 6,
                    'adreno 650' => 6,
                    'adreno 642l' => 5,
                    'adreno 640' => 5,
                    'adreno 630' => 5,
                    'adreno 620' => 4,
                    'adreno 619' => 4,
                    'adreno 619l' => 4, // missing added
                    'adreno 618' => 4,
                    'adreno 616' => 3,
                    'adreno 610' => 3,
                    'adreno 506' => 2,
                    'adreno 505' => 2,
                    'adreno 308' => 2,
                    'adreno 306' => 2,
                    'adreno 305' => 2,

                    // Apple GPU (2024-2025)
                    'apple gpu (8-core)' => 10,
                    'apple gpu (7-core)' => 10,
                    'apple gpu (6-core)' => 10,
                    'apple gpu (5-core)' => 9,
                    'apple gpu (4-core)' => 8,
                    'apple gpu (3-core)' => 7,
                    'apple gpu (2-core)' => 5, // legacy added
                    'apple gpu (1-core)' => 3, // legacy added

                    // ARM Mali (Flagship - G7xx series 2023-2025)
                    'mali-g925' => 10,
                    'mali-g920' => 10,
                    'mali-g720' => 9,
                    'mali-g715' => 9,
                    'mali-g715 mc6' => 9, // missing added
                    'mali-g715 mc4' => 8, // missing added
                    'mali-g710' => 8,
                    'mali-g710 mc10' => 9, // missing added
                    'mali-g78' => 8,
                    'mali-g77' => 7,
                    'mali-g76' => 7,

                    // ARM Mali (Mid-range - G6xx series)
                    'mali-g620' => 8,
                    'mali-g615' => 7,
                    'mali-g615 mc2' => 6, // missing added
                    'mali-g610' => 7,
                    'mali-g610 mc4' => 7, // missing added
                    'mali-g68' => 7,
                    'mali-g57' => 6,
                    'mali-g52' => 5,
                    'mali-g51' => 4,

                    // ARM Mali (Budget - G3x/G7x older)
                    'mali-g72' => 6,
                    'mali-g71' => 5,
                    'mali-g31' => 3,

                    // Immortalis (Premium Mali with Ray Tracing)
                    'immortalis-g925' => 10,
                    'immortalis-g920' => 10,
                    'immortalis-g720' => 10,
                    'immortalis-g715' => 9,

                    // Samsung Xclipse (AMD RDNA)
                    'xclipse 530' => 6,
                    'xclipse 540' => 7,
                    'xclipse 550' => 8,
                    'xclipse 940' => 9,

                    // Huawei Maleoon (for Kirin chipsets)
                    'maleoon 935' => 8,
                    'maleoon 950' => 8, // missing added
                    'maleoon 920' => 6, // missing added
                    'maleoon 910' => 7,
                    'maleoon 900' => 6,

                    // PowerVR (Older devices & some budget)
                    'powervr gm9446' => 6,
                    'powervr ge8320' => 4,
                    'powervr ge8300' => 3,
                    'powervr sgx544' => 2, // missing added
                    'powervr sgx543' => 2, // missing added
                    'powervr g6430' => 4,   // missing added

                    // Imagination IMG (Some MediaTek)
                    'img bxm-8-256' => 7,
                    'img gx6250' => 5, // missing added
                    'img gx5300' => 4, // missing added
                ],
                'default' => 5,
            ]
        ],
        'ram_management' => [
            'ram' => [
                'label' => 'RAM',
                'weight' => 70,
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
                'weight' => 30,
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

        ],
        'storage_management' => [
            'storage_type' => [
                'label' => 'Storage Type',
                'weight' => 30,
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

            'storage_capacity' => [
                'label' => 'Storage Capacity',
                'weight' => 70,
                'unit' => [
                    'value' => 'GB',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 1024, 'score' => 10],  // 1TB+
                    ['min' => 512, 'score' => 9],    // 512GB
                    ['min' => 256, 'score' => 8],    // 256GB
                    ['min' => 128, 'score' => 6],    // 128GB
                    ['min' => 64, 'score' => 4],     // 64GB
                    ['min' => 32, 'score' => 2],     // 32GB (very limited)
                ],
                'default' => 2,
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
        ]

    ],

];

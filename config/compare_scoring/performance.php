<?php
return [
    'label' => 'Performance',
    'specs' => [
        'chipset' => [
            'label' => 'Chipset',
            'weight' => 35,
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
                'snapdragon 690' => 4,
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
                'exynos 1480' => 6,
                'exynos 1380' => 6,
                'exynos 1330' => 5,
                'exynos 850' => 3,

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

        'ram' => [
            'label' => 'RAM',
            'weight' => 15,
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

        'storage_type' => [
            'label' => 'Storage Type',
            'weight' => 10,
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
            'weight' => 15,
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

        'antutu_score' => [
            'label' => 'AnTuTu Benchmark',
            'weight' => 15,
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

        'cpu' => [
            'label' => 'CPU Cores',
            'weight' => 5,
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

        // Optional additional specs
        'gpu' => [
            'label' => 'GPU',
            'weight' => 0, // Don't score separately (included in chipset)
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
                'adreno 722' => 8,  // Added - Mid-high tier
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
                'adreno 618' => 4,
                'adreno 616' => 3,
                'adreno 610' => 3,
                'adreno 506' => 2,
                'adreno 505' => 2,

                // Apple GPU (2024-2025)
                'apple gpu (8-core)' => 10,
                'apple gpu (7-core)' => 10,
                'apple gpu (6-core)' => 10,
                'apple gpu (5-core)' => 9,
                'apple gpu (4-core)' => 8,
                'apple gpu (3-core)' => 7,

                // ARM Mali (Flagship - G7xx series 2023-2025)
                'mali-g925' => 10,
                'mali-g920' => 10,
                'mali-g720' => 9,
                'mali-g715' => 9,
                'mali-g710' => 8,
                'mali-g78' => 8,
                'mali-g77' => 7,
                'mali-g76' => 7,

                // ARM Mali (Mid-range - G6xx series)
                'mali-g620' => 8,
                'mali-g615' => 7,
                'mali-g610' => 7,
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

                // Huawei Maleoon (for Kirin chipsets)
                'maleoon 935' => 8,
                'maleoon 910' => 7,
                'maleoon 900' => 6,

                // PowerVR (Older devices & some budget)
                'powervr gm9446' => 6,
                'powervr ge8320' => 4,
                'powervr ge8300' => 3,

                // Imagination IMG (Some MediaTek)
                'img bxm-8-256' => 7,

                // Qualcomm Adreno (Budget/Entry)
                'adreno 308' => 2,
                'adreno 306' => 2,
                'adreno 305' => 2,
            ],
            'default' => 5,
        ],
        'ram_type' => [
            'label' => 'RAM Type',
            'weight' => 5,
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

];

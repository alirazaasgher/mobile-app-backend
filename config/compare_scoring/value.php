<?php
return [
    'label' => 'Value for Money',
    'specs' => [
        'price' => [
            'label' => 'Launch Price (USD)',
            'weight' => 35, // Balanced for total ownership cost
            'ranges' => [
                ['max' => 400, 'score' => 10], // Budget/Mid-range sweet spot
                ['max' => 750, 'score' => 8],  // Premium mid-range (Pixel 9a, Galaxy A56)
                ['max' => 1100, 'score' => 6], // Base flagships (iPhone 17, S25)
                ['max' => 1400, 'score' => 4], // Pro/Ultra flagships (S25 Ultra, iPhone 17 Pro Max)
                ['max' => 2000, 'score' => 2], // Foldables/Ultra-premium
            ],
        ],
        'resale_value_retention' => [
            'label' => 'Estimated 12-Month Resale Retention (%)',
            'weight' => 30,
            'ranges' => [
                ['min_percent' => 65, 'score' => 10], // Apple ecosystem strength (~65-70%)
                ['min_percent' => 55, 'score' => 8],  // Samsung/Pixel solid (~55-60%)
                ['min_percent' => 40, 'score' => 5],  // Average Android brands
                ['min_percent' => 25, 'score' => 2],  // Budget/rapid-depreciation brands
            ],
        ],
        'longevity_score' => [
            'label' => 'Hardware/Software Longevity Score',
            'weight' => 25, // 7-year updates now table stakes for flagships
            'ranges' => [
                ['min_score' => 9.0, 'score' => 10], // 7+ years updates, IP68+, Gorilla Armor
                ['min_score' => 7.5, 'score' => 8], // 6 years updates, IP68
                ['min_score' => 6.0, 'score' => 5], // 4-5 years, IP67
                ['min_score' => 4.0, 'score' => 2], // 3 years or less
            ],
        ],
        'warranty_service' => [
            'label' => 'Warranty & Service Network',
            'weight' => 10, // Replaced obsolete "inclusions"
            'ranges' => [
                ['value' => '2+ years warranty + global service centers', 'score' => 10], // Apple/Samsung
                ['value' => '2 years warranty + regional service', 'score' => 7],        // OnePlus, Sony
                ['value' => '1 year warranty + limited service', 'score' => 4],         // Budget brands
            ],
        ],
    ],
];

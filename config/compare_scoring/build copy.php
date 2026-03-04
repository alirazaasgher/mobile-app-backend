<?php
return [
    'label' => 'Build Quality',
    'weights' => [
        'durability' => 35,  // Increased: The #1 factor for long-term value
        'materials' => 25,  // Slightly decreased: Titanium is now standard in flagships
        'biometrics' => 20,  // Stable: Essential for security and UX
        'design' => 20,  // Decreased: Subjective; ergonomics now matter more than looks
    ],
    'categories' => [
        "materials" => [
            'build_material' => [
                'label' => 'Build Material',
                'weight' => 65,
                'scale' => [
                    // Premium materials
                    'titanium' => 10,
                    'ceramic' => 10,
                    'stainless steel' => 9,
                    'aluminum' => 8,
                    'aluminium' => 8,
                    'metal' => 8,
                    'glass' => 7,
                    'glass front' => 7,
                    'gorilla glass' => 8,
                    'gorilla glass front' => 8,
                    'ceramic shield' => 9,
                    'ceramic shield 2' => 9,
                    'ceramic shield front' => 9,

                    // Mid-range materials
                    'plastic frame' => 5,
                    'polycarbonate' => 5,
                    'polymer' => 5,
                    'nylon fiber' => 5,      // added for mid-range
                    'silicone polymer' => 6, // optional, sometimes used in build

                    // Budget materials
                    'plastic' => 4,
                    'glass back' => 7,       // already included, but can keep for completeness
                ],
                'default' => 5,
            ],

            'back_material' => [
                'label' => 'Back Material',
                'weight' => 35,
                'scale' => [
                    'ceramic' => 10,
                    'ceramic shield' => 9.8,   // tiny edge to pure ceramic backs
                    'gorilla glass victus 2' => 9.2,
                    'gorilla glass victus' => 9.0,
                    'victus 2' => 9.2,
                    'victus' => 9.0,
                    'gorilla glass 7' => 8.0,
                    'glass' => 7.0,
                    'gorilla glass 6' => 7.2,
                    'gorilla glass 5' => 7.0,
                    'matte glass' => 8.2,   // bonus for reduced visibility of scratches
                    'frosted glass' => 8.2,
                    'leather' => 7.8,   // premium feel + decent longevity
                    'vegan leather' => 7.0,
                    'eco leather' => 7.0,
                    'silicone polymer' => 6.5,   // great for drops, bad for scratches/longevity
                    'polycarbonate' => 6.5,   // bump up — very tough against scratches & cracks
                    'plastic' => 4.5,
                    'nylon fiber' => 5.5,
                ],
                'default' => 5,
            ],
        ],
        "durability" => [
            'ip_rating' => [
                'label' => 'Water Resistance Rating',
                'weight' => 40,
                'scale' => [
                    // IP ratings - dust and water protection (IP6X = dust tight)
                    'ip69k' => 10, // Highest rating with high-pressure/temperature cleaning
                    'ip69' => 10,  // Highest rating (dust tight + high pressure/temperature water)
                    'ip68' => 10,  // Dust tight + submersion beyond 1m
                    'ip67' => 8,   // Dust tight + submersion up to 1m
                    'ip66' => 7,   // Dust tight + powerful water jets
                    'ip65' => 6,   // Dust tight + water jets
                    'ip64' => 5,   // Dust tight + water splashes

                    // IP5X ratings (dust protected but not tight)
                    'ip58' => 9,   // Dust protected + extended submersion
                    'ip57' => 7,   // Dust protected + submersion up to 1m
                    'ip56' => 6,   // Dust protected + water jets
                    'ip55' => 5,   // Dust protected + water jets (low pressure)
                    'ip54' => 4,   // Dust protected + water splashes
                    'ip53' => 3,   // Dust protected + water spray
                    'ip52' => 3,   // Dust protected + dripping water
                    'ip48' => 8,
                    // Water resistance only (IPX ratings - no dust protection)
                    'ipx8' => 9,   // Extended submersion (no dust rating)
                    'ipx7' => 7,   // Submersion up to 1m (no dust rating)
                    'ipx6' => 5,   // Powerful water jets (no dust rating)
                    'ipx5' => 4,   // Water jets (no dust rating)
                    'ipx4' => 4,   // Water splashes (no dust rating)

                    // Marketing terms
                    'water resistant' => 5,
                    'splash resistant' => 4,
                    'splash proof' => 4,
                    'none' => 2,
                    'no' => 2,
                ],
                'default' => 2,
            ],
            'glass_protection' => [
                'label' => 'Glass Protection',
                'weight' => 60,
                'scale' => [
                    // Latest generation (2024-2025)
                    'victus 3' => 10,
                    'ceramic shield (latest)' => 10,
                    'dragon crystal glass 3' => 10,
                    'gorilla glass armor 2' => 10,
                    'gorilla glass armor' => 10,

                    // Recent flagship (2023-2024)
                    'victus 2' => 9,
                    'ceramic shield' => 9,
                    'kunlun glass' => 9,
                    'dragon crystal glass 2' => 9,
                    'gorilla glass victus+' => 9,
                    'panda king kong glass' => 9,

                    // Mid-tier flagship (2022-2023)
                    'victus' => 8,
                    'gorilla glass 7' => 8,
                    'gorilla glass 7i' => 8,
                    'victus+' => 8,
                    'dragon crystal glass' => 8,
                    'schott xensation up' => 8,

                    // Older flagship / Modern mid-range (2020-2021)
                    'gorilla glass 6' => 7,
                    'dragontrail pro' => 7,
                    'asahi glass' => 7,

                    // Mid-range (2018-2019)
                    'gorilla glass 5' => 6,
                    'dragontail glass' => 6,
                    'schott xensation' => 6,
                    'agc dragontrail' => 6,

                    // Budget / Older phones (2016-2017)
                    'gorilla glass 4' => 5,
                    'gorilla glass 3' => 5,
                    'panda glass' => 5,
                    'dragontrail x' => 5,

                    // Entry-level / Very old
                    'gorilla glass 2' => 4,
                    'gorilla glass' => 4,
                    'soda-lime glass' => 3,
                    'tempered glass' => 3,
                    'aluminosilicate glass' => 3,

                    // Generic/Unknown
                    'toughened glass' => 2,
                    'reinforced glass' => 2,
                    'standard glass' => 1,
                ],
                'default' => 0,
            ],
        ],
        "design" => [
            'weight' => [
                'label' => 'Weight (grams)',
                'weight' => 60,
                'unit' => [
                    'value' => 'g',
                    'position' => 'after',   // before | after
                    'space' => true           // true => add space, false => no space
                ],
                'ranges' => [
                    // Lightweight (compact phones, premium)
                    ['min' => 120, 'max' => 150, 'score' => 10],
                    ['min' => 150, 'max' => 170, 'score' => 9],

                    // Standard weight (most flagship phones)
                    ['min' => 170, 'max' => 190, 'score' => 8],
                    ['min' => 190, 'max' => 210, 'score' => 7],

                    // Heavy (large batteries, gaming phones)
                    ['min' => 210, 'max' => 230, 'score' => 6],
                    ['min' => 230, 'max' => 250, 'score' => 5],

                    // Very heavy
                    ['min' => 250, 'max' => 300, 'score' => 4],
                    ['min' => 300, 'max' => 400, 'score' => 3],
                ],
                'default' => 5,
            ],

            'thickness' => [
                'label' => 'Thickness (mm)',
                'weight' => 40,
                'unit' => [
                    'value' => 'mm',
                    'position' => 'after',   // before | after
                    'space' => true           // true => add space, false => no space
                ],
                'ranges' => [
                    // Ultra-thin
                    ['max' => 7.0, 'score' => 10],
                    ['max' => 7.5, 'score' => 9],
                    ['max' => 8.0, 'score' => 8],

                    // Standard
                    ['max' => 8.5, 'score' => 7],
                    ['max' => 9.0, 'score' => 6],

                    // Thick (gaming phones, large batteries)
                    ['max' => 10.0, 'score' => 5],
                    ['max' => 11.0, 'score' => 4],
                    ['max' => 12.0, 'score' => 3],
                ],
                'default' => 5,
            ],
        ],
        'biometrics' => [
            'fingerprint_sensor' => [
                'label' => 'Fingerprint Sensor Type',
                'weight' => 100,
                'scale' => [
                    'ultrasonic' => 10,
                    'ultrasonic in-display' => 10,
                    'in-display ultrasonic' => 10,
                    'optical in-display' => 8,
                    'in-display optical' => 8,
                    'in-display' => 8,
                    'under display' => 8,
                    'side-mounted' => 7,
                    'side mounted' => 7,
                    'rear-mounted' => 6,
                    'rear mounted' => 6,
                    'capacitive' => 7,
                    'none' => 2,
                    'face unlock only' => 3,
                ],
                'default' => 5,
            ],
        ],
    ]
];

<?php
return [
    'label' => 'Camera',
    'weights' => [
        'main_camera' => 30,
        'video' => 25,
        'lens_system' => 20,
        'selfie' => 10,
        'features_ai' => 15,
    ],
    'categories' => [

        'main_camera' => [
            'wide' => [
                'label' => 'Main Camera Resolution (MP)',
                'weight' => 40,  // Reduced from 25
                'unit' => [
                    'value' => 'MP',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 200, 'score' => 10],  // Xiaomi 14 Ultra
                    ['min' => 108, 'score' => 9],
                    ['min' => 64, 'score' => 8],
                    ['min' => 50, 'score' => 8],    // Common flagship
                    ['min' => 48, 'score' => 7],
                    ['min' => 32, 'score' => 6],
                    ['min' => 24, 'score' => 5],
                    ['min' => 16, 'score' => 4],
                    ['min' => 12, 'score' => 3],
                    ['min' => 8, 'score' => 2],
                ],
                'default' => 2,
            ],
            'sensor_size' => [
                'label' => 'Main Camera Sensor Size (inches)',
                'weight' => 35,
                'ranges' => [
                    ['min' => 1.0, 'score' => 10],   // 1" (Sony Xperia PRO-I)
                    ['min' => 0.9, 'score' => 10],
                    ['min' => 0.8, 'score' => 9],
                    ['min' => 0.7, 'score' => 9],
                    ['min' => 0.6, 'score' => 8],    // 1/1.3" common flagship
                    ['min' => 0.5, 'score' => 7],    // 1/1.56"
                    ['min' => 0.4, 'score' => 6],    // 1/2.55"
                    ['min' => 0.3, 'score' => 5],
                    ['min' => 0.2, 'score' => 4],
                ],
                'default' => 5,
                // Common formats: "1/1.28"", "1/1.56"", "1/2.55""
            ],
            'wide_aperture' => [
                'label' => 'Main Camera Aperture (f-number)',
                'weight' => 20,
                'unit' => [
                    'value' => 'f/',
                    'position' => 'before',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    // LOWER is BETTER (wider aperture = more light)
                    ['max' => 1.4, 'score' => 10],  // f/1.4 or better (Xiaomi 14 Ultra)
                    ['max' => 1.5, 'score' => 10],  // f/1.5
                    ['max' => 1.6, 'score' => 9],   // f/1.6
                    ['max' => 1.7, 'score' => 9],   // f/1.7
                    ['max' => 1.8, 'score' => 8],   // f/1.8 (common flagship)
                    ['max' => 1.9, 'score' => 8],   // f/1.9
                    ['max' => 2.0, 'score' => 7],   // f/2.0
                    ['max' => 2.2, 'score' => 6],   // f/2.2
                    ['max' => 2.4, 'score' => 5],   // f/2.4
                    ['max' => 2.8, 'score' => 4],   // f/2.8 (budget)
                    ['max' => 3.5, 'score' => 3],   // f/3.5
                ],
                'default' => 4,
            ],
            'flash' => [
                'label' => 'Flash Type',
                'weight' => 5,
                'scale' => [
                    'dual-led' => 8,
                    'quad-led' => 10,
                    'ring led' => 9,
                    'led' => 6,
                    'single led' => 6,
                    'none' => 2,
                ],
                'default' => 6,
            ],
        ],

        'lens_system' => [
            'ultrawide' => [
                'label' => 'Ultrawide Camera (MP)',
                'weight' => 20,  // Reduced from 15
                'unit' => [
                    'value' => 'MP',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 50, 'score' => 10],
                    ['min' => 48, 'score' => 10],
                    ['min' => 32, 'score' => 9],
                    ['min' => 16, 'score' => 8],
                    ['min' => 12, 'score' => 7],
                    ['min' => 8, 'score' => 6],
                    ['min' => 5, 'score' => 5],
                    ['min' => 2, 'score' => 3],
                ],
                'default' => 0,  // Many phones don't have ultrawide
            ],
            'ultrawide_aperture' => [
                'label' => 'Ultrawide Camera Aperture',
                'weight' => 10,
                'unit' => [
                    'value' => 'f/',
                    'position' => 'before',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['max' => 1.8, 'score' => 10],  // f/1.8 (rare, premium)
                    ['max' => 2.0, 'score' => 9],   // f/2.0
                    ['max' => 2.2, 'score' => 8],   // f/2.2 (common flagship)
                    ['max' => 2.4, 'score' => 7],   // f/2.4 (standard)
                    ['max' => 2.6, 'score' => 6],   // f/2.6
                    ['max' => 2.8, 'score' => 5],   // f/2.8
                    ['max' => 3.0, 'score' => 4],   // f/3.0 (budget)
                ],
                'default' => 4,
            ],
            'telephoto' => [
                'label' => 'Telephoto Camera (MP)',
                'weight' => 15,
                'unit' => [
                    'value' => 'MP',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 64, 'score' => 10],
                    ['min' => 50, 'score' => 9],
                    ['min' => 48, 'score' => 9],
                    ['min' => 32, 'score' => 8],
                    ['min' => 24, 'score' => 7],
                    ['min' => 12, 'score' => 6],
                    ['min' => 10, 'score' => 5],
                    ['min' => 8, 'score' => 4],
                ],
                'default' => 0,  // Many phones don't have telephoto
            ],
            'telephoto_aperture' => [
                'label' => 'Telephoto Camera Aperture',
                'weight' => 10,
                'unit' => [
                    'value' => 'f/',
                    'position' => 'before',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['max' => 1.8, 'score' => 10],  // f/1.8 (rare)
                    ['max' => 2.0, 'score' => 9],   // f/2.0
                    ['max' => 2.2, 'score' => 8],   // f/2.2
                    ['max' => 2.5, 'score' => 7],   // f/2.5
                    ['max' => 2.8, 'score' => 6],   // f/2.8 (common)
                    ['max' => 3.4, 'score' => 5],   // f/3.4
                    ['max' => 4.0, 'score' => 4],   // f/4.0
                ],
                'default' => 4,
            ],
            'macro_aperture' => [
                'label' => 'Macro Camera Aperture',
                'weight' => 5,
                'unit' => [
                    'value' => 'f/',
                    'position' => 'before',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['max' => 2.0, 'score' => 10],
                    ['max' => 2.4, 'score' => 8],
                    ['max' => 2.8, 'score' => 6],
                    ['max' => 3.0, 'score' => 5],
                ],
                'default' => 5,
            ],

            'optical_zoom' => [
                'label' => 'Optical Zoom (x)',
                'weight' => 15,
                'unit' => [
                    'value' => 'x',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 10, 'score' => 10],  // 10x (Samsung, Xiaomi)
                    ['min' => 8, 'score' => 9],
                    ['min' => 6, 'score' => 9],
                    ['min' => 5, 'score' => 8],    // 5x (common flagship)
                    ['min' => 3, 'score' => 7],    // 3x
                    ['min' => 2, 'score' => 6],    // 2x
                    ['min' => 1.5, 'score' => 5],
                ],
                'default' => 0,  // No optical zoom
            ],
            'periscope_telephoto' => [
                'label' => 'Periscope Telephoto (MP)',
                'weight' => 20,
                'unit' => [
                    'value' => 'MP',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 64, 'score' => 10],
                    ['min' => 50, 'score' => 9],
                    ['min' => 48, 'score' => 9],
                    ['min' => 32, 'score' => 8],
                    ['min' => 24, 'score' => 7],
                    ['min' => 12, 'score' => 6],
                    ['min' => 10, 'score' => 5],
                ],
                'default' => 0,  // Rare feature
            ],
            'periscope_telephoto_aperture' => [
                'label' => 'Periscope Telephoto Aperture',
                'weight' => 15,
                'unit' => [
                    'value' => 'f/',
                    'position' => 'before',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['max' => 1.8, 'score' => 10],  // f/1.8 (extremely rare)
                    ['max' => 2.0, 'score' => 9],   // f/2.0
                    ['max' => 2.5, 'score' => 8],   // f/2.5
                    ['max' => 2.8, 'score' => 7],   // f/2.8
                    ['max' => 3.0, 'score' => 6],   // f/3.0 (common)
                    ['max' => 3.5, 'score' => 5],   // f/3.5
                    ['max' => 4.5, 'score' => 4],   // f/4.5
                    ['max' => 6.0, 'score' => 3],   // f/6.0 (very narrow)
                ],
                'default' => 4,
            ],
        ],

        'video' => [
            'video_resolution' => [
                'label' => 'Max Video Resolution',
                'weight' => 60,  // Reduced from 15
                'scale' => [
                    '8k@60fps' => 10,
                    '8k@30fps' => 10,
                    '8k@24fps' => 10,
                    '8k' => 10,
                    '4k@120fps' => 10,
                    '4k@60fps' => 9,
                    '4k@30fps' => 8,
                    '4k' => 8,
                    '1080p@60fps' => 7,
                    '1080p@30fps' => 6,
                    '1080p' => 6,
                    '720p' => 4,
                    '480p' => 2,
                ],
                'default' => 5,
            ],
            'stabilization' => [
                'label' => 'Image Stabilization',
                'weight' => 40,  // Increased from 1 - very important!
                'scale' => [
                    'ois + eis' => 10,
                    'optical + electronic' => 10,
                    'gimbal' => 10,  // Vivo X-series
                    'sensor-shift' => 10,  // iPhone
                    'ois' => 8,
                    'optical' => 8,
                    'eis' => 5,
                    'electronic' => 5,
                    'digital' => 4,
                    'none' => 2,
                ],
                'default' => 2,
            ],            // Directional mics
        ],

        'selfie' => [
            'front' => [
                'label' => 'Front Camera (MP)',
                'weight' => 70,  // Reduced from 12
                'unit' => [
                    'value' => 'MP',
                    'position' => 'after',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['min' => 64, 'score' => 10],
                    ['min' => 50, 'score' => 9],
                    ['min' => 32, 'score' => 9],
                    ['min' => 20, 'score' => 8],
                    ['min' => 16, 'score' => 8],
                    ['min' => 12, 'score' => 7],
                    ['min' => 10, 'score' => 6],
                    ['min' => 8, 'score' => 6],
                    ['min' => 5, 'score' => 5],
                    ['min' => 2, 'score' => 4],
                ],
                'default' => 4,
            ],
            'front_aperture' => [
                'label' => 'Front Camera Aperture',
                'weight' => 30,
                'unit' => [
                    'value' => 'f/',
                    'position' => 'before',   // before | after
                    'space' => false           // true => add space, false => no space
                ],
                'ranges' => [
                    ['max' => 1.8, 'score' => 10],
                    ['max' => 2.0, 'score' => 9],
                    ['max' => 2.2, 'score' => 8],
                    ['max' => 2.4, 'score' => 7],
                    ['max' => 2.8, 'score' => 6],
                ],
                'default' => 6,
            ],
        ],

    ]
];

<?php
return [

    'default' => [],

    'gamming' => [
        'display' => [
            'specs' => [
                'size' => 1.3,
                'refresh_rate' => 1.8,           // CRITICAL for gamers
                'brightness_(peak)' => 1.4,
                'pixel_density' => 1.2,
                'hdr_support' => 1.3,
                'screen_ratio' => 1.2,
            ],
        ],
        'performance' => [
            'specs' => [
                'chipset' => 1.8,                // CRITICAL
                'gpu' => 1.9,                    // CRITICAL
                'ram' => 1.6,
                'storage_capacity' => 1.3,
                'cpu' => 1.5,
                'storage_type' => 1.2,           // UFS matters
            ],
        ],
        'camera' => [
            'specs' => [
                'main' => 1.0,
                'video_resolution' => 1.3,       // streaming/recording gameplay
                'stabilization' => 1.2,
                'front' => 0.8,                  // less important
            ],
        ],
        'battery' => [
            'specs' => [
                'capacity' => 1.5,               // long gaming sessions
                'Fast' => 1.4,                   // quick top-ups
            ],
        ],
        'build' => [
            'specs' => [
                'weight' => 0.9,                 // lighter is better for long sessions
                'thickness' => 0.8,
            ],
        ],
        'features' => [
            'specs' => [
                'stereo_speakers' => 1.4,        // important for gaming
                'wifi' => 1.3,                   // online gaming
                '3.5mm_jack' => 1.2,
            ],
        ],
    ],

    'camera_phone' => [
        'display' => [
            'specs' => [
                'size' => 1.2,
                'resolution' => 1.3,             // viewing photos
                'brightness_(peak)' => 1.2,
                'hdr_support' => 1.2,
            ],
        ],
        'performance' => [
            'specs' => [
                'chipset' => 1.2,                // photo processing
                'ram' => 1.1,
                'storage_capacity' => 1.4,       // storing photos/videos
            ],
        ],
        'camera' => [
            'specs' => [
                'main' => 1.8,                   // CRITICAL
                'ultrawide' => 1.6,
                'telephoto' => 1.7,
                'sensor_size' => 1.8,            // CRITICAL
                'optical_zoom' => 1.7,
                'video_resolution' => 1.6,
                'stabilization' => 1.6,
                'front' => 1.3,
            ],
        ],
        'battery' => [
            'specs' => [
                'capacity' => 1.3,               // photo/video shoots
                'Fast' => 1.1,
            ],
        ],
        'build' => [
            'specs' => [
                'ip_rating' => 1.3,              // protection for outdoor photography
            ],
        ],
        'features' => [
            'specs' => [
                'nfc' => 1.0,
                'wifi' => 1.1,
            ],
        ],
    ],

    'battery_life' => [
        'display' => [
            'specs' => [
                'refresh_rate' => 0.7,           // lower refresh = better battery
                'brightness_(typical)' => 1.1,
            ],
        ],
        'performance' => [
            'specs' => [
                'chipset' => 1.2,                // efficiency matters
                'ram' => 1.0,
            ],
        ],
        'camera' => [
            'specs' => [
                'main' => 1.0,
                'video_resolution' => 0.9,       // 4K drains battery
            ],
        ],
        'battery' => [
            'specs' => [
                'capacity' => 1.9,               // CRITICAL
                'Fast' => 1.7,                   // CRITICAL
                'Wirless' => 1.3,
                'Reverce' => 1.2,
            ],
        ],
        'build' => [
            'specs' => [
                'weight' => 0.9,                 // bigger battery = heavier
            ],
        ],
        'features' => [
            'specs' => [
                'nfc' => 1.0,
                'wifi' => 1.0,
            ],
        ],
    ],

    'all_rounder' => [
        'display' => [
            'specs' => [
                'size' => 1.1,
                'refresh_rate' => 1.2,
                'brightness_(peak)' => 1.1,
            ],
        ],
        'performance' => [
            'specs' => [
                'chipset' => 1.3,
                'ram' => 1.2,
                'storage_capacity' => 1.2,
            ],
        ],
        'camera' => [
            'specs' => [
                'main' => 1.3,
                'ultrawide' => 1.2,
                'video_resolution' => 1.2,
            ],
        ],
        'battery' => [
            'specs' => [
                'capacity' => 1.3,
                'Fast' => 1.2,
            ],
        ],
        'build' => [
            'specs' => [
                'ip_rating' => 1.2,
            ],
        ],
        'features' => [
            'specs' => [
                'nfc' => 1.1,
                'stereo_speakers' => 1.1,
            ],
        ],
    ],
];


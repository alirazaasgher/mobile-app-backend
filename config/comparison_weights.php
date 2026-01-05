<?php
// config/comparison_weights.php
return [
    'display' => [
        'weight' => 0.25, // 25% of total score
        'specs' => [
            'size' => ['weight' => 0.10, 'type' => 'optimal_range', 'optimal' => [6.1, 6.8]],
            'type' => ['weight' => 0.20, 'type' => 'ranking', 'values' => [
                'LTPO AMOLED' => 10,
                'AMOLED' => 9,
                'Super AMOLED' => 9,
                'OLED' => 8,
                'IPS LCD' => 6,
                'LCD' => 4,
            ]],
            'resolution' => ['weight' => 0.15, 'type' => 'numeric_higher'],
            'refresh_rate' => ['weight' => 0.20, 'type' => 'numeric_higher'],
            'pixel_density' => ['weight' => 0.15, 'type' => 'numeric_higher'],
            'brightness_(peak)' => ['weight' => 0.10, 'type' => 'numeric_higher'],
            'brightness_(typical)' => ['weight' => 0.05, 'type' => 'numeric_higher'],
            'has_branded_glass' => ['weight' => 0.05, 'type' => 'boolean'],
        ]
    ],
    'performance' => [
        'weight' => 0.20,
        'specs' => [
            'chipset' => ['weight' => 0.60, 'type' => 'benchmark'], // Use AnTuTu/Geekbench scores
            'cpu' => ['weight' => 0.20, 'type' => 'qualitative'],
            'gpu' => ['weight' => 0.20, 'type' => 'qualitative'],
        ]
    ],
    'camera' => [
        'weight' => 0.20,
        'specs' => [
            'main' => ['weight' => 0.40, 'type' => 'camera_scoring'],
            'front' => ['weight' => 0.20, 'type' => 'camera_scoring'],
            'main_video' => ['weight' => 0.25, 'type' => 'video_scoring'],
            'front_video' => ['weight' => 0.15, 'type' => 'video_scoring'],
        ]
    ],
    'battery' => [
        'weight' => 0.15,
        'specs' => [
            'capacity' => ['weight' => 0.50, 'type' => 'numeric_higher'],
            'Fast' => ['weight' => 0.30, 'type' => 'numeric_higher'],
            'Wirless' => ['weight' => 0.10, 'type' => 'numeric_higher'],
            'Reverce' => ['weight' => 0.10, 'type' => 'boolean'],
        ]
    ],
    'software' => [
        'weight' => 0.10,
        'specs' => [
            'os' => ['weight' => 0.30, 'type' => 'version_comparison'],
            'updates' => ['weight' => 0.70, 'type' => 'update_scoring'],
        ]
    ],
    'features' => [
        'weight' => 0.10,
        'specs' => [
            'nfc' => ['weight' => 0.15, 'type' => 'boolean'],
            'stereo_speakers' => ['weight' => 0.20, 'type' => 'boolean'],
            '3.5mm_jack' => ['weight' => 0.10, 'type' => 'boolean'],
            'wifi' => ['weight' => 0.25, 'type' => 'wifi_scoring'],
            'bluetooth_version' => ['weight' => 0.15, 'type' => 'version_comparison'],
            'usb' => ['weight' => 0.15, 'type' => 'usb_scoring'],
        ]
    ],
];
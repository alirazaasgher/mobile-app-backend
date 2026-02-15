<?php
return [
    'label' => 'Connectivity',
    'weights' => [
        'wireless_connectivity' => 35,
        'audio_features' => 20,
        'physical_connectivity' => 25,
        'extras' => 20,
    ],
    'categories' => [
        'wireless_connectivity' => [
            // 'sim' => [
            //     'label' => 'SIM & Identity Tech',
            //     'weight' => 20,
            //     'scale' => [
            //         'iSIM + eSIM + NanoSIM' => 10, // The "Global Traveler" Max (Rare & Premium)
            //         'iSIM + eSIM' => 9.5, // 2026 Ultra-Premium (No mechanical tray)
            //         'dual sim + esim' => 9,   // 2025/26 Flagship Standard (2 Physical + Digital)
            //         'dual esim' => 8.5, // The Apple/High-end Android "Trayless" approach
            //         'single sim + esim' => 7.5, // Standard Mid-range
            //         'single sim' => 5,   // Budget / Legacy
            //         'esim only' => 6.5, // Lower due to lack of physical backup in some regions
            //     ],
            //     'default' => 7.5,
            // ],
            'wifi' => [
                'label' => 'Wi-Fi Version',
                'weight' => 20,
                'scale' => [
                    // Wi-Fi 7 (2024+) - Most specific first
                    '7 (802.11be)' => 10,
                    '802.11be' => 10,
                    '7' => 10,
                    '7 (dual-band)' => 10,
                    '7 (tri-band)' => 10,

                    // Wi-Fi 6E (2021-2024)
                    '802.11ax 6ghz' => 9,
                    '6e' => 9,
                    '6e (dual-band)' => 9,
                    '6e (tri-band)' => 9,

                    // Wi-Fi 6 (2019-2023)
                    '6 (802.11ax)' => 8,
                    '802.11ax' => 8,
                    'wi-fi 6' => 8,
                    '6' => 8,
                    '6 (dual-band)' => 8,
                    '6 (tri-band)' => 8,

                    // Wi-Fi 5 (2014-2019)
                    '802.11ac' => 6,
                    '5' => 6,
                    '5 (dual-band)' => 6,

                    // Wi-Fi 4 (2009-2014)
                    '802.11n' => 4,
                    '4' => 4,
                    '4 (dual-band)' => 4,

                    // Older
                    '802.11g' => 3,
                    '802.11b' => 2,
                ],
                'default' => 5,
            ],

            'bluetooth_version' => [
                'label' => 'Bluetooth Version',
                'weight' => 15,
                'scale' => [
                    // Latest versions
                    '6.0' => 10,
                    '5.4' => 10,
                    '5.3' => 10,
                    '5.2' => 9,
                    '5.1' => 8,
                    '5.0' => 7,

                    // Older versions
                    '4.2' => 5,
                    '4.1' => 4,
                    '4.0' => 4,
                    '3.0' => 3,
                    '2.1' => 2,
                ],
                'default' => 5,
            ],
            'nfc' => [
                'label' => 'NFC',
                'weight' => 15,
                'scale' => [
                    'yes' => 10,
                    'true' => 10,
                    'supported' => 10,
                    'available' => 10,
                    'no' => 2,
                    'false' => 2,
                    'not supported' => 2,
                    'none' => 2,
                ],
                'default' => 2,
            ],
        ],

        'audio_features' => [
            'stereo_speakers' => [
                'label' => 'Stereo Speakers',
                'weight' => 15,
                'scale' => [
                    'yes' => 10,
                    'true' => 10,
                    'dual' => 10,
                    'stereo' => 10,
                    'quad' => 10,
                    'dolby atmos' => 10,
                    'harman kardon' => 10,
                    'no' => 3,
                    'false' => 3,
                    'mono' => 3,
                    'single' => 3,
                ],
                'default' => 3,
            ],

            '3.5mm_jack' => [
                'label' => '3.5mm Headphone Jack',
                'weight' => 10,
                'scale' => [
                    'yes' => 10,
                    'true' => 10,
                    'supported' => 10,
                    'available' => 10,
                    'no' => 5,
                    'false' => 5,
                    'not supported' => 5,
                    'none' => 5,
                ],
                'default' => 5,
            ],
        ],

        'physical_connectivity' => [
            'usb' => [
                'label' => 'USB Type',
                'weight' => 15,
                'scale' => [
                    // USB-C with high-speed standards
                    'usb 4.0' => 10,
                    'usb4' => 10,
                    'thunderbolt 4' => 10,
                    'thunderbolt 3' => 10,

                    'usb 3.2 gen 2' => 9,
                    'usb-c 3.2' => 9,
                    'type-c 3.2' => 9,
                    'usb 3.2' => 9,

                    'usb 3.1' => 8,
                    'usb-c 3.1' => 8,
                    'type-c 3.1' => 8,

                    'usb 3.0' => 7,
                    'usb-c 3.0' => 7,
                    'type-c 3.0' => 7,

                    // USB-C 2.0 (MUST come before generic type-c)
                    'type-c 2.0' => 6,
                    'usb-c 2.0' => 6,
                    'usb 2.0 type-c' => 6,

                    // Standard USB-C (generic - matches last)
                    'usb type-c' => 7,
                    'usb-c' => 7,
                    'type-c' => 7,

                    // Older standards
                    'micro usb 2.0' => 3,
                    'micro usb' => 3,
                    'micro-usb' => 3,

                    // Lightning (Apple)
                    'lightning' => 5,
                ],
                'default' => 5,
            ],
        ],

        'extras' => [
            'infrared' => [
                'label' => 'Infrared (IR Blaster)',
                'weight' => 5,
                'scale' => [
                    'yes' => 10,
                    'true' => 10,
                    'supported' => 10,
                    'ir blaster' => 10,
                    'no' => 2,
                    'false' => 2,
                    'none' => 2,
                ],
                'default' => 2,
            ],
        ],

    ],
];

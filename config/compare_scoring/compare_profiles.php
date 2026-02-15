<?php
return [

    'balanced' => [
        'weights' => [
            'performance' => 20,
            'display' => 19,
            'camera' => 19,
            'battery' => 16,
            'software' => 10,
            'build' => 8,
            'connectivity' => 8,
        ],
        "display" => require __DIR__ . '/display.php',
        "camera" => require __DIR__ . '/camera.php',
        "battery" => require __DIR__ . '/battery.php',
        "performance" => require __DIR__ . '/performance.php',
        "build" => require __DIR__ . '/build.php',
        "connectivity" => require __DIR__ . '/connectivity.php'
    ],

    'gaming' => [
        'display' => [
            'specs' => [
                'refresh_rate' => 1.9,        // 144Hz+ critical
                'touch_sampling_rate' => 1.7, // High touch response
                'response_time_ms' => 1.6,    // Low ghosting
                'brightness_peak' => 1.5,     // Outdoor visibility
                'hdr_support' => 1.4,         // HDR gaming
                'size' => 1.3,                // Immersion
                'screen_ratio' => 1.2,        // Wide aspect for games
                'pixel_density' => 1.1,       // Less important
                'resolution' => 1.2,          // 1080p enough, 1440p nice
            ],
        ],
        'performance' => [
            'specs' => [
                'gpu' => 2.0,                 # Most critical
                'cooling_system' => 1.8,      # Sustained performance
                'chipset' => 1.7,             # Overall platform
                'cpu' => 1.6,                 # Game logic/loading
                'ram_capacity' => 1.5,        # 12GB+ preferred
                'ram_type' => 1.4,            # LPDDR5X better
                'storage_type' => 1.4,        # UFS 4.0 for load times
                'storage_capacity' => 1.3,    # 256GB+ for games
            ],
        ],
        'camera' => [
            'specs' => [
                'video_resolution' => 1.3,    # Game recording
                'stabilization' => 1.2,       # Smooth recording
                'main_camera_mp' => 1.0,      # Not important
                'front_camera_mp' => 0.8,     # Least important
                'low_light_performance' => 1.0,
            ],
        ],
        'battery' => [
            'specs' => [
                'battery_life_hours' => 1.7,  # Gaming endurance
                'capacity_mah' => 1.6,        # Raw capacity
                'fast_charging_w' => 1.5,     # Quick top-ups
                'cooling_while_charging' => 1.4, # Bypass charging
                'wireless_charging' => 1.0,   # Not important
                'reverse_charging' => 0.8,    # Not needed
            ],
        ],
        'build' => [
            'specs' => [
                'cooling_material' => 1.6,    # Vapor chamber etc
                'weight_g' => 0.7,            # Lighter better
                'thickness_mm' => 0.8,        # Thinner better
                'ip_rating' => 1.2,           # Some protection
                'build_material' => 1.1,
            ],
        ],
        'features' => [
            'specs' => [
                'game_triggers' => 1.8,       # Shoulder buttons
                'game_mode_software' => 1.7,  # Performance modes
                'audio_latency' => 1.6,       # Low lag audio
                'stereo_speakers' => 1.5,     # Immersive sound
                'wifi_version' => 1.5,        # Wi-Fi 6E for low latency
                '3.5mm_jack' => 1.4,          # Wired headphones
                'haptics_vibration' => 1.3,   # Tactile feedback
                'nfc' => 0.9,                 # Not important
                'fingerprint_sensor' => 1.2,
            ],
        ],
    ],

    'camera' => [
        'display' => [
            'specs' => [
                'brightness_peak' => 1.7,     # Outdoor photo review
                'hdr_support' => 1.6,         # HDR photo viewing
                'color_accuracy' => 1.6,      # True-to-life colors
                'resolution' => 1.5,          # High res for detail
                'pixel_density' => 1.4,       # Sharpness
                'size' => 1.3,                # Good viewing area
                'refresh_rate' => 1.1,        # Not critical
                'screen_ratio' => 1.0,
            ],
        ],
        'performance' => [
            'specs' => [
                'image_signal_processor' => 1.8, # Dedicated ISP
                'neural_processor' => 1.7,       # AI photography
                'chipset' => 1.6,                # Overall performance
                'ram_capacity' => 1.5,           # Photo processing
                'storage_type' => 1.5,           # Fast photo saving
                'storage_capacity' => 1.6,       # Lots of photos/videos
                'cpu' => 1.4,
                'gpu' => 1.2,
            ],
        ],
        'camera' => [
            'specs' => [
                'main_sensor_size' => 2.0,       # Most important
                'low_light_performance' => 1.9,  # Night photography
                'optical_zoom' => 1.8,           # True zoom
                'telephoto_mp' => 1.8,           # Zoom quality
                'main_camera_mp' => 1.7,         # Resolution
                'ultrawide_mp' => 1.7,           # Wide shots
                'stabilization' => 1.7,          # OIS critical
                'video_resolution' => 1.7,       # 8K video
                'aperture_size' => 1.6,          # Low light ability
                'portrait_mode' => 1.5,          # Depth effects
                'pro_mode_features' => 1.5,      # Manual controls
                'front_camera_mp' => 1.4,        # Selfies
                'macro_camera' => 1.2,           # Bonus feature
            ],
        ],
        'battery' => [
            'specs' => [
                'battery_life_hours' => 1.6,     # All-day shooting
                'capacity_mah' => 1.5,
                'fast_charging_w' => 1.3,        # Quick top-up
                'wireless_charging' => 1.2,
                'reverse_charging' => 1.0,
            ],
        ],
        'build' => [
            'specs' => [
                'ip_rating' => 1.7,              # Outdoor protection
                'camera_bump_protection' => 1.5, # Lens durability
                'build_material' => 1.3,
                'weight_g' => 1.0,
                'thickness_mm' => 1.0,
            ],
        ],
        'features' => [
            'specs' => [
                'raw_photo_support' => 1.8,      # Professional editing
                'manual_video_controls' => 1.7,  # Videography
                'microphone_quality' => 1.6,     # Video audio
                'stereo_speakers' => 1.3,        # Video playback
                'wifi_version' => 1.3,           # Fast photo transfer
                'bluetooth_version' => 1.2,
                'nfc' => 1.2,
                '3.5mm_jack' => 1.1,             # External mic
            ],
        ],
    ],

    'battery' => [
        'display' => [
            'specs' => [
                'refresh_rate' => 0.6,           # Lower saves battery
                'brightness_typical' => 1.3,     # Power-efficient
                'adaptive_refresh' => 1.5,       # Auto adjusts
                'display_technology' => 1.4,     # AMOLED more efficient
                'resolution' => 0.8,             # Lower res saves power
                'size' => 0.9,                   # Smaller = less power
                'brightness_peak' => 1.1,
            ],
        ],
        'performance' => [
            'specs' => [
                'power_efficiency' => 1.9,       # Chip efficiency
                'chipset' => 1.5,                # Efficient chip
                'battery_saver_modes' => 1.7,    # Software optimization
                'cpu' => 1.3,
                'ram_capacity' => 1.2,
                'gpu' => 1.1,                    # Less important
                'storage_type' => 1.0,
            ],
        ],
        'camera' => [
            'specs' => [
                'main_camera_mp' => 1.0,
                'video_resolution' => 0.7,       # 4K/8K drains battery
                'stabilization' => 1.1,
                'front_camera_mp' => 0.9,
                'low_light_performance' => 1.0,
            ],
        ],
        'battery' => [
            'specs' => [
                'battery_life_hours' => 2.0,     # Most important
                'capacity_mah' => 1.8,
                'fast_charging_w' => 1.7,
                'charging_efficiency' => 1.6,    # Heat management
                'wireless_charging' => 1.3,
                'reverse_charging' => 1.0,       # Optional
                'battery_health_features' => 1.5, # Longevity
            ],
        ],
        'build' => [
            'specs' => [
                'weight_g' => 0.8,               # Lighter better
                'thickness_mm' => 0.7,           # Can be thicker for battery
                'ip_rating' => 1.2,
                'build_material' => 1.0,
            ],
        ],
        'features' => [
            'specs' => [
                'nfc' => 1.1,
                'wifi_version' => 1.2,
                'bluetooth_version' => 1.1,
                'stereo_speakers' => 1.0,
                '3.5mm_jack' => 1.1,
                'always_on_display' => 0.6,      # Drains battery
            ],
        ],
    ],

    'budget_conscious' => [
        'display' => [
            'specs' => [
                'size' => 1.3,
                'refresh_rate' => 1.4,           # 90Hz nice to have
                'brightness_typical' => 1.3,
                'resolution' => 1.2,
                'display_technology' => 1.1,
                'hdr_support' => 0.9,            # Premium feature
                'pixel_density' => 1.0,
            ],
        ],
        'performance' => [
            'specs' => [
                'price_to_performance' => 1.9,   # Value ratio
                'chipset' => 1.5,
                'ram_capacity' => 1.4,
                'storage_capacity' => 1.4,
                'cpu' => 1.3,
                'gpu' => 1.2,
                'storage_type' => 1.1,
                'cooling_system' => 0.9,         # Not essential
            ],
        ],
        'camera' => [
            'specs' => [
                'main_camera_mp' => 1.3,
                'video_resolution' => 1.1,       # 4K enough
                'ultrawide_mp' => 1.2,           # Useful extra
                'front_camera_mp' => 1.2,
                'stabilization' => 1.1,
                'low_light_performance' => 1.0,
                'telephoto_mp' => 0.8,           # Luxury feature
            ],
        ],
        'battery' => [
            'specs' => [
                'capacity_mah' => 1.6,
                'battery_life_hours' => 1.5,
                'fast_charging_w' => 1.4,
                'wireless_charging' => 0.7,      # Premium feature
                'reverse_charging' => 0.5,       # Not needed
            ],
        ],
        'build' => [
            'specs' => [
                'build_durability' => 1.4,       # Should last
                'weight_g' => 1.0,
                'thickness_mm' => 1.0,
                'ip_rating' => 1.2,              # Basic splash resistance
                'build_material' => 1.1,
            ],
        ],
        'features' => [
            'specs' => [
                'nfc' => 1.3,                    # Mobile payments
                'stereo_speakers' => 1.2,
                '3.5mm_jack' => 1.4,             # Save on headphones
                'wifi_version' => 1.2,
                'bluetooth_version' => 1.1,
                'fingerprint_sensor' => 1.3,
                'face_unlock' => 1.2,
                'microsd_support' => 1.5,        # Expandable storage
            ],
        ],
    ],

    'media_consumer' => [
        'display' => [
            'specs' => [
                'size' => 1.8,                   # Large screen
                'hdr_support' => 1.7,            # HDR content
                'brightness_peak' => 1.6,        # Good visibility
                'color_accuracy' => 1.6,         # True colors
                'resolution' => 1.6,             # 1440p+ preferred
                'display_technology' => 1.5,     # AMOLED for contrast
                'refresh_rate' => 1.4,           # Smooth scrolling
                'screen_ratio' => 1.3,           # Wide content
                'pixel_density' => 1.4,
            ],
        ],
        'performance' => [
            'specs' => [
                'media_decoding' => 1.7,         # Video codec support
                'chipset' => 1.4,
                'ram_capacity' => 1.3,           # Multitasking
                'storage_capacity' => 1.6,       # Lots of media
                'storage_type' => 1.3,           # Fast loading
                'gpu' => 1.2,                    # Some gaming
                'cpu' => 1.2,
            ],
        ],
        'camera' => [
            'specs' => [
                'video_resolution' => 1.5,       # Video recording
                'stabilization' => 1.4,          # Smooth video
                'main_camera_mp' => 1.2,
                'front_camera_mp' => 1.3,        # Video calls
                'low_light_performance' => 1.1,
                'ultrawide_mp' => 1.1,
            ],
        ],
        'battery' => [
            'specs' => [
                'battery_life_hours' => 1.7,     # Long viewing
                'capacity_mah' => 1.6,
                'fast_charging_w' => 1.4,
                'wireless_charging' => 1.2,
                'reverse_charging' => 0.9,
            ],
        ],
        'build' => [
            'specs' => [
                'weight_g' => 0.8,               # Lighter for holding
                'thickness_mm' => 0.9,           # Thin
                'screen_to_body_ratio' => 1.4,   # More screen
                'build_material' => 1.2,
                'ip_rating' => 1.1,
            ],
        ],
        'features' => [
            'specs' => [
                'stereo_speakers' => 1.9,        # Critical for media
                'audio_quality' => 1.8,          # DAC quality
                '3.5mm_jack' => 1.7,             # Wired headphones
                'bluetooth_codecs' => 1.6,       # LDAC, aptX
                'haptics_vibration' => 1.4,      # Immersive feedback
                'wifi_version' => 1.4,           # Fast streaming
                'bluetooth_version' => 1.3,
                'nfc' => 1.0,
            ],
        ],
    ],

    'business_professional' => [
        'display' => [
            'specs' => [
                'brightness_peak' => 1.7,        # Outdoor readability
                'size' => 1.4,                   # Productivity
                'color_accuracy' => 1.3,         # Professional work
                'refresh_rate' => 1.2,           # Smooth UI
                'resolution' => 1.3,
                'hdr_support' => 1.1,
                'screen_ratio' => 1.1,
            ],
        ],
        'performance' => [
            'specs' => [
                'ram_capacity' => 1.7,           # Multitasking
                'chipset' => 1.6,
                'storage_capacity' => 1.7,       # Documents/files
                'storage_type' => 1.5,           # Fast access
                'cpu' => 1.5,
                'security_features' => 1.6,      # Hardware security
                'gpu' => 1.1,
            ],
        ],
        'camera' => [
            'specs' => [
                'front_camera_mp' => 1.6,        # Video conferencing
                'video_resolution' => 1.5,       # Video calls
                'microphone_quality' => 1.7,     # Clear audio in calls
                'stabilization' => 1.4,          # Stable video
                'main_camera_mp' => 1.3,         # Document scanning
                'low_light_performance' => 1.2,
            ],
        ],
        'battery' => [
            'specs' => [
                'battery_life_hours' => 1.8,     # All-day work
                'capacity_mah' => 1.6,
                'fast_charging_w' => 1.5,
                'wireless_charging' => 1.4,      # Convenient
                'reverse_charging' => 1.2,       # Charge accessories
                'battery_health_features' => 1.3,
            ],
        ],
        'build' => [
            'specs' => [
                'ip_rating' => 1.7,              # Durability
                'build_material' => 1.5,         # Premium feel
                'weight_g' => 1.1,               # Can be heavier
                'thickness_mm' => 1.0,
                'durability_tests' => 1.4,       # MIL-STD etc
            ],
        ],
        'features' => [
            'specs' => [
                'security_updates' => 1.9,       # Long-term support
                'nfc' => 1.8,                    # Payments/access
                'enterprise_features' => 1.7,    # MDM, Knox, etc
                'wifi_version' => 1.6,           # Reliable connectivity
                'bluetooth_version' => 1.5,      # Accessories
                'desktop_mode' => 1.6,           # Samsung DeX etc
                'fingerprint_sensor' => 1.7,
                'face_unlock' => 1.5,
                'stereo_speakers' => 1.4,
                '3.5mm_jack' => 1.0,
            ],
        ],
    ],

];

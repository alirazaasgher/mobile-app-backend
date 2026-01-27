<?php
return [

    'balanced' => [
        '2023' => [
            'weights' => [
                'performance' => 20,
                'display' => 18,
                'camera' => 18,
                'battery' => 18,
                'software' => 8,
                'build' => 10,
                'features' => 8,
            ],
            'spec_weights' => [
                'performance' => [
                    'chipset' => 1.3, // Baseline architecture
                    'gpu' => 1.2, // Boosted: Crucial for high-refresh 2023 gaming
                    'cpu' => 1.1,
                    'antutu_score' => 1.2,
                    'geekbench_multi_score' => 1.0,
                    'ram_capacity' => 1.0,
                    'ram_type' => 1.1, // Increased: Differentiates LPDDR5X vs LPDDR5
                    'storage_capacity' => 0.9, // Dropped slightly: Capacity doesn't equal speed
                    'storage_type' => 1.2, // Increased: UFS 4.0 vs 3.1 is a massive 2023 jump
                    'cooling_system' => 1.2, // Kept high: Hardware (VC area, etc.)
                    'thermal_management' => 1.0, // Increased: Logic/Software throttling control
                ],
                'display' => [
                    'brightness_peak' => 1.3, // ✅ Perfect
                    'refresh_rate' => 1.2, // ✅
                    'size' => 1.2, // ↑ Could argue 1.2 - size drove purchase decisions
                    'display_technology' => 1.1, // ✅
                    'hdr_support' => 1.0, // ↓ To 1.0 - still not mainstream in content
                    'pixel_density' => 1.0, // ✅
                    'type' => 1.0, // ✅
                    'pwm_flicker' => 0.8, // ↓ To 0.8 - truly niche concern in 2023
                    'color_accuracy' => 0.8, // ↓ To 0.8 - reviewers cared, users didn't
                    'touch_sampling' => 0.7, // ↓ To 0.7 - only gaming phones emphasized this
                ],
                'camera' => [
                    'low_light_performance' => 1.4, // ✅ Perfect leader
                    'main_sensor_size' => 1.4, // ✅ Co-leader
                    'computational_photography' => 1.3, // ✅ Critical addition
                    'optical_zoom' => 1.2, // ✅ Right level
                    'stabilization' => 1.2, // ✅
                    'color_science' => 1.1, // ✅ Important differentiator
                    'portrait_mode' => 1.1, // ✅
                    'ultrawide_quality' => 1.0, // ✅
                    'telephoto_quality' => 1.0, // ✅
                    'ai_processing' => 1.0, // ✅
                    'video_stabilization' => 0.9, // ✅
                    'video_resolution' => 0.9, // ✅
                    'main_camera_mp' => 0.8, // ✅
                    'front_camera_mp' => 0.7, // ✅
                    'macro_camera' => 0.5, // ✅
                    'depth_sensor' => 0.4, // ✅
                ],
                'battery' => [
                    'screen_on_time' => 1.6, // 🥇 ACTUAL LEADER: Every review's #1 metric
                    'battery_life_hours' => 1.5, // ✅ Perfect - but SOT is the measured version
                    'charging_curve' => 1.4, // 🥈 CRITICAL: 0-50% speed > 0-100% speed
                    'fast_charging_w' => 1.3, // ✅ Marketing wars (65W vs 100W vs 150W)
                    'standby_drain' => 1.2, // ⚡️ Always-on displays & 5G killed idle battery
                    'capacity_mah' => 1.1, // ✅ Important but not definitive
                    'battery_health' => 1.0, // 🔋 New awareness: 80% limit, degradation
                    'wireless_charging' => 0.9, // ✅ Expected in flagships
                    'charging_tech' => 0.8, // 🔌 GaN chargers, PD 3.1, PPS
                    'thermal_charging' => 0.7, // 🌡️ Fast charging without overheating
                    'reverse_charging' => 0.5, // ✅ Niche feature
                    'adapter_included' => 0.4, // 📦 Apple removal made this a consideration
                ],
                'build' => [
                    'water_resistance' => 1.4, // 🥇 "Can it survive rain or accidental drops in water?"
                    'scratch_resistance' => 1.3, // "Do I need a screen protector?"
                    'premium_feel' => 1.3, // "Does it feel cheap or expensive in hand?"
                    'drop_durability' => 1.2, // "Will the screen crack if I drop it?"
                    'comfortable_weight' => 1.1, // "Not too heavy for daily use"
                    'good_looking_design' => 1.0, // "Looks nice and modern"
                    'color_options' => 0.9, // "Comes in colors I like"
                    'fingerprint_resistance' => 0.8, // "Doesn't show smudges easily"
                    'button_quality' => 0.7, // "Buttons feel solid, not loose"
                    'thin_borders' => 0.6, // "Looks more futuristic"
                ],

            ],
        ],
        '2025' => [
            'weights' => [
                'performance' => 20,
                'display' => 19,
                'camera' => 19,
                'battery' => 16,
                'software' => 10,
                'build' => 8,
                'features' => 8,
            ],
            'spec_weights' => [
                'display' => [
                    'refresh_rate' => 1.3, // ↑ Could argue 1.3 - LTPO 3.0 will be THE battery saver
                    'pwm_flicker' => 1.2, // ✅ Perfect
                    'brightness_peak' => 1.1, // ↓ To 1.1 - dimming range matters more than peak
                    'adaptive_brightness_ai' => 1.1, // NEW: AI-driven auto-brightness that actually works
                    'display_technology' => 1.1, // ✅
                    'color_accuracy' => 1.0, // ✅
                    'hdr_support' => 1.0, // ✅
                    'size' => 1.0, // ✅
                    'pixel_density' => 1.0, // ✅
                    'ultra_dark_mode' => 1.0, // ↑ To 1.0 - critical for always-on AI assistants
                    'type' => 0.9, // ↓ OLED so universal it matters less
                    'touch_sampling' => 0.8, // ↓ Further - truly niche
                    'screen_protection' => 0.8, // NEW: Ceramic Shield vs Gorilla Glass Victus 3
                ],
                'performance' => [
                    'chipset' => 1.3, // ✅ Perfect
                    'npu_ai_performance' => 1.3, // ↑ Could argue for top billing
                    'gpu' => 1.1, // ↓ Slightly if AI dominates
                    'ai_feature_latency' => 1.1, // NEW: User experience metric
                    'thermal_management' => 1.1, // ✅
                    'storage_type' => 1.1, // ✅
                    'ram_type' => 1.1, // ✅
                    'antutu_score' => 1.0, // ↓ Further to 1.0
                    'cpu' => 1.0,
                    'cooling_system' => 1.0,
                    'ram_capacity' => 1.0,
                    'geekbench_multi_score' => 0.8, // ↓ Benchmarks less relevant
                    'storage_capacity' => 0.8,
                ],
                'camera' => [
                    'ai_processing' => 1.5, // ↑ Could be #1 - AI will define camera quality
                    'low_light_performance' => 1.4, // ✅ Still critical
                    'computational_zoom' => 1.3, // NEW: AI-powered 2x-10x zoom quality
                    'main_sensor_size' => 1.2, // ↓ Further to 1.2 - AI reduces hardware dependency
                    'optical_zoom' => 1.2, // ✅
                    'video_ai_features' => 1.2, // NEW: AI Cinematic mode, object tracking
                    'stabilization' => 1.1, // ✅
                    'video_resolution' => 1.1, // ✅
                    'telephoto_quality' => 1.0, // CHANGED: More than MP
                    'portrait_ai' => 1.0, // NEW: AI-generated studio lighting
                    'ultrawide_quality' => 0.9, // CHANGED: More than MP
                    'main_camera_mp' => 0.8, // ↓ Further to 0.8
                    'front_camera_ai' => 0.8, // NEW: Vlogger AI features
                    'front_camera_mp' => 0.7, // ↓ MP less important than AI
                    'macro_camera' => 0.4, // ↓ Further - computational macro from ultrawide
                    'multi_frame_hdr' => 0.8, // NEW: Standardized but less differentiating
                ],
                'battery' => [
                    'ai_power_management' => 1.5, // 🥇 LEADER: AI predicts usage, optimizes everything
                    'battery_life_hours' => 1.4, // ✅ Still critical but AI-enhanced
                    'thermal_efficiency' => 1.3, // 🔥 Heat management for battery health
                    'fast_charging_curve' => 1.2, // ⚡️ 0-80% in 15min is the new race
                    'battery_health_tech' => 1.2, // ⬆ Guaranteed 5-year health is a selling point
                    'capacity_mah' => 1.1, // ⬇ New battery tech makes mAh less relevant
                    'wireless_charging' => 1.0, // ✅ Qi2 standard
                    'sustainable_charging' => 0.9, // 🌱 Charges when grid is greenest
                    'fast_charging_w' => 0.9, // ⬇ Diminishing returns indeed
                    'reverse_wireless' => 0.7, // NEW: Qi2 reverse charging for accessories
                    'battery_swappability' => 0.6, // NEW: Some brands revive removable batteries
                    'reverse_charging' => 0.5, // ⬇ Niche
                ],
                'build' => [
                    'scratch_resistance' => 1.4, // 🥇 "Can I go without a screen protector?"
                    'water_proofing' => 1.3, // "Can it survive rain or toilet drops?"
                    'premium_look_feel' => 1.2, // "Does it look/feel expensive?"
                    'drop_protection' => 1.2, // "Will it crack if I drop it?"
                    'comfortable_weight' => 1.1, // "Not too heavy for pockets/hands"
                    'repair_availability' => 1.0, // NEW: "Can I get it fixed easily/cheaply?"
                    'heat_management' => 1.0, // "Does it get hot when charging?"
                    'button_feel' => 0.9, // "Are buttons satisfying to press?"
                    'color_options' => 0.8, // "Does it come in nice colors?"
                    'slim_design' => 0.8, // "Looks sleek in my pocket"
                ],
            ],
        ],
        '2026' => [
            'weights' => [
                'performance' => 20,
                'display' => 19,
                'camera' => 19,
                'battery' => 16,
                'software' => 10,
                'build' => 8,
                'features' => 8,
            ],
            'spec_weights' => [
                'display' => [
                    'pwm_flicker' => 1.4, // ↑ Could be even higher - class action lawsuits may emerge
                    'display_technology' => 1.3, // ↑ Tandem OLED vs Micro-LED will be the premium divide
                    'adaptive_intelligence' => 1.2, // NEW: AI that adjusts everything (refresh, color, brightness) based on content/context
                    'refresh_rate' => 1.1, // ↓ Slightly - LTPO 4.0 makes this table stakes
                    'power_efficiency' => 1.1, // NEW: nits-per-watt becomes key metric
                    'color_accuracy' => 1.1, // ✅
                    'ultra_dark_mode' => 1.1, // ✅
                    'brightness_peak' => 1.0, // ↓ To 1.0 - truly diminishing returns
                    'hdr_support' => 1.0, // ✅
                    'touch_sampling' => 1.0, // ✅
                    'size' => 0.9, // ↓ As foldables vary sizes dynamically
                    'pixel_density' => 0.8, // ↓ 600+ PPI offers zero visible improvement
                    'always_on_display_ai' => 0.8, // NEW: Contextual AOD with LLM-summarized info
                ],
                'performance' => [
                    'npu_ai_performance' => 1.4, // ✅ Perfect leader
                    'ai_efficiency_score' => 1.3, // NEW: TOPS/W matters for battery
                    'chipset' => 1.2, // ↓ Slightly - NPU might dominate chip discourse
                    'thermal_management' => 1.3, // ↑ This could be #2 constraint
                    'gpu' => 1.1, // ↓ Slightly - AI will overshadow gaming
                    'storage_type' => 1.2, // ✅
                    'ram_bandwidth' => 1.1, // NEW: More than just type/capacity
                    'ram_type' => 1.0, // ↓ Standardized by 2026
                    'antutu_score' => 0.9, // ↓ Further
                    'ram_capacity' => 1.0, // ✅
                    'cpu' => 0.9, // ✅
                    'cooling_system' => 0.9, // ✅
                    'geekbench_multi_score' => 0.7, // ↓ Further
                    'storage_capacity' => 0.8, // ✅
                ],
                'camera' => [
                    'ai_creative_assistant' => 1.6, // ↑ Beyond processing to "co-creator"
                    'video_workflow_specs' => 1.4, // ↑ Pro video could be the #2 differentiator
                    'low_light_performance' => 1.2, // ↓ AI solves this nearly perfectly
                    'optical_zoom' => 1.2, // ✅
                    'stabilization' => 1.2, // ✅
                    'computational_bokeh' => 1.2, // NEW: AI-generated lens blur (different focal lengths)
                    'ar_camera_features' => 1.1, // NEW: Real-time 3D scanning, AR composition
                    'main_sensor_size' => 1.0, // ↓ Further to 1.0
                    'front_camera_ai' => 1.0, // NEW: More than MP - AR expression tracking
                    'camera_ui_ai' => 1.0, // NEW: Predictive shooting suggestions
                    'telephoto_quality' => 0.9, // CHANGED: AI zoom reduces need
                    'main_camera_mp' => 0.7, // ↓ Further - irrelevant
                    'ultrawide_quality' => 0.7, // ↓
                    'thermal_management' => 0.8, // NEW: 8K 60fps requires cooling
                    'macro_camera' => 0.3, // ↓ Effectively dead
                    'lens_coatings' => 0.7, // NEW: Anti-reflective, hydrophobic
                ],
                'battery' => [
                    'ai_lifetime_optimization' => 1.6, // 🥇 NEW LEADER: AI that extends usable life to 7+ years
                    'battery_health_guarantee' => 1.5, // ⚡️ Guaranteed 90% capacity after 5 years
                    'solid_state_performance' => 1.4, // 🆕 Solid-state batteries become mainstream
                    'thermal_self_regulation' => 1.3, // ❄️ Batteries that actively cool/heater themselves
                    'wireless_charging' => 1.2, // ⬆ Becomes PRIMARY method (desk, car, furniture)
                    'fast_charging_efficiency' => 1.1, // ⚡️ Watts matter less than heat generation
                    'capacity_mah' => 1.0, // ⬇ Truly irrelevant with new energy densities
                    'sustainable_manufacturing' => 0.9, // 🌍 Carbon-negative battery production
                    'modular_replacement' => 0.8, // 🔧 User-replaceable returns via regulation
                    'ambient_charging' => 0.7, // 🔋 Solar, RF, or kinetic trickle charging
                    'grid_integration' => 0.6, // 🏠 Phones as home battery backups
                ],
                'build' => [
                    'scratch_resistance' => 1.4, // 🥇 "Does it need a screen protector?"
                    'drop_survival' => 1.4, // 🥇 "Will it survive if I drop it?"
                    'water_resistance' => 1.3, // "Can I use it in the rain/bathroom?"
                    'premium_feel' => 1.2, // "Does it feel cheap or expensive?"
                    'weight_comfort' => 1.1, // "Is it too heavy in my pocket?"
                    'repair_cost' => 1.1, // ⬆ "How much to fix the screen?"
                    'color_durability' => 1.0, // "Will the color rub off?"
                    'button_quality' => 1.0, // "Are the buttons clicky or mushy?"
                    'heat_in_hand' => 0.9, // "Does it get hot when charging/gaming?"
                    'case_compatibility' => 0.8, // "Will it work with my favorite case?"
                ],






            ],
        ],
        // ... 2025, 2026
    ],

    // 'balanced' => [
    //     'build' => [
    //         'weight' => 0.9,
    //         'specs' => [
    //             'weight_g' => 1.1,          // Lighter better
    //             'thickness_mm' => 1.0,
    //             'ip_rating' => 1.3,         // IP68+ standard
    //             'build_material' => 1.2,
    //             'durability_cert' => 1.0,   // Gorilla Glass Victus+
    //         ],
    //     ],
    //     'features' => [
    //         'weight' => 0.9,
    //         'specs' => [
    //             'stereo_speakers' => 1.1,
    //             'audio_quality' => 1.0,
    //             'haptics' => 1.1,           // Added
    //             'wifi_version' => 1.0,      // WiFi 7
    //             'bluetooth_version' => 0.8,
    //             'nfc' => 0.9,
    //             'fingerprint_sensor' => 1.0,
    //             'face_unlock' => 0.9,
    //             'uWB_support' => 0.7,       // Ultra-wideband
    //             'ir_blaster' => 0.4,
    //         ],
    //     ],
    //     'software' => [
    //         'weight' => 1.1,
    //         'specs' => [
    //             'os_version' => 1.2,
    //             'update_policy_years' => 1.5,
    //             'security_updates' => 1.3,
    //             'ui_optimization' => 1.1,
    //             'bloatware' => 1.2,         // Lower is better
    //             'ai_features' => 1.0,
    //         ],
    //     ],
    //     'value' => [
    //         'weight' => 1.4,                // Most important for balanced profile
    //         'specs' => [
    //             'launch_price' => 1.2,
    //             'current_price' => 1.5,
    //             'price_to_score_ratio' => 2.0,
    //             'resale_value' => 1.3,
    //         ],
    //     ],
    //     'connectivity' => [                 // Added missing category
    //         'weight' => 0.8,
    //         'specs' => [
    //             '5g_bands' => 1.2,
    //             '6g_ready' => 0.9,
    //             'modem_quality' => 1.1,
    //         ],
    //     ],
    // ],

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

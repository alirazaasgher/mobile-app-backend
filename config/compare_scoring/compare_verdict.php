<?php
return [

    // 'value' => [
    //     'label' => 'Value for Money',
    //     'icon' => '💰',
    //     'priority' => 'critical',
    //     'weight' => 1.6,
    //     'keywords' => ['price', 'value', 'cost', 'affordable', 'budget', 'pricing'],
    //     'thresholds' => [
    //         'decisive' => 25,    // 25%+ price difference = decisive
    //         'notable' => 15,     // 15-24% = significant
    //         'marginal' => 8,     // 8-14% = slight advantage
    //         // < 8% = too close
    //     ],
    //     'verdicts' => [
    //         'winner' => '{phone} offers much better value for money',
    //         'close' => 'Both phones are similarly priced for what they offer',
    //         'advantages' => [
    //             'price' => '{value}% cheaper while offering similar features',
    //             'launch_price' => 'launched at {value} lower price',
    //             'current_price' => 'currently {value} less expensive',
    //             'price_performance' => 'better performance per dollar',
    //             'discounts' => 'more frequent and deeper discounts',
    //             'resale_value' => 'better {value}% resale value after 1 year',
    //             'bundled_items' => 'includes {value} in the box',
    //         ],
    //     ],
    // ],

    'performance' => [
        'label' => 'Performance',
        'icon' => '⚡',
        'priority' => 'high',
        'weight' => 1.4,
        'keywords' => ['performance', 'speed', 'gaming', 'multitasking', 'processor', 'benchmark'],
        'thresholds' => [
            'decisive' => 22,    // 20%+ benchmark difference
            'notable' => 12,     // 10-19% difference
            'marginal' => 6,     // 5-9% difference
        ],
        'verdicts' => [
            'winner' => '{phone} delivers significantly better performance',
            'close' => 'Both phones offer excellent performance for daily tasks',
            'advantages' => [
                'chipset' => 'more powerful {value} chipset',
                'gpu' => '{value}% better GPU performance for gaming',
                'cpu' => '{value}% faster CPU in benchmarks',
                'ram_capacity' => '{value}GB more RAM for better multitasking',
                'ram_type' => 'faster {value} RAM',
                'storage_speed' => '{value}% faster read/write speeds',
                'sustained_performance' => 'better thermal management for sustained performance',
                'ai_performance' => '{value}TOPS NPU for AI tasks',
                'app_launch_speed' => 'noticeably faster app loading',
                'gaming_performance' => 'higher and more stable frame rates in games',
                'npu_tops' => 'superior AI processing with {value} TOPS',
                'ray_tracing' => 'hardware-accelerated ray tracing for console-quality gaming',
                'efficiency_node' => 'more efficient {value}nm process for better battery during load',
                'memory_bandwidth' => 'higher memory bandwidth for smoother AI agent response',
                'thermal_headroom' => 'superior cooling allows for longer peak gaming sessions',
            ],
        ],
    ],

    'camera' => [
        'label' => 'Camera',
        'icon' => '📷',
        'priority' => 'high',
        'weight' => 1.4,
        'keywords' => ['camera', 'photos', 'video', 'photography', 'low-light', 'portrait'],
        'thresholds' => [
            'decisive' => 16,    // Clearly better camera system
            'notable' => 9,      // Noticeably better
            'marginal' => 4,     // Slight advantage
        ],
        'verdicts' => [
            'winner' => '{phone} captures significantly better photos and videos',
            'close' => 'Both phones take excellent photos in most situations',
            'advantages' => [
                'main_sensor' => 'larger {value}" main sensor',
                'low_light' => 'significantly better low-light performance',
                'portrait_mode' => 'more natural portrait photos with better edge detection',
                'video_quality' => 'better video stabilization and dynamic range',
                'zoom_quality' => 'sharper {value}x zoom photos',
                'ultrawide' => 'wider {value}° ultrawide lens with less distortion',
                'front_camera' => 'better selfies with {value}MP front camera',
                'pro_features' => 'more professional camera features (ProRAW, LOG video)',
                'software_processing' => 'superior computational photography',
                'night_mode' => 'faster and brighter night mode photos',
            ],
        ],
    ],

    'battery' => [
        'label' => 'Battery & Charging',
        'icon' => '🔋',
        'priority' => 'high',
        'weight' => 1.3,
        'keywords' => ['battery', 'charging', 'endurance', 'screen-on-time', 'fast-charging'],
        'thresholds' => [
            'decisive' => 18,    // 2+ hours more SOT or 50% faster charging
            'notable' => 10,     // 1-2 hours SOT or 30% faster charging
            'marginal' => 5,     // 30-60 mins SOT or 15% faster charging
        ],
        'verdicts' => [
            'winner' => '{phone} offers superior battery life and charging',
            'close' => 'Both phones provide all-day battery life',
            'advantages' => [
                'screen_on_time' => '{value} hours more screen-on time',
                'charging_speed' => '{value}% faster charging (0-100%)',
                'wireless_charging' => '{value}W faster wireless charging',
                'battery_health' => 'better battery longevity and health management',
                'power_efficiency' => 'more power-efficient display/processor',
                'reverse_charging' => 'faster {value}W reverse wireless charging',
                'standby_time' => 'better standby battery life',
                'charging_flexibility' => 'more charging options (PD, PPS, etc.)',
                'battery_capacity' => '{value}mAh larger battery',
            ],
        ],
    ],

    'display' => [
        'label' => 'Display',
        'icon' => '📱',
        'priority' => 'medium',
        'weight' => 1.2,
        'keywords' => ['display', 'screen', 'brightness', 'refresh-rate', 'colors'],
        'thresholds' => [
            'decisive' => 12,    // Clear visible superiority
            'notable' => 7,      // Noticeably better
            'marginal' => 3,     // Slightly better
        ],
        'verdicts' => [
            'winner' => '{phone} has a superior display',
            'close' => 'Both phones have excellent displays',
            'advantages' => [
                'peak_brightness' => '{value}nits brighter for outdoor visibility',
                'refresh_rate' => 'smoother {value}Hz adaptive refresh rate',
                'color_accuracy' => 'better color accuracy ({value} ΔE)',
                'hdr_support' => 'superior HDR support (Dolby Vision, HDR10+)',
                'pwm_frequency' => 'higher {value}Hz PWM for less eye strain',
                'touch_sampling' => '{value}Hz touch sampling for gaming',
                'screen_protection' => 'stronger {value} glass protection',
                'bezels' => 'smaller bezels with {value}% screen-to-body ratio',
                'always_on_display' => 'more feature-rich Always On Display',
                'curved_edges' => 'better edge handling with minimal distortion',
            ],
        ],
    ],

    'software' => [
        'label' => 'Software & Updates',
        'icon' => '🔄',
        'priority' => 'medium',
        'weight' => 1.1,
        'keywords' => ['software', 'updates', 'android', 'ios', 'support', 'upgrades'],
        'thresholds' => [
            'decisive' => 15,    // 2+ more years of updates
            'notable' => 8,      // 1 more year of updates
            'marginal' => 4,     // Faster update delivery
        ],
        'verdicts' => [
            'winner' => '{phone} has superior software support',
            'close' => 'Both phones offer good software experiences',
            'advantages' => [
                'update_promise' => '{value} more years of OS updates',
                'security_updates' => 'longer security update support',
                'update_speed' => 'faster and more consistent updates',
                'software_features' => 'more exclusive software features',
                'bloatware' => 'cleaner software with less bloatware',
                'customization' => 'more customization options',
                'ecosystem' => 'better ecosystem integration ({value})',
                'beta_access' => 'early access to beta software',
                'debloatability' => 'easier to remove unwanted apps',
            ],
        ],
    ],

    'build' => [
        'label' => 'Build & Design',
        'icon' => '🏗️',
        'priority' => 'medium',
        'weight' => 1.0,
        'keywords' => ['build', 'design', 'durability', 'materials', 'premium', 'ergonomics'],
        'thresholds' => [
            'decisive' => 14,    // Clearly superior build
            'notable' => 7,      // Noticeably better
            'marginal' => 3,     // Slight advantage
        ],
        'verdicts' => [
            'winner' => '{phone} has superior build quality and design',
            'close' => 'Both phones are well-built and premium',
            'advantages' => [
                'ip_rating' => 'better {value} water and dust resistance',
                'weight' => '{value}g lighter for better handling',
                'thickness' => '{value}mm thinner profile',
                'materials' => 'premium {value} construction',
                'durability' => 'better drop and scratch resistance',
                'ergonomics' => 'more comfortable to hold and use',
                'color_options' => 'more attractive color options',
                'button_feel' => 'better tactile feedback from buttons',
                'fingerprint_sensor' => 'faster and more reliable {value} fingerprint sensor',
                'haptics' => 'superior haptic feedback system',
            ],
        ],
    ],

    'features' => [
        'label' => 'Features & Connectivity',
        'icon' => '✨',
        'priority' => 'low',
        'weight' => 0.9,
        'keywords' => ['features', 'connectivity', 'extras', 'speakers', 'biometrics'],
        'thresholds' => [
            'decisive' => 12,    // Several important extra features
            'notable' => 6,      // 2-3 useful extras
            'marginal' => 2,     // 1-2 minor extras
        ],
        'verdicts' => [
            'winner' => '{phone} offers more useful features',
            'close' => 'Both phones have comprehensive feature sets',
            'advantages' => [
                'speaker_quality' => 'better stereo speakers with {value} audio',
                'bluetooth' => 'newer Bluetooth {value} with LE Audio',
                'wifi' => 'faster {value} Wi-Fi connectivity',
                '5g_bands' => 'more 5G bands for better coverage',
                'satellite' => 'emergency satellite connectivity',
                'headphone_jack' => 'includes 3.5mm headphone jack',
                'sd_card' => 'microSD card expansion support',
                'ir_blaster' => 'infrared blaster for remote control',
                'usb_port' => 'faster {value} USB port',
                'esim_support' => 'dual eSIM support',
                'nfc' => 'more advanced NFC features',
            ],
        ],
    ],

    // Overall verdict configuration
    'overall' => [
        'templates' => [
            'clear_winner' => '🏆 **{phone} is the clear winner** - it excels in {count} out of {total} categories and offers the best overall package',
            'strong_advantage' => '⭐ **{phone} has a strong advantage** - it wins in {categories} and represents better value',
            'slight_edge' => '📈 **{phone} has a slight edge** - while close, it wins {count} categories and offers minor advantages in daily use',
            'balanced_trade_off' => '⚖️ **It depends on your priorities** - {phone1} excels at {categories1}, while {phone2} is better for {categories2}',
            'too_close' => '🤝 **Both are excellent choices** - they trade blows in different areas with comparable overall performance',
            'profile_recommendation' => '🎯 **For {profile} users**: {phone} is the better choice because {reason}',
            'value_winner' => '💸 **Best value**: Despite not winning all categories, {phone} offers the best balance of price and features',
        ],
        'thresholds' => [
            'clear_winner' => 0.75,      // Wins 75%+ categories with significant margins
            'strong_advantage' => 0.65,  // Wins 65%+ categories
            'slight_edge' => 0.55,       // Wins 55%+ categories
            'balanced' => 0.45,          // 45-55% = trade-offs
            'too_close' => 0.40,         // < 40% = too close to call
        ],
        'category_importance_order' => ['value', 'performance', 'camera', 'battery', 'display', 'software', 'build', 'features'],
    ],

    // Profile-specific recommendations (updated)
    'profile_priorities' => [
        'balanced' => [
            'categories' => ['value', 'performance', 'camera', 'battery'],
            'message' => 'Looking for a well-rounded phone that does everything well',
        ],
        'gamer' => [
            'categories' => ['performance', 'display', 'battery', 'features'],
            'message' => 'Prioritizing gaming performance, high refresh rates, and sustained performance',
        ],
        'photographer' => [
            'categories' => ['camera', 'display', 'storage', 'battery'],
            'message' => 'Camera quality, display accuracy, and photo storage are most important',
        ],
        'power_user' => [
            'categories' => ['battery', 'performance', 'software', 'features'],
            'message' => 'All-day battery, top performance, and productivity features matter most',
        ],
        'budget_shopper' => [
            'categories' => ['value', 'battery', 'performance', 'software'],
            'message' => 'Best performance and features for the lowest price',
        ],
        'media_consumer' => [
            'categories' => ['display', 'battery', 'features', 'build'],
            'message' => 'Display quality, speakers, and multimedia features are key',
        ],
        'business_user' => [
            'categories' => ['software', 'battery', 'build', 'features'],
            'message' => 'Software support, security, durability, and professional features',
        ],
        'student' => [
            'categories' => ['value', 'battery', 'performance', 'software'],
            'message' => 'Affordable price, all-day battery, and good performance for studies',
        ],
    ],

    // Tie-breaker rules (updated)
    'tie_breakers' => [
        'value_score',           // First: Value for money comparison
        'critical_category_wins', // Second: Wins in value/performance/camera
        'total_weighted_score',   // Third: Overall weighted score
        'user_profile_match',     // Fourth: Match with user profile
        'price',                  // Fifth: Lower price wins
        'release_date',           // Sixth: Newer device
    ],

    // Score magnitude descriptors
    'score_modifiers' => [
        'game_changer' => 25,    // 25+ point lead = "revolutionary" or "game-changing"
        'dominant' => 18,        // 18-24 point lead = "dominates"
        'significant' => 12,     // 12-17 point lead = "significantly better"
        'noticeable' => 8,       // 8-11 point lead = "noticeably better"
        'slight' => 4,           // 4-7 point lead = "slightly better"
        'marginal' => 1,         // 1-3 point lead = "marginally better"
        // 0 points = "identical" or "too close to call"
    ],

    // Category scoring rules
    'scoring_rules' => [
        'diminishing_returns' => true,  // Apply diminishing returns for certain metrics
        'minimum_thresholds' => [       // Minimum acceptable values
            'battery' => ['screen_on_time' => 6],  // Hours
            'display' => ['peak_brightness' => 500], // nits
            'performance' => ['benchmark_score' => 500000], // AnTuTu
        ],
        'critical_factors' => [         // Factors that can veto a win
            'battery' => ['screen_on_time' => 4], // Less than 4 hours SOT is unacceptable
            'software' => ['security_updates' => 2], // Less than 2 years support
        ],
    ],

    // Language variations
    'language' => [
        'intensity_modifiers' => [
            'game_changer' => ['revolutionary', 'game-changing', 'next-level', 'unmatched'],
            'dominant' => ['dominates', 'crushes', 'outclasses', 'leaves in the dust'],
            'significant' => ['significantly better', 'clearly superior', 'substantially better'],
            'noticeable' => ['noticeably better', 'perceptibly better', 'distinctly better'],
            'slight' => ['slightly better', 'a bit better', 'somewhat better'],
            'marginal' => ['marginally better', 'barely better', 'just edges out'],
        ],
        'comparison_phrases' => [
            'vs' => ['versus', 'compared to', 'against', 'facing off with'],
            'winner' => ['takes the crown', 'comes out on top', 'wins this battle', 'emerges victorious'],
            'close' => ['neck and neck', 'too close to call', 'splitting hairs', 'a photo finish'],
        ],
    ],

    // Recommendation confidence
    'confidence' => [
        'strong' => [
            'threshold' => 20,
            'phrase' => 'We strongly recommend',
            'icon' => '🎯',
        ],
        'moderate' => [
            'threshold' => 12,
            'phrase' => 'We recommend',
            'icon' => '👍',
        ],
        'weak' => [
            'threshold' => 6,
            'phrase' => 'We lean toward',
            'icon' => '🤔',
        ],
        'neutral' => [
            'threshold' => 3,
            'phrase' => 'Consider',
            'icon' => '⚖️',
        ],
        'too_close' => [
            'threshold' => 0,
            'phrase' => 'Both are good, but',
            'icon' => '🤝',
        ],
    ],

    // Special scenarios
    'special_scenarios' => [
        'price_gap' => [
            'significant' => 30,  // 30%+ price difference
            'moderate' => 20,     // 20-29% difference
            'small' => 10,        // 10-19% difference
        ],
        'age_gap' => [
            'generation' => 365,  // 1 year = different generation
            'half_gen' => 180,    // 6 months = refresh model
            'same_gen' => 90,     // 3 months = same generation
        ],
        'ecosystem_lock' => [     // Bonus points for same ecosystem
            'apple' => 5,         // iPhone to iPhone comparison
            'samsung' => 3,       // Galaxy to Galaxy
            'google' => 3,        // Pixel to Pixel
            'xiaomi' => 2,        // Xiaomi to Xiaomi
        ],
    ],

    // Real-world testing considerations
    'real_world_factors' => [
        'camera' => [
            'consistency' => true,  // Score consistency across shots
            'auto_mode' => true,    // Point-and-shoot quality
            'pro_mode' => false,    // Manual controls (weighted less)
        ],
        'battery' => [
            'adaptive_battery' => true,  // Learning patterns
            '5g_impact' => true,         // Battery drain on 5G
            'standby_drain' => true,     // Overnight battery loss
        ],
        'performance' => [
            'real_world_speed' => true,  // App launch, multitasking
            'thermal_throttling' => true, // Sustained performance
            'ram_management' => true,    // App retention
        ],
    ],
];

<?php
return [
    'label' => 'Software & Updates',
    'specs' => [
        'os' => [
            'label' => 'Latest OS Support',
            'weight' => 25,
            'scale' => [
                'iOS 26' => 10,       // Apple's current year-based naming (Sep 2025)
                'Android 16' => 10,   // Google's current major release (Jun 2025)
                'iOS 18' => 7,        // Legacy version (2024)
                'Android 15' => 7,    // Legacy version (2024)
            ],
        ],
        'update_policy' => [
            'label' => 'Long-term Support Policy',
            'weight' => 25,
            'ranges' => [
                ['min_years' => 7, 'score' => 10], // Flagship standard: Galaxy S25/S26, Pixel 9/10
                ['min_years' => 6, 'score' => 9],  // Current Apple support lifecycle
                ['min_years' => 5, 'score' => 8],  // Standard for mid-range premium devices
                ['min_years' => 3, 'score' => 5],  // Budget-tier standard
            ],
        ],
        'ai_capabilities' => [
            'label' => 'AI Intelligence Level',
            'weight' => 20,
            'ranges' => [
                ['value' => 'Autonomous On-Device AI Agent', 'score' => 10], // High-level agentic AI (Apple Intelligence+)
                ['value' => 'Multimodal Generative AI', 'score' => 9],       // 2025 hardware-level text/image/video gen
                ['value' => 'Cloud-based AI Assistants', 'score' => 6],     // Standard LLM-backed assistants
            ],
        ],
        'ux_cleanliness' => [
            'label' => 'UX & Bloatware',
            'weight' => 15,
            'ranges' => [
                ['value' => 'Clean / Ad-free', 'score' => 10], // iOS, Pixel UI, Nothing OS
                ['value' => 'Minor Bloatware', 'score' => 7],  // Samsung One UI, refined Chinese skins
                ['value' => 'Heavy Bloat / Ads', 'score' => 4], // Ad-supported budget models
            ],
        ],
        'ecosystem_sync' => [
            'label' => 'Ecosystem Integration',
            'weight' => 15,
            'ranges' => [
                ['value' => 'Full Continuity (Watch/Tab/PC)', 'score' => 10], // Apple Continuity, Samsung DeX/Share
                ['value' => 'Standard Sync', 'score' => 7],
                ['value' => 'Standalone only', 'score' => 5],
            ],
        ],
    ],
];

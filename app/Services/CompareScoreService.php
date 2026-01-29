<?php

namespace App\Services;

class CompareScoreService
{
    protected array $config;

    /* -------------------------------
       PUBLIC API
    --------------------------------*/

    public function scoreCategory(
        string $category,
        array $values,
        string $profile = 'balanced'
    ): array {
        $profileConfig = config("compare_scoring.compare_profiles.$profile", []);
        $specConfigs = $profileConfig[$category]['specs'] ?? [];
        $scoredSpecs = [];
        $categoryScore = 0;
        foreach ($specConfigs as $specKey => $specConfig) {

            // Check if the value exists
            if (!array_key_exists($specKey, $values)) {
                continue;
            }

            $value = $values[$specKey];
            $score = $this->scoreSpec($value, $specConfig);
            // Skip if scoring failed
            if ($score === null) {
                continue;
            }

            $specWeight = $specConfig['weight'];
            $contribution = ($score / 10) * $specWeight;
            $categoryScore += $contribution;

            // Store scored spec
            $scoredSpecs[$specKey] = [
                'value' => $this->formatValueWithUnit($value, $specConfig),
                'score' => $score,
                'out_of' => 10,
                'base_weight' => $specWeight,
                'contribution' => round($contribution, 2),
            ];
        }

        $adjustments = $this->applyContextualAdjustments($category, $values, $scoredSpecs, $profile);
        $categoryScore += $adjustments['total_adjustment'];

        // Handle meta specs
        foreach (array_diff_key($values, $specConfigs) as $metaKey => $metaValue) {
            $scoredSpecs[$metaKey] = [
                'value' => is_bool($metaValue) ? ($metaValue ? 'Yes' : 'No') : $metaValue,
                'score' => null,
                'out_of' => null,
                'weight' => null,
            ];
        }

        return [
            'score' => round($categoryScore, 2),
            'out_of' => 100,
            'specs' => $scoredSpecs,
            'adjustments' => $adjustments['details'],
        ];
    }

    protected function applyContextualAdjustments(string $category, array $values, array $scoredSpecs, string $profile): array
    {
        $adjustments = [];
        $totalAdjustment = 0;

        if ($category === 'display') {
            // Extract and sanitize all values
            $brightness_sdr = (int)($values['brightness_typical'] ?? 0);
            $brightness_hdr = (int)($values['brightness_peak'] ?? 0);
            $sustainedBrightness = (int)($values['sustained_brightness'] ?? 0);
            $hasHDR = ($values['hdr_support'] ?? 'no') !== 'no';
            $refreshRate = (int)($values['refresh_rate'] ?? 0);
            $touchSampling = (int)($values['touch_sampling_rate'] ?? 0);
            $screenSize = (float)($values['size'] ?? 0);
            $ppi = (int)($values['pixel_density'] ?? 0);
            $hasAdaptive = ($values['adaptive_refresh_rate'] ?? 'no') !== 'no';
            $colorBits = (int)($values['color_depth_bits'] ?? 8);
            $pwmFrequency = (int)($values['pwm_dimming_frequency'] ?? 0);
            $panelType = strtolower($values['panel_type'] ?? '');
            $contrastRatio = $values['contrast_ratio'] ?? '';

            // ==================================================================
            // 1. SDR/HDR Efficiency Analysis
            // ==================================================================
            if ($brightness_hdr > 0 && $brightness_sdr > 0) {
                $sdr_efficiency_ratio = $brightness_sdr / $brightness_hdr;

                // Bonus for displays that maintain high SDR brightness
                if ($sdr_efficiency_ratio > 0.6) {
                    $bonus = 1.0;
                    $totalAdjustment += $bonus;
                    $adjustments[] = [
                        'type' => 'bonus',
                        'reason' => "Excellent SDR efficiency ({$brightness_sdr}nits SDR, {$brightness_hdr}nits HDR)",
                        'value' => $bonus,
                    ];
                }

                // Penalty for displays with huge HDR numbers but poor SDR
                if ($brightness_hdr > 2000 && $brightness_sdr < 800 && $sdr_efficiency_ratio < 0.4) {
                    $penalty = -1.5;
                    $totalAdjustment += $penalty;
                    $adjustments[] = [
                        'type' => 'penalty',
                        'reason' => "High HDR brightness ({$brightness_hdr}nits) but poor SDR ({$brightness_sdr}nits) - limited daily usability",
                        'value' => $penalty,
                    ];
                }
            }

            // ==================================================================
            // 2. Exceptional Brightness Bonus
            // ==================================================================
            if ($hasHDR && $brightness_hdr > 1800 && $brightness_sdr > 1000) {
                $bonus = 2.5;
                $totalAdjustment += $bonus;
                $adjustments[] = [
                    'type' => 'bonus',
                    'reason' => "Exceptional brightness (SDR: {$brightness_sdr}nits, HDR: {$brightness_hdr}nits)",
                    'value' => $bonus,
                ];
            }

            // ==================================================================
            // 3. Sustained Brightness Check (Thermal Management)
            // ==================================================================
            if ($brightness_hdr > 1500 && $sustainedBrightness > 0 && $sustainedBrightness < 800) {
                $penalty = -1.5;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "Poor sustained brightness ({$sustainedBrightness}nits) vs peak ({$brightness_hdr}nits) - thermal throttling issues",
                    'value' => $penalty,
                ];
            }

            // ==================================================================
            // 4. PWM Dimming / Eye Strain (CRITICAL FOR HEALTH)
            // ==================================================================
            if ($pwmFrequency > 0 && $pwmFrequency < 1000) {
                $penalty = -2.0;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "Low PWM frequency ({$pwmFrequency}Hz) may cause eye strain for sensitive users",
                    'value' => $penalty,
                ];
            } elseif ($pwmFrequency >= 3840 || ($values['dc_dimming'] ?? false)) {
                $bonus = 1.5;
                $totalAdjustment += $bonus;
                $adjustments[] = [
                    'type' => 'bonus',
                    'reason' => $pwmFrequency >= 3840
                        ? "High PWM frequency ({$pwmFrequency}Hz) - flicker-free display"
                        : "DC dimming enabled - flicker-free display",
                    'value' => $bonus,
                ];
            }

            // ==================================================================
            // 5. Refresh Rate vs Touch Sampling Validation
            // ==================================================================
            if ($refreshRate >= 120 && $touchSampling < 240) {
                $penalty = -2.0;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "High refresh rate ({$refreshRate}Hz) without proportional touch sampling ({$touchSampling}Hz)",
                    'value' => $penalty,
                ];
            }

            // ==================================================================
            // 6. Complete Gaming Display Package
            // ==================================================================
            if ($refreshRate >= 120 && $touchSampling >= 360 && $hasAdaptive) {
                $bonus = 2.0;
                $totalAdjustment += $bonus;
                $adjustments[] = [
                    'type' => 'bonus',
                    'reason' => "Excellent gaming display ({$refreshRate}Hz refresh, {$touchSampling}Hz touch, adaptive refresh)",
                    'value' => $bonus,
                ];
            }

            // ==================================================================
            // 7. PPI vs Screen Size (REFINED WITH SOFT/HARD PENALTIES)
            // ==================================================================
            if ($screenSize > 6.7) {
                if ($ppi < 360) {
                    // Hard penalty for unacceptable sharpness
                    $penalty = -3.0;
                    $totalAdjustment += $penalty;
                    $adjustments[] = [
                        'type' => 'penalty',
                        'reason' => "Large screen ({$screenSize}\") with unacceptable pixel density ({$ppi} PPI)",
                        'value' => $penalty,
                    ];
                } elseif ($ppi >= 360 && $ppi < 400) {
                    // Soft penalty for mediocre sharpness
                    $penalty = -1.5;
                    $totalAdjustment += $penalty;
                    $adjustments[] = [
                        'type' => 'penalty',
                        'reason' => "Large screen ({$screenSize}\") with below-average pixel density ({$ppi} PPI)",
                        'value' => $penalty,
                    ];
                }
            }

            // ==================================================================
            // 8. HDR Support without 10-bit Color Depth
            // ==================================================================
            if ($hasHDR && $colorBits < 10) {
                $penalty = -2.0;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "HDR support claimed but lacks 10-bit color depth (only {$colorBits}-bit)",
                    'value' => $penalty,
                ];
            }

            // ==================================================================
            // 9. Contrast Ratio Analysis (SAFE STRING HANDLING)
            // ==================================================================
            // Clean contrast ratio string (handles "5,000,000:1", "Infinite", etc.)
            $contrastRatioLower = strtolower(trim($contrastRatio));
            $cleanContrast = (int) filter_var($contrastRatio, FILTER_SANITIZE_NUMBER_INT);

            $isOLED = str_contains($panelType, 'oled') || str_contains($panelType, 'amoled');
            $isLCD = str_contains($panelType, 'lcd') || str_contains($panelType, 'ips');

            // OLED Infinite Contrast Bonus
            if ($isOLED && ($contrastRatioLower === 'infinite' || $cleanContrast > 1000000)) {
                $bonus = 1.5;
                $totalAdjustment += $bonus;
                $adjustments[] = [
                    'type' => 'bonus',
                    'reason' => 'True infinite contrast ratio (OLED technology)',
                    'value' => $bonus,
                ];
            }

            // LCD Poor Contrast Penalty
            if ($isLCD && $cleanContrast > 0 && $cleanContrast < 1500) {
                $penalty = -1.5;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "Below-average contrast ratio for LCD panel ({$cleanContrast}:1)",
                    'value' => $penalty,
                ];
            }

            // ==================================================================
            // 10. Adaptive Refresh Rate Quality (LTPO vs LTPS)
            // ==================================================================
            if ($hasAdaptive) {
                $isLTPO = str_contains($panelType, 'ltpo');
                $minRefreshRate = (int)($values['min_refresh_rate'] ?? $refreshRate);

                // Premium LTPO with 1Hz capability
                if ($isLTPO && $minRefreshRate <= 1 && $isOLED) {
                    $bonus = 2.0;
                    $totalAdjustment += $bonus;
                    $adjustments[] = [
                        'type' => 'bonus',
                        'reason' => "Premium LTPO technology with 1Hz-{$refreshRate}Hz adaptive refresh - exceptional battery efficiency",
                        'value' => $bonus,
                    ];
                }
                // Good adaptive refresh (down to 10-24Hz)
                elseif ($minRefreshRate > 1 && $minRefreshRate <= 24) {
                    $bonus = 1.0;
                    $totalAdjustment += $bonus;
                    $adjustments[] = [
                        'type' => 'bonus',
                        'reason' => "Good adaptive refresh range ({$minRefreshRate}Hz-{$refreshRate}Hz)",
                        'value' => $bonus,
                    ];
                }
                // Fake "adaptive" that only goes to 60Hz
                elseif ($minRefreshRate >= 60 && $refreshRate >= 120) {
                    $penalty = -1.0;
                    $totalAdjustment += $penalty;
                    $adjustments[] = [
                        'type' => 'penalty',
                        'reason' => "Limited adaptive refresh range ({$minRefreshRate}Hz-{$refreshRate}Hz) - marketing gimmick",
                        'value' => $penalty,
                    ];
                }
            }

            // ==================================================================
            // 11. Resolution Efficiency Check
            // ==================================================================
            $resolution = strtolower($values['resolution'] ?? '');
            $isQHD = str_contains($resolution, '1440') || str_contains($resolution, 'qhd');

            if ($isQHD && $screenSize < 6.3 && $ppi > 550) {
                $penalty = -1.0;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "Unnecessarily high resolution ({$ppi} PPI) for {$screenSize}\" screen - impacts battery with minimal visual benefit",
                    'value' => $penalty,
                ];
            }

            // ==================================================================
            // 12. Outdoor Usability (Brightness + Reflectance)
            // ==================================================================
            if ($brightness_hdr > 2000 && $brightness_sdr > 1000) {
                $reflectance = (float)($values['reflectance_ratio'] ?? 100);

                if ($reflectance > 0 && $reflectance < 4.5) {
                    $bonus = 1.5;
                    $totalAdjustment += $bonus;
                    $adjustments[] = [
                        'type' => 'bonus',
                        'reason' => "Exceptional outdoor visibility (high brightness + low reflectance: {$reflectance}%)",
                        'value' => $bonus,
                    ];
                }
            }

            // ==================================================================
            // 13. Glass Protection Reality Check
            // ==================================================================
            $glassProtection = strtolower($values['glass_protection'] ?? '');

            // Bonus for premium protection
            if (
                str_contains($glassProtection, 'victus 2') ||
                str_contains($glassProtection, 'victus 3') ||
                str_contains($glassProtection, 'ceramic shield')
            ) {
                $bonus = 1.0;
                $totalAdjustment += $bonus;
                $adjustments[] = [
                    'type' => 'bonus',
                    'reason' => 'Top-tier glass protection (latest generation)',
                    'value' => $bonus,
                ];
            }

            // Penalty for no protection on premium device
            $phonePrice = (int)($values['price'] ?? 0);
            if (empty($glassProtection) && $phonePrice > 600) {
                $penalty = -2.0;
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => 'No glass protection on premium device',
                    'value' => $penalty,
                ];
            }
        }

        return [
            'total_adjustment' => round($totalAdjustment, 1),
            'details' => $adjustments,
        ];
    }

    private function formatValueWithUnit($value, array $specConfig): string
    {
        if (isset($specConfig['unit']) && !is_null($value) && $value !== '') {
            $unit = $specConfig['unit']['value'] ?? '';
            $position = $specConfig['unit']['position'] ?? 'after';
            $space = $specConfig['unit']['space'] ?? true;
            $separator = $space ? ' ' : '';

            return $position === 'before'
                ? $unit . $separator . $value
                : $value . $separator . $unit;
        }

        return (string) $value; // Ensure value is returned as a string
    }
    /* -------------------------------
       SCORING LOGIC
    --------------------------------*/

    protected function scoreSpec($value, array $config): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $isString = is_string($value);
        $isNumeric = is_numeric($value);

        if ($isString) {
            $value = strtolower($value);
        }

        // Keyword scale matching
        if ($isString && isset($config['scale'])) {
            $normalizedValue = $this->normalizeString($value);

            foreach ($config['scale'] as $key => $score) {
                $normalizedKey = $this->normalizeString(strtolower($key));

                // Try exact match first
                if (str_contains($normalizedValue, $normalizedKey)) {
                    return $score;
                }

                // Try fuzzy match (remove parentheses content for comparison)
                if ($this->fuzzyMatch($normalizedValue, $normalizedKey)) {
                    return $score;
                }
            }
        }

        // Numeric range matching
        if ($isNumeric && isset($config['ranges'])) {
            $numericValue = (float) $value;

            foreach ($config['ranges'] as $range) {
                $hasMin = isset($range['min']);
                $hasMax = isset($range['max']);

                if (!$hasMin && !$hasMax) {
                    continue;
                }

                if (
                    (!$hasMin || $numericValue >= $range['min']) &&
                    (!$hasMax || $numericValue <= $range['max'])
                ) {
                    return $range['score'];
                }
            }
        }

        return $config['default'] ?? null;
    }

    /**
     * Fuzzy match that handles parentheses variations
     * Matches "apple gpu (6-core)" with "apple gpu (6-core graphics)"
     */
    protected function fuzzyMatch(string $value, string $key): bool
    {
        // If key has closing parenthesis at the end, try matching without it
        if (str_ends_with($key, ')')) {
            // Remove the closing parenthesis from key
            $keyWithoutClosing = rtrim($key, ')');

            // Check if value contains the key without closing paren
            if (str_contains($value, $keyWithoutClosing)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize string for consistent comparison
     */
    protected function normalizeString(string $value): string
    {
        // Replace various hyphen/dash types with standard hyphen
        $value = str_replace(['‑', '–', '—', '−', '_'], '-', $value);

        // Normalize whitespace
        $value = preg_replace('/\s+/', ' ', trim($value));

        return $value;
    }

    protected function calculateWeightedScore(array $specs): int
    {
        $totalWeight = 0;
        $weightedScore = 0;

        foreach ($specs as $spec) {
            $weightedScore += $spec['score'] * $spec['weight'];
            $totalWeight += $spec['weight'];
        }

        if ($totalWeight === 0) {
            return 0;
        }

        // Normalize → 100
        return (int) round(($weightedScore / ($totalWeight * 10)) * 100);
    }
}

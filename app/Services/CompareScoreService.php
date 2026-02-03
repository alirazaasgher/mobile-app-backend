<?php

namespace App\Services;

class CompareScoreService
{
    protected array $config;

    /* -------------------------------
       PUBLIC API
    --------------------------------*/
//$values = $this->applyInferences($values, $specConfigs);
    public function scoreCategory(
        string $category,
        array $values,
        string $profile = 'balanced'
    ): array {
        $profileConfig = config("compare_scoring.compare_profiles.$profile", []);
        $specConfigs = $profileConfig[$category]['specs'] ?? [];
 
        

        $scoredSpecs = [];
        $categoryScore = 0;
        foreach ($values as $specKey => $value) {
            if (isset($specConfigs[$specKey])) {
                $specConfig = $specConfigs[$specKey];
                $score = $this->scoreSpec($value, $specConfig);

                if ($score !== null) {
                    $specWeight = $specConfig['weight'];
                    $contribution = ($score / 10) * $specWeight;
                    $categoryScore += $contribution;

                    $scoredSpecs[$specKey] = [
                        'value' => $this->formatValueWithUnit($value, $specConfig),
                        'score' => $score,
                        'out_of' => 10,
                        'base_weight' => $specWeight,
                        'contribution' => round($contribution, 2),
                    ];
                    continue;
                }
            }

            // Otherwise → meta spec (no score), but KEEP POSITION
            $scoredSpecs[$specKey] = [
                'value' => is_bool($value) ? ($value ? 'Yes' : 'No') : $value,
                'score' => null,
                'out_of' => null,
                'weight' => null,
            ];

        }
        $adjustments = $this->applyContextualAdjustments($category, $values, $scoredSpecs, $profileConfig);
        $categoryScore += $adjustments['total_adjustment'];
        return [
            'score' => round($categoryScore, 2),
            'out_of' => 100,
            'specs' => $scoredSpecs,
            //'adjustments' => $adjustments['details'],
        ];
    }

    /**
     * Apply inference rules to fill missing values
     */
    protected function applyInferences(
        array $values,
        array $specConfigs,
    ): array {
        foreach ($specConfigs as $specKey => $specConfig) {
            // Skip if value already exists
            if (array_key_exists($specKey, $values) && $values[$specKey] !== null) {
                continue;
            }

            // Check if this spec has inference rules
            $inference = $specConfig['inference'] ?? null;
            if (!$inference) {
                continue;
            }
            // Check if inference conditions are met
            if (!$this->checkInferenceConditions($inference['conditions'] ?? [], $values)) {
                continue;
            }

            if (isset($inference['value_map'])) {
                $inferredValue = $this->inferValueFromMap(
                    $inference['value_map'],
                    $values
                );

                if ($inferredValue !== null) {
                    $values[$specKey] = $inferredValue;
                }
            }
        }

        return $values;
    }

    /**
     * Infer a value based on related spec values using a value map
     */
    protected function inferValueFromMap(array $valueMap, array $values): ?float
    {
        // Get peak brightness for mapping
        $peakBrightness = $values['brightness_peak'] ?? null;

        if ($peakBrightness === null) {
            return null;
        }

        // Find the first matching tier
        foreach ($valueMap as $tier) {
            $minPeak = $tier['min_peak'] ?? 0;

            if ($peakBrightness >= $minPeak) {
                return $tier['value'];
            }
        }

        // Fallback to last tier if no match
        return end($valueMap)['value'] ?? null;
    }

    /**
     * Check if all inference conditions are met
     */
    protected function checkInferenceConditions(array $conditions, array $values): bool
    {
        foreach ($conditions as $key => $condition) {

            // CONDITION 1: Key must be missing / null
            if ($condition === null) {
                if (array_key_exists($key, $values) && $values[$key] !== null && $values[$key] !== '') {
                    return false;
                }
                continue;
            }

            // CONDITION 2: Key must exist
            if (!array_key_exists($key, $values)) {
                return false;
            }

            $value = $values[$key];
            // CONDITION 3: Boolean match
            if (is_bool($condition)) {
                $valueBool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($valueBool !== $condition) {
                    return false;
                }
                continue;
            }

            // CONDITION 4: Array-based rules
            if (is_array($condition)) {

                // not_null check
                if (isset($condition['not_null']) && $condition['not_null'] === true) {
                    if ($value === null || $value === '' || $value === 0) {
                        return false;
                    }
                }

                // min check
                if (isset($condition['min']) && $value < $condition['min']) {
                    return false;
                }

                // max check
                if (isset($condition['max']) && $value > $condition['max']) {
                    return false;
                }

                continue;
            }
            if ($value !== $condition) {
                return false;
            }
        }

        return true;
    }


    protected function applyContextualAdjustments(string $category, array $values, array $scoredSpecs, array $profileConfig): array
    {

        $adjustments = [];
        $totalAdjustment = 0;
        if ($category === 'display') {
            // Extract and sanitize all values
            $brightness_sdr = (int) ($values['brightness_typical'] ?? 0);
            $brightness_hdr = (int) ($values['brightness_peak'] ?? 0);
            $sustainedBrightness = (int) ($values['sustained_brightness'] ?? 0);
            $hasHDR = ($values['hdr_support'] ?? 'no') !== 'no';
            $refreshRate = (int) ($values['refresh_rate'] ?? 0);
            $touchSampling = (int) ($values['touch_sampling_rate'] ?? 0);
            $screenSize = (float) ($values['size'] ?? 0);
            $ppi = (int) ($values['pixel_density'] ?? 0);
            $hasAdaptive = ($values['adaptive_refresh_rate'] ?? 'no') !== 'no';
            $colorBits = (int) ($values['color_depth'] ?? 8);
            $pwmFrequency = (int) ($values['pwm_frequency'] ?? 0);
            $panelType = strtolower($values['type'] ?? '');
            $contrastRatio = $values['contrast_ratio'] ?? '';
            $isLCD = str_contains($panelType, 'lcd') || str_contains($panelType, 'ips');

            // ==================================================================
            // 1. SDR/HDR Efficiency Analysis
            // ==================================================================
            if ($brightness_hdr > 0 && $brightness_sdr > 0) {
                $sdr_efficiency_ratio = $brightness_sdr / $brightness_hdr;

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
                $penalty = match (true) {
                    $pwmFrequency < 250 => -2.5,  // very bad, severe flicker
                    $pwmFrequency < 500 => -2.0,  // bad
                    $pwmFrequency < 1000 => -1.0,  // noticeable but mild
                };
                $totalAdjustment += $penalty;
                $adjustments[] = [
                    'type' => 'penalty',
                    'reason' => "Low PWM frequency ({$pwmFrequency}Hz) may cause eye strain for sensitive users",
                    'value' => $penalty,
                ];
            }

            // ==================================================================
            // 5. Refresh Rate vs Touch Sampling Validation
            // ==================================================================
            if ($refreshRate >= 120 && $touchSampling > 0) {
                $touchRatio = $touchSampling / $refreshRate;

                if ($touchRatio < 1.5) {
                    $penalty = match (true) {
                        $touchRatio < 1.0 => -2.0,  // touch sampling is lower than refresh rate
                        $touchRatio < 1.5 => -1.0,  // close but not proportional
                    };
                    $totalAdjustment += $penalty;
                    $adjustments[] = [
                        'type' => 'penalty',
                        'reason' => "High refresh rate ({$refreshRate}Hz) without proportional touch sampling ({$touchSampling}Hz)",
                        'value' => $penalty,
                    ];
                }
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
                } elseif ($ppi < 400) {
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
            } else if ($colorBits >= 10 && $hasHDR && $isOLED && $ppi >= 400 && $refreshRate >= 120) {
                $bonus = match (true) {
                    $colorBits >= 12 => 1.5,
                    $colorBits >= 10 => 1.0, // Now catches 10-bit panels
                };
                $totalAdjustment += $bonus;
                $adjustments[] = [
                    'type' => 'bonus',
                    'reason' => "{$colorBits}-bit color depth with HDR, OLED, {$ppi} PPI, and {$refreshRate}Hz refresh - excellent display",
                    'value' => $bonus,
                ];
            }

            // ==================================================================
            // 9. Contrast Ratio Analysis (SAFE STRING HANDLING)
            // ==================================================================
            // Clean contrast ratio string (handles "5,000,000:1", "Infinite", etc.)
            $cleanContrast = (int) filter_var($contrastRatio, FILTER_SANITIZE_NUMBER_INT);
            $isLCD = str_contains($panelType, 'lcd') || str_contains($panelType, 'ips');

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
                $minRefreshRate = (int) ($values['min_refresh_rate'] ?? $refreshRate);
                $range = $refreshRate - $minRefreshRate;

                if ($refreshRate >= 120 && $range < 60) {
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
        $value = str_replace(['-', '-', '—', '-', '_'], '-', $value);

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

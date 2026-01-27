<?php

namespace App\Services;

class CompareScoreService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('compare_scoring');
    }

    /* -------------------------------
       PUBLIC API
    --------------------------------*/

    public function scoreCategory(
        string $category,
        array $values,
        string $profile = 'balanced'
    ): array {
        // Early exit if the category is not defined
        if (!isset($this->config[$category])) {
            return ['score' => 0, 'specs' => []];
        }


        $specConfigs = $this->config[$category]['specs'];

        $profileMultipliers = config("compare_scoring.compare_profiles.$profile.$category.specs", []);
        $scoredSpecs = [];

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

            // Format value with unit if applicable
            $valueWithUnit = $this->formatValueWithUnit($value, $specConfig);

            // Calculate effective weight
            $baseWeight = $specConfig['weight'];
            $multiplier = $profileMultipliers[$specKey] ?? 1;
            $effectiveWeight = round($baseWeight * $multiplier, 2);

            // Store scored spec
            $scoredSpecs[$specKey] = [
                'value' => $valueWithUnit,
                'score' => $score,
                'out_of' => 10,
                'weight' => $effectiveWeight,
                'base_weight' => $baseWeight,
                'profile_multiplier' => $multiplier,
            ];
        }

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
            'score' => $this->calculateWeightedScore($scoredSpecs),
            'out_of' => 100,
            'specs' => $scoredSpecs,
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

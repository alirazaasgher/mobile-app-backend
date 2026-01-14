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

    public function scoreCategory(string $category, array $values): array
    {
        if (!isset($this->config[$category])) {
            return ['score' => 0, 'specs' => []];
        }

        $specConfigs = $this->config[$category]['specs'];

        $scoredSpecs = [];

        foreach ($specConfigs as $specKey => $specConfig) {

            if (!array_key_exists($specKey, $values)) {
                continue; // missing spec is ignored
            }

            $value = $values[$specKey];
            $score = $this->scoreSpec($value, $specConfig);

            if ($score === null) {
                continue;
            }

            if (isset($specConfig['unit']) && $value !== null && $value !== '') {
                $unit = $specConfig['unit']['value'] ?? '';
                $position = $specConfig['unit']['position'] ?? 'after';
                $space = $specConfig['unit']['space'] ?? true;

                $separator = $space ? ' ' : '';

                if ($position === 'before') {
                    $valueWithUnit = $unit . $separator . $value;
                } else { // after
                    $valueWithUnit = $value . $separator . $unit;
                }
            } else {
                $valueWithUnit = $value;
            }


            $scoredSpecs[$specKey] = [
                'value' => $valueWithUnit,
                'score' => $score,
                'out_of' => 10,
                'weight' => $specConfig['weight']
            ];
        }

        $metaKeys = array_diff_key($values, $specConfigs);

        foreach ($metaKeys as $metaKey => $metaValue) {
            $scoredSpecs[$metaKey] = [
                'value' => $metaValue,
                'score' => null,      // no numeric score
                'out_of' => null,
                'weight' => null,
            ];
        }

        return [
            'score' => $this->calculateWeightedScore($scoredSpecs),
            'out_of' => 100,
            'specs' => $scoredSpecs
        ];
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

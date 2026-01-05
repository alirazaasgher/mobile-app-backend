<?php
// app/Services/PhoneComparisonService.php
namespace App\Services;

class PhoneComparisonService
{
    protected $weights;
    protected $benchmarkScores;

    public function __construct()
    {
        $this->weights = config('comparison_weights');
        $this->benchmarkScores = $this->loadBenchmarkScores();
    }

    /**
     * Compare multiple phones and return scores
     */
    public function comparePhones($phones): array
    {
        $results = [];
        
        foreach ($phones as $phone) {
           
            $specs = $phone->compare_specs['key'];
            $scores = [
                'overall' => 0,
                'categories' => []
            ];

            foreach ($this->weights as $category => $categoryConfig) {
                $categoryScore = $this->calculateCategoryScore(
                    $specs[$category] ?? [],
                    $categoryConfig,
                    $phones // Pass all phones for relative comparison
                );

                $scores['categories'][$category] = [
                    'score' => $categoryScore,
                    'weighted_score' => $categoryScore * $categoryConfig['weight'],
                    'weight' => $categoryConfig['weight'],
                    'specs' => $this->getSpecScores($specs[$category] ?? [], $categoryConfig)
                ];

                $scores['overall'] += $categoryScore * $categoryConfig['weight'];
            }

            $results[$phone->id] = [
                'phone' => $phone,
                'scores' => $scores
            ];
        }

        return $this->addComparativeInsights($results);
    }

    /**
     * Calculate score for a specific category
     */
    protected function calculateCategoryScore(array $specs, array $config, $allPhones): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($config['specs'] as $specKey => $specConfig) {
            $value = $specs[$specKey] ?? null;
            
            if ($value === null) {
                continue; // Skip missing specs
            }

            $score = $this->calculateSpecScore($value, $specConfig, $specKey, $allPhones);
            $totalScore += $score * $specConfig['weight'];
            $totalWeight += $specConfig['weight'];
        }

        return $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;
    }

    /**
     * Calculate individual spec score
     */
    protected function calculateSpecScore($value, array $config, string $specKey, $allPhones): float
    {
        switch ($config['type']) {
            case 'numeric_higher':
                return $this->numericHigherScore($value, $specKey, $allPhones);
            
            case 'optimal_range':
                return $this->optimalRangeScore($value, $config['optimal']);
            
            case 'ranking':
                return $this->rankingScore($value, $config['values']);
            
            case 'boolean':
                return $this->booleanScore($value);
            
            case 'benchmark':
                return $this->benchmarkScore($value);
            
            case 'version_comparison':
                return $this->versionScore($value, $specKey, $allPhones);
            
            case 'camera_scoring':
                return $this->cameraScore($value);
            
            case 'video_scoring':
                return $this->videoScore($value);
            
            case 'wifi_scoring':
                return $this->wifiScore($value);
            
            case 'usb_scoring':
                return $this->usbScore($value);
            
            case 'update_scoring':
                return $this->updateScore($value);
            
            default:
                return 0.5; // Neutral score for unknown types
        }
    }

    /**
     * Numeric comparison (higher is better)
     */
    protected function numericHigherScore($value, string $specKey, $allPhones): float
    {
        $numericValue = $this->extractNumericValue($value);
        
        if ($numericValue === null) {
            return 0;
        }

        // Get all values for this spec from all phones
        $allValues = [];
        foreach ($allPhones as $phone) {
            $specs = $phone->compare_specs['key'];
            $categoryKey = $this->getCategoryForSpec($specKey);
            $phoneValue = $specs[$categoryKey][$specKey] ?? null;
            
            if ($phoneValue !== null) {
                $allValues[] = $this->extractNumericValue($phoneValue);
            }
        }

        $allValues = array_filter($allValues);
        
        if (empty($allValues)) {
            return 0;
        }

        $min = min($allValues);
        $max = max($allValues);

        if ($max == $min) {
            return 1.0; // All phones have same value
        }

        // Normalize to 0-1 scale
        return ($numericValue - $min) / ($max - $min);
    }

    /**
     * Optimal range scoring (e.g., screen size sweet spot)
     */
    protected function optimalRangeScore($value, array $range): float
    {
        $numericValue = $this->extractNumericValue($value);
        
        if ($numericValue === null) {
            return 0;
        }

        [$min, $max] = $range;
        $mid = ($min + $max) / 2;

        if ($numericValue >= $min && $numericValue <= $max) {
            // Within optimal range, score based on distance from midpoint
            $distanceFromMid = abs($numericValue - $mid);
            $maxDistance = ($max - $min) / 2;
            return 1.0 - ($distanceFromMid / $maxDistance) * 0.2; // 0.8-1.0 for optimal range
        }

        // Outside optimal range
        if ($numericValue < $min) {
            $distance = $min - $numericValue;
            return max(0, 0.8 - ($distance / $min) * 0.8);
        } else {
            $distance = $numericValue - $max;
            return max(0, 0.8 - ($distance / $max) * 0.8);
        }
    }

    /**
     * Ranking-based scoring
     */
    protected function rankingScore($value, array $rankingValues): float
    {
        if (isset($rankingValues[$value])) {
            return $rankingValues[$value] / 10; // Normalize to 0-1
        }

        // Fuzzy matching for similar values
        foreach ($rankingValues as $key => $score) {
            if (stripos($value, $key) !== false || stripos($key, $value) !== false) {
                return $score / 10;
            }
        }

        return 0.5; // Default for unknown values
    }

    /**
     * Boolean scoring
     */
    protected function booleanScore($value): float
    {
        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        // Handle string values
        $value = strtolower(trim($value));
        return in_array($value, ['yes', 'true', '1', 'available']) ? 1.0 : 0.0;
    }

    /**
     * Benchmark-based scoring (chipset performance)
     */
    protected function benchmarkScore($chipset): float
    {
        $chipset = strtolower(trim($chipset));
        
        // Check if we have benchmark data
        if (isset($this->benchmarkScores[$chipset])) {
            $score = $this->benchmarkScores[$chipset];
            // Normalize to 0-1 based on max known score
            return min(1.0, $score / 2000000); // Adjust divisor based on current top scores
        }

        // Fallback: heuristic based on chipset name
        return $this->heuristicChipsetScore($chipset);
    }

    /**
     * Heuristic chipset scoring when benchmark data unavailable
     */
    protected function heuristicChipsetScore($chipset): float
    {
        // Snapdragon series
        if (preg_match('/snapdragon\s*(\d+)/i', $chipset, $matches)) {
            $number = (int)$matches[1];
            if ($number >= 8) {
                return 0.9 + ($number - 800) / 1000; // 8xx series
            } elseif ($number >= 7) {
                return 0.7 + ($number - 700) / 1000;
            } else {
                return 0.5;
            }
        }

        // Apple A-series
        if (preg_match('/a(\d+)/i', $chipset, $matches)) {
            $number = (int)$matches[1];
            return min(1.0, 0.6 + ($number - 12) / 10);
        }

        // MediaTek Dimensity
        if (preg_match('/dimensity\s*(\d+)/i', $chipset, $matches)) {
            $number = (int)$matches[1];
            if ($number >= 9000) {
                return 0.9;
            } elseif ($number >= 8000) {
                return 0.8;
            } else {
                return 0.6;
            }
        }

        // Google Tensor
        if (stripos($chipset, 'tensor') !== false) {
            return 0.85;
        }

        return 0.5; // Default for unknown chipsets
    }

    /**
     * Camera scoring
     */
    protected function cameraScore($cameraSetup): float
    {
        if (empty($cameraSetup) || !is_array($cameraSetup)) {
            return 0;
        }

        $score = 0;
        $count = 0;

        foreach ($cameraSetup as $camera) {
            $mp = $this->extractNumericValue($camera['megapixels'] ?? '0');
            $aperture = $this->extractNumericValue($camera['aperture'] ?? '2.0');
            
            // Higher MP is better (up to a point)
            $mpScore = min(1.0, $mp / 200);
            
            // Lower aperture is better (wider)
            $apertureScore = max(0, 1.0 - (($aperture - 1.4) / 1.4));
            
            // Check for special features
            $hasOIS = isset($camera['features']) && stripos($camera['features'], 'ois') !== false;
            $hasLaser = isset($camera['features']) && stripos($camera['features'], 'laser') !== false;
            
            $cameraScore = ($mpScore * 0.4) + ($apertureScore * 0.4) + ($hasOIS ? 0.15 : 0) + ($hasLaser ? 0.05 : 0);
            
            $score += $cameraScore;
            $count++;
        }

        return $count > 0 ? $score / $count : 0;
    }

    /**
     * Video recording scoring
     */
    protected function videoScore($videoSpec): float
    {
        if (empty($videoSpec)) {
            return 0;
        }

        $score = 0;
        
        // Check for 8K
        if (stripos($videoSpec, '8k') !== false) {
            $score = 1.0;
        } elseif (stripos($videoSpec, '4k') !== false) {
            // Check frame rate
            if (preg_match('/(\d+)fps/i', $videoSpec, $matches)) {
                $fps = (int)$matches[1];
                if ($fps >= 120) {
                    $score = 0.95;
                } elseif ($fps >= 60) {
                    $score = 0.85;
                } else {
                    $score = 0.75;
                }
            } else {
                $score = 0.75;
            }
        } elseif (stripos($videoSpec, '1080p') !== false) {
            $score = 0.5;
        }

        return $score;
    }

    /**
     * WiFi version scoring
     */
    protected function wifiScore($wifi): float
    {
        if (stripos($wifi, '7') !== false) return 1.0;
        if (stripos($wifi, '6e') !== false) return 0.9;
        if (stripos($wifi, '6') !== false) return 0.8;
        if (stripos($wifi, '5') !== false) return 0.6;
        return 0.4;
    }

    /**
     * USB standard scoring
     */
    protected function usbScore($usb): float
    {
        if (stripos($usb, '4') !== false) return 1.0;
        if (stripos($usb, '3.2') !== false) return 0.9;
        if (stripos($usb, '3.1') !== false) return 0.8;
        if (stripos($usb, '3.0') !== false) return 0.7;
        if (stripos($usb, '2.0') !== false) return 0.4;
        return 0.5;
    }

    /**
     * Software update policy scoring
     */
    protected function updateScore($updatePolicy): float
    {
        if (empty($updatePolicy)) {
            return 0;
        }

        // Extract years of OS updates and security patches
        preg_match('/(\d+)\s*years?.*os/i', $updatePolicy, $osMatches);
        preg_match('/(\d+)\s*years?.*security/i', $updatePolicy, $securityMatches);

        $osYears = isset($osMatches[1]) ? (int)$osMatches[1] : 0;
        $securityYears = isset($securityMatches[1]) ? (int)$securityMatches[1] : 0;

        // Weight OS updates more than security
        $score = ($osYears / 7) * 0.6 + ($securityYears / 7) * 0.4;

        return min(1.0, $score);
    }

    /**
     * Version comparison scoring
     */
    protected function versionScore($value, string $specKey, $allPhones): float
    {
        $version = $this->extractVersion($value);
        
        if ($version === null) {
            return 0;
        }

        // Get all versions from all phones
        $allVersions = [];
        foreach ($allPhones as $phone) {
            $specs = $phone->compare_specs['key'];
            $categoryKey = $this->getCategoryForSpec($specKey);
            $phoneValue = $specs[$categoryKey][$specKey] ?? null;
            
            if ($phoneValue !== null) {
                $v = $this->extractVersion($phoneValue);
                if ($v !== null) {
                    $allVersions[] = $v;
                }
            }
        }

        if (empty($allVersions)) {
            return 0;
        }

        $minVersion = min($allVersions);
        $maxVersion = max($allVersions);

        if ($maxVersion == $minVersion) {
            return 1.0;
        }

        return ($version - $minVersion) / ($maxVersion - $minVersion);
    }

    /**
     * Get detailed spec scores
     */
    protected function getSpecScores(array $specs, array $config): array
    {
        $specScores = [];

        foreach ($config['specs'] as $specKey => $specConfig) {
            $value = $specs[$specKey] ?? null;
            
            $specScores[$specKey] = [
                'value' => $value,
                'score' => $value !== null ? $this->calculateSpecScore($value, $specConfig, $specKey, []) * 100 : null,
                'weight' => $specConfig['weight']
            ];
        }

        return $specScores;
    }

    /**
     * Add comparative insights
     */
    protected function addComparativeInsights(array $results): array
    {
        // Find winner in each category
        foreach ($this->weights as $category => $categoryConfig) {
            $maxScore = 0;
            $winnerId = null;

            foreach ($results as $phoneId => $result) {
                $score = $result['scores']['categories'][$category]['score'] ?? 0;
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $winnerId = $phoneId;
                }
            }

            // Mark winner
            foreach ($results as $phoneId => &$result) {
                $result['scores']['categories'][$category]['is_winner'] = ($phoneId === $winnerId);
            }
        }

        // Find overall winner
        $maxOverall = 0;
        $overallWinnerId = null;

        foreach ($results as $phoneId => $result) {
            if ($result['scores']['overall'] > $maxOverall) {
                $maxOverall = $result['scores']['overall'];
                $overallWinnerId = $phoneId;
            }
        }

        foreach ($results as $phoneId => &$result) {
            $result['scores']['is_overall_winner'] = ($phoneId === $overallWinnerId);
        }

        return $results;
    }

    /**
     * Helper: Extract numeric value from string
     */
    protected function extractNumericValue($value): ?float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value)) {
            preg_match('/(\d+\.?\d*)/', $value, $matches);
            return isset($matches[1]) ? (float)$matches[1] : null;
        }

        return null;
    }

    /**
     * Helper: Extract version number
     */
    protected function extractVersion($value): ?float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value)) {
            preg_match('/(\d+\.?\d*)/', $value, $matches);
            return isset($matches[1]) ? (float)$matches[1] : null;
        }

        return null;
    }

    /**
     * Helper: Get category for a spec
     */
    protected function getCategoryForSpec(string $specKey): string
    {
        foreach ($this->weights as $category => $config) {
            if (isset($config['specs'][$specKey])) {
                return $category;
            }
        }

        return '';
    }

    /**
     * Load benchmark scores (you can cache this)
     */
    protected function loadBenchmarkScores(): array
    {
        // You would populate this from a database or API
        // This is just sample data
        return [
            'snapdragon 8 gen 3' => 1900000,
            'snapdragon 8 gen 2' => 1600000,
            'apple a17 pro' => 1850000,
            'apple a16 bionic' => 1700000,
            'dimensity 9300' => 1750000,
            'google tensor g3' => 1200000,
            // ... more chipsets
        ];
    }
}
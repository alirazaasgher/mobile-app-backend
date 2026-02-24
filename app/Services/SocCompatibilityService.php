<?php

namespace App\Services;

class SocCompatibilityService
{
    private array $phoneSpecs;
    private array $chipsetSpecs;
    private array $results = [];

    public function __construct(array $phoneSpecs, array $chipsetSpecs)
    {
        $this->phoneSpecs = $this->flattenSpecs($phoneSpecs);
        $this->chipsetSpecs = $this->flattenSpecs($chipsetSpecs);
    }

    // ─────────────────────────────────────────────
    // MAIN ENTRY POINT
    // ─────────────────────────────────────────────

    public function check(): array
    {
        $this->checkDisplayResolution();
        $this->checkRefreshRate();
        $this->checkCamera();
        $this->checkVideo();
        $this->checkRamSpeed();
        $this->checkAspectRatio();
        $this->checkDisplayBandwidth();

        $hasWarnings = collect($this->results)->contains(fn($r) => $r['status'] !== 'ok');
        $hardLimits = collect($this->results)->filter(fn($r) => $r['status'] === 'hard_limit')->count();
        $warnings = collect($this->results)->filter(fn($r) => $r['status'] === 'warning')->count();

        return [
            'has_issues' => $hasWarnings,
            'hard_limits' => $hardLimits,
            'warnings' => $warnings,
            'checks' => $this->results,
        ];
    }

    // ─────────────────────────────────────────────
    // 1. DISPLAY RESOLUTION  (Hard Limit)
    // ─────────────────────────────────────────────

    private function checkDisplayResolution(): void
    {
        // Phone: "2772 x 1280 (~447 PPI)"
        $phoneRes = $this->phoneSpecs['resolution'] ?? null;
        // Chipset: "2880 * 1208"
        $chipsetRes = $this->chipsetSpecs['max_display_res'] ?? null;
        if (!$phoneRes || !$chipsetRes) {
            $this->results[] = $this->unavailable('resolution', 'Display Resolution');
            return;
        }

        $phoneWidth = $this->parseResolutionWidth($phoneRes);
        $chipsetWidth = $this->parseResolutionWidth($chipsetRes);

        if (!$phoneWidth || !$chipsetWidth) {
            $this->results[] = $this->unavailable('resolution', 'Display Resolution');
            return;
        }

        if ($phoneWidth > $chipsetWidth) {
            $this->results[] = [
                'type' => 'resolution',
                'label' => 'Display Resolution',
                'status' => 'hard_limit',
                'phone_value' => $this->cleanResolution($phoneRes),
                // 'soc_max' => $this->cleanResolution($chipsetRes),
                'note' => "Exceeds SOC maximum. The manufacturer claims {$this->cleanResolution($phoneRes)}, but this chipset natively supports up to {$this->cleanResolution($chipsetRes)}.",
            ];
        } else {
            $this->results[] = $this->ok('resolution', 'Display Resolution', $this->cleanResolution($phoneRes), $this->cleanResolution($chipsetRes));
        }
    }

    // ─────────────────────────────────────────────
    // 2. REFRESH RATE
    // ─────────────────────────────────────────────

    private function checkRefreshRate(): void
    {
        // Phone: "120Hz"
        $phoneHz = $this->phoneSpecs['refresh_rate'] ?? null;
        // Chipset: "120Hz"
        $chipsetHz = $this->chipsetSpecs['max_refresh_rate'] ?? null;

        if (!$phoneHz || !$chipsetHz) {
            $this->results[] = $this->unavailable('refresh_rate', 'Refresh Rate');
            return;
        }

        $phoneVal = $this->parseHz($phoneHz);
        $chipsetVal = $this->parseHz($chipsetHz);

        if (!$phoneVal || !$chipsetVal) {
            $this->results[] = $this->unavailable('refresh_rate', 'Refresh Rate');
            return;
        }

        if ($phoneVal > $chipsetVal) {
            $this->results[] = [
                'type' => 'refresh_rate',
                'label' => 'Refresh Rate',
                'status' => 'hard_limit',
                'phone_value' => "{$phoneVal}Hz",
                'note' => "Exceeds SOC maximum. The manufacturer specifies {$phoneVal}Hz, but this chipset natively supports up to {$chipsetVal}Hz.",
            ];
        } else {
            $this->results[] = $this->ok('refresh_rate', 'Refresh Rate', "{$phoneVal}Hz", "{$chipsetVal}Hz");
        }
    }

    // ─────────────────────────────────────────────
    // 3. CAMERA MEGAPIXELS
    // ─────────────────────────────────────────────

    private function checkCamera(): void
    {

        // Phone: "50 MP (wide), 8 MP (ultrawide)"  — take highest
        $phoneCamera = $this->phoneSpecs['setup'] ?? null;
        // Chipset: "200MP" or "200 MP"
        $chipsetCamera = $this->chipsetSpecs['max_camera_res'] ?? null;

        if (!$phoneCamera || !$chipsetCamera) {
            $this->results[] = $this->unavailable('setup', 'Camera Resolution');
            return;
        }

        $phoneMP = $this->parseHighestMP($phoneCamera);
        $chipsetMP = $this->parseMP($chipsetCamera);

        if (!$phoneMP || !$chipsetMP) {
            $this->results[] = $this->unavailable('setup', 'Camera Resolution');
            return;
        }

        if ($phoneMP > $chipsetMP) {
            $this->results[] = [
                'type' => 'setup',
                'label' => 'Camera Resolution',
                'status' => 'warning',
                'phone_value' => "{$phoneMP}MP",
                'soc_max' => "{$chipsetMP}MP",
                'note' => 'Achieved via pixel binning or software tricks. Not true native resolution.',
            ];
        } else {
            $this->results[] = $this->ok('setup', 'Camera Resolution', "{$phoneMP}MP", "{$chipsetMP}MP");
        }
    }

    // ─────────────────────────────────────────────
    // 4. VIDEO CAPTURE  (Hard Limit)
    // ─────────────────────────────────────────────

    private function checkVideo(): void
    {
        // Phone: "4K@30fps, 1080p@30/60fps..."
        $phoneVideo = $this->phoneSpecs['video'] ?? null;
        // Chipset: "8k @ 60fps"
        $chipsetVideo = $this->chipsetSpecs['video_playback'] ?? null;

        if (!$phoneVideo || !$chipsetVideo) {
            $this->results[] = $this->unavailable('video', 'Video Recording');
            return;
        }

        $phoneRes = $this->parseVideoResolution($phoneVideo);
        $chipsetRes = $this->parseVideoResolution($chipsetVideo);

        if (!$phoneRes || !$chipsetRes) {
            $this->results[] = $this->unavailable('video', 'Video Recording');
            return;
        }

        $phoneRank = $this->videoRank($phoneRes);
        $chipsetRank = $this->videoRank($chipsetRes);

        if ($phoneRank > $chipsetRank) {
            $this->results[] = [
                'type' => 'video',
                'label' => 'Video Recording',
                'status' => 'hard_limit',
                'phone_value' => strtoupper($phoneRes),
                'soc_max' => strtoupper($chipsetRes),
                'note' => 'Hard limit. SOC cannot encode this resolution. Phone will cap at SOC maximum.',
            ];
        } else {
            $this->results[] = $this->ok('video', 'Video Recording', strtoupper($phoneRes), strtoupper($chipsetRes));
        }
    }

    // ─────────────────────────────────────────────
    // 5. RAM SPEED
    // ─────────────────────────────────────────────

    private function checkRamSpeed(): void
    {
        // Phone: "LPDDR4X"
        $phoneRam = $this->phoneSpecs['ram_type'] ?? null;
        // Chipset: ["LPDDR", "LPDDR3", "LPDDR4"]  — take highest supported
        $chipsetRam = $this->chipsetSpecs['memory_type'] ?? null;
        if (!$phoneRam || !$chipsetRam) {
            $this->results[] = $this->unavailable('ram_speed', 'RAM Speed');
            return;
        }

        $phoneRank = $this->ramRank($phoneRam);
        // chipset memory_type is an array — take the highest rank
        $chipsetRams = is_array($chipsetRam) ? $chipsetRam : [$chipsetRam];
        $chipsetRank = max(array_map(fn($r) => $this->ramRank($r), $chipsetRams));
        $chipsetMax = $this->highestRam($chipsetRams);

        if ($phoneRank > $chipsetRank) {
            $this->results[] = [
                'type' => 'ram_speed',
                'label' => 'RAM Speed',
                'status' => 'warning',
                'phone_value' => strtoupper($phoneRam),
                'soc_max' => strtoupper($chipsetMax),
                'note' => 'Phone RAM will be downclocked to SOC maximum supported speed. Performance is limited.',
            ];
        } else {
            $this->results[] = $this->ok('ram_speed', 'RAM Speed', strtoupper($phoneRam), strtoupper($chipsetMax));
        }
    }

    private function checkAspectRatio(): void
    {
        $phoneW = $this->parseResolutionWidth($this->phoneSpecs['resolution'] ?? '');
        $phoneH = $this->parseResolutionHeight($this->phoneSpecs['resolution'] ?? '');

        // Get SoC max resolution from your chipset data (e.g., "3840 x 2160")
        $socRawRes = $this->chipsetSpecs['max_display_res'] ?? '3840 x 2160';
        $socW = $this->parseResolutionWidth($socRawRes);
        $socH = $this->parseResolutionHeight($socRawRes);

        $phoneRatioStr = $this->calculateAspectRatio($phoneW, $phoneH);
        $socRatioStr = $this->calculateAspectRatio($socW, $socH);

        $decimalPhone = $this->getDecimalRatio($phoneW, $phoneH);

        // Logic: Warning if phone ratio is more "extreme" than what the SoC was built for
        $status = ($decimalPhone > 2.3) ? 'warning' : 'ok';

        $this->results[] = [
            'type' => 'aspect_ratio',
            'label' => 'Aspect Ratio',
            'status' => $status,
            'phone_value' => $phoneRatioStr, // e.g., "19.6:9"
            'soc_max' => $socRatioStr,     // e.g., "16:9 (Native Max)" or "21:9"
            'note' => $status === 'warning' ? 'Extremely tall display might require custom scaling.' : ''
        ];
    }

    private function checkDisplayBandwidth(): void
    {
        $w = $this->parseResolutionWidth($this->phoneSpecs['resolution']);
        $h = $this->parseResolutionHeight($this->phoneSpecs['resolution']);
        $refresh = (int) ($this->phoneSpecs['refresh_rate'] ?? 60);
        $bitDepth = 10; // 2026 Standard

        $requiredGbps = ($w * $h * $refresh * $bitDepth * 3) / 10 ** 9;

        // Define SoC Max based on Chipset
        $chipsetName = strtoupper($this->chipsetSpecs['name'] ?? '');
        $maxGbps = 48.0; // Default for 2026 Flagships (HDMI 2.1 equivalent)

        if (str_contains($chipsetName, 'DIMENSITY 9500') || str_contains($chipsetName, '8 ELITE')) {
            $maxGbps = 60.0; // Next-gen bandwidth
        } elseif (str_contains($chipsetName, '7 GEN') || str_contains($chipsetName, 'DIMENSITY 8')) {
            $maxGbps = 32.0;
        }

        $status = $requiredGbps > $maxGbps ? 'hard_limit' : 'ok';

        $this->results[] = [
            'type' => 'display_bandwidth',
            'label' => 'Display Bandwidth',
            'status' => $status,
            'phone_value' => round($requiredGbps, 1) . " Gbps",
            'soc_max' => $maxGbps . " Gbps",
            'note' => $status === 'hard_limit' ? 'Exceeds native DPU bandwidth. Requires DSC (Compression) or external scalar.' : ''
        ];
    }
    // ─────────────────────────────────────────────
    // PARSERS
    // ─────────────────────────────────────────────
    private function parseResolutionHeight(string $res): ?int
    {
        if (preg_match('/(?:\*|x|X|by)\s*(\d{3,5})/', $res, $matches)) {
            return (int) $matches[1];
        }
        // If first regex fails, try picking the smaller number from the string
        preg_match_all('/\d+/', $res, $numbers);
        return count($numbers[0]) >= 2 ? min((int) $numbers[0][0], (int) $numbers[0][1]) : null;
    }
    private function calculateAspectRatio(int $width, int $height): string
    {
        $longSide = max($width, $height);
        $shortSide = min($width, $height);

        // Calculate what the long side would be if the short side was 9
        // Formula: (Long Side / Short Side) * 9
        $normalizedLong = round(($longSide / $shortSide) * 9, 1);

        // Check if it's a clean whole number (like 18.0) and remove .0
        $normalizedLong = str_replace('.0', '', (string) $normalizedLong);

        return $normalizedLong . ":9";
    }
    private function getDecimalRatio(int $width, int $height): float
    {
        return round(max($width, $height) / min($width, $height), 2);
    }
    // "2772 x 1280 (~447 PPI)"  OR  "2880 * 1208"  → 2772
    private function parseResolutionWidth(string $res): ?int
    {
        if (preg_match('/(\d{3,5})\s*[x*×]\s*(\d{3,5})/i', $res, $matches)) {
            return max((int) $matches[1], (int) $matches[2]);
        }

        return null;
    }

    private function cleanResolution(string $res): string
    {
        preg_match('/(\d{3,4})\s*[x*×]\s*(\d{3,4})/i', $res, $matches);
        return isset($matches[1], $matches[2]) ? "{$matches[1]} x {$matches[2]}" : $res;
    }

    // "120Hz" → 120
    private function parseHz(string $hz): ?int
    {
        preg_match('/(\d+)\s*hz/i', $hz, $matches);
        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    // "50 MP (wide), 8 MP (ultrawide)" → 50
    private function parseHighestMP(string $setup): ?int
    {
        preg_match_all('/(\d+)\s*mp/i', $setup, $matches);
        if (empty($matches[1]))
            return null;
        return max(array_map('intval', $matches[1]));
    }

    // "200MP" or "200 MP" → 200
    private function parseMP(string $mp): ?int
    {
        preg_match('/(\d+)\s*mp/i', $mp, $matches);
        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    // "4K@30fps, 1080p..." → "4k"   |   "8k @ 60fps" → "8k"
    private function parseVideoResolution(string $video): ?string
    {
        $video = strtolower($video);
        if (str_contains($video, '8k'))
            return '8k';
        if (str_contains($video, '4k'))
            return '4k';
        if (str_contains($video, '1080'))
            return '1080p';
        if (str_contains($video, '720'))
            return '720p';
        return null;
    }

    // Higher = better
    private function videoRank(string $res): int
    {
        return match (strtolower($res)) {
            '8k' => 4,
            '4k' => 3,
            '1080p' => 2,
            '720p' => 1,
            default => 0,
        };
    }

    // "LPDDR4X" → rank
    private function ramRank(string $ram): int
    {
        $ram = strtolower(trim($ram));
        return match (true) {
            str_contains($ram, 'lpddr5x') => 5,
            str_contains($ram, 'lpddr5') => 4,
            str_contains($ram, 'lpddr4x') => 3,
            str_contains($ram, 'lpddr4') => 2,
            str_contains($ram, 'lpddr3') => 1,
            str_contains($ram, 'lpddr') => 0,
            default => -1,
        };
    }

    private function highestRam(array $rams): string
    {
        return collect($rams)
            ->sortByDesc(fn($r) => $this->ramRank($r))
            ->first();
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    private function flattenSpecs(array $specs): array
    {
        $flat = [];

        foreach ($specs as $spec) {

            if (!empty($spec['specifications'])) {

                $decodedSpecs = json_decode($spec['specifications'], true);

                if (is_array($decodedSpecs)) {
                    foreach ($decodedSpecs as $key => $value) {
                        $flat[$key] = $value;
                    }
                }
            }
        }

        return $flat;
    }

    private function ok(string $type, string $label, string $phoneValue, string $socMax): array
    {
        return [
            'type' => $type,
            'label' => $label,
            'status' => 'ok',
            'phone_value' => $phoneValue,
            'soc_max' => $socMax,
            'note' => null,
        ];
    }

    private function unavailable(string $type, string $label): array
    {
        return [
            'type' => $type,
            'label' => $label,
            'status' => 'unavailable',
            'phone_value' => null,
            'soc_max' => null,
            'note' => 'Data not available yet.',
        ];
    }
}

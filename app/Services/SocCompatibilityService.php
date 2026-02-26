<?php

namespace App\Services;

use App\Models\RamType;
use App\Models\StorageType;

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
        $this->checkScreenToBody();
        $this->checkAspectRatio();
        $this->checkDisplayBandwidth();
        $this->checkUsbSpeed();
        $this->checkStorageSpeed();

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
        $phoneCamera = $this->phoneSpecs['rear'] ?? null;

        // Chipset: "200MP" or "200 MP"
        $chipsetCamera = $this->chipsetSpecs['max_camera_res'] ?? null;
        if (!$phoneCamera || !$chipsetCamera) {
            $this->results[] = $this->unavailable('rear', 'Camera Resolution');
            return;
        }

        $phoneMP = $this->parseHighestMP($phoneCamera);
        $chipsetMP = $this->parseMP($chipsetCamera);

        if (!$phoneMP || !$chipsetMP) {
            $this->results[] = $this->unavailable('rear', 'Camera Resolution');
            return;
        }

        if ($phoneMP > $chipsetMP) {
            $this->results[] = [
                'type' => 'rear',
                'label' => 'Camera Resolution',
                'status' => 'warning',
                'phone_value' => "{$phoneMP}MP",
                'soc_max' => "{$chipsetMP}MP",
                'note' => "The manufacturer uses a {$phoneMP} MP sensor, but this SoC ISP natively supports up to {$chipsetMP} MP. Full-resolution captures will likely experience shutter lag or require heavy software processing.",
            ];
        } else {
            $this->results[] = $this->ok('rear', 'Camera Resolution', "{$phoneMP}MP", "{$chipsetMP}MP");
        }
    }

    // ─────────────────────────────────────────────
    // 4. VIDEO CAPTURE  (Hard Limit)
    // ─────────────────────────────────────────────

    private function checkVideo(): void
    {
        $phoneVideo = $this->phoneSpecs['video'] ?? null;
        $chipsetVideo = $this->chipsetSpecs['video_playback'] ?? null;

        if (!$phoneVideo || !$chipsetVideo) {
            $this->results[] = $this->unavailable('video', 'Video Recording');
            return;
        }

        // Extract both Res and FPS: e.g., ['res' => 4000, 'fps' => 120]
        $phoneData = $this->parseVideoSpec($phoneVideo);
        $socData = $this->parseVideoSpec($chipsetVideo);

        if (!$phoneData || !$socData) {
            $this->results[] = $this->unavailable('video', 'Video Recording');
            return;
        }

        // Calculate "Power Score" (Res * FPS)
        $phoneScore = $phoneData['res_value'] * $phoneData['fps'];
        $socScore = $socData['res_value'] * $socData['fps'];

        if ($phoneScore > $socScore) {
            $this->results[] = [
                'type' => 'video',
                'label' => 'Video Performance',
                'status' => 'hard_limit',
                'phone_value' => "{$phoneData['label']} @ {$phoneData['fps']}fps",
                'soc_max' => "{$socData['label']} @ {$socData['fps']}fps",
                'note' => "Framerate mismatch. The chipset's encoder lacks the throughput for {$phoneData['fps']}fps at this resolution. Performance will be capped at {$socData['fps']}fps.",
            ];
        } else {
            $this->results[] = $this->ok('video', 'Video Performance', "{$phoneData['label']} @ {$phoneData['fps']}fps", "{$socData['label']} @ {$socData['fps']}fps");
        }
    }

    // ─────────────────────────────────────────────
    // 5. RAM SPEED
    // ─────────────────────────────────────────────

    private function checkRamSpeed(): void
    {
        $phoneRam = $this->phoneSpecs['ram_type'] ?? null;
        $chipsetIds = $this->chipsetSpecs['memory_type'] ?? null; // Assuming this is the array of IDs

        if (!$phoneRam || empty($chipsetIds)) {
            $this->results[] = $this->unavailable('ram_type', 'Memory Type');
            return;
        }

        // Get the names from DB
        $supportedTypes = RamType::whereIn('id', $chipsetIds)->pluck('name')->toArray();

        // Use your existing ramRank to find the best supported by the SoC
        $chipsetMaxName = '';
        $chipsetMaxRank = -1;

        foreach ($supportedTypes as $type) {
            $rank = $this->ramRank($type);
            if ($rank > $chipsetMaxRank) {
                $chipsetMaxRank = $rank;
                $chipsetMaxName = $type;
            }
        }

        $phoneRank = $this->ramRank($phoneRam);

        if ($phoneRank > $chipsetMaxRank) {
            $this->results[] = [
                'type' => 'ram_type',
                'label' => 'Memory Type',
                'status' => 'warning',
                'phone_value' => strtoupper($phoneRam),
                'soc_max' => strtoupper($chipsetMaxName),
                'note' => "Hardware mismatch. The phone uses " . strtoupper($phoneRam) . ", but this SoC supports up to " . strtoupper($chipsetMaxName) . ". The RAM will be downclocked, reducing overall bandwidth.",
            ];
        } else {
            $this->results[] = $this->ok('ram_type', 'Memory Type', strtoupper($phoneRam), strtoupper($chipsetMaxName));
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

    private function checkScreenToBody(): void
    {

        $claimed = (float) ($this->phoneSpecs['screen_to_body_ratio'] ?? 0);
        $estimated = $this->calculateEstimatedRatio();
        if (!$claimed || !$estimated)
            return;
        $diff = $claimed - $estimated;
        $diff = $claimed - $estimated;

        if ($diff > 2.0) {
            // SCENARIO 1: Significant Discrepancy (Warning)
            $this->results[] = [
                'type' => 'screen_to_body_ratio',
                'label' => 'Screen-to-Body',
                'status' => 'warning',
                'phone_value' => "{$claimed}%",
                'soc_max' => "{$estimated}%",
                'note' => "Marketing gap detected. The brand claims {$claimed}%, but geometric calculation suggests {$estimated}%. This usually implies the manufacturer is excluding the outer frame or the 'black border' of the panel.",
            ];
        } else if (abs($diff) > 0.1) {
            // SCENARIO 2: Slight Difference (Informational/Ok)
            // We still show the estimated value so the user sees the audit was performed.
            $this->results[] = [
                'type' => 'screen_to_body_ratio',
                'label' => 'Screen-to-Body',
                'status' => 'warning',
                'phone_value' => "{$claimed}%",
                'soc_max' => "{$estimated}%",
                'note' => "Estimated: {$estimated}%. Minor variations are common due to corner rounding and bezel measurements.",
            ];
        } else {
            // Perfect match
            $this->results[] = $this->ok('screen_to_body_ratio', 'Screen-to-Body', "{$claimed}%", "{$estimated}%");
        }
    }

    private function checkUsbSpeed(): void
    {

        // Phone: "USB Type-C 2.0, OTG"
        $phoneUsb = $this->phoneSpecs['usb'] ?? null;
        // Chipset Native Support: e.g., "3.1" or "3.2"
        $socUsbMax = $this->chipsetSpecs['usb_version'] ?? null;
        if (!$phoneUsb || !$socUsbMax)
            return;

        // Extract version numbers (e.g., 2.0, 3.1, 3.2)
        preg_match('/(\d+\.\d+)/', $phoneUsb, $phoneMatch);
        preg_match('/(\d+\.\d+)/', $socUsbMax, $socMatch);

        $phoneVer = isset($phoneMatch[1]) ? (float) $phoneMatch[1] : 2.0;
        $socVer = isset($socMatch[1]) ? (float) $socMatch[1] : 3.0;

        if ($phoneVer < $socVer) {
            $this->results[] = [
                'type' => 'usb',
                'label' => 'Data Transfer',
                'status' => 'warning',
                'phone_value' => "USB {$phoneVer}",
                'soc_max' => "USB {$socVer}",
                'note' => "The SoC supports high-speed USB {$socVer}, but the manufacturer used a slower USB {$phoneVer} port. File transfers to PC will be significantly slower (capped at 480Mbps).",
            ];
        } else {
            $this->results[] = $this->ok('usb', 'Data Transfer', "USB {$phoneVer}", "USB {$socVer}");
        }
    }

    private function checkStorageSpeed(): void
    {
        $phoneUfs = $this->phoneSpecs['storage_type'] ?? null;
        $socUfsIds = $this->chipsetSpecs['storage_type'] ?? null; // Array of IDs

        if (!$phoneUfs || empty($socUfsIds))
            return;

        // 1. Resolve names from Database
        $supportedTypes = StorageType::whereIn('id', $socUfsIds)->pluck('name')->toArray();

        $chipsetMaxName = '';
        $chipsetMaxRank = -1;

        // 2. Find the highest rank supported by the SoC
        foreach ($supportedTypes as $type) {
            $rank = $this->storageRank($type);
            if ($rank > $chipsetMaxRank) {
                $chipsetMaxRank = $rank;
                $chipsetMaxName = $type;
            }
        }

        $phoneRank = $this->storageRank($phoneUfs);

        // FIX: Compare phoneRank against chipsetMaxRank (Integer vs Integer)
        if ($phoneRank > $chipsetMaxRank) {
            $this->results[] = [
                'type' => 'storage_type',
                'label' => 'Storage Speed',
                'status' => 'warning',
                'phone_value' => strtoupper($phoneUfs),
                'soc_max' => strtoupper($chipsetMaxName),
                'note' => "The storage module ($phoneUfs) exceeds the chipset's native controller ($chipsetMaxName). It will downclock to match the SoC's maximum bandwidth.",
            ];
        } else if ($phoneRank < $chipsetMaxRank) {
            $this->results[] = [
                'type' => 'storage_type',
                'label' => 'Storage Speed',
                'status' => 'warning',
                'phone_value' => strtoupper($phoneUfs),
                'soc_max' => strtoupper($chipsetMaxName),
                'note' => "Efficiency bottleneck. This SoC supports faster $chipsetMaxName storage, but the manufacturer used slower $phoneUfs memory. This will result in slower app installations and loading times.",
            ];
        } else {
            // Use the actual name for the OK status
            $this->results[] = $this->ok('storage_type', 'Storage Speed', strtoupper($phoneUfs), strtoupper($chipsetMaxName));
        }
    }

    private function storageRank(string $ufs): int
    {
        $ufs = strtolower($ufs);
        return match (true) {
            str_contains($ufs, '4.1') => 5,
            str_contains($ufs, '4.0') => 4,
            str_contains($ufs, '3.1') => 3,
            str_contains($ufs, '3.0') => 2,
            str_contains($ufs, '2.2') => 1,
            default => 0,
        };
    }

    private function calculateEstimatedRatio(): ?float
    {

        $dimensions = $this->phoneSpecs['dimensions'];
        $dimensions = strip_tags($dimensions);
        $dimensions = str_ireplace('mm', '', $dimensions);
        $parts = preg_split('/\s*[x×]\s*/i', trim($dimensions));
        $length = isset($parts[0]) ? (float) $parts[0] : 0;
        $width = isset($parts[1]) ? (float) $parts[1] : 0;
        $diag = (float) ($this->phoneSpecs['size'] ?? 0);
        $ratioStr = $this->phoneSpecs['aspect_ratio'] ?? '20:9';
        if (!$length || !$width || !$diag)
            return null;

        // 1. Get Width and Height parts (e.g., 20 and 9)
        $parts = explode(':', $ratioStr);
        $w_part = (float) ($parts[0] ?? 20);
        $h_part = (float) ($parts[1] ?? 9);

        // 2. Use Pythagorean theorem to find the area of the screen
        // screen_area = (diag^2 * w * h) / (w^2 + h^2)
        $screenAreaSqIn = ($diag ** 2 * $w_part * $h_part) / ($w_part ** 2 + $h_part ** 2);

        // 3. Convert square inches to square mm (1 inch = 25.4 mm)
        $screenAreaMm = $screenAreaSqIn * (25.4 ** 2);

        // 4. Calculate total front area of the phone
        $phoneAreaMm = $length * $width;

        return round(($screenAreaMm / $phoneAreaMm) * 100, 1);
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
    private function parseVideoSpec(string $spec): ?array
    {
        $spec = strtolower($spec);

        // Assign pixel-weight to resolutions
        $resMap = ['8k' => 8000, '4k' => 4000, '1080' => 1080, '720' => 720];
        $resLabel = '1080p';
        $resValue = 1080;

        foreach ($resMap as $key => $val) {
            if (str_contains($spec, $key)) {
                $resValue = $val;
                $resLabel = strtoupper($key);
                break;
            }
        }

        // Extract FPS using Regex (finds "60" in "4K@60fps" or "60 fps")
        preg_match('/(\d+)\s*fps/i', $spec, $matches);
        $fps = isset($matches[1]) ? (int) $matches[1] : 30; // Default to 30 if not found

        return [
            'res_value' => $resValue,
            'fps' => $fps,
            'label' => $resLabel
        ];
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

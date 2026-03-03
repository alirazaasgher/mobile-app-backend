<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chipset;
use App\Models\ChipsetsBrands;
use App\Models\ChipsetSpecification;
use App\Models\RamType;
use App\Models\StorageType;
use App\Services\PhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
class ChipsetController extends Controller
{
    protected $phoneService;
    public function __construct(PhoneService $phoneService)
    {
        $this->phoneService = $phoneService;
        $ram_type = RamType::pluck('name', 'id');
        $storage_type = StorageType::pluck('name', 'id');
        $brands = ChipsetsBrands::all();
        $specificationTemplates = [
            'benchmarks' => [
                'items' => [
                    // AnTuTu v11 (Latest Standard)
                    ['key' => 'antutu_score', 'label' => 'AnTuTu Score', 'type' => 'number', 'placeholder' => '3,500,000+'],

                    // Geekbench 6/7
                    ['key' => 'geekbench_single', 'label' => 'Geekbench Single-Core', 'type' => 'number', 'placeholder' => '3,200'],
                    ['key' => 'geekbench_multi', 'label' => 'Geekbench Multi-Core', 'type' => 'number', 'placeholder' => '10,500'],

                    // GPU & Gaming Specifics
                    ['key' => 'geekbench_gpu', 'label' => 'Geekbench GPU (OpenCL/Vulkan)', 'type' => 'number', 'placeholder' => '22,000'],
                    ['key' => 'ray_tracing_score', 'label' => '3DMark Solar Bay (Ray Tracing)', 'type' => 'number', 'placeholder' => '12,000'],

                    // Stability & Efficiency
                    ['key' => 'stability_score', 'label' => '3DMark Wildlife Stability', 'type' => 'text', 'placeholder' => '95%'],
                ]
            ],
            'cpu' => [
                'items' => [
                    // Identification
                    ['key' => 'cpu_name', 'label' => 'CPU Brand', 'type' => 'text', 'placeholder' => 'Kryo (Snapdragon 8 Gen 3)'],
                    ['key' => 'cpu_speed', 'label' => 'CPU Speed', 'type' => 'textarea', 'placeholder' => 'Kryo (Snapdragon 8 Gen 3)'],

                    // Manufacturing (The "Process" part)
                    ['key' => 'process', 'label' => 'Process Node', 'type' => 'text', 'placeholder' => '4nm'],
                    [
                        'key' => 'manufacturing',
                        'label' => 'Manufacturing',
                        'type' => 'select', // single-select dropdown
                        'options' => [
                            'TSMC' => 'TSMC',
                            'Samsung' => 'Samsung',
                            'Intel' => 'Intel',
                            'GlobalFoundries' => 'GlobalFoundries',
                            'UMC' => 'UMC',
                            'SMIC' => 'SMIC',
                            'Other' => 'Other'
                        ],
                        'placeholder' => 'Select Manufacturer'
                    ],
                    // Architecture
                    ['key' => 'architecture', 'label' => 'Architecture', 'type' => 'text', 'placeholder' => '64-bit'],
                    // Core Specs
                    ['key' => 'cores', 'label' => 'Total Cores', 'type' => 'text', 'placeholder' => '8 (Octa-core)'],
                    ['key' => 'frequency', 'label' => 'Max Frequency', 'type' => 'text', 'placeholder' => '3300 MHz'],

                    // Memory/Cache
                    ['key' => 'l2_cache', 'label' => 'L2 Cache', 'type' => 'text', 'placeholder' => '2 MB'],
                    ['key' => 'l3_cache', 'label' => 'L3 Cache', 'type' => 'text', 'placeholder' => '8 MB'],
                ]
            ],
            'gpu' => [
                'items' => [
                    // Core Identity
                    ['key' => 'gpu_name', 'label' => 'GPU Name', 'type' => 'text', 'placeholder' => 'Adreno 722'],
                    ['key' => 'frequency', 'label' => 'GPU Frequency', 'type' => 'text', 'placeholder' => '1150 MHz'],
                    // Software APIs
                    ['key' => 'vulkan_version', 'label' => 'Vulkan Version', 'type' => 'text', 'placeholder' => '1.3'],
                    ['key' => 'opencl_version', 'label' => 'OpenCL Version', 'type' => 'text', 'placeholder' => '2.0'],
                    ['key' => 'directx_version', 'label' => 'DirectX Version', 'type' => 'text', 'placeholder' => '12.1'],
                ],

            ],
            'memory_&_storage' => [
                'items' => [
                    ['key' => 'memory_type', 'label' => 'Memory Type', 'type' => 'multiselect', 'options' => $ram_type],
                    ['key' => 'frequency', 'label' => 'Memory Frequency', 'type' => 'text', 'placeholder' => '4200 MHz'],
                    ['key' => 'bandwidth', 'label' => 'Max Bandwidth', 'type' => 'text', 'placeholder' => '33.6 Gb/s'],
                    ['key' => 'max_size', 'label' => 'Max Capacity', 'type' => 'text', 'placeholder' => '16 GB'],
                    // Storage Specs
                    [
                        'key' => 'storage_type',
                        'label' => 'Storage Type',
                        'type' => 'multiselect',
                        'options' => $storage_type
                    ],
                ]
            ],
            'ai_accelerator' => [
                'items' => [
                    // The Identity of the NPU
                    ['key' => 'npu_name', 'label' => 'AI Processor (NPU)', 'type' => 'text', 'placeholder' => 'Hexagon / Neural Engine / NPU 3.0'],

                    // Performance (The "Engine" size)
                    ['key' => 'tops_int8', 'label' => 'Performance (INT8)', 'type' => 'text', 'placeholder' => '45 TOPS'],
                    ['key' => 'tflops_fp16', 'label' => 'Performance (FP16)', 'type' => 'text', 'placeholder' => '18 TFLOPS'],

                    // Generative AI Metrics (Crucial for 2026)
                    ['key' => 'llm_tokens', 'label' => 'On-Device LLM Speed', 'type' => 'text', 'placeholder' => '20 tokens/sec'],
                    ['key' => 'model_support', 'label' => 'Supported Models', 'type' => 'text', 'placeholder' => 'Gemini Nano, Llama 3 (7B), Stable Diffusion'],

                    // Architecture & Precision
                    ['key' => 'precision_support', 'label' => 'Precision Support', 'type' => 'text', 'placeholder' => 'INT4, INT8, FP16, BF16'],
                    ['key' => 'memory_bandwidth_ai', 'label' => 'Dedicated AI Bandwidth', 'type' => 'text', 'placeholder' => '32 GB/s'],

                    // Use Cases
                    ['key' => 'ai_tasks', 'label' => 'Hardware-Accelerated Tasks', 'type' => 'text', 'placeholder' => 'Live Translation, Generative Fill, Voice Isolation'],
                ]
            ],
            'multimedia' => [
                'items' => [

                    // Display Specs
                    [
                        'key' => 'max_display_res',
                        'label' => 'Max Display Resolution',
                        'type' => 'select',
                        'options' => [
                            // 720p Tier (Budget / Entry-level)
                            'HD' => '1280 x 720 (HD)',
                            'HD+' => '1600 x 720 (HD+)',           // Common 20:9 budget standard

                            // 1080p+ Tier (Mainstream & Ultra-Wide)
                            'FHD' => '1920 x 1080 (FHD)',
                            'FHD+' => '2400 x 1080 (FHD+)',        // Standard 20:9 (e.g., Redmi, base Samsung)
                            'WFHD' => '2560 x 1080 (WFHD)',        // Standard 21:9
                            'WFHD+' => '2900 x 1300 (WFHD+)',      // New Snapdragon 7s Gen 4 Standard
                            'FHD+ Ex' => '2800 x 1260 (FHD+ Max)', // Used in high-end mid-range (Nothing Phone 4a)

                            // 1.5K Tier (Premium Mid-range / Upper Tier)
                            '1.5K' => '2712 x 1220 (1.5K)',        // Popularized by Xiaomi/Redmi
                            '1.5K+' => '3000 x 1200 (1.5K+)',      // High-end foldables/tablets
                            'Fold' => '2208 x 1768 (Foldable)',    // Inner Display Standard (Z Fold series)

                            // 1440p Tier (Flagship)
                            'QHD' => '2560 x 1440 (QHD)',
                            'QHD+' => '3200 x 1440 (QHD+)',        // Flagship 20:9 (Galaxy S Ultra, Pixel Pro)
                            'WQHD+' => '2960 x 1440 (WQHD+)',      // MediaTek Dimensity 8450 Max Resolution

                            // 2160p Tier (Elite)
                            '4K' => '3840 x 2160 (4K UHD)',
                            'Custom' => 'Other / Custom'
                        ],
                        'placeholder' => 'Select Resolution'
                    ],
                    ['key' => 'max_external_resolution ', 'label' => 'Maximum External Display Resolution', 'type' => 'text', 'placeholder' => '4K Ultra HD @ 60 Hz'],
                    ['key' => 'max_refresh_rate', 'label' => 'Max Refresh Rate', 'type' => 'text', 'placeholder' => '144Hz'],
                    ['key' => 'hdr_standards', 'label' => 'HDR Support', 'type' => 'text', 'placeholder' => 'HDR10+, Dolby Vision'],
                    ['key' => 'dual_screen_support', 'label' => 'Dual Screen support', 'type' => 'select', 'options' => ['Yes' => 'Yes', 'No' => 'No']],

                    // Camera (The ISP Power)
                    ['key' => 'isp_name', 'label' => 'ISP Model', 'type' => 'text', 'placeholder' => 'Spectra / Cognitive ISP'],
                    ['key' => 'max_camera_res', 'label' => 'Max Camera Resolution', 'type' => 'text', 'placeholder' => '1x 200MP or 3x 32MP'],
                    ['key' => 'max_dual_camera_res', 'label' => 'Max Dual Camera', 'type' => 'text', 'placeholder' => '32+21MP'],
                    ['key' => 'max_triple_camera_mp', 'label' => 'Max Triple Camera', 'type' => 'text', 'placeholder' => '21MP'],
                    ['key' => 'video_capture', 'label' => 'Video Capture', 'type' => 'text', 'placeholder' => '8K @ 30FPS, 4K @ 120FPS'],
                    ['key' => 'video_playback', 'label' => 'Video Playback', 'type' => 'text', 'placeholder' => '8K @ 60FPS'],
                    ['key' => 'max_slowmo_resolution ', 'label' => 'Slow Motion', 'type' => 'text', 'placeholder' => '1080p @ 120 FPS'],
                    ['key' => 'max_color_depth', 'label' => 'Color Depth', 'type' => 'text', 'placeholder' => '10-bit'],

                    // Audio & Features
                    ['key' => 'audio_features', 'label' => 'Audio Features', 'type' => 'text', 'placeholder' => 'Spatial Audio, Lossless Support'],
                    ['key' => 'video_codecs', 'label' => 'Video Codecs', 'type' => 'text', 'placeholder' => 'AV1 (Decode/Encode), HEVC, VP9'],
                    ['key' => 'audio_codecs', 'label' => 'Audio Codecs', 'type' => 'text', 'placeholder' => 'AAC, AIFF, MP3, WAV, LDAC'],
                ]
            ],
            'connectivity' => [
                'items' => [
                    // Cellular / Modem
                    ['key' => 'usb_version', 'label' => 'USB Version', 'type' => 'text', 'placeholder' => 'USB 3.2 Gen 2x2'],
                    ['key' => 'usb_type', 'label' => 'USB Type', 'type' => 'text', 'placeholder' => 'Type C'],
                    ['key' => 'peak_speed', 'label' => 'Peak Download Speed', 'type' => 'text', 'placeholder' => '10 Gbps'],
                    ['key' => 'download_speed', 'label' => 'Max Download Speed', 'type' => 'text', 'placeholder' => '10 Gbps'],
                    ['key' => 'peak_upload_speed', 'label' => 'Peak Upload Speed', 'type' => 'text', 'placeholder' => '3.5 Gbps'],
                    ['key' => 'upload_speed', 'label' => 'Max Upload Speed', 'type' => 'text', 'placeholder' => '3.5 Gbps'],
                    // Wi-Fi
                    [
                        'key' => 'wifi_standard',
                        'label' => 'Wi-Fi Standard',
                        'type' => 'multiselect',
                        'options' => [
                            'Wi-Fi 4' => 'Wi-Fi 4 (802.11n)',
                            'Wi-Fi 5' => 'Wi-Fi 5 (802.11ac)',
                            'Wi-Fi 6' => 'Wi-Fi 6 (802.11ax)',
                            'Wi-Fi 6E' => 'Wi-Fi 6E (802.11ax 6GHz)'
                        ]
                    ],
                    [
                        'key' => 'wifi_bands',
                        'label' => 'Wi-Fi Bands',
                        'type' => 'multiselect',
                        'options' => [
                            '2.4GHz' => '2.4 GHz',
                            '5GHz' => '5 GHz',
                            '6GHz' => '6 GHz',
                            '60GHz' => '60 GHz (WiGig)'
                        ]
                    ],
                    // Bluetooth & Location
                    ['key' => 'bluetooth_version', 'label' => 'Bluetooth Version', 'type' => 'text', 'placeholder' => '5.4'],
                    ['key' => 'navigation', 'label' => 'Navigation', 'type' => 'text', 'placeholder' => 'GPS, GLONASS, Beidou, Galileo, QZSS, NavIC'],
                ]
            ],

        ];

        View::share([
            'ramTypes' => $ram_type,
            'storageTypes' => $storage_type,
            'brands' => $brands,
            'specificationTemplates' => $specificationTemplates
        ]);
    }

    public function index()
    {
        $chipsets = Chipset::with('brand:id,name') // Only fetch id and name from brands
            ->select('id', 'name', 'brand_id', 'created_at', 'release_date')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        echo "<pre>";
        print_r($chipsets);
        exit;
        return view('admin.chipset.index', compact('chipsets'));
    }

    public function create()
    {
        return view("admin.chipset.create");
    }

    public function store(Request $request)
    {
        // 1. Validate the data
        // Use dot notation for nested JSON fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string',
            'release_date' => 'required|date',
            'tier' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $brandName = ChipsetsBrands::find($validated['brand_id'])->name ?? 'unknown';
            $chipset = Chipset::create([
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'release_date' => $validated['release_date'] ?? null,
                'tier' => $validated['tier'],
            ]);
            DB::commit();
            return redirect()->route('admin.chipsets.create')->with('success', 'SoC added successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Phone store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            echo "<pre>";
            print_r($e->getMessage());
            exit;
            return back()->withInput()->withErrors(['error' => 'Failed to save phone.']);
        }


    }

    public function edit($id)
    {
        $chipset = Chipset::with('specifications')->findOrFail($id);
        $chipset->specifications = collect($chipset->specifications)->keyBy('category');
        // echo "<pre>";
        // print_r($chipset->toArray());
        // exit;
        return view('admin.chipset.create', compact('chipset'));
    }

    public function update(Request $request, $id)
    {
        // echo "<pre>";
        // print_r($request->all());
        // exit;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string',
            'release_date' => 'required|date',
            'tier' => 'nullable|string'
        ]);

        $chipset = Chipset::with('brand')->findOrFail($id);
        $specs = $request->input('specifications');
        DB::beginTransaction();
        $updateData = [];
        try {
            $brandName = ChipsetsBrands::find($validated['brand_id'])->name ?? 'unknown';
            $brandSlug = Str::slug($chipset->brand->name);
            $chipsetSlug = Str::slug($chipset->name);
            $basePath = "{$brandSlug}/{$chipsetSlug}";
            $updateData = [
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'release_date' => $validated['release_date'] ?? null,
                'tier' => $validated['tier'],
            ];

            // 2. Handle the image upload and add to the array if it exists
            if ($request->hasFile('primary_image')) {
                $updatedPath = $this->phoneService->uploadSingleImage(
                    $request->file('primary_image'),
                    "{$basePath}/primary_images",
                    $chipset->primary_image
                );

                // Add the key to the existing array instead of overwriting it
                $updateData['primary_image'] = $updatedPath;
            }
            $chipset->update($updateData);
            foreach ($specs as $category => $categorySpecs) {
                ChipsetSpecification::updateOrCreate(
                    [
                        'chipset_id' => $chipset->id,
                        'category' => $category
                    ],
                    [
                        'specifications' => json_encode($categorySpecs)
                    ]
                );
            }
            DB::commit();
            $message = 'Chipset updated successfully!';
            return redirect()->route('admin.chipsets.edit', $chipset->id)->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Phone update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            dd($e->getMessage(), $e->getLine(), $e->getFile());
            return back()->withInput()->withErrors(['error' => 'Failed to update chipset.']);
        }

    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Phone;
use App\Models\RamType;
use App\Models\StorageType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use App\Services\PhoneService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MobileController extends Controller
{
    protected $phoneService;
    public function __construct(PhoneService $phoneService)
    {
        $this->phoneService = $phoneService;
        $brands = Brand::all();
        $ram_type = RamType::all();
        $storage_type = StorageType::all();
        $allMobiles = Phone::all();
        $specificationTemplates = [
            'build' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'dimensions', 'label' => 'Dimensions', 'type' => 'textarea', 'placeholder' => '162.8 x 77.6 x 8.2 mm'],
                    ['key' => 'weight', 'label' => 'Weight', 'type' => 'text', 'placeholder' => '169 g'],
                    ['key' => 'build', 'label' => 'Build Material', 'type' => 'text', 'placeholder' => 'Glass front (Gorilla Glass), glass back, aluminum frame'],
                    ['key' => 'durability', 'label' => 'Durability (IP rating)', 'type' => 'text', 'placeholder' => 'IP68 dust/water resistant, MIL-STD-810H certified'],
                    ['key' => 'sim', 'label' => 'SIM Support', 'type' => 'textarea', 'placeholder' => 'Dual SIM (Nano-SIM, eSIM, dual stand-by)'],
                ]
            ],

            'display' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'placeholder' => 'AMOLED, HDR10+'],
                    ['key' => 'size', 'label' => 'Screen Size', 'type' => 'text', 'placeholder' => '6.83 inches'],
                    ['key' => 'resolution', 'label' => 'Resolution', 'type' => 'text', 'placeholder' => '2772 x 1280 (~447ppi)'],
                    ['key' => 'aspect_ratio', 'label' => 'Aspect Ratio', 'type' => 'text', 'placeholder' => '19.5:9'],
                    ['key' => 'refresh_rate', 'label' => 'Refresh Rate', 'type' => 'text', 'placeholder' => '120Hz'],
                    ['key' => 'pwm_frequency', 'label' => 'PWM Frequency', 'type' => 'text', 'placeholder' => '2160Hz'],
                    ['key' => 'brightness', 'label' => 'Brightness', 'type' => 'text', 'placeholder' => 'Peak 3200 nits'],
                    ['key' => 'protection', 'label' => 'Protection', 'type' => 'text', 'placeholder' => 'Gorilla Glass 7i'],
                    ['key' => 'touch_sampling_rate', 'label' => 'Touch Sampling Rate', 'type' => 'text', 'placeholder' => '480Hz'],
                    ['key' => 'features', 'label' => 'Display Features', 'type' => 'text', 'placeholder' => 'Always-on display, Dolby Vision, HDR10+'],
                ]
            ],


            'performance' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'os', 'label' => 'Operating System', 'type' => 'text', 'placeholder' => 'Android 14, One UI 6.1'],
                    ['key' => 'chipset', 'label' => 'Chipset', 'type' => 'text', 'placeholder' => 'Qualcomm Snapdragon 8 Gen 3 (4 nm)'],
                    ['key' => 'cpu', 'label' => 'CPU', 'type' => 'text', 'placeholder' => 'Octa-core (1x3.3 GHz X4)'],
                    ['key' => 'gpu', 'label' => 'GPU', 'type' => 'text', 'placeholder' => 'Adreno 750'],
                    ['key' => 'cpu_architecture', 'label' => 'CPU Architecture', 'type' => 'text', 'placeholder' => '64-bit, ARMv9'],
                    ['key' => 'cooling', 'label' => 'Cooling System', 'type' => 'text', 'placeholder' => 'Vapor chamber, AI thermal control'],
                    ['key' => 'benchmark', 'label' => 'Benchmark Scores', 'type' => 'text', 'placeholder' => 'AnTuTu: 1,250,000 / Geekbench: 2200 (S) • 7200 (M)'],
                ]
            ],

            'main_camera' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'setup', 'label' => 'Setup', 'type' => 'text', 'placeholder' => 'Triple (50 MP + 10 MP + 12 MP)'],
                    ['key' => 'main_sensor', 'label' => 'Main Sensor', 'type' => 'text', 'placeholder' => '50 MP, f/1.8, (wide), OIS, PDAF'],
                    ['key' => 'other_sensors', 'label' => 'Other Sensors', 'type' => 'textarea', 'placeholder' => '10 MP (telephoto 3x), 12 MP (ultrawide 120°)'],
                    ['key' => 'features', 'label' => 'Features', 'type' => 'text', 'placeholder' => 'LED flash, HDR, panorama'],
                    ['key' => 'video', 'label' => 'Video', 'type' => 'text', 'placeholder' => '8K@30fps, 4K@60fps, 1080p@240fps'],
                ]
            ],

            'selfie_camera' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'setup', 'label' => 'Setup', 'type' => 'text', 'placeholder' => 'Single (12 MP)'],
                    ['key' => 'sensor', 'label' => 'Sensor Details', 'type' => 'text', 'placeholder' => '12 MP, f/2.2 (wide), Dual Pixel PDAF'],
                    ['key' => 'features', 'label' => 'Features', 'type' => 'text', 'placeholder' => 'HDR, Portrait Mode, Night Selfie'],
                    ['key' => 'video', 'label' => 'Video', 'type' => 'text', 'placeholder' => '4K@60fps, 1080p@30fps'],
                ]
            ],

            'battery' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'placeholder' => 'Li-Ion / Li-Po, non-removable'],
                    ['key' => 'capacity', 'label' => 'Capacity', 'type' => 'text', 'placeholder' => '5000 mAh'],
                    ['key' => 'charging_speed', 'label' => 'Charging Speed', 'type' => 'text', 'placeholder' => '45W wired (50% in 20 min)'],
                    ['key' => 'wireless', 'label' => 'Wireless Charging', 'type' => 'text', 'placeholder' => '15W wireless (Qi/PMA)'],
                    ['key' => 'reverse', 'label' => 'Reverse Charging', 'type' => 'text', 'placeholder' => '4.5W reverse wireless'],
                    ['key' => 'endurance', 'label' => 'Endurance Rating', 'type' => 'text', 'placeholder' => '120 hours (estimated)'],
                ]
            ],

            'network' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'technology', 'label' => 'Technology', 'type' => 'text', 'placeholder' => 'GSM / HSPA / LTE / 5G'],
                    ['key' => 'supported_bands', 'label' => 'Supported Bands', 'type' => 'textarea', 'placeholder' => '2G: GSM 850/900/1800/1900, 3G: WCDMA 1/2/5/8'],
                    ['key' => 'speed', 'label' => 'Speed', 'type' => 'text', 'placeholder' => 'HSPA, LTE-A, 5G (SA/NSA)'],
                ]
            ],

            'connectivity' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'wifi', 'label' => 'Wi-Fi', 'type' => 'text', 'placeholder' => 'Wi-Fi 6E (802.11 a/b/g/n/ac/6e), dual-band'],
                    ['key' => 'bluetooth', 'label' => 'Bluetooth', 'type' => 'text', 'placeholder' => '5.3, A2DP, LE'],
                    ['key' => 'positioning', 'label' => 'Positioning', 'type' => 'text', 'placeholder' => 'GPS, GLONASS, GALILEO, BDS, QZSS'],
                    ['key' => 'nfc', 'label' => 'NFC', 'type' => 'select', 'options' => ['Yes', 'No']],
                    ['key' => 'infrared', 'label' => 'Infrared Port', 'type' => 'select', 'options' => ['Yes', 'No']],
                    ['key' => 'usb', 'label' => 'USB Type', 'type' => 'text', 'placeholder' => 'USB Type-C 3.2, OTG, DisplayPort'],
                ]
            ],

            'audio' => [
                'expandable' => false,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'stereo', 'label' => 'Stereo Speakers', 'type' => 'select', 'options' => ['Yes', 'No']],
                    ['key' => '3.5mm_jack', 'label' => '3.5mm Jack', 'type' => 'select', 'options' => ['Yes', 'No']],
                    ['key' => 'quality', 'label' => 'Audio Quality', 'type' => 'text', 'placeholder' => '32-bit/384kHz, Dolby Atmos'],
                    ['key' => 'features', 'label' => 'Features', 'type' => 'text', 'placeholder' => 'Hi-Res Audio, AKG tuning, Noise cancellation'],
                ]
            ],

            'security' => [
                'expandable' => true,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'fingerprint', 'label' => 'Fingerprint Sensor', 'type' => 'text', 'placeholder' => 'Under display, ultrasonic'],
                    ['key' => 'face_unlock', 'label' => 'Face Unlock', 'type' => 'text', 'placeholder' => '2D / 3D facial recognition'],
                    ['key' => 'other_security_features', 'label' => 'Other Security', 'type' => 'text', 'placeholder' => 'Knox, Secure Folder, Privacy Dashboard'],
                    ['key' => 'software_updates', 'label' => 'Software & Updates', 'type' => 'text', 'placeholder' => 'Android 14, 4 years major + 5 years security updates'],
                ]
            ],

            'Sensors' => [
                'expandable' => false,
                'max_visible' => 4,
                'items' => [
                    ['key' => 'sensors', 'label' => 'Available Sensors', 'type' => 'text', 'placeholder' => 'Fingerprint, accelerometer, gyro, proximity, compass, barometer'],
                ]
            ],
        ];

        View::share([
            'brands' => $brands,
            'ramTypes' => $ram_type,
            'storageTypes' => $storage_type,
            'specificationTemplates' => $specificationTemplates,
            'allMobiles' => $allMobiles
        ]);
    }

    // List all mobiles
    public function index()
    {
        $mobiles = Phone::orderBy('created_at', 'desc')->get();
        return view('admin.mobiles.index', compact('mobiles'));
    }

    // Show create form
    public function create()
    {
        return view('admin.mobiles.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string',
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string',
            'release_date' => 'nullable|date',
            'variants' => 'required|array|min:1',
            'specifications' => 'required|array|min:1',
            'status' => 'nullable|string',
            'competitors' => 'nullable|array'
        ]);
        $deleted = $request->input('action') === 'draft' ? 1 : 0;
        $storage_type = $request->input('storage_type');
        $ram_type = $request->input('ram_type');
        $sd_card = $request->input('sd_card');
        DB::beginTransaction();
        try {
            // primary image
            $primaryPath = $this->phoneService->handlePrimaryImage($request->file('primary_image'));

            // create phone
            $phone = Phone::create([
                'brand_id' => $validated['brand'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'tagline' => $validated['tagline'] ?? null,
                'primary_image' => $primaryPath,
                'release_date' => $validated['release_date'] ?? null,
                'announced_date' => $request->input('announced_date'),
                'status' => $request->input('status'),
                'deleted' => $deleted
            ]);
            $competitors = $validated['competitors'] ?? [];
            $phone->competitors()->sync($competitors);
            // variants
            $variantsSpecs = $validated['variants']['specs'] ?? [];
            $priceModifiersUSD = $validated['variants']['price_modifier_usd'] ?? [];
            $priceModifiersPKR = $validated['variants']['price_modifier_pkr'] ?? [];
            // $ramType = $validated['variants']['ram_type'] ?? null;
            // $storageType = $validated['variants']['storage_type'] ?? null;
            [$ram_list, $storage_list, $price_list] = $this->phoneService->syncVariants($phone, $variantsSpecs, $priceModifiersPKR, $priceModifiersUSD);

            // colors & images
            $variantsColors = $validated['variants']['colors'] ?? [];
            $color_names = $validated['variants']['color_names'] ?? [];
            $color_hex = $validated['variants']['color_hex'] ?? [];
            $color_images = $validated['variants']['color_image'] ?? [];
            $available_colors = $this->phoneService->syncColorsAndImages(
                $phone,
                $variantsColors,
                $color_names,
                $color_hex,
                $color_images,
                $validated['variants']['delete_images'] ?? []
            );

            // memory spec
            $memorySpec = $this->phoneService->buildMemorySpec(
                // $ram_list,
                // $storage_list,
                // $storage_type,
                $ram_type,
                $sd_card == "1" ? "YES" : "NO"
            );

            // merge memory into specifications (insert after 'performance')
            $specs = $validated['specifications'];
            $updatedSpecs = [];
            foreach ($specs as $key => $value) {
                $updatedSpecs[$key] = $value;
                if ($key === 'performance') {
                    $updatedSpecs['memory'] = $memorySpec;
                }
            }



            $this->phoneService->saveSpecifications($phone, $updatedSpecs, function ($category) use ($request) {
                return $request->input("searchable_text-$category");
            });


            // search index
            update_phone_search_index($storage_type, $ram_type, $sd_card, $ram_list, $storage_list, $price_list, $available_colors, $updatedSpecs, $validated, $phone->id);

            DB::commit();
            $message = $deleted === 'draft' ? 'Phone saved as draft!' : 'Phone published successfully!';
            return redirect()->route('mobiles.create')->with('success', $message);
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
        $mobile = Phone::with([
            'competitors',
            'variants',
            'colors',
            'specifications',
            'searchIndex' => function ($q) {
                $q->select('phone_id', 'storage_type', 'ram_type', 'sd_card'); // only required columns
            }
        ])->findOrFail($id);
        // echo "<pre>";
        // print_r($mobile->toArray());
        // exit;
        $mobile->specifications = collect($mobile->specifications)->keyBy('category');
        $existingCompetitors = $mobile->competitors->pluck('id')->toArray();
        return view('admin.mobiles.create', compact('mobile', 'existingCompetitors'));
    }

    public function update(Request $request, $id)
    {

        // echo "<pre>";
        // print_r($request->all());
        // exit;
        $validated = $request->validate([
            'brand' => 'required|string',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:50',
            'tagline' => 'nullable|string',
            'release_date' => 'nullable|date',
            'variants' => 'required|array|min:1',
            'specifications' => 'required|array|min:1',
            'status' => 'nullable|string',
            'competitors' => 'nullable|array'
        ]);
        $phone = Phone::findOrFail($id);
        $deleted = $request->input('action') === 'draft' ? 1 : 0;
        $storage_type = $request->input('storage_type');
        $ram_type = $request->input('ram_type');
        $sd_card = $request->input('sd_card');
        DB::beginTransaction();
        try {
            $primaryPath = $this->phoneService->handlePrimaryImage($request->file('primary_image'));
            $updateData = [
                'brand_id' => $validated['brand'],
                'tagline' => $validated['tagline'] ?? null,
                'release_date' => $validated['release_date'] ?? null,
                'announced_date' => $request->input('announced_date') ?? null,
                'status' => $request->input('status'),
                'deleted' => $deleted
            ];

            if ($primaryPath) {
                $updateData['primary_image'] = $primaryPath;
            }

            $phone->update($updateData);
            $competitors = $validated['competitors'] ?? [];
            $phone->competitors()->sync($competitors);
            // variants - smart sync (diff)
            $variantsSpecs = $validated['variants']['specs'] ?? [];
            $priceModifiersUSD = $validated['variants']['price_modifier_usd'] ?? null;
            $priceModifiersPKR = $validated['variants']['price_modifier_pkr'] ?? null;
            // $ramType = $validated['variants']['ram_type'] ?? null;
            // $storageType = $validated['variants']['storage_type'] ?? null;
            [$ram_list, $storage_list, $price_list] = $this->phoneService->syncVariants($phone, $variantsSpecs, $priceModifiersPKR, $priceModifiersUSD);

            // colors & images (preserve old unless deleted)
            $variantsColors = $validated['variants']['colors'] ?? [];
            $color_names = $validated['variants']['color_names'] ?? [];
            $color_hex = $validated['variants']['color_hex'] ?? [];
            $color_images = $validated['variants']['color_image'] ?? [];
            $available_colors = $this->phoneService->syncColorsAndImages(
                $phone,
                $variantsColors,
                $color_names,
                $color_hex,
                $color_images,
                $validated['variants']['delete_images'] ?? []
            );

            // memory spec injected after performance
            $memorySpec = $this->phoneService->buildMemorySpec(

                $ram_type,
                $sd_card == "1" ? "YES" : "NO"
            );



            $specs = $validated['specifications'];
            $updatedSpecs = [];
            foreach ($specs as $key => $value) {
                $updatedSpecs[$key] = $value;
                if ($key === 'performance') {
                    $updatedSpecs['memory'] = $memorySpec;
                }
            }

            $this->phoneService->saveSpecifications($phone, $updatedSpecs, function ($category) use ($request) {
                return $request->input("searchable_text-$category");
            }, true);


            update_phone_search_index($storage_type, $ram_type, $sd_card, $ram_list, $storage_list, $price_list, $available_colors, $updatedSpecs, $validated, $id);
            // search index (if published)

            DB::commit();

            $message = $deleted === 'draft' ? 'Phone saved as draft!' : 'Phone updated successfully!';
            return redirect()->route('mobiles.edit', $phone->id)->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Phone update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            dd($e->getMessage(), $e->getLine(), $e->getFile());
            return back()->withInput()->withErrors(['error' => 'Failed to update phone.']);
        }
    }
}

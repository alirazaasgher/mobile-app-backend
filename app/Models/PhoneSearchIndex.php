<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneSearchIndex extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'phone_id';

    protected $fillable = [
        'phone_id', 'brand', 'model', 'name', 'main_image',
        'min_storage_gb', 'max_storage_gb',
        'screen_size_inches', 'battery_capacity_mah', 'selfie_camera_mp',
        'os', 'chipset', 'has_4g', 'has_nfc', 'has_wireless_charging',
        'has_fast_charging', 'has_fingerprint', 'has_face_unlock', 'has_dual_sim',
        'has_expandable_storage', 'has_headphone_jack', 'has_stereo_speakers',
        'refresh_rate_max', 'display_type', 'has_ultrawide_camera', 'has_telephoto_camera',
        'has_macro_camera', 'video_recording_max', 'build_material', 'ip_rating',
        'weight_grams', 'color_count', 'storage_option_count', 'is_available',
        'popularity_score', 'avg_rating', 'total_reviews', 'search_content','storage_type','ran_type','sd_card'
    ];

    protected $casts = [
        'min_storage_gb' => 'integer',
        'max_storage_gb' => 'integer',
        'screen_size_inches' => 'decimal:1',
        'battery_capacity_mah' => 'integer',
        'selfie_camera_mp' => 'integer',
        'refresh_rate_max' => 'integer',
        'weight_grams' => 'integer',
        'color_count' => 'integer',
        'storage_option_count' => 'integer',
        'popularity_score' => 'integer',
        'avg_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        // Boolean casts
        'has_4g' => 'boolean',
        'has_nfc' => 'boolean',
        'has_wireless_charging' => 'boolean',
        'has_fast_charging' => 'boolean',
        'has_fingerprint' => 'boolean',
        'has_face_unlock' => 'boolean',
        'has_dual_sim' => 'boolean',
        'has_expandable_storage' => 'boolean',
        'has_headphone_jack' => 'boolean',
        'has_stereo_speakers' => 'boolean',
        'has_ultrawide_camera' => 'boolean',
        'has_telephoto_camera' => 'boolean',
        'has_macro_camera' => 'boolean',
        'is_available' => 'boolean',
        'available_colors' => 'array',
        'tags' => 'array',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
    }
}
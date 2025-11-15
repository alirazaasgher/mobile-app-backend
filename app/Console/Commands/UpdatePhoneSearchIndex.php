<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phone;
use App\Models\PhoneSearchIndex;

class UpdatePhoneSearchIndex extends Command
{
    protected $signature = 'phones:update-search-index {--phone_id=}';
    protected $description = 'Update or create phone search index for phones';

    public function handle()
    {
        $phoneId = $this->option('phone_id');

        if ($phoneId) {
            $phones = Phone::with(['variants'])->where('id', $phoneId)->get();
        } else {
            $phones = Phone::with(['variants'])->get();
        }

        foreach ($phones as $phone) {
            $this->updateSearchIndex($phone);
        }

        $this->info('Phone search index updated successfully.');
    }

    protected function updateSearchIndex(Phone $phone)
    {
        $variants = $phone->variants;

        if ($variants->isEmpty()) {
            return;
        }

        $minPrice = $variants->min('price');
        $maxPrice = $variants->max('price');
        $ramGb    = optional($phone->specifications()->where('category', 'memory')->first())->specifications['ram_gb'] ?? null;
        $screen   = optional($phone->specifications()->where('category', 'display')->first())->specifications['size_inches'] ?? null;
        $mainCam  = optional($phone->specifications()->where('category', 'main_camera')->first())->specifications['megapixels'] ?? null;
        $has5g    = $phone->specifications()->where('category', 'network')->whereJsonContains('specifications->technology', '5G')->exists();

        PhoneSearchIndex::updateOrCreate(
            ['phone_id' => $phone->id],
            [
                'min_price'          => $minPrice,
                'max_price'          => $maxPrice,
                'ram_gb'             => $ramGb,
                'screen_size_inches' => $screen,
                'main_camera_mp'     => $mainCam,
                'has_5g'             => $has5g,
            ]
        );
    }
}

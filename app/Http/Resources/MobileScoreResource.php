<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MobileScoreResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'score' => $this->score,
        ];
    }
}

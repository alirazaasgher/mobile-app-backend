<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneSpecificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            $this->category => json_decode($this->specifications),
            'is_expandable' => $this->expandable,
            'max_visible' => $this->max_visible
            // 'searchable_text' => $this->searchable_text,
        ];
    }
}
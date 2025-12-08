<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetitorResource extends JsonResource
{
    public function toArray($request)
    {
        $request->merge(['from_competitor' => true]);
        $baseUrl = config('app.url');
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'primary_image' => $this->primary_image ? $baseUrl . '/storage/' . ltrim($this->primary_image, '/')
                : null,
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex'),hideDetails: true),    
        ];
    }
}
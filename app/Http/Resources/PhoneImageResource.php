<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneImageResource extends JsonResource
{
  public function toArray($request)
{
    $baseUrl = config('app.url'); // or env('APP_URL')
    
    return [
        'id' => $this->id,
        'url' => $this->image_url 
            ? $baseUrl . '/storage/' . ltrim($this->image_url, '/')
            : null,
        'sort' => $this->sort,
    ];
}
}
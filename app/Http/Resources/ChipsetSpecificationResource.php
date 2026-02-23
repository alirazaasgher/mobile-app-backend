<?php
namespace App\Http\Resources;

use App\Models\RamType;
use App\Models\StorageType;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ChipsetSpecificationResource extends JsonResource
{
    public function toArray($request)
    {
        $specs = json_decode($this->specifications, true);

        if ($this->category === 'memory' && isset($specs['memory_type'])) {
            $specs['memory_type'] = $this->resolveMemoryTypes($specs['memory_type']);
        }

        if ($this->category === 'multimedia' && isset($specs['storage_type'])) {
            $specs['storage_type'] = $this->resolveStorageTypes($specs['storage_type']);
        }

        return [$this->category => $specs];
    }

    private function resolveMemoryTypes(array $ids): array
    {
        $memoryTypes = Cache::rememberForever(
            'memory_types',
            fn() =>
            RamType::pluck('name', 'id')
        );

        return collect($ids)
            ->map(fn($id) => $memoryTypes[$id] ?? $id)
            ->toArray();
    }
    private function resolveStorageTypes(array $ids): array
    {
        $memoryTypes = Cache::rememberForever(
            'storage_type',
            fn() =>
            StorageType::pluck('name', 'id')
        );

        return collect($ids)
            ->map(fn($id) => $memoryTypes[$id] ?? $id)
            ->toArray();
    }
}

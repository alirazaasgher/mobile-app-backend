@extends('admin.layouts.app')
@section('content')
@php
$categories = ['general', 'network', 'sim', 'body', 'platform', 'memory', 'display', 'main_camera', 'selfie_camera', 'audio', 'sensors', 'connectivity', 'battery', 'misc'];
$network = ['technology', '2G bands', '3G bands', '4G bands', '5G bands', 'speed'];
@endphp
 <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Mobiles Management</h2>
        <p class="text-muted mb-0">Add Phone</p>
    </div>
</div>

<!-- Error Messages -->
@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Success Message -->
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="container bg-white shadow rounded">


  <form action="{{ isset($mobile) ? route('admin.mobiles.update', $mobile->id) : route('admin.mobiles.store') }}" method="POST"
    enctype="multipart/form-data">
    @csrf

    @if(isset($mobile))
    @method('PUT')
    @endif

    <!-- Phone Basic Info -->

      <!-- Brand & Name -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-medium">Brand</label>
          <select name="brand" class="form-select">
            <option value="">Select brand</option>
            @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" {{ old('brand', $mobile->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
              {{ $brand->name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-medium">Name</label>
          <input type="text" name="name" value="{{ old('name', $mobile->name ?? '') }}" class="form-control" required>
        </div>
      </div>

      <!-- Mobile Description -->
      <div class="mb-3">
        <label class="form-label fw-medium">Mobile Description</label>
        @php
        $descEditorId = 'editor_' . uniqid();
        $descTextareaId = 'textarea_' . uniqid();
        @endphp
        <div id="{{ $descEditorId }}" class="quill-editor mb-2" data-target="{{ $descTextareaId }}" style="min-height:150px;"></div>
        <textarea name="description" id="{{ $descTextareaId }}" class="form-control d-none">{!! $mobile->description ?? '' !!}</textarea>
      </div>

      <!-- Pros & Cons -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-medium">Pros</label>
          @php
          $prosEditorId = 'editor_' . uniqid();
          $prosTextareaId = 'textarea_' . uniqid();
          @endphp
          <div class="mb-2">
            <div id="{{ $prosEditorId }}"
              class="quill-editor"
              data-target="{{ $prosTextareaId }}"
              style="min-height:180px;"></div>
          </div>

          <textarea name="pros" id="{{ $prosTextareaId }}"
            class="form-control d-none">{!! old('pros', $mobile->pros ?? '') !!}</textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-medium">Cons</label>
          @php
          $consEditorId = 'editor_' . uniqid();
          $consTextareaId = 'textarea_' . uniqid();
          @endphp
          <div class="mb-2">
            <div id="{{ $consEditorId }}"
              class="quill-editor"
              data-target="{{ $consTextareaId }}"
              style="min-height:180px;"></div>
          </div>

          <textarea name="cons" id="{{ $consTextareaId }}"
            class="form-control d-none">{!! old('cons', $mobile->cons ?? '') !!}</textarea>
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-medium">Primary Image</label>
          <input type="file" name="primary_image" class="form-control">
          @if(isset($mobile) && $mobile->primary_image)
          <div class="mt-2">
            <img src="{{ Storage::url($mobile->primary_image) }}" alt="Primary Image" class="img-thumbnail" style="width:100px;height:100px;">
          </div>
          @endif
        </div>
        <div class="col-md-6">
          <label class="form-label fw-medium">Primary Color</label>
          <input type="text" name="primary_color" value="{{ old('primary_color', $mobile->primary_color ?? '') }}" class="form-control">
        </div>
      </div>


      <!-- Announced & Release Date -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-medium">Announced Date</label>
          <input type="date" name="announced_date"
            value="{{ old('announced_date', isset($mobile->announced_date) ? \Carbon\Carbon::parse($mobile->announced_date)->format('Y-m-d') : '') }}"
            class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-medium">Release Date</label>
          <input type="date" name="release_date"
            value="{{ old('release_date', isset($mobile->release_date) ? \Carbon\Carbon::parse($mobile->release_date)->format('Y-m-d') : '') }}"
            class="form-control">
        </div>
      </div>

      <!-- Status & Top Competitors -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-medium">Status</label>
            <select name="status" class="form-select">
                <option value="">Select Status</option>
                <option value="new" {{ old('status', $mobile->status ?? '') == 'new' ? 'selected' : '' }}>New</option>
                <option value="upcoming" {{ old('status', $mobile->status ?? '') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="rumored" {{ old('status', $mobile->status ?? '') == 'rumored' ? 'selected' : '' }}>Rumored</option>
                <option value="discontinued" {{ old('status', $mobile->status ?? '') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
            </select>
        </div>
        {{-- <div class="col-md-6">
            <label class="form-label fw-medium">Top Competitors</label>
            <select name="competitors[]" class="form-select" multiple size="6">
                @foreach ($allMobiles as $m)
                    <option value="{{ $m->id }}" {{ in_array($m->id, old('competitors', $existingCompetitors ?? [])) ? 'selected' : '' }}>
                        {{ $m->name }}
                    </option>
                @endforeach
            </select>
        </div> --}}
    </div>


      <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-gradient border-0 py-4">
          <h4 class="mb-0 text-dark fw-bold">
            <i class="bi bi-phone me-2"></i>Phone Specifications
          </h4>
        </div>

        <div class="card-body p-4">
          <!-- Tabs Header -->
          <ul class="nav nav-pills nav-fill mb-4 gap-2" id="specTabs" role="tablist">
            @foreach ($specificationTemplates as $index => $fields)
            <li class="nav-item flex-grow-0" role="presentation">
              <button class="nav-links rounded-3 {{ $loop->first ? 'active' : '' }} position-relative"
                id="tab-{{ $index }}-tab" data-bs-toggle="tab"
                data-bs-target="#tab-{{ $index }}" type="button" role="tab"
                aria-controls="tab-{{ $index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                <span class="d-block fw-semibold">{{ ucfirst($index) }}</span>
                <span class="badge bg-white text-primary position-absolute top-0 end-0 mt-1 me-1">{{ count($fields['items']) }}</span>
              </button>
            </li>
            @endforeach
          </ul>

          <!-- Tabs Content -->
          <div class="tab-content">
            @foreach ($specificationTemplates as $fieldindex => $fields)
            @php
            $expandable = $fields['expandable'];
            $max_visible = $fields['max_visible'];
            @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
              id="tab-{{ $fieldindex }}" role="tabpanel" aria-labelledby="tab-{{ $fieldindex }}-tab">

              <input type="hidden" name="specifications[{{ $fieldindex }}][expandable]" value="{{ $expandable }}" />
              <input type="hidden" name="specifications[{{ $fieldindex }}][max_visible]" value="{{ $max_visible }}" />

              <!-- Specifications Fields -->
              <div class="row g-3">
                @foreach ($fields['items'] as $index => $field)
                @php
                $fieldId = 'spec_' . $field['key'] . '_' . $index;
                $specData = $mobile->specifications[$fieldindex] ?? null;
                $specDataArray = $specData ? json_decode($specData->specifications ?? '{}', true) : [];
                $specValue = old("specifications.$fieldindex." . $field['key'], $specDataArray[$field['key']] ?? '');
                @endphp

                <div class="col-12">
                  <div class="spec-field-wrapper bg-light rounded-3 p-3 border border-1 hover-shadow transition">
                    <div class="row align-items-center">
                      <!-- Label -->
                      <div class="col-lg-4 col-md-5 mb-2 mb-lg-0">
                        <label for="{{ $fieldId }}" class="form-label fw-semibold text-secondary mb-0">
                          <i class="bi bi-circle-fill text-primary me-2" style="font-size: 6px;"></i>
                          {{ $field['label'] }}
                          @if(isset($field['required']) && $field['required'])
                          <span class="text-danger ms-1">*</span>
                          @endif
                        </label>
                      </div>

                      <!-- Field -->
                      <div class="col-lg-8 col-md-7">
                        @if ($field['type'] === 'select' && isset($field['options']))
                        <select name="specifications[{{ $fieldindex }}][{{ $field['key'] }}]"
                          id="{{ $fieldId }}" class="form-select shadow-sm">
                          <option value="">-- Select {{ $field['label'] }} --</option>
                          @foreach ($field['options'] as $option)
                          <option value="{{ $option }}" {{ $specValue == $option ? 'selected' : '' }}>
                            {{ $option }}
                          </option>
                          @endforeach
                        </select>
                        @elseif ($field['type'] === 'textarea')
                        <div id="editor-{{ $fieldId }}" class="quill-editor mb-2 bg-white rounded border" data-target="{{ $fieldId }}"></div>
                        <textarea name="specifications[{{ $fieldindex }}][{{ $field['key'] }}]"
                          id="{{ $fieldId }}" class="form-control d-none">{!! $specValue !!}</textarea>
                        @else
                        <input type="{{ $field['type'] }}"
                          name="specifications[{{ $fieldindex }}][{{ $field['key'] }}]"
                          id="{{ $fieldId }}" value="{{ $specValue }}"
                          placeholder="{{ $field['placeholder'] ?? 'Enter ' . strtolower($field['label']) }}"
                          class="form-control shadow-sm"/>
                        @endif

                        @if(isset($field['help']))
                        <small class="text-muted d-block mt-1">
                          <i class="bi bi-info-circle me-1"></i>{{ $field['help'] }}
                        </small>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

            </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Variants (Colors & Storage)</h2>
        <div id="variants-wrapper" class="space-y-2">
          <div class="variant-row border p-4 rounded bg-gray-50 space-y-4">
            <!-- Row 2: Dynamic Color options -->
            <div class="mb-4">

              <!-- Header -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-semibold">Color Options</h5>
                <button type="button" id="addColorBtn" class="btn btn-primary btn-sm">
                  + Add Color
                </button>
              </div>

              <div id="color-options-container" class="vstack gap-3">

                {{-- Existing colors --}}
                @if(!empty($mobile->colors))
                @foreach($mobile->colors as $index => $color)
                @php
                $colorId = 'color_' . $index;
                $colorSlug = $color['slug'];
                @endphp

                <div class="border rounded p-3" id="{{ $colorId }}">
                  <!-- Row layout -->
                  <div class="row align-items-center g-2">
                    <!-- Name -->
                    <div class="col-md-4">
                      <input type="text"
                        name="variants[color_names][{{ $colorSlug }}]"
                        value="{{ $color['name'] }}"
                        class="form-control form-control-sm"
                        placeholder="Color name">
                    </div>

                    <!-- Hex -->
                    <div class="col-md-3">
                      <input type="text"
                        name="variants[color_hex][{{ $colorSlug }}]"
                        value="{{ $color['hex_code'] }}"
                        class="form-control form-control-sm">
                    </div>

                    <!-- Images -->
                    <div class="col-md-4">
                      <input type="file"
                        name="variants[color_image][{{ $colorSlug }}][]"
                        class="form-control form-control-sm"
                        accept="image/*" multiple>
                    </div>

                    <!-- Remove -->
                    <div class="col-md-1 text-center">
                      <button type="button"
                        class="remove-color btn btn-sm btn-outline-danger"
                        data-target="{{ $colorId }}">

                      <i class="bi bi-trash"></i>
                      </button>
                    </div>

                  </div>

                  {{-- Existing images --}}
                  @if(!empty($color->images))
                  <div class="row g-3 mt-3">
                    @foreach($color->images as $img)
                    <div class="col-6 col-md-4 col-lg-2">
                      <div class="border rounded p-2 text-center position-relative">
                        <img src="{{ asset('storage/'.$img->image_url) }}"
                          class="img-fluid mb-2"
                          style="height:100px;object-fit:contain;cursor:pointer">

                        <div class="form-check">
                          <input type="checkbox"
                            name="variants[delete_images][]"
                            value="{{ $img->id }}"
                            class="form-check-input">
                          <label class="form-check-label text-danger small">
                            Delete
                          </label>
                        </div>

                      </div>
                    </div>
                    @endforeach
                  </div>
                  @endif

                </div>
                @endforeach

                @else
                {{-- Default empty row --}}
                <div class="border rounded p-3" id="color_0">
                  <div class="row align-items-center g-2">
                    <div class="col-md-4">
                      <input type="text"
                        name="variants[colors][color_names][color_0]"
                        class="form-control form-control-sm"
                        placeholder="Color name">
                    </div>

                    <div class="col-md-3">
                      <input type="text"
                        name="variants[color_hex][color_0]"
                        value="#000000"
                        class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                      <input type="file"
                        name="variants[color_image][color_0][]"
                        class="form-control form-control-sm"
                        multiple>
                    </div>

                  </div>
                </div>
                @endif

              </div>
            </div>

            <!-- Row 3: RAM/Storage options with price modifiers -->
            @php
            $variantIndex ??= 0;
            $existingVariants = collect($mobile->variants ?? [])
            ->map(fn($v) => "{$v['ram']}/{$v['storage']}")
            ->toArray();

            $storageOptions = [
            // --- Basic Tier ---
            ['value' => '4/64', 'label' => '4GB/64GB', 'modifier' => 0, 'badge' => 'Basic'],
            ['value' => '4/128', 'label' => '4GB/128GB', 'modifier' => 0, 'badge' => 'Basic'],

            // --- Standard Tier ---
            ['value' => '6/128', 'label' => '6GB/128GB', 'modifier' => 0, 'badge' => 'Standard'],
            ['value' => '6/256', 'label' => '6GB/256GB', 'modifier' => 0, 'badge' => 'Standard'],

            // --- Premium Tier ---
            ['value' => '8/128', 'label' => '8GB/128GB', 'modifier' => 0, 'badge' => 'Premium'],
            ['value' => '8/256', 'label' => '8GB/256GB', 'modifier' => 0, 'badge' => 'Premium'],
            ['value' => '8/512', 'label' => '8GB/512GB', 'modifier' => 0, 'badge' => 'Premium'],
            ['value' => '8/1TB', 'label' => '8GB/1TB', 'modifier' => 0, 'badge' => 'Premium'],
            ['value' => '8/2TB', 'label' => '8GB/2TB', 'modifier' => 0, 'badge' => 'Premium'],

            // --- Pro Tier ---
            ['value' => '12/256', 'label' => '12GB/256GB', 'modifier' => 0, 'badge' => 'Pro'],
            ['value' => '12/512', 'label' => '12GB/512GB', 'modifier' => 0, 'badge' => 'Pro'],

            ['value' => '12/1TB', 'label' => '12GB/1TB', 'modifier' => 0, 'badge' => 'Pro'],
            ['value' => '12/2TB', 'label' => '12GB/2TB', 'modifier' => 0, 'badge' => 'Pro'],
            ['value' => '16/256', 'label' => '16GB/256GB', 'modifier' => 0, 'badge' => 'Pro'],
            ['value' => '16/512', 'label' => '16GB/512GB', 'modifier' => 0, 'badge' => 'Pro'],
            ['value' => '16/1TB', 'label' => '16GB/1TB', 'modifier' => 0, 'badge' => 'Pro'],
            ['value' => '20/1TB', 'label' => '20GB/1TB', 'modifier' => 0, 'badge' => 'Pro']
            ];
            $selectedSpecs = old("variants.{$variantIndex}.specs", $existingVariants);

            $badgeColors = [
            'Basic' => 'bg-gray-100 text-gray-700',
            'Standard' => 'bg-blue-100 text-blue-700',
            'Premium' => 'bg-purple-100 text-purple-700',
            'Pro' => 'bg-green-100 text-green-700',
            ];
            @endphp

            @foreach(collect($storageOptions)->groupBy('badge') as $badge => $options)
            <h5 class="fw-semibold mt-4 mb-2">{{ $badge }} Options</h5>
            <div class="row g-3">
              @foreach($options as $option)
              @php
              $optionValue = $option['value'];
              $isChecked = in_array($optionValue, $selectedSpecs);
              $existingVariant = collect($mobile->variants ?? [])->first(function ($v) use ($optionValue) {
              [$ram, $storage] = explode('/', $optionValue);
              return $v['ram'] == $ram && $v['storage'] == $storage;
              });
              $modifierValueUSD = old("variants.{$variantIndex}.price_modifier.{$optionValue}", $existingVariant['usd_price'] ?? $option['modifier']);
              $modifierValuePKR = old("variants.{$variantIndex}.price_modifier.{$optionValue}", $existingVariant['pkr_price'] ?? $option['modifier']);
              $badgeClass = $badgeColors[$option['badge']] ?? 'bg-light text-dark';
              @endphp

              <div class="col-md-6">
                <div class="card {{ $isChecked ? 'border-primary bg-light' : '' }} h-100">
                  <div class="card-body d-flex flex-column">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" name="variants[specs][]"
                        value="{{ $optionValue }}" id="variant_{{ $loop->parent->index }}_{{ $loop->index }}"
                        {{ $isChecked ? 'checked' : '' }}>
                      <label class="form-check-label fw-semibold" for="variant_{{ $loop->parent->index }}_{{ $loop->index }}">
                        {{ $option['label'] }}
                        <span class="badge {{ $badgeClass }} ms-2">{{ $option['badge'] }}</span>
                      </label>
                    </div>

                    <div class="row g-2 mt-2 align-items-center">
                      <div class="col-auto">
                        <label class="form-label small mb-0">Price USD</label>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">$</span>
                          <input type="number" name="variants[price_modifier_usd][{{ $optionValue }}]"
                            value="{{ $modifierValueUSD ?? '' }}" placeholder="0.00" step="0.01" min="0"
                            class="form-control form-control-sm">
                        </div>
                      </div>

                      <div class="col-auto">
                        <label class="form-label small mb-0">Price PKR</label>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">₨</span>
                          <input type="number" name="variants[price_modifier_pkr][{{ $optionValue }}]"
                            value="{{ $modifierValuePKR ?? '' }}" placeholder="0" step="1" min="0"
                            class="form-control form-control-sm">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            @endforeach

            @php
            $storageTypeSelected = $mobile->searchIndex['storage_type'] ?? null;
            $ramTypeSelected = $mobile->searchIndex['ram_type'] ?? null;
            $sdCardSelected = $mobile->searchIndex['sd_card'] ?? null
            @endphp
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-medium">RAM Type:</label>
                <select name="ram_type" class="form-select">
                  <option value="">Please Select</option>
                  @foreach ($ramTypes as $ramType)
                  <option value="{{ $ramType->name }}" {{ old('ram_type', $ramTypeSelected ?? '') == $ramType->name ? 'selected' : '' }}>
                    {{ $ramType->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Storage Type:</label>
                <select name="storage_type" class="form-select">
                  <option value="">Select storage</option>
                  @foreach ($storageTypes as $storageType)
                  <option value="{{ $storageType->name }}" {{ old('storage_type', $storageTypeSelected ?? '') == $storageType->name ? 'selected' : '' }}>
                    {{ $storageType->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-medium">D Card</label>
                <select name="sd_card" class="form-select">
                  <option value="">Select Status</option>
                  <option value="1" {{ old('status', $sdCardSelected ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                  <option value="0" {{ old('status', $sdCardSelected ?? '') == '0' ? 'selected' : '' }}>NO
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="pb-3">
          <div class="d-flex gap-2 mt-3">
            <button type="submit" name="action" value="publish" class="btn btn-success px-4">
              Save Phone
            </button>

            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary px-4">
              Save as Draft
            </button>
          </div>
        </div>
      </div>


  </form>
</div>
@endsection

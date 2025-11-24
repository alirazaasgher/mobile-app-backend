@extends('admin.layouts.app')

@section('content')
  @php
    $categories = ['general', 'network', 'sim', 'body', 'platform', 'memory', 'display', 'main_camera', 'selfie_camera', 'audio', 'sensors', 'connectivity', 'battery', 'misc'];
    $network = ['technology', '2G bands', '3G bands', '4G bands', '5G bands', 'speed'];
  @endphp
  <div class="w-full mx-auto p-6 bg-white shadow rounded">
    @if($errors->any())
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Add Phone</h1>

    <form action="{{ isset($mobile) ? route('mobiles.update', $mobile->id) : route('mobiles.store') }}" method="POST"
      enctype="multipart/form-data">
      @csrf

      @if(isset($mobile))
        @method('PUT')
      @endif

      <!-- Phone Basic Info -->
      <div class="space-y-4 mb-6">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium">Brand</label>
            <select name="brand"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select brand</option>
              @foreach ($brands as $brand)
                <option value="{{$brand->id}}" {{ old('brand', $mobile->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                  {{$brand->name}}
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name', $mobile->name ?? '') }}"
              class="border rounded w-full p-2" required>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium">Primary Image URL</label>
            <input type="file" name="primary_image" class="border rounded w-full p-2">

            {{-- Show current image in Edit --}}
            @if(isset($mobile) && $mobile->primary_image)
              <div class="mt-2">
                <img src="{{ asset('storage/' . $mobile->primary_image) }}" alt="Primary Image"
                  class="w-24 h-24 object-cover rounded">
              </div>
            @endif
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium">Announced Date</label>
            <input type="date" name="announced_date"
              value="{{ old('announced_date', isset($mobile->announced_date) ? \Carbon\Carbon::parse($mobile->announced_date)->format('Y-m-d') : '') }}"
              class="border rounded w-full p-2">
          </div>
          <div>
            <label class="block font-medium">Release Date</label>
            <input type="date" name="release_date"
              value="{{ old('release_date', isset($mobile->release_date) ? \Carbon\Carbon::parse($mobile->release_date)->format('Y-m-d') : '') }}"
              class="border rounded w-full p-2">
          </div>
        </div>
      </div>

      <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Phone Specifications</h2>

        <div class="bg-gray-50 p-4 rounded-lg">
          <!-- Tabs header with better visual hierarchy -->
          <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-6" id="tabs" role="tablist">
            @foreach ($specificationTemplates as $index => $fields)
               <button type="button" data-tab="{{ $index }}" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                         {{ $loop->first ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-gray-600 hover:text-blue-600 hover:border-gray-300' }}
                         focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-t-md" role="tab"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="tab-{{ $index }}">
                {{ ucfirst($index) }}
                <span class="ml-1 text-xs text-gray-500">({{ count($fields['items']) }})</span>
              </button>
            @endforeach
          </div>

          <!-- Tabs content -->
          <div>
            @foreach ($specificationTemplates as $fieldindex => $fields)
              <div id="tab-{{ $fieldindex }}" class="tab-content {{ $loop->first ? '' : 'hidden' }}" role="tabpanel"
                aria-labelledby="tab-{{ $fieldindex }}">
                @php
                $expandable = $fields['expandable'];
                $max_visible = $fields['max_visible'];
                @endphp
                    <input type="hidden" name="specifications[{{ $fieldindex }}][expandable]"
                            value="{{ $expandable }}"/>
                            <input type="hidden" name="specifications[{{ $fieldindex }}][max_visible]"
                            value="{{ $max_visible }}"/>
                <!-- Specifications fields -->
                <div class="space-y-3">
                  @foreach ($fields['items'] as $index => $field)
                    @php
                      $fieldId = 'spec_' . $field['key'] . '_' . $index;
                      $specData = [];
                      if (isset($mobile->specifications[$fieldindex])) {
                        $specData = json_decode($mobile->specifications[$fieldindex]->specifications ?? '{}', true);
                      }
                      $specValue = old("specifications.$fieldindex." . $field['key'], $specData[$field['key']] ?? '');
                    @endphp

                    <div
                      class="grid grid-cols-1 lg:grid-cols-3 gap-3 items-start p-4 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">

                      <label for="{{ $fieldId }}" class="text-sm font-medium text-gray-700 pt-2">
                        {{ $field['label'] }}
                        @if(isset($field['required']) && $field['required'])
                          <span class="text-red-500">*</span>
                        @endif
                      </label>

                      <div class="lg:col-span-2">
                        @if ($field['type'] === 'select' && isset($field['options']))
                          <select name="specifications[{{$fieldindex}}][{{ $field['key'] }}]" id="{{ $fieldId }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                                   text-sm transition-colors"
                            onchange="updateSearchableText('{{ $fieldindex }}')">
                            <option value="">-- Select {{ $field['label'] }} --</option>
                            @foreach ($field['options'] as $option)
                              <option value="{{ $option }}" {{ $specValue == $option ? 'selected' : '' }}>
                                {{ $option }}
                              </option>
                            @endforeach
                          </select>
                        @else
                          <input type="{{ $field['type'] }}" name="specifications[{{ $fieldindex }}][{{ $field['key'] }}]"
                            id="{{ $fieldId }}" value="{{ $specValue }}"
                            placeholder="{{ $field['placeholder'] ?? 'Enter ' . strtolower($field['label']) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                                  text-sm transition-colors"
                            onchange="updateSearchableText('{{ $fieldindex }}')" />
                        @endif

                        @if(isset($field['help']))
                          <p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>

                <!-- Searchable Text Preview -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                  <label for="searchable_text_{{$fieldindex}}" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                      Search Preview
                      <span class="ml-2 text-xs text-gray-500 font-normal">(auto-generated from above fields)</span>
                    </span>
                  </label>
                  <textarea name="searchable_text[{{$fieldindex}}]" id="searchable_text_{{$fieldindex}}" rows="3" readonly
                    class="w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm bg-white text-gray-700 text-sm"
                    placeholder="Fill in the specifications above to generate searchable text...">{{ old("searchable_text.$fieldindex") }}</textarea>
                  <p class="mt-2 text-xs text-blue-700">
                    <strong>Tip:</strong> This text helps users find this phone when searching. It updates automatically as
                    you fill in the fields.
                  </p>
                </div>

                <!-- Add Custom Field Button -->
                <div
                  class="mt-6 flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                  <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Need to add a specification not listed above?</span>
                  </div>
                  <button type="button"
                    class="add-field-btn inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    data-category="{{ $fieldindex }}" title="Add a custom specification field">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Custom Field
                  </button>
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
            <div class="space-y-3">
  <div class="flex justify-between items-center">
    <h3 class="text-base font-semibold text-gray-900">Color Options</h3>
    <button type="button" id="addColorBtn"
            class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600">
      + Add Color
    </button>
  </div>

 <div id="color-options-container" class="space-y-2">
    {{-- ✅ Pre-render existing colors --}}
    @if(!empty($mobile->colors))
        @foreach($mobile->colors as $index => $color)
            @php
                $colorId = 'color_' . $index;
                $colorSlug = $color['slug'];
            @endphp

            <div class="color-option-row flex flex-col space-y-2 w-full border p-2 rounded" id="{{ $colorId }}">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="variants[colors][]"
                        value="{{ $colorSlug }}" class="rounded flex-shrink-0" checked>

                    <div class="color-preview w-4 h-4 rounded-full border flex-shrink-0"
                        style="background-color: {{ $color['hex_code'] }}"></div>

                    <input type="text" name="variants[color_names][{{ $colorSlug }}]"
                        value="{{ $color['name'] }}" placeholder="Color Name"
                        class="color-name-input text-sm border rounded px-2 py-1 flex-1">

                    <input type="text" name="variants[color_hex][{{ $colorSlug }}]"
                        value="{{ $color['hex_code'] }}"
                        class="color-hex-input text-xs border rounded px-2 py-1 flex-1"
                        oninput="updateColorCircle('{{ $colorId }}', this.value)">

                    <input type="file" name="variants[color_image][{{ $colorSlug }}][]"
                        class="text-xs border rounded px-2 py-1 flex-1" accept="image/*" multiple>

                    <button type="button" class="remove-color bg-red-400 text-white px-2 py-1 rounded text-xs"
                        onclick="removeColorOption('{{ $colorId }}')">×</button>
                </div>

              {{-- ✅ Show existing images with delete checkbox --}}
@if(!empty($color->images))
  <div class="existing-images grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mt-4">
    @foreach($color->images as $img)
      <div class="relative group rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 bg-gray-50">
        {{-- Clickable image to view full size --}}
        <img
          src="{{ asset('storage/' . $img->image_url) }}"
          class="w-full h-32 object-contain bg-white p-2 cursor-pointer group-hover:scale-105 transition-transform duration-300"
          alt="Color Image"
          onclick="openImageModal('{{ asset('storage/' . $img->image_url) }}', {{ $img->id }})"
        >

        {{-- 🗑️ Delete checkbox overlay --}}
        <label class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer flex items-end justify-center pb-2"
          onclick="event.stopPropagation()">
          <div class="flex items-center gap-2 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-lg">
            <input
              type="checkbox"
              name="variants[delete_images][]"
              value="{{ $img->id }}"
              class="w-4 h-4 accent-red-500 cursor-pointer"
              onclick="event.stopPropagation()"
            >
            <span class="text-sm font-medium text-red-600">Delete</span>
          </div>
        </label>

        {{-- Image index badge --}}
        <div class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full pointer-events-none">
          {{ $loop->iteration }}
        </div>

        {{-- View icon --}}
        <div class="absolute top-2 right-2 bg-white/90 text-gray-700 p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </div>
      </div>
    @endforeach
  </div>
@endif
            </div>
        @endforeach
    @else
        {{-- Default 1 row if no colors --}}
        <div class="color-option-row flex items-center space-x-2 w-full border p-2 rounded" id="color_0">
            <input type="checkbox" name="variants[colors][]" value="color_0" class="rounded flex-shrink-0" checked>
            <div class="color-preview w-4 h-4 rounded-full border flex-shrink-0" style="background-color: #000000"></div>

            <input type="text" name="variants[color_names][color_0]" placeholder="Color Name"
                class="color-name-input text-sm border rounded px-2 py-1 flex-1">

            <input type="text" name="variants[color_hex][color_0]" value="#000000"
                class="color-hex-input text-xs border rounded px-2 py-1 flex-1"
                oninput="updateColorCircle('color_0', this.value)">

            <input type="file" name="variants[color_image][color_0][]"
                class="text-xs border rounded px-2 py-1 flex-1" accept="image/*" multiple>

            <button type="button" class="remove-color bg-red-400 text-white px-2 py-1 rounded text-xs"
                onclick="removeColorOption('color_0')">×</button>
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

                // --- Pro Tier ---
                ['value' => '12/256', 'label' => '12GB/256GB', 'modifier' => 0, 'badge' => 'Pro'],
                ['value' => '12/512', 'label' => '12GB/512GB', 'modifier' => 0, 'badge' => 'Pro'],
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
              <h4 class="text-sm font-semibold mt-6 mb-2 text-gray-700">{{ $badge }} Options</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($options as $option)
                      @php
                        $optionValue = $option['value'];
                        $isChecked = in_array($optionValue, $selectedSpecs);
                        $existingVariant = collect($mobile->variants ?? [])->first(function ($v) use ($optionValue) {
                          [$ram, $storage] = explode('/', $optionValue);
                          return $v['ram'] == $ram && $v['storage'] == $storage;
                        });
                        $modifierValue = old("variants.{$variantIndex}.price_modifier.{$optionValue}", $existingVariant['price'] ?? $option['modifier']);

                        $badgeClass = $badgeColors[$option['badge']] ?? 'bg-gray-100 text-gray-700';
                      @endphp

                      <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all duration-200
                  hover:border-blue-400 hover:shadow-md transform hover:scale-[1.01]
                  {{ $isChecked ? 'border-blue-500 bg-blue-50 shadow-sm scale-[1.01]' : 'border-gray-200 bg-white' }}">

                        <div class="flex items-start space-x-3">
                          <input type="checkbox" name="variants[specs][]" value="{{ $optionValue }}" {{ $isChecked ? 'checked' : '' }}
                            class="mt-1 h-5 w-5 rounded text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer">

                          <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                              <span class="text-sm font-semibold text-gray-900">{{ $option['label'] }}</span>
                              <span class="text-xs font-medium px-2 py-1 rounded-full {{ $badgeClass }}">
                                {{ $option['badge'] }}
                              </span>
                            </div>

                            <div class="flex items-center space-x-2 mt-3">
                              <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Price Adjustment:</label>
                              <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                                <input type="number" name="variants[price_modifier][{{ $optionValue }}]"
                                  value="{{ $modifierValue }}" placeholder="0.00" step="0.01" min="0" class="w-full pl-7 pr-3 py-2 text-sm border border-gray-300 rounded-md
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                              </div>
                            </div>
                          </div>
                        </div>
                      </label>
                @endforeach
              </div>
            @endforeach
    <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium">SD Card</label>
            <input type="text" name="sd_card"
              value="{{ old('name', $mobile->sd_card ?? 'No') }}"
              class="border rounded w-full p-2">
          </div>
          <div>
            <label class="block font-medium">Ram Type</label>
            <input type="text" name="ram_type"
              value="{{ old('name', $mobile->ram_type ?? '') }}"
              class="border rounded w-full p-2">
          </div>
        </div>
            <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium">Storage Type</label>
            <input type="text" name="storage_type"
              value="{{ old('name', $mobile->storage_type ?? '') }}"
              class="border rounded w-full p-2">
          </div>
        </div>
          </div>
        </div>
      </div>

      <button type="submit" name="action" value="publish" class="bg-green-500 text-white px-6 py-2 rounded">Save
        Phone</button>
      <button type="submit" name="action" value="draft" class="bg-green-500 px-6 py-2 rounded">Save as Draft</button>
    </form>
  </div>


  <script>

    document.addEventListener('DOMContentLoaded', function () {
      const tabButtons = document.querySelectorAll('.tab-btn');

      tabButtons.forEach(button => {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          const targetTab = this.getAttribute('data-tab');

          // Remove active state from all tabs
          tabButtons.forEach(btn => {
            btn.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50');
            btn.classList.add('border-transparent', 'text-gray-600');
            btn.setAttribute('aria-selected', 'false');
          });

          // Add active state to clicked tab
          this.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50');
          this.classList.remove('border-transparent', 'text-gray-600');
          this.setAttribute('aria-selected', 'true');

          // Hide all tab contents
          document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
          });

          // Show target tab content
          document.getElementById('tab-' + targetTab).classList.remove('hidden');
        });
      });

      // Initialize searchable text on page load
      @foreach ($specificationTemplates as $fieldindex => $fields)
        updateSearchableText('{{ $fieldindex }}');
      @endforeach
    });
    // const tabButtons = document.querySelectorAll(".tab-btn");
    // const tabContents = document.querySelectorAll(".tab-content");
    // tabName = tabButtons[0].getAttribute("data-tab");
    // tabButtons.forEach(btn => {
    //   btn.addEventListener("click", (e) => {
    //     e.preventDefault();
    //     const target = btn.getAttribute("data-tab");
    //     tabName = target;
    //     // Reset buttons
    //     tabButtons.forEach(b => {
    //       b.classList.remove("border-blue-600", "text-blue-600");
    //       b.classList.add("border-transparent", "text-gray-600");
    //     });

    //     // Hide all contents
    //     tabContents.forEach(c => c.classList.add("hidden"));

    //     // Activate current tab
    //     btn.classList.add("border-blue-600", "text-blue-600");
    //     btn.classList.remove("border-transparent", "text-gray-600");

    //     document.getElementById(target).classList.remove("hidden");
    //   });
    // });
    document.getElementById('variants-wrapper').addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-variant')) {
        e.target.closest('.variant-row').remove();
      }
    });

    // Toggle between simple and array value types
    function toggleValueType(selectElement, keyIndex, categoryName) {
      const keyRow = selectElement.closest('.spec-key-row');
      const simpleContainer = keyRow.querySelector('.simple-value-container');
      const arrayContainer = keyRow.querySelector('.array-values-container');
      const addValueBtn = keyRow.querySelector('.add-key-value');

      if (selectElement.value === 'simple') {
        simpleContainer.classList.remove('hidden');
        arrayContainer.classList.add('hidden');
        addValueBtn.style.display = 'none';
      } else {
        simpleContainer.classList.add('hidden');
        arrayContainer.classList.remove('hidden');
        addValueBtn.style.display = 'inline-block';
      }
    }


    document.getElementById('addColorBtn').addEventListener('click', function() {
    colorCounter++;
    const colorId = `color_${colorCounter}`;
    const colorSlug = colorId.toLowerCase();

    const container = document.getElementById('color-options-container');

    const html = `
      <div class="color-option-row flex items-center space-x-2 w-full border p-2 rounded" id="${colorId}">
        <input type="checkbox" name="variants[colors][]" value="${colorSlug}" class="rounded flex-shrink-0" checked>
        <div class="color-preview w-4 h-4 rounded-full border flex-shrink-0" style="background-color: #000000"></div>

        <input type="text" name="variants[color_names][${colorSlug}]" placeholder="Color Name"
               class="color-name-input text-sm border rounded px-2 py-1 flex-1">

        <input type="text" name="variants[color_hex][${colorSlug}]" value="#000000"
               class="color-hex-input text-xs border rounded px-2 py-1 flex-1"
               oninput="updateColorCircle('${colorId}', this.value)">

        <input type="file" name="variants[color_image][${colorSlug}][]"
               class="text-xs border rounded px-2 py-1 flex-1" accept="image/*" multiple>

        <button type="button" class="remove-color bg-red-400 text-white px-2 py-1 rounded text-xs"
                onclick="removeColorOption('${colorId}')">×</button>
      </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
  });

    function removeColorOption(colorId) {
    document.getElementById(colorId)?.remove();
  }

  function updateColorCircle(colorId, hex) {
    // Optional: Auto-set common color hex values
      const commonColors = {
        'black': '#000000',
        'white': '#FFFFFF',
        'blue': '#1E3A8A',
        'red': '#DC2626',
        'gold': '#FFD700',
        'silver': '#C0C0C0',
        'purple': '#7C3AED',
        'green': '#059669',
        'pink': '#EC4899',
        'gray': '#6B7280',
        'grey': '#6B7280'
      };

      const lowerColorName = colorName.toLowerCase();
      if (commonColors[lowerColorName]) {
        const colorRow = document.getElementById(`color-row-${colorId}`);
        const hexInput = colorRow.querySelector('.color-hex-input');
        const colorPreview = colorRow.querySelector('.color-preview');

        hexInput.value = commonColors[lowerColorName];
        colorPreview.style.backgroundColor = commonColors[lowerColorName];
      }
  }
    let colorCounter = 0;
    // Add first color option by default when page loads
    let fieldCounter = 0;
    function addCustomField(tabName) {
      fieldCounter++;
      // const container = document.getElementById(id);
      const customFieldHTML = `
                                                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200" id="custom-field-${fieldCounter}">
                                                      <input type="text" name="custom_keys[${tabName}][]" placeholder="Field name (e.g., special_feature)"
                                                             class="text-sm font-medium px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                      <div class="md:col-span-2 flex space-x-2">
                                                          <input type="text" name="custom_values[${tabName}][]" placeholder="Field value"
                                                                 class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                 onchange="updateSearchableText()">
                                                          <button type="button" onclick="removeCustomField(${fieldCounter})"
                                                                  class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md">
                                                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                              </svg>
                                                          </button>
                                                      </div>
                                                  </div>
                                              `;
      const temp = document.createElement('div');
      temp.innerHTML = customFieldHTML.trim();
      const node = temp.firstChild;

      // Find "Display Order" div
      const orderField = document.getElementById(`order-field-${tabName}`);

      // Insert new field *after* Display Order
      orderField.insertAdjacentElement('afterend', node);
    }

    function removeCustomField(id) {
      document.getElementById(`custom-field-${id}`).remove();
      updateSearchableText();
    }

    function updateSearchableText(category) {
      const tabContent = document.getElementById('tab-' + category);
      if (!tabContent) return;

      const inputs = tabContent.querySelectorAll('input[type="text"], input[type="number"], select');
      const searchableTextarea = document.getElementById('searchable_text_' + category);

      if (!searchableTextarea) return;

      let searchableText = [];

      inputs.forEach(input => {
        const value = input.value.trim();
        if (value && value !== '' && input.id.startsWith('spec_')) {
          // Get the label text
          const label = tabContent.querySelector(`label[for="${input.id}"]`);
          const labelText = label ? label.textContent.replace('*', '').trim() : '';

          searchableText.push(`${labelText}: ${value}`);
        }
      });

      searchableTextarea.value = searchableText.join(' | ');
    }

    const buttons = document.getElementsByClassName('add-field-btn');

    Array.from(buttons).forEach(btn => {
      btn.addEventListener('click', function (e) {
        console.log(this.id); // or e.target.id
        addCustomField(this.id);
      });
    });

    function addCustomVariant() {
      const container = event.target.previousElementSibling;
      const variantIndex = {{ $variantIndex ?? 0 }};

      const ram = prompt('Enter RAM (e.g., 16):');
      const storage = prompt('Enter Storage (e.g., 1TB or 1024):');

      if (ram && storage) {
        const value = `${ram}/${storage}`;
        const label = `${ram}GB/${storage}${storage.includes('TB') ? '' : 'GB'}`;

        const html = `
                      <label class="flex items-center justify-between p-2 border rounded hover:bg-gray-50 cursor-pointer transition-colors">
                          <div class="flex items-center space-x-2">
                              <input
                                  type="checkbox"
                                  name="variants[${variantIndex}][specs][]"
                                  value="${value}"
                                  class="rounded text-blue-600"
                                  checked
                              >
                              <span class="text-sm font-medium">${label}</span>
                          </div>
                          <input
                              type="number"
                              name="variants[${variantIndex}][price_modifier][${value}]"
                              placeholder="Modifier"
                              class="w-20 text-xs border rounded px-2 py-1"
                              value="0"
                          >
                      </label>
                  `;

        container.insertAdjacentHTML('beforeend', html);
      }
    }

    function selectAll(variantIndex) {
      const checkboxes = document.querySelectorAll(`input[name="variants[${variantIndex}][specs][]"]`);
      checkboxes.forEach(cb => cb.checked = true);
      updateCardStyles(variantIndex);
    }

    function clearAll(variantIndex) {
      const checkboxes = document.querySelectorAll(`input[name="variants[${variantIndex}][specs][]"]`);
      checkboxes.forEach(cb => cb.checked = false);
      updateCardStyles(variantIndex);
    }

    function updateCardStyles(variantIndex) {
      const checkboxes = document.querySelectorAll(`input[name="variants[${variantIndex}][specs][]"]`);
      checkboxes.forEach(cb => {
        const label = cb.closest('label');
        if (cb.checked) {
          label.classList.add('border-blue-500', 'bg-blue-50', 'shadow-sm');
          label.classList.remove('border-gray-200', 'bg-white');
        } else {
          label.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-sm');
          label.classList.add('border-gray-200', 'bg-white');
        }
      });
    }

    // Add event listeners to all checkboxes for dynamic styling
    document.addEventListener('DOMContentLoaded', function () {
      const checkboxes = document.querySelectorAll('input[type="checkbox"][name*="[specs]"]');
      checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
          const label = this.closest('label');
          if (this.checked) {
            label.classList.add('border-blue-500', 'bg-blue-50', 'shadow-sm');
            label.classList.remove('border-gray-200', 'bg-white');
          } else {
            label.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-sm');
            label.classList.add('border-gray-200', 'bg-white');
          }
        });
      });
    });
  </script>
@endsection

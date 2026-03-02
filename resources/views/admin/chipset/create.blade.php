@extends('admin.layouts.app')

@section('content')
  <div class="container">
    <form action="{{ isset($chipset) ? route('admin.chipsets.update', $chipset->id) : route('admin.chipsets.store') }}"  enctype="multipart/form-data"
      method="POST">
      @csrf
      @if(isset($chipset))
        @method('PUT')
      @endif
      <div class="row g-4">
          {{-- Basic Info --}}
          <div class="card mb-4">
            <div class="card-header fw-semibold">
              <i class="bi bi-cpu me-2"></i> Basic Information
            </div>
            <div class="card-body">
              <div class="row g-3">

                <div class="col-md-6">
                  <label class="form-label">Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $chipset->name ?? '') }}" placeholder="Snapdragon 8 Gen 3">
                  @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Brand <span class="text-danger">*</span></label>
                  <select name="brand_id" class="form-select @error('brand') is-invalid @enderror">
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                      <option value="{{ $brand->id }}" {{ old('brand_id', $chipset->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tier <span class="text-danger">*</span></label>
                  <select name="tier" class="form-select @error('tier') is-invalid @enderror">
                    <option value="">Select Tier</option>
                    @foreach(['flagship' => 'Flagship', 'upper_mid' => 'Upper Mid-Range', 'mid_range' => 'Mid-Range', 'budget' => 'Budget'] as $value => $label)
                      <option value="{{ $value }}" {{ old('tier', $chipset->tier ?? '') == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                  @error('tier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                <label class="form-label">Release Date</label>
                <input type="date"
                    name="release_date"
                    class="form-control @error('release_date') is-invalid @enderror"
                    value="{{ old('release_date', isset($chipset->release_date) ? \Carbon\Carbon::parse($chipset->release_date)->format('Y-m-d') : '') }}"
                    min="2010-01-01"
                    max="2035-12-31">

                @error('release_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Primary Image</label>
                <input type="file" name="primary_image" class="form-control">
                @if(isset($chipset) && $chipset->primary_image)
                <div class="mt-2">
                  <img src="{{ Storage::url($chipset->primary_image) }}" alt="Primary Image" class="img-thumbnail" style="width:100px;height:100px;">
                </div>
                @endif
              </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <ul class="nav nav-pills nav-fill mb-4 gap-2" id="specTabs" role="tablist">
              @foreach ($specificationTemplates as $index => $fields)
                <li class="nav-item flex-grow-0" role="presentation">
                  <button class="nav-links rounded-3 {{ $loop->first ? 'active' : '' }} position-relative"
                    id="tab-{{ $index }}-tab" data-bs-toggle="tab" data-bs-target="#tab-{{ $index }}" type="button"
                    role="tab" aria-controls="tab-{{ $index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    <span class="d-block fw-semibold">{{ ucfirst($index) }}</span>
                    <span
                      class="badge bg-white text-primary position-absolute top-0 end-0 mt-1 me-1">{{ count($fields['items']) }}</span>
                  </button>
                </li>
              @endforeach
            </ul>
            <div class="tab-content">
            @foreach ($specificationTemplates as $fieldindex => $fields)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
              id="tab-{{ $fieldindex }}" role="tabpanel" aria-labelledby="tab-{{ $fieldindex }}-tab">
              <!-- Specifications Fields -->
              <div class="row g-3">
                @foreach ($fields['items'] as $index => $field)
                @php
                $fieldId = 'spec_' . $field['key'] . '_' . $index;
                $specData = $chipset->specifications[$fieldindex] ?? null;
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
                        @elseif ($field['type'] === 'multiselect' && isset($field['options']))
                        <select name="specifications[{{ $fieldindex }}][{{ $field['key'] }}][]"
                            id="{{ $fieldId }}"
                            class="form-control selectpicker"
                            multiple>
                            @foreach ($field['options'] as $value => $label)
                                @php
                                    // Ensure $specValue is an array for in_array comparison
                                    $currentValues = is_array($specValue) ? $specValue : json_decode($specValue ?? '[]', true);
                                @endphp
                                <option value="{{ $value }}" {{ in_array($value, (array)$currentValues) ? 'selected' : '' }}>
                                    {{ $label }}
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
           {{-- Submit --}}
          <div class="card">
            <div class="card-header fw-semibold">
              <i class="bi bi-floppy me-2"></i> Save
            </div>
            <div class="card-body d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> {{  isset($chipset) ? 'Update Chipset' : 'Create Chipset' }}
              </button>
              <a href="{{ route('admin.chipsets.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x me-1"></i> Cancel
              </a>
            </div>
          </div>
      </div>

    </form>
  </div>
  @push('scripts')
    <script src="{{ asset('js/chipset.js') }}"></script>
    <script src="{{ asset('js/quill-editor.js') }}"></script>
  @endpush
@endsection

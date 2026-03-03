@extends('admin.layouts.app')

@section('content')

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">Mobiles Management</h2>
      <p class="text-muted mb-0">Manage your chipset listings</p>
    </div>
    <a href="{{ route('admin.mobiles.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle me-2"></i>Add New Chipset
    </a>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-primary bg-opacity-10 rounded p-3">
                <i class="bi bi-phone text-primary fs-4"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Total Chipset</h6>
              <h4 class="mb-0 fw-bold">{{ $chipsets->total() }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Table Card -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
      <div class="row align-items-center">
        <div class="col-auto">
          <div class="d-flex gap-2">
            <!-- Filter Dropdown -->
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filter
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">All Status</a></li>
                <li><a class="dropdown-item" href="#">Published</a></li>
                <li><a class="dropdown-item" href="#">Draft</a></li>
              </ul>
            </div>

            <!-- Search Box -->
            <div class="input-group input-group-sm" style="width: 250px;">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control border-start-0" placeholder="Search mobiles...">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th scope="col" class="ps-4 py-3 text-muted fw-semibold" style="width: 80px;">#ID</th>
              <th scope="col" class="py-3 text-muted fw-semibold">Name</th>
              <th scope="col" class="py-3 text-muted fw-semibold" style="width: 140px;">Announced Year</th>
              <th scope="col" class="py-3 text-muted fw-semibold" style="width: 140px;">Created</th>
              <th scope="col" class="pe-4 py-3 text-center text-muted fw-semibold" style="width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($chipsets as $chipset)
              <tr>
                <td class="ps-4">
                  <span class="text-muted fw-medium">#{{ $chipset->id }}</span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="ms-0">
                      <h6 class="mb-0 fw-semibold">{{ $chipset->name }}</h6>
                      <small class="text-muted">{{ $chipset->brand->name }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="text-muted">
                    {{ $chipset->announced_year}}
                  </span>
                </td>
                <td>
                  <span class="text-muted">{{ $chipset->created_at->format('M d, Y') }}</span>
                </td>
                <td class="pe-4">
                  <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.chipsets.edit', $chipset->id) }}" class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="tooltip" title="Edit Mobile">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('admin.chipsets.destroy', $chipset->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this mobile?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                        title="Delete Mobile">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-5">
                  <div class="py-4">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <h5 class="mt-3 text-muted">No chipset found</h5>
                    <p class="text-muted mb-3">Get started by adding your first chipset</p>
                    <a href="{{ route('admin.chipset.create') }}" class="btn btn-primary btn-sm">
                      <i class="bi bi-plus-circle me-2"></i>Add New Chipset
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination Footer -->
    @if($chipsets->hasPages())
      <div class="card-footer bg-white border-top py-3">
        <div class="row align-items-center">
          <div class="col-md-6">
            <div class="text-muted small">
              Showing <strong>{{ $chipsets->firstItem() }}</strong> to <strong>{{ $chipsets->lastItem() }}</strong> of
              <strong>{{ $chipsets->total() }}</strong> entries
            </div>
          </div>
          <div class="col-md-6">
            <nav aria-label="Page navigation">
              {{ $chipsets->links('pagination::bootstrap-5') }}
            </nav>
          </div>
        </div>
      </div>
    @endif
  </div>

@endsection

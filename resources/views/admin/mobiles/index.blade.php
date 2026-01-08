@extends('admin.layouts.app')

@section('content')

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1">Mobiles Management</h2>
        <p class="text-muted mb-0">Manage your mobile phone listings</p>
      </div>
      <a href="{{ route('admin.mobiles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add New Mobile
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
                <h6 class="text-muted mb-1">Total Mobiles</h6>
                <h4 class="mb-0 fw-bold">{{ $mobiles->total() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-success bg-opacity-10 rounded p-3">
                  <i class="bi bi-check-circle text-success fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="text-muted mb-1">Published</h6>
                <h4 class="mb-0 fw-bold">{{ $mobiles->where('deleted', 0)->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-warning bg-opacity-10 rounded p-3">
                  <i class="bi bi-clock text-warning fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="text-muted mb-1">Drafts</h6>
                <h4 class="mb-0 fw-bold">{{ $mobiles->where('deleted', 1)->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-info bg-opacity-10 rounded p-3">
                  <i class="bi bi-calendar-event text-info fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="text-muted mb-1">This Month</h6>
                <h4 class="mb-0 fw-bold">{{ $mobiles->where('created_at', '>=', now()->startOfMonth())->count() }}</h4>
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
          <div class="col">
            <h5 class="mb-0 fw-semibold">All Mobiles ({{ $mobiles->total() }} total)</h5>
          </div>
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
                <th scope="col" class="py-3 text-muted fw-semibold" style="width: 130px;">Status</th>
                <th scope="col" class="py-3 text-muted fw-semibold" style="width: 140px;">Release Date</th>
                <th scope="col" class="py-3 text-muted fw-semibold" style="width: 140px;">Created</th>
                <th scope="col" class="pe-4 py-3 text-center text-muted fw-semibold" style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($mobiles as $mobile)
                <tr>
                  <td class="ps-4">
                    <span class="text-muted fw-medium">#{{ $mobile->id }}</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="ms-0">
                        <h6 class="mb-0 fw-semibold">{{ $mobile->brand->name }} {{ $mobile->name }}</h6>
                        <small class="text-muted">{{ $mobile->brand->name }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($mobile->deleted === 0)
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>Published
                      </span>
                    @else
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                        <i class="bi bi-clock me-1"></i>Draft
                      </span>
                    @endif
                  </td>
                  <td>
                    <span class="text-muted">
                      {{ $mobile->release_date ? date('M d, Y', strtotime($mobile->release_date)) : '—' }}
                    </span>
                  </td>
                  <td>
                    <span class="text-muted">{{ $mobile->created_at->format('M d, Y') }}</span>
                  </td>
                  <td class="pe-4">
                    <div class="d-flex justify-content-center gap-2">
                      <a href="{{ route('admin.mobiles.edit', $mobile->id) }}"
                         class="btn btn-sm btn-outline-primary"
                         data-bs-toggle="tooltip"
                         title="Edit Mobile">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <form action="{{ route('admin.mobiles.destroy', $mobile->id) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this mobile?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="tooltip"
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
                      <h5 class="mt-3 text-muted">No mobiles found</h5>
                      <p class="text-muted mb-3">Get started by adding your first mobile</p>
                      <a href="{{ route('admin.mobiles.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-2"></i>Add New Mobile
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
      @if($mobiles->hasPages())
        <div class="card-footer bg-white border-top py-3">
          <div class="row align-items-center">
            <div class="col-md-6">
              <div class="text-muted small">
                Showing <strong>{{ $mobiles->firstItem() }}</strong> to <strong>{{ $mobiles->lastItem() }}</strong> of <strong>{{ $mobiles->total() }}</strong> entries
              </div>
            </div>
            <div class="col-md-6">
              <nav aria-label="Page navigation">
                {{ $mobiles->links('pagination::bootstrap-5') }}
              </nav>
            </div>
          </div>
        </div>
      @endif
    </div>

@endsection

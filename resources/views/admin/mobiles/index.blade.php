@extends('admin.layouts.app')

@section('content')
  <div class="card mt-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Mobiles</h5>
      <a href="{{ route('admin.mobiles.create') }}" class="btn btn-primary btn-sm">
        + Add New Mobile
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col">#ID</th>
            <th scope="col">Name</th>
            <th scope="col">Status</th>
            <th scope="col">Release Date</th>
            <th scope="col">Created</th>
            <th scope="col" class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($mobiles as $mobile)
            <tr>
              <th scope="row">#{{ $mobile->id }}</th>
              <td>{{ $mobile->brand->name }} {{ $mobile->name }}</td>
              <td>
                @if($mobile->deleted === 0)
                  <span class="badge bg-success">Published</span>
                @else
                  <span class="badge bg-warning text-dark">Draft</span>
                @endif
              </td>
              <td>{{ $mobile->release_date ? date('M d, Y', strtotime($mobile->release_date)) : '—' }}</td>
              <td>{{ $mobile->created_at->format('M d, Y') }}</td>
              <td class="text-center">
                <a href="{{ route('admin.mobiles.edit', $mobile->id) }}" class="text-decoration-none text-primary me-2">Edit</a>

                <form action="{{ route('admin.mobiles.destroy', $mobile->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" onclick="return confirm('Are you sure you want to delete this mobile?')"
                    class="btn btn-link p-0 m-0 text-danger text-decoration-none">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-secondary">No mobiles found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@extends('admin.layouts.app')

@section('content')
  <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Mobiles</h3>
      <a href="{{ route('mobiles.create') }}"
        class="bg-blue-600 text-white text-sm px-4 py-2 rounded-md hover:bg-blue-700 transition">
        + Add New Mobile
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">#ID</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Release Date</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Created</th>
            <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @forelse ($mobiles as $mobile)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">#{{ $mobile->id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $mobile->name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($mobile->deleted === 0)
                  <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Published</span>
                @else
                  <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Draft</span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                {{ $mobile->release_date ? date('M d, Y', strtotime($mobile->release_date)) : '—' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                {{ $mobile->created_at->format('M d, Y') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                <a href="{{ route('mobiles.edit', $mobile->id) }}"
                  class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>

                <form action="{{ route('mobiles.destroy', $mobile->id) }}" method="POST" class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" onclick="return confirm('Are you sure you want to delete this mobile?')"
                    class="text-red-600 hover:text-red-800 font-medium">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-6 text-gray-500">No mobiles found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

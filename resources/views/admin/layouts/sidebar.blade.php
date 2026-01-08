{{-- Sidebar --}}
<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 235px; height: 100vh; overflow-y: auto; position: fixed; top: 0; left: 0;">
    {{-- Logo / Brand --}}
    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">AdminPanel</span>
    </a>
    <hr>

    {{-- Navigation --}}
    <ul class="nav nav-pills flex-column mb-auto">

        {{-- Dashboard --}}
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active bg-secondary' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        {{-- All Mobiles --}}
        <li>
            <a href="{{ route('admin.mobiles.index') }}"
                class="nav-link text-white {{ request()->routeIs('admin.mobiles.index') ? 'active bg-secondary' : '' }}">
                <i class="bi bi-phone me-2"></i> All Mobile
            </a>
        </li>

        {{-- Add Mobile --}}
        <li>
            <a href="{{ route('admin.mobiles.create') }}"
                class="nav-link text-white {{ request()->routeIs('admin.mobiles.create') ? 'active bg-secondary' : '' }}">
                <i class="bi bi-plus-circle me-2"></i> Add Mobile
            </a>
        </li>

        {{-- Products --}}
        <li>
            <a href="#"
                class="nav-link text-white">
                <i class="bi bi-box-seam me-2"></i> Products
            </a>
        </li>

        {{-- Orders --}}
        <li>
            <a href="#"
                class="nav-link text-white">
                <i class="bi bi-bag-check me-2"></i> Orders
            </a>
        </li>

        {{-- Analytics --}}
        <li>
            <a href="#"
                class="nav-link text-white">
                <i class="bi bi-graph-up me-2"></i> Analytics
            </a>
        </li>

        {{-- Settings --}}
        <li>
            <a href="#"
                class="nav-link text-white">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
        </li>

    </ul>
</div>

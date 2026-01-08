{{-- Sidebar --}}
<aside id="sidebar"
       class="bg-dark text-white p-3"
       style="
            width:235px;
            min-height:100vh;
            position:sticky;
            top:0;
       ">

    {{-- Brand --}}
    <a href="{{ route('admin.dashboard') }}"
       class="d-flex align-items-center mb-4 text-white text-decoration-none">
        <span class="fs-5 fw-semibold">AdminPanel</span>
    </a>

    {{-- Navigation --}}
    <ul class="nav nav-pills flex-column gap-1">

        {{-- Dashboard --}}
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>

        {{-- All Mobiles --}}
        <li class="nav-item">
            <a href="{{ route('admin.mobiles.index') }}"
               class="nav-link {{ request()->routeIs('admin.mobiles.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-phone me-2"></i>
                All Mobiles
            </a>
        </li>

        {{-- Add Mobile --}}
        <li class="nav-item">
            <a href="{{ route('admin.mobiles.create') }}"
               class="nav-link {{ request()->routeIs('admin.mobiles.create') ? 'active' : 'text-white' }}">
                <i class="bi bi-plus-circle me-2"></i>
                Add Mobile
            </a>
        </li>

    </ul>

</aside>

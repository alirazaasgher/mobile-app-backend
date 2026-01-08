<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom" style="height:56px;">
  <div class="container-fluid px-3">

    <!-- Left section -->
    <div class="d-flex align-items-center gap-3">
      <!-- Sidebar toggle (mobile only) -->
      <button id="menu-toggle" class="btn btn-outline-secondary d-lg-none" type="button">
        <i class="bi bi-list fs-4"></i>
      </button>

      <h2 class="h5 mb-0 fw-semibold text-dark">Dashboard</h2>
    </div>

    <!-- Right section -->
    <div class="d-flex align-items-center gap-3 ms-auto">

      <!-- Search -->
      <form class="d-none d-md-block position-relative">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input type="text" class="form-control ps-5" placeholder="Search..." style="width: 220px;">
      </form>

      <!-- Notifications -->
      <button class="btn btn-light position-relative rounded-circle">
        <i class="bi bi-bell fs-5"></i>
        <span class="position-absolute top-0 start-100 translate-middle
                             p-1 bg-danger border border-white rounded-circle"></span>
      </button>

      <!-- Logout Button -->
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </button>
      </form>

    </div>

  </div>
</header>

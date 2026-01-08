<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom sticky-top" style="z-index: 1020;">
            <div class="container-fluid d-flex justify-content-between align-items-center px-3">
                {{-- Left section --}}
                <div class="d-flex align-items-center">
                    <button id="menu-toggle" class="btn btn-light d-lg-none me-3">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <h2 class="h5 mb-0 fw-semibold">Dashboard</h2>
                </div>

                {{-- Right section --}}
                <div class="d-flex align-items-center">
                    <form class="d-none d-md-flex position-relative me-3">
                        <input type="text" class="form-control ps-5 pe-3" placeholder="Search...">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                    </form>

                    <button class="btn btn-light position-relative me-3 rounded-circle">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>

                    <div class="d-flex align-items-center">
                        <img src="https://via.placeholder.com/32" alt="Profile" class="rounded-circle me-2">
                        <span class="d-none d-md-block fw-medium">John Doe</span>
                    </div>
                </div>
            </div>
        </header>
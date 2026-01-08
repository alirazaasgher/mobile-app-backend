@extends('admin.layouts.app')
@section('content')
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Total Users</p>
                        <h3 class="fw-bold">12,345</h3>
                        <p class="text-success mb-0">+5.2% from last month</p>
                    </div>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <svg class="bi" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Revenue</p>
                        <h3 class="fw-bold">$98,456</h3>
                        <p class="text-success mb-0">+12.1% from last month</p>
                    </div>
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <svg class="bi" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Orders</p>
                        <h3 class="fw-bold">2,847</h3>
                        <p class="text-danger mb-0">-2.4% from last month</p>
                    </div>
                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <svg class="bi" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Active Sessions</p>
                        <h3 class="fw-bold">1,234</h3>
                        <p class="text-success mb-0">+8.7% from last hour</p>
                    </div>
                    <div class="bg-purple text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <svg class="bi" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="row g-4">
        <!-- Chart Section -->
        <div class="col-12 col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Sales Overview</h5>
                    <div class="d-flex align-items-center justify-content-center" style="height:16rem; background-color:#f8f9fa; border-radius:.375rem;">
                        <p class="text-secondary mb-0">Chart placeholder - integrate with your preferred charting library</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recent Activity</h5>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-1">
                            <span class="badge bg-success rounded-circle me-2" style="width:8px; height:8px;"></span>
                            <span class="flex-grow-1 text-secondary">New user registered: jane.doe@email.com</span>
                            <small class="text-muted">2 min ago</small>
                        </div>
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-1">
                            <span class="badge bg-primary rounded-circle me-2" style="width:8px; height:8px;"></span>
                            <span class="flex-grow-1 text-secondary">Order #12345 was completed</span>
                            <small class="text-muted">5 min ago</small>
                        </div>
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-1">
                            <span class="badge bg-warning rounded-circle me-2" style="width:8px; height:8px;"></span>
                            <span class="flex-grow-1 text-secondary">Payment pending for order #12344</span>
                            <small class="text-muted">12 min ago</small>
                        </div>
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-1">
                            <span class="badge bg-danger rounded-circle me-2" style="width:8px; height:8px;"></span>
                            <span class="flex-grow-1 text-secondary">System maintenance scheduled</span>
                            <small class="text-muted">1 hour ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Orders</h5>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#12345</td>
                        <td>John Smith</td>
                        <td><span class="badge bg-success">Completed</span></td>
                        <td>$299.00</td>
                        <td>2024-01-15</td>
                    </tr>
                    <tr>
                        <td>#12344</td>
                        <td>Sarah Johnson</td>
                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                        <td>$159.50</td>
                        <td>2024-01-14</td>
                    </tr>
                    <tr>
                        <td>#12343</td>
                        <td>Mike Wilson</td>
                        <td><span class="badge bg-primary">Processing</span></td>
                        <td>$89.99</td>
                        <td>2024-01-14</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

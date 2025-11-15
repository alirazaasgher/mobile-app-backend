@extends('admin.layouts.app')
@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div
            class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">12,345</p>
                    <p class="text-sm text-green-600 mt-1">+5.2% from last month</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">$98,456</p>
                    <p class="text-sm text-green-600 mt-1">+12.1% from last month</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Orders</p>
                    <p class="text-3xl font-bold text-gray-900">2,847</p>
                    <p class="text-sm text-red-600 mt-1">-2.4% from last month</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                    <p class="text-3xl font-bold text-gray-900">1,234</p>
                    <p class="text-sm text-green-600 mt-1">+8.7% from last hour</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Chart Section -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Overview</h3>
            <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                <p class="text-gray-500">Chart placeholder - integrate with your preferred charting library</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <p class="text-sm text-gray-700">New user registered: jane.doe@email.com</p>
                    <span class="text-xs text-gray-500">2 min ago</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <p class="text-sm text-gray-700">Order #12345 was completed</p>
                    <span class="text-xs text-gray-500">5 min ago</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                    <p class="text-sm text-gray-700">Payment pending for order #12344</p>
                    <span class="text-xs text-gray-500">12 min ago</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <p class="text-sm text-gray-700">System maintenance scheduled</p>
                    <span class="text-xs text-gray-500">1 hour ago</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order
                            ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#12345</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">John Smith</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Completed</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$299.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-15</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#12344</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Sarah Johnson</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$159.50</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-14</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#12343</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Mike Wilson</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Processing</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$89.99</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-14</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
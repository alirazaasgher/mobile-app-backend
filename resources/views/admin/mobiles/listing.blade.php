@extends('admin.layouts.app')
@section('content')
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
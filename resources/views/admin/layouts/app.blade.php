<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/mobile.js') }}"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">
    <!-- Sidebar -->
    @include('admin.layouts.sidebar')

    <!-- Main content area -->
    <div class="flex-1 flex flex-col">
        <!-- Header -->
        @include('admin.layouts.header')
        <!-- Page Content -->
        <main class="p-6 flex-1">
            @yield('content')
        </main>
    </div>
</body>
</html>

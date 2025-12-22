<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <link href="{{ asset('css/summernote.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/quill_snow.css') }}" rel="stylesheet">
    <script src="{{ asset('js/quill.js') }}"></script>

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
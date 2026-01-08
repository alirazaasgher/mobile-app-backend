<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mobile.css') }}" rel="stylesheet">
    <script src="{{ asset('css/select2.css') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <link href="{{ asset('css/summernote.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/quill_snow.css') }}" rel="stylesheet">
    <script src="{{ asset('js/quill.js') }}"></script>

    <script src="{{ asset('js/mobile.js') }}"></script>
</head>

<body class="bg-light min-vh-100 d-flex">

    <!-- Sidebar -->
    <div class="bg-dark text-white vh-100" style="width: 250px; overflow-y: auto;">
        @include('admin.layouts.sidebar')
    </div>

    <!-- Right content -->
    <div class="flex-grow-1 d-flex flex-column">

        <!-- Header -->
        @include('admin.layouts.header')

        <!-- Main content -->
        <main class="p-4 flex-grow-1 overflow-auto">
            @yield('content')
        </main>
    </div>
</body>



</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/mobile.css') }}" rel="stylesheet">
    <link src="{{ asset('css/select2.css') }}" rel="stylesheet">
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <link href="{{ asset('css/summernote.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/quill_snow.css') }}" rel="stylesheet">
    <script src="{{ asset('js/quill.js') }}"></script>

    <script src="{{ asset('js/mobile.js?v=1') }}"></script>
</head>

<body class="bg-light">

<div class="d-flex min-vh-100">

    <!-- LEFT SIDEBAR (FULL HEIGHT) -->
    @include('admin.layouts.sidebar')

    <!-- RIGHT SIDE -->
    <div class="flex-grow-1 d-flex flex-column">

        <!-- RIGHT HEADER -->
        @include('admin.layouts.header')

        <!-- MAIN CONTENT -->
        <main class="flex-grow-1 p-4">
            @yield('content')
        </main>

    </div>

</div>





</html>

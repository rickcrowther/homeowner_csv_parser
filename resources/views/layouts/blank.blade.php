<html>
<!-- partial:partials/header.html -->
@include('includes/header')
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper">
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('main_container')
                </div>
                <!-- content-wrapper ends -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
</body>

<!-- App Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<!-- Blade Scripts -->
@stack('scripts')
</body>
</html>

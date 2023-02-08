<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<link href="{{ mix('css/app.css') }}" rel="stylesheet"/>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<!-- Site wrapper -->
<div id="app" class="wrapper">


        <!-- Main content -->
        <section class="content">
            @yield('content')
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Footer -->

</div>
<!-- ./wrapper -->
    </body>
</html>

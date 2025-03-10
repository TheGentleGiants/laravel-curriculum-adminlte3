<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('layouts.partials.head')

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed" >
    <div id="app" class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-lime navbar-light">
            <!-- Left navbar links -->

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu"
                       onclick="toggleMenu()"><i class="fas fa-bars"></i></a>
                </li>
                <!--                    <li class="nav-item d-none d-sm-inline-block">
                                        <a href="#" class="nav-link">Contact</a>
                                    </li>-->
            </ul>

            <!-- SEARCH FORM -->
            <!--                <form class="form-inline ml-3">
                                <div class="input-group input-group-sm">
                                    <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>-->

            <!-- Right navbar links -->
            @include('partials.navbar')
        </nav>
    @include('partials.topmenu')
        <!-- /.navbar -->
    <!-- Content Header (Page header) -->
        <section class="content-header p-2">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-8 pl-0">
                        <h1> @yield('title')</h1>
                    </div>
                    <div class="col-sm-4 pr-0">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <div class="pl-2 pr-2" style="padding-bottom:50px">
            @yield('content')
        </div>
            <!-- Footer -->
            @include('partials.footer', ['contentonly' => true])

    </div>

    <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
    <script src="{{ asset('node_modules/tinymce/tinymce.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>

    @yield('scripts')

</body>

</html>

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ URL::to('/home') }}" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ URL::to('/users') }}" class="nav-link {{ Request::is('users') ? 'active' : '' }}">
                Users
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ URL::to('/divisions') }}" class="nav-link {{ Request::is('divisions') ? 'active' : '' }}">
                Divisions
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ URL::to('/positions') }}" class="nav-link {{ Request::is('positions') ? 'active' : '' }}">
                Positions
            </a>
        </li>
    </ul>
</nav>

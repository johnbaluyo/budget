<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a class="brand-link text-decoration-none" href="index3.html">
        <img class="brand-image img-circle elevation-3" src="{{ asset('psrti_logo_new.png') }}" alt="PSRTI Logo"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Budget Tracking</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img class="img-circle elevation-2" src="{{ asset('siwawa.jpg') }}" alt="User Image">
            </div>
            <div class="info">
                <a class="d-block text-decoration-none" href="#">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="false"
                role="menu">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('approvedbudget') ? 'active' : '' }}"
                        href="{{ URL::to('/approvedbudget') }}">
                        <i class="nav-icon fas fa">₱</i>
                        {{-- <i class="nav-icon fas fa-hand-holding-usd"></i> --}}
                        <p>
                            Approved Budget
                        </p>
                    </a>
                </li>
                <hr class="bg-light">
                <div class="form-inline w-100">
                    <div class="input-group w-100">
                        <div class="input-group-append">
                            <span class="btn btn-sidebar btn-secondary">
                                Year:
                            </span>
                        </div>
                        @php
                            $years = App\ApprovedBudget::orderBy('year', 'asc')->pluck('year');
                            $currentYear = now()->year;
                            $selectedYear = request('year', session('selected_year', $currentYear));
                            session(['selected_year' => $selectedYear]);
                            if (!$years->contains($selectedYear)) {
                                $selectedYear = $years->last();
                                session(['selected_year' => $selectedYear]);
                            }
                        @endphp
                        <form class="w-100" id="yearFilterForm" method="GET"
                            action="{{ URL::to('gaa') }}/{{ $selectedYear }}">
                            <select class="form-select w-100" id="year" name="year"
                                onchange="document.getElementById('yearFilterForm').submit();">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div><br>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('gaa/*') ? 'active' : '' }}"
                        href="{{ URL::to('/gaa') }}/{{ $selectedYear }}">
                        {{-- <i class="nav-icon fas fa-hand-holding-usd"></i> --}}
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p>
                            GAA
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('projects/*') ? 'active' : '' }}"
                        href="{{ URL::to('/projects') }}/{{ $selectedYear }}">
                        {{-- <i class="nav-icon fas fa-project-diagram"></i> --}}
                        <i class="nav-icon fas fa-project-diagram"></i>
                        <p>
                            PROJECTS
                        </p>
                    </a>
                </li>
                <hr class="bg-light">
                <li class="nav-header">
                    <h6>
                        <center>BUDGET TRACKING</center>
                    </h6>
                </li>
                @php
                    $approved_budget = App\ApprovedBudget::where('year', $selectedYear)->first();
                    $projects = App\Project::where('approved_budget_id', $approved_budget->id ?? '')->get();
                @endphp
                @foreach ($projects as $project)
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('project/' . $project->id) ? 'active' : '' }}"
                            href="{{ route('project', ['id' => $project->id, 'year' => request('year', $selectedYear ?? '')]) }}">
                            <i class="nav-icon fas fa-caret-right"></i>
                            <p>{{ $project->project_name }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
        <hr class="bg-light">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="false"
                role="menu">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        Logout
                    </a>

                    <form class="d-none" id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>

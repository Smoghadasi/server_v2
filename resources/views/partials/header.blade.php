<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="menu-item menu-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="menu-item d-flex align-items-center">
                @if(in_array('searchAll',auth()->user()->userAccess))
                    <form class="d-flex" action="{{ route('admin.searchAll') }}" method="get">
                        {{-- @csrf --}}
                        <input type="text" class="form-control border-0 shadow-none" name="title" placeholder="جستجو..." aria-label="Search...">
                    </form>
                @endif
            </div>
        </div>
        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center mr-auto f-ir">
            <!-- Place this tag where you want the button to render. -->
            <li class="menu-item lh-1 me-3 f-ir">
                <a class="f-ir" href="#"> {{ Auth::user()->name }} {{ Auth::user()->lastName }}</a>
            </li>
            <!-- User -->
            <li class="menu-item navbar-dropdown dropdown-user dropdown">
                <a class="menu-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('pictures/users/user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end new-style-13">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('pictures/users/user.png') }}" alt
                                            class="w-px-40 h-auto rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">
                                        {{ auth()->user()->name }} {{ auth()->user()->lastName }}
                                    </span>
                                    <small class="text-muted">
                                        @if (auth()->user()->role == ROLE_ADMIN)
                                            مدیر
                                        @elseif(auth()->user()->role == ROLE_OPERATOR)
                                            کارشناس
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ url('admin/profile') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">پروفایل من</span>
                        </a>
                        <li>
                            <a class="dropdown-item" href="{{ route('web-notification.index') }}">
                                <i class="bx bx-bell me-2"></i>
                                <span class="align-middle">اعلان</span>
                            </a>
                        </li>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ url('admin/logout') }}">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">خروج</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

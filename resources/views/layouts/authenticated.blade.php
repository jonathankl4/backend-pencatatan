<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pencatatan App')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    @stack('styles')
</head>
<body>

    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Appbar -->
    <nav class="navbar navbar-custom sticky-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                @if(isset($showBackButton) && $showBackButton)
                    <button class="btn btn-link text-white text-decoration-none p-0 me-3" 
                        onclick="{{ isset($backUrl) ? 'window.location.href=\''.$backUrl.'\'' : 'history.back()' }}">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </button>
                @else
                    <button class="navbar-toggler border-0 shadow-none px-2 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#appDrawer" aria-controls="appDrawer">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                @endif
                <span class="navbar-brand mb-0 h1">@yield('title')</span>
            </div>
            <div>
                @stack('appbar_actions')
            </div>
        </div>
    </nav>

    <!-- Drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="appDrawer" aria-labelledby="appDrawerLabel">
        <div class="offcanvas-header flex-column align-items-start">
            <h5 class="offcanvas-title mb-2" id="appDrawerLabel">Pencatatan</h5>
            <p class="mb-0 text-white-50" id="drawer-user-email">user@example.com</p>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-2">
            <div class="nav flex-column">
                <a href="/dashboard" class="nav-link-custom {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                
                <a href="/expenses" class="nav-link-custom {{ request()->is('expenses*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Pengeluaran
                </a>
                <a href="/sales" class="nav-link-custom {{ request()->is('sales*') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i> Penjualan
                </a>
                
            </div>
            
            <hr class="my-3">
            
            <div class="nav flex-column">
                <a href="#" class="nav-link-custom text-danger" id="btnLogout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    @yield('content')

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.1/sweetalert2.all.min.js"></script>
    
    <script>
        // Global AJAX setup
        $.ajaxSetup({
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        function showLoading() {
            $('#loading-overlay').css('display', 'flex');
        }

        function hideLoading() {
            $('#loading-overlay').hide();
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: 'var(--primary)'
            });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1500,
                showConfirmButton: false
            });
        }

        // Intercept 401 Unauthorized globally
        $(document).ajaxError(function(event, jqXHR) {
            if (jqXHR.status === 401) {
                localStorage.removeItem('token');
                window.location.href = '/login';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const email = localStorage.getItem('user_email');
            if(email) {
                document.getElementById('drawer-user-email').innerText = email;
            }

            const btnLogout = document.getElementById('btnLogout');
            if (btnLogout) {
                btnLogout.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Logout',
                        text: "Are you sure you want to logout?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F44336',
                        cancelButtonColor: '#757575',
                        confirmButtonText: 'Yes, logout'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showLoading();
                            $.ajax({
                                url: '/api/logout',
                                type: 'POST',
                                success: function() {
                                    localStorage.removeItem('token');
                                    localStorage.removeItem('user_email');
                                    window.location.href = '/login';
                                },
                                error: function() {
                                    localStorage.removeItem('token');
                                    localStorage.removeItem('user_email');
                                    window.location.href = '/login';
                                }
                            });
                        }
                    });
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>

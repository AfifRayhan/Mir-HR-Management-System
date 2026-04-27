<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            font-family: 'Figtree', sans-serif !important;
            border-radius: 1rem !important;
        }
        .swal2-styled.swal2-confirm {
            background-color: #10b981 !important; /* success color */
            border-radius: 0.5rem !important;
            padding: 0.5rem 1.5rem !important;
            font-weight: 600 !important;
        }
        .swal2-styled.swal2-cancel {
            border-radius: 0.5rem !important;
            padding: 0.5rem 1.5rem !important;
            font-weight: 600 !important;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased"
    @if(session('success')) data-flash-success="{{ session('success') }}" @endif
    @if(session('error')) data-flash-error="{{ session('error') }}" @endif
    @if(session('warning')) data-flash-warning="{{ session('warning') }}" @endif
    @if(session('info')) data-flash-info="{{ session('info') }}" @endif
>
    <!-- Main app content (navigation handled inside individual pages like the HR dashboard) -->
    <main>
        {{ $slot }}
    </main>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Handle Flash Messages
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            ['success', 'error', 'warning', 'info'].forEach(type => {
                const message = document.body.getAttribute('data-flash-' + type);
                if (message) {
                    Toast.fire({
                        icon: type,
                        title: message
                    });
                }
            });

            // 2. Handle Confirmation Dialogs
            document.body.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.hasAttribute('data-confirm')) {
                    e.preventDefault();
                    const message = form.getAttribute('data-confirm-message') || 'Are you sure you want to proceed?';
                    const title = form.getAttribute('data-confirm-title') || 'Confirmation Required';
                    const type = form.getAttribute('data-confirm-type') || 'warning';

                    Swal.fire({
                        title: title,
                        text: message,
                        icon: type,
                        showCancelButton: true,
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'No, cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Temporary remove the attribute to avoid infinite loop
                            const originalAttr = form.getAttribute('data-confirm');
                            form.removeAttribute('data-confirm');
                            form.submit();
                            form.setAttribute('data-confirm', originalAttr);
                        }
                    });
                }
            });

            // Handle Direct Click Confirmations (for links/buttons not in forms)
            document.body.addEventListener('click', function(e) {
                const el = e.target.closest('[data-confirm-click]');
                if (el) {
                    e.preventDefault();
                    const message = el.getAttribute('data-confirm-message') || 'Are you sure?';
                    const href = el.getAttribute('href');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed && href) {
                            window.location.href = href;
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>




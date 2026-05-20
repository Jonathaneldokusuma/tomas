<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tomas - @yield('title', 'Solusi Jasa Terlengkap')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#007AFF',
                        navy: '#1A2B47',
                        gold: '#C9A227',
                    }
                }
            }
        }
    </script>
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #F2F2F7; }
        .phone-wrap { max-width: 430px; margin: 0 auto; min-height: 100vh; background: #fff; position: relative; overflow-x: hidden; }
        .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; z-index: 50; }
        .page-content { padding-bottom: 80px; }
        .nav-icon { display: flex; flex-direction: column; align-items: center; font-size: 10px; gap: 2px; }
        .tomas-logo { font-size: 22px; font-weight: 800; color: #1A2B47; letter-spacing: 1px; }
        .tomas-logo span { color: #C9A227; }
        .logo-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }
        .logo-icon img { width: 44px; height: 44px; object-fit: contain; }
        .logo-full img { height: 90px; object-fit: contain; }
    </style>
    @stack('styles')
</head>
<body>
<div class="phone-wrap">
    @yield('content')
</div>
@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ business_config('business_name', config('app.name', 'Laravel')) }}</title>

        @php
            $favicon = business_config('favicon_url');
            if ($favicon && !str_starts_with($favicon, 'http')) {
                $favicon = '/storage/' . $favicon;
            }
        @endphp
        @if($favicon)
            <link rel="icon" href="{{ $favicon }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
            :root {
                /* Dynamic Colors - Logic Controlled by config/branding/colors.php */
                @foreach(config('branding.colors.css_vars', []) as $key => $data)
                {{ $key }}: {{ $data['key'] ? business_config($data['key'], $data['default']) : $data['default'] }};
                @endforeach

                --font-primary: "{{ business_config('font_primary', config('branding.fonts.primary', 'Instrument Sans')) }}", sans-serif;
                --font-secondary: "{{ business_config('font_secondary', config('branding.fonts.secondary', 'Roboto')) }}", sans-serif;
            }
        </style>

        <script>
            // Initialize Dark Mode based on localStorage -> Default to Light
            (function() {
                try {
                    const persistedTheme = localStorage.getItem('theme');

                    if (persistedTheme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {
                    console.error('Theme initialization failed', e);
                }
            })();
        </script>

        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

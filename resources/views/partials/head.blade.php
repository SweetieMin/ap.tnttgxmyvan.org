<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ asset('storage/images/sites/FAVICON_default.png') }}" sizes="any">
<link rel="icon" href="{{ asset('storage/images/sites/FAVICON_default.png') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('storage/images/sites/FAVICON_default.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link
    href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Manrope:wght@400;500;600&display=swap"
    rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
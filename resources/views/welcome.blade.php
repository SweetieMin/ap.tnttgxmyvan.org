<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AP TNTT') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[linear-gradient(180deg,#fffaf0_0%,#f3fbff_46%,#ffffff_100%)] text-zinc-900 antialiased">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.18),transparent_44%),radial-gradient(circle_at_top_right,rgba(245,158,11,0.22),transparent_34%)]"></div>
            <div class="pointer-events-none absolute -left-20 top-28 h-64 w-64 rounded-full bg-cyan-200/30 blur-3xl"></div>
            <div class="pointer-events-none absolute right-0 top-10 h-72 w-72 rounded-full bg-amber-200/30 blur-3xl"></div>

            <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-6 lg:px-10">
                <header class="flex items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center rounded-full border border-cyan-200 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700 backdrop-blur">
                            {{ __('Hệ thống quản lý giáo lý') }}
                        </div>
                        <div class="mt-3 text-2xl font-extrabold tracking-tight text-zinc-950 lg:text-3xl">
                            {{ config('app.name', 'AP TNTT Giáo xứ Mỹ Vân') }}
                        </div>
                    </div>

                    <nav class="flex items-center gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-full border border-zinc-200 bg-white/90 px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700">
                                {{ __('Đăng nhập') }}
                            </a>
                        @endif
                    </nav>
                </header>

                <main class="grid flex-1 items-center gap-12 py-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] lg:py-20">
                    <section class="space-y-8">
                        <div class="space-y-5">
                            <div class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                                {{ __('Điểm danh, điểm số, lịch học và phân công giảng dạy trong một nơi') }}
                            </div>

                            <div class="max-w-4xl space-y-4">
                                <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-zinc-950 md:text-5xl lg:text-6xl">
                                    {{ __('Quản lý thiếu nhi và lớp học rõ ràng, đúng hạn, dễ theo dõi.') }}
                                </h1>
                                <p class="max-w-2xl text-base leading-8 text-zinc-600 md:text-lg">
                                    {{ __('Theo dõi lịch học từng buổi, ghi nhận điểm tinh thần, lý thuyết, thực hành và phân quyền đúng cho giáo viên, trưởng lớp và thiếu nhi.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-zinc-950 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-zinc-950/15 transition hover:bg-cyan-700">
                                    {{ __('Vào hệ thống') }}
                                </a>
                            @endif
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-3xl border border-white/80 bg-white/80 p-5 shadow-sm backdrop-blur">
                                <div class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ __('Điểm danh') }}</div>
                                <p class="mt-3 text-sm leading-7 text-zinc-600">
                                    {{ __('Mở đúng theo giờ bắt đầu của buổi học và khóa theo hạn nhập điểm đã cấu hình.') }}
                                </p>
                            </div>

                            <div class="rounded-3xl border border-white/80 bg-white/80 p-5 shadow-sm backdrop-blur">
                                <div class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ __('Bảng điểm') }}</div>
                                <p class="mt-3 text-sm leading-7 text-zinc-600">
                                    {{ __('Tính tổng tự động từ TT, LT, TH và hiển thị trạng thái đạt hay chưa đạt cho từng buổi.') }}
                                </p>
                            </div>

                            <div class="rounded-3xl border border-white/80 bg-white/80 p-5 shadow-sm backdrop-blur">
                                <div class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ __('Phân công') }}</div>
                                <p class="mt-3 text-sm leading-7 text-zinc-600">
                                    {{ __('Gắn giáo viên theo môn, theo lớp và giới hạn thao tác đúng theo role, permission.') }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="relative">
                        <div class="absolute inset-0 translate-x-6 translate-y-6 rounded-[2rem] bg-cyan-200/40 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-[2rem] border border-cyan-100 bg-white/90 p-6 shadow-2xl shadow-cyan-950/10 backdrop-blur">
                            <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">{{ __('Bảng điều khiển') }}</p>
                                    <h2 class="mt-2 text-xl font-bold text-zinc-950">{{ __('Một buổi học được quản lý trọn vẹn') }}</h2>
                                </div>

                                <div class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ __('Đang sẵn sàng') }}
                                </div>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl border border-zinc-100 bg-zinc-50 p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-zinc-900">{{ __('Lớp Căn Bản 2026') }}</div>
                                            <div class="mt-1 text-sm text-zinc-500">{{ __('Lý tưởng, tôn chỉ, 10 điều tâm niệm') }}</div>
                                        </div>
                                        <div class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">
                                            {{ __('19:00 - 20:15') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-amber-50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">{{ __('Tinh thần') }}</div>
                                        <div class="mt-3 text-3xl font-extrabold text-amber-900">8.00</div>
                                    </div>
                                    <div class="rounded-2xl bg-sky-50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">{{ __('Lý thuyết') }}</div>
                                        <div class="mt-3 text-3xl font-extrabold text-sky-900">9.00</div>
                                    </div>
                                    <div class="rounded-2xl bg-emerald-50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">{{ __('Thực hành') }}</div>
                                        <div class="mt-3 text-3xl font-extrabold text-emerald-900">8.50</div>
                                    </div>
                                </div>

                                <div class="rounded-3xl border border-zinc-100 bg-white p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-zinc-900">{{ __('Kết quả tự động') }}</div>
                                            <div class="mt-1 text-sm text-zinc-500">{{ __('[((LT + TH) / 2) + TT] / 2') }}</div>
                                        </div>

                                        <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-700">
                                            {{ __('Đạt 8.38') }}
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-3 text-sm text-zinc-600">
                                        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3">
                                            <span>{{ __('Giáo viên được gán mới được nhập điểm') }}</span>
                                            <span class="font-semibold text-zinc-900">{{ __('Có') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3">
                                            <span>{{ __('Khóa điểm khi quá hạn theo ngày và giờ bắt đầu') }}</span>
                                            <span class="font-semibold text-zinc-900">{{ __('Có') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3">
                                            <span>{{ __('Lưu lịch sử cập nhật điểm') }}</span>
                                            <span class="font-semibold text-zinc-900">{{ __('Có') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>

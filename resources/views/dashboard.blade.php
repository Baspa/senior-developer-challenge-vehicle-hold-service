<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="api-key" content="{{ config('hold.api_key') }}">
    <title>Vehicle Soft-Hold</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full font-sans">
    <div x-data="dashboard" class="mx-auto max-w-6xl px-6 py-12">
        <header class="mb-10 flex items-end justify-between gap-6">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Vehicle soft-hold</h1>
                <p class="mt-2 max-w-prose text-sm text-zinc-600 dark:text-zinc-400">
                    Reserve a vehicle for {{ (int) config('hold.ttl_minutes') }} minutes. A second buyer who clicks Reserve while a hold is active gets a 409 with the remaining time.
                </p>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($vehicles as $vehicle)
                @php
                    $hold = $vehicle->activeHold;
                @endphp
                <article
                    x-data="vehicleCard({{ Illuminate\Support\Js::from([
                        'id' => $vehicle->id,
                        'name' => $vehicle->name,
                        'vin' => $vehicle->vin,
                        'hold' => $hold ? [
                            'id' => $hold->id,
                            'buyer_ref' => $hold->buyer_ref,
                            'expires_at' => $hold->expires_at->toIso8601String(),
                        ] : null,
                    ]) }})"
                    class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold" x-text="vehicle.name"></h2>
                            <p class="font-mono text-xs text-zinc-500" x-text="vehicle.vin"></p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="hold
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300'
                                : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300'"
                        >
                            <span class="size-1.5 rounded-full" :class="hold ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                            <span x-text="hold ? 'Reserved' : 'Available'"></span>
                        </span>
                    </div>

                    <div x-show="hold" x-cloak class="rounded-lg bg-zinc-100 px-4 py-3 text-sm dark:bg-zinc-800/60">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Buyer</span>
                            <span class="font-mono text-xs" x-text="hold?.buyer_ref"></span>
                        </div>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Expires in</span>
                            <span class="font-mono text-base tabular-nums" x-text="countdown"></span>
                        </div>
                    </div>

                    <div class="mt-auto flex gap-2">
                        <button
                            x-show="!hold"
                            x-cloak
                            type="button"
                            @click="reserve"
                            :disabled="busy"
                            class="flex-1 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white cursor-pointer"
                        >
                            <span x-text="busy ? 'Reserving…' : 'Reserve'"></span>
                        </button>
                        <button
                            x-show="hold"
                            x-cloak
                            type="button"
                            @click="release"
                            :disabled="busy"
                            class="flex-1 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                        >
                            <span x-text="busy ? 'Releasing…' : 'Release hold'"></span>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

          <div
            x-show="toast"
            x-cloak
            x-transition.opacity
            class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4"
        >
            <div
                class="pointer-events-auto rounded-xl px-4 py-3 text-sm shadow-lg ring-1"
                :class="toastTone === 'error'
                    ? 'bg-rose-600 text-white ring-rose-500/30'
                    : 'bg-zinc-900 text-white ring-white/10 dark:bg-white dark:text-zinc-900'"
            >
                <span x-text="toast"></span>
            </div>
        </div>
    </div>
</body>
</html>
<div class="mt-6 flex flex-wrap justify-center gap-x-4 gap-y-2 text-sm">
    <a href="{{ url('/') }}" class="font-medium text-gray-500 transition hover:text-amber-600 dark:text-gray-400">
        {{ __('Nazad na web stranicu') }}
    </a>

    <form action="{{ filament()->getLogoutUrl() }}" method="post">
        @csrf
        <button type="submit" class="font-medium text-gray-500 transition hover:text-red-600 dark:text-gray-400">
            {{ __('Odjavi se') }}
        </button>
    </form>
</div>

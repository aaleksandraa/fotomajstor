<x-filament-panels::page>
    @unless ($profile?->active)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Vaš profil još nije aktivan. Nakon što popunite podatke, administrator će ga pregledati i objaviti.
        </div>
    @endunless

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" size="lg">
                Sačuvaj profil
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

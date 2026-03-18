<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-x-3">
                <x-filament::icon
                    icon="heroicon-o-qr-code"
                    class="h-6 w-6 text-gray-500 dark:text-gray-400"
                />
                <div>
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Scanner
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Scanner les QR codes pour emprunter ou retourner des outils
                    </p>
                </div>
            </div>

            <x-filament::link
                href="/scanner"
                target="_blank"
                icon="heroicon-m-arrow-top-right-on-square"
                icon-position="after"
            >
                Ouvrir le scanner
            </x-filament::link>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

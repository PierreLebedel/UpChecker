<footer class="bg-base-300 text-base-content">
    <div class="container py-5 footer items-center">
        <aside class="items-center grid-flow-col">
            <x-application-logo class="inline-block h-5 w-auto me-0" />
            <p class="flex justify-start gap-2">
                <span class="font-semibold">{{ config('app.name') }}</span>
                <span>&middot;</span>
                <span>{{ date('Y') }}</span>
                <span>&middot;</span>
                <a href="https://github.com/PierreLebedel/UpChecker" target="_blank" title="Github">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" /></svg>
                </a>
            </p>
            
        </aside> 
        <nav class="grid-flow-col gap-4 sm:justify-self-end">
            <a href="https://laravel.com/" target="_blank">Laravel</a>
            <a href="https://tailwindcss.com/" target="_blank">Tailwind CSS</a>
            <a href="https://daisyui.com/" target="_blank">daisyUI</a>

            
        </nav>
    </div>
</footer>
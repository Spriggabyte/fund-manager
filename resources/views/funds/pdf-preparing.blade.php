<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generating fact sheet') }} — {{ $fund->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                <div id="pdf-pending">
                    <svg class="animate-spin h-10 w-10 mx-auto mb-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-gray-700">Your PDF fact sheet is being generated. This can take up to a minute.</p>
                    <p class="text-sm text-gray-500 mt-2">The download will start automatically when it is ready.</p>
                </div>

                <div id="pdf-failed" class="hidden">
                    <p class="text-red-600 font-medium">PDF generation failed.</p>
                    <p class="text-sm text-gray-500 mt-2" id="pdf-error"></p>
                </div>

                <div class="mt-8">
                    <a href="{{ route('funds.show', $fund) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to fund</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const statusUrl = @json(route('funds.pdf.status', $export));
            let downloaded = false;

            async function poll() {
                try {
                    const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'done' && data.download_url && !downloaded) {
                            downloaded = true;
                            window.location = data.download_url;
                            return;
                        }
                        if (data.status === 'failed') {
                            document.getElementById('pdf-pending').classList.add('hidden');
                            document.getElementById('pdf-failed').classList.remove('hidden');
                            document.getElementById('pdf-error').textContent = data.error || '';
                            return;
                        }
                    }
                } catch (e) {
                    // transient error — keep polling
                }
                setTimeout(poll, 2000);
            }

            poll();
        })();
    </script>
</x-app-layout>

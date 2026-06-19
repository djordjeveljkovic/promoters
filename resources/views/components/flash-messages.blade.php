<div> {{-- This outer div is fine --}}
    {{-- The main conditional for the alert container --}}
    @if (session('success') || session('error') || session('info') || $errors->any())
    <div class="fixed top-5 right-14 z-[100] space-y-3 w-full max-w-sm pointer-events-none">

        {{-- Success Message --}}
        @if (session('success'))
        <div id="session-alert-success"
             class="flex items-start p-4 text-green-800 rounded-lg shadow-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 pointer-events-auto"
             role="alert"
             style="opacity: 0; transition: opacity 0.3s ease-out, transform 0.3s ease-out; transform: translateX(100%);"
             data-timeout="5000"> {{-- Store timeout here --}}
            <svg class="shrink-0 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                {!! session('success') !!}
            </div>
            <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700"
                    data-dismiss-target aria-label="Close"> {{-- Simplified data-dismiss-target --}}
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- General Error Message (from session('error')) --}}
        @if (session('error'))
        <div id="session-alert-error"
             class="flex items-start p-4 text-red-800 rounded-lg shadow-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 pointer-events-auto"
             role="alert"
             style="opacity: 0; transition: opacity 0.3s ease-out, transform 0.3s ease-out; transform: translateX(100%);"
             data-timeout="7000"> {{-- Store timeout here --}}
            <svg class="shrink-0 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a9 9 0 100 18A9 9 0 0010 1zm-1.707 5.293a1 1 0 011.414 0L10 8.586l.293-.293a1 1 0 111.414 1.414L11.414 10l.293.293a1 1 0 01-1.414 1.414L10 11.414l-.293.293a1 1 0 01-1.414-1.414L8.586 10l-.293-.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                {!! session('error') !!}
            </div>
            <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"
                    data-dismiss-target aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Info Message --}}
        @if (session('info'))
        <div id="session-alert-info"
             class="flex items-start p-4 text-blue-800 rounded-lg shadow-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 pointer-events-auto"
             role="alert"
             style="opacity: 0; transition: opacity 0.3s ease-out, transform 0.3s ease-out; transform: translateX(100%);"
             data-timeout="5000"> {{-- Store timeout here --}}
             <svg class="shrink-0 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                {!! session('info') !!}
            </div>
            <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700"
                    data-dismiss-target aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Validation Errors (from $errors bag) --}}
        @if ($errors->any())
        <div id="validation-errors-alert"
             class="flex items-start p-4 text-red-800 rounded-lg shadow-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 pointer-events-auto"
             role="alert"
             style="opacity: 0; transition: opacity 0.3s ease-out, transform 0.3s ease-out; transform: translateX(100%);"
             data-timeout="10000"> {{-- Store timeout here --}}
            <svg class="shrink-0 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a9 9 0 100 18A9 9 0 0010 1zm-1.707 5.293a1 1 0 011.414 0L10 8.586l.293-.293a1 1 0 111.414 1.414L11.414 10l.293.293a1 1 0 01-1.414 1.414L10 11.414l-.293.293a1 1 0 01-1.414-1.414L8.586 10l-.293-.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                <p class="font-bold mb-1">Please correct the following issues:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"
                    data-dismiss-target aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        @endif
    </div>
    @endif

    <script>
        function initializeAlert(alertElement) {
            if (!alertElement || alertElement.dataset.initialized === 'true') {
                return;
            }

            alertElement.dataset.initialized = 'true';

            requestAnimationFrame(() => {
                alertElement.style.opacity = '1';
                alertElement.style.transform = 'translateX(0)';
            });

            const timeoutDuration = parseInt(alertElement.dataset.timeout) || 5000;

            const autoDismissTimer = setTimeout(() => {
                dismissAlert(alertElement);
            }, timeoutDuration);

            const closeButton = alertElement.querySelector('[data-dismiss-target]');
            if (closeButton) {
                const newButton = closeButton.cloneNode(true);
                closeButton.parentNode.replaceChild(newButton, closeButton);

                newButton.addEventListener('click', function () {
                    clearTimeout(autoDismissTimer);
                    dismissAlert(alertElement);
                });
            }
        }

        function dismissAlert(alertElement) {
            if (alertElement) {
                alertElement.style.opacity = '0';
                alertElement.style.transform = 'translateX(100%)';
                alertElement.addEventListener('transitionend', () => {
                    alertElement.remove();
                }, { once: true });
            }
        }

        function findAndInitializeAllAlerts() {
            initializeAlert(document.getElementById('session-alert-success'));
            initializeAlert(document.getElementById('session-alert-error'));
            initializeAlert(document.getElementById('session-alert-info'));
            initializeAlert(document.getElementById('validation-errors-alert'));
        }

        document.addEventListener('DOMContentLoaded', function () {
            findAndInitializeAllAlerts();
        });

        window.addEventListener('alert-shown', event => {
            setTimeout(findAndInitializeAllAlerts, 50);
        });

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', ({ el, component }) => {
                setTimeout(findAndInitializeAllAlerts, 50);
            });
        }

    </script>
</div>

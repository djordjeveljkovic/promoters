<x-layouts.app>
    <div class="mx-auto dark:bg-zinc-300 rounded-xl px-4 py-6 text-gray-700">
        {{-- Alpine.js komponenta za kontrolu modala --}}
        <div x-data="{ isFeedbackModalOpen: false }" class="">

            {{-- Sekcija 1: Kontakt informacije --}}
            <div class="mb-10">
                <h2 class="text-2xl sm:text-3xl font-semibold text-zinc-700 mb-5 border-b pb-3">Kontakt za podršku</h2>
                <p class="mb-2 text-lg">Za svu pomoć, pitanja ili tehničku podršku, slobodno se obratite Lazaru Naskoviću.</p>
                <p class="mb-2 text-lg">Možete ga kontaktirati putem telefona: <strong class="text-zinc-600 font-medium">+381 63 7363 680</strong></p>
                <p class="mb-2 text-lg">Ili putem email adrese: <strong class="text-zinc-600 font-medium">lazar.buster@gmail.com</strong></p>
                <p class="italic text-gray-600 text-lg mt-3">Tu smo da vam pomognemo!</p>
            </div>

            <hr class="my-8 border-gray-300">

            {{-- Sekcija 2: Instrukcije za prodaju ulaznica --}}
            <div class="instructions mb-10">
                <h2 class="text-2xl sm:text-3xl font-semibold text-zinc-700 mb-5 border-b pb-3">Kako prodati ulaznicu?</h2>
                <div class="space-y-3 text-lg">
                    <p>U polje <strong class="text-zinc-600 font-medium">Email Kupca</strong> upisujete email adresu na koju šaljete ulaznice.</p>
                    <p>Nakon toga, birate <strong class="text-zinc-600 font-medium">tip ulaznice</strong>, birate <strong class="text-zinc-600 font-medium">količinu</strong> i kliknete na dugme <strong class="text-zinc-600 font-medium">dodaj stavku</strong>.</p>
                    <p>U jednoj porudžbini možete dodati <strong class="text-red-600 font-medium">maksimalno 10 ulaznica</strong>.</p>
                    <p>Nakon što dodate ulaznice koje prodajete, kliknite na dugme <strong class="text-zinc-600 font-medium">Izvrši porudžbinu i pošalji ulaznice</strong>.</p>
                    <p>Preusmeriće vas na stranicu <strong class="text-zinc-600 font-medium">Prodate ulaznice</strong> gde možete pratiti status porudžbine.</p>
                    <p>Nakon što status porudžbine iz <em class="italic">"u obradi"</em> pređe u <em class="italic">"završeno"</em>, ulaznice su uspešno poslate i vama se računa komisija.</p>
                </div>
            </div>

            <hr class="my-8 border-gray-300">

            {{-- Dugme za otvaranje modalnog prozora za povratne informacije --}}
            <div class="text-center mt-10">
                <button @click="isFeedbackModalOpen = true"
                        class="bg-zinc-600 hover:bg-zinc-700 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transform hover:scale-105 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-opacity-75">
                    Ostavi povratnu informaciju / Prijavi problem
                </button>
            </div>

<div x-show="isFeedbackModalOpen"
     {{-- Definicije tranzicije samo za "fade" efekat pozadinskog sloja --}}
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0" {{-- Uklonjeno: transform scale-90 --}}
     x-transition:enter-end="opacity-100" {{-- Uklonjeno: transform scale-100 --}}
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100" {{-- Uklonjeno: transform scale-100 --}}
     x-transition:leave-end="opacity-0"   {{-- Uklonjeno: transform scale-90 --}}

     {{-- Dodaje/uklanja 'overflow-hidden' sa body taga da spreči skrolovanje pozadine --}}
     x-effect="isFeedbackModalOpen ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')"
     class="fixed inset-0 bg-zinc-800 bg-opacity-80 flex items-center justify-center z-[9999] p-4"
     @keydown.escape.window="isFeedbackModalOpen = false" {{-- Zatvara modal pritiskom na Escape --}}
     style="display: none;" {{-- Inicijalno sakriven --}}
     >
    <div @click.away="isFeedbackModalOpen = false" {{-- Zatvara modal klikom van ovog diva (na sivu pozadinu) --}}
         class="bg-white p-6 sm:p-8 rounded-xl shadow-2xl w-full max-w-xl max-h-[95vh] overflow-y-auto border-2 border-zinc-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl sm:text-2xl font-semibold text-zinc-700">Pošaljite nam poruku</h3>
            <button @click="isFeedbackModalOpen = false" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
        </div>

        <form id="feedbackForm" action="{{-- /vasa-backend-ruta-za-feedback --}}" method="POST">
            @csrf

            <div class="mb-5">
                <label for="feedbackSubjectType" class="block text-sm font-medium text-gray-700 mb-1">Tip poruke <span class="text-red-500">*</span></label>
                <select id="feedbackSubjectType" name="subject_type" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:border-zinc-500 transition duration-150">
                    <option value="" disabled selected>Izaberite tip...</option>
                    <option value="pomoc">Potrebna mi je pomoć</option>
                    <option value="greska">Prijavljujem grešku</option>
                    <option value="sugestija">Imam sugestiju</option>
                    <option value="ostalo">Ostalo</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="feedbackMessage" class="block text-sm font-medium text-gray-700 mb-1">Poruka <span class="text-red-500">*</span></label>
                <textarea id="feedbackMessage" name="message" rows="6" required
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:border-zinc-500 transition duration-150"
                          placeholder="Unesite vašu poruku ovde..."></textarea>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" @click="isFeedbackModalOpen = false"
                        class="w-full sm:w-auto px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    Odustani
                </button>
                <button type="submit"
                        class="w-full lg:w-fit h-fit px-6 py-2.5 text-sm font-medium text-white bg-zinc-600 hover:bg-zinc-700 border border-transparent rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500">
                    Pošalji poruku
                </button>
            </div>
        </form>
    </div>
</div>
        </div>
    </div>
</x-layouts.app>

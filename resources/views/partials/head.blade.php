<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<style>
input[type="text"],
select {
    background-color: #F9FAFB; /* bg-gray-50 */
    border: 1px solid #D1D5DB; /* border border-gray-300 */
    color: #111827; /* text-gray-900 */
    font-size: 0.875rem; /* 14px, text-sm */
    border-radius: 0.5rem; /* rounded-lg */
    display: block;
    width: 100%;
    padding: 0.625rem; /* p-2.5 (10px) */
    box-sizing: border-box; /* Ensures padding and border don't increase total width/height */
    -webkit-appearance: none; /* Removes default browser styling for select */
    -moz-appearance: none;    /* Removes default browser styling for select */
    appearance: none;         /* Removes default browser styling for select */
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; /* Smooth transitions */
}

.datatable-top {
    display: flex;
    width: 100%;
    justify-content: space-between;
    margin-bottom:10px;
}

.datatable-bottom {
    margin-top: 15px;
}

.datatable-dropdown{
    display: flex;
    width: fit-content;
}

.datatable-dropdown label{
    display:flex;
    white-space: preserve nowrap;
    place-items: baseline;
    gap: .8rem
}

/* --- Placeholder for Input (Light Theme) --- */
input::placeholder {
    color: #6B7280; /* A common placeholder color, adjust if needed */
}

/* --- Focus Styles (Light Theme) --- */
input:focus,
select:focus {
    outline: none; /* Remove default outline */
    border-color: #3B82F6; /* focus:border-blue-500 */
    box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); /* focus:ring-blue-500 (approximated with box-shadow) */
}

/* --- Specific styles for Select to add dropdown arrow --- */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.625rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em; /* Adjust size as needed */
    padding-right: 2.5rem; /* Make space for the arrow */
}


/* --- Dark Theme Styles --- */
@media (prefers-color-scheme: dark) {
    input,
    select {
        background-color: #374151; /* dark:bg-gray-700 */
        border-color: #4B5563;    /* dark:border-gray-600 */
        color: #FFFFFF;           /* dark:text-white */
    }

    input::placeholder {
        color: #9CA3AF;           /* dark:placeholder-gray-400 */
    }

    input:focus,
    select:focus {
        border-color: #3B82F6;    /* dark:focus:border-blue-500 */
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); /* dark:focus:ring-blue-500 (approximated) */
    }

    /* --- Select arrow for dark mode --- */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239CA3AF' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }
}
</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

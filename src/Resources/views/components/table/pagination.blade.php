@props([
    /**
     * Vue expressions (evaluated in the host component's scope) — passed as strings:
     *   page  : current page         e.g. "currentPage", "modalPage"
     *   total : total pages          e.g. "totalPages", "modalLastPage"
     *   prev  : run on previous       e.g. "currentPage--", "fetchModalProducts(modalPage - 1)"
     *   next  : run on next           e.g. "currentPage++", "fetchModalProducts(modalPage + 1)"
     */
    'page',
    'total',
    'prev',
    'next',
])

<div
    v-if="{{ $total }} > 1"
    {{ $attributes->merge(['class' => 'flex items-center gap-1.5 text-sm']) }}
>
    <button
        type="button"
        class="grid h-8 w-8 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        :class="{ 'cursor-not-allowed opacity-50': {{ $page }} === 1 }"
        :disabled="{{ $page }} === 1"
        @click="{{ $prev }}"
    >
        <span class="icon-sort-left rtl:icon-sort-right text-xl"></span>
    </button>

    <span class="px-1 text-gray-600 dark:text-gray-300" v-text="{{ $page }} + ' / ' + {{ $total }}"></span>

    <button
        type="button"
        class="grid h-8 w-8 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        :class="{ 'cursor-not-allowed opacity-50': {{ $page }} === {{ $total }} }"
        :disabled="{{ $page }} === {{ $total }}"
        @click="{{ $next }}"
    >
        <span class="icon-sort-right rtl:icon-sort-left text-xl"></span>
    </button>
</div>

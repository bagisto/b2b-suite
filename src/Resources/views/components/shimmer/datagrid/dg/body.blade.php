{{--
    Loading skeleton for the shared B2B multi-row datagrid body (`b2b-dg-grid`): ten rows of
    four aligned columns that mirror the real record layout — a title + status block, two
    stacked label/value groups, and a content + action column. The grid template lives in the
    consuming view's scoped style block; bar sizes are inline to survive the Tailwind purge.
--}}
@for ($i = 0; $i < 10; $i++)
    <div class="b2b-dg-grid border-b px-4 py-4 dark:border-gray-800">
        {{-- Primary: title, subtitle, status pill --}}
        <div class="flex flex-col gap-2">
            <div class="shimmer" style="height: 19px; width: 70%; max-width: 160px;"></div>

            <div class="shimmer" style="height: 14px; width: 55%; max-width: 120px;"></div>

            <div class="shimmer rounded-full" style="height: 22px; width: 72px;"></div>
        </div>

        {{-- Second column: three label / value pairs --}}
        <div class="b2b-dg-divider flex flex-col gap-3">
            @for ($j = 0; $j < 3; $j++)
                <div class="flex flex-col gap-1">
                    <div class="shimmer" style="height: 10px; width: 60px;"></div>

                    <div class="shimmer" style="height: 16px; width: 80%; max-width: 150px;"></div>
                </div>
            @endfor
        </div>

        {{-- Third column: three label / value pairs --}}
        <div class="b2b-dg-divider flex flex-col gap-3">
            @for ($j = 0; $j < 3; $j++)
                <div class="flex flex-col gap-1">
                    <div class="shimmer" style="height: 10px; width: 72px;"></div>

                    <div class="shimmer" style="height: 16px; width: 60%; max-width: 110px;"></div>
                </div>
            @endfor
        </div>

        {{-- Fourth column: content block + action button --}}
        <div class="b2b-dg-divider flex items-start justify-between gap-2">
            <div class="flex flex-col gap-1.5">
                <div class="shimmer" style="height: 16px; width: 120px;"></div>

                <div class="shimmer" style="height: 14px; width: 90px;"></div>
            </div>

            <div class="shimmer rounded-md" style="height: 36px; width: 36px;"></div>
        </div>
    </div>
@endfor

{{--
    Loading skeleton for the company-credit ledger datagrid body (`b2b-credit-grid`): ten rows
    of five aligned columns (date, operation, amount, balance, details). Grid template comes
    from the view's scoped style block; bar sizes are inline to survive the Tailwind purge.
--}}
@for ($i = 0; $i < 10; $i++)
    <div class="b2b-credit-grid border-b px-4 py-4 dark:border-gray-800">
        {{-- Date --}}
        <div class="flex flex-col gap-1.5">
            <div
                class="shimmer"
                style="height: 16px; width: 90px;"
            ></div>

            <div
                class="shimmer"
                style="height: 12px; width: 60px;"
            ></div>
        </div>

        {{-- Operation --}}
        <div class="b2b-credit-divider flex flex-col gap-1.5">
            <div
                class="shimmer"
                style="height: 16px; width: 80%; max-width: 120px;"
            ></div>
        </div>

        {{-- Amount --}}
        <div class="b2b-credit-divider flex flex-col gap-1.5">
            <div
                class="shimmer"
                style="height: 16px; width: 70px;"
            ></div>
        </div>

        {{-- Balance --}}
        <div class="b2b-credit-divider flex flex-col gap-1.5">
            <div
                class="shimmer"
                style="height: 16px; width: 70px;"
            ></div>
        </div>

        {{-- Details --}}
        <div class="b2b-credit-divider flex flex-col gap-1.5">
            <div
                class="shimmer"
                style="height: 14px; width: 90%; max-width: 160px;"
            ></div>

            <div
                class="shimmer"
                style="height: 14px; width: 70%; max-width: 120px;"
            ></div>
        </div>
    </div>
@endfor

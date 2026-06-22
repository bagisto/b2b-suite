{{--
    Loading skeleton for the companies multi-row datagrid body (`b2b-company-grid`): ten rows
    of a select box, an identity block (name + email + status), two stacked label/value groups
    and a right-aligned action column. Grid template comes from the view's scoped style block;
    bar sizes are inline to survive the Tailwind purge.
--}}
@for ($i = 0; $i < 10; $i++)
    <div class="b2b-company-grid border-b px-4 py-4 dark:border-gray-800">
        {{-- Select --}}
        <div
            class="shimmer"
            style="height: 22px; width: 22px;"
        ></div>

        {{-- Identity: name, email, status pill --}}
        <div class="flex flex-col gap-2">
            <div
                class="shimmer"
                style="height: 19px; width: 80%; max-width: 170px;"
            ></div>

            <div
                class="shimmer"
                style="height: 14px; width: 60%; max-width: 130px;"
            ></div>

            <div
                class="shimmer rounded-full"
                style="height: 22px; width: 72px;"
            ></div>
        </div>

        {{-- Contact: two label / value pairs --}}
        <div class="b2b-company-divider flex flex-col gap-3">
            @for ($j = 0; $j < 2; $j++)
                <div class="flex flex-col gap-1">
                    <div
                        class="shimmer"
                        style="height: 10px; width: 55px;"
                    ></div>

                    <div
                        class="shimmer"
                        style="height: 16px; width: 80%; max-width: 150px;"
                    ></div>
                </div>
            @endfor
        </div>

        {{-- Details: three label / value pairs --}}
        <div class="b2b-company-divider flex flex-col gap-3">
            @for ($j = 0; $j < 3; $j++)
                <div class="flex flex-col gap-1">
                    <div
                        class="shimmer"
                        style="height: 10px; width: 70px;"
                    ></div>

                    <div
                        class="shimmer"
                        style="height: 16px; width: 65%; max-width: 120px;"
                    ></div>
                </div>
            @endfor
        </div>

        {{-- Actions --}}
        <div class="b2b-company-divider flex items-start justify-end gap-2">
            <div
                class="shimmer rounded-md"
                style="height: 36px; width: 36px;"
            ></div>

            <div
                class="shimmer rounded-md"
                style="height: 36px; width: 36px;"
            ></div>
        </div>
    </div>
@endfor

{{--
    Loading skeleton for the shop account datagrids' mobile card view (users, roles,
    requisitions): a bordered card with a header row (id + status), a few stacked field lines
    and a footer row. Bar sizes are inline so they survive the B2B bundle's Tailwind purge.
--}}
@for ($i = 0; $i < 6; $i++)
    <div class="mb-4 w-full rounded-md border p-4 last:mb-0 dark:border-gray-800">
        {{-- Header: id + status pill --}}
        <div class="flex justify-between">
            <div class="shimmer" style="height: 16px; width: 80px;"></div>

            <div class="shimmer rounded-full" style="height: 20px; width: 64px;"></div>
        </div>

        {{-- Body: title + field lines --}}
        <div class="mt-3 grid gap-2.5">
            <div class="shimmer" style="height: 16px; width: 60%; max-width: 180px;"></div>

            <div class="shimmer" style="height: 13px; width: 75%; max-width: 220px;"></div>

            <div class="shimmer" style="height: 13px; width: 50%; max-width: 160px;"></div>

            <div class="shimmer" style="height: 13px; width: 55%; max-width: 170px;"></div>
        </div>

        {{-- Footer: label + flag --}}
        <div class="mt-4 flex justify-between">
            <div class="shimmer" style="height: 13px; width: 90px;"></div>

            <div class="shimmer rounded-full" style="height: 20px; width: 56px;"></div>
        </div>
    </div>
@endfor

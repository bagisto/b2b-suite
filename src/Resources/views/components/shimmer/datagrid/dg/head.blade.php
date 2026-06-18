{{--
    Loading skeleton for the shared B2B multi-row datagrid header (`b2b-dg-head`, a four
    column grid). The grid template lives in each consuming view's scoped style block; this
    only fills the columns with shimmer bars. Like the real header it is hidden below 1024px.
    Bar dimensions use inline styles so they survive the B2B bundle's Tailwind purge.
--}}
<div class="b2b-dg-head border-b px-4 py-2.5 dark:border-gray-800">
    <div>
        <div class="shimmer" style="height: 16px; width: 70%; max-width: 150px;"></div>
    </div>

    <div class="b2b-dg-divider">
        <div class="shimmer" style="height: 16px; width: 72%; max-width: 150px;"></div>
    </div>

    <div class="b2b-dg-divider">
        <div class="shimmer" style="height: 16px; width: 64%; max-width: 130px;"></div>
    </div>

    <div class="b2b-dg-divider">
        <div class="shimmer" style="height: 16px; width: 50%; max-width: 90px;"></div>
    </div>
</div>

{{--
    Loading skeleton for the companies multi-row datagrid header: a leading select
    column plus four aligned columns.

    `b2b-datagrid-head` carries the chrome, defined per area in this package's own
    `admin.css` / `shop.css`, so it cannot drift from the real header.
    `b2b-company-head` adds only the grid template and the breakpoint it is hidden
    at, which live in the consuming view's scoped style block.
--}}
<div class="b2b-company-head b2b-datagrid-head">
    <div class="shimmer h-[26px] w-6"></div>

    <div>
        <div class="shimmer h-[17px] w-[70%] max-w-[150px]"></div>
    </div>

    <div class="b2b-company-divider">
        <div class="shimmer h-[17px] w-[60%] max-w-[120px]"></div>
    </div>

    <div class="b2b-company-divider">
        <div class="shimmer h-[17px] w-[60%] max-w-[120px]"></div>
    </div>

    <div class="b2b-company-divider">
        <div class="shimmer h-[17px] w-[50%] max-w-[90px]"></div>
    </div>
</div>

@props(['groups' => [1, 1, 1, 1]])

{{--
    Loading skeleton for the shared B2B multi-row datagrid header.

    `b2b-datagrid-head` carries the chrome, defined per area in this package's own
    `admin.css` / `shop.css`, so the skeleton and the real header are styled from
    one place. `b2b-dg-head` adds only the grid template and the breakpoint it is
    hidden at, from the consuming view's scoped style block — which is why the
    skeleton lines up with that grid without repeating its columns here.

    `groups` mirrors the real header's `columnGroup` array: one entry per column
    group, falsy where that group has no labels. Grids whose last column holds only
    row actions pass `[1, 1, 1, 0]`, so the skeleton leaves that column blank
    instead of promising a heading the table never renders.
--}}
<div class="b2b-dg-head b2b-datagrid-head">
    @foreach ($groups as $index => $group)
        <div @class(['b2b-dg-divider' => $index > 0])>
            @if ($group)
                <div @class([
                    'shimmer h-[17px]',
                    'w-[70%] max-w-[150px]' => ! $index,
                    'w-[64%] max-w-[130px]' => (bool) $index,
                ])></div>
            @endif
        </div>
    @endforeach
</div>

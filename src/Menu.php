<?php

namespace Webkul\B2BSuite;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Webkul\Core\Menu as BaseMenu;
use Webkul\Core\Menu\MenuItem;

class Menu extends BaseMenu
{
    /**
     * Menu items.
     */
    protected array $items = [];

    /**
     * Config menu.
     */
    protected array $configMenu = [];

    /**
     * Current item key.
     */
    protected string $currentKey = '';

    /**
     * Get all menu items.
     */
    public function getItems(?string $area = null): Collection
    {
        if (! $area) {
            throw new \Exception('Area must be provided to get menu items.');
        }

        $configMenu = collect(config("menu.$area"));

        switch ($area) {
            case self::ADMIN:
                $this->configMenu = $configMenu
                    ->filter(fn ($item) => bouncer()->hasPermission($item['key']))
                    ->toArray();
                break;

            case self::CUSTOMER:
                $canShowWishlist = ! (bool) core()->getConfigData('customer.settings.wishlist.wishlist_option');
                $canShowGdpr = ! (bool) core()->getConfigData('general.gdpr.settings.enabled');

                $this->configMenu = $configMenu
                    ->reject(fn ($item) => ($item['key'] == 'account.wishlist' && $canShowWishlist) ||
                        ($item['key'] == 'account.gdpr_data_request' && $canShowGdpr)
                    )
                    ->filter(function ($item) {
                        $key = $item['key'];
                        $hasPermission = customer_bouncer()->hasPermission($key);

                        return $hasPermission;
                    })
                    ->toArray();

                break;

            default:
                $this->configMenu = $configMenu->toArray();
                break;
        }

        if (! $this->items) {
            $this->prepareMenuItems();
        }

        $filtered = $this->removeUnauthorizedMenuItem();

        return collect($filtered)->sortBy('sort');
    }

    /**
     * Add menu item.
     */
    public function addItem(MenuItem $item): void
    {
        $this->items[$item->getKey()] = $item;
    }

    /**
     * Prepare menu items.
     */
    private function prepareMenuItems(): void
    {
        $menuWithDotNotation = [];

        foreach ($this->configMenu as $item) {
            if (strpos(request()->url(), route($item['route'])) !== false) {
                $this->currentKey = $item['key'];
            }

            $menuWithDotNotation[$item['key']] = $item;
        }

        $menu = Arr::undot(Arr::dot($menuWithDotNotation));

        foreach ($menu as $menuItemKey => $menuItem) {
            $subMenuItems = $this->processSubMenuItems($menuItem);

            $this->addItem(new MenuItem(
                key: $menuItemKey,
                name: trans($menuItem['name']),
                route: $menuItem['route'],
                sort: $menuItem['sort'],
                icon: $menuItem['icon'],
                children: $subMenuItems,
            ));
        }
    }

    /**
     * Process sub menu items.
     */
    private function processSubMenuItems($menuItem): Collection
    {
        return collect($menuItem)
            ->sortBy('sort')
            ->filter(fn ($value) => is_array($value))
            ->map(function ($subMenuItem) {

                $subSubMenuItems = $this->processSubMenuItems($subMenuItem);

                return new MenuItem(
                    key: $subMenuItem['key'],
                    name: trans($subMenuItem['name']),
                    route: $subMenuItem['route'],
                    sort: $subMenuItem['sort'],
                    icon: $subMenuItem['icon'],
                    children: $subSubMenuItems,
                );
            });
    }

    /**
     * Remove unauthorized menu items.
     */
    private function removeUnauthorizedMenuItem(): array
    {
        $filtered = collect($this->items)->map(function ($item) {

            $this->removeChildrenUnauthorizedMenuItem($item);

            return $item;
        })->toArray();

        return $filtered;
    }

    /**
     * Remove unauthorized menuItem's children (recursive).
     */
    private function removeChildrenUnauthorizedMenuItem(MenuItem &$menuItem): void
    {
        if (! $menuItem->haveChildren()) {
            return;
        }

        $firstChild = $menuItem->getChildren()->first();

        $menuItem->route = $firstChild->getRoute();

        $this->removeChildrenUnauthorizedMenuItem($firstChild);
    }
}

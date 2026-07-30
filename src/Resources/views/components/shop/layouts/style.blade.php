{{--
    Injected into the core shop layout head by `Providers/EventServiceProvider`.

    It must stay on `head.before`, not `head.after`: loading a utility sheet after
    the core one lets its plain utilities override core's responsive variants, and
    would also override `@stack('styles')` and the merchant's custom CSS.
--}}
@bagistoVite(['src/Resources/assets/css/shop.css'], 'b2b-suite-shop')

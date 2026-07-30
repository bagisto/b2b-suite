{{--
    Injected into the core admin layout head by `Providers/EventServiceProvider`.

    It must stay on `head.before`, not `head.after`: loading a utility sheet after
    the core one lets its plain utilities override core's responsive variants,
    which previously broke the responsive flash toasts and the admin sidebar.
--}}
@bagistoVite(['src/Resources/assets/css/admin.css'], 'b2b-suite-admin')

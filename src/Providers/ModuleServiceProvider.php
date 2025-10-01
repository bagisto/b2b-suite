<?php

namespace Webkul\B2BSuite\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\B2BSuite\Models\CustomerFlat::class,
        \Webkul\B2BSuite\Models\CompanyAttribute::class,
        \Webkul\B2BSuite\Models\CompanyAttributeValue::class,
        \Webkul\B2BSuite\Models\CompanyAttributeTranslation::class,
        \Webkul\B2BSuite\Models\CompanyAttributeGroup::class,
        \Webkul\B2BSuite\Models\CompanyAttributeOption::class,
        \Webkul\B2BSuite\Models\CompanyAttributeOptionTranslation::class,
        \Webkul\B2BSuite\Models\CompanyAttributeGroupTranslation::class,
        \Webkul\B2BSuite\Models\CompanyRole::class,
        \Webkul\B2BSuite\Models\CustomerQuote::class,
        \Webkul\B2BSuite\Models\CustomerQuoteItem::class,
        \Webkul\B2BSuite\Models\CustomerQuoteQuotation::class,
        \Webkul\B2BSuite\Models\CustomerQuoteMessage::class,
        \Webkul\B2BSuite\Models\CustomerQuoteAttachment::class,
        \Webkul\B2BSuite\Models\CustomerRequisitionList::class,
        \Webkul\B2BSuite\Models\CustomerRequisitionListProduct::class,
    ];
}

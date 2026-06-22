<?php

namespace Webkul\B2BSuite\Providers;

use Webkul\B2BSuite\Models\CompanyAttribute;
use Webkul\B2BSuite\Models\CompanyAttributeGroup;
use Webkul\B2BSuite\Models\CompanyAttributeGroupTranslation;
use Webkul\B2BSuite\Models\CompanyAttributeOption;
use Webkul\B2BSuite\Models\CompanyAttributeOptionTranslation;
use Webkul\B2BSuite\Models\CompanyAttributeTranslation;
use Webkul\B2BSuite\Models\CompanyAttributeValue;
use Webkul\B2BSuite\Models\CompanyCatalog;
use Webkul\B2BSuite\Models\CompanyCatalogProduct;
use Webkul\B2BSuite\Models\CompanyCredit;
use Webkul\B2BSuite\Models\CompanyCreditTransaction;
use Webkul\B2BSuite\Models\CompanyFlat;
use Webkul\B2BSuite\Models\CompanyRole;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Models\CustomerQuoteAttachment;
use Webkul\B2BSuite\Models\CustomerQuoteItem;
use Webkul\B2BSuite\Models\CustomerQuoteMessage;
use Webkul\B2BSuite\Models\CustomerQuoteQuotation;
use Webkul\B2BSuite\Models\CustomerRequisitionList;
use Webkul\B2BSuite\Models\CustomerRequisitionListProduct;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        CompanyFlat::class,
        CompanyAttribute::class,
        CompanyAttributeValue::class,
        CompanyAttributeTranslation::class,
        CompanyAttributeGroup::class,
        CompanyAttributeOption::class,
        CompanyAttributeOptionTranslation::class,
        CompanyAttributeGroupTranslation::class,
        CompanyRole::class,
        CustomerQuote::class,
        CustomerQuoteItem::class,
        CustomerQuoteQuotation::class,
        CustomerQuoteMessage::class,
        CustomerQuoteAttachment::class,
        CustomerRequisitionList::class,
        CustomerRequisitionListProduct::class,
        CompanyCatalog::class,
        CompanyCatalogProduct::class,
        CompanyCredit::class,
        CompanyCreditTransaction::class,
    ];
}

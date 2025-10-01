<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CustomerQuoteAttachment;
use Webkul\Core\Eloquent\Repository;

class CustomerQuoteAttachmentRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CustomerQuoteAttachment::class;
    }
}

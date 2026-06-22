<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Models\CompanyInvitation;
use Webkul\Core\Eloquent\Repository;

class CompanyInvitationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model()
    {
        return CompanyInvitation::class;
    }
}

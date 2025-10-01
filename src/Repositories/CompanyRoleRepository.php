<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Container\Container;
use Webkul\B2BSuite\Contracts\CompanyRole;
use Webkul\Core\Eloquent\Repository;
use Webkul\Customer\Repositories\CustomerRepository;

class CompanyRoleRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected Container $container,
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CompanyRole::class;
    }

    /**
     * Count customers with all access.
     */
    public function countCustomersWithAllAccess(): int
    {
        return $this->customerRepository->getModel()::query()
            ->leftJoin('company_roles', 'customers.company_role_id', '=', 'company_roles.id')
            ->where('company_roles.permission_type', 'all')
            ->get()
            ->count();
    }
}

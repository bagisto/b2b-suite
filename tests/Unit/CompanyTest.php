<?php

namespace Webkul\B2BSuite\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webkul\B2BSuite\Models\Company;

class CompanyTest extends TestCase
{
    /**
     * Test company model creation.
     *
     * @return void
     */
    public function test_company_creation()
    {
        $companyData = [
            'name'   => 'Test Company',
            'email'  => 'test@company.com',
            'phone'  => '+1-555-0123',
            'status' => true,
        ];

        $company = new Company($companyData);

        $this->assertEquals('Test Company', $company->name);
        $this->assertEquals('test@company.com', $company->email);
        $this->assertEquals('+1-555-0123', $company->phone);
        $this->assertTrue($company->status);
    }

    /**
     * Test company full address attribute.
     *
     * @return void
     */
    public function test_company_full_address()
    {
        $company = new Company([
            'address'     => '123 Main St',
            'city'        => 'Test City',
            'state'       => 'TS',
            'country'     => 'Test Country',
            'postal_code' => '12345',
        ]);

        $expectedAddress = '123 Main St, Test City, TS, Test Country 12345';
        $this->assertEquals($expectedAddress, $company->full_address);
    }
}

<?php

namespace Webkul\B2BSuite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webkul\B2BSuite\Repositories\CompanyAttributeRepository;
use Webkul\B2BSuite\Repositories\CompanyAttributeValueRepository;
use Webkul\Core\Rules\Decimal;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Core\Rules\Slug;
use Webkul\Customer\Repositories\CustomerRepository;

class CompanyRequest extends FormRequest
{
    /**
     * Rules.
     *
     * @var array
     */
    protected $rules;

    /**
     * Create a new form request instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CompanyAttributeRepository $companyAttributeRepository,
        protected CompanyAttributeValueRepository $companyAttributeValueRepository,
    ) {}

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $customerId = $this->id ?? null;

        $this->rules = [
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'slug'  => [
                'required',
                new Slug,
                Rule::unique('customers', 'slug')->ignore($customerId),
            ],
            'phone'  => [
                'required',
                new PhoneNumber,
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
        ];

        if (
            is_null($customerId)
            && request()->route()?->getName() !== 'admin.customers.companies.store'
        ) {
            $this->rules['password'] = 'required|min:6|confirmed';
        }

        $attributes = $customerId
            ? $this->customerRepository->find($customerId)->getAllCustomAttributes()
            : $this->companyAttributeRepository->getSignUpAttributes();

        foreach ($attributes as $attribute) {
            if (
                in_array($attribute->code, ['email', 'slug', 'phone'])
                || $attribute->type == 'boolean'
            ) {
                continue;
            }

            $validations = [];

            if (! isset($this->rules[$attribute->code])) {
                $validations[] = $attribute->is_required ? 'required' : 'nullable';
            } else {
                $validations = $this->rules[$attribute->code];
            }

            if (
                $attribute->type == 'text'
                && $attribute->validation
            ) {
                if ($attribute->validation === 'decimal') {
                    $validations[] = new Decimal;
                } elseif ($attribute->validation === 'regex') {
                    $validations[] = 'regex:'.$attribute->regex;
                } else {
                    $validations[] = $attribute->validation;
                }
            }

            if ($attribute->type == 'price') {
                $validations[] = new Decimal;
            }

            if ($attribute->is_unique) {
                array_push($validations, function ($field, $value, $fail) use ($attribute, $customerId) {
                    if (
                        ! $this->companyAttributeValueRepository->isValueUnique(
                            (int) $customerId,
                            $attribute->id,
                            $attribute->column_name,
                            request($attribute->code)
                        )
                    ) {
                        $fail(trans('b2b_suite::app.admin.companies.edit.already-taken', [
                            'name' => ':attribute',
                        ]));
                    }
                });
            }

            $this->rules[$attribute->code] = $validations;
        }

        return $this->rules;
    }
}

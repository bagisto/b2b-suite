<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\B2BSuite\Contracts\CompanyAttribute;
use Webkul\Core\Eloquent\Repository;

class CompanyAttributeRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected CompanyAttributeOptionRepository $companyAttributeOptionRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return CompanyAttribute::class;
    }

    /**
     * Create company attribute.
     */
    public function create(array $data)
    {
        $data = $this->validateUserInput($data);

        $options = $data['options'] ?? [];

        unset($data['options']);

        $attribute = $this->model->create($data);

        if (in_array($attribute->type, [
            AttributeTypeEnum::CHECKBOX->value,
            AttributeTypeEnum::SELECT->value,
            AttributeTypeEnum::MULTISELECT->value,
        ])) {
            foreach ($options as $optionInputs) {
                $this->companyAttributeOptionRepository->create(array_merge([
                    'company_attribute_id' => $attribute->id,
                ], $optionInputs));
            }
        }

        return $attribute;
    }

    /**
     * Update company attribute.
     */
    public function update(array $data, $id)
    {
        $data = $this->validateUserInput($data);

        $attribute = $this->find($id);

        $attribute->update($data);

        if (! in_array($attribute->type, [
            AttributeTypeEnum::CHECKBOX->value,
            AttributeTypeEnum::SELECT->value,
            AttributeTypeEnum::MULTISELECT->value,
        ])) {
            return $attribute;
        }

        if (! isset($data['options'])) {
            return $attribute;
        }

        foreach ($data['options'] as $optionId => $optionInputs) {
            $isNew = $optionInputs['isNew'] == 'true';

            if ($isNew) {
                $this->companyAttributeOptionRepository->create(array_merge([
                    'company_attribute_id' => $attribute->id,
                ], $optionInputs));
            } else {
                $isDelete = $optionInputs['isDelete'] == 'true';

                if ($isDelete) {
                    $this->companyAttributeOptionRepository->delete($optionId);
                } else {
                    $this->companyAttributeOptionRepository->update($optionInputs, $optionId);
                }
            }
        }

        return $attribute;
    }

    /**
     * Validate user input.
     */
    public function validateUserInput(array $data): array
    {
        if (isset($data['is_configurable'])) {
            $data['value_per_channel'] = $data['value_per_locale'] = 0;
        }

        if (! in_array($data['type'], [
            AttributeTypeEnum::CHECKBOX->value,
            AttributeTypeEnum::SELECT->value,
            AttributeTypeEnum::MULTISELECT->value,
            AttributeTypeEnum::BOOLEAN->value,
        ])) {
            $data['is_filterable'] = 0;
        }

        if (in_array($data['type'], [
            AttributeTypeEnum::SELECT->value,
            AttributeTypeEnum::MULTISELECT->value,
            AttributeTypeEnum::BOOLEAN->value,
        ])) {
            unset($data['value_per_locale']);
        }

        return $data;
    }

    /**
     * Get sign up attributes.
     */
    public function getSignUpAttributes(): Collection
    {
        return $this->model
            ->with('translations')
            ->whereHas('attribute_group')
            ->where('is_visible_on_sign_up', 1)
            ->orderBy('position')
            ->get()
            ->append('validations');
    }
}

<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Webkul\B2BSuite\Contracts\CompanyAttributeGroup;
use Webkul\Core\Eloquent\Repository;

class CompanyAttributeGroupRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected CompanyAttributeRepository $companyAttributeRepository,
        protected Container $container,
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return CompanyAttributeGroup::class;
    }

    /**
     * Update the attribute mapping.
     */
    public function updateMapping(array $data): bool
    {
        foreach ($data['attribute_groups'] as &$group) {
            if (
                isset($group['locales'])
                && is_array($group['locales'])
            ) {
                foreach ($group['locales'] as $locale => $value) {
                    $group[$locale] = ['name' => $value];
                }

                unset($group['locales']);
            }
        }

        $previousAttributeGroupIds = $this->pluck('id');

        foreach ($data['attribute_groups'] ?? [] as $attributeGroupId => $attributeGroupInputs) {
            if (Str::contains($attributeGroupId, 'group_')) {
                $attributeGroup = $this->model->create([
                    'code'        => $attributeGroupInputs['code'],
                    'admin_name'  => $attributeGroupInputs['admin_name'],
                    'position'    => $attributeGroupInputs['position'] ?? 0,
                    'column'      => $attributeGroupInputs['column'],
                ]);

                if (empty($attributeGroupInputs['custom_attributes'])) {
                    continue;
                }

                foreach ($attributeGroupInputs['custom_attributes'] as $attributeInputs) {
                    $attribute = $this->companyAttributeRepository->find($attributeInputs['id']);

                    $attributeGroup->custom_attributes()->save($attribute, [
                        'position' => $attributeInputs['position'],
                    ]);
                }
            } else {
                if (is_numeric($index = $previousAttributeGroupIds->search($attributeGroupId))) {
                    $previousAttributeGroupIds->forget($index);
                }

                $attributeGroup = $this->model->find($attributeGroupId);

                $attributeGroup->update($attributeGroupInputs);

                $previousAttributeIds = $attributeGroup->custom_attributes()->get()->pluck('id');

                foreach ($attributeGroupInputs['custom_attributes'] ?? [] as $attributeInputs) {
                    if (is_numeric($index = $previousAttributeIds->search($attributeInputs['id']))) {
                        $previousAttributeIds->forget($index);

                        $attributeGroup->custom_attributes()->updateExistingPivot($attributeInputs['id'], [
                            'position' => $attributeInputs['position'],
                        ]);
                    } else {
                        $attribute = $this->companyAttributeRepository->find($attributeInputs['id']);

                        $attributeGroup->custom_attributes()->save($attribute, [
                            'position' => $attributeInputs['position'],
                        ]);
                    }
                }

                if ($previousAttributeIds->count()) {
                    $attributeGroup->custom_attributes()->detach($previousAttributeIds);
                }
            }
        }

        foreach ($previousAttributeGroupIds as $attributeGroupId) {
            $this->delete($attributeGroupId);
        }

        return true;
    }

    /**
     * Prepare locale data for the attribute groups.
     */
    protected function prepareLocaleData(array $data): array
    {
        return array_map(function ($item) {
            return [
                'code'        => $item['code'],
                'admin_name'  => $item['admin_name'],
                'position'    => $item['position'] ?? 0,
                'column'      => $item['column'],
            ];
        }, $data);
    }
}

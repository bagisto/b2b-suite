<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\Attribute\Enums\ValidationEnum;
use Webkul\B2BSuite\DataGrids\Admin\CompanyAttributeDataGrid;
use Webkul\B2BSuite\Repositories\CompanyAttributeGroupRepository;
use Webkul\B2BSuite\Repositories\CompanyAttributeRepository;
use Webkul\Core\Rules\Code;

class CompanyAttributeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CompanyAttributeRepository $companyAttributeRepository,
        protected CompanyAttributeGroupRepository $companyAttributeGroupRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return datagrid(CompanyAttributeDataGrid::class)->process();
        }

        return view('b2b_suite::admin.company-attributes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('b2b_suite::admin.company-attributes.create')
            ->with([
                'locales'        => core()->getAllLocales(),
                'attributeTypes' => AttributeTypeEnum::getValues(),
                'validations'    => ValidationEnum::getValues(),
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code'          => ['required', 'not_in:type,company_attributes', 'unique:company_attributes,code', new Code],
            'admin_name'    => 'required',
            'type'          => 'required',
            'default_value' => 'in:0,1',
        ]);

        Event::dispatch('b2b_suite.company_attribute.create.before');

        $attribute = $this->companyAttributeRepository->create($request->all());

        Event::dispatch('b2b_suite.company_attribute.create.after', $attribute);

        return to_route('admin.customers.attributes.index')
            ->withSuccess(trans('b2b_suite::app.admin.company-attributes.create-success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $attribute = $this->companyAttributeRepository->with(['options' => function ($query) {
            $query->orderBy('sort_order', 'asc');
        }])->findOrFail($id);

        return view('b2b_suite::admin.company-attributes.edit')
            ->with([
                'attribute'      => $attribute,
                'locales'        => core()->getAllLocales(),
                'attributeTypes' => AttributeTypeEnum::getValues(),
                'validations'    => ValidationEnum::getValues(),
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'code'          => ['required', 'not_in:type,company_attributes', 'unique:company_attributes,code,'.$id, new Code],
            'admin_name'    => 'required',
            'type'          => 'required',
            'default_value' => 'in:0,1',
        ]);

        Event::dispatch('b2b_suite.company_attribute.update.before', $id);

        $attribute = $this->companyAttributeRepository->update($request->all(), $id);

        Event::dispatch('b2b_suite.company_attribute.update.after', $attribute);

        return to_route('admin.customers.attributes.index')
            ->withSuccess(trans('b2b_suite::app.admin.company-attributes.update-success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $attribute = $this->companyAttributeRepository->findOrFail($id);

        if (! $attribute->is_user_defined) {
            return response()->json([
                'message' => trans('admin::app.catalog.attributes.user-define-error'),
            ], 400);
        }

        try {
            Event::dispatch('b2b_suite.company_attribute.delete.before', $id);

            $this->companyAttributeRepository->delete($id);

            Event::dispatch('b2b_suite.company_attribute.delete.after', $id);

            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.company-attributes.delete-success'),
            ]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans('b2b_suite::app.admin.company-attributes.delete-failed'),
        ], 500);
    }

    /**
     * Remove the specified resources from database.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            $attribute = $this->companyAttributeRepository->find($index);

            if (! $attribute->is_user_defined) {
                return response()->json([
                    'message' => trans('b2b_suite::app.admin.company-attributes.delete-failed'),
                ], 422);
            }
        }

        try {
            foreach ($indices as $index) {
                Event::dispatch('b2b_suite.company_attribute.delete.before', $index);

                $this->companyAttributeRepository->delete($index);

                Event::dispatch('b2b_suite.company_attribute.delete.after', $index);
            }

            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.mass-delete-success'),
            ]);
        } catch (Exception $exception) {
            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.company-attributes.delete-failed'),
            ], 500);
        }
    }

    /**
     * Show the form for editing attribute mapping.
     */
    public function editMapping(): View
    {
        $customAttributes = $this->companyAttributeRepository->all([
            'id', 'code', 'admin_name', 'type', 'is_user_defined',
        ]);

        $attributeGroups = $this->companyAttributeGroupRepository->with([
            'translations',
            'custom_attributes',
        ])->all();

        // Get locales in a more reliable way
        $locales = core()->getAllLocales();

        // Ensure we have the right structure for the frontend
        $localesArray = $locales->map(function ($locale) {
            return [
                'id'   => $locale->id,
                'code' => $locale->code,
                'name' => $locale->name,
            ];
        })->values()->toArray();

        return view('b2b_suite::admin.company-attributes.mapping')
            ->with([
                'customAttributes' => $customAttributes,
                'attributeGroups'  => $attributeGroups,
                'locales'          => $localesArray,
            ]);
    }

    /**
     * Update the attribute mapping.
     */
    public function updateMapping(Request $request): RedirectResponse
    {
        $request->validate([
            'attribute_groups'                                => 'required|array',
            'attribute_groups.*.code'                         => 'required|string|max:255',
            'attribute_groups.*.admin_name'                   => 'required|string|max:255',
            'attribute_groups.*.position'                     => 'nullable|integer',
            'attribute_groups.*.column'                       => 'required|integer|in:1,2',
            'attribute_groups.*.custom_attributes'            => 'array',
            'attribute_groups.*.custom_attributes.*.id'       => 'required',
            'attribute_groups.*.custom_attributes.*.position' => 'required|integer',
        ]);

        $this->companyAttributeGroupRepository->updateMapping($request->all());

        return to_route('admin.customers.attributes.index')
            ->withSuccess(trans('b2b_suite::app.admin.company-attributes.mapping.update-success'));
    }
}

@extends('admin::layouts.content')

@section('page_title')
    {{ __('b2b_suite::app.admin.companies.view.title') }}
@stop

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>
                    <i class="icon angle-left-icon back-link" onclick="history.length > 1 ? history.go(-1) : window.location = '{{ route('admin.customers.companies.index') }}'"></i>
                    {{ __('b2b_suite::app.admin.companies.view.title') }} - {{ $company->name }}
                </h1>
            </div>

            <div class="page-action">
                @if (bouncer()->hasPermission('b2b_suite.companies.edit'))
                    <a href="{{ route('admin.customers.companies.dit', $company->id) }}" class="btn btn-lg btn-primary">
                        {{ __('admin::app.admin.edit') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="page-content">
            <div class="form-container">
                <accordian title="{{ __('b2b_suite::app.admin.companies.view.general-info') }}" :active="true">
                    <div slot="body">
                        <div class="control-group">
                            <label>{{ __('b2b_suite::app.admin.companies.create.name') }}</label>
                            <div class="control">{{ $company->name }}</div>
                        </div>

                        <div class="control-group">
                            <label>{{ __('b2b_suite::app.admin.companies.create.email') }}</label>
                            <div class="control">{{ $company->email }}</div>
                        </div>

                        @if($company->phone)
                            <div class="control-group">
                                <label>{{ __('b2b_suite::app.admin.companies.create.phone') }}</label>
                                <div class="control">{{ $company->phone }}</div>
                            </div>
                        @endif

                        @if($company->website)
                            <div class="control-group">
                                <label>{{ __('b2b_suite::app.admin.companies.create.website') }}</label>
                                <div class="control"><a href="{{ $company->website }}" target="_blank">{{ $company->website }}</a></div>
                            </div>
                        @endif

                        @if($company->industry)
                            <div class="control-group">
                                <label>{{ __('b2b_suite::app.admin.companies.create.industry') }}</label>
                                <div class="control">{{ $company->industry }}</div>
                            </div>
                        @endif

                        @if($company->description)
                            <div class="control-group">
                                <label>{{ __('b2b_suite::app.admin.companies.create.description') }}</label>
                                <div class="control">{{ $company->description }}</div>
                            </div>
                        @endif

                        <div class="control-group">
                            <label>{{ __('b2b_suite::app.admin.companies.create.status') }}</label>
                            <div class="control">
                                <span class="badge {{ $company->status ? 'badge-success' : 'badge-danger' }}">
                                    {{ $company->status ? __('admin::app.admin.system.enable') : __('admin::app.admin.system.disable') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </accordian>

                @if($company->address || $company->city || $company->state || $company->country || $company->postal_code)
                    <accordian title="{{ __('b2b_suite::app.admin.companies.view.address-info') }}" :active="false">
                        <div slot="body">
                            @if($company->address)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.address') }}</label>
                                    <div class="control">{{ $company->address }}</div>
                                </div>
                            @endif

                            @if($company->city)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.city') }}</label>
                                    <div class="control">{{ $company->city }}</div>
                                </div>
                            @endif

                            @if($company->state)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.state') }}</label>
                                    <div class="control">{{ $company->state }}</div>
                                </div>
                            @endif

                            @if($company->country)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.country') }}</label>
                                    <div class="control">{{ $company->country }}</div>
                                </div>
                            @endif

                            @if($company->postal_code)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.postal-code') }}</label>
                                    <div class="control">{{ $company->postal_code }}</div>
                                </div>
                            @endif
                        </div>
                    </accordian>
                @endif

                @if($company->contact_person || $company->contact_email || $company->contact_phone)
                    <accordian title="{{ __('b2b_suite::app.admin.companies.view.contact-info') }}" :active="false">
                        <div slot="body">
                            @if($company->contact_person)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.contact-person') }}</label>
                                    <div class="control">{{ $company->contact_person }}</div>
                                </div>
                            @endif

                            @if($company->contact_email)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.contact-email') }}</label>
                                    <div class="control">{{ $company->contact_email }}</div>
                                </div>
                            @endif

                            @if($company->contact_phone)
                                <div class="control-group">
                                    <label>{{ __('b2b_suite::app.admin.companies.create.contact-phone') }}</label>
                                    <div class="control">{{ $company->contact_phone }}</div>
                                </div>
                            @endif
                        </div>
                    </accordian>
                @endif
            </div>
        </div>
    </div>
@stop

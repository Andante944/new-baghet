@extends('layouts.app')

@php
    $editing = $client->exists;
    $selectedCrm = (int) old('crm_company_id', $client->crm_company_id);
    $crmIds = array_column($crmCompanies, 'id');
@endphp
@section('title', ($editing ? __('site.client_edit') : __('site.client_add')) . ' — Baghet')

@section('content')
<section class="account-page account-page--form">
    <a class="back-link" href="{{ route('admin.clients.index') }}">← {{ __('site.clients') }}</a>
    <div class="panel">
        <h1>{{ $editing ? __('site.client_edit') : __('site.client_add') }}</h1>

        <form class="stack-form" method="post" action="{{ $editing ? route('admin.clients.update', $client) : route('admin.clients.store') }}">
            @csrf
            @if($editing) @method('put') @endif

            <label>
                <span>{{ __('site.client_crm_company') }}</span>
                <select name="crm_company_id" id="crm_company_id">
                    <option value="">{{ __('site.client_crm_none') }}</option>
                    @if($selectedCrm > 0 && ! in_array($selectedCrm, $crmIds, true))
                        <option value="{{ $selectedCrm }}" selected>CRM #{{ $selectedCrm }} — {{ __('site.client_crm_unavailable') }}</option>
                    @endif
                    @foreach($crmCompanies as $company)
                        <option
                            value="{{ $company['id'] }}"
                            data-name="{{ $company['name'] }}"
                            data-phone="{{ $company['phone'] }}"
                            data-email="{{ $company['email'] }}"
                            @selected($selectedCrm === $company['id'])
                        >
                            {{ $company['name'] }}@if($company['business_name']) — {{ $company['business_name'] }}@endif
                        </option>
                    @endforeach
                </select>
                <small>{{ __('site.client_crm_hint') }}</small>
            </label>

            <div class="form-grid">
                <label>
                    <span>{{ __('site.contact_name') }}</span>
                    <input id="client_name" name="name" value="{{ old('name', $client->name) }}" required>
                </label>
                <label>
                    <span>{{ __('site.company') }}</span>
                    <input id="client_company_name" name="company_name" value="{{ old('company_name', $client->company_name) }}">
                </label>
                <label>
                    <span>Email</span>
                    <input id="client_email" type="email" name="email" value="{{ old('email', $client->email) }}" required>
                </label>
                <label>
                    <span>{{ __('site.phone_label') }}</span>
                    <input id="client_phone" name="phone" value="{{ old('phone', $client->phone) }}">
                </label>
                <label>
                    <span>{{ __('site.password') }} @if($editing)<small>{{ __('site.password_leave_blank') }}</small>@else<small>{{ __('site.client_password_auto') }}</small>@endif</span>
                    <input type="password" name="password" autocomplete="new-password">
                </label>
                <label>
                    <span>Bitrix CONTACT_ID <small>{{ __('site.optional') }}</small></span>
                    <input type="number" name="bitrix_contact_id" value="{{ old('bitrix_contact_id', $client->bitrix_contact_id) }}" min="1" placeholder="{{ __('site.optional') }}">
                </label>
                <label>
                    <span>Bitrix COMPANY_ID <small>{{ __('site.optional') }}</small></span>
                    <input type="number" name="bitrix_company_id" value="{{ old('bitrix_company_id', $client->bitrix_company_id) }}" min="1" placeholder="{{ __('site.optional') }}">
                </label>
            </div>

            <p class="muted">{{ __('site.client_bitrix_hint') }}</p>

            <label class="check-row">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->exists ? $client->is_active : true))>
                <span>{{ __('site.active') }}</span>
            </label>

            <button class="btn btn--primary" type="submit">{{ __('site.save') }}</button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.getElementById('crm_company_id')?.addEventListener('change', function () {
        const option = this.selectedOptions[0];
        if (!option || !option.value) {
            return;
        }

        const fill = (id, value) => {
            const field = document.getElementById(id);
            if (field && !field.value && value) {
                field.value = value;
            }
        };

        fill('client_company_name', option.dataset.name);
        fill('client_phone', option.dataset.phone);
        fill('client_email', option.dataset.email);
    });
</script>
@endpush

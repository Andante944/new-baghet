@extends('layouts.app')

@php $editing = $client->exists; @endphp
@section('title', ($editing ? __('site.client_edit') : __('site.client_add')) . ' — Baghet')

@section('content')
<section class="account-page account-page--form">
    <a class="back-link" href="{{ route('admin.clients.index') }}">← {{ __('site.clients') }}</a>
    <div class="panel">
        <h1>{{ $editing ? __('site.client_edit') : __('site.client_add') }}</h1>

        <form class="stack-form" method="post" action="{{ $editing ? route('admin.clients.update', $client) : route('admin.clients.store') }}">
            @csrf
            @if($editing) @method('put') @endif

            <div class="form-grid">
                <label>
                    <span>{{ __('site.contact_name') }}</span>
                    <input name="name" value="{{ old('name', $client->name) }}" required>
                </label>
                <label>
                    <span>{{ __('site.company') }}</span>
                    <input name="company_name" value="{{ old('company_name', $client->company_name) }}">
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}" required>
                </label>
                <label>
                    <span>{{ __('site.phone_label') }}</span>
                    <input name="phone" value="{{ old('phone', $client->phone) }}">
                </label>
                <label>
                    <span>{{ __('site.password') }} @if($editing)<small>{{ __('site.password_leave_blank') }}</small>@endif</span>
                    <input type="password" name="password" @required(! $editing) autocomplete="new-password">
                </label>
                <label>
                    <span>Bitrix CONTACT_ID</span>
                    <input type="number" name="bitrix_contact_id" value="{{ old('bitrix_contact_id', $client->bitrix_contact_id) }}" min="1">
                </label>
                <label>
                    <span>Bitrix COMPANY_ID</span>
                    <input type="number" name="bitrix_company_id" value="{{ old('bitrix_company_id', $client->bitrix_company_id) }}" min="1">
                </label>
            </div>

            <label class="check-row">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->exists ? $client->is_active : true))>
                <span>{{ __('site.active') }}</span>
            </label>

            <button class="btn btn--primary" type="submit">{{ __('site.save') }}</button>
        </form>
    </div>
</section>
@endsection

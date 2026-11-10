@extends('layouts.app')

@section('title', __('site.clients') . ' — Baghet')

@section('content')
<section class="account-page">
    <div class="account-head">
        <div>
            <a class="back-link" href="{{ route('admin.dashboard') }}">← {{ __('site.nav_admin') }}</a>
            <h1>{{ __('site.clients') }}</h1>
        </div>
        <div class="account-head__actions">
            <a class="btn btn--outline" href="{{ route('admin.clients.import') }}">{{ __('site.client_import') }}</a>
            <a class="btn btn--primary" href="{{ route('admin.clients.create') }}">{{ __('site.client_add') }}</a>
        </div>
    </div>

    @if(session('generated_password'))
        <div class="panel">
            <p><strong>{{ __('site.client_password_generated') }}</strong></p>
            <p>Email: <code>{{ session('generated_password')['email'] }}</code><br>
                {{ __('site.password') }}: <code>{{ session('generated_password')['password'] }}</code></p>
            <p class="muted">{{ __('site.client_password_once') }}</p>
        </div>
    @endif

    <form class="search-form" method="get">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('site.client_search') }}">
        <button class="btn btn--outline" type="submit">{{ __('site.search') }}</button>
    </form>

    <div class="panel">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('site.client') }}</th>
                        <th>Email</th>
                        <th>{{ __('site.phone_label') }}</th>
                        <th>CRM</th>
                        <th>{{ __('site.orders') }}</th>
                        <th>{{ __('site.active') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <strong>{{ $client->company_name ?: $client->name }}</strong>
                                @if($client->company_name)<small>{{ $client->name }}</small>@endif
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone ?: '—' }}</td>
                            <td>{{ $client->crm_company_id ? 'CRM #'.$client->crm_company_id : '—' }}</td>
                            <td>{{ $client->orders_count }}</td>
                            <td>{{ $client->is_active ? __('site.yes') : __('site.no') }}</td>
                            <td><a href="{{ route('admin.clients.edit', $client) }}">{{ __('site.edit') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">{{ __('site.no_clients') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pager-space">{{ $clients->links('vendor.pagination.baghet') }}</div>
</section>
@endsection

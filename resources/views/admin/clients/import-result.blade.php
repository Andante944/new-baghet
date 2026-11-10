@extends('layouts.app')

@section('title', __('site.client_import_result') . ' — Baghet')

@section('content')
<section class="account-page account-page--form">
    <a class="back-link" href="{{ route('admin.clients.index') }}">← {{ __('site.clients') }}</a>

    <div class="panel">
        <h1>{{ __('site.client_import_result') }}</h1>

        <p>{{ __('site.client_import_created') }}: <strong>{{ count($created) }}</strong></p>

        @if($token)
            <p><a class="btn btn--primary" href="{{ route('admin.clients.import.download', $token) }}">{{ __('site.client_import_download') }}</a></p>
        @endif

        @if($created !== [])
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('site.company') }}</th>
                            <th>{{ __('site.contact_name') }}</th>
                            <th>Email</th>
                            <th>{{ __('site.phone_label') }}</th>
                            <th>{{ __('site.password') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($created as $row)
                            <tr>
                                <td>{{ $row['company_name'] ?: '—' }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['email'] }}</td>
                                <td>{{ $row['phone'] ?: '—' }}</td>
                                <td><code>{{ $row['password'] }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($skipped !== [])
            <h2>{{ __('site.client_import_skipped') }}</h2>
            <ul>
                @foreach($skipped as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif

        @if($importErrors !== [])
            <h2>{{ __('site.client_import_errors') }}</h2>
            <ul>
                @foreach($importErrors as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif

        <div class="form-actions">
            <a class="btn btn--outline" href="{{ route('admin.clients.import') }}">{{ __('site.client_import_more') }}</a>
            <a class="btn btn--outline" href="{{ route('admin.clients.index') }}">{{ __('site.clients') }}</a>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', __('site.client_import') . ' — Baghet')

@section('content')
<section class="account-page account-page--form">
    <a class="back-link" href="{{ route('admin.clients.index') }}">← {{ __('site.clients') }}</a>

    <div class="panel">
        <h1>{{ __('site.client_import') }}</h1>
        <p class="muted">{{ __('site.client_import_intro') }}</p>

        @if($errors->any())
            <div class="form-errors">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form class="stack-form" method="post" action="{{ route('admin.clients.import.store') }}" enctype="multipart/form-data">
            @csrf

            <label>
                <span>{{ __('site.client_import_file') }}</span>
                <input type="file" name="file" accept=".csv,.txt,text/csv,text/plain">
            </label>

            <label>
                <span>{{ __('site.client_import_rows') }}</span>
                <textarea name="rows" rows="8" placeholder="company_name;name;email;phone;password">{{ old('rows') }}</textarea>
            </label>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">{{ __('site.client_import_submit') }}</button>
                <a class="btn btn--outline" href="{{ route('admin.clients.import.template') }}">{{ __('site.client_import_template') }}</a>
                <a class="btn btn--outline" href="{{ route('admin.clients.index') }}">{{ __('site.cancel') }}</a>
            </div>
        </form>

        <p class="muted">{{ __('site.client_import_columns') }}</p>
    </div>
</section>
@endsection

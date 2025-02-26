@extends('layout.app')

@section('title', __('Forbidden'))

@section('page')
    <div class="error-wrapper">
        <div class="error-container">
            <div class="code">403</div>
            <div class="message">{{ __('Forbidden') }}</div>
        </div>
    </div>
@endsection

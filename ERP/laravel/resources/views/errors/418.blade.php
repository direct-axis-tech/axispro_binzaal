@extends('layout.app')

@section('title', __("I'm a teapot"))

@section('page')
    <div class="error-wrapper">
        <div class="error-container">
            <div class="code">418</div>
            <div class="message">{{ __("I'm a teapot") }}</div>
        </div>
    </div>
@endsection
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'MobileAudit') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="icon" type="image/png" href="{{ asset('images/ma_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/ma_logo_550.png') }}">
    <link rel="apple-touch-startup-image" href="{{ asset('images/ma_logo_550.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MobileAudit') }}</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' rel="stylesheet">
</head>
<body>

<div id="app">
    <home title="{{config('app.name', 'MobileAudit')}}" user_name="Admin" :items="routes">
        <router-view></router-view>
    </home>
</div>

<script src="{{ asset('js/app.js') }}?ver_1.0.1.0"></script>
</body>
</html>
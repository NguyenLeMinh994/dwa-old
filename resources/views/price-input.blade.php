<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master')
@section ('head.title')
Price Input Manage
@stop

@section('body.content')
    <router-view name="priceInputIndex"></router-view>
    <router-view></router-view>
@stop
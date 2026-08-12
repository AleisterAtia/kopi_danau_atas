@extends('layouts.app')

@section('title', __('Akses Ditolak'))

@section('content')
<x-error-page
    code="403"
    :title="__('Akses Ditolak')"
    :message="__('Anda tidak memiliki izin untuk mengakses halaman ini, atau tautan yang Anda buka sudah tidak berlaku.')"
    :primary-label="__('Kembali ke Beranda')"
    :primary-url="route('home')"
    :secondary-label="__('Masuk ke Akun')"
    :secondary-url="route('login')"
/>
@endsection

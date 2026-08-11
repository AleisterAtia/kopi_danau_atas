@extends('layouts.app')

@section('title', __('Sesi Telah Berakhir'))

@section('content')
<x-error-page
    code="419"
    :title="__('Sesi Telah Berakhir')"
    :message="__('Halaman ini sudah terbuka terlalu lama sehingga sesi Anda kedaluwarsa. Silakan coba lagi.')"
    :primary-label="__('Coba Lagi')"
    :primary-url="url()->previous()"
    :secondary-label="__('Kembali ke Beranda')"
    :secondary-url="route('home')"
/>
@endsection

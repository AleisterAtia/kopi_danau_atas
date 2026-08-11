@extends('layouts.app')

@section('title', __('Perlu Masuk Terlebih Dahulu'))

@section('content')
<x-error-page
    code="401"
    :title="__('Perlu Masuk Terlebih Dahulu')"
    :message="__('Sesi Anda berakhir atau Anda belum masuk. Silakan masuk untuk melanjutkan.')"
    :primary-label="__('Masuk')"
    :primary-url="route('login')"
    :secondary-label="__('Kembali ke Beranda')"
    :secondary-url="route('home')"
/>
@endsection

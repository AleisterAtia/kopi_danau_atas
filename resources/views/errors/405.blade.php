@extends('layouts.app')

@section('title', __('Metode Tidak Diizinkan'))

@section('content')
<x-error-page
    code="405"
    :title="__('Metode Tidak Diizinkan')"
    :message="__('Permintaan tidak dapat diproses melalui cara ini. Silakan kembali dan coba lagi dari halaman sebelumnya.')"
    :primary-label="__('Kembali ke Beranda')"
    :primary-url="route('home')"
/>
@endsection

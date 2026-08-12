@extends('layouts.app')

@section('title', __('Terlalu Banyak Percobaan'))

@section('content')
<x-error-page
    code="429"
    :title="__('Terlalu Banyak Percobaan')"
    :message="__('Anda telah mencoba terlalu banyak kali dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba lagi.')"
    :primary-label="__('Kembali ke Beranda')"
    :primary-url="route('home')"
/>
@endsection

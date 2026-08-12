@extends('layouts.app')

@section('title', __('Halaman Tidak Ditemukan'))

@section('content')
<x-error-page
    code="404"
    :title="__('Halaman Tidak Ditemukan')"
    :message="__('Halaman yang Anda cari mungkin sudah dipindahkan atau tidak pernah ada. Yuk kembali jelajahi paket wisata kami.')"
    :primary-label="__('Kembali ke Beranda')"
    :primary-url="route('home')"
    :secondary-label="__('Lihat Paket Wisata')"
    :secondary-url="route('packages.index')"
/>
@endsection

@extends('layouts.app')

@section('title', $package->name)

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex text-sm text-primary-lighter mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-white">{{ __('Beranda') }}</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('packages.index') }}" class="hover:text-white">{{ __('Paket Wisata') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-white">{{ $package->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-4xl md:text-5xl font-bold text-white font-heading">{{ $package->name }}</h1>
    </div>
</div>

<div class="py-12 bg-bg min-h-screen" x-data="{ 
        visitDate: '', 
        guestCount: 1, 
        capacity: {{ $package->daily_capacity }}, 
        available: {{ $package->daily_capacity }},
        price: {{ $package->price }},
        checking: false,
        checkQuota() {
            if(!this.visitDate) return;
            this.checking = true;
            fetch(`/api/kuota/{{ $package->id }}/${this.visitDate}`)
                .then(res => res.json())
                .then(data => {
                    this.available = data.available;
                    this.capacity = data.capacity;
                    if(this.guestCount > this.available) this.guestCount = this.available > 0 ? 1 : 0;
                    this.checking = false;
                })
                .catch(() => this.checking = false);
        }
    }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Gallery -->
                @if($package->images->count() > 0)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm" x-data="{ mainImage: '{{ Storage::url($package->images->first()->image_path) }}' }">
                    <div class="aspect-[16/9] w-full">
                        <img :src="mainImage" alt="{{ $package->name }}" class="w-full h-full object-cover transition-opacity duration-300">
                    </div>
                    @if($package->images->count() > 1)
                    <div class="grid grid-cols-4 gap-2 p-4">
                        @foreach($package->images as $img)
                        <div @click="mainImage = '{{ Storage::url($img->image_path) }}'" class="aspect-square cursor-pointer rounded-lg overflow-hidden border-2 hover:border-accent transition-colors" :class="mainImage === '{{ Storage::url($img->image_path) }}' ? 'border-accent' : 'border-transparent'">
                            <img src="{{ Storage::url($img->image_path) }}" class="w-full h-full object-cover">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                <!-- Description & Info -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm">
                    <div class="flex flex-wrap gap-4 mb-8">
                        <div class="flex items-center text-text-secondary bg-gray-50 px-4 py-2 rounded-lg">
                            <svg class="w-5 h-5 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-medium">{{ $package->duration_hours }} Jam</span>
                        </div>
                        <div class="flex items-center text-text-secondary bg-gray-50 px-4 py-2 rounded-lg">
                            <svg class="w-5 h-5 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="font-medium">Maks. {{ $package->daily_capacity }} Orang/Hari</span>
                        </div>
                        <div class="flex items-center text-text-secondary bg-gray-50 px-4 py-2 rounded-lg">
                            <span class="text-warning text-lg mr-1">★</span>
                            <span class="font-bold mr-1">{{ $package->averageRating > 0 ? $package->averageRating : '-' }}</span>
                            <span>({{ $package->reviews->count() }} Ulasan)</span>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold font-heading mb-4">{{ __('Deskripsi Paket') }}</h2>
                    <div class="prose prose-lg max-w-none text-gray-600 mb-8">
                        {!! $package->description !!}
                    </div>

                    @if($package->facilities)
                    <h2 class="text-2xl font-bold font-heading mb-4">{{ __('Fasilitas') }}</h2>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-gray-600">
                        @foreach(explode("\n", $package->facilities) as $facility)
                            @if(trim($facility))
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-success mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ trim($facility) }}</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                    @endif
                </div>

                <!-- Reviews -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm">
                    <h2 class="text-2xl font-bold font-heading mb-6">{{ __('Ulasan Pengunjung') }}</h2>
                    
                    @if($package->reviews->isEmpty())
                        <p class="text-gray-500 italic">{{ __('Belum ada ulasan untuk paket ini.') }}</p>
                    @else
                        <div class="space-y-6">
                            @foreach($package->reviews as $review)
                            <div class="border-b border-gray-100 last:border-0 pb-6 last:pb-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center">
                                        @if($review->user->avatar)
                                            <img src="{{ str_starts_with($review->user->avatar, 'http') ? $review->user->avatar : Storage::url($review->user->avatar) }}" class="w-10 h-10 rounded-full object-cover mr-3">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold mr-3">
                                                {{ substr($review->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ $review->user->name }}</h4>
                                            <div class="text-xs text-gray-500">{{ $review->created_at->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="flex text-warning">
                                        @for($i=1; $i<=5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-warning' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-gray-600 mt-2">{{ $review->comment }}</p>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Booking Widget -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sticky top-28">
                    <div class="mb-6 pb-6 border-b border-gray-100">
                        <span class="text-sm text-gray-500 uppercase tracking-wider font-semibold">{{ __('Harga Paket') }}</span>
                        <div class="text-3xl font-bold text-primary font-heading mt-1">
                            Rp {{ number_format($package->price, 0, ',', '.') }}<span class="text-sm text-gray-500 font-normal">/orang</span>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
                            <ul class="list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('booking.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tour_package_id" value="{{ $package->id }}">

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tanggal Kunjungan') }}</label>
                                <input type="date" name="visit_date" x-model="visitDate" @change="checkQuota" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20" required>
                            </div>

                            <div>
                                <div class="flex justify-between mb-1">
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Jumlah Orang') }}</label>
                                    <span class="text-xs text-primary font-medium" x-show="visitDate && !checking">
                                        Sisa: <span x-text="available"></span>
                                    </span>
                                    <span class="text-xs text-gray-400" x-show="checking">Mengecek...</span>
                                </div>
                                <div class="flex items-center">
                                    <button type="button" @click="if(guestCount > 1) guestCount--" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200">-</button>
                                    <input type="number" name="guest_count" x-model.number="guestCount" min="1" :max="available" class="w-full text-center border-y-gray-300 border-x-0 focus:ring-0" required readonly>
                                    <button type="button" @click="if(guestCount < available) guestCount++" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-r-lg hover:bg-gray-200">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-bg rounded-lg p-4 mb-6" x-show="visitDate && guestCount > 0">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600" x-text="`${guestCount} x Rp {{ number_format($package->price, 0, ',', '.') }}`"></span>
                                <span class="font-medium text-gray-900" x-text="`Rp ${new Intl.NumberFormat('id-ID').format(guestCount * price)}`"></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between font-bold">
                                <span class="text-gray-900">{{ __('Total') }}</span>
                                <span class="text-primary" x-text="`Rp ${new Intl.NumberFormat('id-ID').format(guestCount * price)}`"></span>
                            </div>
                        </div>

                        @auth
                            <button type="submit" class="w-full btn-primary justify-center text-lg" :disabled="!visitDate || available < 1 || guestCount < 1 || checking" :class="{'opacity-50 cursor-not-allowed': !visitDate || available < 1 || guestCount < 1 || checking}">
                                <span x-show="available < 1 && visitDate && !checking">{{ __('Kuota Penuh') }}</span>
                                <span x-show="available >= 1 || !visitDate">{{ __('Lanjutkan') }}</span>
                            </button>
                            <p class="text-xs text-center text-gray-500 mt-3">
                                {{ __('Selanjutnya Anda akan diminta melengkapi data pemesan.') }}
                            </p>
                        @else
                            <a href="{{ route('login') }}" class="w-full btn-primary justify-center text-lg text-center">
                                {{ __('Masuk untuk Memesan') }}
                            </a>
                        @endauth
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

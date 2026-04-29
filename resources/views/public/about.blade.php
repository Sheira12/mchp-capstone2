@extends('layouts.public')

@section('title', 'About the Parish')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">About Our Parish</h1>

    <div class="prose prose-lg max-w-none">
        <div class="bg-blue-50 rounded-2xl p-8 mb-8">
            <div class="flex items-center gap-4 mb-4">
                <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo" class="w-16 h-16 rounded-full object-cover">
                <div>
                    <h2 class="text-xl font-bold text-blue-900">Mary Help of Christians Parish</h2>
                    <p class="text-blue-700">Southville 1, Niugan, Cabuyao, Laguna</p>
                    <p class="text-blue-600 text-sm">Diocese of San Pablo</p>
                </div>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-4">Our History</h2>
        <p class="text-gray-600 mb-6">
            Mary Help of Christians Parish serves the community of Southville 1, Niugan, Cabuyao, Laguna.
            The parish is dedicated to Mary Help of Christians, whose feast day is celebrated on May 24.
            We are part of the Diocese of San Pablo and serve the spiritual needs of our growing community.
        </p>

        <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
        <p class="text-gray-600 mb-6">
            To be a vibrant community of faith, rooted in the Gospel, celebrating the sacraments,
            and serving the poor and marginalized in the spirit of Mary Help of Christians.
        </p>

        <h2 class="text-2xl font-bold text-gray-900 mb-4">Parish Clergy</h2>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <p class="font-semibold text-gray-900">{{ config('parish.priest') }}</p>
            <p class="text-gray-500 text-sm">Parish Priest</p>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-4">Parish Ministries</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach(['Basic Ecclesial Communities (BEC)', 'Parish Pastoral Council', 'Youth Ministry', 'Couples for Christ', 'Legion of Mary', 'Knights of Columbus', 'Parish Finance Committee', 'Liturgical Ministry', 'Social Action Ministry'] as $ministry)
            <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-700">{{ $ministry }}</div>
            @endforeach
        </div>
    </div>
</div>
@endsection

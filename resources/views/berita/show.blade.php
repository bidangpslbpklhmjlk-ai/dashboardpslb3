@extends('layouts.app')

@section('title', $berita->title)

@section('content')
<div class="pt-32 pb-20 max-w-4xl mx-auto px-4">
    <h1 class="text-4xl font-black text-slate-900 mb-6">{{ $berita->title }}</h1>
    
    <div class="mb-8">
        <img src="{{ $berita->image }}" alt="{{ $berita->title }}" class="w-full rounded-2xl shadow-lg">
    </div>

    <div class="prose max-w-none text-slate-700">
        {!! $berita->content !!}
    </div>
</div>
@endsection
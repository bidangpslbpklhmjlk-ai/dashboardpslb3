{{-- Notifikasi umum: sukses, peringatan generate, konflik, error validasi --}}
@if(session('success'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif

@if(session('peringatan'))
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-semibold mb-1">Sebagian jadwal dilewati:</p>
        <ul class="list-disc pl-5 space-y-0.5">@foreach(session('peringatan') as $m)<li>{{ $m }}</li>@endforeach</ul>
    </div>
@endif

@if(session('konflik'))
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-semibold mb-2">Perubahan ditolak karena jadwal bertabrakan:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach(session('konflik') as $k)
                <li><span class="font-semibold">[{{ str_replace('_', ' ', $k['jenis']) }}]</span> {{ $k['pesan'] }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

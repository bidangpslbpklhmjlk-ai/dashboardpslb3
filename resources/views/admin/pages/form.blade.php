@extends('layouts.admin')

@section('title', $item->exists ? 'Edit Halaman' : 'Tambah Halaman')

@section('content')
<div class="p-8 max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $item->exists ? 'Edit Halaman' : 'Buat Halaman Baru' }}</h1>
            <p class="text-slate-500 text-sm">Tulis konten informasi kaya (rich text) untuk halaman statis.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.pages.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50">Batal</a>
            <button type="submit" form="pageForm" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20">
                {{ $item->exists ? 'Update Halaman' : 'Simpan Halaman' }}
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-600 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form id="pageForm" action="{{ $item->exists ? route('admin.pages.update', $item->id) : route('admin.pages.store') }}" method="POST" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
        @csrf
        @if($item->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Input Judul Halaman --}}
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Judul Halaman</label>
                <input type="text" name="title" value="{{ old('title', $item->title) }}" 
                       class="w-full text-xl font-bold border-0 border-b-2 border-slate-100 focus:border-blue-500 focus:ring-0 p-0 py-2 transition" 
                       placeholder="Contoh: Profil Dinas, Kontak Kami..." required>
            </div>

            {{-- Input Kategori Halaman (Agar Sinkron dengan Sidebar Grouping) --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori Menu</label>
                <input type="text" name="category" value="{{ old('category', $item->category) }}" 
                       class="w-full text-xl font-bold border-0 border-b-2 border-slate-100 focus:border-blue-500 focus:ring-0 p-0 py-2 transition text-slate-600" 
                       placeholder="Contoh: Profil, Informasi" required>
            </div>
        </div>
        
        {{-- Bagian Editor WYSIWYG --}}
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Isi Konten Halaman</label>
            
            {{-- Input tersembunyi untuk menampung HTML hasil ketikan dari Quill Editor sebelum dikirim ke Controller --}}
            <input type="hidden" name="content" id="contentHiddenInput" value="{{ old('content', $item->content) }}">
            
            {{-- Kontainer Fisik WYSIWYG Editor --}}
            <div id="editorContainer" class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="min-height: 380px;">
                {!! old('content', $item->content) !!}
            </div>
        </div>
    </form>
</div>

{{-- Tambahkan CDN stylesheet dan JavaScript Quill.js --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<style>
    /* Styling Toolbar agar serasi dengan Dashboard Slate-Tailwind */
    .ql-toolbar.ql-snow {
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        border-color: #e2e8f0 !important;
        background-color: #f8fafc;
        padding: 0.75rem;
    }
    /* Styling wadah area ketik teks */
    .ql-container.ql-snow {
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
        border-color: #e2e8f0 !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
    }
    .ql-editor {
        min-height: 320px;
        color: #334155;
        line-height: 1.6;
    }
    .ql-editor.ql-blank::before {
        color: #94a3b8;
        font-style: normal;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inisialisasi Quill WYSIWYG
        const editorQuill = new Quill('#editorContainer', {
            theme: 'snow',
            placeholder: 'Mulai ketik konten halaman lengkap Anda di sini (Anda bisa menebalkan teks, membuat list, menyisipkan link, dll)...',
modules: {
    toolbar: {
        container: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image'], // Tambahkan 'image' di sini
            [{ 'color': [] }, { 'background': [] }],
            ['clean']
        ],
        handlers: {
            image: function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = async () => {
                    const file = input.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Mengirim gambar ke server
                    const response = await fetch('{{ route("admin.pages.upload-image") }}', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    if (data.url) {
                        const range = editorQuill.getSelection();
                        editorQuill.insertEmbed(range.index, 'image', data.url);
                    }
                };
            }
        }
    }
}
        });

        // Tangkap form data sebelum dikirim
        const formHalaman = document.querySelector('#pageForm');
        const hiddenInputKonten = document.querySelector('#contentHiddenInput');

        formHalaman.addEventListener('submit', function(e) {
            // Ambil data teks berbentuk format kode HTML murni dari editor
            const htmlContent = editorQuill.root.innerHTML;
            
            // Masukkan kode HTML ke dalam input 'content' tersembunyi
            hiddenInputKonten.value = htmlContent;
            
            // Cegah submit jika teks kosong (Quill default menyisakan "<p><br></p>" kalau kosong)
            if (editorQuill.getText().trim().length === 0) {
                e.preventDefault();
                alert('Kolom isi konten halaman tidak boleh kosong!');
                return false;
            }
        });
    });
</script>
@endsection
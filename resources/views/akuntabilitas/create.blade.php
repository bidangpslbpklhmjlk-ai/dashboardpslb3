@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">

        <h3>Tambah Dokumen Akuntabilitas</h3>

    </div>

    <div class="card-body">

        <form
            action="{{ route('admin.akuntabilitas.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            @include('admin.akuntabilitas._form')

            <button
                type="submit"
                class="btn btn-success">

                Simpan

            </button>

        </form>

    </div>

</div>

@endsection
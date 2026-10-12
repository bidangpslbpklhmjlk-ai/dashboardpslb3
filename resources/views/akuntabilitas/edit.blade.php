@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">

        <h3>Edit Dokumen Akuntabilitas</h3>

    </div>

    <div class="card-body">

        <form
            action="{{ route('admin.akuntabilitas.update', $item->id) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            @include('admin.akuntabilitas._form')

            <button
                type="submit"
                class="btn btn-primary">

                Update

            </button>

        </form>

    </div>

</div>

@endsection
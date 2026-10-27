@extends('layouts.admin')
@section('title', 'Tambah Peran')
@section('content')
    @include('admin.peran._form', ['peran' => null])
@endsection
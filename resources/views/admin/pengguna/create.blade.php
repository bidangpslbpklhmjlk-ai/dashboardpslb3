@extends('layouts.admin')
@section('title', 'Tambah Pengguna')
@section('content')
    @include('admin.pengguna._form', ['pengguna' => null])
@endsection
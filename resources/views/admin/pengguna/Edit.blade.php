@extends('layouts.admin')
@section('title', 'Edit Pengguna')
@section('content')
    @include('admin.pengguna._form', ['pengguna' => $pengguna])
@endsection
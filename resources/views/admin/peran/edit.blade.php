@extends('layouts.admin')
@section('title', 'Edit Peran')
@section('content')
    @include('admin.peran._form', ['peran' => $peran])
@endsection
@extends('layout.app')
@section('content')
<div class="col-12">
    <h1 class="p-3 border text-center my-3">Create User</h1>
</div>

<div class="col-8 mx-auto">
    <form action="{{ route('users.store') }}" method="post" class="form border p-3">
        @csrf

        @include('layout.error')
        @include('layout.message')

        <div class="mb-3">
            <label class="form-label">User Name</label>
            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">User Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password">
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="password_confirmation">
        </div>

        <div class="mb-3">
            <label class="form-label">User Role</label>
            <select name="role" class="form-control">
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-success w-100">Save</button>
        </div>
    </form>
</div>
@endsection

@extends('layout.app')
@section('content')
 <div class="col-12">
    <h1 class="p-3 border text-center my-3">Edit User info</h1>
</div>
            @include('layout.message')
            @include('layout.error')



<div class="col-8 mx-auto">
    <form action="{{ route('users.update',  $user->id) }}" method="post" class="form border p-3">
        @csrf
        @method('put')
        <div class="mb-3">
            <label for="">User Name</label>

            <input type="text" value="{{ $user->name }}" class="form-control" name="name">
        </div>

        <div class="mb-3">
            <label for="">User Email</label>
            <input type="email" value="{{ $user->email }}" class="form-control" name="email">
        </div>

        <div class="mb-3">
            <label for="">User Password</label>
            <input type="password" class="form-control" name="password">
        </div>
        </div>

      <div class="mb-3">
            <label class="form-label">User Role</label>
            <select name="role" class="form-control">
                <option @selected($user->role == 'admin') value="admin">Admin</option>
                <option @selected($user->role == 'user') value="user">User</option>
            </select>
        </div>

        <div class="mb-3">
            <input type="submit" class="form-control bg-success text-white" value="Save">
        </div>
    </form>
</div>
@endsection

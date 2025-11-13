@extends('layout.app')
@section('content')
    <div class="col-12">
        <a href="{{ url('users/create') }}" class="btn btn-primary my-3">Add New User</a>
        <h1 class="p-3 border text-center my-3">All Users</h1>
    </div>

    <div class="col-12">
        @include('layout.message')
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Posts</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
    <a href="{{ route('users.posts', $user->id) }}"
       class="btn btn-sm"
       style="background-color: #0d47a1; color: white; border: none;">
       Show
    </a>
</td>


                        <td>
                            <a href="{{ url('users/' . $user->id . '/edit') }}" class="btn btn-info btn-sm">Edit</a>
                        </td>
                        <td>
                            <form action="{{ url('users/' . $user->id) }}" method="post" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <input type="submit" value="Delete" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this user?')">
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-12">
        {{ $users->links() }}
    </div>
@endsection

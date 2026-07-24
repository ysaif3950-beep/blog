@extends('layout.app')
@section('content')
    <div class="col-12">
        @can('create', \App\Models\User::class)
        <a href="{{ url('users/create') }}" class="btn btn-primary my-3">Add New User</a>
        @endcan
        <h1 class="p-3 border text-center my-3">All Users</h1>
    </div>

    <div class="col-12">
        @include('layout.message')
        @php
            $canViewUserPosts = $users->getCollection()->contains(fn ($user) => auth()->user()->can('view', $user));
            $canUpdateUsers = $users->getCollection()->contains(fn ($user) => auth()->user()->can('update', $user));
            $canDeleteUsers = $users->getCollection()->contains(fn ($user) => auth()->user()->can('delete', $user));
        @endphp

        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    @if ($canViewUserPosts)
                        <th>Posts</th>
                    @endif
                    @if ($canUpdateUsers)
                        <th>Edit</th>
                    @endif
                    @if ($canDeleteUsers)
                        <th>Delete</th>
                    @endif
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
                        @if ($canViewUserPosts)
                            <td>
                                @can('view', $user)
                                    <a href="{{ route('users.posts', $user->id) }}"
                                       class="btn btn-sm"
                                       style="background-color: #0d47a1; color: white; border: none;">
                                        Show
                                    </a>
                                @endcan
                            </td>
                        @endif


                        @if ($canUpdateUsers)
                            <td>
                                @can('update', $user)
                                <a href="{{ url('users/' . $user->id . '/edit') }}" class="btn btn-info btn-sm">Edit</a>
                                @endcan
                            </td>
                        @endif
                        @if ($canDeleteUsers)
                            <td>
                                @can('delete', $user)
                                    <form action="{{ url('users/' . $user->id) }}" method="post" class="d-inline">
                                        @method('DELETE')
                                        @csrf
                                        <input type="submit" value="Delete" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this user?')">
                                    </form>
                                @endcan
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-12">
        {{ $users->links() }}
    </div>
@endsection

@extends('layout.app')
@section('content')
    <div class="col-12">
          @can('create', \App\Models\Tag::class)

        <a href="{{ url('tags/create') }}" class="btn btn-primary my-3">Add New Tag</a>
        @endcan
        <h1 class="p-3 border text-center my-3">All tags</h1>
    </div>

    <div class="col-12">
        @include('layout.message')
        @php
            $canUpdateTags = $tags->getCollection()->contains(fn ($tag) => auth()->user()->can('update', $tag));
            $canDeleteTags = $tags->getCollection()->contains(fn ($tag) => auth()->user()->can('delete', $tag));
        @endphp

        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Tag Name</th>
                    <th>Posts</th>
                    @if ($canUpdateTags)
                        <th>Edit</th>
                    @endif
                    @if ($canDeleteTags)
                        <th>Delete</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($tags as $tag)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tag->name }}</td>

                          <td>
                            @forelse ($tag->posts as $post)
                            <span class="badge bg-primary me-1">{{ $post->title }}</span>


                 @empty
                    <span class="text-muted">No posts</span>
                @endforelse
                          </td>

                        @if ($canUpdateTags)
                            <td>
                                @can('update' ,$tag)

                                <a href="{{ url('tags/' . $tag->id . '/edit') }}" class="btn btn-info btn-sm">Edit</a>
                                @endcan

                            </td>
                        @endif
                        @if ($canDeleteTags)
                            <td>
                                @can('delete', $tag)
                                    <form action="{{ url('tags/' . $tag->id) }}" method="post" class="d-inline">
                                        @method('DELETE')
                                        @csrf

                                        <input type="submit" value="Delete" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this tag?')">
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
        {{ $tags->links() }}
    </div>
@endsection

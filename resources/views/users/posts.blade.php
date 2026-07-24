@extends('layout.app')
@section('content')
    <div class="col-12">
            @can('create', \App\Models\Post::class)
                <a href="{{url('posts/create')}}" class="btn btn-primary my-3 " >Add New Post</a>
            @endcan
          <h1 class="p-3 border text-center my-3">All posts for {{ $user->name }}</h1>
        </div>

        <div class="col-12">
                @include('layout.message')
               @php
    $canUpdatePosts = $posts->getCollection()->contains(
        fn ($post) => auth()->user()->can('update', $post)
    );

    $canDeletePosts = $posts->getCollection()->contains(
        fn ($post) => auth()->user()->can('delete', $post)
    );
@endphp

          <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Writer</th>
                @if ($canUpdatePosts)
                    <th>Edit</th>
                @endif
                @if ($canDeletePosts)
                    <th>Delete</th>
                @endif
              </tr>
            </thead>
            <tbody>
                @foreach ($posts as $post )

              <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$post->title}}</td>
                <td>{{$post->description}}</td>
                <td>{{$post->user->name}}</td>
                @if ($canUpdatePosts)
                    <td>
                        @can('update', $post)
                            <a href="{{url('posts/' .$post->id. '/edit')}}" class="btn btn-info btn-sm">Edit</a>
                        @endcan
                    </td>
                @endif
                @if ($canDeletePosts)
                    <td>
                        @can('delete', $post)
                            <form action="{{url('posts/'.$post->id)}}" method="post" class="d-inline">
                                @method('Delete')
                                @csrf
                                <input type="submit" value="Delete" class="btn btn-danger btn-sm">
                            </form>
                        @endcan
                    </td>
                @endif
              </tr>
                @endforeach

            </tbody>
          </table>
        </div>

@endsection

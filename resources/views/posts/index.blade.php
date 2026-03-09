@extends('layout.app')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-5 pb-4 border-bottom">
        <div>
            <h1 class="mb-2" style="font-size: var(--text-4xl); font-weight: 800;">All Posts</h1>
            <p class="text-muted mb-0" style="font-size: var(--text-base);">
                Browse and manage your blog posts
            </p>
        </div>
        @can('create', \App\Models\Post::class)
            <a href="{{ url('posts/create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-lg"></i>
                New Post
            </a>
        @endcan
    </div>

    @include('layout.message')

    @if($posts->count() > 0)
        <!-- Posts Grid -->
        <div class="row g-4 mb-5">
            @foreach ($posts as $post)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card h-100">
                        <!-- Post Image -->
                        <div class="position-relative" style="height: 200px; overflow: hidden; background-color: var(--gray-100);">
                            @if($post->image_url)
                                <img src="{{ $post->image_url }}" 
                                     class="w-100 h-100 object-fit-cover" 
                                     alt="{{ $post->title }}"
                                     style="transition: transform 0.3s ease;">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-image" style="font-size: 3rem; color: var(--gray-300);"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Post Content -->
                        <div class="card-body d-flex flex-column" style="padding: var(--space-5);">
                            <!-- Tags -->
                            @if($post->tags->count() > 0)
                                <div class="mb-3">
                                    @foreach ($post->tags->take(3) as $tag)
                                        <span class="badge text-primary bg-primary-light me-1 mb-1" 
                                              style="font-weight: 500; padding: var(--space-1) var(--space-3); font-size: var(--text-xs);">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                    @if($post->tags->count() > 3)
                                        <span class="badge bg-light text-muted" style="font-weight: 500; padding: var(--space-1) var(--space-3); font-size: var(--text-xs);">
                                            +{{ $post->tags->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Title -->
                            <h3 class="mb-2" style="font-size: var(--text-xl); font-weight: 600; line-height: var(--leading-tight);">
                                {{ Str::limit($post->title, 60) }}
                            </h3>
                            
                            <!-- Description -->
                            <p class="text-muted flex-grow-1" style="font-size: var(--text-sm); line-height: var(--leading-relaxed); margin-bottom: var(--space-4);">
                                {{ Str::limit($post->description, 100) }}
                            </p>

                            <!-- Meta Info -->
                            <div class="d-flex align-items-center gap-3 mb-4 pt-3 border-top" style="font-size: var(--text-xs); color: var(--gray-500);">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle"></i>
                                    <span>{{ $post->user->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-calendar3"></i>
                                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                @can('update', $post)
                                    <a href="{{ url('posts/' . $post->id . '/edit') }}" 
                                       class="btn btn-secondary btn-sm flex-fill">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('delete', $post)
                                    <form action="{{ url('posts/' . $post->id) }}" method="post" class="flex-fill">
                                        @method('Delete')
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-secondary btn-sm w-100"
                                                style="color: var(--danger);"
                                                onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5" style="padding: var(--space-10) 0;">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                     style="width: 80px; height: 80px; background-color: var(--gray-100);">
                    <i class="bi bi-file-text" style="font-size: 2rem; color: var(--gray-400);"></i>
                </div>
            </div>
            <h3 class="mb-2" style="font-weight: 600;">No posts yet</h3>
            <p class="text-muted mb-4" style="font-size: var(--text-base);">
                Get started by creating your first blog post.
            </p>
            @can('create', \App\Models\Post::class)
                <a href="{{ url('posts/create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-lg"></i>
                    Create Your First Post
                </a>
            @endcan
        </div>
    @endif

    <style>
        .card:hover img {
            transform: scale(1.05);
        }
    </style>
@endsection

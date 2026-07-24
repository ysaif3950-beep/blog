@extends('layout.app')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-5 pb-4 border-bottom">
        <div>
            <h1 class="mb-2" style="font-size: var(--text-4xl); font-weight: 800;">My Profile</h1>
            <p class="text-muted mb-0" style="font-size: var(--text-base);">
                Your posts, tags, and profile activity in one place
            </p>
        </div>

        @can('update', $user)
            <a href="{{ route('users.profile.edit') }}" class="btn btn-secondary">
                <i class="bi bi-pencil"></i>
                Edit Profile
            </a>
        @endcan
    </div>

    @include('layout.message')

    <div class="card mb-5 overflow-hidden">
        <div class="card-body" style="padding: var(--space-6);">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-5">
                    <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4 text-center text-sm-start">
                        <img src="{{ $user->profile_image_url }}"
                             alt="{{ $user->name }}"
                             class="rounded-circle object-fit-cover flex-shrink-0"
                             style="width: 136px; height: 136px; border: 5px solid var(--primary-50); box-shadow: var(--shadow-md);">

                        <div class="pt-sm-3">
                            <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'secondary' }} mb-3">
                                {{ ucfirst($user->role) }}
                            </span>
                            <h2 class="mb-2" style="font-size: var(--text-3xl); font-weight: 800;">{{ $user->name }}</h2>
                            <p class="text-muted mb-0" style="font-size: var(--text-sm);">
                                Member since {{ $user->created_at->format('M Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="border rounded-3 h-100" style="padding: var(--space-4); background-color: var(--gray-50);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">
                                        <i class="bi bi-file-text"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: var(--text-sm);">Posts</div>
                                        <div style="font-size: var(--text-3xl); font-weight: 800;">{{ $posts->total() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded-3 h-100" style="padding: var(--space-4); background-color: var(--gray-50);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">
                                        <i class="bi bi-tags"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: var(--text-sm);">Tags</div>
                                        <div style="font-size: var(--text-3xl); font-weight: 800;">{{ $tags->total() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded-3 h-100" style="padding: var(--space-4); background-color: var(--gray-50);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: var(--text-sm);">Updated</div>
                                        <div style="font-size: var(--text-lg); font-weight: 700;">{{ $user->updated_at->format('M d') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1" style="font-size: var(--text-2xl); font-weight: 700;">My Posts</h2>
                <p class="text-muted mb-0" style="font-size: var(--text-sm);">Posts written by you</p>
            </div>

            @can('create', \App\Models\Post::class)
                <a href="{{ route('posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    New Post
                </a>
            @endcan
        </div>

        @if ($posts->count() > 0)
            <div class="row g-4">
                @foreach ($posts as $post)
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="card h-100">
                            <div class="position-relative" style="height: 180px; overflow: hidden; background-color: var(--gray-100);">
                                <img src="{{ $post->image_url }}"
                                     alt="{{ $post->title }}"
                                     class="w-100 h-100 object-fit-cover">
                            </div>

                            <div class="card-body d-flex flex-column" style="padding: var(--space-5);">
                                @if ($post->tags->count() > 0)
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach ($post->tags->take(3) as $tag)
                                            <span class="badge text-primary bg-primary-light">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <h3 class="mb-2" style="font-size: var(--text-xl); font-weight: 700;">
                                    {{ Str::limit($post->title, 55) }}
                                </h3>

                                <p class="text-muted flex-grow-1" style="font-size: var(--text-sm); line-height: var(--leading-relaxed);">
                                    {{ Str::limit($post->description, 110) }}
                                </p>

                                <div class="d-flex justify-content-between align-items-center gap-3 pt-3 border-top">
                                    <span class="text-muted" style="font-size: var(--text-xs);">{{ $post->created_at->format('M d, Y') }}</span>
                                    <a href="{{ route('posts.show', $post->id) }}" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-eye"></i>
                                        Show
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $posts->appends(request()->except('posts_page'))->links() }}
            </div>
        @else
            <div class="card">
                <div class="card-body text-center" style="padding: var(--space-8);">
                    <i class="bi bi-file-text mb-3" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <h3 class="mb-2">No posts yet</h3>
                    <p class="text-muted mb-4">Start writing your first post.</p>
                    @can('create', \App\Models\Post::class)
                        <a href="{{ route('posts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Create Post
                        </a>
                    @endcan
                </div>
            </div>
        @endif
    </section>

    <section>
        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1" style="font-size: var(--text-2xl); font-weight: 700;">My Tags</h2>
                <p class="text-muted mb-0" style="font-size: var(--text-sm);">Tags created by you</p>
            </div>

            @can('create', \App\Models\Tag::class)
                <a href="{{ route('tags.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    New Tag
                </a>
            @endcan
        </div>

        @if ($tags->count() > 0)
            <div class="row g-3">
                @foreach ($tags as $tag)
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body" style="padding: var(--space-4);">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h3 class="mb-1" style="font-size: var(--text-lg); font-weight: 700;">{{ $tag->name }}</h3>
                                        <p class="text-muted mb-0" style="font-size: var(--text-sm);">
                                            {{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}
                                        </p>
                                    </div>

                                    @can('update', $tag)
                                        <a href="{{ route('tags.edit', $tag->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $tags->appends(request()->except('tags_page'))->links() }}
            </div>
        @else
            <div class="card">
                <div class="card-body text-center" style="padding: var(--space-8);">
                    <i class="bi bi-tags mb-3" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <h3 class="mb-2">No tags yet</h3>
                    <p class="text-muted mb-4">Create tags to organize your posts.</p>
                    @can('create', \App\Models\Tag::class)
                        <a href="{{ route('tags.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Create Tag
                        </a>
                    @endcan
                </div>
            </div>
        @endif
    </section>

    @can('delete', $user)
        <section class="mt-5">
            <div class="border border-danger rounded-3" style="padding: var(--space-5);">
                <div class="mb-4">
                    <h2 class="text-danger mb-1" style="font-size: var(--text-2xl); font-weight: 700;">Danger Zone</h2>
                    <p class="text-muted mb-0" style="font-size: var(--text-sm);">Deleting your account is permanent and cannot be undone. There is no recovery.</p>
                </div>

                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    Delete My Account
                </button>
            </div>
        </section>

        <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3">
                    <div class="modal-header">
                        <h2 class="modal-title text-danger" id="deleteAccountModalLabel" style="font-size: var(--text-xl); font-weight: 700;">Delete Account?</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" style="padding: var(--space-5);">
                        <p class="text-muted mb-0" style="font-size: var(--text-sm); line-height: var(--leading-relaxed);">
                            Are you sure you want to delete your account? This action is permanent and cannot be undone.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                        <form action="{{ route('users.profile.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete My Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

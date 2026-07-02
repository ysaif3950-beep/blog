@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <a href="{{ route('posts.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Back to Posts
            </a>

            <div class="d-flex gap-2">
                @can('update', $post)
                    <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-secondary">
                        <i class="bi bi-pencil"></i>
                        Edit
                    </a>
                @endcan

                @can('delete', $post)
                    <form action="{{ route('posts.destroy', $post->id) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-secondary"
                                style="color: var(--danger);"
                                onclick="return confirm('Are you sure you want to delete this post?')">
                            <i class="bi bi-trash"></i>
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <article class="card overflow-hidden">
            <div class="position-relative d-flex align-items-center justify-content-center" style="aspect-ratio: 16 / 9; max-height: 520px; background-color: var(--gray-100);">
                <img src="{{ $post->image_url }}"
                     alt="{{ $post->title }}"
                     class="w-100 h-100 object-fit-contain"
                     style="padding: var(--space-3);">
            </div>

            <div class="card-body" style="padding: var(--space-6);">
                @if($post->tags->count() > 0)
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach ($post->tags as $tag)
                            <span class="badge text-primary bg-primary-light"
                                  style="font-weight: 500; padding: var(--space-2) var(--space-3); font-size: var(--text-xs);">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <h1 class="mb-4" style="font-size: var(--text-4xl); font-weight: 800;">
                    {{ $post->title }}
                </h1>

                <div class="d-flex flex-wrap align-items-center gap-4 pb-4 mb-5 border-bottom" style="color: var(--gray-600); font-size: var(--text-sm);">
                    <div class="d-flex align-items-center gap-2">
                        <div class="user-avatar">
                            {{ substr($post->user->name ?? 'U', 0, 1) }}
                        </div>
                        <span>{{ $post->user->name ?? 'Unknown' }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3"></i>
                        <span>{{ $post->created_at->format('M d, Y') }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock"></i>
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div style="font-size: var(--text-lg); line-height: var(--leading-relaxed); color: var(--gray-800); white-space: pre-line;">
                    {{ $post->description }}
                </div>
            </div>
        </article>
    </div>
</div>
@endsection

@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <!-- Header -->
        <div class="mb-4">
            <h1 style="font-size: var(--text-3xl); font-weight: 700;">Edit Post</h1>
            <p class="text-muted" style="font-size: var(--text-base);">Update your post content and settings</p>
        </div>

        @include('layout.message')
        @include('layout.error')

        <!-- Form Card -->
        <div class="card">
            <div class="card-body" style="padding: var(--space-6);">
                <form action="{{ url('posts/'.$post->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <!-- Title Field -->
                    <div class="mb-4">
                        <label class="form-label" for="post-title">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="post-title"
                               name="title" 
                               value="{{ $post->title }}" 
                               required>
                    </div>

                    <!-- Description Field -->
                    <div class="mb-4">
                        <label class="form-label" for="post-description">
                            Content <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="post-description"
                                  name="description" 
                                  rows="8" 
                                  required>{{ $post->description }}</textarea>
                    </div>

                    <!-- Tags Field -->
                    <div class="mb-4">
                        <label class="form-label" for="post-tags">Tags</label>
                        <select name="tags[]" 
                                id="post-tags"
                                class="form-select" 
                                multiple 
                                style="height: 120px;">
                             @foreach ($tags as $tag)
                                <option @selected($post->tags->contains($tag->id)) value="{{ $tag->id }}">{{ $tag->name }}</option>
                             @endforeach
                        </select>
                        <div class="form-text">Select tags to help categorize your post</div>
                    </div>

                    <!-- Image Field -->
                    <div class="mb-5">
                        <label class="form-label" for="post-image">Cover Image</label>
                        @if($post->image_url)
                            <div class="mb-3 p-3 border rounded" style="background-color: var(--gray-50);">
                                <p class="small text-muted mb-2">Current Image:</p>
                                <img src="{{ $post->image_url }}" alt="Current post image" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        @endif
                        <input type="file" 
                               class="form-control" 
                               id="post-image"
                               name="image"
                               accept="image/*">
                        <div class="form-text">Upload a new image to replace the current one</div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-3 pt-4 border-top">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i>
                            Update Post
                        </button>
                        <a href="{{ url('posts') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

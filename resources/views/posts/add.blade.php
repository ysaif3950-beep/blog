@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <!-- Header -->
        <div class="mb-4">
            <h1 style="font-size: var(--text-3xl); font-weight: 700;">Create New Post</h1>
            <p class="text-muted" style="font-size: var(--text-base);">Share your ideas and stories with your audience</p>
        </div>

        @include('layout.error')
        @include('layout.message')

        <!-- Form Card -->
        <div class="card">
            <div class="card-body" style="padding: var(--space-6);">
                <form action="{{ url('posts') }}" method="post" enctype="multipart/form-data">
                    @csrf
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
                               value="{{ old('title') }}" 
                               placeholder="Write a descriptive title for your post" 
                               required 
                               autofocus>
                        <div class="form-text">Make it clear and engaging to attract readers</div>
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
                                  placeholder="Write your post content here..." 
                                  required>{{ old('description') }}</textarea>
                        <div class="form-text">Share your thoughts, ideas, or story</div>
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
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Select tags to help categorize your post (Hold Ctrl/Cmd for multiple)</div>
                    </div>

                    <!-- Image Field -->
                    <div class="mb-5">
                        <label class="form-label" for="post-image">Cover Image</label>
                        <input type="file" 
                               class="form-control" 
                               id="post-image"
                               name="image"
                               accept="image/*">
                        <div class="form-text">Upload a cover image for your post (JPG, PNG, or GIF)</div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-3 pt-4 border-top">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i>
                            Publish Post
                        </button>
                        <a href="{{ url('posts') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

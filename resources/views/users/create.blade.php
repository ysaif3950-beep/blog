@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <!-- Header -->
        <div class="mb-4">
            <h1 style="font-size: var(--text-3xl); font-weight: 700;">Create New User</h1>
            <p class="text-muted" style="font-size: var(--text-base);">Add a new account and assign the right access level</p>
        </div>

        @include('layout.error')
        @include('layout.message')

        <!-- Form Card -->
        <div class="card">
            <div class="card-body" style="padding: var(--space-6);">
                <form action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-4">
                        <label class="form-label" for="user-name">
                            User Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="user-name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Enter the user's full name"
                               required
                               autofocus>
                        <div class="form-text">Use the name that should appear across the blog</div>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-4">
                        <label class="form-label" for="user-email">
                            User Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               class="form-control"
                               id="user-email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="name@example.com"
                               required>
                        <div class="form-text">This email will be used for login and notifications</div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-4">
                        <label class="form-label" for="user-password">
                            Password <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               class="form-control"
                               id="user-password"
                               name="password"
                               placeholder="Create a secure password"
                               required>
                        <div class="form-text">Password must be at least 8 characters</div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-4">
                        <label class="form-label" for="user-password-confirmation">
                            Confirm Password <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               class="form-control"
                               id="user-password-confirmation"
                               name="password_confirmation"
                               placeholder="Repeat the password"
                               required>
                    </div>

                    <!-- Role Field -->
                    <div class="mb-4">
                        <label class="form-label" for="user-role">
                            User Role <span class="text-danger">*</span>
                        </label>
                        <select name="role" id="user-role" class="form-select" required>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                            <option value="user" @selected(old('role', 'user') === 'user')>User</option>
                        </select>
                        <div class="form-text">Choose whether this account can manage the app or only use it</div>
                    </div>

                    <!-- Image Field -->
                    <div class="mb-5">
                        <label class="form-label" for="profile-image">Profile Image</label>
                        <input type="file"
                               class="form-control"
                               id="profile-image"
                               name="profile_image"
                               accept="image/*">
                        <div class="form-text">Upload a profile image for this user (JPG, PNG, or GIF)</div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-3 pt-4 border-top">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i>
                            Save User
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
 
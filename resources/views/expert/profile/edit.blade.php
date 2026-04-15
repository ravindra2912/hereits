@extends('expert.layouts.app')

@section('content')
<div class="expert-container">
    <div class="card">
        <div class="card-header">
            <span>Profile</span>
        </div>
        <div class="card-body">
            <form action="{{ route('expert.profile.update') }}" method="POST" enctype="multipart/form-data" class="formaction" data-action="reload">
                @csrf

                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <img id="preview_image" src="{{ getImage($expert->expert_image) }}"
                            class="rounded-circle border border-4 border-white shadow-sm object-fit-cover"
                            style="width: 150px; height: 150px;" loading="lazy">

                        <label for="expert_image_input" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 36px; height: 36px; cursor: pointer; border: 2px solid #fff; transform: translate(5px, 5px);">
                            <i class="bi bi-camera-fill"></i>
                        </label>

                        <input type="file" name="expert_image" id="expert_image_input" class="d-none" accept="image/*" onchange="previewProfileImage(this)">
                    </div>
                </div>

                <script>
                    function previewProfileImage(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('preview_image').src = e.target.result;
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                </script>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="expert_name" class="form-control" required value="{{ old('expert_name', $expert->expert_name) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email', $expert->email) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Job Title</label>
                    <input type="text" name="title" class="form-control" required value="{{ old('title', $expert->title) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description (Bio)</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $expert->description) }}</textarea>
                </div>

                <hr class="my-4">
                <p class="text-muted small mb-3">Leave blank to keep current password</p>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary w-100 btn_action">
                    <span id="buttonText">Save Changes</span>
                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
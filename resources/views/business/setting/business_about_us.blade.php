@extends('business.layouts.main')
@section('title', 'Business About Us')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.css') }}">
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">About Us</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active" aria-current="page">About Us</li>
        </ol>
    </nav>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4">
        <form action="{{ route('business.setting.systemsetting.update') }}" data-action="none" class="formaction" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="post">
            <input type="hidden" name="form_type" value="about_us">

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border bg-light h-100 rounded-4">
                        <div class="card-body text-center p-4">
                            <label class="form-label small fw-bold text-uppercase text-muted d-block mb-3">About Us Image</label>
                            <div class="mb-3">
                                <div class="avtar-upload avtar-landscape">
                                    <div class="avtar-edit">
                                        <input type="file" name="about_us_image" class="about_avtar_input img-hide" id="about_us_image" accept="image/png, image/webp, image/jpeg" />
                                        <label for="about_us_image"><i class="bi bi-pencil-fill"></i></label>
                                    </div>
                                    <div class="avtar-preview">
                                        <img src="{{ getImage($setting->about_us_image) }}" id="about_us_preview" alt="About Us" loading="lazy" />
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">Recommended: 800x600px</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Description / History</label>
                        <textarea class="form-control" name="about_us_text" id="about_summernote">{{ $setting->about_us_text }}</textarea>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                            <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="buttonText">Update About Us</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
    $('.about_avtar_input').on('change', function(event) {
        var input = event.target;
        var image = $('#about_us_preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                image.attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    });

    $(function() {
        $('#about_summernote').summernote({
            dialogsInBody: true,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endpush
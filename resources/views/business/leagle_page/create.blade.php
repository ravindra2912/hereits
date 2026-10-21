@extends('business.layouts.main')
@section('title', 'Add Legal Page')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add Legal Page</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item"><a href="{{ route('business.setting.business.leagle-page.index') }}" class="text-decoration-none">Legal Pages</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form id="legal-page-form" action="{{ route('business.setting.business.leagle-page.store') }}" method="POST" class="formaction" data-action="redirect">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Page Type <span class="text-danger">*</span></label>
                    <select class="form-select required" name="type" id="type">
                        <option value="">Select Page Type</option>
                        @foreach($availableTypes as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Status <span class="text-danger">*</span></label>
                    <select class="form-select required" name="status" id="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-uppercase text-muted">Description / Content <span class="text-danger">*</span></label>
                    <textarea class="form-control required" name="description" id="description_editor" rows="15" placeholder="Enter page content here..."></textarea>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="{{ route('business.setting.business.leagle-page.index') }}" class="btn btn-light rounded-pill px-4 me-2 border">Cancel</a>
                    <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Save Page</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    let descriptionEditor;
    ClassicEditor
        .create(document.querySelector('#description_editor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
        })
        .then(editor => {
            descriptionEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    $('#legal-page-form').on('submit', function() {
        if (descriptionEditor) {
            document.querySelector('#description_editor').value = descriptionEditor.getData();
        }
    });
</script>
@endpush

@extends('business.layouts.main')
@section('title', 'Edit Legal Page')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Legal Page</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item"><a href="{{ route('business.setting.business.leagle-page.index') }}" class="text-decoration-none">Legal Pages</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form id="legal-page-form" action="{{ route('business.setting.business.leagle-page.update', $page->id) }}" method="POST" class="formaction" data-action="redirect">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Page Type</label>
                    <input type="text" class="form-control" value="{{ $allTypes[$page->type] ?? ucwords(str_replace('_', ' ', $page->type)) }}" readonly disabled />
                    <input type="hidden" name="type" value="{{ $page->type }}" />
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Status <span class="text-danger">*</span></label>
                    <select class="form-select required" name="status" id="status">
                        <option value="active" {{ $page->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $page->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-uppercase text-muted">Description / Content <span class="text-danger">*</span></label>
                    <textarea class="form-control required" name="description" id="description_editor" rows="15">{{ $page->description }}</textarea>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="{{ route('business.setting.business.leagle-page.index') }}" class="btn btn-light rounded-pill px-4 me-2 border">Cancel</a>
                    <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Update Page</span>
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

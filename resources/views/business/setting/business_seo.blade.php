@extends('business.layouts.main')
@section('title', 'Business SEO')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Business SEO</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active" aria-current="page">SEO</li>
        </ol>
    </nav>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4">
        <form action="{{ route('business.setting.seo.update', $business->id) }}" data-action="none" class="formaction" method="POST">
            @csrf
            <input type="hidden" name="_method" value="post">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold">Search Engine Optimization</h5>
                        <p class="text-muted small">Improve your business visibility on search engines.</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Meta Description <span class="text-danger">*</span></label>
                        <textarea class="form-control required" name="seo_description" rows="4" placeholder="Brief description of your business...">{{ $business->seo_description }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Meta Keywords <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" value="{{ $business->seo_keyword }}" name="seo_keyword" placeholder="e.g. fashion, retail, store" />
                        <div class="form-text">Separate keywords with commas.</div>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                            <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="buttonText">Update SEO</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
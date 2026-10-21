@extends('admin.layouts.main')
@section('title', 'System Logs')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header & Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 border-bottom pb-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-terminal text-primary me-2"></i>System Logs</h3>
            <p class="text-muted mb-0 small">View, search, and monitor system log entries in real time.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Log File Switcher Dropdown -->
            @if(count($availableFiles) > 0)
            <form method="GET" action="{{ route('admin.logs.index') }}" class="d-inline-block">
                @if(request('level')) <input type="hidden" name="level" value="{{ request('level') }}"> @endif
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                <select name="file" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill shadow-sm pe-4 fw-semibold border-secondary-subtle">
                    @foreach($availableFiles as $file)
                        <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>
                            📄 {{ $file }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif

            <!-- Download Button -->
            <a href="{{ route('admin.logs.download', ['file' => $selectedFile]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-download me-1"></i> Download
            </a>

            <!-- Clear Log Button -->
            <form method="POST" action="{{ route('admin.logs.clear') }}" onsubmit="return confirm('Are you sure you want to clear {{ $selectedFile }}?');" class="d-inline">
                @csrf
                <input type="hidden" name="file" value="{{ $selectedFile }}">
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                    <i class="bi bi-trash3 me-1"></i> Clear Log
                </button>
            </form>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="file" value="{{ $selectedFile }}">

                <!-- Search Input -->
                <div class="col-md-5 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Search log messages or stack traces..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('admin.logs.index', ['file' => $selectedFile, 'level' => request('level')]) }}" class="btn btn-outline-secondary btn-sm" title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Level Filters -->
                <div class="col-md-7 col-lg-6 text-md-end">
                    <div class="btn-group btn-group-sm flex-wrap gap-1" role="group">
                        <a href="{{ route('admin.logs.index', ['file' => $selectedFile, 'search' => request('search')]) }}" 
                           class="btn rounded-pill px-3 {{ !request('level') || request('level') === 'ALL' ? 'btn-dark' : 'btn-outline-secondary' }}">
                            All
                        </a>
                        @foreach(['ERROR', 'WARNING', 'INFO', 'DEBUG'] as $lvl)
                            <a href="{{ route('admin.logs.index', ['file' => $selectedFile, 'level' => $lvl, 'search' => request('search')]) }}" 
                               class="btn rounded-pill px-3 {{ request('level') === $lvl ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}">
                                {{ $lvl }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Data Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="fw-bold mb-0 text-secondary">
                Showing entries for <span class="text-primary">{{ $selectedFile }}</span> 
                <span class="badge bg-light text-dark border ms-2">{{ number_format($fileSize / 1024, 2) }} KB</span>
            </h6>
            <span class="text-muted small">Total: <strong>{{ $paginatedLogs->total() }}</strong> entries</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted uppercase small">
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th style="width: 120px;">Level</th>
                            <th style="width: 170px;">Date & Time</th>
                            <th>Log Message</th>
                            <th class="text-end pe-4" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedLogs as $log)
                        <tr>
                            <td class="ps-4 text-muted small fw-bold">{{ $loop->iteration + ($paginatedLogs->currentPage() - 1) * $paginatedLogs->perPage() }}</td>
                            <td>
                                <span class="badge {{ $log['badge_class'] }} px-2 py-1 rounded-pill small fw-semibold">
                                    {{ $log['level'] }}
                                </span>
                            </td>
                            <td class="text-nowrap small text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $log['date'] }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark text-break font-monospace small">
                                    {{ $log['message'] }}
                                </div>
                                @if(!empty($log['stack']))
                                    <div class="text-muted small mt-1 font-monospace text-truncate" style="max-width: 650px;">
                                        <i class="bi bi-code-square me-1"></i>{{ Str::limit($log['stack'], 140) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if(!empty($log['stack']))
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#stackModal_{{ $loop->index }}">
                                        <i class="bi bi-eye me-1"></i> Details
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                                <h6 class="fw-bold mb-1">No Log Entries Found</h6>
                                <p class="small mb-0 opacity-75">There are no log records matching your current filter criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($paginatedLogs->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            <div class="d-flex justify-content-center">
                {{ $paginatedLogs->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modals placed outside table to prevent DOM blinking -->
@foreach($paginatedLogs as $log)
    @if(!empty($log['stack']))
    <div class="modal fade text-start" id="stackModal_{{ $loop->index }}" tabindex="-1" aria-labelledby="stackModalLabel_{{ $loop->index }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h6 class="modal-title fw-bold" id="stackModalLabel_{{ $loop->index }}">
                        <span class="badge {{ $log['badge_class'] }} me-2">{{ $log['level'] }}</span>
                        Log Entry Details #{{ $log['id'] }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Timestamp</label>
                        <div class="fw-bold">{{ $log['date'] }} ({{ $log['env'] }})</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Message</label>
                        <div class="p-3 bg-white border rounded-3 text-danger font-monospace small text-break">
                            {{ $log['message'] }}
                        </div>
                    </div>
                    <div>
                        <label class="form-label text-muted small fw-bold">Stack Trace / Details</label>
                        <pre class="p-3 bg-dark text-success-subtle rounded-3 font-monospace small text-wrap border mb-0" style="max-height: 400px; overflow-y: auto; font-size: 12px;">{{ $log['stack'] }}</pre>
                    </div>
                </div>
                <div class="modal-footer bg-white rounded-bottom-4 py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection

@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Laravel Logs')

@section('page_title', 'Laravel Logs')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Laravel Logs</h5>
                <div class="card-tools">
                    <a href="{{ url('master-admin/logs/clear') }}" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Clear Logs
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="log-search" placeholder="Search logs...">
                        <button class="btn btn-outline-secondary" type="button" id="search-btn">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
                
                <div class="log-container bg-dark text-light p-3 rounded" style="max-height: 600px; overflow-y: auto; font-family: monospace;">
                    <pre id="log-content">{{ $logContents }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('log-search');
        const searchBtn = document.getElementById('search-btn');
        const logContent = document.getElementById('log-content');
        const originalContent = logContent.textContent;
        
        function performSearch() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            
            if (searchTerm === '') {
                logContent.textContent = originalContent;
                return;
            }
            
            const lines = originalContent.split('\n');
            const filteredLines = lines.filter(line => 
                line.toLowerCase().includes(searchTerm)
            );
            
            logContent.textContent = filteredLines.join('\n');
        }
        
        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    });
</script>
@endpush

@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Database Configuration')

@section('page_title', 'Database Configuration')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="databaseTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab">
                            <i class="bi bi-gear"></i> Configuration
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="manager-tab" data-bs-toggle="tab" data-bs-target="#manager" type="button" role="tab">
                            <i class="bi bi-table"></i> Database Manager
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="query-tab" data-bs-toggle="tab" data-bs-target="#query" type="button" role="tab">
                            <i class="bi bi-code"></i> SQL Query
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tools-tab" data-bs-toggle="tab" data-bs-target="#tools" type="button" role="tab">
                            <i class="bi bi-tools"></i> Tools
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="databaseTabsContent">
                    <!-- Configuration Tab -->
                    <div class="tab-pane fade show active" id="config" role="tabpanel">
                        <form action="{{ route('master-admin.settings.database.update') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="connection" class="form-label">Database Driver</label>
                                <select name="connection" id="connection" class="form-select @error('connection') is-invalid @enderror">
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver }}" {{ $config['connection'] == $driver ? 'selected' : '' }}>
                                            {{ ucfirst($driver) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('connection')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="host" class="form-label">Database Host</label>
                                <input type="text" class="form-control @error('host') is-invalid @enderror" 
                                       id="host" name="host" value="{{ old('host', $config['host']) }}">
                                @error('host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="port" class="form-label">Database Port</label>
                                <input type="text" class="form-control @error('port') is-invalid @enderror" 
                                       id="port" name="port" value="{{ old('port', $config['port']) }}">
                                @error('port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="database" class="form-label">Database Name</label>
                                <input type="text" class="form-control @error('database') is-invalid @enderror" 
                                       id="database" name="database" value="{{ old('database', $config['database']) }}">
                                @error('database')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Database Username</label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username', $config['username']) }}">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Database Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Leave blank to keep current password">
                                <div class="form-text">Leave blank to keep the current password.</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('master-admin.settings.index') }}" class="btn btn-secondary">Back to Settings</a>
                                <div>
                                    <a href="{{ route('master-admin.settings.database.test') }}" class="btn btn-info me-2">
                                        <i class="bi bi-database-check"></i> Test Connection
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Save Configuration
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Database Manager Tab -->
                    <div class="tab-pane fade" id="manager" role="tabpanel">
                        <div class="text-center">
                            <i class="bi bi-table fs-1 text-primary mb-3"></i>
                            <h5>Database Manager</h5>
                            <p class="text-muted">Manage your database tables, browse data, and perform CRUD operations similar to phpMyAdmin.</p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a target="_bank" href="{{ route('master-admin.settings.database.manager.index') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-up-right"></i> Open Database Manager
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- SQL Query Tab -->
                    <div class="tab-pane fade" id="query" role="tabpanel">
                        <form action="{{ route('master-admin.settings.database.execute') }}" method="POST">
                            @csrf
                            <input type="hidden" name="active_tab" value="query">
                            <div class="mb-3">
                                <label for="sql_query" class="form-label">SQL Query</label>
                                <textarea class="form-control" id="sql_query" name="sql_query" rows="8" 
                                          placeholder="Enter your SQL query here...&#10;Example:&#10;SELECT * FROM users LIMIT 10;&#10;UPDATE users SET status = 'active' WHERE id = 1;">{{ old('sql_query') }}</textarea>
                                <div class="form-text">
                                    <strong>Warning:</strong> Be careful with UPDATE, DELETE, and DROP statements. 
                                    Database operations like DROP DATABASE, GRANT, REVOKE are blocked for security.
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        Tip: Use semicolon (;) to separate multiple statements
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-play-fill"></i> Execute Query
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tools Tab -->
                    <div class="tab-pane fade" id="tools" role="tabpanel">
                        <div class="row">
                            <!-- Import Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bi bi-upload"></i> Import Data</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Import SQL files or restore database from backup.</p>
                                        <a href="{{ route('master-admin.settings.database.import') }}" class="btn btn-success">
                                            <i class="bi bi-file-earmark-arrow-up"></i> Import SQL File
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Export Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bi bi-download"></i> Export Data</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Export database or individual tables as SQL backup.</p>
                                        <a href="{{ route('master-admin.backup.index') }}" class="btn btn-info">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Backup Database
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Maintenance Section -->
                            <div class="col-md-12">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Dangerous Operations</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-danger">
                                            <strong>Warning:</strong> These operations can permanently delete data. 
                                            Make sure to backup your database before proceeding.
                                        </div>
                                        
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#dropTablesModal">
                                                <i class="bi bi-trash"></i> Drop All Tables
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drop All Tables Confirmation Modal -->
<div class="modal fade" id="dropTablesModal" tabindex="-1" aria-labelledby="dropTablesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="dropTablesModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Drop All Tables
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>DANGER!</strong>
                    This action will permanently delete ALL tables and data in your database.
                </div>
                <p><strong>This action cannot be undone!</strong></p>
                <p>Are you absolutely sure you want to drop all tables from the database?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDrop" required>
                    <label class="form-check-label" for="confirmDrop">
                        I understand this will delete all my data
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('master-admin.settings.database.drop-all') }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDropBtn" disabled>Drop All Tables</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheckbox = document.getElementById('confirmDrop');
    const confirmBtn = document.getElementById('confirmDropBtn');
    
    confirmCheckbox.addEventListener('change', function() {
        confirmBtn.disabled = !this.checked;
    });

    // Handle tab switching based on various conditions
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check URL parameter first
    if (urlParams.get('tab') === 'manager') {
        const managerTab = new bootstrap.Tab(document.getElementById('manager-tab'));
        managerTab.show();
    }
    // Check session-based active tab
    @if(session('active_tab'))
        @if(session('active_tab') === 'query')
            const queryTab = new bootstrap.Tab(document.getElementById('query-tab'));
            queryTab.show();
        @elseif(session('active_tab') === 'tools')
            const toolsTab = new bootstrap.Tab(document.getElementById('tools-tab'));
            toolsTab.show();
        @endif
    @endif
    
    // Legacy support for old form submission
    @if(old('active_tab') === 'query')
        const queryTabLegacy = new bootstrap.Tab(document.getElementById('query-tab'));
        queryTabLegacy.show();
    @endif
});
</script>
@endsection

@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Database Manager')

@section('page_title', 'Database Manager')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('master-admin.settings.database.manager.create-table') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Table
                    </a>
                    <a href="{{ route('master-admin.settings.database.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(empty($tables))
                    <div class="text-center py-4">
                        <i class="bi bi-database fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No tables found in database</p>
                        <a href="{{ route('master-admin.settings.database.manager.create-table') }}" class="btn btn-primary">Create First Table</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Table Name</th>
                                    <th>Rows</th>
                                    <th>Size</th>
                                    @if($driver === 'mysql')
                                    <th>Engine</th>
                                    <th>Collation</th>
                                    @endif
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tables as $table)
                                <tr>
                                    <td>
                                        <a href="{{ route('master-admin.settings.database.manager.table', $table['name']) }}" class="text-decoration-none">
                                            <i class="bi bi-table"></i> {{ $table['name'] }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($table['rows']) }}</td>
                                    <td>{{ $table['size'] }}</td>
                                    @if($driver === 'mysql')
                                    <td>{{ $table['engine'] }}</td>
                                    <td>{{ $table['collation'] }}</td>
                                    @endif
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('master-admin.settings.database.manager.table', $table['name']) }}" class="btn btn-outline-primary" title="Browse">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('master-admin.settings.database.manager.create-record', $table['name']) }}" class="btn btn-outline-success" title="Insert">
                                                <i class="bi bi-plus"></i>
                                            </a>
                                            <a href="{{ route('master-admin.settings.database.manager.export-table', $table['name']) }}" class="btn btn-outline-info" title="Export">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Drop" onclick="confirmDrop('{{ $table['name'] }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Drop Table Modal -->
<div class="modal fade" id="dropTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Drop Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to drop table <strong id="tableToDelete"></strong>?</p>
                <p class="text-danger">This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="dropTableForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Drop Table</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDrop(tableName) {
    document.getElementById('tableToDelete').textContent = tableName;
    const baseUrl = '{{ route("master-admin.settings.database.manager.drop-table", ":table") }}';
    const url = baseUrl.replace(':table', tableName);
    document.getElementById('dropTableForm').action = url;
    new bootstrap.Modal(document.getElementById('dropTableModal')).show();
}
</script>
@endsection

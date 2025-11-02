@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Table: ' . $table)

@section('page_title', 'Table: ' . $table)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">({{ $data->total() }} records)</span>
                </div>
                <div>
                    <a href="{{ route('master-admin.settings.database.manager.create-record', $table) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus"></i> Insert Record
                    </a>
                    <a href="{{ route('master-admin.settings.database.manager.export-table', $table) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-download"></i> Export
                    </a>
                    <a href="{{ route('master-admin.settings.database.manager.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Controls -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('master-admin.settings.database.manager.table', $table) }}">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search records...">
                                <input type="hidden" name="sort" value="{{ $sortBy }}">
                                <input type="hidden" name="order" value="{{ $sortOrder }}">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if($search)
                                <a href="{{ route('master-admin.settings.database.manager.table', $table) }}" class="btn btn-outline-danger">
                                    <i class="bi bi-x"></i> Clear
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <form method="GET" action="{{ route('master-admin.settings.database.manager.table', $table) }}" class="d-inline-block">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="sort" value="{{ $sortBy }}">
                            <input type="hidden" name="order" value="{{ $sortOrder }}">
                            <select name="per_page" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per page</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per page</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per page</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per page</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Table Structure -->
                <div class="accordion mb-4" id="structureAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#structureCollapse">
                                <i class="bi bi-table me-2"></i>Table Structure
                            </button>
                        </h2>
                        <div id="structureCollapse" class="accordion-collapse collapse" data-bs-parent="#structureAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Column</th>
                                                <th>Type</th>
                                                @if($driver === 'mysql')
                                                <th>Null</th>
                                                <th>Key</th>
                                                <th>Default</th>
                                                <th>Extra</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($columns as $column)
                                            <tr>
                                                @if($driver === 'mysql')
                                                <td><code>{{ $column->Field }}</code></td>
                                                <td>{{ $column->Type }}</td>
                                                <td>{{ $column->Null }}</td>
                                                <td>{{ $column->Key }}</td>
                                                <td>{{ $column->Default }}</td>
                                                <td>{{ $column->Extra }}</td>
                                                @else
                                                <td><code>{{ $column->name }}</code></td>
                                                <td>{{ $column->type }}</td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Data -->
                <h6>Table Data</h6>
                @if($data->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    @foreach($data->first() as $key => $value)
                                    <th>
                                        <a href="{{ route('master-admin.settings.database.manager.table', array_merge([$table], request()->query(), ['sort' => $key, 'order' => ($sortBy === $key && $sortOrder === 'asc') ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark">
                                            {{ $key }}
                                            @if($sortBy === $key)
                                                <i class="bi bi-caret-{{ $sortOrder === 'asc' ? 'up' : 'down' }}-fill"></i>
                                            @else
                                                <i class="bi bi-caret-up text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    @endforeach
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $record)
                                <tr>
                                    @foreach($record as $value)
                                    <td>
                                        @if(is_string($value) && strlen($value) > 50)
                                            <span title="{{ $value }}">{{ Str::limit($value, 50) }}</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                    @endforeach
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if(isset($record->id))
                                            <a href="{{ route('master-admin.settings.database.manager.edit-record', [$table, $record->id]) }}" 
                                               class="btn btn-outline-primary btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm" title="Delete" 
                                                    onclick="confirmDelete('{{ $table }}', '{{ $record->id }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }} results
                        </div>
                        <div>
                            @if($data->hasPages())
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if($data->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $data->previousPageUrl() }}" rel="prev">Previous</a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @php
                                            $start = max($data->currentPage() - 2, 1);
                                            $end = min($start + 4, $data->lastPage());
                                            $start = max($end - 4, 1);
                                        @endphp

                                        @if($start > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $data->url(1) }}">1</a>
                                            </li>
                                            @if($start > 2)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                        @endif

                                        @for($i = $start; $i <= $end; $i++)
                                            @if($i == $data->currentPage())
                                                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $data->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        @if($end < $data->lastPage())
                                            @if($end < $data->lastPage() - 1)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $data->url($data->lastPage()) }}">{{ $data->lastPage() }}</a>
                                            </li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if($data->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $data->nextPageUrl() }}" rel="next">Next</a>
                                            </li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        @if($search)
                            <p class="text-muted mt-2">No records found matching "{{ $search }}"</p>
                            <a href="{{ route('master-admin.settings.database.manager.table', $table) }}" class="btn btn-secondary">Clear Search</a>
                        @else
                            <p class="text-muted mt-2">No records found</p>
                            <a href="{{ route('master-admin.settings.database.manager.create-record', $table) }}" class="btn btn-success">Insert First Record</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Record Modal -->
<div class="modal fade" id="deleteRecordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this record?</p>
                <p class="text-danger">This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteRecordForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Record</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(table, id) {
    const baseUrl = '{{ route("master-admin.settings.database.manager.delete-record", ["table" => ":table", "id" => ":id"]) }}';
    const url = baseUrl.replace(':table', table).replace(':id', id);
    document.getElementById('deleteRecordForm').action = url;
    new bootstrap.Modal(document.getElementById('deleteRecordModal')).show();
}
</script>
@endsection

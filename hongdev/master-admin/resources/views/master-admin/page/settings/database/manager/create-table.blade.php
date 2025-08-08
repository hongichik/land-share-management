@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Create Table')

@section('page_title', 'Create New Table')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('master-admin.settings.database.manager.store-table') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="table_name" class="form-label">Table Name</label>
                        <input type="text" class="form-control @error('table_name') is-invalid @enderror" 
                               id="table_name" name="table_name" value="{{ old('table_name') }}" required>
                        @error('table_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6>Columns</h6>
                    <div id="columns-container">
                        <div class="row column-row mb-3" data-index="0">
                            <div class="col-md-3">
                                <label class="form-label">Column Name</label>
                                <input type="text" class="form-control" name="columns[0][name]" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="columns[0][type]" required>
                                    <option value="INT">INT</option>
                                    <option value="VARCHAR">VARCHAR</option>
                                    <option value="TEXT">TEXT</option>
                                    <option value="DATETIME">DATETIME</option>
                                    <option value="DATE">DATE</option>
                                    <option value="TIME">TIME</option>
                                    <option value="TIMESTAMP">TIMESTAMP</option>
                                    <option value="DECIMAL">DECIMAL</option>
                                    <option value="FLOAT">FLOAT</option>
                                    <option value="DOUBLE">DOUBLE</option>
                                    <option value="BOOLEAN">BOOLEAN</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Length</label>
                                <input type="text" class="form-control" name="columns[0][length]" placeholder="e.g. 255">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[0][null]" value="1">
                                    <label class="form-check-label">Null</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[0][primary]" value="1">
                                    <label class="form-check-label">Primary</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[0][auto_increment]" value="1">
                                    <label class="form-check-label">Auto Inc</label>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-sm btn-danger remove-column" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-success btn-sm" id="add-column">
                            <i class="bi bi-plus"></i> Add Column
                        </button>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.settings.database.manager.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Table</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let columnIndex = 1;

document.getElementById('add-column').addEventListener('click', function() {
    const container = document.getElementById('columns-container');
    const newRow = document.createElement('div');
    newRow.className = 'row column-row mb-3';
    newRow.setAttribute('data-index', columnIndex);
    
    newRow.innerHTML = `
        <div class="col-md-3">
            <input type="text" class="form-control" name="columns[${columnIndex}][name]" required>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="columns[${columnIndex}][type]" required>
                <option value="INT">INT</option>
                <option value="VARCHAR">VARCHAR</option>
                <option value="TEXT">TEXT</option>
                <option value="DATETIME">DATETIME</option>
                <option value="DATE">DATE</option>
                <option value="TIME">TIME</option>
                <option value="TIMESTAMP">TIMESTAMP</option>
                <option value="DECIMAL">DECIMAL</option>
                <option value="FLOAT">FLOAT</option>
                <option value="DOUBLE">DOUBLE</option>
                <option value="BOOLEAN">BOOLEAN</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="columns[${columnIndex}][length]" placeholder="e.g. 255">
        </div>
        <div class="col-md-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[${columnIndex}][null]" value="1">
                <label class="form-check-label">Null</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[${columnIndex}][primary]" value="1">
                <label class="form-check-label">Primary</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[${columnIndex}][auto_increment]" value="1">
                <label class="form-check-label">Auto Inc</label>
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-danger remove-column">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newRow);
    columnIndex++;
    updateRemoveButtons();
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-column')) {
        e.target.closest('.column-row').remove();
        updateRemoveButtons();
    }
});

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.column-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.remove-column');
        removeBtn.disabled = rows.length === 1;
    });
}
</script>
@endsection

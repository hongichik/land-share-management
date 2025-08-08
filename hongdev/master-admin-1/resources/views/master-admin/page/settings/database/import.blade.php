@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Import SQL File')

@section('page_title', 'Import SQL File')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('master-admin.settings.database.import.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">SQL File</label>
                        <input type="file" class="form-control @error('sql_file') is-invalid @enderror" 
                               id="sql_file" name="sql_file" accept=".sql,.txt">
                        <div class="form-text">
                            Upload a SQL file (.sql or .txt). Maximum file size: 50MB
                        </div>
                        @error('sql_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Important Notes:</h6>
                        <ul class="mb-0">
                            <li>Make sure your SQL file contains valid SQL statements</li>
                            <li>Each statement should end with a semicolon (;)</li>
                            <li>Comments starting with -- are ignored</li>
                            <li>Large files may take time to process</li>
                            <li>Consider backing up your database before importing</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.settings.database.index') }}" class="btn btn-secondary">Back to Database Settings</a>
                        <button type="submit" class="btn btn-primary">Import SQL File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

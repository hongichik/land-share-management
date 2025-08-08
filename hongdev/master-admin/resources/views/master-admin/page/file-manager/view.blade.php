@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'View File: ' . $fileInfo['name'])

@section('page_title', 'View File: ' . $fileInfo['name'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('master-admin.file-manager.download', ['path' => $filePath, 'disk' => $disk]) }}" 
                       class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <a href="{{ route('master-admin.file-manager.index', ['disk' => $disk, 'path' => dirname($filePath)]) }}" 
                       class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- File Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>File Name:</th><td>{{ $fileInfo['name'] }}</td></tr>
                            <tr><th>Size:</th><td>{{ number_format($fileInfo['size'] / 1024, 2) }} KB</td></tr>
                            <tr><th>Type:</th><td>{{ $fileInfo['mimeType'] }}</td></tr>
                            <tr><th>Extension:</th><td>{{ $fileInfo['extension'] ?: 'None' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Modified:</th><td>{{ date('Y-m-d H:i:s', $fileInfo['modified']) }}</td></tr>
                            <tr><th>Is Text File:</th><td>{{ $isTextFile ? 'Yes' : 'No' }}</td></tr>
                            @if($isTextFile && $fileInfo['size'] < 1048576)
                                <tr><th>Actions:</th><td>
                                    <a href="{{ route('master-admin.file-manager.edit', ['path' => $filePath, 'disk' => $disk]) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- File Content -->
                @if($content !== null)
                    <h6>File Content:</h6>
                    <div class="bg-dark text-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">
                        <pre><code>{{ $content }}</code></pre>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        @if(!$isTextFile)
                            File content cannot be displayed (not a text file).
                        @else
                            File is too large to display (over 1MB).
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

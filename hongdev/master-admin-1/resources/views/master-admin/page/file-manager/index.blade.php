@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'File Manager')

@section('page_title', 'File Manager')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">File Manager</h5>
                        <small class="text-muted">{{ $diskInfo['name'] ?? 'Unknown' }} 
                            @if(isset($diskInfo['description']))
                                - {{ $diskInfo['description'] }}
                            @endif
                        </small>
                        @if(isset($diskInfo['path']))
                            <br><small class="text-info">Path: {{ $diskInfo['path'] }}</small>
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group me-2">
                            <a href="?disk=public" class="btn btn-sm {{ $disk === 'public' ? 'btn-primary' : 'btn-outline-primary' }}" 
                               title="Public folder - Web accessible files">
                                <i class="bi bi-globe"></i> Public
                            </a>
                            <a href="?disk=storage" class="btn btn-sm {{ $disk === 'storage' ? 'btn-primary' : 'btn-outline-primary' }}" 
                               title="Storage/app/public - Laravel storage disk">
                                <i class="bi bi-hdd-stack"></i> Storage
                            </a>
                            <a href="?disk=local" class="btn btn-sm {{ $disk === 'local' ? 'btn-primary' : 'btn-outline-primary' }}" 
                               title="Storage/app - Private Laravel files">
                                <i class="bi bi-folder"></i> Local
                            </a>
                        </div>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newFolderModal">
                            <i class="bi bi-folder-plus"></i> New Folder
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        @foreach($breadcrumbs as $crumb)
                            @if($loop->last)
                                <li class="breadcrumb-item active">{{ $crumb['name'] }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="?disk={{ $disk }}&path={{ $crumb['path'] }}">{{ $crumb['name'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>

                <!-- File Grid -->
                @if(empty($items))
                    <div class="text-center py-5">
                        <i class="bi bi-folder2-open display-1 text-muted"></i>
                        <p class="text-muted mt-3">This folder is empty</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($items as $item)
                            <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                                <div class="card h-100 file-item">
                                    @if($item['type'] === 'directory')
                                        <a href="?disk={{ $disk }}&path={{ $item['path'] }}" class="card-body text-center p-2 text-decoration-none">
                                    @elseif(in_array($item['extension'], ['txt', 'php', 'js', 'css', 'html', 'json', 'md', 'env', 'log']))
                                        <a href="{{ route('master-admin.file-manager.view', ['path' => $item['path'], 'disk' => $disk]) }}" class="card-body text-center p-2 text-decoration-none">
                                    @else
                                        <div class="card-body text-center p-2">
                                    @endif
                                        @if($item['type'] === 'directory')
                                            <div class="">
                                                <i class="bi bi-folder-fill text-primary display-4"></i>
                                                <div class="mt-2">
                                                    <small class="text-truncate d-block">{{ $item['name'] }}</small>
                                                </div>
                                            </div>
                                        @else
                                            @php
                                                $icon = match($item['extension']) {
                                                    'pdf' => 'bi-file-pdf text-danger',
                                                    'doc', 'docx' => 'bi-file-word text-primary',
                                                    'xls', 'xlsx' => 'bi-file-excel text-success',
                                                    'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image text-info',
                                                    'mp4', 'avi', 'mov' => 'bi-file-play text-warning',
                                                    'mp3', 'wav' => 'bi-file-music text-purple',
                                                    'zip', 'rar' => 'bi-file-zip text-dark',
                                                    'php', 'js', 'css', 'html' => 'bi-file-code text-success',
                                                    default => 'bi-file-earmark text-muted'
                                                };
                                            @endphp
                                            <i class="bi {{ $icon }} display-4"></i>
                                            <div class="mt-2">
                                                <small class="text-truncate d-block">{{ $item['name'] }}</small>
                                                <small class="text-muted">{{ number_format($item['size'] / 1024, 1) }}KB</small>
                                            </div>
                                        @endif
                                    @if($item['type'] === 'directory' || in_array($item['extension'], ['txt', 'php', 'js', 'css', 'html', 'json', 'md', 'env', 'log']))
                                        </a>
                                    @else
                                        </div>
                                    @endif
                                    
                                    <!-- Action Icons -->
                                    <div class="card-footer p-1">
                                        <div class="btn-group w-100" role="group">
                                            @if($item['type'] === 'file')
                                                <a href="{{ route('master-admin.file-manager.view', ['path' => $item['path'], 'disk' => $disk]) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if(in_array($item['extension'], ['txt', 'php', 'js', 'css', 'html', 'json', 'md', 'env', 'log']))
                                                    <a href="{{ route('master-admin.file-manager.edit', ['path' => $item['path'], 'disk' => $disk]) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('master-admin.file-manager.download', ['path' => $item['path'], 'disk' => $disk]) }}" 
                                                   class="btn btn-sm btn-outline-success" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" 
                                                    onclick="confirmDelete('{{ $item['name'] }}', '{{ $item['path'] }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('master-admin.file-manager.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="path" value="{{ $currentPath }}">
                    <input type="hidden" name="disk" value="{{ $disk }}">
                    <div class="mb-3">
                        <label class="form-label">Select Files</label>
                        <input type="file" class="form-control" name="files[]" multiple required>
                        <div class="form-text">Max: 20MB per file</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Folder Modal -->
<div class="modal fade" id="newFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('master-admin.file-manager.create-directory') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="path" value="{{ $currentPath }}">
                    <input type="hidden" name="disk" value="{{ $disk }}">
                    <div class="mb-3">
                        <label class="form-label">Folder Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-danger">This cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('master-admin.file-manager.delete') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="disk" value="{{ $disk }}">
                    <input type="hidden" name="path" id="deleteItemPath">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(name, path) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteItemPath').value = path;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.file-item {
    transition: transform 0.2s;
}
.file-item:hover {
    transform: translateY(-2px);
}
</style>
@endsection

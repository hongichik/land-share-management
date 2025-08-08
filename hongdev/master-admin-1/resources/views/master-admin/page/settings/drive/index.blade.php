@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Google Drive Configuration')

@section('page_title', 'Google Drive Configuration')


@section('content')
<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('master-admin.settings.drive.update') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="filesystem_cloud" class="form-label">Filesystem Cloud</label>
                        <input type="text" class="form-control @error('filesystem_cloud') is-invalid @enderror" 
                               id="filesystem_cloud" name="filesystem_cloud" value="{{ old('filesystem_cloud', env('FILESYSTEM_CLOUD', 'google')) }}">
                        <div class="form-text">Set to "google" for Google Drive integration</div>
                        @error('filesystem_cloud')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client ID</label>
                        <input type="text" class="form-control @error('client_id') is-invalid @enderror" 
                               id="client_id" name="client_id" value="{{ old('client_id', $config['client_id']) }}">
                        <div class="form-text">Google Cloud Console OAuth Client ID</div>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="client_secret" class="form-label">Client Secret</label>
                        <input type="text" class="form-control @error('client_secret') is-invalid @enderror" 
                               id="client_secret" name="client_secret" 
                               value="{{ old('client_secret', ($config['client_secret'] === '••••••••' ? '••••••••' : $config['client_secret'])) }}">
                        <div class="form-text">Google Cloud Console OAuth Client Secret. Leave as is to keep existing value.</div>
                        @error('client_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="refresh_token" class="form-label">Refresh Token</label>
                        <input type="text" class="form-control @error('refresh_token') is-invalid @enderror" 
                               id="refresh_token" name="refresh_token" 
                               value="{{ old('refresh_token', ($config['refresh_token'] === '••••••••' ? '••••••••' : $config['refresh_token'])) }}">
                        <div class="form-text">OAuth Refresh Token. Leave as is to keep existing value.</div>
                        @error('refresh_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="access_token" class="form-label">Access Token (Optional)</label>
                        <input type="text" class="form-control @error('access_token') is-invalid @enderror" 
                               id="access_token" name="access_token" 
                               value="{{ old('access_token', env('GOOGLE_DRIVE_ACCESS_TOKEN', '')) }}">
                        <div class="form-text">OAuth Access Token (will be refreshed automatically if empty)</div>
                        @error('access_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="folder" class="form-label">Default Folder (Optional)</label>
                        <input type="text" class="form-control @error('folder') is-invalid @enderror" 
                               id="folder" name="folder" value="{{ old('folder', env('GOOGLE_DRIVE_FOLDER', '')) }}">
                        <div class="form-text">Google Drive Folder path or ID where files will be stored</div>
                        @error('folder')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>How to set up Google Drive integration:</h6>
                        <ol class="mb-0">
                            <li>Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                            <li>Create a new project or select an existing one</li>
                            <li>Enable the Google Drive API</li>
                            <li>Create OAuth consent screen (external)</li>
                            <li>Create OAuth 2.0 credentials (Web application)</li>
                            <li>Use the provided client ID and secret</li>
                            <li>Get refresh token using OAuth flow</li>
                        </ol>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.settings.index') }}" class="btn btn-secondary">Back to Settings</a>
                        <div>
                            <a href="{{ route('master-admin.settings.drive.test') }}" class="btn btn-info me-2">Test Connection</a>
                            <button type="submit" class="btn btn-primary">Save Configuration</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

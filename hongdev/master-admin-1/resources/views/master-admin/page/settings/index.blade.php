@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Settings')

@section('page_title', 'Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">System Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-gear-fill fs-1 text-primary mb-3"></i>
                                <h6>Environment</h6>
                                <p class="text-muted">Manage environment and debug settings</p>
                                <a href="{{ route('master-admin.settings.environment.index') }}" class="btn btn-primary">Configure</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-database-fill fs-1 text-success mb-3"></i>
                                <h6>Database</h6>
                                <p class="text-muted">Configure database connections</p>
                                <a href="{{ route('master-admin.settings.database.index') }}" class="btn btn-success">Configure</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-envelope-fill fs-1 text-warning mb-3"></i>
                                <h6>Mail</h6>
                                <p class="text-muted">Configure email settings</p>
                                <a href="{{ route('master-admin.settings.mail.index') }}" class="btn btn-warning">Configure</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-cloud-fill fs-1 text-info mb-3"></i>
                                <h6>Google Drive</h6>
                                <p class="text-muted">Configure cloud storage</p>
                                <a href="{{ route('master-admin.settings.drive.index') }}" class="btn btn-info">Configure</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

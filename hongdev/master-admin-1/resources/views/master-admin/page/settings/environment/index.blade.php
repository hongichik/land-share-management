@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Environment Settings')

@section('page_title', 'Environment Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Environment Configuration</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Current Environment</th>
                                <td class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ app()->environment() === 'production' ? 'danger' : (app()->environment() === 'testing' ? 'warning' : 'success') }}">
                                        {{ strtoupper(app()->environment()) }}
                                    </span>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-admin.settings.environment.change', 'local') }}" 
                                           class="btn btn-outline-success {{ app()->environment() === 'local' ? 'active' : '' }}">Local</a>
                                        <a href="{{ route('master-admin.settings.environment.change', 'testing') }}" 
                                           class="btn btn-outline-warning {{ app()->environment() === 'testing' ? 'active' : '' }}">Testing</a>
                                        <a href="{{ route('master-admin.settings.environment.change', 'production') }}" 
                                           class="btn btn-outline-danger {{ app()->environment() === 'production' ? 'active' : '' }}">Production</a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Debug Mode</th>
                                <td class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ config('app.debug') ? 'warning' : 'success' }}">
                                        {{ config('app.debug') ? 'ENABLED' : 'DISABLED' }}
                                    </span>
                                    <a href="{{ route('master-admin.settings.environment.debug', config('app.debug') ? 'off' : 'on') }}" 
                                       class="btn btn-sm {{ config('app.debug') ? 'btn-success' : 'btn-warning' }}">
                                        {{ config('app.debug') ? 'Disable Debug' : 'Enable Debug' }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h6><i class="bi bi-info-circle me-2"></i>Environment Information:</h6>
                    <ul class="mb-0">
                        <li><strong>Local:</strong> Development environment with full debugging</li>
                        <li><strong>Testing:</strong> Testing environment for automated tests</li>
                        <li><strong>Production:</strong> Live environment with optimized performance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

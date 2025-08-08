@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Welcome to Master Admin')

@section('page_title', 'Dashboard')


@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">System Performance</h5>
                <div>
                    <button id="refresh-metrics" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                    <div class="form-check form-switch d-inline-block ms-2">
                        <input class="form-check-input" type="checkbox" id="auto-refresh">
                        <label class="form-check-label" for="auto-refresh">Auto-refresh</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- CPU Usage -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-3">CPU Usage</h6>
                                <div class="position-relative d-inline-block">
                                    <canvas id="cpu-chart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <span id="cpu-percentage" class="fs-4 fw-bold">0%</span>
                                    </div>
                                </div>
                                <div class="mt-2" id="cpu-details">
                                    Cores: <span id="cpu-cores">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Memory Usage -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-3">Memory Usage</h6>
                                <div class="position-relative d-inline-block">
                                    <canvas id="memory-chart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <span id="memory-percentage" class="fs-4 fw-bold">0%</span>
                                    </div>
                                </div>
                                <div class="mt-2" id="memory-details">
                                    Used: <span id="memory-used">-</span> / <span id="memory-total">-</span><br>
                                    Free: <span id="memory-free">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Disk Usage -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-3">Disk Usage</h6>
                                <div class="position-relative d-inline-block">
                                    <canvas id="disk-chart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <span id="disk-percentage" class="fs-4 fw-bold">0%</span>
                                    </div>
                                </div>
                                <div class="mt-2" id="disk-details">
                                    Used: <span id="disk-used">-</span> / <span id="disk-total">-</span><br>
                                    Free: <span id="disk-free">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Server Load -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-3 text-center">Server Information</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Load (1m)</th>
                                            <td id="load-1min">-</td>
                                        </tr>
                                        <tr>
                                            <th>Load (5m)</th>
                                            <td id="load-5min">-</td>
                                        </tr>
                                        <tr>
                                            <th>Load (15m)</th>
                                            <td id="load-15min">-</td>
                                        </tr>
                                        <tr>
                                            <th>Uptime</th>
                                            <td id="system-uptime">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Cache Management</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('master-admin.commands.execute', 'cache-clear') }}" class="btn btn-outline-primary">
                        <i class="bi bi-trash"></i> Clear Application Cache
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'config-clear') }}" class="btn btn-outline-primary">
                        <i class="bi bi-trash"></i> Clear Config Cache
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'config-cache') }}" class="btn btn-outline-primary">
                        <i class="bi bi-gear-fill"></i> Cache Config
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'route-clear') }}" class="btn btn-outline-primary">
                        <i class="bi bi-trash"></i> Clear Route Cache
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'route-cache') }}" class="btn btn-outline-primary">
                        <i class="bi bi-signpost-split"></i> Cache Routes
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'view-clear') }}" class="btn btn-outline-primary">
                        <i class="bi bi-trash"></i> Clear View Cache
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'optimize-clear') }}" class="btn btn-outline-primary">
                        <i class="bi bi-lightning"></i> Clear All Caches
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'optimize') }}" class="btn btn-outline-primary">
                        <i class="bi bi-lightning-charge"></i> Optimize Application
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Database Management</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('master-admin.commands.execute', 'migrate') }}" class="btn btn-outline-primary">
                        <i class="bi bi-database"></i> Run Migrations
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'migrate-fresh') }}" class="btn btn-outline-danger">
                        <i class="bi bi-database-x"></i> Fresh Migrations
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'migrate-status') }}" class="btn btn-outline-primary">
                        <i class="bi bi-list-check"></i> Migration Status
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'db-seed') }}" class="btn btn-outline-primary">
                        <i class="bi bi-card-list"></i> Run Seeders
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'db-wipe') }}" class="btn btn-outline-danger">
                        <i class="bi bi-exclamation-triangle"></i> Wipe Database
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">System Management</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('master-admin.commands.execute', 'storage-link') }}" class="btn btn-outline-primary">
                        <i class="bi bi-link"></i> Create Storage Link
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'key-generate') }}" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> Generate App Key
                    </a>
                    <a href="#" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#maintenanceModeModal">
                        <i class="bi bi-slash-circle"></i> Maintenance Mode On
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'up') }}" class="btn btn-outline-success">
                        <i class="bi bi-check-circle"></i> Maintenance Mode Off
                    </a>
                    <a href="{{ route('master-admin.logs.view') }}" class="btn btn-outline-info">
                        <i class="bi bi-file-text"></i> View Laravel Logs
                    </a>
                    <a href="{{ route('master-admin.logs.clear') }}" class="btn btn-outline-warning">
                        <i class="bi bi-trash"></i> Clear Laravel Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Package Management</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('master-admin.commands.execute', 'vendor-publish-all') }}" class="btn btn-outline-primary">
                        <i class="bi bi-box-seam"></i> Publish All Vendor Assets
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'publish-master-admin-public') }}" class="btn btn-outline-info">
                        <i class="bi bi-folder-plus"></i> Publish Master Admin Assets
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'publish-master-admin-environment') }}" class="btn btn-outline-warning">
                        <i class="bi bi-gear-wide-connected"></i> Publish Master Admin Environment Files
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'package-discover') }}" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> Discover Packages
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'composer-dump-autoload') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-repeat"></i> Dump Composer Autoload
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Queue Management</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('master-admin.commands.execute', 'queue-restart') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise"></i> Restart Queue Workers
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'queue-retry-all') }}" class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat"></i> Retry All Failed Jobs
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'queue-clear') }}" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Clear Failed Jobs
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'schedule-list') }}" class="btn btn-outline-info">
                        <i class="bi bi-list-task"></i> List Scheduled Tasks
                    </a>
                    <a href="{{ route('master-admin.commands.execute', 'schedule-run') }}" class="btn btn-outline-primary">
                        <i class="bi bi-play-circle"></i> Run Scheduled Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">System Information</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Laravel Version</th>
                                <td>{{ app()->version() }}</td>
                            </tr>
                            <tr>
                                <th>PHP Version</th>
                                <td>{{ phpversion() }}</td>
                            </tr>
                            <tr>
                                <th>Server</th>
                                <td>{{ isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <th>Environment</th>
                                <td class="d-flex justify-content-between align-items-center">
                                    <span>{{ app()->environment() }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-admin.settings.environment.change', 'local') }}" class="btn btn-outline-primary {{ app()->environment() === 'local' ? 'active' : '' }}">Local</a>
                                        <a href="{{ route('master-admin.settings.environment.change', 'testing') }}" class="btn btn-outline-primary {{ app()->environment() === 'testing' ? 'active' : '' }}">Testing</a>
                                        <a href="{{ route('master-admin.settings.environment.change', 'production') }}" class="btn btn-outline-primary {{ app()->environment() === 'production' ? 'active' : '' }}">Production</a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Debug Mode</th>
                                <td class="d-flex justify-content-between align-items-center">
                                    <span>{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</span>
                                    <a href="{{ route('master-admin.settings.environment.debug', config('app.debug') ? 'off' : 'on') }}" 
                                       class="btn btn-sm {{ config('app.debug') ? 'btn-warning' : 'btn-success' }}">
                                        {{ config('app.debug') ? 'Disable Debug Mode' : 'Enable Debug Mode' }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Database Configuration</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Database Connection</th>
                                <td>{{ config('database.default') }}</td>
                            </tr>
                            <tr>
                                <th>Database Host</th>
                                <td>{{ config('database.connections.' . config('database.default') . '.host') }}</td>
                            </tr>
                            <tr>
                                <th>Database Name</th>
                                <td>{{ config('database.connections.' . config('database.default') . '.database') }}</td>
                            </tr>
                            <tr>
                                <th>Database Username</th>
                                <td>{{ config('database.connections.' . config('database.default') . '.username') }}</td>
                            </tr>
                            <tr>
                                <th>Database Status</th>
                                <td>
                                    @php
                                        try {
                                            DB::connection()->getPdo();
                                            $dbStatus = true;
                                        } catch (\Exception $e) {
                                            $dbStatus = false;
                                        }
                                    @endphp
                                    
                                    @if($dbStatus)
                                        <span class="badge bg-success">Connected</span>
                                    @else
                                        <span class="badge bg-danger">Disconnected</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Database Config</th>
                                <td>
                                    <a href="{{ route('master-admin.settings.database.test') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-database-check"></i> Test Connection
                                    </a>
                                    <a href="{{ route('master-admin.settings.database.index') }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-gear"></i> Configure Database
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Gmail Configuration</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Mail Driver</th>
                                <td>{{ config('mail.mailer') }}</td>
                            </tr>
                            <tr>
                                <th>Mail Host</th>
                                <td>{{ config('mail.mailers.smtp.host') }}</td>
                            </tr>
                            <tr>
                                <th>Mail Port</th>
                                <td>{{ config('mail.mailers.smtp.port') }}</td>
                            </tr>
                            <tr>
                                <th>Mail Username</th>
                                <td>{{ config('mail.mailers.smtp.username') }}</td>
                            </tr>
                            <tr>
                                <th>Mail From Address</th>
                                <td>{{ config('mail.from.address') }}</td>
                            </tr>
                            <tr>
                                <th>Mail From Name</th>
                                <td>{{ config('mail.from.name') }}</td>
                            </tr>
                            <tr>
                                <th>Mail Encryption</th>
                                <td>{{ config('mail.mailers.smtp.encryption') }}</td>
                            </tr>
                            <tr>
                                <th>Gmail Config</th>
                                <td>
                                    <a href="{{ route('master-admin.settings.mail.test') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-envelope-check"></i> Test Mail
                                    </a>
                                    <a href="{{ route('master-admin.settings.mail.index') }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-gear"></i> Configure Gmail
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Google Drive Configuration</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Client ID</th>
                                <td>{{ env('GOOGLE_DRIVE_CLIENT_ID') ? '✓ Set' : '✗ Not set' }}</td>
                            </tr>
                            <tr>
                                <th>Client Secret</th>
                                <td>{{ env('GOOGLE_DRIVE_CLIENT_SECRET') ? '✓ Set' : '✗ Not set' }}</td>
                            </tr>
                            <tr>
                                <th>Refresh Token</th>
                                <td>{{ env('GOOGLE_DRIVE_REFRESH_TOKEN') ? '✓ Set' : '✗ Not set' }}</td>
                            </tr>
                            <tr>
                                <th>Default Folder ID</th>
                                <td>{{ env('GOOGLE_DRIVE_FOLDER') ?: 'Not set' }}</td>
                            </tr>
                            <tr>
                                <th>Storage Status</th>
                                <td>
                                    @if(env('GOOGLE_DRIVE_CLIENT_ID') && env('GOOGLE_DRIVE_CLIENT_SECRET') && env('GOOGLE_DRIVE_REFRESH_TOKEN'))
                                        <span class="badge bg-success">Configured</span>
                                    @else
                                        <span class="badge bg-danger">Not Configured</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Google Drive Config</th>
                                <td>
                                    <a href="{{ route('master-admin.settings.drive.test') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-cloud-check"></i> Test Connection
                                    </a>
                                    <a href="{{ route('master-admin.settings.drive.index') }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-gear"></i> Configure Drive
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Maintenance Mode Warning Modal -->
<div class="modal fade" id="maintenanceModeModal" tabindex="-1" aria-labelledby="maintenanceModeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="maintenanceModeModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Maintenance Mode Warning</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Warning:</strong> Enabling maintenance mode will make your site inaccessible to regular users, including this admin panel.</p>
        
        <div class="alert alert-info">
          <h6><i class="bi bi-info-circle me-2"></i>How to disable maintenance mode if you get locked out:</h6>
          <ol class="mb-0">
            <li>Open terminal/command prompt</li>
            <li>Navigate to your Laravel project root</li>
            <li>Run command: <code>php artisan up</code></li>
          </ol>
        </div>
        
        <div class="alert alert-danger mt-3">
          <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Important:</strong> 
          After enabling maintenance mode, you will be locked out of this admin panel. 
          The only way to disable maintenance mode will be using the command line as described above.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="{{ route('master-admin.commands.execute', 'down') }}" class="btn btn-warning">Enable Maintenance Mode</a>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attach click handlers to buttons to show loading state
        document.querySelectorAll('.card-body a.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                this.classList.add('disabled');
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Performance monitoring
    document.addEventListener('DOMContentLoaded', function() {
        // Create charts
        const cpuChart = new Chart(document.getElementById('cpu-chart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#0d6efd', '#f8f9fa'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '75%',
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        const memoryChart = new Chart(document.getElementById('memory-chart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#fd7e14', '#f8f9fa'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '75%',
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        const diskChart = new Chart(document.getElementById('disk-chart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#20c997', '#f8f9fa'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '75%',
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        // Function to fetch and update metrics
        function updateMetrics() {
            fetch('{{ url("master-admin/system/metrics") }}')
                .then(response => response.json())
                .then(data => {
                    // Update CPU
                    document.getElementById('cpu-percentage').textContent = data.cpu.usage + '%';
                    document.getElementById('cpu-cores').textContent = data.cpu.cores;
                    cpuChart.data.datasets[0].data = [data.cpu.usage, 100 - data.cpu.usage];
                    cpuChart.update();
                    
                    // Update Memory
                    document.getElementById('memory-percentage').textContent = data.memory.percentage + '%';
                    document.getElementById('memory-used').textContent = data.memory.used;
                    document.getElementById('memory-total').textContent = data.memory.total;
                    document.getElementById('memory-free').textContent = data.memory.free;
                    memoryChart.data.datasets[0].data = [data.memory.percentage, 100 - data.memory.percentage];
                    memoryChart.update();
                    
                    // Update Disk
                    document.getElementById('disk-percentage').textContent = data.disk.percentage + '%';
                    document.getElementById('disk-used').textContent = data.disk.used;
                    document.getElementById('disk-total').textContent = data.disk.total;
                    document.getElementById('disk-free').textContent = data.disk.free;
                    diskChart.data.datasets[0].data = [data.disk.percentage, 100 - data.disk.percentage];
                    diskChart.update();
                    
                    // Update Server Load
                    document.getElementById('load-1min').textContent = data.server_load['1min'];
                    document.getElementById('load-5min').textContent = data.server_load['5min'];
                    document.getElementById('load-15min').textContent = data.server_load['15min'];
                    document.getElementById('system-uptime').textContent = data.uptime;
                })
                .catch(error => {
                    console.error('Error fetching metrics:', error);
                });
        }
        
        // Initial fetch
        updateMetrics();
        
        // Setup refresh button
        document.getElementById('refresh-metrics').addEventListener('click', updateMetrics);
        
        // Auto-refresh toggle
        let refreshInterval = null;
        document.getElementById('auto-refresh').addEventListener('change', function(e) {
            if (e.target.checked) {
                refreshInterval = setInterval(updateMetrics, 5000); // Refresh every 5 seconds
            } else {
                clearInterval(refreshInterval);
            }
        });
    });
</script>
@endpush

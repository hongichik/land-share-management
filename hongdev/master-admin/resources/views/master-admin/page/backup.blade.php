{{-- filepath: /Users/admin/Documents/code/thu_vien/laravel/vendor/hongdev/master-admin/resources/views/master-admin/page/backup.blade.php --}}
@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Backup')

@section('page_title', 'Backup & Restore')


@section('content')
<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card mb-4">
            <div class="card-body">
                <form id="backup-form" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Backup Type</label>
                        <select class="form-select" name="type" id="backup-type">
                            <option value="database">Database Only (.sql)</option>
                            <option value="storage">Storage (storage/app/public)</option>
                            <option value="full">Full Project (zip, exclude vendor/node_modules)</option>
                            <option value="all">Backup All (database + storage + full)</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" id="btn-backup">Start Backup</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Backup Files</h5>
            </div>
            <div class="card-body">
                @if(!empty($backups))
                <div class="accordion" id="backupAccordion">
                    @foreach($backups as $year => $days)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-year-{{ $year }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-year-{{ $year }}" aria-expanded="false" aria-controls="collapse-year-{{ $year }}">
                                {{ $year }} ({{ count($days) }} days)
                            </button>
                        </h2>
                        <div id="collapse-year-{{ $year }}" class="accordion-collapse collapse" aria-labelledby="heading-year-{{ $year }}" data-bs-parent="#backupAccordion">
                            <div class="accordion-body p-0">
                                <div class="accordion" id="accordion-{{ $year }}">
                                    @foreach($days as $date => $files)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $year }}-{{ str_replace('-', '', $date) }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $year }}-{{ str_replace('-', '', $date) }}" aria-expanded="false" aria-controls="collapse-{{ $year }}-{{ str_replace('-', '', $date) }}">
                                                {{ $date }}
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $year }}-{{ str_replace('-', '', $date) }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $year }}-{{ str_replace('-', '', $date) }}" data-bs-parent="#accordion-{{ $year }}">
                                            <div class="accordion-body p-0">
                                                <ul class="list-group list-group-flush">
                                                    @foreach($files as $file)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $file['name'] ?? '' }}
                                                        <span>
                                                            <a href="{{ $file['download_url'] ?? '#' }}" class="btn btn-sm btn-outline-primary" target="_blank">Download</a>
                                                        </span>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-info mb-0">
                    No backup files found on Google Drive.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnBackup = document.getElementById('btn-backup');
    const backupType = document.getElementById('backup-type');
    let lastBackupType = 'database';

    btnBackup.addEventListener('click', function() {
        btnBackup.disabled = true;
        btnBackup.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        let url = '';
        if (backupType.value === 'database') {
            url = '{{ route("master-admin.backup.database") }}';
        } else if (backupType.value === 'storage') {
            url = '{{ route("master-admin.backup.storage") }}';
        } else if (backupType.value === 'full') {
            url = '{{ route("master-admin.backup.full") }}';
        } else if (backupType.value === 'all') {
            url = '{{ route("master-admin.backup.all") }}';
        }
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json())
        .then(data => {
            btnBackup.disabled = false;
            btnBackup.innerHTML = 'Start Backup';
            if(data.success) {
                lastBackupType = backupType.value;
                if (data.filename) {
                    alert('Backup created: ' + data.filename);
                } else if (data.results) {
                    // Handle backup all response
                    let messages = [];
                    if (data.results.database && data.results.database.filename) {
                        messages.push('Database: ' + data.results.database.filename);
                    }
                    if (data.results.storage && data.results.storage.filename) {
                        messages.push('Storage: ' + data.results.storage.filename);
                    }
                    if (data.results.full && data.results.full.filename) {
                        messages.push('Full: ' + data.results.full.filename);
                    }
                    alert('Backups created:\n' + messages.join('\n'));
                } else {
                    alert('Backup completed successfully');
                }
                location.reload();
            } else {
                alert('Backup failed: ' + (data.message || 'Unknown error'));
            }
        }).catch(() => {
            btnBackup.disabled = false;
            btnBackup.innerHTML = 'Start Backup';
            alert('Backup failed.');
        });
    });
});
</script>
@endpush
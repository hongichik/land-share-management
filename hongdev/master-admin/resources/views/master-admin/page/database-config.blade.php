@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Database Configuration')

@section('page_title', 'Database Configuration')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ url('master-admin/settings/database/config') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="connection" class="form-label">Database Driver</label>
                        <select name="connection" id="connection" class="form-select @error('connection') is-invalid @enderror">
                            @foreach($drivers as $driver)
                                <option value="{{ $driver }}" {{ $config['connection'] == $driver ? 'selected' : '' }}>
                                    {{ ucfirst($driver) }}
                                </option>
                            @endforeach
                        </select>
                        @error('connection')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="host" class="form-label">Database Host</label>
                        <input type="text" class="form-control @error('host') is-invalid @enderror" 
                               id="host" name="host" value="{{ old('host', $config['host']) }}">
                        @error('host')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="port" class="form-label">Database Port</label>
                        <input type="text" class="form-control @error('port') is-invalid @enderror" 
                               id="port" name="port" value="{{ old('port', $config['port']) }}">
                        @error('port')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="database" class="form-label">Database Name</label>
                        <input type="text" class="form-control @error('database') is-invalid @enderror" 
                               id="database" name="database" value="{{ old('database', $config['database']) }}">
                        @error('database')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Database Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username', $config['username']) }}">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Database Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Leave blank to keep current password">
                        <div class="form-text">Leave blank to keep the current password.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ url('master-admin') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

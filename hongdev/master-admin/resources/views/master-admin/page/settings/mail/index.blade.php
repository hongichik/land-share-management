@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Mail Configuration')

@section('page_title', 'Mail Configuration')

@section('content')
<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('master-admin.settings.mail.update') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="driver" class="form-label">Mail Driver</label>
                        <select name="driver" id="driver" class="form-select @error('driver') is-invalid @enderror">
                            <option value="smtp" {{ $config['driver'] == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="sendmail" {{ $config['driver'] == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                            <option value="mailgun" {{ $config['driver'] == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="ses" {{ $config['driver'] == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            <option value="log" {{ $config['driver'] == 'log' ? 'selected' : '' }}>Log</option>
                        </select>
                        @error('driver')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="host" class="form-label">SMTP Host</label>
                        <input type="text" class="form-control @error('host') is-invalid @enderror" 
                               id="host" name="host" value="{{ old('host', $config['host']) }}">
                        <div class="form-text">For Gmail: smtp.gmail.com</div>
                        @error('host')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="port" class="form-label">SMTP Port</label>
                        <input type="text" class="form-control @error('port') is-invalid @enderror" 
                               id="port" name="port" value="{{ old('port', $config['port']) }}">
                        <div class="form-text">For Gmail: 587 (TLS) or 465 (SSL)</div>
                        @error('port')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">SMTP Username</label>
                        <input type="email" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username', $config['username']) }}">
                        <div class="form-text">Your Gmail address</div>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">SMTP Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Leave blank to keep current password">
                        <div class="form-text">For Gmail, use an <a href="https://myaccount.google.com/apppasswords" target="_blank">App Password</a> (not your regular password)</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="encryption" class="form-label">Encryption</label>
                        <select name="encryption" id="encryption" class="form-select @error('encryption') is-invalid @enderror">
                            <option value="tls" {{ $config['encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $config['encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="" {{ $config['encryption'] == '' ? 'selected' : '' }}>None</option>
                        </select>
                        <div class="form-text">For Gmail, use TLS</div>
                        @error('encryption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="from_address" class="form-label">From Address</label>
                        <input type="email" class="form-control @error('from_address') is-invalid @enderror" 
                               id="from_address" name="from_address" value="{{ old('from_address', $config['from_address']) }}">
                        @error('from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" class="form-control @error('from_name') is-invalid @enderror" 
                               id="from_name" name="from_name" value="{{ old('from_name', $config['from_name']) }}">
                        @error('from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Gmail Setup Instructions:</h6>
                        <ol class="mb-0">
                            <li>Enable 2-Step Verification on your Google account</li>
                            <li>Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">Google Account Security</a></li>
                            <li>Use that App Password (not your regular password) in this form</li>
                            <li>Make sure "Less secure app access" is turned off</li>
                        </ol>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.settings.index') }}" class="btn btn-secondary">Back to Settings</a>
                        <div>
                            <a href="{{ route('master-admin.settings.mail.test') }}" class="btn btn-info me-2">Test Mail</a>
                            <button type="submit" class="btn btn-primary">Save Configuration</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

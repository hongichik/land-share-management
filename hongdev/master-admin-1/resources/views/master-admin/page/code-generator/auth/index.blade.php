{{-- filepath: /Users/admin/Documents/code/thu_vien/laravel/vendor/hongdev/master-admin/resources/views/master-admin/page/backup.blade.php --}}
@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Auth Generator')

@section('page_title', 'Authentication System Generator')

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Progress Steps -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="step-item active" id="step-1-indicator">
                        <div class="step-number">1</div>
                        <div class="step-label">Choose Auth Type</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" id="step-2-indicator">
                        <div class="step-number">2</div>
                        <div class="step-label">Configure Migrations</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" id="step-3-indicator">
                        <div class="step-number">3</div>
                        <div class="step-label">Generate & Review</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title" id="step-title">Step 1: Choose Authentication Type</h5>
                <p class="text-muted mb-0" id="step-description">Select the type of authentication system you want to generate</p>
            </div>
            <div class="card-body">
                <!-- Step 1: Authentication Type Selection -->
                <div id="step-1" class="wizard-step">
                    <!-- Authentication Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">Authentication Type</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card auth-option {{ old('auth_type') === 'single' ? 'border-primary' : '' }}">
                                    <div class="card-body text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="auth_type" id="single_auth" 
                                                   value="single" {{ old('auth_type', 'single') === 'single' ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="single_auth">
                                                <i class="bi bi-person-circle display-4 text-primary d-block mb-3"></i>
                                                <h5>Single Authentication</h5>
                                                <p class="text-muted">
                                                    One users table for all user types. Simple and straightforward.
                                                </p>
                                                <div class="features-list text-start mt-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-check text-success"></i> Users table<br>
                                                        <i class="bi bi-check text-success"></i> Login/Register<br>
                                                        <i class="bi bi-check text-success"></i> Role-based permissions
                                                    </small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card auth-option {{ old('auth_type') === 'dual' ? 'border-primary' : '' }}">
                                    <div class="card-body text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="auth_type" id="dual_auth" 
                                                   value="dual" {{ old('auth_type') === 'dual' ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="dual_auth">
                                                <i class="bi bi-people-fill display-4 text-warning d-block mb-3"></i>
                                                <h5>Dual Authentication</h5>
                                                <p class="text-muted">
                                                    Separate tables for admins and users. Complete separation of concerns.
                                                </p>
                                                <div class="features-list text-start mt-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-check text-success"></i> Admins table<br>
                                                        <i class="bi bi-check text-success"></i> Users table<br>
                                                        <i class="bi bi-check text-success"></i> Separate login systems
                                                    </small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Features -->
                    <div class="mb-4">
                        <label class="form-label">Additional Features</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="forgot_password" 
                                           id="forgot_password" {{ in_array('forgot_password', old('features', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="forgot_password">
                                        <i class="bi bi-key text-warning me-2"></i>
                                        Forgot Password Functionality
                                    </label>
                                    <div class="form-text">Generate password reset controllers and views</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="email_verification" 
                                           id="email_verification" {{ in_array('email_verification', old('features', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_verification">
                                        <i class="bi bi-envelope-check text-info me-2"></i>
                                        Email Verification
                                    </label>
                                    <div class="form-text">Require email verification for new accounts</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="remember_me" 
                                           id="remember_me" {{ in_array('remember_me', old('features', ['remember_me'])) ? 'checked' : '' }} checked>
                                    <label class="form-check-label" for="remember_me">
                                        <i class="bi bi-clock-history text-success me-2"></i>
                                        Remember Me Functionality
                                    </label>
                                    <div class="form-text">Allow users to stay logged in</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="profile_management" 
                                           id="profile_management" {{ in_array('profile_management', old('features', ['profile_management'])) ? 'checked' : '' }} checked>
                                    <label class="form-check-label" for="profile_management">
                                        <i class="bi bi-person-gear text-primary me-2"></i>
                                        Profile Management
                                    </label>
                                    <div class="form-text">User profile editing functionality</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Permission System -->
                    <div class="mb-4">
                        <label class="form-label">Role & Permission System</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="roles_permissions" 
                                           id="roles_permissions" {{ in_array('roles_permissions', old('features', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="roles_permissions">
                                        <i class="bi bi-shield-check text-success me-2"></i>
                                        Roles & Permissions System
                                    </label>
                                    <div class="form-text">Generate roles and permissions tables with middleware</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6" id="permission-type-container" style="display: none;">
                                <label class="form-label">Permission Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="permission_type" value="simple" 
                                           id="simple_permissions" {{ old('permission_type', 'simple') === 'simple' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="simple_permissions">
                                        <i class="bi bi-person-badge text-info me-2"></i>
                                        Simple Roles (Admin, User, Moderator)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="permission_type" value="advanced" 
                                           id="advanced_permissions" {{ old('permission_type') === 'advanced' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="advanced_permissions">
                                        <i class="bi bi-shield-lock text-warning me-2"></i>
                                        Advanced Permissions (Granular control)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="role-details" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle me-2"></i>What will be created:</h6>
                                <div id="simple-role-info" style="display: none;">
                                    <ul class="mb-0">
                                        <li><strong>Roles table:</strong> id, name, slug, description</li>
                                        <li><strong>User-Role pivot:</strong> Many-to-many relationship</li>
                                        <li><strong>Default roles:</strong> Admin, User, Moderator</li>
                                        <li><strong>Middleware:</strong> Role-based route protection</li>
                                        <li><strong>Helper methods:</strong> hasRole(), assignRole(), etc.</li>
                                    </ul>
                                </div>
                                <div id="advanced-role-info" style="display: none;">
                                    <ul class="mb-0">
                                        <li><strong>Roles table:</strong> id, name, slug, description</li>
                                        <li><strong>Permissions table:</strong> id, name, slug, description</li>
                                        <li><strong>Role-Permission pivot:</strong> Many-to-many relationship</li>
                                        <li><strong>User-Role pivot:</strong> Many-to-many relationship</li>
                                        <li><strong>Middleware:</strong> Permission-based route protection</li>
                                        <li><strong>Helper methods:</strong> hasPermission(), can(), assignRole(), etc.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1 Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="button" class="btn btn-primary" id="next-to-step-2">
                            Next: Configure Migrations <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Migration Configuration -->
                <div id="step-2" class="wizard-step" style="display: none;">
                    <!-- Users Table Configuration -->
                    <div class="mb-4">
                        <h6><i class="bi bi-table me-2"></i>Users Table Configuration</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_name" checked>
                                            <label class="form-check-label" for="users_name">Name field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_email" checked>
                                            <label class="form-check-label" for="users_email">Email field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_password" checked>
                                            <label class="form-check-label" for="users_password">Password field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_phone">
                                            <label class="form-check-label" for="users_phone">Phone field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_avatar">
                                            <label class="form-check-label" for="users_avatar">Avatar field</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_status">
                                            <label class="form-check-label" for="users_status">Status field (active/inactive)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_email_verified" checked>
                                            <label class="form-check-label" for="users_email_verified">Email verified at</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_timestamps" checked>
                                            <label class="form-check-label" for="users_timestamps">Timestamps (created_at, updated_at)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_soft_deletes">
                                            <label class="form-check-label" for="users_soft_deletes">Soft deletes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="users_remember_token" checked>
                                            <label class="form-check-label" for="users_remember_token">Remember token</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admins Table Configuration (if dual auth) -->
                    <div class="mb-4" id="admins-config" style="display: none;">
                        <h6><i class="bi bi-shield-person me-2"></i>Admins Table Configuration</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_name" checked>
                                            <label class="form-check-label" for="admins_name">Name field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_email" checked>
                                            <label class="form-check-label" for="admins_email">Email field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_password" checked>
                                            <label class="form-check-label" for="admins_password">Password field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_role" checked>
                                            <label class="form-check-label" for="admins_role">Role field (super_admin, admin, moderator)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_status" checked>
                                            <label class="form-check-label" for="admins_status">Status field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_last_login">
                                            <label class="form-check-label" for="admins_last_login">Last login field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_timestamps" checked>
                                            <label class="form-check-label" for="admins_timestamps">Timestamps</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="admins_remember_token" checked>
                                            <label class="form-check-label" for="admins_remember_token">Remember token</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles Table Configuration (if roles enabled) -->
                    <div class="mb-4" id="roles-config" style="display: none;">
                        <h6><i class="bi bi-people me-2"></i>Roles Table Configuration</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="roles_name" checked>
                                            <label class="form-check-label" for="roles_name">Name field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="roles_slug" checked>
                                            <label class="form-check-label" for="roles_slug">Slug field</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="roles_description">
                                            <label class="form-check-label" for="roles_description">Description field</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="roles_color">
                                            <label class="form-check-label" for="roles_color">Color field (for UI)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="roles_level">
                                            <label class="form-check-label" for="roles_level">Level field (hierarchy)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="roles_timestamps" checked>
                                            <label class="form-check-label" for="roles_timestamps">Timestamps</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Configuration -->
                    <div class="mb-4">
                        <h6><i class="bi bi-database me-2"></i>Database Configuration</h6>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> The generator will create migration files based on your configuration.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="run_migrations" 
                                           id="run_migrations" {{ in_array('run_migrations', old('features', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="run_migrations">
                                        <i class="bi bi-database text-success me-2"></i>
                                        Run Migrations Automatically
                                    </label>
                                    <div class="form-text">Automatically run migrations after generation</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="seed_data" 
                                           id="seed_data" {{ in_array('seed_data', old('features', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="seed_data">
                                        <i class="bi bi-person-plus text-warning me-2"></i>
                                        Generate Sample Data
                                    </label>
                                    <div class="form-text">Create seeders with sample users/admins</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 Actions -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" id="back-to-step-1">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary" id="next-to-step-3">
                            Next: Review & Generate <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Review & Generate -->
                <div id="step-3" class="wizard-step" style="display: none;">
                    <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Form disabled for UI testing');">
                        @csrf
                        
                        <!-- Hidden inputs for all configurations will be generated by JavaScript -->
                        <div id="hidden-inputs"></div>
                        
                        <!-- Review Summary -->
                        <div class="mb-4">
                            <h6><i class="bi bi-list-check me-2"></i>Review Your Configuration</h6>
                            <div id="configuration-summary" class="alert alert-secondary">
                                <!-- Summary will be populated by JavaScript -->
                            </div>
                        </div>

                        <!-- Final Actions -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="back-to-step-2">
                                <i class="bi bi-arrow-left"></i> Back to Configure
                            </button>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-gear-fill"></i> Generate Authentication System
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-option {
    transition: all 0.3s ease;
    cursor: pointer;
}

.auth-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.auth-option.border-primary {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-item.active .step-number {
    background: #0d6efd;
    color: white;
}

.step-item.completed .step-number {
    background: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}

.step-item.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 20px;
    align-self: center;
    margin-top: -20px;
}

.wizard-step {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    
    // Step navigation functions
    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
        
        // Show current step
        document.getElementById(`step-${step}`).style.display = 'block';
        
        // Update step indicators
        document.querySelectorAll('.step-item').forEach((el, index) => {
            el.classList.remove('active', 'completed');
            if (index + 1 === step) {
                el.classList.add('active');
            } else if (index + 1 < step) {
                el.classList.add('completed');
            }
        });
        
        // Update titles
        const titles = {
            1: 'Step 1: Choose Authentication Type',
            2: 'Step 2: Configure Migrations',
            3: 'Step 3: Review & Generate'
        };
        
        const descriptions = {
            1: 'Select the type of authentication system you want to generate',
            2: 'Configure database tables and their fields',
            3: 'Review your configuration and generate the authentication system'
        };
        
        document.getElementById('step-title').textContent = titles[step];
        document.getElementById('step-description').textContent = descriptions[step];
        
        currentStep = step;
        
        // Update step 2 visibility based on selections
        if (step === 2) {
            updateStep2Visibility();
        }
        
        // Update step 3 summary
        if (step === 3) {
            updateStep3Summary();
        }
    }
    
    function updateStep2Visibility() {
        const authType = document.querySelector('input[name="auth_type"]:checked').value;
        const hasRoles = document.getElementById('roles_permissions').checked;
        
        // Show/hide admins config
        const adminsConfig = document.getElementById('admins-config');
        if (authType === 'dual') {
            adminsConfig.style.display = 'block';
        } else {
            adminsConfig.style.display = 'none';
        }
        
        // Show/hide roles config
        const rolesConfig = document.getElementById('roles-config');
        if (hasRoles) {
            rolesConfig.style.display = 'block';
        } else {
            rolesConfig.style.display = 'none';
        }
    }
    
    function updateStep3Summary() {
        const authType = document.querySelector('input[name="auth_type"]:checked').value;
        const features = Array.from(document.querySelectorAll('input[name="features[]"]:checked')).map(el => el.value);
        
        let summary = `<h6>Authentication Type: ${authType === 'single' ? 'Single Table' : 'Dual Tables'}</h6>`;
        summary += `<p><strong>Features:</strong> ${features.length ? features.join(', ') : 'None selected'}</p>`;
        
        // Add table configurations
        summary += '<h6>Tables to be created:</h6><ul>';
        summary += '<li>Users table with configured fields</li>';
        
        if (authType === 'dual') {
            summary += '<li>Admins table with configured fields</li>';
        }
        
        if (features.includes('roles_permissions')) {
            summary += '<li>Roles table</li>';
            summary += '<li>Role pivot tables</li>';
            
            const permissionType = document.querySelector('input[name="permission_type"]:checked')?.value;
            if (permissionType === 'advanced') {
                summary += '<li>Permissions table</li>';
            }
        }
        
        summary += '</ul>';
        
        document.getElementById('configuration-summary').innerHTML = summary;
        
        // Generate hidden inputs
        generateHiddenInputs();
    }
    
    function generateHiddenInputs() {
        const hiddenInputsContainer = document.getElementById('hidden-inputs');
        hiddenInputsContainer.innerHTML = '';
        
        // Add all form data as hidden inputs
        const authType = document.querySelector('input[name="auth_type"]:checked').value;
        hiddenInputsContainer.innerHTML += `<input type="hidden" name="auth_type" value="${authType}">`;
        
        // Add features
        document.querySelectorAll('input[name="features[]"]:checked').forEach(el => {
            hiddenInputsContainer.innerHTML += `<input type="hidden" name="features[]" value="${el.value}">`;
        });
        
        // Add permission type if roles enabled
        const permissionType = document.querySelector('input[name="permission_type"]:checked')?.value;
        if (permissionType) {
            hiddenInputsContainer.innerHTML += `<input type="hidden" name="permission_type" value="${permissionType}">`;
        }
        
        // Add table configurations (you can expand this based on your needs)
        // For now, we'll just add a summary
        hiddenInputsContainer.innerHTML += `<input type="hidden" name="table_config" value="configured">`;
    }
    
    // Event listeners
    document.getElementById('next-to-step-2').addEventListener('click', () => showStep(2));
    document.getElementById('back-to-step-1').addEventListener('click', () => showStep(1));
    document.getElementById('next-to-step-3').addEventListener('click', () => showStep(3));
    document.getElementById('back-to-step-2').addEventListener('click', () => showStep(2));
    
    // Update step 2 when auth type or roles change
    document.querySelectorAll('input[name="auth_type"]').forEach(input => {
        input.addEventListener('change', () => {
            if (currentStep === 2) updateStep2Visibility();
        });
    });
    
    document.getElementById('roles_permissions').addEventListener('change', () => {
        if (currentStep === 2) updateStep2Visibility();
    });
    
    // Initialize
    showStep(1);
    
    // ...existing JavaScript for auth options and roles...
});
</script>
@endsection

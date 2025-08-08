@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Edit Employee: {{ $employee->staff->username }}</h4>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.employees.update', $employee->employee_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username"
                                   value="{{ old('username', $employee->staff->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email', $employee->staff->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror"
                                   id="department" name="department"
                                   value="{{ old('department', $employee->department) }}" required>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control @error('position') is-invalid @enderror"
                                   id="position" name="position"
                                   value="{{ old('position', $employee->position) }}" required>
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="hire_date" class="form-label">Hire Date</label>
                            <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                   id="hire_date" name="hire_date"
                                   value="{{ old('hire_date', $employee->hire_date) }}" required>
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-3">
                            
                            <button type="submit" class="btn btn-primary px-4">
                                     <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-1"></i> Update Employee
                                </a>
                                </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-header {
        border-radius: 1rem 1rem 0 0 !important;
    }

    .form-control {
        border-radius: 0.5rem;
        padding: 10px 15px;
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        border-radius: 0.5rem;
    }

    .btn-primary:hover {
        background-color: #3a5ec0;
        border-color: #3a5ec0;
    }

    .btn-outline-secondary {
        border-radius: 0.5rem;
    }

    .invalid-feedback {
        display: block;
        margin-top: 0.25rem;
        color: #dc3545;
    }

    .is-invalid {
        border-color: #dc3545;
    }
</style>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Employee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <!-- Bootstrap CSS & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.05);
        }

        .form-control-lg {
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            font-size: 1rem;
        }

        .form-control-lg:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .form-label {
            font-weight: 500;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .btn-primary:hover {
            background-color: #365dcf;
        }

        .toggle-password {
            border-radius: 0 0.75rem 0.75rem 0;
        }
    </style>
</head>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            <i class="fas fa-user-plus me-2 text-primary"></i>Add New Employee
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark rounded-pill shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>

    </div>

    <div class="card">
        <form id="employeeForm" class="needs-validation" novalidate x-data="adminDashboard" @submit.prevent="addEmployee">
            <div class="card-header bg-white border-bottom">
                <h3 class="mb-0">Employee Details</h3>
            <div class="row g-0">
                <!-- Left Side -->
                <div class="col-md-6 p-4 bg-white">
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" x-model="formData.username" id="username" class="form-control form-control-lg" required>
                        <div class="invalid-feedback">Please enter a username.</div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" x-model="formData.email" id="email" class="form-control form-control-lg" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>

                    <div class="mb-4">
                        <label for="position" class="form-label">Position</label>
                        <input type="text"  x-model="formData.position" id="position" class="form-control form-control-lg" required>
                        <div class="invalid-feedback">Please enter a position.</div>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-6 p-4 bg-light">
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" x-model="formData.password" id="password" class="form-control form-control-lg" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 8 characters with at least one number.</small>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" x-model="formData.password_confirmation" id="password_confirmation" class="form-control form-control-lg" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="hire_date" class="form-label">Hire Date</label>
                        <input type="date" x-model="formData.hire_date" id="hire_date" class="form-control form-control-lg" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end p-4 border-top">
                <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 me-3">
                    <i class="fas fa-undo me-2"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Save Employee
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap + JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Password Visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Form Validation + Spinner
    const form = document.getElementById('employeeForm');
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Processing...`;
        }
        form.classList.add('was-validated');
    });

    document.addEventListener('alpine:init', () => {
    Alpine.data('adminDashboard', () => ({
        showAddEmployeeModal: false,
        showEditEmployeeModal: false,
        showAddCategoryModal: false,
        isLoading: false,
        errors: {},
        formData: {
            // Employee form data
            username: '',
            email: '',
            password: '',
            password_confirmation: '',
            position: '',
            hire_date: '',
            // Category form data
            name: '',
            slug: ''
        },
        currentEmployeeId: null,
        currentCategoryId: null,

        init() {
            // Initialize any date pickers or other components
            this.initDateRangePicker();
        },

        initDateRangePicker() {
            $('#dateRangePicker').daterangepicker({
                opens: 'right',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        },

        async addEmployee() {
            this.isLoading = true;
            this.errors = {};
            
            
            try {
                const response = await fetch("{{ url('/api/admin/employees') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Authorization': 'Bearer {{ Auth::user()->api_token }}'

                    },
                    body: JSON.stringify({
                        username: this.formData.username,
                        email: this.formData.email,
                        password: this.formData.password,
                        password_confirmation: this.formData.password_confirmation,
                        position: this.formData.position,
                        hire_date: this.formData.hire_date
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                    }
                    throw new Error(data.message || 'Failed to add employee');
                }

                // Success - show notification and reload
                Swal.fire({
                    title: 'Success!',
                    text: 'Employee added successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });

            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } finally {
                this.isLoading = false;
            }
        },

        async addCategory() {
            this.isLoading = true;
            this.errors = {};
            
            try {
                const response = await fetch('/admin/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: this.formData.name,
                        slug: this.formData.slug || this.generateSlug(this.formData.name)
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                    }
                    throw new Error(data.message || 'Failed to add category');
                }

                // Success - show notification and reload
                Swal.fire({
                    title: 'Success!',
                    text: 'Category added successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });

            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } finally {
                this.isLoading = false;
            }
        },

        generateSlug(name) {
            return name.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove non-word chars
                .replace(/\s+/g, '-')      // Replace spaces with -
                .replace(/--+/g, '-');     // Replace multiple - with single -
        },

        openAddEmployeeModal() {
            this.resetEmployeeForm();
            this.showAddEmployeeModal = true;
        },

        openAddCategoryModal() {
            this.resetCategoryForm();
            this.showAddCategoryModal = true;
        },

        resetEmployeeForm() {
            this.formData.username = '';
            this.formData.email = '';
            this.formData.password = '';
            this.formData.password_confirmation = '';
            this.formData.position = '';
            this.formData.hire_date = '';
            this.errors = {};
        },

        resetCategoryForm() {
            this.formData.name = '';
            this.formData.slug = '';
            this.errors = {};
        },

        // Other existing functions like confirmDelete, etc.
        confirmDelete(type, id) {
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete this ${type}!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performDelete(type, id);
                }
            });
        },

        async performDelete(type, id) {
            try {
                const response = await fetch(`/admin/${type}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || `Failed to delete ${type}`);
                }

                Swal.fire(
                    'Deleted!',
                    `${type.charAt(0).toUpperCase() + type.slice(1)} has been deleted.`,
                    'success'
                ).then(() => {
                    window.location.reload();
                });

            } catch (error) {
                Swal.fire(
                    'Error!',
                    error.message,
                    'error'
                );
            }
        }
    }));
});
</script>
</body>
</html>

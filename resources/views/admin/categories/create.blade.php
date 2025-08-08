<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Category</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            <i class="fas fa-tags me-2 text-primary"></i>Add New Category
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark rounded-pill shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="card">
        <form id="categoryForm" class="needs-validation" novalidate x-data="addCategoryPage" @submit.prevent="submitCategory">
            <div class="card-header bg-white border-bottom">
                <h3 class="mb-0">Category Details</h3>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" x-model="formData.name" id="name" class="form-control form-control-lg" required>
                    <div class="invalid-feedback">Please enter a category name.</div>
                </div>

                <div class="mb-4">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" x-model="formData.slug" id="slug" class="form-control form-control-lg" required>
                    <div class="invalid-feedback">Please enter a slug.</div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end p-4 border-top">
                <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 me-3">
                    <i class="fas fa-undo me-2"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Form Validation with Bootstrap styles
    const form = document.getElementById('categoryForm');
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
        Alpine.data('addCategoryPage', () => ({
            formData: {
                name: '',
                slug: ''
            },
            errors: {},
            isLoading: false,

            async submitCategory() {
                this.errors = {};
                this.isLoading = true;

                try {
                    const response = await fetch("{{ url('/api/admin/categories') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Authorization': 'Bearer {{ Auth::user()->api_token }}',
                        },
                        body: JSON.stringify({
                            name: this.formData.name,
                            slug: this.formData.slug
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.errors || {};
                        }
                        throw new Error(data.message || 'Failed to add category');
                    }

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
            }
        }));
    });
</script>
</body>
</html>

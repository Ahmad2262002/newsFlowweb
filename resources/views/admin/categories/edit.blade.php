@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Category
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark rounded-pill shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Categories
        </a>
    </div>

    <div class="card shadow-sm">
        <form id="editCategoryForm">
            @csrf
            @method('PUT')
            <div class="card-header bg-white border-bottom">
                <h3 class="mb-0">Category Details</h3>
            </div>
            
            <div class="card-body p-4">
                <div class="mb-4">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" name="name" id="name" 
                           class="form-control form-control-lg" 
                           value="{{ old('name', $category->name ?? '') }}"
                           required>
                    <div class="invalid-feedback" id="name-error"></div>
                </div>

                <div class="mb-4">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" 
                           class="form-control form-control-lg" 
                           value="{{ old('slug', $category->slug ?? '') }}"
                           required>
                    <div class="invalid-feedback" id="slug-error"></div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end p-4 border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-3" onclick="window.history.back()">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const authToken = localStorage.getItem('auth_token') || '';
    const categoryId = "{{ $category->category_id ?? '' }}"; // Get from server-side or empty
    
    // If we don't have server-side data, fetch via API
    @if(!isset($category))
    function loadCategoryData() {
        const apiCategoryId = window.location.pathname.split('/').pop();
        $.ajax({
            url: `/api/admin/categories/${apiCategoryId}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + authToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.data) {
                    $('#name').val(response.data.name);
                    $('#slug').val(response.data.slug);
                }
            },
            error: function(xhr) {
                window.location.href = "{{ route('admin.categories.index') }}";
            }
        });
    }
    
    loadCategoryData();
    @endif

    // Handle form submission
    $('#editCategoryForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submitBtn');
        const originalBtnText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitBtn.html(`
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Updating...
        `);

        // Prepare form data
        const formData = {
            name: $('#name').val(),
            slug: $('#slug').val()
        };

        // Make API request - use the categoryId we defined at the top
        $.ajax({
            url: `/api/admin/categories/${categoryId}`,
            type: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + authToken,
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: formData,
            success: function(response) {
                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: 'Category updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = "{{ route('admin.categories.index') }}";
                });
            },
            error: function(xhr) {
                // Reset button state
                submitBtn.prop('disabled', false);
                submitBtn.html(originalBtnText);

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}-error`).text(value[0]);
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON.message || 'Failed to update category',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    });

    // Auto-generate slug from name
    $('#name').on('blur', function() {
        if ($('#slug').val() === '') {
            const slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)+/g, '');
            $('#slug').val(slug);
        }
    });
});
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Category Details</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h4>{{ $category->name }}</h4>
                        <p class="text-muted">Slug: {{ $category->slug }}</p>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.categories.edit', $category->category_id) }}" 
                           class="btn btn-primary">
                            Edit Category
                        </a>
                        
                        <form action="{{ route('admin.categories.destroy', $category->category_id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                Delete Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
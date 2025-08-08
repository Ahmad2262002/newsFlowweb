<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    @push('styles')

    @endpush

    <!-- Add jQuery and other scripts in the head to ensure they load first -->
    @push('scripts')
    <!-- Load jQuery FIRST -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Then load other dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />
    @endpush

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <div class="py-6" x-data="{ 
        showAddEmployeeModal: false,
        showEditEmployeeModal: false,
        showAddCategoryModal: false,
        isLoading: false,
        
        ...adminDashboard()
    }" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Notification Area -->
            @if(session('success') || session('error'))
            <div class="mb-4">
                @if(session('success'))
                <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
                @endif
            </div>
            @endif

            <!-- Welcome Card with Stats -->
            <div class="mb-6 bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-lg shadow text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold">{{ __("Welcome back, Admin!") }}</h3>
                        <p class="mt-2 opacity-90">Manage Other Mind.</p>
                    </div>
                    <div class="flex space-x-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $employees->total() }}</div>
                            <div class="text-sm opacity-90">Employees</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $articles->total() }}</div>
                            <div class="text-sm opacity-90">Articles</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $categories->total() }}</div>
                            <div class="text-sm opacity-90">Categories</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Tabs Component -->
            <div x-data="{ openTab: 'employees' }" x-cloak>
                <!-- Tabs Navigation -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="openTab = 'employees'"
                                :class="{
                                        'border-blue-500 text-blue-600': openTab === 'employees', 
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': openTab !== 'employees'
                                    }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover-scale">
                                <i class="fas fa-users mr-2"></i> Employees
                            </button>
                            <button @click="openTab = 'articles'"
                                :class="{
                                        'border-blue-500 text-blue-600': openTab === 'articles', 
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': openTab !== 'articles'
                                    }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover-scale">
                                <i class="fas fa-newspaper mr-2"></i> Articles
                            </button>
                            <button @click="openTab = 'categories'"
                                :class="{
                                        'border-blue-500 text-blue-600': openTab === 'categories', 
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': openTab !== 'categories'
                                    }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover-scale">
                                <i class="fas fa-tags mr-2"></i> Categories
                            </button>
                            <button @click="openTab = 'actions'"
                                :class="{
                                        'border-blue-500 text-blue-600': openTab === 'actions', 
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': openTab !== 'actions'
                                    }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover-scale">
                                <i class="fas fa-history mr-2"></i> Action Logs
                            </button>
                            
                        </nav>
                    </div>
                </div>

                <!-- Tabs Content -->
                <div>
                    <!-- Employees Tab -->
                    <div x-show="openTab === 'employees'" x-transition class="bg-white rounded-lg shadow overflow-hidden hover-scale">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium">
                                    <i class="fas fa-users mr-2 text-blue-500"></i> Employees Management
                                </h3>
                                <a href="{{ route('admin.employees.create') }}"
   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center hover-scale transition duration-200">
   <i class="fas fa-plus mr-2"></i> Add Employee
</a>
                            </div>

                            <!-- Add Employee Modal -->
                            <div x-show="showAddEmployeeModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
                                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showAddEmployeeModal = false">
                                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                    </div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                        @click.stop>
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                                Add New Employee
                                            </h3>
                                            <form id="addEmployeeForm">
                                                @csrf
                                                <div class="mb-4">
                                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                                    <input type="text" id="username" name="username" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div class="mb-4">
                                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                                    <input type="email" id="email" name="email" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div class="mb-4">
                                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                                    <input type="password" id="password" name="password" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div class="mb-4">
                                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                                    <input type="password" id="password_confirmation" name="password_confirmation" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                </div>

                                                <div class="mb-4">
                                                    <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                                                    <input type="text" id="position" name="position" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div class="mb-4">
                                                    <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
                                                    <input type="date" id="hire_date" name="hire_date" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="button" @click="addEmployee()"
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                <span x-show="!isLoading">Add Employee</span>
                                                <span x-show="isLoading" class="flex items-center">
                                                    <i class="fas fa-spinner loading-spinner mr-2"></i> Processing...
                                                </span>
                                            </button>
                                            <button type="button" @click="showAddEmployeeModal = false"
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           

                            <div class="mb-4 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="employeeSearch" placeholder="Search employees..."
                                    class="pl-10 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-gray-200" id="employeesTable">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hire Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($employees as $employee)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $employee->staff->username }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                                {{ $employee->staff->email }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $employee->position }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium action-btn-container">
                                                <div class="flex space-x-2">

                                                    <button @click="confirmDelete('employee', {{ $employee->employee_id }})"
                                                        class="action-btn text-red-600 hover:text-red-800"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <a href="{{ route('admin.employees.show', $employee->employee_id) }}"
                                                        class="action-btn text-green-600 hover:text-green-800"
                                                        title="View">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    @if ($employees->onFirstPage())
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white cursor-not-allowed">
                                        Previous
                                    </span>
                                    @else
                                    <a href="{{ $employees->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                    @endif

                                    @if ($employees->hasMorePages())
                                    <a href="{{ $employees->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                    @else
                                    <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white cursor-not-allowed">
                                        Next
                                    </span>
                                    @endif
                                </div>

                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing <span class="font-medium">{{ $employees->firstItem() }}</span> to
                                            <span class="font-medium">{{ $employees->lastItem() }}</span> of
                                            <span class="font-medium">{{ $employees->total() }}</span> results
                                        </p>
                                    </div>
                                    <div>
                                        {{ $employees->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Articles Tab -->
                    <div x-show="openTab === 'articles'" x-transition class="bg-white rounded-lg shadow overflow-hidden hover-scale">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium">
                                    <i class="fas fa-newspaper mr-2 text-green-500"></i> Articles Management
                                </h3>

                            </div>

                            <div class="mb-4 flex flex-col md:flex-row gap-4">
                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" id="articleSearch" placeholder="Search articles..."
                                        class="pl-10 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <select id="articleStatusFilter" class="p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Status</option>
                                    <option value="1">Published</option>
                                    <option value="0">Draft</option>
                                </select>
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200" id="articlesTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($articles as $article)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                <a href="{{ route('admin.articles.show', $article->article_id) }}" class="hover:text-blue-600">
                                                    {{ Str::limit($article->title, 30) }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $article->employee->staff->username }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $article->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($article->status)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle mr-1"></i> Published
                                                </span>
                                                @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-pen mr-1"></i> Draft
                                                </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium action-btn-container">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.articles.show', $article->article_id) }}"
                                                        class="action-btn text-blue-600 hover:text-blue-800"
                                                        title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($article->status)
                                                    <button @click="unpublishArticle({{ $article->article_id }})"
                                                        class="action-btn text-purple-600 hover:text-purple-800"
                                                        title="Unpublish">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </button>
                                                    @else
                                                    <button @click="publishArticle({{ $article->article_id }})"
                                                        class="action-btn text-green-600 hover:text-green-800"
                                                        title="Publish">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @endif
                                                    <!-- <button @click="confirmDelete('article', {{ $article->article_id }})"
                                                        class="action-btn text-red-600 hover:text-red-800"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button> -->
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                {{ $articles->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div x-show="openTab === 'categories'" x-transition class="bg-white rounded-lg shadow overflow-hidden hover-scale">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium">
                                    <i class="fas fa-tags mr-2 text-purple-500"></i> Categories Management
                                </h3>
                                
                                <a href="{{ route('admin.categories.create') }}"
   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center hover-scale transition duration-200">
   <i class="fas fa-plus mr-2"></i> Add Category
   
</a>
                            </div>

                            <div class="mb-4 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="categorySearch" placeholder="Search categories..."
                                    class="pl-10 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-gray-200" id="categoriesTable">
                                <table class="min-w-full divide-y divide-gray-200" id="categoriesTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($categories as $category)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $category->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                                {{ $category->slug }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $category->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium action-btn-container">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.categories.edit', $category->category_id) }}"
                                                        class="action-btn text-blue-600 hover:text-blue-800"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button @click="confirmDelete('category', {{ $category->category_id }})"
                                                        class="action-btn text-red-600 hover:text-red-800"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                {{ $categories->links() }}
                            </div>
                        </div>

                        <!-- Add Category Modal -->
                        <div x-show="showAddCategoryModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
                            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showAddCategoryModal = false">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                    @click.stop>
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                            Add New Category
                                        </h3>
                                        <form id="addCategoryForm">
                                            @csrf
                                            <div class="mb-4">
                                                <label for="category_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                                                <input type="text" id="category_name" name="name" required
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div class="mb-4">
                                                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                                <input type="text" id="slug" name="name" required
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="button" @click="addCategory()"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                            :disabled="isLoading">
                                            <span x-show="!isLoading">Add Category</span>
                                            <span x-show="isLoading" class="flex items-center">
                                                <i class="fas fa-spinner loading-spinner mr-2"></i> Processing...
                                            </span>
                                        </button>
                                        <button type="button" @click="showAddCategoryModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Logs Tab -->
                    <div x-show="openTab === 'actions'" x-transition class="bg-white rounded-lg shadow overflow-hidden hover-scale">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium">
                                    <i class="fas fa-history mr-2 text-gray-500"></i> Action Logs
                                </h3>
                                <div class="flex flex-col md:flex-row gap-4">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar text-gray-400"></i>
                                        </div>
                                        <input type="text" id="dateRangePicker" placeholder="Select date range"
                                            class="pl-10 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex gap-2">
                                        <button id="filterLogsBtn"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center hover-scale">
                                            <i class="fas fa-filter mr-2"></i> Filter
                                        </button>
                                        <button id="resetLogsFilterBtn"
                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 flex items-center hover-scale">
                                            <i class="fas fa-sync-alt mr-2"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($actions as $action)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $action->admin->staff->username ?? 'System' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ ucfirst(str_replace('_', ' ', $action->action_type)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $action->description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $action->created_at->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                {{ $actions->links() }}
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Alpine.js should be loaded after jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>


    @endpush
</x-app-layout> <x-slot name="header">
    <h2 class="text-xl font-semibold text-gray-800 leading-tight">
        {{ __('Admin Dashboard') }}
    </h2>
</x-slot>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Daterangepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <style>
        [x-cloak] {
            display: none !important;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding-left: 0.625rem;
            padding-right: 0.625rem;
            padding-top: 0.125rem;
            padding-bottom: 0.125rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .hover-scale {
            transition-property: transform;
            transition-duration: 200ms;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 9999px;
            transition-property: background-color;
            transition-duration: 200ms;
        }

        .action-btn:hover {
            background-color: #f3f4f6;
        }

        .loading-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Added to ensure action buttons are always visible */
        .action-btn-container {
            min-width: 120px;
        }

        /* Welcome card gradient */
        .welcome-card {
            background: linear-gradient(to right, #3b82f6, #8b5cf6);
        }

        /* Tab styling */
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6b7280;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
        }

        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom-color: #d1d5db;
            color: #374151;
        }

        /* Modal backdrop */
        .modal-backdrop {
            background-color: rgba(107, 114, 128, 0.75);
        }
    </style>
</head>

<body>

    

    <!-- Edit Employee Modal -->
    <div x-show="showEditEmployeeModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showEditEmployeeModal = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                @click.stop>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Edit Employee
                    </h3>
                    <form id="editEmployeeForm">
                        <input type="hidden" id="edit_employee_id">
                        <div class="mb-4">
                            <label for="edit_username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" id="edit_username" name="username" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{'border-red-500': errors.username}">
                            <p x-show="errors.username" class="mt-1 text-sm text-red-600" x-text="errors.username[0]"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="edit_email" name="email" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{'border-red-500': errors.email}">
                            <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email[0]"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_department" class="block text-sm font-medium text-gray-700">Department</label>
                            <input type="text" id="edit_department" name="department" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{'border-red-500': errors.department}">
                            <p x-show="errors.department" class="mt-1 text-sm text-red-600" x-text="errors.department[0]"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_position" class="block text-sm font-medium text-gray-700">Position</label>
                            <input type="text" id="edit_position" name="position" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{'border-red-500': errors.position}">
                            <p x-show="errors.position" class="mt-1 text-sm text-red-600" x-text="errors.position[0]"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
                            <input type="date" id="edit_hire_date" name="hire_date" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{'border-red-500': errors.hire_date}">
                            <p x-show="errors.hire_date" class="mt-1 text-sm text-red-600" x-text="errors.hire_date[0]"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                            <input type="password" id="edit_password" name="password"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{'border-red-500': errors.password}">
                            <p x-show="errors.password" class="mt-1 text-sm text-red-600" x-text="errors.password[0]"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" id="edit_password_confirmation" name="password_confirmation"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </form>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="updateEmployee()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <span x-show="!isLoading">Update Employee</span>
                        <span x-show="isLoading" class="flex items-center">
                            <i class="fas fa-spinner loading-spinner mr-2"></i> Updating...
                        </span>
                    </button>
                    <button type="button" @click="showEditEmployeeModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCategoryForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="add-category-text">Add Category</span>
                            <span class="add-category-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Are you sure?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmationText">You are about to delete this item!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <span class="delete-text">Yes, delete it!</span>
                        <span class="delete-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Publish/Unpublish Confirmation Modal -->
    <div class="modal fade" id="publishConfirmationModal" tabindex="-1" aria-labelledby="publishConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="publishConfirmationModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="publishConfirmationText">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmPublishBtn">
                        <span class="publish-text">Confirm</span>
                        <span class="publish-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <div id="notificationArea" class="fixed top-4 right-4 z-50" style="width: 300px;"></div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- Daterangepicker -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <script>
        // Global variables
        let currentEmployeeId = null;
        let currentArticleId = null;
        let currentCategoryId = null;
        let currentDeleteType = null;
        let currentPublishAction = null;
        const authToken = document.querySelector('meta[name="api-token"]')?.content || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        window.API_BASE_URL = '/api/admin'; // Set this according to your actual API base
        

        $(document).ready(function() {
            // Initialize date range picker
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

            // Load initial data
            loadDashboardStats();
            loadEmployees();
            loadArticles();
            loadCategories();
            loadActionLogs();

            // Search functionality
            function setupSearch(inputId, tableSelector) {
                $(inputId).on('keyup', function() {
                    const value = $(this).val().toLowerCase();
                    $(tableSelector + ' tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
            }

            setupSearch('#employeeSearch', '#employeesTable');
            setupSearch('#articleSearch', '#articlesTable');
            setupSearch('#categorySearch', '#categoriesTable');

            // Filter articles by status
            $('#articleStatusFilter').change(function() {
                const status = $(this).val();
                $('#articlesTable tbody tr').each(function() {
                    const rowStatus = $(this).find('td').eq(3).find('.badge-success').length ? '1' : '0';
                    $(this).toggle(status === '' || rowStatus === status);
                });
            });

            // Add employee form submission
            $('#addEmployeeForm').submit(function(e) {
                e.preventDefault();

                const formData = {
                    username: $('#username').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val(),
                    position: $('#position').val(),
                    hire_date: $('#hire_date').val()
                };

                // Basic validation
                if (!formData.username || !formData.email || !formData.password || !formData.position || !formData.hire_date) {
                    showNotification('Error!', 'Please fill all required fields', 'error');
                    return;
                }

                if (formData.password !== formData.password_confirmation) {
                    showNotification('Error!', 'Passwords do not match', 'error');
                    return;
                }

                // Show loading state
                $('.add-employee-text').addClass('d-none');
                $('.add-employee-loading').removeClass('d-none');

                // Make API call
                $.ajax({
                    url: '/api/admin/employees',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: formData,
                    success: function(response) {
                        // Hide loading state
                        $('.add-employee-text').removeClass('d-none');
                        $('.add-employee-loading').addClass('d-none');

                        // Close modal
                        $('#addEmployeeModal').modal('hide');

                        // Show success message
                        showNotification('Success!', 'Employee added successfully', 'success');

                        // Reset form
                        $('#addEmployeeForm')[0].reset();

                        // Reload employees
                        loadEmployees();
                        loadDashboardStats();
                    },
                    error: function(xhr) {
                        // Hide loading state
                        $('.add-employee-text').removeClass('d-none');
                        $('.add-employee-loading').addClass('d-none');

                        // Show error message
                        const errorMessage = xhr.responseJSON?.message || 'Failed to add employee';
                        showNotification('Error!', errorMessage, 'error');
                    }
                });
            });

            // Edit employee form submission
            $('#editEmployeeForm').submit(function(e) {
                e.preventDefault();

                const formData = {
                    username: $('#edit_username').val(),
                    position: $('#edit_position').val()
                };

                // Basic validation
                if (!formData.username || !formData.position) {
                    showNotification('Error!', 'Please fill all required fields', 'error');
                    return;
                }

                // Show loading state
                $('.update-employee-text').addClass('d-none');
                $('.update-employee-loading').removeClass('d-none');

                // Make API call
                $.ajax({
                    url: `/api/admin/employees/${currentEmployeeId}`,
                    type: 'PUT',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: formData,
                    success: function(response) {
                        // Hide loading state
                        $('.update-employee-text').removeClass('d-none');
                        $('.update-employee-loading').addClass('d-none');

                        // Close modal
                        $('#editEmployeeModal').modal('hide');

                        // Show success message
                        showNotification('Success!', 'Employee updated successfully', 'success');

                        // Reload employees
                        loadEmployees();
                    },
                    error: function(xhr) {
                        // Hide loading state
                        $('.update-employee-text').removeClass('d-none');
                        $('.update-employee-loading').addClass('d-none');

                        // Show error message
                        const errorMessage = xhr.responseJSON?.message || 'Failed to update employee';
                        showNotification('Error!', errorMessage, 'error');
                    }
                });
            });

            // Add category form submission
            $('#addCategoryForm').submit(function(e) {
                e.preventDefault();

                const formData = {
                    name: $('#category_name').val()
                };

                // Basic validation
                if (!formData.name) {
                    showNotification('Error!', 'Category name is required', 'error');
                    return;
                }

                // Show loading state
                $('.add-category-text').addClass('d-none');
                $('.add-category-loading').removeClass('d-none');

                // Make API call
                $.ajax({
                    url: '/api/admin/categories',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: formData,
                    success: function(response) {
                        // Hide loading state
                        $('.add-category-text').removeClass('d-none');
                        $('.add-category-loading').addClass('d-none');

                        // Close modal
                        $('#addCategoryModal').modal('hide');

                        // Show success message
                        showNotification('Success!', 'Category added successfully', 'success');

                        // Reset form
                        $('#addCategoryForm')[0].reset();

                        // Reload categories
                        loadCategories();
                        loadDashboardStats();
                    },
                    error: function(xhr) {
                        // Hide loading state
                        $('.add-category-text').removeClass('d-none');
                        $('.add-category-loading').addClass('d-none');

                        // Show error message
                        const errorMessage = xhr.responseJSON?.message || 'Failed to add category';
                        showNotification('Error!', errorMessage, 'error');
                    }
                });
        });

            // Delete confirmation
            function confirmDelete(type, id) {
                currentDeleteType = type;
                if (type === 'employee') {
                    currentEmployeeId = id;
                    $('#deleteConfirmationText').text('You are about to delete this employee and all their articles!');
                } else if (type === 'article') {
                    currentArticleId = id;
                    $('#deleteConfirmationText').text('You are about to delete this article!');
                } else if (type === 'category') {
                    currentCategoryId = id;
                    $('#deleteConfirmationText').text('You are about to delete this category and remove it from all articles!');
                }

                $('#deleteConfirmationModal').modal('show');

                $('#confirmDeleteBtn').off('click').on('click', function() {
                    // Show loading state
                    $('.delete-text').addClass('d-none');
                    $('.delete-loading').removeClass('d-none');

                    if (currentDeleteType === 'employee') {
                        // First delete all articles by this employee
                        $.ajax({
                            url: `/api/admin/employees/${currentEmployeeId}/articles`, // Fixed URL
                            type: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + authToken,
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            success: function() {
                                // Then delete the employee
                                deleteEmployee();
                            },
                            error: function(xhr) {
                                handleDeleteError(xhr);
                            }
                        });
                    } else if (currentDeleteType === 'category') {
                        // First remove all article-category relationships
                        $.ajax({
                            url: `/api/admin/categories/${currentCategoryId}/articles`, // Fixed URL
                            type: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + authToken,
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            success: function() {
                                // Then delete the category
                                deleteCategory();
                            },
                            error: function(xhr) {
                                handleDeleteError(xhr);
                            }
                        });
                    } else if (currentDeleteType === 'article') {
                        // Use the admin endpoint for article deletion
                        $.ajax({
                            url: `/api/admin/articles/${currentArticleId}`,
                            type: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + authToken,
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            success: function(response) {
                                $('#deleteConfirmationModal').modal('hide');
                                showNotification('Deleted!', 'Article deleted successfully', 'success');
                                $('.delete-text').removeClass('d-none');
                                $('.delete-loading').addClass('d-none');
                                loadArticles();
                                loadDashboardStats();
                            },
                            error: function(xhr) {
                                $('#deleteConfirmationModal').modal('hide');
                                $('.delete-text').removeClass('d-none');
                                $('.delete-loading').addClass('d-none');

                                let errorMessage = 'Failed to delete article';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                showNotification('Error!', errorMessage, 'error');
                                console.error('Delete error:', xhr.responseText);
                            }
                        });
                    }
                });
            }

            function deleteEmployee() {
                $.ajax({
                    url: `/web/admin/employees/${currentEmployeeId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        handleDeleteSuccess('employee');
                    },
                    error: function(xhr) {
                        handleDeleteError(xhr);
                    }
                });
            }

            function deleteCategory() {
                $.ajax({
                    url: `/web/admin/categories/${currentCategoryId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        handleDeleteSuccess('category');
                    },
                    error: function(xhr) {
                        handleDeleteError(xhr);
                    }
                });
            }

            function deleteArticle() {
                $.ajax({
                    url: `/api/admin/articles/${currentArticleId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#deleteConfirmationModal').modal('hide');
                        showNotification('Deleted!', 'Article deleted successfully', 'success');
                        $('.delete-text').removeClass('d-none');
                        $('.delete-loading').addClass('d-none');
                        loadArticles();
                        loadDashboardStats();
                    },
                    error: function(xhr) {
                        $('#deleteConfirmationModal').modal('hide');
                        $('.delete-text').removeClass('d-none');
                        $('.delete-loading').addClass('d-none');

                        let errorMessage = 'Failed to delete article';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        showNotification('Error!', errorMessage, 'error');
                        console.error('Delete error:', xhr.responseText);
                    }
                });
            }

            function handleDeleteSuccess(type) {
                // Hide modal
                $('#deleteConfirmationModal').modal('hide');

                // Show success message
                showNotification('Deleted!', `${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully`, 'success');

                // Reset loading state
                $('.delete-text').removeClass('d-none');
                $('.delete-loading').addClass('d-none');

                // Reload data
                if (type === 'employee') {
                    loadEmployees();
                } else if (type === 'article') {
                    loadArticles();
                } else if (type === 'category') {
                    loadCategories();
                }
                loadDashboardStats();
            }

            function handleDeleteError(xhr) {
                // Hide modal
                $('#deleteConfirmationModal').modal('hide');

                // Reset loading state
                $('.delete-text').removeClass('d-none');
                $('.delete-loading').addClass('d-none');

                // Show error message
                const errorMessage = xhr.responseJSON?.message || 'Failed to delete';
                showNotification('Error!', errorMessage, 'error');
            }

            // Publish/Unpublish confirmation
            function confirmPublishAction(action, id) {
                currentPublishAction = action;
                currentArticleId = id;

                const isPublish = action === 'publish';
                $('#publishConfirmationModal').modal('show');
                $('#publishConfirmationText').text(
                    isPublish ?
                    'This article will be visible to all users.' :
                    'This article will no longer be visible to users.'
                );
                $('.modal-title').text(isPublish ? 'Publish Article?' : 'Unpublish Article?');

                $('#confirmPublishBtn').off('click').on('click', function() {
                    // Show loading state
                    $('.publish-text').addClass('d-none');
                    $('.publish-loading').removeClass('d-none');

                    const endpoint = isPublish ? 'publish' : 'unpublish';

                    // Make API call
                    $.ajax({
                        url: `/api/admin/articles/${currentArticleId}/${endpoint}`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + authToken,
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            // Hide modal
                            $('#publishConfirmationModal').modal('hide');

                            // Show success message
                            showNotification(
                                isPublish ? 'Published!' : 'Unpublished!',
                                isPublish ? 'Article published successfully' : 'Article unpublished successfully',
                                'success'
                            );

                            // Reset loading state
                            $('.publish-text').removeClass('d-none');
                            $('.publish-loading').addClass('d-none');

                            // Reload articles
                            loadArticles();
                        },
                        error: function(xhr) {
                            // Hide modal
                            $('#publishConfirmationModal').modal('hide');

                            // Reset loading state
                            $('.publish-text').removeClass('d-none');
                            $('.publish-loading').addClass('d-none');

                            // Show error message
                            const errorMessage = xhr.responseJSON?.message || 'Failed to perform action';
                            showNotification('Error!', errorMessage, 'error');
                        }
                    });
                });
            }

            // Filter logs button functionality
            $('#filterLogsBtn').click(() => {
                const dateRange = $('#dateRangePicker').val();
                if (dateRange) {
                    const dates = dateRange.split(' - ');
                    loadActionLogs(dates[0], dates[1]);
                }
            });

            // Reset logs filter button functionality
            $('#resetLogsFilterBtn').click(() => {
                $('#dateRangePicker').val('');
                loadActionLogs();
            });

            // Function to show edit employee modal
            window.showEditEmployee = function(id, username, position) {
                currentEmployeeId = id;
                $('#edit_employee_id').val(id);
                $('#edit_username').val(username);
                $('#edit_position').val(position);
                $('#editEmployeeModal').modal('show');
            };

            // Global functions for buttons
            window.confirmDelete = confirmDelete;
            window.publishArticle = function(id) {
                confirmPublishAction('publish', id);
            };
            window.unpublishArticle = function(id) {
                confirmPublishAction('unpublish', id);
            };
        });

        // Function to load dashboard stats
        function loadDashboardStats() {
            $.ajax({
                url: '/api/admin/dashboard',
                type: 'GET',
                headers: {

                    'Authorization': 'Bearer ' + authToken,
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    // This is a placeholder - you would need to implement actual API endpoints
                    // that return the counts for employees, articles, and categories
                    $('#employeesCount').text(response.employeesCount || 0);
                    $('#articlesCount').text(response.articlesCount || 0);
                    $('#categoriesCount').text(response.categoriesCount || 0);
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        // Redirect to login if unauthorized
                        // window.location.href = '/login';
                    } else {
                        console.error('Failed to load dashboard stats:', xhr.responseText);
                    }
                }
            });
        }

        // Function to load employees
        function loadEmployees(page = 1) {
            $.ajax({
                url: `/api/admin/employees?page=${page}`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    const employees = response.data || [];
                    const tbody = $('#employeesTableBody');
                    tbody.empty();

                    if (employees.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center py-4">No employees found</td></tr>');
                        return;
                    }

                    employees.forEach(employee => {
                        const hireDate = new Date(employee.hire_date);
                        const formattedDate = hireDate.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        const row = `
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    ${employee.staff.username}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    ${employee.staff.email}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${employee.position}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${formattedDate}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium action-btn-container">
                                    <div class="flex space-x-2">
                                        <button onclick="showEditEmployee(${employee.employee_id}, '${employee.staff.username}', '${employee.position}')"
                                            class="action-btn text-blue-600 hover:text-blue-800"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmDelete('employee', ${employee.employee_id})"
                                            class="action-btn text-red-600 hover:text-red-800"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <a href="/admin/employees/${employee.employee_id}"
                                            class="action-btn text-green-600 hover:text-green-800"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    // Update pagination info
                    $('#employeesPaginationInfo').html(`
                        Showing ${response.from || 0} to ${response.to || 0} of ${response.total || 0} entries
                    `);

                    // Update pagination controls
                    updatePagination('employeesPagination', response);
                },
                error: function(xhr) {
                    console.error('Failed to load employees:', xhr.responseText);
                    $('#employeesTableBody').html('<tr><td colspan="5" class="text-center py-4">Failed to load employees</td></tr>');
                }
            });
        }

        // Function to load articles
        function loadArticles(page = 1) {
            $.ajax({
                url: `/api/admin/articles?page=${page}`,
                type: 'GET',
                headers: {

                    'Authorization': 'Bearer ' + authToken,
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    const articles = response.data || [];
                    const tbody = $('#articlesTableBody');
                    tbody.empty();

                    if (articles.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center py-4">No articles found</td></tr>');
                        return;
                    }

                    articles.forEach(article => {
                        const createdDate = new Date(article.created_at);
                        const formattedDate = createdDate.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        const statusBadge = article.status ?
                            '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Published</span>' :
                            '<span class="badge badge-warning"><i class="fas fa-pen mr-1"></i> Draft</span>';

                        const publishButton = article.status ?
                            `<button onclick="unpublishArticle(${article.article_id})" class="action-btn text-purple-600 hover:text-purple-800" title="Unpublish">
                                <i class="fas fa-eye-slash"></i>
                            </button>` :
                            `<button onclick="publishArticle(${article.article_id})" class="action-btn text-green-600 hover:text-green-800" title="Publish">
                                <i class="fas fa-eye"></i>
                            </button>`;

                        const row = `
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    <a href="/admin/articles/${article.article_id}" class="hover:text-blue-600">
                                        ${article.title.length > 30 ? article.title.substring(0, 30) + '...' : article.title}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${article.employee.staff.username}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${formattedDate}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${statusBadge}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium action-btn-container">
                                    <div class="flex space-x-2">
                                        <a href="/admin/articles/${article.article_id}"
                                            class="action-btn text-blue-600 hover:text-blue-800"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/articles/${article.article_id}/edit"
                                            class="action-btn text-yellow-600 hover:text-yellow-800"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        ${publishButton}
                                        <button onclick="confirmDelete('article', ${article.article_id})"
                                            class="action-btn text-red-600 hover:text-red-800"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    // Update pagination controls
                    updatePagination('articlesPagination', response);
                },
                error: function(xhr) {
                    console.error('Failed to load articles:', xhr.responseText);
                    $('#articlesTableBody').html('<tr><td colspan="5" class="text-center py-4">Failed to load articles</td></tr>');
                }
            });
        }

        // Function to load categories
        function loadCategories(page = 1) {
            $.ajax({
                url: `/api/admin/categories?page=${page}`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'X-CSRF-TOKEN': csrfToken, // for POST/PUT/DELETE requests
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const categories = response.data || [];
                    const tbody = $('#categoriesTableBody');
                    tbody.empty();

                    if (categories.length === 0) {
                        tbody.append('<tr><td colspan="4" class="text-center py-4">No categories found</td></tr>');
                        return;
                    }

                    categories.forEach(category => {
                        const createdDate = new Date(category.created_at);
                        const formattedDate = createdDate.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        const row = `
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    ${category.name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    ${category.slug}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${formattedDate}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium action-btn-container">
                                    <div class="flex space-x-2">
                                        <a href="/admin/categories/${category.category_id}/edit"
                                            class="action-btn text-blue-600 hover:text-blue-800"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete('category', ${category.category_id})"
                                            class="action-btn text-red-600 hover:text-red-800"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <a href="/admin/categories/${category.category_id}"
                                            class="action-btn text-green-600 hover:text-green-800"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    // Update pagination controls
                    updatePagination('categoriesPagination', response);
                },
                error: function(xhr) {
                    console.error('Failed to load categories:', xhr.responseText);
                    $('#categoriesTableBody').html('<tr><td colspan="4" class="text-center py-4">Failed to load categories</td></tr>');
                }
            });
        }

        function loadActionLogs(from = null, to = null) {
            let url = '/api/admin/actionLogs';
            if (from && to) {
                url += `?from=${from}&to=${to}`;
            }

            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const logs = response.data || [];
                    const tbody = $('#actionLogsTableBody');
                    tbody.empty();

                    if (logs.length === 0) {
                        tbody.append('<tr><td colspan="4" class="text-center py-4">No action logs found</td></tr>');
                        return;
                    }

                    logs.forEach(log => {
                        const timestamp = new Date(log.created_at);
                        const formattedTimestamp = timestamp.toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        const actionType = log.action_type.replace(/_/g, ' ');
                        const adminName = log.admin_username; // Now using the transformed data

                        const row = `
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                            ${adminName}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${actionType.charAt(0).toUpperCase() + actionType.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            ${log.description}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${formattedTimestamp}
                        </td>
                    </tr>
                `;
                        tbody.append(row);
                    });

                    // Update pagination controls
                    updatePagination('actionLogsPagination', response.pagination);
                },
                error: function(xhr) {
                    console.error('Failed to load action logs:', xhr.responseText);
                    $('#actionLogsTableBody').html('<tr><td colspan="4" class="text-center py-4">Failed to load action logs</td></tr>');
                }
            });
        }

        // Function to update pagination controls
        function updatePagination(paginationId, response) {
            const pagination = $(`#${paginationId}`);
            pagination.empty();

            if (!response || !response.links) {
                pagination.append(`
                    <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                `);
                return;
            }

            // Previous button
            if (response.prev_page_url) {
                pagination.append(`
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="load${paginationId.replace('Pagination', '')}(${response.current_page - 1})">Previous</a>
                    </li>
                `);
            } else {
                pagination.append(`
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                `);
            }

            // Page numbers
            for (let i = 1; i <= response.last_page; i++) {
                if (i === response.current_page) {
                    pagination.append(`
                        <li class="page-item active">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                } else {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="load${paginationId.replace('Pagination', '')}(${i})">${i}</a>
                        </li>
                    `);
                }
            }

            // Next button
            if (response.next_page_url) {
                pagination.append(`
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="load${paginationId.replace('Pagination', '')}(${response.current_page + 1})">Next</a>
                    </li>
                `);
            } else {
                pagination.append(`
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Next</a>
                    </li>
                `);
            }
        }

        // Function to show notification
        function showNotification(title, message, type) {
            const typeClasses = {
                'success': 'bg-green-100 border-green-500 text-green-700',
                'error': 'bg-red-100 border-red-500 text-red-700',
                'info': 'bg-blue-100 border-blue-500 text-blue-700'
            };

            const notification = `
        <div class="border-l-4 p-4 mb-4 ${typeClasses[type]}" role="alert">
            <p class="font-bold">${title}</p>
            <p>${message}</p>
        </div>
    `;

            $('#notificationArea').append(notification);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('#notificationArea').empty();
            }, 5000);
        }

        

        // Global functions for pagination
        window.loadEmployees = loadEmployees;
        window.loadArticles = loadArticles;
        window.loadCategories = loadCategories;
        window.loadActionLogs = loadActionLogs;

        

    </script>


</body>

</html>
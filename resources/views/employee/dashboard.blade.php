<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Employee Dashboard') }}
        </h2>
    </x-slot>

    <!-- Add meta tags for authentication -->
    @push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush

    <div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen" x-data="dashboard()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
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
                            <h3 class="text-2xl font-bold">{{ __("Welcome back, ") }} {{ Auth::user()->username }}!</h3>
                            <p class="mt-2 opacity-90">Manage your articles and content.</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-center">
                                <div class="text-3xl font-bold" x-text="articles.length"></div>
                                <div class="text-sm opacity-90">Your Articles</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold" x-text="categories.length"></div>
                                <div class="text-sm opacity-90">Categories</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                <i class="fas fa-newspaper mr-2 text-blue-500"></i> Your Articles
                            </h3>
                            <button @click="showAddArticleModal = true"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center transition-transform hover:scale-105">
                                <i class="fas fa-plus mr-2"></i> Add Article
                            </button>
                        </div>

                        <!-- Filter and Search Controls -->
                        <div class="mb-4 flex flex-col md:flex-row gap-4">
                            <div class="relative flex-grow">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" x-model="searchQuery" placeholder="Search articles..."
                                    class="pl-10 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                            </div>
                            <select x-model="statusFilter" @change="fetchArticles()"
                                class="p-2 border rounded focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                <option value="all">All Status</option>
                                <option value="1">Published</option>
                                <option value="0">Draft</option>
                            </select>
                            <button @click="fetchArticles()"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 flex items-center transition-transform hover:scale-105 dark:bg-gray-600 dark:text-gray-100">
                                <i class="fas fa-sync-alt mr-2"></i> Refresh
                            </button>
                        </div>

                        <!-- Articles Table -->
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Created At</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <template x-for="article in filteredArticles" :key="article.id">
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <template x-if="article.image_url">
                                <div class="flex-shrink-0 h-8 w-8 mr-2">  <!-- Changed from h-10 w-10 to h-8 w-8 -->
                                    <img class="h-8 w-8 rounded object-cover" :src="article.image_url" :alt="article.title">  <!-- Changed from rounded-full to rounded -->
                                </div>
                            </template>
                            <div class="flex-1 min-w-0">  <!-- Changed to allow text truncation -->
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="article.title"></p>
                                    <button @click="showArticleDetails(article)" 
                                        class="ml-2 text-gray-400 hover:text-blue-500 dark:hover:text-blue-400"
                                        title="View details">
                                        <i class="fas fa-info-circle text-xs"></i>  <!-- Added info icon -->
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="article.source_name"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span x-bind:class="{
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': article.status == 1,
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': article.status == 0
                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                            <span x-text="article.status == 1 ? 'Published' : 'Draft'"></span>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDate(article.created_at)"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button @click="editArticle(article)"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-gray-700">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button @click="confirmDelete(article.id)"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-gray-700">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
                                    <template x-if="!articles.length && !isLoading">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No articles found
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="isLoading">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center">
                                                <div class="flex justify-center items-center py-4">
                                                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-600 sm:px-6" x-show="totalPages > 1">
                            <div>
                                <button @click="prevPage()" :disabled="currentPage === 1"
                                    :class="{
                                        'bg-gray-100 text-gray-400 cursor-not-allowed': currentPage === 1,
                                        'bg-white text-gray-700 hover:bg-gray-50': currentPage > 1
                                    }" class="px-3 py-1 rounded-md border dark:bg-gray-700 dark:text-gray-300">
                                    Previous
                                </button>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                            </div>
                            <div>
                                <button @click="nextPage()" :disabled="currentPage === totalPages"
                                    :class="{
                                        'bg-gray-100 text-gray-400 cursor-not-allowed': currentPage === totalPages,
                                        'bg-white text-gray-700 hover:bg-gray-50': currentPage < totalPages
                                    }" class="px-3 py-1 rounded-md border dark:bg-gray-700 dark:text-gray-300">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Article Modal -->
                <div x-show="showAddArticleModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showAddArticleModal = false; resetArticleForm();">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                            @click.stop>
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Add New Article
                                </h3>
                                <form id="addArticleForm">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="mb-4">
                                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title *</label>
                                            <input type="text" id="title" name="title" required x-model="articleForm.title"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                        <div class="mb-4">
                                            <label for="source_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source Name *</label>
                                            <input type="text" id="source_name" name="source_name" required x-model="articleForm.source_name"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content *</label>
                                        <textarea id="content" name="content" rows="6" required x-model="articleForm.content"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="mb-4">
                                            <label for="published_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Published Date *</label>
                                            <input type="date" id="published_date" name="published_date" required x-model="articleForm.published_date"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                        <div class="mb-4">
                                            <label for="author_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Author Name *</label>
                                            <input type="text" id="author_name" name="author_name" required x-model="articleForm.author_name"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="mb-4">
                                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                                            <select id="status" name="status" required x-model="articleForm.status"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                                <option value="0">Draft</option>
                                                <option value="1">Published</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category *</label>
                                            <select id="category_id" name="category_id" required x-model="articleForm.category_id"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                                <template x-for="category in categories" :key="category.category_id">
                                                    <option :value="category.category_id" x-text="category.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="article_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Article Photo</label>
                                        <input type="file" id="article_photo" name="article_photo" accept="image/*" @change="handleFileUpload($event)"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max file size: 2MB (JPEG, PNG)</p>
                                        <template x-if="articleForm.imagePreview">
                                            <div class="mt-2">
                                                <img :src="articleForm.imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded">
                                            </div>
                                        </template>
                                    </div>
                                </form>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="submitArticle()"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    <span x-show="!isLoading">Submit</span>
                                    <span x-show="isLoading" class="flex items-center">
                                        <i class="fas fa-spinner animate-spin mr-2"></i> Processing...
                                    </span>
                                </button>
                                <button type="button" @click="showAddArticleModal = false; resetArticleForm();"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Article Modal -->
                <div x-show="showEditArticleModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showEditArticleModal = false; resetArticleForm();">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                            @click.stop>
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Edit Article
                                </h3>
                                <form id="editArticleForm">
                                    <input type="hidden" name="article_id" x-model="articleForm.id">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="mb-4">
                                            <label for="edit_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title *</label>
                                            <input type="text" id="edit_title" name="title" required x-model="articleForm.title"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                        <div class="mb-4">
                                            <label for="edit_source_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source Name *</label>
                                            <input type="text" id="edit_source_name" name="source_name" required x-model="articleForm.source_name"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="edit_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content *</label>
                                        <textarea id="edit_content" name="content" rows="6" required x-model="articleForm.content"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="mb-4">
                                            <label for="edit_published_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Published Date *</label>
                                            <input type="date" id="edit_published_date" name="published_date" required x-model="articleForm.published_date"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                        <div class="mb-4">
                                            <label for="edit_author_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Author Name *</label>
                                            <input type="text" id="edit_author_name" name="author_name" required x-model="articleForm.author_name"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="mb-4">
                                            <label for="edit_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                                            <select id="edit_status" name="status" required x-model="articleForm.status"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                                <option value="0">Draft</option>
                                                <option value="1">Published</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label for="edit_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category *</label>
                                            <select id="edit_category_id" name="category_id" required x-model="articleForm.category_id"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                                <template x-for="category in categories" :key="category.category_id">
                                                    <option :value="category.category_id" x-text="category.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="edit_article_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Article Photo</label>
                                        <input type="file" id="edit_article_photo" name="article_photo" accept="image/*" @change="handleFileUpload($event)"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max file size: 2MB (JPEG, PNG)</p>
                                        <template x-if="articleForm.imagePreview">
                                            <div class="mt-2">
                                                <img :src="articleForm.imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded">
                                            </div>
                                        </template>
                                        <template x-if="articleForm.image_url && !articleForm.imagePreview">
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Current image:</p>
                                                <img :src="articleForm.image_url" alt="Current" class="h-20 w-20 object-cover rounded">
                                            </div>
                                        </template>
                                    </div>
                                </form>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="updateArticle()"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    <span x-show="!isLoading">Update</span>
                                    <span x-show="isLoading" class="flex items-center">
                                        <i class="fas fa-spinner animate-spin mr-2"></i> Updating...
                                    </span>
                                </button>
                                <button type="button" @click="showEditArticleModal = false; resetArticleForm();"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Debug Panel -->
    <div x-data="{ debug: false }" class="fixed bottom-4 right-4 z-50">
        <button @click="debug = !debug" class="bg-blue-500 text-white p-2 rounded-full shadow-lg hover:bg-blue-600 transition-colors">
            <i class="fas fa-bug"></i>
        </button>

        <div x-show="debug" class="bg-white dark:bg-gray-800 p-4 rounded shadow-lg mt-2 w-80 max-h-96 overflow-auto">
            <h3 class="font-bold mb-2 dark:text-gray-100">Debug Info</h3>
            <div class="mb-2">
                <strong class="dark:text-gray-300">Categories:</strong>
                <pre x-text="JSON.stringify($data.categories, null, 2)" class="dark:text-gray-300 text-xs"></pre>
            </div>
            <div class="mb-2">
                <strong class="dark:text-gray-300">Articles Count:</strong>
                <div x-text="$data.articles.length" class="dark:text-gray-300"></div>
            </div>
            <div class="mb-2">
                <strong class="dark:text-gray-300">Current Page:</strong>
                <div x-text="$data.currentPage" class="dark:text-gray-300"></div>
            </div>
            <div class="mb-2">
                <strong class="dark:text-gray-300">Auth Token:</strong>
                <div x-text="$data.getAuthHeaders().Authorization ? 'Exists' : 'Missing'" class="dark:text-gray-300"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Load required libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                // Initialize all required properties with default values
                showAddArticleModal: false,
                showEditArticleModal: false,
                isLoading: false,
                articles: [], // Initialize as empty array
                categories: [], // Initialize as empty array
                currentPage: 1, // Initialize with default value
                totalPages: 1, // Initialize with default value
                statusFilter: 'all',
                searchQuery: '',
                sessionStatus: 'Unknown',

                // Article form data
                articleForm: {
                    id: null,
                    title: '',
                    content: '',
                    source_name: '',
                    published_date: '',
                    author_name: '',
                    status: '0',
                    category_id: '',
                    article_photo: null,
                    imagePreview: null,
                    image_url: null
                },

                // Initialize component
                async init() {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    try {
                        // Check session first
                        const isAuthenticated = await this.checkSession();
                        if (!isAuthenticated) return;

                        await Promise.allSettled([
                            this.fetchArticles(),
                            this.fetchCategories()
                        ]);
                    } catch (error) {
                        console.error('Initialization error:', error);
                        this.showAlert('error', 'Failed to load dashboard data');
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Get authentication headers (session-based)
                getAuthHeaders() {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    return {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };
                },

                // Check session status
                async checkSession() {
                    try {
                        const response = await axios.get('/employee/check-session', {
                            headers: this.getAuthHeaders(),
                            timeout: 5000
                        });

                        if (response.data?.authenticated) {
                            this.sessionStatus = 'Authenticated';
                            return true;
                        } else {
                            this.sessionStatus = 'Not authenticated';
                            this.redirectToLogin();
                            return false;
                        }
                    } catch (error) {
                        console.error('Session check failed:', error);
                        this.sessionStatus = 'Error checking session';
                        this.redirectToLogin();
                        return false;
                    }
                },

                // Redirect to login page
                redirectToLogin() {
                    window.location.href = '/login';
                },

                // Fetch articles
                async fetchArticles() {
                    this.isLoading = true;
                    try {
                        const response = await axios.get('/api/employee/articles/my-articles', {
                            params: {
                                page: this.currentPage,
                                status: this.statusFilter === 'all' ? null : this.statusFilter,
                                search: this.searchQuery || null
                            },
                            headers: this.getAuthHeaders()
                        });

                        // Handle response structure properly
                        if (response.data && response.data.success) {
                            this.articles = response.data.data.data || [];
                            this.totalPages = response.data.data.last_page || 1;
                            this.currentPage = response.data.data.current_page || 1;
                        } else {
                            throw new Error(response.data?.message || 'Invalid response structure');
                        }
                    } catch (error) {
                        console.error('Error details:', error);
                        let errorMsg = 'Failed to fetch articles';
                        if (error.response) {
                            errorMsg = error.response.data?.message ||
                                (error.response.data?.errors ? JSON.stringify(error.response.data.errors) : errorMsg);
                        }
                        this.showAlert('error', errorMsg);
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Fetch categories
                async fetchCategories() {
                    try {
                        const response = await axios.get('/employee/categories', {
                            headers: this.getAuthHeaders()
                        });
                        this.categories = response.data.data || [];

                        // Set default category if available
                        if (this.categories.length > 0 && !this.articleForm.category_id) {
                            this.articleForm.category_id = this.categories[0].category_id;
                        }
                    } catch (error) {
                        console.error('Error fetching categories:', error);
                        this.showAlert('error', 'Failed to fetch categories');
                    }
                },

                // Filtered articles computed property
                get filteredArticles() {
                    if (!this.articles || !Array.isArray(this.articles)) return [];

                    let filtered = [...this.articles];

                    // Status filter
                    if (this.statusFilter !== 'all') {
                        filtered = filtered.filter(a => String(a.status) === String(this.statusFilter));
                    }

                    // Search query
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(a =>
                            (a.title && a.title.toLowerCase().includes(query)) ||
                            (a.source_name && a.source_name.toLowerCase().includes(query))
                        );
                    }

                    return filtered;
                },

                // Utility for showing alerts
                showAlert(type, message) {
                    Swal.fire({
                        icon: type,
                        title: type === 'error' ? 'Error' : 'Success',
                        text: message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                },

                // Format date utility
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    } catch (e) {
                        return dateStr;
                    }
                },

                // Pagination controls
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.fetchArticles();
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.fetchArticles();
                    }
                },

                // Reset article form
                resetArticleForm() {
                    this.articleForm = {
                        id: null,
                        title: '',
                        content: '',
                        source_name: '',
                        published_date: '',
                        author_name: '',
                        status: '0',
                        category_id: this.categories.length > 0 ? this.categories[0].category_id : '',
                        article_photo: null,
                        imagePreview: null,
                        image_url: null
                    };
                },

                // Handle file upload
                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Validate file size (2MB max)
                        if (file.size > 2 * 1024 * 1024) {
                            this.showAlert('error', 'File size exceeds 2MB limit');
                            event.target.value = '';
                            return;
                        }

                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            this.showAlert('error', 'Only JPEG, PNG, and GIF images are allowed');
                            event.target.value = '';
                            return;
                        }

                        this.articleForm.article_photo = file;
                        const reader = new FileReader();
                        reader.onload = e => {
                            this.articleForm.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.articleForm.article_photo = null;
                        this.articleForm.imagePreview = null;
                    }
                },

                // Submit new article using FormData for file upload
                async submitArticle() {
                    if (!this.articleForm.category_id || !this.categories.some(c => c.category_id == this.articleForm.category_id)) {
                        this.showAlert('error', 'Please select a valid category');
                        return;
                    }
                    if (this.isLoading) return;
                    this.isLoading = true;

                    try {
                        // Validate required fields
                        const requiredFields = ['title', 'content', 'source_name', 'published_date', 'author_name', 'category_id'];
                        const missingFields = requiredFields.filter(field => !this.articleForm[field]);

                        if (missingFields.length > 0) {
                            this.showAlert('error', `Missing required fields: ${missingFields.join(', ')}`);
                            return;
                        }

                        // Prepare the article data
                        const articleData = {
                            title: this.articleForm.title,
                            content: this.articleForm.content,
                            source_name: this.articleForm.source_name,
                            published_date: this.articleForm.published_date,
                            author_name: this.articleForm.author_name,
                            status: this.articleForm.status,
                            category_id: this.articleForm.category_id,
                            employee_id: "{{ Auth::id() }}",
                            article_photo: '' // Initialize as empty string
                        };

                        // Handle file upload if present
                        if (this.articleForm.article_photo instanceof File) {
                            // Convert file to base64 string
                            articleData.article_photo = await this.fileToBase64(this.articleForm.article_photo);
                        } else if (this.articleForm.image_url) {
                            // Use existing image URL if available
                            articleData.article_photo = this.articleForm.image_url;
                        }

                        // Submit the article
                        const response = await axios.post('/api/employee/articles', articleData, {
                            headers: this.getAuthHeaders()
                        });

                        if (response.data.success) {
                            this.showAddArticleModal = false;
                            this.resetArticleForm();
                            this.showAlert('success', 'Article added successfully');
                            await this.fetchArticles();
                        } else {
                            throw new Error(response.data.message || 'Failed to add article');
                        }
                    } catch (error) {
                        console.error('Error submitting article:', error);
                        let errorMsg = 'Failed to add article';
                        if (error.response) {
                            errorMsg = error.response.data?.message ||
                                (error.response.data?.errors ? Object.values(error.response.data.errors).flat().join('\n') : errorMsg);
                        }
                        this.showAlert('error', errorMsg);
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Helper method to convert file to base64
                fileToBase64(file) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result); // Return full data URL
                        reader.onerror = error => reject(error);
                        reader.readAsDataURL(file);
                    });
                },

                // Edit article
                editArticle(article) {

                    this.articleForm = {
                        id: article.article_id || article.id, // Handle both naming conventions
                        title: article.title,
                        content: article.content,
                        source_name: article.source_name,
                        published_date: article.published_date,
                        author_name: article.author_name,
                        status: String(article.status),
                        category_id: String(article.category_id ||
                            (article.categories && article.categories[0]?.category_id)),
                        article_photo: null,
                        imagePreview: article.image_url || null,
                        image_url: article.image_url || null
                    };
                    this.showEditArticleModal = true;
                },

                // Update article using FormData for file upload
                // Update article using FormData for file upload
                // Update article with enhanced validation and error handling
                async updateArticle() {
                    try {

                        if (this.isLoading) return;

                        // Only proceed if we have an ID (but don't show error)
                        if (!this.articleForm.id) {
                            console.error('Missing article ID silently ignored');
                            return; // Just return without showing error
                        }

                        this.isLoading = true;

                        // Only validate category if it's being changed
                        if (this.articleForm.category_id) {
                            const categoryExists = this.categories.some(
                                c => c.category_id == this.articleForm.category_id
                            );

                            if (!categoryExists) {
                                this.showAlert('error', `Selected category doesn't exist`);
                                this.isLoading = false;
                                return;
                            }
                        }

                        // Prepare update data - only include fields that have values
                        const updateData = {};

                        // Add fields only if they exist and are different from original
                        if (this.articleForm.title) updateData.title = this.articleForm.title;
                        if (this.articleForm.content) updateData.content = this.articleForm.content;
                        if (this.articleForm.source_name) updateData.source_name = this.articleForm.source_name;
                        if (this.articleForm.published_date) updateData.published_date = this.articleForm.published_date;
                        if (this.articleForm.author_name) updateData.author_name = this.articleForm.author_name;
                        if (this.articleForm.status !== undefined) updateData.status = this.articleForm.status;
                        if (this.articleForm.category_id) updateData.category_id = Number(this.articleForm.category_id);

                        // Handle image upload if present
                        if (this.articleForm.article_photo instanceof File) {
                            try {
                                updateData.article_photo = await this.fileToBase64(this.articleForm.article_photo);
                            } catch (error) {
                                console.error('Image processing failed:', error);
                                this.showAlert('error', 'Failed to process the image');
                                this.isLoading = false;
                                return;
                            }
                        } else if (!this.articleForm.image_url && this.articleForm.article_photo === null) {
                            updateData.article_photo = ''; // Explicitly remove image
                        }

                        // Check if we have at least one field to update
                        if (Object.keys(updateData).length === 0) {
                            this.showAlert('info', 'No changes detected');
                            this.isLoading = false;
                            return;
                        }

                        const response = await axios.put(
                            `/api/employee/articles/${this.articleForm.id}`,
                            updateData, {
                                headers: this.getAuthHeaders()
                            }
                        );

                        if (response.data.success) {
                            this.showEditArticleModal = false;
                            this.resetArticleForm();
                            this.showAlert('success', 'Article updated successfully');
                            await this.fetchArticles();
                        } else {
                            throw new Error(response.data.message || 'Failed to update article');
                        }
                    } catch (error) {
                        console.error('Error updating article:', error);
                        let errorMsg = 'Failed to update article';

                        if (error.response) {
                            errorMsg = error.response.data?.message ||
                                (error.response.data?.errors ? Object.values(error.response.data.errors).flat().join('\n') : errorMsg);
                        }

                        this.showAlert('error', errorMsg);
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Confirm delete
                confirmDelete(articleId) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You are about to delete this article!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Delete'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            await this.deleteArticle(articleId);
                        }
                    });
                },

                // Delete article
                async deleteArticle(articleId) {
                    try {
                        const result = await Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        });

                        if (result.isConfirmed) {
                            this.isLoading = true;
                            const response = await axios.delete(`/api/employee/articles/${articleId}`, {
                                headers: this.getAuthHeaders()
                            });

                            if (response.data.success) {
                                this.showAlert('success', 'Article deleted successfully');
                                await this.fetchArticles();
                            } else {
                                throw new Error(response.data.message || 'Failed to delete article');
                            }
                        }
                    } catch (error) {
                        console.error('Error deleting article:', error);
                        let errorMsg = 'Failed to delete article';

                        if (error.response) {
                            errorMsg = error.response.data?.message || errorMsg;

                            // Handle unauthorized (401) or not found (404)
                            if (error.response.status === 401) {
                                this.redirectToLogin();
                                return;
                            }
                        }

                        this.showAlert('error', errorMsg);
                    } finally {
                        this.isLoading = false;
                    }
                },
                // Add this to your methods in the dashboard component
showArticleDetails(article) {
    Swal.fire({
        title: article.title,
        html: `
            <div class="text-left">
                ${article.image_url ? `<img src="${article.image_url}" alt="${article.title}" class="mb-4 rounded-lg w-full max-h-40 object-cover">` : ''}
                <p class="mb-2"><strong>Source:</strong> ${article.source_name}</p>
                <p class="mb-2"><strong>Author:</strong> ${article.author_name}</p>
                <p class="mb-2"><strong>Published:</strong> ${this.formatDate(article.published_date)}</p>
                <div class="border-t my-3"></div>
                <p class="text-sm">${article.content}</p>
            </div>
        `,
        showCloseButton: true,
        showConfirmButton: false,
        width: '600px'
    });
}
            }));
        });
    </script>
    @endpush
</x-app-layout>
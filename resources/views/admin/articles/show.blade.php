<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
                {{ __('📰 Article Overview') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700 transition duration-200">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                    <i class="fas fa-chevron-left mr-2"></i> Back to Dashboard
                </a>
            </div>

            <!-- Article Card -->
            <div class="bg-white/70 backdrop-blur-md shadow-2xl rounded-3xl overflow-hidden border border-gray-200">
                <!-- Header -->
                <div class="p-10 border-b border-gray-200 bg-white/40">
                    <div class="flex justify-between items-start flex-wrap gap-4">
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900 mb-3">{{ $article->title }}</h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-user-circle mr-1.5"></i>
                                    {{ $article->author_name ?? $article->employee->staff->username }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-1.5"></i>
                                    {{ \Carbon\Carbon::parse($article->published_date)->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($article->status)
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i> Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-pen mr-2"></i> Draft
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="grid grid-cols-1 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 p-10">
                        @if($article->image_url)
                        <div class="mb-10 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full h-auto object-cover rounded-xl">
                        </div>
                        @endif

                        <div class="prose max-w-none text-gray-800">
                            {!! $article->content !!}
                        </div>

                        @if($article->source_name)
                        <div class="mt-10 pt-6 border-t border-gray-100">
                            <p class="text-sm text-gray-500">
                                <strong>Source:</strong> {{ $article->source_name }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <aside class="lg:col-span-1 bg-white/50 p-8 border-l border-gray-200 space-y-8">
                        <!-- Categories -->
                        @if($article->categories->isNotEmpty())
                        <div>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Categories</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($article->categories as $category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Timeline -->
                        <div>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Timeline</h3>
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full mt-1 mr-3"></span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Created</p>
                                        <p class="text-sm text-gray-500">{{ $article->created_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <span class="w-3 h-3 bg-purple-500 rounded-full mt-1 mr-3"></span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Last Updated</p>
                                        <p class="text-sm text-gray-500">{{ $article->updated_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Author -->
                        <div>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Author</h3>
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                    <i class="fas fa-user text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $article->employee->staff->username }}</p>
                                    <p class="text-sm text-gray-500">{{ $article->employee->position }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .prose {
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            color: #1f2937;
            line-height: 1.75;
        }

        .prose h1, .prose h2, .prose h3 {
            color: #111827;
            font-weight: 700;
        }

        .prose a {
            color: #2563eb;
            font-weight: 500;
            text-decoration: none;
        }

        .prose a:hover {
            text-decoration: underline;
        }

        .prose img {
            border-radius: 1rem;
        }

        .prose blockquote {
            font-style: italic;
            border-left: 4px solid #d1d5db;
            padding-left: 1rem;
            color: #4b5563;
        }
    </style>
    @endpush
</x-app-layout>

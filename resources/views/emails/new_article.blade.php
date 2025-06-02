<!DOCTYPE html>
<html>
<head>
    <title>New Article Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .article-title { color: #2c3e50; font-size: 20px; }
        .button { 
            background-color: #3490dc; 
            color: white; 
            padding: 10px 15px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block;
        }
        .footer { margin-top: 20px; font-size: 12px; color: #7f8c8d; }
    </style>
</head>
<body>
    <h1>NewsFlow Update</h1>
    
    <h2 class="article-title">{{ $article->title }}</h2>
    <p><strong>Author:</strong> {{ $article->author_name }}</p>
    <p><strong>Published:</strong> {{ $article->published_date->format('F j, Y') }}</p>
    
    <p>{{ Str::limit(strip_tags($article->content), 200) }}</p>
    
    <a href="{{ url('/articles/' . $article->id) }}" class="button">Read Full Article</a>
    
    <!-- <div class="footer">
        <p>You're receiving this email because you're subscribed to NewsFlow updates.</p>
        <p>
            <a href="{{ $unsubscribeUrl }}">Unsubscribe from notifications</a> | 
            <a href="{{ url('/preferences') }}">Manage preferences</a>
        </p>
        <p>&copy; {{ date('Y') }} NewsFlow. All rights reserved.</p>
    </div> -->
</body>
</html>
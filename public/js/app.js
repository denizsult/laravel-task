 
async function loadArticles() {
    try {
        const data = await apiCall(API_ENDPOINTS.ARTICLES);
        displayArticles(data.data);
    } catch (error) {
        document.getElementById('loading').innerHTML = '<p class="text-red-500">Failed to load articles</p>';
    }
}

function displayArticles(articles) {
    const container = document.getElementById('articles-container');
    const loading = document.getElementById('loading');

    if (articles.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-600 dark:text-gray-400">No articles found</p>';
    } else {
        container.innerHTML = articles.map(article => `
            <article class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <h2 class="text-xl font-semibold mb-3">${escapeHtml(article.title)}</h2>
                <div class="text-gray-700 dark:text-gray-300 mb-4">${escapeHtml(article.body)}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Published on ${new Date(article.created_at).toLocaleDateString()}
                </div>
                
                <!-- Comments Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-medium">Comments</h3>
                        <button 
                            onclick="toggleComments('${article.id}')"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                            id="toggle-${article.id}"
                        >
                            Show Comments
                        </button>
                    </div>

                    <div id="comments-${article.id}" class="hidden">
                        ${getAuthToken() ? `
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <h4 class="font-medium mb-3">Add a Comment</h4>
                                <textarea 
                                    id="comment-${article.id}" 
                                    placeholder="Write your comment..."
                                    class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 resize-none"
                                    rows="3"
                                ></textarea>
                                <button 
                                    onclick="submitComment('${article.id}')"
                                    class="mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm transition-colors"
                                >
                                    Submit Comment
                                </button>
                            </div>
                        ` : `
                            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-center">
                                <p class="text-gray-700 dark:text-gray-300">Please login to add comments</p>
                            </div>
                        `}

                        <div id="comments-list-${article.id}" class="space-y-4">
                            <div class="text-center py-4">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading comments...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        `).join('');
    }

    loading.classList.add('hidden');
    container.classList.remove('hidden');
}

// Comment functions
async function toggleComments(articleId) {
    const commentsDiv = document.getElementById(`comments-${articleId}`);
    const toggleBtn = document.getElementById(`toggle-${articleId}`);

    if (commentsDiv.classList.contains('hidden')) {
        commentsDiv.classList.remove('hidden');
        toggleBtn.textContent = 'Hide Comments';
        await loadComments(articleId);
    } else {
        commentsDiv.classList.add('hidden');
        toggleBtn.textContent = 'Show Comments';
    }
}

async function loadComments(articleId) {
    try {
        const data = await apiCall(API_ENDPOINTS.ARTICLE_COMMENTS(articleId));
        displayComments(articleId, data.data);
    } catch (error) {
        document.getElementById(`comments-list-${articleId}`).innerHTML =
            '<p class="text-red-500 text-sm">Failed to load comments</p>';
    }
}

function displayComments(articleId, comments) {
    const container = document.getElementById(`comments-list-${articleId}`);

    if (comments.length === 0) {
        container.innerHTML = '<p class="text-gray-600 dark:text-gray-400 text-sm">No comments yet</p>';
    } else {
        container.innerHTML = comments.map(comment => {
            // Status badge styling
            let statusBadge = '';
            let commentOpacity = '';
            
            switch(comment.status) {
                case 'pending':
                    statusBadge = '<span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded">Pending</span>';
                    commentOpacity = 'opacity-60';
                    break;
                case 'published':
                    statusBadge = '<span class="text-xs px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded">Published</span>';
                    commentOpacity = '';
                    break;
                case 'rejected':
                    statusBadge = '<span class="text-xs px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded">Rejected</span>';
                    commentOpacity = 'opacity-40';
                    break;
                default:
                    statusBadge = '';
                    commentOpacity = '';
            }

            return `
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg ${commentOpacity}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-sm">${escapeHtml(comment.user.name)}</span>
                        <div class="flex items-center gap-2">
                            ${statusBadge}
                            <span class="text-xs text-gray-500 dark:text-gray-400">${new Date(comment.created_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">${escapeHtml(comment.content)}</p>
                    ${comment.status === 'rejected' && comment.rejection_reason ? 
                        `<p class="text-xs text-red-600 dark:text-red-400 mt-2 italic">Reason: ${escapeHtml(comment.rejection_reason)}</p>` : ''}
                </div>
            `;
        }).join('');
    }
}

async function submitComment(articleId) {
    const textarea = document.getElementById(`comment-${articleId}`);
    const content = textarea.value.trim();

    if (!content) {
        alert('Please enter a comment');
        return;
    }

    if (!getAuthToken()) {
        alert('Please login to comment');
        return;
    }

    try {
        const data = await apiCall(API_ENDPOINTS.ARTICLE_COMMENTS(articleId), {
            method: 'POST',
            body: JSON.stringify({ content })
        });

        if (data) {
            textarea.value = '';
            await loadComments(articleId);
            alert('Comment submitted for moderation');
        }
    } catch (error) {
        alert('Failed to submit comment');
    }
}

// Utility function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateHeader();
    loadArticles();
});
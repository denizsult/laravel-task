 const API_ENDPOINTS = {
    LOGIN: '/api/auth/login',
    LOGOUT: '/api/auth/logout',
    USER: '/api/user',
    ARTICLES: '/api/articles',
    ARTICLE_COMMENTS: (articleId) => `/api/articles/${articleId}/comments`,
};

 
const apiCall = async (url, options = {}) => {
    const token = localStorage.getItem('auth_token');
    
    const config = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token && { 'Authorization': `Bearer ${token}` }),
            ...options.headers
        },
        ...options
    };
    
    const response = await fetch(url, config);
    return response.json();
};

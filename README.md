# Laravel Task - Comment Moderation System

A Laravel 12.x application with comment moderation system using Redis cache and queue processing.

## Features

- **Laravel 12.x** with PHP 8.3+
- **UUID primary keys** for users, articles, and comments
- **Laravel Sanctum** authentication with personal access tokens
- **Redis caching** for comment pagination with 60-second TTL
- **Queue-based comment moderation** with banned word filtering
- **Rate limiting** (10 comments per minute per user)
- **Comprehensive testing** with PHPUnit
- **Docker Compose** setup with all services
- **GitHub Actions CI/CD** pipeline

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login with email/password, returns token
- `POST /api/auth/logout` - Logout (requires auth)

### Articles
- `GET /api/articles/{id}` - Get article details

### Comments
- `GET /api/articles/{id}/comments` - Get paginated comments (cached)
  - Query params: `page`, `per_page` (default 10)
- `POST /api/articles/{id}/comments` - Create comment (requires auth, rate limited)
  - Body: `{"content": "comment text"}`
  - Returns: `202 Accepted` with `{"comment_id": "uuid"}`

## Database Schema

### Users
- `id` (UUID, primary key)
- `name`, `email`, `password`
- `timestamps`

### Articles
- `id` (UUID, primary key)
- `title`, `body`
- `timestamps`

### Comments
- `id` (UUID, primary key)
- `article_id` (UUID, foreign key)
- `user_id` (UUID, foreign key)
- `content`, `status` (pending|published|rejected)
- `timestamps`

## Setup Instructions

### Using Docker (Recommended)

1. Clone the repository
2. Copy environment file:
   ```bash
   cp .env.example .env
   ```
3. Build and start containers:
   ```bash
   docker-compose up -d --build
   ```
4. Install dependencies and setup:
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   ```
5. Access the application at `http://localhost:8000`

### Local Development

1. Requirements:
   - PHP 8.3+
   - PostgreSQL 15+
   - Redis 7+
   - Composer

2. Install dependencies:
   ```bash
   composer install
   ```

3. Setup environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure database and Redis in `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel_task
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   ```

5. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

6. Start queue worker:
   ```bash
   php artisan queue:work
   ```

7. Start development server:
   ```bash
   php artisan serve
   ```

## Testing

Run the test suite:
```bash
php artisan test
```

Run with coverage:
```bash
php artisan test --coverage
```

## Comment Moderation

Comments are automatically moderated using a queue job:

1. Comments are created with `status="pending"`
2. `ModerateCommentJob` is dispatched to queue
3. Job checks content against banned words (configurable in `config/comments.php`)
4. Status updated to `published` or `rejected`
5. Cache invalidated for published comments

### Configuration

Edit `config/comments.php` or set environment variables:
```
COMMENT_CACHE_TTL=60
COMMENT_RATE_LIMIT=10
BANNED_WORDS="spam,abuse,inappropriate,offensive"
```

## Caching Strategy

- Comments are cached per page: `comments:article:{id}:page:{n}`
- TTL: 60 seconds (configurable)
- Cache invalidation on comment approval
- Supports Redis tagging for efficient cache clearing

## Rate Limiting

- 10 comments per minute per user (configurable)
- Returns `429 Too Many Requests` when exceeded
- Uses Laravel's built-in rate limiter

## Queue Configuration

- Redis-based queue with exponential backoff
- 3 retry attempts: 1min, 3min, 5min delays
- Idempotent job processing (skips non-pending comments)

## Default Test Data

The seeder creates:
- 2 users: john@example.com, jane@example.com (password: "password")
- 2 articles with sample comments
- Mixed comment statuses for testing

## API Usage Examples

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password"}'
```

### Create Comment
```bash
curl -X POST http://localhost:8000/api/articles/{article-id}/comments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"content": "Great article!"}'
```

### Get Comments
```bash
curl http://localhost:8000/api/articles/{article-id}/comments?page=1&per_page=10
```

## Development

### Code Style
```bash
./vendor/bin/pint
```

### Static Analysis
```bash
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# 🔍 Query Builder

The Hoist PHP Query Builder provides a fluent, expressive interface for building database queries. It supports complex joins, subqueries, aggregations, and advanced filtering while maintaining readability and preventing SQL injection.

## Basic Query Building

### **Simple Queries**

```php
class PostRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getAllPosts()
    {
        return $this->db->table('posts')
            ->select(['id', 'title', 'content', 'created_at'])
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getPostsByCategory($categoryId)
    {
        return $this->db->table('posts')
            ->where('category_id', $categoryId)
            ->where('status', 'published')
            ->get();
    }

    public function getRecentPosts($limit = 10)
    {
        return $this->db->table('posts')
            ->select(['id', 'title', 'excerpt', 'created_at'])
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getPostById($id)
    {
        return $this->db->table('posts')
            ->where('id', $id)
            ->first();
    }
}
```

### **Advanced Where Clauses**

```php
class UserQueryBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getActiveUsers()
    {
        return $this->db->table('users')
            ->where('status', 'active')
            ->where('email_verified', true)
            ->whereNotNull('last_login_at')
            ->get();
    }

    public function getUsersByRole($roles)
    {
        return $this->db->table('users')
            ->whereIn('role', $roles)
            ->where('status', 'active')
            ->get();
    }

    public function getUsersCreatedBetween($startDate, $endDate)
    {
        return $this->db->table('users')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    public function searchUsers($searchTerm)
    {
        return $this->db->table('users')
            ->where(function($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })
            ->where('status', 'active')
            ->get();
    }

    public function getAdminUsers()
    {
        return $this->db->table('users')
            ->where('role', 'admin')
            ->orWhere(function($query) {
                $query->where('role', 'moderator')
                      ->where('permissions', 'LIKE', '%admin%');
            })
            ->get();
    }
}
```

## Joins and Relationships

### **Table Joins**

```php
class PostWithRelationsBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getPostsWithAuthors()
    {
        return $this->db->table('posts')
            ->select([
                'posts.id',
                'posts.title',
                'posts.content',
                'posts.created_at',
                'users.name as author_name',
                'users.email as author_email'
            ])
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('posts.status', 'published')
            ->orderBy('posts.created_at', 'DESC')
            ->get();
    }

    public function getPostsWithCategories()
    {
        return $this->db->table('posts')
            ->select([
                'posts.*',
                'categories.name as category_name',
                'categories.slug as category_slug'
            ])
            ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->where('posts.status', 'published')
            ->get();
    }

    public function getPostsWithCommentCounts()
    {
        return $this->db->table('posts')
            ->select([
                'posts.*',
                'COUNT(comments.id) as comment_count'
            ])
            ->leftJoin('comments', function($join) {
                $join->on('posts.id', '=', 'comments.post_id')
                     ->where('comments.status', '=', 'approved');
            })
            ->where('posts.status', 'published')
            ->groupBy('posts.id')
            ->orderBy('comment_count', 'DESC')
            ->get();
    }

    public function getPopularPosts($minViews = 100)
    {
        return $this->db->table('posts')
            ->select([
                'posts.*',
                'post_stats.views',
                'post_stats.likes',
                'users.name as author_name'
            ])
            ->join('post_stats', 'posts.id', '=', 'post_stats.post_id')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('post_stats.views', '>=', $minViews)
            ->where('posts.status', 'published')
            ->orderBy('post_stats.views', 'DESC')
            ->get();
    }
}
```

### **Complex Joins with Conditions**

```php
class AdvancedJoinBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getUsersWithRecentOrders()
    {
        return $this->db->table('users')
            ->select([
                'users.*',
                'recent_orders.order_count',
                'recent_orders.total_amount'
            ])
            ->join(
                $this->db->raw('(
                    SELECT
                        user_id,
                        COUNT(*) as order_count,
                        SUM(total) as total_amount
                    FROM orders
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY user_id
                ) as recent_orders'),
                'users.id', '=', 'recent_orders.user_id'
            )
            ->where('users.status', 'active')
            ->orderBy('recent_orders.total_amount', 'DESC')
            ->get();
    }

    public function getProductsWithAverageRating()
    {
        return $this->db->table('products')
            ->select([
                'products.*',
                'COALESCE(rating_stats.avg_rating, 0) as average_rating',
                'COALESCE(rating_stats.review_count, 0) as review_count'
            ])
            ->leftJoin(
                $this->db->raw('(
                    SELECT
                        product_id,
                        AVG(rating) as avg_rating,
                        COUNT(*) as review_count
                    FROM reviews
                    WHERE status = "approved"
                    GROUP BY product_id
                ) as rating_stats'),
                'products.id', '=', 'rating_stats.product_id'
            )
            ->where('products.status', 'active')
            ->orderBy('rating_stats.avg_rating', 'DESC')
            ->get();
    }
}
```

## Subqueries and Advanced Filtering

### **Subquery Support**

```php
class SubqueryBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getUsersWithoutOrders()
    {
        return $this->db->table('users')
            ->whereNotExists(function($query) {
                $query->select('1')
                      ->from('orders')
                      ->whereRaw('orders.user_id = users.id');
            })
            ->where('users.status', 'active')
            ->get();
    }

    public function getTopSpendingUsers($limit = 10)
    {
        return $this->db->table('users')
            ->select([
                'users.*',
                $this->db->raw('(
                    SELECT SUM(total)
                    FROM orders
                    WHERE orders.user_id = users.id
                ) as total_spent')
            ])
            ->whereExists(function($query) {
                $query->select('1')
                      ->from('orders')
                      ->whereRaw('orders.user_id = users.id');
            })
            ->orderBy('total_spent', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getProductsAboveAveragePrice()
    {
        return $this->db->table('products')
            ->where('price', '>', function($query) {
                $query->select($this->db->raw('AVG(price)'))
                      ->from('products')
                      ->where('status', 'active');
            })
            ->where('status', 'active')
            ->orderBy('price', 'DESC')
            ->get();
    }

    public function getCategoriesWithActiveProducts()
    {
        return $this->db->table('categories')
            ->select([
                'categories.*',
                $this->db->raw('(
                    SELECT COUNT(*)
                    FROM products
                    WHERE products.category_id = categories.id
                    AND products.status = "active"
                ) as active_product_count')
            ])
            ->having('active_product_count', '>', 0)
            ->orderBy('active_product_count', 'DESC')
            ->get();
    }
}
```

## Aggregations and Grouping

### **Aggregate Functions**

```php
class AggregationBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getSalesStatsByMonth()
    {
        return $this->db->table('orders')
            ->select([
                $this->db->raw('YEAR(created_at) as year'),
                $this->db->raw('MONTH(created_at) as month'),
                $this->db->raw('COUNT(*) as order_count'),
                $this->db->raw('SUM(total) as total_revenue'),
                $this->db->raw('AVG(total) as average_order_value'),
                $this->db->raw('MAX(total) as largest_order'),
                $this->db->raw('MIN(total) as smallest_order')
            ])
            ->where('status', 'completed')
            ->groupBy($this->db->raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->get();
    }

    public function getUserEngagementStats()
    {
        return $this->db->table('users')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                $this->db->raw('COUNT(DISTINCT orders.id) as order_count'),
                $this->db->raw('COUNT(DISTINCT reviews.id) as review_count'),
                $this->db->raw('COUNT(DISTINCT comments.id) as comment_count'),
                $this->db->raw('COALESCE(SUM(orders.total), 0) as total_spent'),
                $this->db->raw('DATEDIFF(NOW(), users.created_at) as days_since_signup')
            ])
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->leftJoin('reviews', 'users.id', '=', 'reviews.user_id')
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
            ->where('users.status', 'active')
            ->groupBy('users.id')
            ->having('order_count', '>', 0)
            ->orderBy('total_spent', 'DESC')
            ->get();
    }

    public function getProductPerformanceMetrics()
    {
        return $this->db->table('products')
            ->select([
                'products.id',
                'products.name',
                'products.price',
                $this->db->raw('COUNT(DISTINCT order_items.order_id) as orders_containing_product'),
                $this->db->raw('SUM(order_items.quantity) as total_quantity_sold'),
                $this->db->raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                $this->db->raw('AVG(reviews.rating) as average_rating'),
                $this->db->raw('COUNT(DISTINCT reviews.id) as review_count')
            ])
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.status', '=', 'completed');
            })
            ->leftJoin('reviews', function($join) {
                $join->on('products.id', '=', 'reviews.product_id')
                     ->where('reviews.status', '=', 'approved');
            })
            ->where('products.status', 'active')
            ->groupBy('products.id')
            ->orderBy('total_revenue', 'DESC')
            ->get();
    }
}
```

## Pagination and Chunking

### **Efficient Data Processing**

```php
class PaginationBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function paginateUsers($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $users = $this->db->table('users')
            ->select(['id', 'name', 'email', 'created_at', 'status'])
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $total = $this->db->table('users')
            ->where('status', 'active')
            ->count();

        return [
            'data' => $users,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next_page' => $page * $perPage < $total,
                'has_prev_page' => $page > 1
            ]
        ];
    }

    public function cursorPaginate($lastId = null, $perPage = 20)
    {
        $query = $this->db->table('posts')
            ->select(['id', 'title', 'content', 'created_at'])
            ->where('status', 'published')
            ->orderBy('id', 'DESC')
            ->limit($perPage + 1); // Get one extra to check if there's more

        if ($lastId) {
            $query->where('id', '<', $lastId);
        }

        $posts = $query->get();

        $hasMore = count($posts) > $perPage;
        if ($hasMore) {
            array_pop($posts); // Remove the extra item
        }

        $nextCursor = $hasMore && !empty($posts) ? end($posts)['id'] : null;

        return [
            'data' => $posts,
            'pagination' => [
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
                'per_page' => $perPage
            ]
        ];
    }

    public function chunkProcess($callback, $chunkSize = 1000)
    {
        $lastId = 0;

        do {
            $chunk = $this->db->table('users')
                ->where('id', '>', $lastId)
                ->orderBy('id', 'ASC')
                ->limit($chunkSize)
                ->get();

            if (empty($chunk)) {
                break;
            }

            // Process chunk
            $callback($chunk);

            // Update last ID for next iteration
            $lastId = end($chunk)['id'];

        } while (count($chunk) === $chunkSize);
    }
}
```

## Query Optimization

### **Performance-Focused Queries**

```php
class OptimizedQueryBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getPostsWithEagerLoading($categoryId = null)
    {
        // Single query to get posts with all related data
        $query = $this->db->table('posts')
            ->select([
                'posts.id',
                'posts.title',
                'posts.excerpt',
                'posts.created_at',
                'users.name as author_name',
                'users.avatar as author_avatar',
                'categories.name as category_name',
                'categories.slug as category_slug',
                'COUNT(DISTINCT comments.id) as comment_count',
                'COUNT(DISTINCT likes.id) as like_count'
            ])
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->leftJoin('comments', function($join) {
                $join->on('posts.id', '=', 'comments.post_id')
                     ->where('comments.status', '=', 'approved');
            })
            ->leftJoin('likes', 'posts.id', '=', 'likes.post_id')
            ->where('posts.status', 'published');

        if ($categoryId) {
            $query->where('posts.category_id', $categoryId);
        }

        return $query
            ->groupBy(['posts.id', 'users.id', 'categories.id'])
            ->orderBy('posts.created_at', 'DESC')
            ->get();
    }

    public function getBatchUserData($userIds)
    {
        // Efficient batch loading
        return $this->db->table('users')
            ->select([
                'id',
                'name',
                'email',
                'avatar',
                'created_at'
            ])
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id'); // Index by ID for fast lookups
    }

    public function getPopularPostsWithCache($limit = 10)
    {
        // Use database query with caching hint
        return $this->db->table('posts')
            ->select([
                'posts.*',
                'post_stats.views',
                'post_stats.updated_at as stats_updated'
            ])
            ->join('post_stats', 'posts.id', '=', 'post_stats.post_id')
            ->where('posts.status', 'published')
            ->where('post_stats.updated_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->orderBy('post_stats.views', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function searchPostsEfficiently($searchTerm, $limit = 20)
    {
        // Use proper indexing strategy
        return $this->db->table('posts')
            ->select(['id', 'title', 'excerpt', 'created_at'])
            ->where(function($query) use ($searchTerm) {
                // Prioritize title matches
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('content', 'LIKE', "%{$searchTerm}%");
            })
            ->where('status', 'published')
            ->orderBy($this->db->raw("
                CASE
                    WHEN title LIKE '%{$searchTerm}%' THEN 1
                    ELSE 2
                END
            "))
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}
```

## Raw Queries and Complex Operations

### **Advanced SQL Operations**

```php
class RawQueryBuilder
{
    private $db;

    public function __construct()
    {
        $this->db = Instance::get()->database;
    }

    public function getComplexAnalytics()
    {
        return $this->db->query("
            WITH monthly_stats AS (
                SELECT
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as order_count,
                    SUM(total) as revenue,
                    AVG(total) as avg_order_value
                FROM orders
                WHERE status = 'completed'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ),
            growth_rates AS (
                SELECT
                    month,
                    revenue,
                    LAG(revenue) OVER (ORDER BY month) as prev_month_revenue,
                    ((revenue - LAG(revenue) OVER (ORDER BY month)) /
                     LAG(revenue) OVER (ORDER BY month) * 100) as growth_rate
                FROM monthly_stats
            )
            SELECT
                month,
                revenue,
                ROUND(growth_rate, 2) as growth_percentage
            FROM growth_rates
            ORDER BY month DESC
        ");
    }

    public function updateBulkPrices($categoryId, $increasePercentage)
    {
        return $this->db->execute("
            UPDATE products
            SET
                price = price * (1 + ? / 100),
                updated_at = NOW()
            WHERE category_id = ?
                AND status = 'active'
        ", [$increasePercentage, $categoryId]);
    }

    public function getTopCustomersBySegment()
    {
        return $this->db->query("
            SELECT
                u.id,
                u.name,
                u.email,
                COUNT(DISTINCT o.id) as order_count,
                SUM(o.total) as total_spent,
                AVG(o.total) as avg_order_value,
                DATEDIFF(NOW(), MAX(o.created_at)) as days_since_last_order,
                CASE
                    WHEN SUM(o.total) >= 1000 THEN 'VIP'
                    WHEN SUM(o.total) >= 500 THEN 'Premium'
                    WHEN COUNT(o.id) >= 5 THEN 'Regular'
                    ELSE 'New'
                END as customer_segment
            FROM users u
            JOIN orders o ON u.id = o.user_id
            WHERE o.status = 'completed'
                AND u.status = 'active'
            GROUP BY u.id
            HAVING total_spent > 100
            ORDER BY total_spent DESC
        ");
    }

    public function generateSalesReport($startDate, $endDate)
    {
        return $this->db->query("
            SELECT
                p.name as product_name,
                c.name as category_name,
                SUM(oi.quantity) as units_sold,
                SUM(oi.quantity * oi.price) as revenue,
                AVG(oi.price) as avg_selling_price,
                COUNT(DISTINCT oi.order_id) as orders_containing_product,
                RANK() OVER (ORDER BY SUM(oi.quantity * oi.price) DESC) as revenue_rank
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed'
                AND o.created_at BETWEEN ? AND ?
            GROUP BY p.id, c.id
            HAVING revenue > 0
            ORDER BY revenue DESC
        ", [$startDate, $endDate]);
    }
}
```

## Query Builder Extensions

### **Custom Query Methods**

```php
class ExtendedQueryBuilder extends QueryBuilder
{
    public function whereDateRange($column, $startDate, $endDate)
    {
        return $this->where($column, '>=', $startDate)
                    ->where($column, '<=', $endDate);
    }

    public function whereActive()
    {
        return $this->where('status', 'active')
                    ->whereNull('deleted_at');
    }

    public function wherePublished()
    {
        return $this->where('status', 'published')
                    ->where('published_at', '<=', date('Y-m-d H:i:s'));
    }

    public function withoutTrashed()
    {
        return $this->whereNull('deleted_at');
    }

    public function onlyTrashed()
    {
        return $this->whereNotNull('deleted_at');
    }

    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'ASC');
    }

    public function random($seed = null)
    {
        if ($seed) {
            return $this->orderBy($this->db->raw("RAND({$seed})"));
        }
        return $this->orderBy($this->db->raw('RAND()'));
    }

    public function search($columns, $term)
    {
        return $this->where(function($query) use ($columns, $term) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%{$term}%");
            }
        });
    }

    public function paginate($page = 1, $perPage = 15)
    {
        $total = $this->count();
        $offset = ($page - 1) * $perPage;

        $items = $this->offset($offset)->limit($perPage)->get();

        return new PaginationResult($items, $total, $page, $perPage);
    }
}

class PaginationResult
{
    public $data;
    public $total;
    public $currentPage;
    public $perPage;
    public $totalPages;
    public $hasNextPage;
    public $hasPrevPage;

    public function __construct($data, $total, $currentPage, $perPage)
    {
        $this->data = $data;
        $this->total = $total;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->totalPages = ceil($total / $perPage);
        $this->hasNextPage = $currentPage < $this->totalPages;
        $this->hasPrevPage = $currentPage > 1;
    }

    public function toArray()
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'total_pages' => $this->totalPages,
                'has_next_page' => $this->hasNextPage,
                'has_prev_page' => $this->hasPrevPage
            ]
        ];
    }
}
```

---

The Query Builder provides a powerful, fluent interface for database operations while maintaining security and performance. Use these patterns to build complex queries efficiently and safely.

**Next:** [Authentication](../advanced/authentication.md) - Learn about user authentication and authorization systems.

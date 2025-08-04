# Cache Directory

This directory contains file-based cache storage for the Hoist PHP framework.

## Structure

-   `data/` - Cached data files organized in subdirectories
-   `tags/` - Cache tag indexes for group operations
-   `meta/` - Cache metadata and statistics
-   `.htaccess` - Prevents direct web access to cache files

## Security

-   All cache files are protected by `.htaccess`
-   Cache files are excluded from version control via `.gitignore`
-   Directory permissions should be `755` with web server write access

## Maintenance

Cache files are automatically managed by the framework:

-   Expired entries are cleaned up automatically
-   Cache can be cleared via `$cache->flush()`
-   Individual entries can be removed via `$cache->forget($key)`

## Do Not Modify

Files in this directory are managed automatically by the caching system.
Manual modification may cause data corruption or cache inconsistencies.

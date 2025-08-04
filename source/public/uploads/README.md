# Uploads Directory

This directory contains user-uploaded files.

## Security Notes

-   Files in this directory are excluded from version control
-   Configure appropriate file type restrictions
-   Implement virus scanning for production use
-   Set proper directory permissions (755)

## File Organization

Consider organizing uploads by:

-   Date: `/uploads/2024/08/filename.ext`
-   User: `/uploads/users/123/filename.ext`
-   Type: `/uploads/images/filename.jpg`

## Do Not Commit

User uploaded files should never be committed to version control.

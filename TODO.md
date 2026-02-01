# Fix Errors in admin/gig-action.php

## Issues Identified:
1. **Database Connection Inconsistency**: Uses PDO while other admin files use mysqli
2. **Undefined Request Object**: Uses Python-style `request.method` and `request.path` instead of PHP superglobals
3. **Potential Type Issues**: Setting deactivated_by_admin to integer 1 instead of boolean

## Plan:
- [ ] Change database connection from getPDOConnection() to getDBConnection() for consistency
- [ ] Fix logging to use PHP $_SERVER superglobals instead of undefined request object
- [ ] Ensure proper error handling and security practices
- [ ] Test the functionality after fixes

## Files to Edit:
- admin/gig-action.php

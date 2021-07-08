# Staging / Production Directory Comparison Tool
Tool for comparing directories and pushing certain (or all) files from staging to production and vise-versa.

## Notes
Ajax requests are secured through CSRF tokens, which requires the session to have started before headers are sent. Include 
this somewhere near the start of your code:
```
if (session_status() === PHP_SESSION_NONE)
    session_start();
```
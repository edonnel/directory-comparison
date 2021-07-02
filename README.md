# Staging / Production Directory Comparison Tool
Tool for comparing directories and pushing certain or all files from staging to production and vise-versa.

## Notes
Ajax requests are secured through CSRF, which requires the session to have started before headers are sent. Include 
this somewhere near the start of your code:
```
if (session_status() === PHP_SESSION_NONE)
    session_start();
```

## To-Do
- ~~security~~
- make listing return as json. use this `header('Content-Type: application/json');`
- ~~load fontawesome~~
- sync - Warning: copy(/home/nwpawia/staging.nwpajobconnect.org/admin/modules/staging/files/backup/06-30-2021_06-38-01//modules/errors/database.php): failed to open stream: No such file or directory in /home/nwpawia/staging.nwpajobconnect.org/admin/modules/staging/files/src/php/deployment.class.php on line 365
- queue for next push
- bulk actions
- load to see more files
- order by date modified
- filter by changed vs new
- ignore file names (error_log)
- ~~add to ignored files manually~~
- notes section
- push entire directory (down arrow)
- updater?
- upgrade to SVG and stop using FontAwesome
- make branch with `extra.php`
- add db config, /uploads, /image to EWS branch
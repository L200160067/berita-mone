\# AI Server Context (Single Source of Truth)



Server Type:

Shared Hosting (CWP)



Environment Constraints:



\- No SSH access

\- No terminal execution

\- No composer install

\- No artisan execution on server

\- No background workers

\- No reliable cron system



PHP:

8.2



Database:

MySQL (access via phpMyAdmin)



Cloud:

Cloudflare (DNS + CDN)



\---



\# Critical Constraints



1\. Laravel CLI cannot be used in production

2\. Queue workers are NOT available

3\. Scheduler is NOT available

4\. Symlink may fail or be disabled

5\. File permissions may be inconsistent



\---



\# Architecture Implication



Server must be treated as:



"Dumb PHP runtime"



All build, optimization, and preparation must happen locally.



Production server only:



\- serves HTTP

\- connects to database

\- reads files


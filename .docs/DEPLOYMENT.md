\# Deployment Guide (CWP Safe – Production Ready)



\---



\# 1. CORE RULES (MANDATORY)



DO NOT RUN:



\- php artisan config:cache ❌

\- php artisan route:cache ❌

\- php artisan view:cache ❌

\- php artisan event:cache ❌



Reason:

These commands freeze environment config and will break production.



\---



\# 2. BUILD STRATEGY



Build locally WITHOUT config caching.



Allowed:



php artisan optimize:clear



\---



\# 3. ENV STRATEGY



.env MUST be configured ONLY on server.



Never upload .env from local.



\---



\# 4. DATABASE STRATEGY



DO NOT:



run migration remotely ❌



USE:



1\. Run migration locally

2\. Export SQL

3\. Import via phpMyAdmin



\---



\# 5. FILE STORAGE (STANDARDIZED)



ONLY USE:



public/uploads/



DO NOT USE:



storage/app/public ❌

storage:link ❌



\---



\# 6. DEPLOYMENT FLOW



Step 1 – Build locally

Step 2 – Copy project

Step 3 – Remove:

&#x20;   .env

&#x20;   node\_modules

&#x20;   tests

Step 4 – Upload to:

/public\_html/releases/vX



Step 5 – Test:

/releases/vX/public



Step 6 – Switch folder



\---



\# 7. SWITCH STRATEGY



Rename:



current → backup

releases/vX → current



\---



\# 8. PERMISSION FIX



Ensure writable:



storage/

bootstrap/cache/

public/uploads/



\---



\# 9. POST DEPLOY TEST



\- homepage

\- login

\- upload

\- database query



\---



\# 10. ROLLBACK



Rename:



backup → current



\---



\# FINAL RULE



If config cache is used → deployment is invalid.


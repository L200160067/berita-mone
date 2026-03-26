\# AI Agent Guide (CWP-Compatible Laravel)



\---



\# 1. DESIGN PRINCIPLE



Application must NOT depend on:



\- queue workers

\- scheduler

\- CLI execution



\---



\# 2. STORAGE RULE



Use:



public/uploads/



Never use:



storage:link

Storage::disk('public') (default)



\---



\# 3. QUEUE STRATEGY



QUEUE\_CONNECTION=sync is NOT a solution for heavy load.



Instead:



\- use lazy computation

\- compute on read

\- avoid background jobs



\---



\# 4. CONTROLLER RULE



Controllers must be lightweight.



Avoid:



heavy loops

heavy computation



\---



\# 5. PERFORMANCE RULE



Always:



\- paginate data

\- limit queries

\- eager load



\---



\# 6. DATABASE RULE



Avoid:



destructive migration



Use:



safe incremental schema



\---



\# 7. UPLOAD RULE



Use direct file move:



$file->move(public\_path('uploads'), $filename);



\---



\# 8. ERROR HANDLING



APP\_DEBUG=false in production



\---



\# FINAL RULE



If a solution requires:



\- SSH

\- queue worker

\- cron



Then it is INVALID for this project.


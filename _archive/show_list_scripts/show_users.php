<?php
system("cd c:\\xampp\\htdocs\\kp_club_management && php artisan tinker --execute=\"\\$users = DB::select('SELECT id, name, email FROM users'); print_r(\\$users);\" 2>&1");

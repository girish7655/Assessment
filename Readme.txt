1. Clone this Repository.
2. Download and Install Xampp and Download composer.
3. Associate composer with Xampp.
4. Unzip the code base and place it in the htdocs inside xampp folder.
5. Open the command prompt and run the command composer install.
6. Run php artisan migrate command to migrate all the database migrations.
7. Run php artisan db:seeder --class="RoleSeeder" to seed roles data into database.
8. Run php artisan storage:link to display the upload images.



 //optional
8. If you wish to have fake data run this command php artisan db:seeder --class="DatabaseSeeder".
9 Run php artisan serve to run the server.
10. Click Register and verify your email and then you are able to login into your account.

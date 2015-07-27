The Auth endpoint does not behave in standard REST fashion. Instead, it uses different methods (GET, POST, etc.)
directed at distinct endpoints.

* auth/login - Handles logging a user in
* auth/logout - Handles logging a user out
* auth/reset-password-start - Starts password reset process
* auth/reset-password-finish - Last step in password reset process
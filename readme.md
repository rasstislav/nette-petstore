Nette Petstore - OpenAPI 3.0
============================

This is a sample Pet Store Server based on the OpenAPI 3.0 specification.


Requirements
------------

This Web Project is compatible with Nette 3.2 and requires PHP 8.3.


Web Server Setup
----------------

To quickly dive in, use PHP's built-in server:

Frontend Server:

	php -S localhost:8000 -t www

API Server:

	php -S localhost:8001 -t www

Then, open `http://localhost:8000` in your browser to view the welcome page. The frontend server will communicate with the API server running on `http://localhost:8001`.

Make sure to configure your application to use `http://localhost:8001` as the base URI for API requests.

For Apache or Nginx users, configure a virtual host pointing to your project's `www/` directory.

**Important Note:** Ensure `app/`, `config/`, `log/`, and `temp/` directories are not web-accessible.
Refer to [security warning](https://nette.org/security-warning) for more details.
## Introduction

This is a package for Laravel 4 that provides a basis for creating APIs.  Particularly, it allows for OAuth 2.0 implementation using any grant type your application requires, including custom grant types.

Additionally, it also standardizes all API responses into an easily definable format.

OAuth 2.0 support is built on top of bshaffer's Oauth2 Server Library for PHP: http://bshaffer.github.io/oauth2-server-php-docs/

For excellent descriptions of OAuth 2.0 and how it is implemented, please check out his documentation.

As a high-level introduction to how OAuth 2.0 is used in ApiFoundation, essentially there is a "token" endpoint where, given particular parameters (such as a user's username and password), returns an access token which maps directly to a user and can then be used to authenticate future API requests.

Those particular parameters are determined by which "grant type" you choose to use in your API.  There are several grant types to choose from:

Authorization Code: this is the standard OAuth 2.0 implementation.  If you've ever used Facebook's Graph API OAuth 2.0 implementation, this is essentially that flow, where
a user is directed to a login screen where they can log in to your system somehow and then authorize an app.  Doing so returns an "authorization code" (which is not the same as an access token!).
This authorization code can then be sent to your API's token endpoint to receive an access token.

Password: simpler implementation, where the user's username and password are sent to the token endpoint directly to receive an access token.  This is the most convenient way to authenticate a user.

Client Credentials:  the app's client id and secret are sent to the token endpoint to receive an access token.  This access token does not map to a user, however.  Im essence, this is the app itself using the API, with access only to the resources under the app's control (as opposed to those accessible to a user).

Refresh Token:  A "refresh token" is sent back when a user is authenticated via the Authorization Code or Password grant types.  This token can then be sent back to the token endpoint for a fresh access token.

Implicit: This is the same as the Authorization Code grant type, except instead of the authorization code being returned when a user logs in to your system, the access token is returned directly.

(Custom grant type) Facebook Access Token: You may create your own grant types.  One such custom grant type is the Facebook Access Token grant type, which allows you to send a Facebook access token to the token endpoint to receive an access token.
In other words, it exchanges a Facebook access token (which identifies a FACEBOOK user) for one of your app's access tokens (which identifies one of YOUR users).  So, if your app has a "login with Facebook" feature, the access token returned by Facebook at the end of their auth flow can then be used to create and/or identify a user in your system.


## Installation

You may use ApiFoundation with new projects or existing, however existing projects will require some modification.  We will start with the steps for a new project first.

# New Project Installation

Install via composer.

Add the service provider to your list of providers in app/config/app.php: 'Shaunpersad\ApiFoundation\ApiFoundationServiceProvider'

Publish the included config file, to make it available to your project for modification: php artisan config:publish shaunpersad/api-foundation

Run the included migrations (Note: this will create a "users" table): php artisan migrate --package="shaunpersad/api-foundation"

This is an included database seeder which you may wish to use as a basis for your own seeder: shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/Database/OAuthSeeder.php

The config file and the created tables are designed to work together out of the box, however should you choose to modify the users table, please check the config file to make sure you change the appropriate values.

Also, if you plan to utilize Facebook integration, please set a Facebook App ID and a Facebook App Secret in the config file.

Find the included sample-routes.php file: shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/sample-routes.php

In it, you will find the various routes you may wish to implement, which will be described in further detail in the "Endpoints" section.

# Existing Project Installation

Install via composer.

Add the service provider to your list of providers in app/config/app.php: 'Shaunpersad\ApiFoundation\ApiFoundationServiceProvider'

Publish the included config file, to make it available to your project for modification: php artisan config:publish shaunpersad/api-foundation

If you already have your own "users" table, DO NOT run the included migrations.  Instead, create your own migration, then find the included create_oauth_tables migration file and copy the code into your own migration file, then run this migration.

This is an included database seeder which you may wish to use as a basis for your own seeder: shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/Database/OAuthSeeder.php

If you are using your own "users" table, then you will likely need to modify the config file to point ApiFoundation to the correct fields in your users table.
Note: you will need to have a field that corresponds to a "username", e.g. an email address or an actual username.  You will also need to have a password field.

Also, if you plan to utilize Facebook integration, please set a Facebook App ID and a Facebook App Secret in the config file.

Next, you may need to extend the class that controls how ApiFoundation interacts with your database: ModelStorage.
While technically you may override any and all methods, you should only have to override a select few to suit your needs:

checkUserCredentials ($username, $password):bool
This accepts the user's username and raw password, and then checks that the password is valid.

getUserInfoByUsername ($username):array
Given a user's username, gets the user's info (typically a database row) as an associative array with a MANDATORY user_id key.

checkPassword ($username, $password):bool
This is by default used by the checkUserCredentials method.  You may override this if you are using a custom Auth method.

getUserInfoByFacebookId ($facebook_id):array
Given a user's facebook id, gets the user's info (typically a database row) as an associative array with a MANDATORY user_id key.

createFacebookUser (GraphUser $facebook_user):void
Creates a new user based on their facebook information.

If using Facebook integration, you may also need to extend the FacebookAccessToken grant type if you wish to control exactly how a
Facebook access token gets exchanged for one of your access tokens.  For example, if you do not wish to use the Facebook user's email address
as their username.

Find the included sample-routes.php file: shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/sample-routes.php

In it, you will find the various routes you may wish to implement, which will be described in further detail in the "Usage" section.
Please read through the comments for each route as you implement them.


## Endpoints

The sample-routes.php file contains several routes which can be grouped as the following kinds of API endpoints:

"authorize" endpoint
"token" endpoint
"redirect" endpoint
"resource" endpoint

# The Authorize endpoint

In the sample-routes.php file, this is the /authorize route.
This endpoint is used with the Authorization Code grant type.
A GET request to this endpoint should display a form or other method for a user to log in to your system.
A POST request to this endpoint should process the login and redirect the user to either a specified "redirect_uri" or back to the form with an error message.
If the user is redirected to the "redirect_uri", that URI should also contain either the Authorization Code ("code" query param), or the Access Token in the URL fragment if the grant type is "Implicit".

# The Token endpoint

In the sample-routes.php file, this is the /api/v1/get-token route.
This is the endpoint that, based on whichever grant type you are using, particular parameters are sent and an Access Token is received.
All requests to this endpoint must have a 'client_id' param, either as a query param, or in the Authorize HTTP Header (Http Basic).
'client_secret' should only be present if your app uses one.  Apps should not use client secrets if the
secrecy of the secret cannot be guaranteed.  If your app does use one, supply it either as a query param
or in the Authorize HTTP Header (Http Basic).

# The Redirect endpoint

In the sample-routes.php file, this is the /login-redirect route.
This is the URI that you'd want the user to be redirected to after being authorized through the Authorize endpoint.

# Resource endpoint

In the sample-routes.php file, this is the /api/v1/me route.
This is an example of an API resource.  Passing a valid access token to this route will return that authenticated user ("me") as a resource.

# Facebook routes

There are two additional routes included to demonstrate the Facebook Access Token grant type.
With the Facebook App ID and Secret supplied in the config file, the /get-facebook-login route will redirect you to Facebook to log in and authorize your app.
After authorizing, Facebook will redirect you to the /facebook-login-redirect route, and display your Facebook Access Token.
This Facebook Access Token can then be sent to the token endpoint to exchange for one of your app's access tokens.

## IoC Bindings

"oauth2" - a singleton which is the underlying OAuth 2.0 server object made by bshaffer.
"requires_oauth_token" - a filter which restricts routes to requiring a valid Access Token (as the "access_token" param).
"authorize_request" - a binding which creates a request for the Authorize endpoint.  You should pass in a "validate_error_callback" and a "validate_success_callback" to this when creating.  See the implementation in the sample-routes.php file.
"authorize_response" - a binding which creates a response for a request to the Authorize endpoint.  You should pass in the "is_authorized" parameter to indicate whether or not the user authorized your app, as well as the "user_id" parameter to indicate which user (if any) committed this action.
"token_response" - a binding which creates a response for the Token endpoint.  This response will include either an Access Token, or error information.
"api_response_array" - a binding which creates the structure for every API response.  This structure can be changed by simply extending our service provider and overriding the makeAPIResponseArray method.

## Examples

For reproducibility, all examples shown have the following assumptions:

 installation of this package in a brand new project
 you have seeded the database with the data in the included seeder
 the base URL is "http://apitest.local"
 the Token endpoint is a POST
 the client secret is not used

# Authorization Code

In your browser, navigate to http://apitest.local/authorize.
You should get an error, as the comments in the sample-routes.php file state that:

 * You must also have the response_type, client_id, state, and redirect_uri
 * set in the URL query, with
 * response_type = "code" if not implicit ("token" if implicit)
 * client_id = your client id,
 * state = any random thing,
 * redirect_uri = a valid redirect_uri from the database.

With the data from the included seeder,
client_id = "testclient",
redirect_uri = "http://apitest.local/login-redirect"
Including these in the URL query will cause the login form will be displayed properly.

e.g. http://apitest.local/authorize?client_id=testclient&response_type=code&redirect_uri=http://apitest.local/login-redirect&state=sdjf

For error checking, try removing parameters.

Authorize the app by clicking the "yes" button, with valid credentials.  If using the included seeder, "admin@local.com" and "password" should suffice.
You should then be redirected to the redirect_uri supplied, with the "code" parameter in the URL as your Authorization Code, or the access_token parameter in the URL fragment as your Access Token if the response_type param was set to "token".

If you received an Authorization Code, you may then POST it (along with the other required params) to the Token endpoint to receive an access token

e.g. Using CocoaRestClient: https://www.dropbox.com/s/c4m86xgu94fpr1r/Screenshot%202014-06-23%2017.14.41.png

# Password

POST the required credentials and other params: https://www.dropbox.com/s/h7xmd9qlz7ft9vz/Screenshot%202014-06-23%2017.18.06.png

# Facebook Login

In your browser, navigate to http://apitest.local/get-facebook-login

You should be redirected to Facebook to log in and authorize the app. Once you have authorized or if you have previously authorized the app, you will
be redirected back to http://apitest.local/facebook-login-redirect, and your Facebook Access Token will be displayed.

You may then POST it (along with the other required params) to the Token endpoint: https://www.dropbox.com/s/dzaxzva56tdcc92/Screenshot%202014-06-23%2017.23.40.png

# Accessing the "me" resource

POST to the "me" endpoint with a valid access token:

1) the admin@local.com user: https://www.dropbox.com/s/dr48oanlpq2k9ju/Screenshot%202014-06-23%2017.27.06.png
2) the facebook user: https://www.dropbox.com/s/h0t0f1e482llbu9/Screenshot%202014-06-23%2017.25.33.png

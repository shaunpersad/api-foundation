## 0.1.6 Notes

Version 0.1.6 adds support for signing in with Google+.  Note, however, that you do not need to be an actual Google+ user to
sign in using Google+.  You simply need a Google account of any kind.  There are two supported ways to implement this in your app:
the recommended hybrid server-side flow using a server code: https://developers.google.com/+/web/signin/server-side-flow and
the pure server-side flow: https://developers.google.com/+/web/signin/redirect-uri-flow.

The hybrid server-side flow is supported in Api Foundation via the gplus_server_code Grant Type, where you must pass a
gplus_server_code parameter which is the "one-time authorization code" specified in Google's documentation.

The pure server-side flow corresponds to the gplus_access_token Grant Type, where you must pass a gplus_access_token parameter
which is an access_token gotten by any other means from Google.

---

This version also adds further flexibility by adding in new config options to specify database field names specific to your app.

Note: if you are upgrading from a previous version of this package, please add a field in your users table that will be used as the
Google+ user id.  If you are using our original migrations, you can simply run >php artisan migrate --package="shaunpersad/api-foundation"
to add in the field automatically.

## Introduction

This is a package for Laravel 4 that provides a basis for creating APIs.  Particularly, it allows for OAuth 2.0 implementation using any Grant Type your application requires, including custom Grant Types.

Additionally, it also standardizes all API responses into an easily definable format.

## Key Concepts
* **Authorization**: A user must give permission for an app to access their resources on the resource server. E.g. When logging in to an app via Facebook, you are giving that app permission to access your resources on Facebook.
* **Authorization Code**: After a successful Authorization process, an Authorization Code is returned.  This code can then be exchanged for an Access Token.
* **Access Token**: A token presented when accessing a protected resource.  Often, this Access Token acts on behalf of a specific user in your system (however this does not always have to be the case).
* **Authentication**: Making a request for an Access Token.  The structure and requirements for this request are based on the Grant Types implemented by the app.
* **Grant Type**: The Authentication method used.  ApiFoundation implements multiple Grant Types, and allows for custom Grant Types to be created.
* **Client**: An app that is using your API.  It can be anything: a website, mobile app, or a server.  "Client" and "app" will be used interchangeably in this doc.
* **Client ID**: Used to identify an app.
* **Client Secret**: A password used to verify the client.  Optional, and, according to OAuth 2.0, MUST be excluded if this secret is at risk of being exposed publicly.

## OAuth 2.0 in ApiFoundation

OAuth 2.0 support is built on top of bshaffer's Oauth2 Server Library for PHP: http://bshaffer.github.io/oauth2-server-php-docs/

For excellent descriptions of OAuth 2.0 and how it is implemented, please check out his documentation.

As a high-level introduction to how OAuth 2.0 is used in ApiFoundation, essentially there is a "Token" endpoint where, given particular parameters (such as a user's username and password), returns an Access Token which (usually) maps directly to a user and can then be used to authenticate future API requests.

Those particular parameters are determined by which Grant Type you choose to use in your API.  There are several Grant Types to choose from:

* **Authorization Code**: this is the standard OAuth 2.0 implementation.  If you've ever used Facebook's Graph API OAuth 2.0 implementation, this is essentially that flow, where
a user is directed to a login screen where they can log in to your system somehow and then authorize an app.  Doing so returns an Authorization Code (which, remember, is not the same as an Access Token!).
This Authorization Code can then be sent to your API's Token endpoint to receive an Access Token.

* **Password (User Credentials)**: simpler implementation, where the user's username and password are sent to the Token endpoint directly to receive an Access Token.  This is the most convenient way to authenticate a user.

* **Client Credentials**:  the app's Client ID and Client Secret are sent to the Token endpoint to receive an Access Token.  This Access Token does not map to a user, however.  In essence, this is the app itself using the API, with access only to the resources under the app's control (as opposed to those accessible to a user).

* **Refresh Token**:  A "Refresh Token" is sent back along with the Access Token when a user is authenticated via the Authorization Code or Password Grant Types.  This Refresh Token can then be sent back to the Token endpoint for a fresh Access Token.

* **Implicit**: This is the same as the Authorization Code Grant Type, except instead of the Authorization Code being returned when a user logs in to your system, the Access Token is returned directly.  This would typically be the preferred method when using the API in front-end JavaScript.

* **Facebook Access Token (Custom Grant Type)**: You may create your own Grant Types.  One such custom Grant Type is the "Facebook Access Token" Grant Type, which allows you to send a Facebook access token to the Token endpoint to receive an Access Token.
In other words, it exchanges a Facebook access token (which identifies a FACEBOOK user) for one of your resource server's Access Tokens (which identifies one of YOUR users).  So, if your app has a "login with Facebook" feature, the access token returned by Facebook at the end of the Facebook auth flow can then be used to create and/or identify a user in your system.

Supporting multiple Grant Types means that your API can be used in numerous situations while still providing a secure method for access, including in mobile apps, in front-end JavaScript, or even completely server-side.

## Installation

You may use ApiFoundation with new projects or existing, however existing projects will require some modification.  We will start with the steps for a new project first.

### New Project Installation

Install via composer.
>require: "shaunpersad/api-foundation": "0.1.6"

Add the service provider to your list of providers in `app/config/app.php`:
>'Shaunpersad\ApiFoundation\ApiFoundationServiceProvider'

Publish the included config file, to make it available to your project for modification:
>php artisan config:publish shaunpersad/api-foundation

This copies the config file to `app/config/packages/shaunpersad/api-foundation`

Run the included migrations (Note: this will create a "users" table):
>php artisan migrate --package="shaunpersad/api-foundation"

This is an included database seeder which you may wish to use as a basis for your own seeder: `shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/Database/OAuthSeeder.php`

The config file and the created tables are designed to work together out of the box, however should you choose to modify the users table, please check the config file to make sure you change the appropriate values.

Also, if you plan to utilize Facebook integration, please set a Facebook App ID and a Facebook App Secret in the config file.

Find the included `sample-routes.php` file: `shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/sample-routes.php`

In it, you will find the various routes you may wish to implement, which will be described in further detail in the "Endpoints" section.  Copy these routes into your project.

### Existing Project Installation

Install via composer.
>require: "shaunpersad/api-foundation": "0.1.6"

Add the service provider to your list of providers in `app/config/app.php`:
>'Shaunpersad\ApiFoundation\ApiFoundationServiceProvider'

Publish the included config file, to make it available to your project for modification:
>php artisan config:publish shaunpersad/api-foundation

If you already have your own "users" table, DO NOT run the included migrations.  Instead, create your own migration, then find the included create_oauth_tables migration file (`shaunpersad/api-foundation/src/migrations/`) and copy the code into your own migration file, then run this migration.

This is an included database seeder which you may wish to use as a basis for your own seeder: `shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/Database/OAuthSeeder.php`

If you are using your own "users" table, then you will likely need to modify the config file to point ApiFoundation to the correct fields in your users table.
Note: you will need to have a field that corresponds to a "username", e.g. an email address or an actual username.  You will also need to have a password field.

Also, if you plan to utilize Facebook integration, please set a Facebook App ID and a Facebook App Secret in the config file.

Next, you may need to extend the class that controls how ApiFoundation interacts with your database: ModelStorage.
While technically you may override any and all methods, you should only have to override a select few to suit your needs:

* >checkUserCredentials ($username, $password):bool

  This accepts the user's username and raw password, and then checks that the password is valid.

* >getUserInfoByUsername ($username):array

  Given a user's username, gets the user's info (typically a database row) as an associative array with a MANDATORY user_id key.

* >checkPassword ($username, $password):bool

  This is by default used by the checkUserCredentials method.  You may override this if you are using a custom Auth method.

* >getUserInfoByFacebookId ($facebook_id):array

  Given a user's facebook id, gets the user's info (typically a database row) as an associative array with a MANDATORY user_id key.

* >createFacebookUser (GraphUser $facebook_user):void

  Creates a new user in your system based on their Facebook information.

If using Facebook integration, you may also need to extend the FacebookAccessToken Grant Type if you wish to control exactly how a
Facebook access token gets exchanged for one of your Access Tokens.  For example, if you do not wish to use the Facebook user's email address
as their username.

In order to use your extended ModelStorage and/or FacebookAccessToken classes, you must also override the relevant IoC bindings, which may include `oauth2`, `oauth2_grant_types`, and/or `oauth2_storage` (see the "IoC Bindings" section).

Find the included `sample-routes.php` file: `shaunpersad/api-foundation/src/Shaunpersad/ApiFoundation/sample-routes.php`

In it, you will find the various routes you may wish to implement, which will be described in further detail in the "Usage" section.
Please read through the comments for each route as you implement them.


## Endpoints

The `sample-routes.php` file contains several routes which can be identified as the following kinds of API endpoints:

### The Authorize endpoint

In the `sample-routes.php file`, this is the `/authorize` route.
This endpoint is used with the Authorization Code Grant Type.
A GET request to this endpoint should display a form or other method for a user to log in to your system.
A POST request to this endpoint should process the login and redirect the user to either a specified "redirect_uri" or back to the form with an error message.
If the user is redirected to the "redirect_uri", that URI should also contain either the Authorization Code ("code" query param), or the Access Token in the URL fragment if the Grant Type is "Implicit".

### The Token endpoint

In the `sample-routes.php` file, this is the `/api/v1/get-token` route.
This is the endpoint that, based on whichever Grant Type you are using, particular parameters are sent and an Access Token is received.

#####Required Parameters

* `client_id` - must be present either in the body of the request or in the Authorize HTTP Header (Http Basic).
* `grant_type` - must be present in the body of the request, and set to one of your Grant Types.

    The value of this param maps to one of the described Grant Types via the `oauth2_grant_types` IoC binding in the ApiFoundationServiceProvider.
    The default mapping is as follows, where the key is what you'd use as the `grant_type` value:
    ```
            return array(
                'authorization_code' => '\OAuth2\GrantType\AuthorizationCode',
                'password' => '\OAuth2\GrantType\UserCredentials',
                'client_credentials' => '\OAuth2\GrantType\ClientCredentials',
                'refresh_token' => '\OAuth2\GrantType\RefreshToken',
                'fb_access_token' => '\Shaunpersad\ApiFoundation\OAuth2\GrantType\FacebookAccessToken',
                'gplus_access_token' => '\Shaunpersad\ApiFoundation\OAuth2\GrantType\GPlusAccessToken',
                'gplus_server_code' => '\Shaunpersad\ApiFoundation\OAuth2\GrantType\GPlusServerCode',
            );
    ```

    Of this list, the Grant Types you wish to support may be defined in the config file.  To add additional Grant Types,
    you will need to override the `oauth2_grant_types` binding by defining your own binding with the same key.
* `client_secret` - should only be present if your app uses one.  Apps should not use Client Secrets if it can be exposed publicly.  If a Client Secret is used, it must be present and must be either in the body of the request or in the Authorize HTTP Header (Http Basic).

### The Redirect endpoint

In the `sample-routes.php` file, this is the `/login-redirect` route.
This is the URI that you'd want the user to be redirected to after being authorized through the Authorize endpoint.

### Resource endpoint

In the `sample-routes.php` file, this is the `/api/v1/me` route.
This is an example of an API resource.  Passing a valid Access Token to this route will return that authenticated user ("me") as a resource.

### Facebook routes

There are two additional routes included to demonstrate the Facebook Access Token Grant Type.
With the Facebook App ID and Secret supplied in the config file, the `/get-facebook-login` route will redirect you to Facebook to log in and authorize your app.
After authorizing, Facebook will redirect you to the `/facebook-login-redirect` route, and display your Facebook access token.
This Facebook access token can then be sent to the Token endpoint to exchange for one of your app's Access Tokens.

### Google+ routes

There are three additional routes included to demonstrate the two Grant Types associated with Google+, corresponding to the two possible Google+ login flows.
With the Google Client ID and Secret supplied in the config file, the `/get-gplus-login` route will redirect you to Google to log in and authorize your app.
After authorizing, Google will redirect you to the `/gplus-login-redirect` route, and display your Google+ access token.
This Google+ access token can then be sent to the Token endpoint to exchange for one of your app's Access Tokens.

## IoC Bindings

 * **oauth2** - a *singleton* which is the underlying OAuth 2.0 server object made by bshaffer's Oauth2 Server Library: http://bshaffer.github.io/oauth2-server-php-docs/.
    This is used internally in the ApiFoundationServiceProvider, so you generally should not need to interact with this.

 * **oauth2_grant_types** - a *singleton* which creates an associative array mapping of `grant_type` to classes that implement that grant_type.
    You must override this binding with your own if you wish to use your own custom grant types.

 * **oauth2_storage** - a *singleton* which creates an object to handle interaction with the database.
    You must override this binding with your own if you wish to use your own custom storage.

 * **requires_oauth_token** - a *filter* which restricts routes to requiring a valid Access Token (as the "access_token" param).
    See the usage in the `sample-routes.php` file.

 * **authorize_request** - a *binding* which creates a request for the Authorize endpoint.
    You should pass in a "validate_error_callback" and a "validate_success_callback" to this when creating.
    This binding will likely only be used once.  See the usage in the `sample-routes.php` file.

 * **authorize_response** - a *binding* which creates a response for a request to the Authorize endpoint.
    You should pass in the "is_authorized" parameter to indicate whether or not the user authorized your app, as well as the "user_id" parameter to indicate which user (if any) committed this action.
    This binding will likely only be used once.  See the usage in the `sample-routes.php` file.

 * **token_response** - a *binding* which creates a response for the Token endpoint.
    This response will include either an Access Token, or error information.
    This binding will likely only be used once.  See the usage in the `sample-routes.php` file.

 * **api_response_array** - a *binding* which creates the structure for every API response.
    This structure can be changed by simply extending our service provider and overriding the makeAPIResponseArray method.

 * **access_token_data** - an *instance* available after successful authentication via the requires_oauth_token filter.
    Contains Access Token data.  You generally won't need to use this.

 * **oauth2_storage** - a *singleton* which creates an instance of Google_Client, required to handle the underlying Google+ auth.


## Helper Classes

 * **SuccessResponse** - Use SuccessResponse::make($object) to create a JSON response for successful API requests.  $object can be anything that would ordinarily be serializable by Laravel, such as an Eloquent Model.
 * **ErrorResponse** - Use ErrorResponse::make($message, $status, $headers) to create a JSON response for API errors.
 * **OAuthRequest** - Used to bridge between bshaffer's Oauth2 Server Library's Request objects and Laravel's.
 * **OAuthResponse** - Used to bridge between bshaffer's Oauth2 Server Library's Response objects and Laravel's.

All of the response objects utilize the structure defined by the api_response_array binding.

## Examples

For reproducibility, all examples shown have the following assumptions:

 * installation of this package in a brand new project
 * you have seeded the database with the data in the included seeder
 * you have copied all the routes from `sample-routes.php` into your routes
 * the base URL is http://apitest.local
 * the Token endpoint is a POST
 * the client secret is not used
 * the client id is passed in the request body (although in the headers is preferred)

### Using Authorization Code Grant Types

In your browser, navigate to http://apitest.local/authorize.
You should get an error, as the comments in the `sample-routes.php` file state that:

You must also have the `response_type`, `client_id`, `state`, and `redirect_uri` set in the URL query, with `response_type` = "code" if not implicit (`token` if implicit) `client_id` = your client id, `state` = any random thing, `redirect_uri` = a valid redirect_uri from the database.

With the data from the included seeder,
`client_id` = "testclient",
`redirect_uri` = "http://apitest.local/login-redirect"
Including these in the URL query will cause the login form will be displayed properly.

>e.g. http://apitest.local/authorize?client_id=testclient&response_type=code&redirect_uri=http://apitest.local/login-redirect&state=sdjf

For error checking, try removing parameters.

Authorize the app by clicking the "yes" button, with valid credentials.  If using the included seeder, `admin@local.com` and `password` should suffice.
You should then be redirected to the redirect_uri supplied, with the `code` parameter in the URL as your Authorization Code, or the `access_token` parameter in the URL fragment as your Access Token if the `response_type` parameter was set to `token`.

If you received an Authorization Code, you may then POST it (along with the other required params) to the Token endpoint to receive an Access Token

e.g. (Using CocoaRestClient): https://www.dropbox.com/s/c4m86xgu94fpr1r/Screenshot%202014-06-23%2017.14.41.png

### Using the Password Grant Type

POST the required credentials and other params: https://www.dropbox.com/s/h7xmd9qlz7ft9vz/Screenshot%202014-06-23%2017.18.06.png

### Using the Facebook Access Token Grant Type

In your browser, navigate to http://apitest.local/get-facebook-login

You should be redirected to Facebook to log in and authorize the app. Once you have authorized or if you have previously authorized the app, you will
be redirected back to http://apitest.local/facebook-login-redirect, and your Facebook Access Token will be displayed.

You may then POST it (along with the other required params) to the Token endpoint: https://www.dropbox.com/s/dzaxzva56tdcc92/Screenshot%202014-06-23%2017.23.40.png

### Accessing resources using Access Tokens

POST to the "me" endpoint with a valid Access Token:

1. the seeded user: https://www.dropbox.com/s/dr48oanlpq2k9ju/Screenshot%202014-06-23%2017.27.06.png
2. the facebook user: https://www.dropbox.com/s/h0t0f1e482llbu9/Screenshot%202014-06-23%2017.25.33.png

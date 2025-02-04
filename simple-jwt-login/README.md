=== Simple JWT Login - Login and Register to WordPress using JWT ===

Contributors: nicu_m
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PK9BCD6AYF58Y&source=url
Tags: jwt, API, auto login, register users, tokens, REST, auth, generate jwt, refresh jwt
Requires at least: 4.4.0
Tested up to: 5.7
Requires PHP: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

== Description ==

This plugin allows you to login or register to a WordPress website using a JWT.

Plugin Documentation Site: [Documentation](https://simplejwtlogin.com?utm_source=readme)

== Some awesome features ==

* Auto-login using JWT and AUTH_KEY
* Register new users via API
* Delete WordPress users based on a JWT 
* Allow auto-login / register / delete users only from specific IP addresses
* Allow register users only from a specific domain name
* API Route for generating new JWT 
* Get JWT from URL, SESSION, COOKIE or HEADER
* Pass request parameters to login URL
* CORS settings for plugin Routes
* Hooks 
* JWT Authentication
* `beta` Allow access private endpoints with JWT

== Login User ==

This plugin is customizable and offers you multiple methods to login to you website, based on multiple scenarios. 
 
In order to login, users have to send JWT. The plugin, validates the JWT, and if everything is OK, it can extract the WordPress email address or user ID.
Users can specify the exact key of the JWT payload where this information can be found.
  
Here are the methods how you can send the JWT in order to auto-login:

1. URL
2. Header
3. Cookie
4. Session

If the JWT is present in multiple places ( like URL and Header), the JWT will be overwritten.

This plugin supports multiple JWT Decryption algorithms, like: HS256, HS512, HS384, RS256,RS384 and RS512.

After the user is logged in you can automatically redirect the user to a page like:

- Dashboard 
- Homepage
- or any other custom Page ( this is mainly used for redirecting users to a landing page)

You can attach to your redirect a URL parameter `redirectUrl` that will be used for redirect instead of the defined ones. 
In order to use this, you have to enable it by checking the option `Allow redirect to a specific URL`.

Also, redirect after login offers some variables that you can use in the customURL and redirectUrl.
Here are the variables which you can use in your URL:
- {{site_url}} : Site URL
- {{user_id}} : Logged in user ID
- {{user_email}} : Logged in user email
- {{user_login}} : Logged in username
- {{user_first_name}} : User first name
- {{user_last_name}} : User last name
- {{user_nicename}} : User nice name
 
You can generate dynamic URLs with these variables, and, before the redirect, the specific value will be replaced.

Here is an example:
```
    http://yourdomain.com?param1={{user_id}}&param2={{user_login}}
``` 
 
Also, this plugin allows you to limit the auto-login based on the client IP address. 
If you are concerned about security, you can limit the auto-login only from some IP addresses.  
 
== Register Users ==

This plugin also allows you to create WordPress users.

This option is disabled by default, but you can enable it at any time.

In order to create users, you just have to make a POST request to the route URL, and send an *email* and a *password* as parameter and the new user will be created.

You can select the type for the new users: editor, author, contributor, subscriber, etc.

Also, you can limit the user creating only for specific IP addresses, or  specific email domains.

Another cool option is "Generate a random password when a new user is created". 
If this option is selected, the password is no more required when a new user is created a random password will be generated.

Another option that you have for register user is "Initialize force login after register". 
When the user registration is completed, the user will continue on the flow configured on login config. 

If auto-login is disabled, this feature will not work and the register user will go on a normal flow and return a json response.

If you want to add custom user_meta on user creation, just add the parameter `user_meta` with a json. This will create user_meta for the new user.

 ``
{"meta_key":"meta_value","meta_key2":"meta_value"}
``

These properties can be passed in the request when the new user is created.

- *email* : (required) (string)  The user email address.
- *password* :  (required) (string) The plain-text user password.
- *user_login* : (string) The user's login username.
- *user_nicename* : (string) The URL-friendly user name.
- *user_url* : (string) The user URL.
- *display_name* : (string) The user's display name. Default is the user's username.
- *nickname* : (string) The user's nickname. Default is the user's username.
- *first_name* : (string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
- *last_name* : (string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
- *description* : (string) The user's biographical description.
- *rich_editing* : (string) Whether to enable the rich-editor for the user. Accepts 'true' or 'false' as a string literal, not boolean. Default 'true'.
- *syntax_highlighting* : (string) Whether to enable the rich code editor for the user. Accepts 'true' or 'false' as a string literal, not boolean. Default 'true'.
- *comment_shortcuts* : (string) Whether to enable comment moderation keyboard shortcuts for the user. Accepts 'true' or 'false' as a string literal, not boolean. Default 'false'.
- *admin_color* : (string) Admin color scheme for the user. Default 'fresh'.
- *use_ssl* : (bool) Whether the user should always access the admin over https. Default false.
- *user_registered* : (string) Date the user registered. Format is 'Y-m-d H:m:s'.
- *user_activation_key* : (string) Password reset key. Default empty.
- *spam* : (bool) Multisite only. Whether the user is marked as spam. Default false.
- *show_admin_bar_front* : (string) Whether to display the Admin Bar for the user on the site's front end. Accepts 'true' or 'false' as a string literal, not boolean. Default 'true'.
- *locale* : (string) User's locale. Default empty.

== Delete User ==

Delete user it is disabled by default.

In order to delete a user, you have to configure where to search the details in the JWT. 
You can delete users by WordPress User ID or by Email address.

Also, you have to choose the JWT parameter key where email or user ID it is stored in the JWT.

Also, you can limit the deletion of users to specific IP addresses for security reasons. 

== Authentication ==

This plugin allows users to generate JWT tokens based from WordPress user email and password.
 
In order to Get a new JWT, just make a POST request to */auth* route with your WordPress email and password and the response will look something like this:

``
     {
         "success": true,
         "data": {
             "jwt": "NEW_GENERATED_JWT_HERE"
         }
     }
`` 
If you want to add extra parameters in the JWT payload, just send the parameter `payload` on `/auth` endpoint, and add a json with the values you want to be added in the payload.

At some point, the JWT will expire.
So, if you want to renew it without having to ask again for user and password, you will have to make a POST request to the *auth/refresh* route.

This will generate a response with a new JWT, similar to the one that `/auth` generates.

If you want to get some details about a JWT, and validate that JWT, you can call `/auth/validate`. If you have a valid JWT, details about the available WordPress user will be returned, and some JWT details.

If you want to revoke a JWT, access `/auth/revoke` and send the `jwt` as a parameter.

The plugin auto-generates the example URL you might need to test these scenarios.

== Auth codes ==
Auth codes are optional, but you can enable them for Auto-login, Register User and Delete user.

This feature allows you to add a layer of protection to your API routes. 

The Auth codes contains 3 parts:
  1. Authentication Key: This is the actual code that you have to add in the request.
  2. WordPress new User Role: can be used when you want to create multiple user types with the create user endpoint. If you leave it blank, the value configured in the 'Register Settings' will be used.
  3. Expiration Date: This allows you to set an expiration date for you auth codes. The format is `Y-M-D H:m:s'. Example : 2020-12-24 23:00:00. If you leave it blank, it will never expired.

Expiration date format: year-month-day hours:minutes:seconds

== Hooks ==

This plugin allows advanced users to link some hooks with the plugin and perform some custom scripts.
Currently, available hooks are:

- simple_jwt_login_login_hook
  - type: action
  - parameters: Wp_User $user
  - description: This hook it is called after the user has been logged in. 
  
- simple_jwt_login_redirect_hook
  - type: action
  - parameters: string $url, array $request
  - description: This hook it is called before the user it will be redirected to the page he specified in the login section. 
  
- simple_jwt_login_register_hook
  - type: action
  - parameters: Wp_User $user, string $plain_text_password
  - description: This hook it is called after a new user has been created.  
  
- simple_jwt_login_delete_user_hook
  - type: action
  - parameters: Wp_User $user
  - description: This hook it is called right after the user has been deleted.

- simple_jwt_login_jwt_payload_auth
  - type: filter
  - parameters: array $payload, array $request
  - return: array $payload
  - description: This hook is called on /auth endpoint. Here you can modify payload parameters. 

- simple_jwt_login_no_redirect_message 
  - type: filter
  - parameters: array $payload, array $request
  - return: array $payload
  - description: This hook is called on /autologin endpoint when the option `No Redirect` is selected. You can customize the message and add parameters.

== CORS == 
  The CORS standard it is needed because it allows servers to specify who can access its assets and how the assets can be accessed. 
  Cross-origin requests are made using the standard HTTP request methods like GET, POST, PUT, DELETE, etc.

== Screenshots ==

1. Dashboard
2. General Settings for JWT 
3. Auto-login configuration
4. Register new users configuration
5. Delete user configuration
6. Authentication configuration for generating and refresh Json Web Tokens
7. Auth Codes 
8. Available Hooks
9. CORS

== Installation ==

Here's how you install and activate the JWT-login plugin:

1. Download the Simple-JWT-login plugin.
2. Upload the .zip file in your WordPress plugin directory.
3. Activate the plugin from the "Plugins" menu in WordPress.

or

1. Go to the 'Plugins' menu in WordPress and click 'Add New'
2. Search for 'Simple JWT Login' and select 'Install Now'
3. Activate the plugin when prompted

Next steps: 

- Go to "General section"
    - set "JWT Decryption key". With this key, we will validate the JWT.
    - choose "JWT Decryption algorithm".

- Go to "Login Settings"
    - please set "Allow Auto-login" to "yes".
    - set parameter "Action" ( Login by WordPress User ID / User Email).
    - set the "JWT parameter key" with the key from your JWT where user email or user ID can be found in the decoded JWT.

After that, you can copy the sample URL from the top of the page ( Login Config section), replace the JWT string with your valid JWT, and you will be redirected to your WordPress and automatically logged in.

Also, if you don't want to add the JWT in the URL, you can add it in the header of the request with the key 'Authorizatoin'.
Please note that the JWT that is set in the header overwrites the one from the URL.

Example:


``
    Authorization: Bearer YOURJWTTOKEN
``


or 


``
    Authorization: YOURJWTTOKEN
``
    

== Frequently Asked Questions ==

= Is this plugin secure? =
Yes, this plugin is secure. It allows to auto-login to your WordPress website using a JWT, that is decrypted and validated against your JWT Decryption key. 
Make sure you set the specific user type when new users are created.

= Can I disable the API for registering new users? =
Yes, both Auto-login and register can be enabled or disabled.

= Can I limit the email addresses that can register in WordPress with this plugin? =
Yes, You can use the domain limitation and add multiple domains separated by comma. 
Users that don't provide an email from that domain, will get an error.

= Can I use a JWT generated by another plugin to login? =
Yes. The only thing you have to make sure, in order to work, is that you use the same "Decryption Key" and encryption algorithm.

= Is the Auth Code required? =
No, it is not required. You can disable it from 'Login config', 'Register Config' and 'Delete User Config'. Just set the parameter 'Login|Register requires Auth Code' to 'No'.

= I don't want other users to be able delete users. What should I do? =
The 'delete users option' is disabled by default. To make sure nobody will delete a user, please make sure the option "Allow Delete" is set to "No".

=Can I automatically log in to a WordPress website from my mobile App using this plugin?=
Yes. The main feature of this plugin is to automatically log in users into a WordPress website using a JWT. So, you can log in into WordPress from mobile apps, react native, angular, Vue js, meteor, backbone, javascript, etc.
 
= How to use hooks? =
 
 Here is a code example, how to send an email after a new user has been created.

``
    add_action( 'simple_jwt_login_register_hook', function($user, $password){
   	    $to      = $user->user_email;
   	    $subject = 'Welcome';
   	    $message = '
                   Welcome to My Site. Your new user credentials are: 
                   email: ' . $to .'
                   password: '. $password;
   	    wp_mail($to, $subject, $message);
       }, 10, 2);
``

Here is an example on how you can overwrite the "No Redirect" response after autologin:
``
    add_filter('simple_jwt_login_no_redirect_message',function($response, $request){
        $response['userId'] = get_current_user_id();
        $response['userDetails'] = wp_get_current_user();
        return $response;
    },10, 2);
``

= I cannot get the JWT from session. Where should I store the JWT? =
The plugin searches for the JWT in:
 - URL ( &jwt=YOUR JWT HERE)
 - SESSION (  ` $_SESSION['simple-jwt-login-token'] `)
 - COOKIE ( ` $_COOKIE['simple-jwt-login-token'] ` )
 - Header ( ` Authorization: Bearer YOUR_JWT_HERE `)

Also, the key name for each parameter, can be changed in the general section. 
 
= I would like to create users with different roles. It is possible? =
Yes. In order to be able to create different users with different roles, first you have to create some AUTH Codes, and set the desired roles for each Auth Code.
After that, for the `create user` route, simply add the AUTH code in the request, and the role from 'Register User' will be overwritten with the one from Auth Code.

== Changelog ==

The [Changelog](https://github.com/nicumicle/simple-jwt-login/blob/master/Changelog.md) can be found in the GitHub repository https://github.com/nicumicle/simple-jwt-login

Also, here you can find the beta version of the plugin, before it is released
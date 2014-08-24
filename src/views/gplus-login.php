<html itemscope itemtype="http://schema.org/Article">
<head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript">
        (function () {
            var po = document.createElement('script');
            po.type = 'text/javascript';
            po.async = true;
            po.src = 'https://plus.google.com/js/client:plusone.js?onload=start';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(po, s);
        })();
    </script>
    <!-- END Pre-requisites -->
</head>
<body>

<div class="container">


    <!--
    Add the sign-in button to your web page and set the data-redirecturi to postmessage to enable the one-time-code flow.
    You can prompt the user to re-authorize your app by adding the data-approvalprompt="force" parameter to your sign-in button.
    This will show the sign-in button every time the user access the app, and require them to grant access permission.
    When the button is clicked, the user is presented with the permissions dialog again,
    and upon authorization your app can exchange the received one-time code for an access token and a new refresh token.
    The old refresh token will be revoked.
    You should only include this parameter in limited cases because it causes the authorization dialog to display every time.
    -->
    <!-- Add where you want your sign-in button to render -->
    <div id="signinButton">
  <span class="g-signin"
        data-scope="https://www.googleapis.com/auth/plus.login"
        data-clientid="<?=$gplus_client_id?>"
        data-redirecturi="postmessage"
        data-accesstype="offline"
        data-cookiepolicy="single_host_origin"
        data-callback="signInCallback">
  </span>
    </div>
    <div id="result"></div>


</div>
<script type="text/javascript">
    function signInCallback(authResult) {

        /*
        * REMOVE THIS IN PRODUCTION.
        *
        */
        console.log(authResult);

        if (authResult['code']) {

            // Hide the sign-in button now that the user is authorized, for example:
            $('#signinButton').attr('style', 'display: none');


            // Send the code to the server
            $.ajax({
                type: 'POST',
                url: '/gplus-login-redirect',
                contentType: 'application/octet-stream; charset=utf-8',
                success: function(result) {

                    console.log(result);
                },
                processData: false,
                data: authResult
            });
        } else if (authResult['error']) {
            // There was an error.
            // Possible error codes:
            //   "access_denied" - User denied access to your app
            //   "immediate_failed" - Could not automatially log in the user
            // console.log('There was an error: ' + authResult['error']);
        }
    }
</script>

</body>

</html>

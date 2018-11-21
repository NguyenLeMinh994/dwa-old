<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="UTF-8">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('head.title')</title>

        <!--begin::Web font -->
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({ 
                google: {
                    "families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700","Asap+Condensed:500"]
                },
                active: function(){
                    sessionStorage.fonts = true;
                }
            });        
        </script>
        
        <link href="/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css" />
        <link href="/assets/app/base/style.bundle.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendors/custom/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/app/base/style.custom.css" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" href="/assets/app/media/img/logo/cloudlab_transparent_105x80.png" />
        
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <style>
            .ui-front{z-index: 9999 !important;}
            .ui-autocomplete-loading { background:url('/assets/app/media/img/wait.gif') no-repeat right center }
        </style>

        <!-- Scripts -->
        <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
        <script src="/assets/app/base/scripts.bundle.js" type="text/javascript"></script>
        <script src="/assets/vendors/custom/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script src="/assets/vendors/custom/cleave-js/cleave.min.js" type="text/javascript"></script>
        <script src="/assets/vendors/custom/numeral-js/numeral.js"></script>
        @yield('head.css')
    </head>

    <body class="m-page--fluid m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default">
        <div class="m-grid m-grid--hor m-grid--root m-page">
            @include ('partials.navbar_metro')

            @yield('body.content')

            @include ('partials.footer_metro')
        </div>

        

        
    </body>
</html>
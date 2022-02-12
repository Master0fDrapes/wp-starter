<?php
    function loginLogo() {
        ?>
        <style type="text/css">
        body.login div#login h1 a {
            background-image: url('');
            padding-bottom: 30px;
        }
        </style>
        <?php
    } add_action( 'login_enqueue_scripts', 'loginLogo' );

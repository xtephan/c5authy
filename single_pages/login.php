<?php
/**
* Login view
* c5authy
* @author: Stefan Fodor
* (C) Stefan Fodor @ 2014
*/
?>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<style>
input {
    height: 30px!important;
}
</style>

<div class="container" style="padding-top: 20px;">
    <div id="loginbox" style="margin-top:0px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading">
                <div class="panel-title">Sign In</div>
            </div>

            <div style="padding-top:30px" class="panel-body" >

                <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>

                <form  method="post" action="<?php echo $this->action('do_login'); ?>" id="loginform" class="form-horizontal" role="form" >

                    <?php
                    $usernamePlacehoder = (USER_REGISTRATION_WITH_EMAIL_ADDRESS == true) ? t('Email Address') : t('Username');
                    ?>
                    <div style="margin-bottom: 25px" class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="uName" type="text" class="form-control" name="uName" value="<?php echo (isset($uName)? $uName : '') ?>" placeholder="<?php echo $usernamePlacehoder?>">
                    </div>

                    <div style="margin-bottom: 25px" class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="uPassword" type="password" class="form-control" name="uPassword" placeholder="<?php echo t('Password'); ?>">
                    </div>

                    <div style="margin-bottom: 25px" class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-phone"></i></span>
                        <input id="uCode" type="password" class="form-control" name="uToken" placeholder="<?php echo t('Token'); ?>">
                    </div>

                    <div style="margin-top:10px" class="form-group">
                        <!-- Button -->

                        <div class="col-sm-12">
                            <input type="submit" value="<?php echo t('Login')?>" class="btn btn-primary">
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
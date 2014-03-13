<?php

class DashboardUsersAuthyController extends Controller {

    public function setIcons() {
        $sp = Page::getByPath('/dashboard/users/authy');
        $sp->setAttribute('icon_dashboard', "icon-cog");
    }

    public function view() {
        echo "hello from controller";
    }

}
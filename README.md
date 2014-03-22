C5 Authy Integration
===========================

Authy Integration in C5 websites.

Usage
-----

0. Create an Authy developer account, create a new application, upgrade the plan to Starter and grab the API KEY.
1. Download the archive in packages and extract it.
2. Install it from C5's Dashboard.
3. Navigate to /dashboard/users/authy/ and set up the API key you got from Authy.
4. Enable Authy Integration by choosing Authentication Type to OTP or 2F.
5. Enjoy!

Warnings
-----------

1. Due to the way Authy works, each user needs to be paired with a phone number. You can do this by editing the user
 and setting his phone number and country code in the newly installed attributes available. Failure in doing this step will result
 in the user being locked out. Even if he is admin. You have been warned!

2. It is recommended use the Production mode, unless running unittests. In sandbox mode, the authy id returned by the
API may not be accurate and can result in users being locked out.

3. Due to a flaw in Concrete5's core single page management, in order to provide the proper login view,
the file /single_pages/login.php in created that points to thw correct login page. If already exists a file with that name,
it will be automatically moved to /single_pages/login.php.bak

Requirements
-----------
* Concrete5 5.6.0 or newer
* PHP curl
* A phone

Phone application
-------------

You can get the Authy app from

__1. iOS__
* [Authy](https://itunes.apple.com/en/app/authy/id494168017?mt=8)

__2. Android__
* [Authy](https://play.google.com/store/apps/details?id=com.authy.authy)

Last Update
----
2014-03-22 23:25
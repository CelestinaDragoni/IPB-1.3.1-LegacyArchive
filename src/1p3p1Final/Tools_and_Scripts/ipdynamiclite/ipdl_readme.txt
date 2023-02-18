Invision Power Dynamic Lite
---------------------------

Installation Snyopsis
.....................

Open up index.php, edit the variables at the top save and upload to your root public_html / www folder.


INSTALLATION
------------

This guide assumes that you have installed IPB in /forums/ and wish for the portal to be the first page your visitors see.

Open up index.php and edit the path to where the forums directory is:

define( 'ROOT_PATH', "./forums" );

This is set to "forums" by default. If you had your IPB installed at http://www.domain.com/chat/ you'd need to make sure you have:

define( 'ROOT_PATH', "./chat" );

Save the file and upload it to your root web directory folder, often called "public_html" or "www".

UPLOADING IPB COMPONENTS
------------------------

If you've already got a version previous to RC2 set up, you should make sure you upload the following files found in the "upload" folder
of this zip file.

/skin/s1/skin_csite.php
/lang/en/lang_csite.php
/sources/ipdynamiclite/*.*

SETTING UP
----------

Before you can use Dynamic lite, you'll need to make sure you've switched it on from the ACP. If you don't do this, you may
receive PHP errors from index.php.

Simply log into your forums ACP and expand the "System Settings" sub-menu and click on "IPDynamic Lite Set-up".

That's it, you're all done!





#WHMCS & Sirportly Integration

This module allows you to fetch data from your WHMCS installation to display within Sirportly, it also allows clients to view, open and reply to support tickets from within the WHMCS clientarea.

## Prerequisites

**Please ensure you have a backup of both your files and database before attempting to install this module**

This module is tested on WHMCS v5.1

## Installation

To install you need to download all the files from the repo and upload them to:

```
WHMCS_ROOT/modules/addons/sirportly
```

With the exception of `submitticket.php` and `viewticket.php` which needs to be uploaded to the root of your WHMCS installation, overwriting the original WHMCS files. 

Once the files have successfully uploaded navigate to the WHMCS administration area and click (Setup > Addon Modules)

Here you will see a list of modules that have been uploaded, within this list you will see Sirportly. Click the 'Activate' link to begin setting up the module.

`API URL` this is the end point for the API. If you are using the cloud version you should leave this value to the default 'api.sirportly.com' otherwise you should enter the URL of your installation.

`API Token` This must be generated by an account administrator within the Sirportly user interface.

`API Secret` This must be generated by an account administrator within the Sirportly user interface.

`Use SSL` Connect to the API via SSL?

## Customer Data Source

Within WHMCS navigate to (Addons > Sirportly), here you will see a list of columns that you may select that will be passed to Sirportly. To setup the Data Source within Sirportly navigate to the ```Workflow``` section of the admin page and click ```Data Sources``` then the 'Add New Data Source' button.
Here you will be presented a form;

* `Name` Enter a memorable name for the datasource, 'WHMCS' is a good start.
* `URL` Here you need to enter the URL of WHMCS plus /modules/addons/sirportly/api.php For example http://www.example.com/modules/addons/sirportly/api.php 
* `Username` The username for a WHMCS administrator.
* `Password` The password for the above administrator.

## Customer Data Frame

Data frames allow you to easily display additional information about your customers directly in Sirportly.  You can configure these frames to automatically grab HTML from WHMCS using this module.

To setup the Data Frame firstly navigate to the Sirportly addon in WHMCS, and set an **auth key**, this will be used by Sirportly to authenticate itself with your installation.

Next, within Sirportly head to the Data Frames page (Admin > Workflow > Data Frames), hit **Add New Data Frame** and enter the following;

* `Name` - Enter a memorable name for the data frame
* `Url` - Here you need to enter the URL of WHMCS plus /modules/addons/sirportly/frame.php For example ```http://www.example.com/modules/addons/sirportly/frame.php```
* `Auth Key` - Enter the same value as you entered in WHMCS

## Support Integration

The module also allows Sirportly to takeover the support ticket area within the clientarea so all new tickets are submitted to Sirportly, to enable this option navigate to (Addons > Sirportly) within the WHMCS administration area and click the 'Support Tickets' link. You will now be presented with three options;

`Brand` Firstly you need to select the brand.

`New Ticket Status` This is the status of the ticket when it is first opened.

`Default Ticket Priority` The client can still choose the priority when submitting a ticket.

Once all three options are saved all support tickets will be added to Sirportly, **not** WHMCS.

## Single Sign On
'SSO' allows you to integrate your public interface with WHMCS, all you need to do is navigate to your Sirportly administration area > Public Interfaces > *click the link* > SSO

![SSO](http://cloud.atechmedia.com/sirportly/publicinterfacesso.png)

`SSO URL` This is the URL to your WHMCS installation and the following /modules/addons/sirportly/sso.php

The rest of the fields are optional

## Knowledge base Integration
To show a Sirportly knowledge base instead of the built-in version you need to navigate to the WHMCS admin area, Setup > Addon Modules and enter the ID of a knowledge base from within Sirportly in to the field `Knowledge Base ID`
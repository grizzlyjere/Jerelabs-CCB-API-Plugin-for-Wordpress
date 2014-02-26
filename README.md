Jerelabs-CCB-API-Plugin-for-Wordpress
=====================================
Update (2/25/14): I've rewritten part of the plugin to make it more stable and work with the latest version of wordpress.  Please let me know if you have any problems or questions (this is very much an alpha version).  The instructions have been updated below as the short code has changed.

Download the latest version here: Jerelabs CCB 0.5

-----

Church Community Builder (CCB) includes a few options for displaying content from your CCB database on a web page.  Unfortunately this consists of an iframe  which gives you no control over what is displayed or how it's displayed.  While this is great for the novice, it's constraining for those of us who want a seamless look (and jquery doesn't work since it's in an iframe from another domain which you can't access).

CCB does however have a fairly simple web service API that allows you to query for some of the basic information you may need. So I set out to write my own Wordpress plugin to allow CCB information to be displayed on any wordpress site.  This is the first version that "works" so the functionality and admin interface is barebones.  I'd welcome your feedback so I can make this more useful to churches across the country.  It uses XSLT so you can make the output look like almost anything you want.


Initial Configuration

Place the folder "jerelabs-ccb" and it's contents into the /wp-content/plugins folder in your wordpress installation
1. Activate the plugin
2. Obtain a CCB API login.  You add an API user in CCB from the Settings | API menu.
3. Back in wordpress, go to Settings | Jerelabs CCB
4. Fill in the information in the CCB Integration Settings section using the information from the CCP API screen.
5. For now you can skip the rest of the options.  I'll talk about them later

Usage

If you use the built in templates, usage is as follows
Groups:  [ccbgroups group_type='Growth Groups']
  group_type: Display only groups of the type specified (optional)

Calendar of Events:  [ccbevents num_days=1]
  num_days: The number of days of events to display (optional, defaults to 7)

If you run into problems contact me and I'll do my best to help you.

Caching

After you get everything working, I highly recommend enabling caching.  It will save the last API call for 4 hours, instead of calling CCB for every page request.  Leaving the cache off in a production environment is a sure way to cause CCB to disable your API account.

Configuring the Output

You can customize the output of the current templates by modifying the XSLT located in the plugin folder.  Contact me if there is a format that you feel should be included by default

Future Revisions

I plan to add a bulleted list version of the group display if there is a demand for it

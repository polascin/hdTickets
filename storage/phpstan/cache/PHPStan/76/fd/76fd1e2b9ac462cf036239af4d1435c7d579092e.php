<?php declare(strict_types = 1);

// odsl-/var/www/hdtickets/app/Models
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/var/www/hdtickets/app/Models/UserSession.php' => 
    array (
      0 => '06170c4c6dc00e6b9605370ce6409de732e2ec48',
      1 => 
      array (
        0 => 'app\\models\\usersession',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopeactive',
        2 => 'app\\models\\scopeexpired',
        3 => 'app\\models\\scopetrusted',
        4 => 'app\\models\\scopecurrent',
        5 => 'app\\models\\getlocationstringattribute',
        6 => 'app\\models\\getdeviceinfoattribute',
        7 => 'app\\models\\getdeviceiconattribute',
        8 => 'app\\models\\isexpired',
        9 => 'app\\models\\isactive',
        10 => 'app\\models\\gettimesincelastactivityattribute',
        11 => 'app\\models\\getsessiondurationattribute',
        12 => 'app\\models\\markastrusted',
        13 => 'app\\models\\revoke',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/Category.php' => 
    array (
      0 => '3212d1c408973e4e598b9f54a25e9ba5c38a6c03',
      1 => 
      array (
        0 => 'app\\models\\category',
      ),
      2 => 
      array (
        0 => 'app\\models\\getroutekeyname',
        1 => 'app\\models\\parent',
        2 => 'app\\models\\children',
        3 => 'app\\models\\scrapedtickets',
        4 => 'app\\models\\tickets',
        5 => 'app\\models\\ticketsources',
        6 => 'app\\models\\scopeactive',
        7 => 'app\\models\\scoperoot',
        8 => 'app\\models\\scopechild',
        9 => 'app\\models\\scopebyparent',
        10 => 'app\\models\\scopesearch',
        11 => 'app\\models\\scopeordered',
        12 => 'app\\models\\isactive',
        13 => 'app\\models\\isroot',
        14 => 'app\\models\\haschildren',
        15 => 'app\\models\\getfullpathattribute',
        16 => 'app\\models\\getancestors',
        17 => 'app\\models\\getdescendants',
        18 => 'app\\models\\getavailableticketscountattribute',
        19 => 'app\\models\\gettotalscrapedticketscountattribute',
        20 => 'app\\models\\getticketsourcescountattribute',
        21 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/PaymentPlan.php' => 
    array (
      0 => 'e6398eb8401a905076ff15ef356c154cb724b501',
      1 => 
      array (
        0 => 'app\\models\\paymentplan',
      ),
      2 => 
      array (
        0 => 'app\\models\\subscriptions',
        1 => 'app\\models\\activesubscriptions',
        2 => 'app\\models\\scopeactive',
        3 => 'app\\models\\scopeordered',
        4 => 'app\\models\\getformattedpriceattribute',
        5 => 'app\\models\\getmonthlyequivalentattribute',
        6 => 'app\\models\\hasunlimitedtickets',
        7 => 'app\\models\\getfeatureslistattribute',
        8 => 'app\\models\\getdefaultplans',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/TicketSource.php' => 
    array (
      0 => '49e99225e6337b023360cd91e0ffbfaeeafcbcbc',
      1 => 
      array (
        0 => 'app\\models\\ticketsource',
      ),
      2 => 
      array (
        0 => 'app\\models\\getplatforms',
        1 => 'app\\models\\getstatuses',
        2 => 'app\\models\\getplatformnameattribute',
        3 => 'app\\models\\getstatusnameattribute',
        4 => 'app\\models\\isavailable',
        5 => 'app\\models\\scopeactive',
        6 => 'app\\models\\scopeavailable',
        7 => 'app\\models\\scopebyplatform',
        8 => 'app\\models\\scopeupcoming',
        9 => 'app\\models\\scopepast',
        10 => 'app\\models\\scopebycountry',
        11 => 'app\\models\\scopebycurrency',
        12 => 'app\\models\\scopeinpricerange',
        13 => 'app\\models\\category',
        14 => 'app\\models\\getformattedpriceattribute',
        15 => 'app\\models\\getcurrencysymbol',
        16 => 'app\\models\\gettimeuntileventattribute',
        17 => 'app\\models\\getlastcheckedhumanattribute',
        18 => 'app\\models\\getstatusbadgeclassattribute',
        19 => 'app\\models\\isplatformclub',
        20 => 'app\\models\\isplatformvenue',
        21 => 'app\\models\\getcurrencies',
        22 => 'app\\models\\getcountries',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/LoginHistory.php' => 
    array (
      0 => '5cce292b528f0bd6a07307ff711643d71c2b9245',
      1 => 
      array (
        0 => 'app\\models\\loginhistory',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopesuccessful',
        2 => 'app\\models\\scopefailed',
        3 => 'app\\models\\scopesuspicious',
        4 => 'app\\models\\scoperecent',
        5 => 'app\\models\\getlocationstringattribute',
        6 => 'app\\models\\getdeviceinfoattribute',
        7 => 'app\\models\\getrisklevelattribute',
        8 => 'app\\models\\getriskcolorattribute',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/ScrapingStats.php' => 
    array (
      0 => 'c67c636f91857cea73fa1eed621c1cf57d077a07',
      1 => 
      array (
        0 => 'app\\models\\scrapingstats',
      ),
      2 => 
      array (
        0 => 'app\\models\\scopesuccessful',
        1 => 'app\\models\\scopefailed',
        2 => 'app\\models\\scopeplatform',
        3 => 'app\\models\\scopemethod',
        4 => 'app\\models\\scoperecent',
        5 => 'app\\models\\getsuccessrate',
        6 => 'app\\models\\getaverageresponsetime',
        7 => 'app\\models\\getplatformavailability',
        8 => 'app\\models\\geterrorstats',
        9 => 'app\\models\\getselectorstats',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/PurchaseQueue.php' => 
    array (
      0 => '5dfe6bb7213c9efbd16d49e59085974726b079bc',
      1 => 
      array (
        0 => 'app\\models\\purchasequeue',
      ),
      2 => 
      array (
        0 => 'app\\models\\getroutekeyname',
        1 => 'app\\models\\getstatuses',
        2 => 'app\\models\\getpriorities',
        3 => 'app\\models\\scrapedticket',
        4 => 'app\\models\\selectedbyuser',
        5 => 'app\\models\\user',
        6 => 'app\\models\\purchaseattempts',
        7 => 'app\\models\\latestattempt',
        8 => 'app\\models\\scopebystatus',
        9 => 'app\\models\\scopebypriority',
        10 => 'app\\models\\scopereadyforprocessing',
        11 => 'app\\models\\scopehighpriority',
        12 => 'app\\models\\scopeexpired',
        13 => 'app\\models\\isactive',
        14 => 'app\\models\\iscompleted',
        15 => 'app\\models\\isfailed',
        16 => 'app\\models\\iscancelled',
        17 => 'app\\models\\isexpired',
        18 => 'app\\models\\isscheduled',
        19 => 'app\\models\\markasprocessing',
        20 => 'app\\models\\markascompleted',
        21 => 'app\\models\\markasfailed',
        22 => 'app\\models\\cancel',
        23 => 'app\\models\\getstatuscolorattribute',
        24 => 'app\\models\\getprioritycolorattribute',
        25 => 'app\\models\\getsuccessrate',
        26 => 'app\\models\\getestimatedprocessingtime',
        27 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/Ticket.php' => 
    array (
      0 => '333f1cbad0e00e3e5fe1f20d99dee2d68098d682',
      1 => 
      array (
        0 => 'app\\models\\ticket',
      ),
      2 => 
      array (
        0 => 'app\\models\\getroutekeyname',
        1 => 'app\\models\\getstatuses',
        2 => 'app\\models\\getpriorities',
        3 => 'app\\models\\getsources',
        4 => 'app\\models\\user',
        5 => 'app\\models\\requester',
        6 => 'app\\models\\assignedto',
        7 => 'app\\models\\assignee',
        8 => 'app\\models\\category',
        9 => 'app\\models\\scopebystatus',
        10 => 'app\\models\\scopebypriority',
        11 => 'app\\models\\scopebyassignee',
        12 => 'app\\models\\scopebycategory',
        13 => 'app\\models\\scopebyuser',
        14 => 'app\\models\\scopebysource',
        15 => 'app\\models\\scopeopen',
        16 => 'app\\models\\scopeclosed',
        17 => 'app\\models\\scopehighpriority',
        18 => 'app\\models\\scopeoverdue',
        19 => 'app\\models\\scoperecent',
        20 => 'app\\models\\scopeindaterange',
        21 => 'app\\models\\scopesearch',
        22 => 'app\\models\\scopewithtag',
        23 => 'app\\models\\isopen',
        24 => 'app\\models\\isclosed',
        25 => 'app\\models\\isoverdue',
        26 => 'app\\models\\ishighpriority',
        27 => 'app\\models\\getprioritycolorattribute',
        28 => 'app\\models\\getstatuscolorattribute',
        29 => 'app\\models\\getformattedtitleattribute',
        30 => 'app\\models\\resolve',
        31 => 'app\\models\\assignto',
        32 => 'app\\models\\addtag',
        33 => 'app\\models\\removetag',
        34 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserPricePreference.php' => 
    array (
      0 => 'b4d78434f732ef94d4b657da10d62828e0446c3e',
      1 => 
      array (
        0 => 'app\\models\\userpricepreference',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopebysport',
        2 => 'app\\models\\scopebycategory',
        3 => 'app\\models\\scopeactive',
        4 => 'app\\models\\scopewithautopurchase',
        5 => 'app\\models\\scopewithinpricerange',
        6 => 'app\\models\\scopewithemailalerts',
        7 => 'app\\models\\geteventcategories',
        8 => 'app\\models\\getseatpreferences',
        9 => 'app\\models\\getalertfrequencies',
        10 => 'app\\models\\matchesprice',
        11 => 'app\\models\\ispricedropsignificant',
        12 => 'app\\models\\ispriceincreasesignificant',
        13 => 'app\\models\\matchesseatpreferences',
        14 => 'app\\models\\matchessectionpreferences',
        15 => 'app\\models\\shouldautopurchase',
        16 => 'app\\models\\getnotificationsettings',
        17 => 'app\\models\\updatenotificationsettings',
        18 => 'app\\models\\getformattedpricerange',
        19 => 'app\\models\\getaveragetargetprice',
        20 => 'app\\models\\clonefor',
        21 => 'app\\models\\getpricestats',
        22 => 'app\\models\\getsimilarpreferences',
        23 => 'app\\models\\validatepreferencedata',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/TicketPriceHistory.php' => 
    array (
      0 => 'a2cdc8444bc1aedca4f86e089aa8a422802408f7',
      1 => 
      array (
        0 => 'app\\models\\ticketpricehistory',
      ),
      2 => 
      array (
        0 => 'app\\models\\ticket',
        1 => 'app\\models\\scoperecent',
        2 => 'app\\models\\scopebetweendates',
        3 => 'app\\models\\getpricechangeattribute',
        4 => 'app\\models\\getquantitychangeattribute',
        5 => 'app\\models\\getaverageprice',
        6 => 'app\\models\\getpricevolatility',
        7 => 'app\\models\\getpricetrend',
        8 => 'app\\models\\recordprice',
        9 => 'app\\models\\cleanup',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserPreferencePreset.php' => 
    array (
      0 => '91b109712ffa220709ef879e752899d496f7f18e',
      1 => 
      array (
        0 => 'app\\models\\userpreferencepreset',
      ),
      2 => 
      array (
        0 => 'app\\models\\creator',
        1 => 'app\\models\\scopesystempresets',
        2 => 'app\\models\\scopeuserpresets',
        3 => 'app\\models\\scopeactive',
        4 => 'app\\models\\scopeaccessibleto',
        5 => 'app\\models\\getdefaultpresets',
        6 => 'app\\models\\createsystempresets',
        7 => 'app\\models\\createfromuserpreferences',
        8 => 'app\\models\\applytouser',
        9 => 'app\\models\\getaccessiblepresetswithstats',
        10 => 'app\\models\\duplicateforuser',
        11 => 'app\\models\\validatepresetdata',
        12 => 'app\\models\\getsummary',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/AnalyticsDashboard.php' => 
    array (
      0 => 'e097b3b4e4e4f643c744b39503d72e5b595f65d5',
      1 => 
      array (
        0 => 'app\\models\\analyticsdashboard',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopepublic',
        2 => 'app\\models\\scopeaccessibleby',
        3 => 'app\\models\\getdefaultforuser',
        4 => 'app\\models\\createdefaultforuser',
        5 => 'app\\models\\markaccessed',
        6 => 'app\\models\\canaccess',
        7 => 'app\\models\\sharewith',
        8 => 'app\\models\\removesharing',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/PriceVolatilityAnalytics.php' => 
    array (
      0 => 'c170236e2fee1c9bb8b30f75383a912c8772b67e',
      1 => 
      array (
        0 => 'app\\models\\pricevolatilityanalytics',
      ),
      2 => 
      array (
        0 => 'app\\models\\ticket',
        1 => 'app\\models\\scopehighvolatility',
        2 => 'app\\models\\scopetrending',
        3 => 'app\\models\\scoperecent',
        4 => 'app\\models\\getpricerangeattribute',
        5 => 'app\\models\\getvolatilityclassificationattribute',
        6 => 'app\\models\\getformattedvolatilityattribute',
        7 => 'app\\models\\calculateforticket',
        8 => 'app\\models\\generatehourlydata',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/DataExportRequest.php' => 
    array (
      0 => '9f38b43564c468310092dfce36d72a6db168dff4',
      1 => 
      array (
        0 => 'app\\models\\dataexportrequest',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\ispending',
        2 => 'app\\models\\isprocessing',
        3 => 'app\\models\\iscompleted',
        4 => 'app\\models\\hasfailed',
        5 => 'app\\models\\isexpired',
        6 => 'app\\models\\isavailablefordownload',
        7 => 'app\\models\\getdownloadurl',
        8 => 'app\\models\\getformattedfilesizeattribute',
        9 => 'app\\models\\markasprocessing',
        10 => 'app\\models\\markascompleted',
        11 => 'app\\models\\markasfailed',
        12 => 'app\\models\\deletefile',
        13 => 'app\\models\\scopeactive',
        14 => 'app\\models\\scopecompleted',
        15 => 'app\\models\\scopeexpired',
        16 => 'app\\models\\scopeavailable',
        17 => 'app\\models\\getexporttypes',
        18 => 'app\\models\\getformats',
        19 => 'app\\models\\getstatuses',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/ScrapedTicket.php' => 
    array (
      0 => 'e0f18b77c7b05c006fc546d4fe0b2b4528343a7d',
      1 => 
      array (
        0 => 'app\\models\\scrapedticket',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\source',
        2 => 'app\\models\\category',
        3 => 'app\\models\\scopehighdemand',
        4 => 'app\\models\\scopebyplatform',
        5 => 'app\\models\\scopeavailable',
        6 => 'app\\models\\scopeforevent',
        7 => 'app\\models\\scopepricerange',
        8 => 'app\\models\\scoperecent',
        9 => 'app\\models\\scopebystatus',
        10 => 'app\\models\\scopeupcoming',
        11 => 'app\\models\\scopebydaterange',
        12 => 'app\\models\\scopebysport',
        13 => 'app\\models\\scopebyteam',
        14 => 'app\\models\\scopebylocation',
        15 => 'app\\models\\scopefulltextsearch',
        16 => 'app\\models\\scopewithinweek',
        17 => 'app\\models\\scopewithinmonth',
        18 => 'app\\models\\getformattedpriceattribute',
        19 => 'app\\models\\gettotalpriceattribute',
        20 => 'app\\models\\getisrecentattribute',
        21 => 'app\\models\\getplatformdisplaynameattribute',
        22 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/AlertEscalation.php' => 
    array (
      0 => 'caf9b50edc7c4a9cccbbb6306935eb4f96e1833b',
      1 => 
      array (
        0 => 'app\\models\\alertescalation',
      ),
      2 => 
      array (
        0 => 'app\\models\\alert',
        1 => 'app\\models\\user',
        2 => 'app\\models\\scopeactive',
        3 => 'app\\models\\scopefailed',
        4 => 'app\\models\\scopecompleted',
        5 => 'app\\models\\isvalid',
        6 => 'app\\models\\hasexceededmaxattempts',
        7 => 'app\\models\\getprogresspercentage',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/AccountDeletionAuditLog.php' => 
    array (
      0 => '062b358f726dc7a9d8934bb93a377cedd84811c1',
      1 => 
      array (
        0 => 'app\\models\\accountdeletionauditlog',
      ),
      2 => 
      array (
        0 => 'app\\models\\log',
        1 => 'app\\models\\scopeforaction',
        2 => 'app\\models\\scopeforuser',
        3 => 'app\\models\\scoperecent',
        4 => 'app\\models\\getactions',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/PurchaseAttempt.php' => 
    array (
      0 => 'a5d1dc9cd5374e5b1cbc0d125d3c6c0724ba9548',
      1 => 
      array (
        0 => 'app\\models\\purchaseattempt',
      ),
      2 => 
      array (
        0 => 'app\\models\\__construct',
        1 => 'app\\models\\purchasequeue',
        2 => 'app\\models\\scrapedticket',
        3 => 'app\\models\\user',
        4 => 'app\\models\\ticket',
        5 => 'app\\models\\scopebystatus',
        6 => 'app\\models\\scopesuccessful',
        7 => 'app\\models\\scopefailed',
        8 => 'app\\models\\issuccess',
        9 => 'app\\models\\isfailed',
        10 => 'app\\models\\isinprogress',
        11 => 'app\\models\\ispending',
        12 => 'app\\models\\markinprogress',
        13 => 'app\\models\\marksuccessful',
        14 => 'app\\models\\markfailed',
        15 => 'app\\models\\cancel',
        16 => 'app\\models\\settransactionidattribute',
        17 => 'app\\models\\gettransactionidattribute',
        18 => 'app\\models\\setconfirmationnumberattribute',
        19 => 'app\\models\\getconfirmationnumberattribute',
        20 => 'app\\models\\setpurchasedetailsattribute',
        21 => 'app\\models\\getpurchasedetailsattribute',
        22 => 'app\\models\\setresponsedataattribute',
        23 => 'app\\models\\getresponsedataattribute',
        24 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserNotificationSettings.php' => 
    array (
      0 => '5f650a7fce033e12b4e0aa24d42f8db35dd9708e',
      1 => 
      array (
        0 => 'app\\models\\usernotificationsettings',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopeenabled',
        2 => 'app\\models\\scopeforchannel',
        3 => 'app\\models\\isconfigured',
        4 => 'app\\models\\getchannelsettings',
        5 => 'app\\models\\updatechannelsettings',
        6 => 'app\\models\\test',
        7 => 'app\\models\\getsupportedchannels',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserPreference.php' => 
    array (
      0 => '2f9f1bd5ebeca957f597bd272d36ab67ed88ca37',
      1 => 
      array (
        0 => 'app\\models\\userpreference',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopeforkey',
        2 => 'app\\models\\scopeforcategory',
        3 => 'app\\models\\getvalue',
        4 => 'app\\models\\setvalue',
        5 => 'app\\models\\getbycategory',
        6 => 'app\\models\\getdefaultpreferences',
        7 => 'app\\models\\initializedefaults',
        8 => 'app\\models\\validatepreference',
        9 => 'app\\models\\getnotificationpreferences',
        10 => 'app\\models\\getalertpreferences',
        11 => 'app\\models\\updatemultiple',
        12 => 'app\\models\\resettodefaults',
        13 => 'app\\models\\exportpreferences',
        14 => 'app\\models\\importpreferences',
        15 => 'app\\models\\getcategoryforkey',
        16 => 'app\\models\\processvalue',
        17 => 'app\\models\\castvalue',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserFavoriteTeam.php' => 
    array (
      0 => 'aae9db85548dbecd456cd015f9b77bafebefff4f',
      1 => 
      array (
        0 => 'app\\models\\userfavoriteteam',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopebysport',
        2 => 'app\\models\\scopebyleague',
        3 => 'app\\models\\scopebypriority',
        4 => 'app\\models\\scopehighpriority',
        5 => 'app\\models\\scopewithemailalerts',
        6 => 'app\\models\\scopewithpushalerts',
        7 => 'app\\models\\scopesearch',
        8 => 'app\\models\\getfullnameattribute',
        9 => 'app\\models\\generateslug',
        10 => 'app\\models\\setteamslugattribute',
        11 => 'app\\models\\getavailablesports',
        12 => 'app\\models\\getleaguesbysport',
        13 => 'app\\models\\getpopularteams',
        14 => 'app\\models\\matchessearch',
        15 => 'app\\models\\getnotificationsettings',
        16 => 'app\\models\\updatenotificationsettings',
        17 => 'app\\models\\getteamstats',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/WebPushSubscription.php' => 
    array (
      0 => '7d4aefb41732ea8d37a533d6f14f8b9030b51063',
      1 => 
      array (
        0 => 'app\\models\\webpushsubscription',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\markasused',
        2 => 'app\\models\\disable',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/AccountDeletionRequest.php' => 
    array (
      0 => '4d8d2ed261b1d94e0e76f0997a90497b20fb030e',
      1 => 
      array (
        0 => 'app\\models\\accountdeletionrequest',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\ispending',
        2 => 'app\\models\\isconfirmed',
        3 => 'app\\models\\iscancelled',
        4 => 'app\\models\\isexpired',
        5 => 'app\\models\\iscompleted',
        6 => 'app\\models\\isingraceperiod',
        7 => 'app\\models\\isgraceperiodexpired',
        8 => 'app\\models\\getremaininggracetime',
        9 => 'app\\models\\gettimeremainingattribute',
        10 => 'app\\models\\cancel',
        11 => 'app\\models\\confirm',
        12 => 'app\\models\\markcompleted',
        13 => 'app\\models\\markexpired',
        14 => 'app\\models\\scopeactive',
        15 => 'app\\models\\scopeingraceperiod',
        16 => 'app\\models\\scopegraceperiodexpired',
        17 => 'app\\models\\getstatuses',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserFavoriteVenue.php' => 
    array (
      0 => 'f20c3100602e3803a8bde0aa58f9471054cc8315',
      1 => 
      array (
        0 => 'app\\models\\userfavoritevenue',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopebycity',
        2 => 'app\\models\\scopebystateprovince',
        3 => 'app\\models\\scopebycountry',
        4 => 'app\\models\\scopebyvenuetype',
        5 => 'app\\models\\scopebypriority',
        6 => 'app\\models\\scopehighpriority',
        7 => 'app\\models\\scopewithemailalerts',
        8 => 'app\\models\\scopesearch',
        9 => 'app\\models\\scopewithinradius',
        10 => 'app\\models\\getfullnameattribute',
        11 => 'app\\models\\generateslug',
        12 => 'app\\models\\setvenueslugattribute',
        13 => 'app\\models\\getavailablevenuetypes',
        14 => 'app\\models\\getpopularvenues',
        15 => 'app\\models\\matchessearch',
        16 => 'app\\models\\getnotificationsettings',
        17 => 'app\\models\\updatenotificationsettings',
        18 => 'app\\models\\distancefrom',
        19 => 'app\\models\\getcapacitytierattribute',
        20 => 'app\\models\\isoutdoor',
        21 => 'app\\models\\getvenuestats',
        22 => 'app\\models\\getsimilarvenues',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/TicketAlert.php' => 
    array (
      0 => '9f152c919829faf6d6265afb2da935076c577241',
      1 => 
      array (
        0 => 'app\\models\\ticketalert',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\scopeactive',
        2 => 'app\\models\\scopeforuser',
        3 => 'app\\models\\scopebyplatform',
        4 => 'app\\models\\scopeneedscheck',
        5 => 'app\\models\\matchesticket',
        6 => 'app\\models\\incrementmatches',
        7 => 'app\\models\\getformattedmaxpriceattribute',
        8 => 'app\\models\\getlastcheckedattribute',
        9 => 'app\\models\\getplatformdisplaynameattribute',
        10 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/UserSubscription.php' => 
    array (
      0 => '2eb2998d8edb4e45f50134e539efab4a26b6ed36',
      1 => 
      array (
        0 => 'app\\models\\usersubscription',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\paymentplan',
        2 => 'app\\models\\scopeactive',
        3 => 'app\\models\\scopeexpired',
        4 => 'app\\models\\scopetrial',
        5 => 'app\\models\\isactive',
        6 => 'app\\models\\isontrial',
        7 => 'app\\models\\istrialexpired',
        8 => 'app\\models\\isexpired',
        9 => 'app\\models\\getdaysremainingattribute',
        10 => 'app\\models\\gettrialdaysremainingattribute',
        11 => 'app\\models\\cancel',
        12 => 'app\\models\\expire',
        13 => 'app\\models\\activate',
        14 => 'app\\models\\starttrial',
        15 => 'app\\models\\getstatuscolorattribute',
        16 => 'app\\models\\getformattedstatusattribute',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/PriceAlertThreshold.php' => 
    array (
      0 => '2dba09430d8b453ba50e13d0283174eb5bf36c6f',
      1 => 
      array (
        0 => 'app\\models\\pricealertthreshold',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\ticket',
        2 => 'app\\models\\scopeactive',
        3 => 'app\\models\\scopeoftype',
        4 => 'app\\models\\shouldtrigger',
        5 => 'app\\models\\trigger',
        6 => 'app\\models\\getformattedtargetpriceattribute',
        7 => 'app\\models\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/User.php' => 
    array (
      0 => '4e52b804196edc95e28f656793d821c9ec914d64',
      1 => 
      array (
        0 => 'app\\models\\user',
      ),
      2 => 
      array (
        0 => 'app\\models\\__construct',
        1 => 'app\\models\\getroles',
        2 => 'app\\models\\hasrole',
        3 => 'app\\models\\isadmin',
        4 => 'app\\models\\isagent',
        5 => 'app\\models\\iscustomer',
        6 => 'app\\models\\isscraper',
        7 => 'app\\models\\isrootadmin',
        8 => 'app\\models\\canmanageusers',
        9 => 'app\\models\\canselectandpurchasetickets',
        10 => 'app\\models\\canmakepurchasedecisions',
        11 => 'app\\models\\canmanagemonitoring',
        12 => 'app\\models\\canviewscrapingmetrics',
        13 => 'app\\models\\canmanagesystem',
        14 => 'app\\models\\canmanageplatforms',
        15 => 'app\\models\\canaccessfinancials',
        16 => 'app\\models\\canmanageapiaccess',
        17 => 'app\\models\\candeleteanydata',
        18 => 'app\\models\\canaccesssystem',
        19 => 'app\\models\\canlogintoweb',
        20 => 'app\\models\\isscrapingrotationuser',
        21 => 'app\\models\\getfullnameattribute',
        22 => 'app\\models\\scopeuniqueusername',
        23 => 'app\\models\\isusernameunique',
        24 => 'app\\models\\getpermissions',
        25 => 'app\\models\\getlastlogininfo',
        26 => 'app\\models\\getactivitystats',
        27 => 'app\\models\\getaccountcreationinfo',
        28 => 'app\\models\\getuserpermissionsdisplay',
        29 => 'app\\models\\getprofilecompletion',
        30 => 'app\\models\\getprofiledisplay',
        31 => 'app\\models\\getprofilepicturesizes',
        32 => 'app\\models\\getprofilepictureurl',
        33 => 'app\\models\\getnotificationpreferences',
        34 => 'app\\models\\getenhanceduserinfo',
        35 => 'app\\models\\tickets',
        36 => 'app\\models\\assignedtickets',
        37 => 'app\\models\\createdby',
        38 => 'app\\models\\createdusers',
        39 => 'app\\models\\subscriptions',
        40 => 'app\\models\\ticketalerts',
        41 => 'app\\models\\currentsubscription',
        42 => 'app\\models\\activesubscription',
        43 => 'app\\models\\loginhistory',
        44 => 'app\\models\\sessions',
        45 => 'app\\models\\recentloginhistory',
        46 => 'app\\models\\activesessions',
        47 => 'app\\models\\hasactivesubscription',
        48 => 'app\\models\\isontrial',
        49 => 'app\\models\\getcurrentplan',
        50 => 'app\\models\\canaccessfeature',
        51 => 'app\\models\\getremainingticketallowance',
        52 => 'app\\models\\hasreachedticketlimit',
        53 => 'app\\models\\subscribetoplan',
        54 => 'app\\models\\deletionrequests',
        55 => 'app\\models\\currentdeletionrequest',
        56 => 'app\\models\\dataexportrequests',
        57 => 'app\\models\\deletionauditlogs',
        58 => 'app\\models\\hasactivedeletionrequest',
        59 => 'app\\models\\isindeletiongraceperiod',
        60 => 'app\\models\\getcurrentdeletionrequest',
        61 => 'app\\models\\getactivitylogoptions',
        62 => 'app\\models\\getencryptionservice',
        63 => 'app\\models\\encrypt',
        64 => 'app\\models\\decrypt',
        65 => 'app\\models\\getencryptedfields',
        66 => 'app\\models\\casts',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Models/DeletedUser.php' => 
    array (
      0 => '6dc076b1e839e22c5a25a810d60751a0ac979e59',
      1 => 
      array (
        0 => 'app\\models\\deleteduser',
      ),
      2 => 
      array (
        0 => 'app\\models\\isrecoverable',
        1 => 'app\\models\\isrecoveryexpired',
        2 => 'app\\models\\getremainingrecoverytime',
        3 => 'app\\models\\getrecoverytimeremainingattribute',
        4 => 'app\\models\\markrecovered',
        5 => 'app\\models\\scoperecoverable',
        6 => 'app\\models\\scoperecoveryexpired',
        7 => 'app\\models\\scoperecovered',
      ),
      3 => 
      array (
      ),
    ),
  ),
));
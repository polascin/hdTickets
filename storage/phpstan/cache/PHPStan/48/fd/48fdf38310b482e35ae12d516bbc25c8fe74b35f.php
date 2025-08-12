<?php declare(strict_types = 1);

// odsl-/var/www/hdtickets/app/Domain
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/var/www/hdtickets/app/Domain/Shared/Events/DomainEventInterface.php' => 
    array (
      0 => '91fe38b07cbb70089ed9d0873068db232a2e7fff',
      1 => 
      array (
        0 => 'app\\domain\\shared\\events\\domaineventinterface',
      ),
      2 => 
      array (
        0 => 'app\\domain\\shared\\events\\geteventid',
        1 => 'app\\domain\\shared\\events\\geteventtype',
        2 => 'app\\domain\\shared\\events\\getaggregaterootid',
        3 => 'app\\domain\\shared\\events\\getaggregatetype',
        4 => 'app\\domain\\shared\\events\\getoccurredat',
        5 => 'app\\domain\\shared\\events\\getversion',
        6 => 'app\\domain\\shared\\events\\getpayload',
        7 => 'app\\domain\\shared\\events\\getmetadata',
        8 => 'app\\domain\\shared\\events\\withmetadata',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Shared/Events/AbstractDomainEvent.php' => 
    array (
      0 => '6055cdb109a2f625e183ee6d5d9e713bd1ebd615',
      1 => 
      array (
        0 => 'app\\domain\\shared\\events\\abstractdomainevent',
      ),
      2 => 
      array (
        0 => 'app\\domain\\shared\\events\\__construct',
        1 => 'app\\domain\\shared\\events\\geteventid',
        2 => 'app\\domain\\shared\\events\\geteventtype',
        3 => 'app\\domain\\shared\\events\\getoccurredat',
        4 => 'app\\domain\\shared\\events\\getversion',
        5 => 'app\\domain\\shared\\events\\getmetadata',
        6 => 'app\\domain\\shared\\events\\withmetadata',
        7 => 'app\\domain\\shared\\events\\geteventname',
        8 => 'app\\domain\\shared\\events\\toarray',
        9 => 'app\\domain\\shared\\events\\fromarray',
        10 => 'app\\domain\\shared\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/Entities/MonitoredTicket.php' => 
    array (
      0 => '09a8c354ceebaf7c782c214e02911a0764214fee',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\entities\\monitoredticket',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\entities\\__construct',
        1 => 'app\\domain\\ticket\\entities\\getid',
        2 => 'app\\domain\\ticket\\entities\\geteventid',
        3 => 'app\\domain\\ticket\\entities\\getsection',
        4 => 'app\\domain\\ticket\\entities\\getrow',
        5 => 'app\\domain\\ticket\\entities\\getseat',
        6 => 'app\\domain\\ticket\\entities\\getprice',
        7 => 'app\\domain\\ticket\\entities\\getavailabilitystatus',
        8 => 'app\\domain\\ticket\\entities\\getsource',
        9 => 'app\\domain\\ticket\\entities\\getdescription',
        10 => 'app\\domain\\ticket\\entities\\getlastmonitoredat',
        11 => 'app\\domain\\ticket\\entities\\getcreatedat',
        12 => 'app\\domain\\ticket\\entities\\getupdatedat',
        13 => 'app\\domain\\ticket\\entities\\updateprice',
        14 => 'app\\domain\\ticket\\entities\\updateavailability',
        15 => 'app\\domain\\ticket\\entities\\updatemonitoringtimestamp',
        16 => 'app\\domain\\ticket\\entities\\isavailable',
        17 => 'app\\domain\\ticket\\entities\\isfromofficialsource',
        18 => 'app\\domain\\ticket\\entities\\getlocationdescription',
        19 => 'app\\domain\\ticket\\entities\\getdomainevents',
        20 => 'app\\domain\\ticket\\entities\\cleardomainevents',
        21 => 'app\\domain\\ticket\\entities\\validate',
        22 => 'app\\domain\\ticket\\entities\\recorddomainevent',
        23 => 'app\\domain\\ticket\\entities\\equals',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/ValueObjects/TicketId.php' => 
    array (
      0 => 'd168cb1d9962ef71d8d3a3805681f9d8082b40eb',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\ticketid',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\__construct',
        1 => 'app\\domain\\ticket\\valueobjects\\value',
        2 => 'app\\domain\\ticket\\valueobjects\\equals',
        3 => 'app\\domain\\ticket\\valueobjects\\validate',
        4 => 'app\\domain\\ticket\\valueobjects\\__tostring',
        5 => 'app\\domain\\ticket\\valueobjects\\fromstring',
        6 => 'app\\domain\\ticket\\valueobjects\\generate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/ValueObjects/PlatformSource.php' => 
    array (
      0 => '9522f0243364eff088a4fc9aacc15c7ce4c75779',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\platformsource',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\__construct',
        1 => 'app\\domain\\ticket\\valueobjects\\platform',
        2 => 'app\\domain\\ticket\\valueobjects\\url',
        3 => 'app\\domain\\ticket\\valueobjects\\displayname',
        4 => 'app\\domain\\ticket\\valueobjects\\isofficial',
        5 => 'app\\domain\\ticket\\valueobjects\\isreseller',
        6 => 'app\\domain\\ticket\\valueobjects\\equals',
        7 => 'app\\domain\\ticket\\valueobjects\\validplatforms',
        8 => 'app\\domain\\ticket\\valueobjects\\validate',
        9 => 'app\\domain\\ticket\\valueobjects\\__tostring',
        10 => 'app\\domain\\ticket\\valueobjects\\ticketmaster',
        11 => 'app\\domain\\ticket\\valueobjects\\stubhub',
        12 => 'app\\domain\\ticket\\valueobjects\\viagogo',
        13 => 'app\\domain\\ticket\\valueobjects\\seetickets',
        14 => 'app\\domain\\ticket\\valueobjects\\officialvenue',
        15 => 'app\\domain\\ticket\\valueobjects\\fromstring',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/ValueObjects/Price.php' => 
    array (
      0 => '9c4dda27bd9881e7082989d7f72b894894dc64bc',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\price',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\__construct',
        1 => 'app\\domain\\ticket\\valueobjects\\amount',
        2 => 'app\\domain\\ticket\\valueobjects\\currency',
        3 => 'app\\domain\\ticket\\valueobjects\\formatted',
        4 => 'app\\domain\\ticket\\valueobjects\\equals',
        5 => 'app\\domain\\ticket\\valueobjects\\isgreaterthan',
        6 => 'app\\domain\\ticket\\valueobjects\\islessthan',
        7 => 'app\\domain\\ticket\\valueobjects\\add',
        8 => 'app\\domain\\ticket\\valueobjects\\subtract',
        9 => 'app\\domain\\ticket\\valueobjects\\percentage',
        10 => 'app\\domain\\ticket\\valueobjects\\validate',
        11 => 'app\\domain\\ticket\\valueobjects\\__tostring',
        12 => 'app\\domain\\ticket\\valueobjects\\gbp',
        13 => 'app\\domain\\ticket\\valueobjects\\usd',
        14 => 'app\\domain\\ticket\\valueobjects\\eur',
        15 => 'app\\domain\\ticket\\valueobjects\\fromstring',
        16 => 'app\\domain\\ticket\\valueobjects\\zero',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/ValueObjects/AvailabilityStatus.php' => 
    array (
      0 => 'c251a27b5ad9653196983c05e7132c90ca13ce30',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\availabilitystatus',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\valueobjects\\__construct',
        1 => 'app\\domain\\ticket\\valueobjects\\value',
        2 => 'app\\domain\\ticket\\valueobjects\\isavailable',
        3 => 'app\\domain\\ticket\\valueobjects\\islimited',
        4 => 'app\\domain\\ticket\\valueobjects\\issoldout',
        5 => 'app\\domain\\ticket\\valueobjects\\isonsalesoon',
        6 => 'app\\domain\\ticket\\valueobjects\\isunknown',
        7 => 'app\\domain\\ticket\\valueobjects\\canpurchase',
        8 => 'app\\domain\\ticket\\valueobjects\\displayname',
        9 => 'app\\domain\\ticket\\valueobjects\\equals',
        10 => 'app\\domain\\ticket\\valueobjects\\validstatuses',
        11 => 'app\\domain\\ticket\\valueobjects\\validate',
        12 => 'app\\domain\\ticket\\valueobjects\\__tostring',
        13 => 'app\\domain\\ticket\\valueobjects\\available',
        14 => 'app\\domain\\ticket\\valueobjects\\limited',
        15 => 'app\\domain\\ticket\\valueobjects\\soldout',
        16 => 'app\\domain\\ticket\\valueobjects\\onsalesoon',
        17 => 'app\\domain\\ticket\\valueobjects\\unknown',
        18 => 'app\\domain\\ticket\\valueobjects\\fromstring',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/Events/TicketAvailabilityChanged.php' => 
    array (
      0 => 'a4b40bd6b2b92d4e4a79aae691d81bdb02ca6ac7',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\events\\ticketavailabilitychanged',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\events\\__construct',
        1 => 'app\\domain\\ticket\\events\\getaggregaterootid',
        2 => 'app\\domain\\ticket\\events\\getaggregatetype',
        3 => 'app\\domain\\ticket\\events\\getpayload',
        4 => 'app\\domain\\ticket\\events\\populatefrompayload',
        5 => 'app\\domain\\ticket\\events\\becameavailable',
        6 => 'app\\domain\\ticket\\events\\becameunavailable',
        7 => 'app\\domain\\ticket\\events\\soldout',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/Events/TicketDiscovered.php' => 
    array (
      0 => '1b60a2974098f352be1b58f5f7471ce8a0183e3f',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\events\\ticketdiscovered',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\events\\__construct',
        1 => 'app\\domain\\ticket\\events\\getaggregaterootid',
        2 => 'app\\domain\\ticket\\events\\getaggregatetype',
        3 => 'app\\domain\\ticket\\events\\getpayload',
        4 => 'app\\domain\\ticket\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/Events/TicketSoldOut.php' => 
    array (
      0 => '9df6aea11f137f5c0319f09d61e6290390644001',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\events\\ticketsoldout',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\events\\__construct',
        1 => 'app\\domain\\ticket\\events\\getaggregaterootid',
        2 => 'app\\domain\\ticket\\events\\getaggregatetype',
        3 => 'app\\domain\\ticket\\events\\getpayload',
        4 => 'app\\domain\\ticket\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Ticket/Events/TicketPriceChanged.php' => 
    array (
      0 => '75b853cdde8eaec4d355232c3cbc0fed848afaf0',
      1 => 
      array (
        0 => 'app\\domain\\ticket\\events\\ticketpricechanged',
      ),
      2 => 
      array (
        0 => 'app\\domain\\ticket\\events\\__construct',
        1 => 'app\\domain\\ticket\\events\\getaggregaterootid',
        2 => 'app\\domain\\ticket\\events\\getaggregatetype',
        3 => 'app\\domain\\ticket\\events\\getpayload',
        4 => 'app\\domain\\ticket\\events\\populatefrompayload',
        5 => 'app\\domain\\ticket\\events\\getpricechange',
        6 => 'app\\domain\\ticket\\events\\isincrease',
        7 => 'app\\domain\\ticket\\events\\isdecrease',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/System/Events/ScrapingJobStarted.php' => 
    array (
      0 => '11a01c9006440b9b190fdbc9003ba4e95ea8cb17',
      1 => 
      array (
        0 => 'app\\domain\\system\\events\\scrapingjobstarted',
      ),
      2 => 
      array (
        0 => 'app\\domain\\system\\events\\__construct',
        1 => 'app\\domain\\system\\events\\getaggregaterootid',
        2 => 'app\\domain\\system\\events\\getaggregatetype',
        3 => 'app\\domain\\system\\events\\getpayload',
        4 => 'app\\domain\\system\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Purchase/ValueObjects/PurchaseStatus.php' => 
    array (
      0 => '382f5065ba2e4f256d211df68093a651dfd8be07',
      1 => 
      array (
        0 => 'app\\domain\\purchase\\valueobjects\\purchasestatus',
      ),
      2 => 
      array (
        0 => 'app\\domain\\purchase\\valueobjects\\__construct',
        1 => 'app\\domain\\purchase\\valueobjects\\value',
        2 => 'app\\domain\\purchase\\valueobjects\\ispending',
        3 => 'app\\domain\\purchase\\valueobjects\\isqueued',
        4 => 'app\\domain\\purchase\\valueobjects\\isprocessing',
        5 => 'app\\domain\\purchase\\valueobjects\\iscompleted',
        6 => 'app\\domain\\purchase\\valueobjects\\isfailed',
        7 => 'app\\domain\\purchase\\valueobjects\\iscancelled',
        8 => 'app\\domain\\purchase\\valueobjects\\isrefunded',
        9 => 'app\\domain\\purchase\\valueobjects\\cancancel',
        10 => 'app\\domain\\purchase\\valueobjects\\canrefund',
        11 => 'app\\domain\\purchase\\valueobjects\\isactive',
        12 => 'app\\domain\\purchase\\valueobjects\\isfinal',
        13 => 'app\\domain\\purchase\\valueobjects\\displayname',
        14 => 'app\\domain\\purchase\\valueobjects\\equals',
        15 => 'app\\domain\\purchase\\valueobjects\\validstatuses',
        16 => 'app\\domain\\purchase\\valueobjects\\validate',
        17 => 'app\\domain\\purchase\\valueobjects\\__tostring',
        18 => 'app\\domain\\purchase\\valueobjects\\pending',
        19 => 'app\\domain\\purchase\\valueobjects\\queued',
        20 => 'app\\domain\\purchase\\valueobjects\\processing',
        21 => 'app\\domain\\purchase\\valueobjects\\completed',
        22 => 'app\\domain\\purchase\\valueobjects\\failed',
        23 => 'app\\domain\\purchase\\valueobjects\\cancelled',
        24 => 'app\\domain\\purchase\\valueobjects\\refunded',
        25 => 'app\\domain\\purchase\\valueobjects\\fromstring',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Purchase/ValueObjects/PurchaseId.php' => 
    array (
      0 => '0dd45a5b2c827088c6b1e266b06a8f7e6a169105',
      1 => 
      array (
        0 => 'app\\domain\\purchase\\valueobjects\\purchaseid',
      ),
      2 => 
      array (
        0 => 'app\\domain\\purchase\\valueobjects\\__construct',
        1 => 'app\\domain\\purchase\\valueobjects\\value',
        2 => 'app\\domain\\purchase\\valueobjects\\equals',
        3 => 'app\\domain\\purchase\\valueobjects\\validate',
        4 => 'app\\domain\\purchase\\valueobjects\\__tostring',
        5 => 'app\\domain\\purchase\\valueobjects\\fromstring',
        6 => 'app\\domain\\purchase\\valueobjects\\generate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Purchase/Events/PurchaseCompleted.php' => 
    array (
      0 => '1ceb6b687966de244a2578073e50beb5987f2b82',
      1 => 
      array (
        0 => 'app\\domain\\purchase\\events\\purchasecompleted',
      ),
      2 => 
      array (
        0 => 'app\\domain\\purchase\\events\\__construct',
        1 => 'app\\domain\\purchase\\events\\getaggregaterootid',
        2 => 'app\\domain\\purchase\\events\\getaggregatetype',
        3 => 'app\\domain\\purchase\\events\\getpayload',
        4 => 'app\\domain\\purchase\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Purchase/Events/PaymentProcessed.php' => 
    array (
      0 => '382f24d1db6081544a155250a6ff1a127c719e53',
      1 => 
      array (
        0 => 'app\\domain\\purchase\\events\\paymentprocessed',
      ),
      2 => 
      array (
        0 => 'app\\domain\\purchase\\events\\__construct',
        1 => 'app\\domain\\purchase\\events\\getaggregaterootid',
        2 => 'app\\domain\\purchase\\events\\getaggregatetype',
        3 => 'app\\domain\\purchase\\events\\getpayload',
        4 => 'app\\domain\\purchase\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Purchase/Events/PurchaseFailed.php' => 
    array (
      0 => '560c79e88d02c1979a1a968ab6fa8e41b362c0e6',
      1 => 
      array (
        0 => 'app\\domain\\purchase\\events\\purchasefailed',
      ),
      2 => 
      array (
        0 => 'app\\domain\\purchase\\events\\__construct',
        1 => 'app\\domain\\purchase\\events\\getaggregaterootid',
        2 => 'app\\domain\\purchase\\events\\getaggregatetype',
        3 => 'app\\domain\\purchase\\events\\getpayload',
        4 => 'app\\domain\\purchase\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Purchase/Events/PurchaseInitiated.php' => 
    array (
      0 => 'a637432b710085cfa788bd790f16414c9b8ff780',
      1 => 
      array (
        0 => 'app\\domain\\purchase\\events\\purchaseinitiated',
      ),
      2 => 
      array (
        0 => 'app\\domain\\purchase\\events\\__construct',
        1 => 'app\\domain\\purchase\\events\\getaggregaterootid',
        2 => 'app\\domain\\purchase\\events\\getaggregatetype',
        3 => 'app\\domain\\purchase\\events\\getpayload',
        4 => 'app\\domain\\purchase\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Monitoring/Events/MonitoringStarted.php' => 
    array (
      0 => '14593aa9dbed0b9d115c42897c80bb3f44130c78',
      1 => 
      array (
        0 => 'app\\domain\\monitoring\\events\\monitoringstarted',
      ),
      2 => 
      array (
        0 => 'app\\domain\\monitoring\\events\\__construct',
        1 => 'app\\domain\\monitoring\\events\\getaggregaterootid',
        2 => 'app\\domain\\monitoring\\events\\getaggregatetype',
        3 => 'app\\domain\\monitoring\\events\\getpayload',
        4 => 'app\\domain\\monitoring\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Monitoring/Events/MonitoringStopped.php' => 
    array (
      0 => 'c0c87f65ce1088ca54a5dda8ce74853c271e8b02',
      1 => 
      array (
        0 => 'app\\domain\\monitoring\\events\\monitoringstopped',
      ),
      2 => 
      array (
        0 => 'app\\domain\\monitoring\\events\\__construct',
        1 => 'app\\domain\\monitoring\\events\\getaggregaterootid',
        2 => 'app\\domain\\monitoring\\events\\getaggregatetype',
        3 => 'app\\domain\\monitoring\\events\\getpayload',
        4 => 'app\\domain\\monitoring\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Monitoring/Events/AlertTriggered.php' => 
    array (
      0 => 'd7e8b7fa3812aefd2dc1ad11dd20ebdf098303c0',
      1 => 
      array (
        0 => 'app\\domain\\monitoring\\events\\alerttriggered',
      ),
      2 => 
      array (
        0 => 'app\\domain\\monitoring\\events\\__construct',
        1 => 'app\\domain\\monitoring\\events\\getaggregaterootid',
        2 => 'app\\domain\\monitoring\\events\\getaggregatetype',
        3 => 'app\\domain\\monitoring\\events\\getpayload',
        4 => 'app\\domain\\monitoring\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Entities/SportsEvent.php' => 
    array (
      0 => '6bba41c63d3879e0cc173e268d4afe59f898e3f8',
      1 => 
      array (
        0 => 'app\\domain\\event\\entities\\sportsevent',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\entities\\__construct',
        1 => 'app\\domain\\event\\entities\\getid',
        2 => 'app\\domain\\event\\entities\\getname',
        3 => 'app\\domain\\event\\entities\\getcategory',
        4 => 'app\\domain\\event\\entities\\geteventdate',
        5 => 'app\\domain\\event\\entities\\getvenue',
        6 => 'app\\domain\\event\\entities\\gethometeam',
        7 => 'app\\domain\\event\\entities\\getawayteam',
        8 => 'app\\domain\\event\\entities\\getcompetition',
        9 => 'app\\domain\\event\\entities\\ishighdemand',
        10 => 'app\\domain\\event\\entities\\getcreatedat',
        11 => 'app\\domain\\event\\entities\\getupdatedat',
        12 => 'app\\domain\\event\\entities\\updatedetails',
        13 => 'app\\domain\\event\\entities\\markashighdemand',
        14 => 'app\\domain\\event\\entities\\unmarkashighdemand',
        15 => 'app\\domain\\event\\entities\\isupcoming',
        16 => 'app\\domain\\event\\entities\\ispast',
        17 => 'app\\domain\\event\\entities\\getdisplayname',
        18 => 'app\\domain\\event\\entities\\getdomainevents',
        19 => 'app\\domain\\event\\entities\\cleardomainevents',
        20 => 'app\\domain\\event\\entities\\recorddomainevent',
        21 => 'app\\domain\\event\\entities\\equals',
        22 => 'app\\domain\\event\\entities\\validate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Services/EventManagementService.php' => 
    array (
      0 => '797e521ca0f9793aab629411af154aca63a6002c',
      1 => 
      array (
        0 => 'app\\domain\\event\\services\\eventmanagementservice',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\services\\__construct',
        1 => 'app\\domain\\event\\services\\createsportsevent',
        2 => 'app\\domain\\event\\services\\updateeventdetails',
        3 => 'app\\domain\\event\\services\\markeventashighdemand',
        4 => 'app\\domain\\event\\services\\unmarkeventashighdemand',
        5 => 'app\\domain\\event\\services\\detecthighdemandevents',
        6 => 'app\\domain\\event\\services\\findeventsbyfilters',
        7 => 'app\\domain\\event\\services\\islikelyhighdemand',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/ValueObjects/Venue.php' => 
    array (
      0 => '64d9283b5035154bb465cd35ee4700c8d516b43e',
      1 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\venue',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\__construct',
        1 => 'app\\domain\\event\\valueobjects\\name',
        2 => 'app\\domain\\event\\valueobjects\\city',
        3 => 'app\\domain\\event\\valueobjects\\country',
        4 => 'app\\domain\\event\\valueobjects\\address',
        5 => 'app\\domain\\event\\valueobjects\\capacity',
        6 => 'app\\domain\\event\\valueobjects\\fullname',
        7 => 'app\\domain\\event\\valueobjects\\equals',
        8 => 'app\\domain\\event\\valueobjects\\validate',
        9 => 'app\\domain\\event\\valueobjects\\__tostring',
        10 => 'app\\domain\\event\\valueobjects\\create',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/ValueObjects/EventId.php' => 
    array (
      0 => '90968af761da966124d268d0b36b80f441f13cb1',
      1 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\eventid',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\__construct',
        1 => 'app\\domain\\event\\valueobjects\\value',
        2 => 'app\\domain\\event\\valueobjects\\equals',
        3 => 'app\\domain\\event\\valueobjects\\validate',
        4 => 'app\\domain\\event\\valueobjects\\__tostring',
        5 => 'app\\domain\\event\\valueobjects\\fromstring',
        6 => 'app\\domain\\event\\valueobjects\\generate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/ValueObjects/SportCategory.php' => 
    array (
      0 => 'bf1ffb29fba62debcbf345d1373228787fccc935',
      1 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\sportcategory',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\__construct',
        1 => 'app\\domain\\event\\valueobjects\\value',
        2 => 'app\\domain\\event\\valueobjects\\displayname',
        3 => 'app\\domain\\event\\valueobjects\\isteamsport',
        4 => 'app\\domain\\event\\valueobjects\\equals',
        5 => 'app\\domain\\event\\valueobjects\\validcategories',
        6 => 'app\\domain\\event\\valueobjects\\validate',
        7 => 'app\\domain\\event\\valueobjects\\__tostring',
        8 => 'app\\domain\\event\\valueobjects\\fromstring',
        9 => 'app\\domain\\event\\valueobjects\\football',
        10 => 'app\\domain\\event\\valueobjects\\basketball',
        11 => 'app\\domain\\event\\valueobjects\\tennis',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/ValueObjects/EventDate.php' => 
    array (
      0 => 'db5ec6abaf3dcc82bb3961ad254090ed56061d32',
      1 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\eventdate',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\valueobjects\\__construct',
        1 => 'app\\domain\\event\\valueobjects\\value',
        2 => 'app\\domain\\event\\valueobjects\\format',
        3 => 'app\\domain\\event\\valueobjects\\isupcoming',
        4 => 'app\\domain\\event\\valueobjects\\ispast',
        5 => 'app\\domain\\event\\valueobjects\\equals',
        6 => 'app\\domain\\event\\valueobjects\\validate',
        7 => 'app\\domain\\event\\valueobjects\\__tostring',
        8 => 'app\\domain\\event\\valueobjects\\fromstring',
        9 => 'app\\domain\\event\\valueobjects\\now',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Events/EventAddedToSchedule.php' => 
    array (
      0 => '53867a83d51c2c5d60b4c2af36788c2c281db7a4',
      1 => 
      array (
        0 => 'app\\domain\\event\\events\\eventaddedtoschedule',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\events\\__construct',
        1 => 'app\\domain\\event\\events\\getaggregaterootid',
        2 => 'app\\domain\\event\\events\\getaggregatetype',
        3 => 'app\\domain\\event\\events\\getpayload',
        4 => 'app\\domain\\event\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Events/SportEventUpdated.php' => 
    array (
      0 => 'e98dad07d0464886459d7c103c7a7182c46ef82a',
      1 => 
      array (
        0 => 'app\\domain\\event\\events\\sporteventupdated',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\events\\__construct',
        1 => 'app\\domain\\event\\events\\getaggregaterootid',
        2 => 'app\\domain\\event\\events\\getaggregatetype',
        3 => 'app\\domain\\event\\events\\getpayload',
        4 => 'app\\domain\\event\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Events/SportEventCreated.php' => 
    array (
      0 => '4ed6138e7ffec05e5404353c2fd1fe8743f86dbd',
      1 => 
      array (
        0 => 'app\\domain\\event\\events\\sporteventcreated',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\events\\__construct',
        1 => 'app\\domain\\event\\events\\create',
        2 => 'app\\domain\\event\\events\\getaggregaterootid',
        3 => 'app\\domain\\event\\events\\getaggregatetype',
        4 => 'app\\domain\\event\\events\\getpayload',
        5 => 'app\\domain\\event\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Events/EventRemovedFromSchedule.php' => 
    array (
      0 => '9bd97fda4ff1c238a9256914384b2014c7670b2a',
      1 => 
      array (
        0 => 'app\\domain\\event\\events\\eventremovedfromschedule',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\events\\__construct',
        1 => 'app\\domain\\event\\events\\getaggregaterootid',
        2 => 'app\\domain\\event\\events\\getaggregatetype',
        3 => 'app\\domain\\event\\events\\getpayload',
        4 => 'app\\domain\\event\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Events/SportEventMarkedAsHighDemand.php' => 
    array (
      0 => 'c9f8331ae2f0e445620e4f79b7510c4e359b935f',
      1 => 
      array (
        0 => 'app\\domain\\event\\events\\sporteventmarkedashighdemand',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\events\\__construct',
        1 => 'app\\domain\\event\\events\\getaggregaterootid',
        2 => 'app\\domain\\event\\events\\getaggregatetype',
        3 => 'app\\domain\\event\\events\\getpayload',
        4 => 'app\\domain\\event\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Events/SportEventUnmarkedAsHighDemand.php' => 
    array (
      0 => 'd25f0feeb3e78d5b3112add2b9bc8f4fa8adc663',
      1 => 
      array (
        0 => 'app\\domain\\event\\events\\sporteventunmarkedashighdemand',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\events\\__construct',
        1 => 'app\\domain\\event\\events\\getaggregaterootid',
        2 => 'app\\domain\\event\\events\\getaggregatetype',
        3 => 'app\\domain\\event\\events\\getpayload',
        4 => 'app\\domain\\event\\events\\populatefrompayload',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Repositories/EventScheduleRepositoryInterface.php' => 
    array (
      0 => '0543906dc063c0cd5ad9d6214a7c52e2a14d8370',
      1 => 
      array (
        0 => 'app\\domain\\event\\repositories\\eventschedulerepositoryinterface',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\repositories\\save',
        1 => 'app\\domain\\event\\repositories\\findbydate',
        2 => 'app\\domain\\event\\repositories\\findbydaterange',
        3 => 'app\\domain\\event\\repositories\\findbyvenue',
        4 => 'app\\domain\\event\\repositories\\findconflictingschedules',
        5 => 'app\\domain\\event\\repositories\\delete',
        6 => 'app\\domain\\event\\repositories\\exists',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Repositories/SportsEventRepositoryInterface.php' => 
    array (
      0 => '32b9d4238f7bf3ecbcc7ee76eeb3b4a3c57eca5b',
      1 => 
      array (
        0 => 'app\\domain\\event\\repositories\\sportseventrepositoryinterface',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\repositories\\save',
        1 => 'app\\domain\\event\\repositories\\findbyid',
        2 => 'app\\domain\\event\\repositories\\findbyname',
        3 => 'app\\domain\\event\\repositories\\findbycategory',
        4 => 'app\\domain\\event\\repositories\\findupcoming',
        5 => 'app\\domain\\event\\repositories\\findbydaterange',
        6 => 'app\\domain\\event\\repositories\\findhighdemandevents',
        7 => 'app\\domain\\event\\repositories\\findbyvenue',
        8 => 'app\\domain\\event\\repositories\\findbyteam',
        9 => 'app\\domain\\event\\repositories\\findbycompetition',
        10 => 'app\\domain\\event\\repositories\\findconflictingevents',
        11 => 'app\\domain\\event\\repositories\\delete',
        12 => 'app\\domain\\event\\repositories\\exists',
        13 => 'app\\domain\\event\\repositories\\findwithfilters',
        14 => 'app\\domain\\event\\repositories\\countwithfilters',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/app/Domain/Event/Aggregates/EventSchedule.php' => 
    array (
      0 => 'abccf8fdfeb48119dd3111a73630710badfc3671',
      1 => 
      array (
        0 => 'app\\domain\\event\\aggregates\\eventschedule',
      ),
      2 => 
      array (
        0 => 'app\\domain\\event\\aggregates\\__construct',
        1 => 'app\\domain\\event\\aggregates\\addevent',
        2 => 'app\\domain\\event\\aggregates\\removeevent',
        3 => 'app\\domain\\event\\aggregates\\getevent',
        4 => 'app\\domain\\event\\aggregates\\getallevents',
        5 => 'app\\domain\\event\\aggregates\\geteventsbycategory',
        6 => 'app\\domain\\event\\aggregates\\getupcomingevents',
        7 => 'app\\domain\\event\\aggregates\\gethighdemandevents',
        8 => 'app\\domain\\event\\aggregates\\hasconflicts',
        9 => 'app\\domain\\event\\aggregates\\getconflictingevents',
        10 => 'app\\domain\\event\\aggregates\\getscheduledate',
        11 => 'app\\domain\\event\\aggregates\\getvenue',
        12 => 'app\\domain\\event\\aggregates\\geteventcount',
        13 => 'app\\domain\\event\\aggregates\\isempty',
        14 => 'app\\domain\\event\\aggregates\\getdomainevents',
        15 => 'app\\domain\\event\\aggregates\\cleardomainevents',
        16 => 'app\\domain\\event\\aggregates\\validateeventdate',
        17 => 'app\\domain\\event\\aggregates\\recorddomainevent',
      ),
      3 => 
      array (
      ),
    ),
  ),
));
<?php declare(strict_types = 1);

// odsl-/var/www/hdtickets/app/Domain/Event
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
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
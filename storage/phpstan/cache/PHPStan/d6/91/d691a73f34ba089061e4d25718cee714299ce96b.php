<?php declare(strict_types = 1);

// odsl-/var/www/hdtickets/app/Domain/Purchase
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
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
  ),
));
<?php
$CONFIG = array (
  'instanceid' => 'ocxsedduus1t',
  'passwordsalt' => '5FArVurb1wwS2DRUp1qXlpmOsHtzZL',
  'secret' => 'wUTa7y0ubVtETvdfb+NfoYzfFd6wXTlH4M4HAMlc698V5s6f',
  'trusted_domains' => 
  array (
    0 => '192.168.74.132',
  ),
  'enable_previews' => false,
  'datadirectory' => '/var/www/owncloud/data',
  'overwrite.cli.url' => 'http://192.168.74.132/owncloud',
  'dbtype' => 'mysql',
  'version' => '9.1.1.3',
  'dbname' => 'owncloud',
  'dbhost' => 'localhost',
  'dbtableprefix' => 'oc_',
  'dbuser' => 'oc_yf',
  'dbpassword' => 'gUpLFIruklZratHOqUEdTMSw+5X/fX',
  'logtimezone' => 'UTC',
  'installed' => true,
  'integrity.check.disabled' => true,
   'objectstore'=> array (
    'class' => 'OC\\Files\\ObjectStore\\CephS3',
    'arguments' => 
    array (
      'bucket' => 'owncloud',
      'autocreate' => true,
      'version' => '2006-03-01',
      'region' => '',
      'key' => 'E60Z7V7OW9Y1U8WFI9T3',
      'secret' => 'yf',
      'endpoint' => 'http://192.168.74.128:80/',
      'PathStyle' => true,
    ),
  ),
);

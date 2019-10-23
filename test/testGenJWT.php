<?php
  // TODO: add unit tests
  echo "Testing Verifying\n";
  require_once '../src/YufuSDK.php';

  $YufuSDK = new YufuSDK(
    "yufu",
    "idp-07b40589-a585-40ea-9e86-979f3c652b56",
    "../test/private_key.pem"
  );
  echo "\nTesting Generating\n";
  echo $YufuSDK->generateIDPUrl(array('sub' => 'VEGA'));

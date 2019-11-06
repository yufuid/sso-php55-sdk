<?php
echo "Testing Verifying\n";
require_once '../src/YufuSDK.php';
$YufuSDK = new YufuSDK(
    "tn-yufu",
    "yufuid.com/ai-test",
    null,
    "../test/public_key.pem"
);
$JWT = $YufuSDK->verify("eyJraWQiOiJhaS10ZXN0OnNzbyIsImFsZyI6IlJTMjU2In0.eyJhcHBJbnN0YW5jZUlkIjoiYWktdGVzdCIsImF1ZCI6ImFpLXRlc3QiLCJzdWIiOiJ5dW56aGFuZyIsInByb3RvY29sIjoiIiwidG50X2lkIjoidG4teXVmdSIsInNjb3BlIjoic3NvIiwiaXNzIjoieXVmdWlkLmNvbS9haS10ZXN0IiwibmFtZSI6Inl1bnpoYW5nIiwiZXhwIjoyNTcxODMwMjE1LCJpYXQiOjE1NzE4Mjk2MTV9.a3xd0z5m-DT2WY1OLR9GJiMuLEU6ihmJlcBIoITjPJDHMAqTmj7nQ0DW9YXFV8NLQLnvpec1LZHNtJjO7VHFJnv3_B6XB6U5qCarak-SbfszJ-YWsznp4zD0RwJq-3dNJQrmAEeT700aZ3-7SB5uPkp_mD-soJbNH0BPeN0uSEPJc0o3oCrNoLfni-E_dxVnFsme5C8hZ9qfrF8rovP1DhoU-FpgQS7BbvtO28xdBOBaT21rvCDrCJMqU2uUW4fmhV_Vev-lpz-UlfpYKyOLTMCgB2Gv2JnLnT832rz71DaFIXcR0IYnszeuJVMkulPYGrloe894fls0o5qblapsBg");
assert( $JWT->sub == 'yunzhang');
assert($JWT->iss == 'yufuid.com/ai-test');
assert($JWT->tnt_id == 'tn-yufu');
echo "\n";


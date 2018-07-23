PHP 5.5
==============================================

更多语言支持请前往：http://demoforyufu.readthedocs.io/zh_CN/latest/

玉符SDK集成了签署和验证JWT令牌的功能，使得身份提供者（IDP）和服务提供者（SP）只需要用很少的代码就可以轻松将玉符提供的单点登录和其他功能集成到现有的服务之中。

--------------

使用SDK之前
-----------

- 作为身份提供者（IDP），可以使用玉符SDK为用户生成并签署JWT令牌，并送到玉符服务器上认证并登录到第三方服务提供商。
  需从玉符注册并获取相应的身份提供者ID，并生成pem格式的公私钥文件。
- 作为服务提供者（SP），可以使用玉符SDK验证JWT令牌的有效性（包括有效期、签名等），成功后进行相应的建权。
- 如果服务提供者（SP）还为用户/租户提供身份联合、第三方登录的功能，可以使用玉符SDK生成并签署JWT令牌，并送到玉符服务器上认证并跳转到第三方身份提供者。(需额外生成pem格式的公私钥文件)

--------------

使用SDK
-------

**身份提供者（IDP)**

1. 实例化SDK的身份提供商::

    $YufuSDK = new YufuSDK(
      "Your tenant name",                             // 在玉符注册的租户名称。
      "idp-xxxxxxxxxxx",                              // 从玉符注册并获取相应的身份提供者ID
      "../test/priv.pem"                              // 租户在服务器上生成私钥文件（.pem格式），需安全保管。
    );

#. 根据用户选取或提前定义的服务应用ID，可以为用户生成并签署对应令牌\ ``generateIDPUrl``\ ，并向用户发送302跳转登录到第三方服务提供商，代码样式::

    $payload = array(
        'sub' => "username123123",                     // 用户名称或ID
        'spid' => "sp-123"                             // 用户选取或提前定义的服务应用ID
    );
    $url = $YufuSDK->generateIDPUrl(payload);
    // redirectURL(url);                               // 跳转用户至玉符云服务器认证授权，可根据需求在URL中添加定制的请求参数(如内网ip)便于后期审计

**服务提供者（SP)**

1. 实例化SDK的服务提供商::

    $YufuSDK = new YufuSDK(
      "Your tenant name",                             // 在玉符注册的租户名称。
      null,
      null,
      "Your public key path"                          // 本地的公钥路径，例: ../test/public.pem
    );

#. 实现单点登录：接收并验证\ ``verify``\ JWT令牌的有效性（包括有效期、签名等），如通过，说明该令牌来自玉符信任的有效租户(企业/组织)的用户，样例::

    $idToken = $this->getIdToken();                   // 从URL中获得 ID token
    $claims = $YufuSDK->verify(idToken);              // 使用验证玉符SDK实例进行验证, 如果成功会返回包含用户信息的对象，失败则会产生授权错误的异常
    $username = $claims->sub;                         // 用户名称
    $clientId = $claims->aud;                         // 玉符签发的唯一识别码
    $tenant = $claims->tnt;                           // 租户名称

#. 根据第2步获取的用户信息，您的系统可进行鉴权和赋予登录系统等相应权限，否则提示用户登录失败。推荐鉴权方案:

   -  服务提供商(SP)允许租户管理员输入一个玉符的唯一识别码
   -  在第2步Token验证通过后，根据获取的租户名称/ID和识别码,
     查看是否匹配该租户在服务提供商(SP)所提供的唯一识别码，如匹配则表示租户为有效租户。
   -  接着查看用户是否存在于租户中，如果存在，则赋予登录系统的权限。
   
#. 测试方法:

   -  使用test文件夹中public.pem的路径传入公钥路径中
   -  用长期token进行verify测试, 例子可见testVerify.php
   -  长期token如下:
   eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImM3ODk5Y2M2ODE3NThlYjdkNmUyNDU4NDJkMWY5MjQxMTNmYTQyN2IifQ.eyJzdWIiOiJWRUdBIiwiYXVkIjoiY2lkcCIsInRudCI6Inl1ZnUiLCJpYXQiOjE1MzE4MTg3NTgsImV4cCI6MzE1MzE4MTg3NTgsImlzcyI6ImlkcC0wN2I0MDU4OS1hNTg1LTQwZWEtOWU4Ni05NzlmM2M2NTJiNTYifQ.lutn8IWFeWWITf_q_xTullkJLatSOJho0qLUARlOzoTOnTyECB-idoZvquZ6H8LSsme8mvA9NWAWS-XNS1i6V_ns0R0zlHf89DUyan6D7yAlP0_7M70j3GgQfX92ovELMLl2VYed19Nq2OxykIM5Of8iDB5xwyv43Lyl-Y9GSTY1IXNfgnIFPNUIOpO1Qz8XEj7uxhtcppX2r0WKoyPSZ6Vy5k-IYwKf7_zBTsChEpSr-IroueKF1xcsWgWsu-3913FLhMU0FMdMdseG-pFsnAb2gRR9Pbk-Y2eXsV_Sj4UV1-tzget_2wDA9hGCiLV3WfNCemo4tRIgn5vXN0IsGw

异常说明
--------

1. 在调用SDK方法时可能抛出以下异常:

   -  InvalidFormatException : token格式不正确
   -  ExpiredException : token过期
   -  TokenParseException : 解析token出错
   -  TokenTooEarlyException : token在生效之前使用
   -  CannotRetrieveKeyException : key为空，请检查服务器的网络连接
   -  SignatureInvalidException: token的签名无效，请检查key是不是对应

FAQ
---

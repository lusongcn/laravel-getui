个推是国内领先的推送技术服务商,提供安卓(Android)和iOS推送SDK,为APP开发者提供高效稳定推送技术服务;
每个APP都需要推送，在做后端的时候，我们肯定是需要个推来实现对APP推送消息，个推的使用功能特别多，如果自己单独去开发，一定浪费大量的时间，所以我集成了Laravel个推拓展，方便大家学习使用

# 开发前的准备  
1. 安装Laravel  
1. 申请个推APPKEY，APPID，MASTERSECRET等  
1. 有一个自己的测试CID，问APP开发人员要吧  

# 安装拓展
1.在 `composer.json` 的 `require` 里面加入以下内容：
```php
"earnp/getui": "v1.0"
```

2.添加完成后，执行 `composer update`
```php
composer update
```
3.等待下载安装完成，需要在`config/app.php`中注册服务提供者同时注册下相应门面：
```php
'providers' => [
    //........
    Earnp\Getui\GetuiServiceprovider::class,
],

'aliases' => [
     //..........
    'Getui'     => Earnp\Getui\Facades\Getui::class,
],
```
服务注入以后，如果要使用自定义的配置，还可以发布配置文件到config目录：
```php
php artisan vendor:publish
```

# 其他接口

### 获取单日用户数据
```php
Getui::getUserDataByDate($date);
```

请求参数`date`,查询的日期（格式：yyyyMMdd）,比如：20170525  
返回参数：
```php
[
  "result" => "Success",
  "data" => [
  // 新注册用户数
  "newRegistCount" => 512,
  // 累计注册用户数
  "registTotalCount" => 14349,
  // 活跃用户数
  "activeCount" => 1544,
  // 在线用户数
  "onlineCount" => 55,
  "appId" => "",
  "date" => "20170726",
  ]
]
```

### 获取单日推送数据
```php
Getui::getPushDataByDate($date);
```

请求参数`date`,查询的日期（格式：yyyyMMdd）,比如：20170525  

返回参数：
```php
[
  "result" => "ok",
  "data" => [
    "appId" => "",
    "date" => "20170726",
    // 发送总数
    "sendCount" => 255,
    // 在线发送数
    "sendOnlineCount" => 140,
    // 接收数
    "receiveCount" => 138,
    // 展示数
    "showCount" => 25,
    // 点击数
    "clickCount" => 12,
  ],
  "GT" => "{"sent":140,"feedback":138,"displayed":25,"clicked":12}"
]
```

### 停止任务接口
```php
Getui::stopPushTask($taskId);
```
请求参数`taskId`,发送任务的taskId，在Push返回中获取  
返回参数：`true`,`false`

### 查询用户状态
```php
Getui::getUserStatus($CID);
```
请求参数`CID`,用户唯一标识符,默认查询的是`config/getui.php`文件中的`CID`  

```php
[
  // 是否在线，Offline代表不在线，Online代表在线
  "result" => "Offline",
  // 最后登陆时间
  "lastLogin" => 1501142220930,
  "isblack" => false,
]
```

### 获取推送状态
```php
Getui::getPushMessageResult($taskId);
```
请求参数`taskId`,发送任务的taskId，在Push返回中获取  
返回参数：
```php
[
  "result" => "ok",
  "taskId" => "OSS-0728_071043065bcecc3a129937763b2309cd"
]
```

### 通过标签获取用户总数
```php
Getui::getUserCountByTags($tagList);
```
请求参数`tagList`,标签列表`Array`,比如：`array("laravel","php")`  

返回参数：
```php
[
  "result" => "Success",
  "appId" => "",
  "tagCount" => [],
]
```

### 大数据综合分析用户得到的标签:即用户画像
```php
Getui::getPersonaTags();
```
返回参数：
```php
[
  "result" => "Success",
  "tags" => []
]
```

# 消息模板

### 点击通知打开应用模板，可传递参数  
IGtNotificationTemplate  
$config配置信息如下

参数 | 类型 | 说明
----|------|----
type | str  | HIGH/SIMPLE SIMPLE代表简单模版，HIGH代表高级模版
title | str  | HIGH模版使用，消息标题
body | str  | 消息内容
logo | str  | logo
logourl | str  | logo地址

### 点击通知打开网页模板  
IGtLinkTemplate  

$config配置信息如下：

参数 | 类型 | 说明
----|------|----
title | str  | 消息标题
body | str  | 消息内容
logo | str  | logo
logourl | str  | logo地址
url | str  | url代表当点击弹窗跳转到的网址

### 点击通知弹窗下载模板(iOS 不支持使用该模板)
IGtNotyPopLoadTemplate  
$config配置信息如下：  

参数 | 类型 | 说明
----|------|----
type | str  | notice/bomb/download notice代表通知栏，bomb代表弹框，download代表下载
title | str  | 消息标题
body | str  | 消息内容
logo | str  | logo
loadurl | str  | loadurl代表当使用download模版时的下载地址

### 透传消息模版，可传递参数  
IGtTransmissionTemplate  
$config配置信息如下

参数 | 类型 | 说明
----|------|----
type | str  | HIGH/SIMPLE SIMPLE代表简单模版，HIGH代表高级模版
title | str  | HIGH模版使用，消息标题
body | str  | 消息内容
logo | str  | logo
logourl | str  | logo地址


# 消息推送方式

### 对单个用户推送消息

```php
$template = "IGtTransmissionTemplate";
$data = "a";
$config = array("type" => "HIGH", "title" => "你有一条新消息", "body" => "你有一个3000元的订单需要申请","logo"=>"","logourl"=>"");
$CID = "";
$test = Getui::pushMessageToSingle($template,$config,$data,$CID);
```
参数说明：  
$template代表上一步的消息模板  
$data代表您推送的内容，具体询问APP开发人员，一般为JSON格式，如果只是普通的发送消息随意填写  
$config参考模版所需内容，这里使用透传为例  
$CID为发送给某人具体CID，默认为config/getui.php中的测试CID  

返回参数:
```php
[
  "result" => "ok",
  "taskId" => "OSS-0728_071043065bcecc3a129937763b2309cd",
  "status" => "successed_offline",
]
```

### 对指定列表用户推送消息
```php
$template = "IGtTransmissionTemplate";
$data = "a";
$config = array("type" => "HIGH", "title" => "你有一条新消息", "body" => "你有一个3000元的订单需要申请","logo"=>"","logourl"=>"");
$CID = "";
$test = Getui::pushMessageToList($template,$config,$data,$CID);
```
参数说明：  
$template代表上一步的消息模板  
$data代表您推送的内容，具体询问APP开发人员，一般为JSON格式，如果只是普通的发送消息随意填写  
$config参考模版所需内容，这里使用透传为例  
$CID为发送给用户组的CID（列表模式），默认为config/getui.php中的测试CID  

返回参数:
```php
[
  "result" => "ok"
  "contentId" => "OSL-0728_hLKYR4tmkC7S0lI7QnSTT"
]
```
应用场景：  
场景1，对于抽奖活动的应用，需要对已知的某些用户推送中奖消息，就可以通过ClientID列表方式推送消息。  
场景2，向新客用户发放抵用券，提升新客的转化率，就可以事先提取新客列表，将消息指定发送给这部分指定CID用户。  


### 对指定应用群推消息
```php
// 模版选择
$template = "IGtTransmissionTemplate";
// 发送内容
$data = "IGtTransmissionTemplate";
// 条件选择
$choice = array(
        // 发送手机类型
        "phoneTypeList"=>["ANDROID","IPHONE","IPAD"],
        // 发送城市
        "provinceList"=>["北京","上海"],
        // 标签选择，为空即可
        "tagList"=>["标签"],
        // 年龄选择
        "age"=>["10","11"]
    );
$config = array("type" => "HIGH", "title" => "你有一条新消息", "body" => "贷贷还更新了哦，快去看看吧","logo"=>"","logourl"=>"");
$test = Getui::pushMessageToApp($template,$config,$data,$choice);
```
参数说明：  
$template代表上一步的消息模板  
$data代表您推送的内容，具体询问APP开发人员，一般为JSON格式，如果只是普通的发送消息随意填写  
$config参考模版所需内容，这里使用透传为例  
$choice为发送给用户组的条件筛选  

返回参数:
```php
[
  "result" => "ok"
  "contentId" => "OSL-0728_hLKYR4tmkC7S0lI7QnSTT"
]
```

应用场景：  
1、对全部APP的用户推送消息  
2、对某个城市的人推送消息  
3、对某个年龄的人推送消息


# 推送接口模式
推送接口包括https模式和http模式
更改只需要修改`config/getui.php`中的`HTTPS`参数，`true`代表为Https推送接口，`false`代表为Http推送接口，默认为Http请求
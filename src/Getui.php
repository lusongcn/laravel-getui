<?php 
namespace Earnp\Getui;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Earnp\Getui\Libarys\IGtPush;
use Earnp\Getui\Libarys\IGtBatch;

class Getui
{
    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */

    // 请求构造
    public static function IGtPush()
    {
        if (Config::get("getui.HTTPS")) return new IGtPush(Config::get("getui.HOST")[1],Config::get("getui.APPKEY"),Config::get("getui.MASTERSECRET"),Config::get("getui.HTTPS"));
        return new IGtPush(Config::get("getui.HOST")[0],Config::get("getui.APPKEY"),Config::get("getui.MASTERSECRET"),Config::get("getui.HTTPS"));
    }

    // 大数据综合分析用户得到的标签:即用户画像
    public static function getPersonaTags()
    {
        $igt = self::IGtPush();
        $ret = $igt->getPersonaTags(Config::get("getui.APPID"));
        return $ret;
    }

    // 停止推送任务
    public static function stopPushTask($taskId=""){
        $igt = self::IGtPush();
        $rep = $igt->stop($taskId);
        return $rep;
    }

    // 获取用户状态
    public static function getUserStatus($CID="") {
        $igt = self::IGtPush();
        $CID = empty($CID) ? Config::get("getui.CID") :$CID;
        $rep = $igt->getClientIdStatus(Config::get("getui.APPID"),$CID);
        return $rep;
    }

    // 通过标签获取用户总数
    public static function getUserCountByTags($tagList) {
        $igt = self::IGtPush();
        $ret = $igt->getUserCountByTags(Config::get("getui.APPID"), $tagList);
        return $ret;
    }

    // 获取推送状态
    public static function getPushMessageResult($taskId){
        $igt = self::IGtPush();
        $ret = $igt->getPushResult($taskId);
        return $ret;
    }

    // 获取单日用户数据
    public static function getUserDataByDate($date){
        $igt = self::IGtPush();
        $ret = $igt->queryAppUserDataByDate(Config::get("getui.APPID"),$date);
        return $ret;
    }

    // 获取单日推送数据
    public static function getPushDataByDate($date){
        $igt = self::IGtPush();
        $ret = $igt->queryAppPushDataByDate(Config::get("getui.APPID"),$date);
        return $ret;
    }


    // 单一透传消息
    public static function pushMessageToSingle($template,$config,$data,$CID){
        $igt = self::IGtPush();
        // 类型参数
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板
        switch ($template) {
            case 'IGtNotificationTemplate':
                $template = self::IGtNotificationTemplate($data,$config);
                break;
            case 'IGtLinkTemplate':
                $template = self::IGtLinkTemplate($data,$config);
                break;
            case 'IGtNotyPopLoadTemplate':
                $template = self::IGtNotyPopLoadTemplate($data,$config);
                break;
            default:
                $template = self::IGtTransmissionTemplate($data,$config);
                break;
        }

        $message = new \IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        //$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，2为4G/3G/2G，1为wifi推送，0为不限制推送
        // 消息推送方式
        $CID = empty($CID) ? Config::get("getui.CID") : $CID;
        //接收方
        $target = new \IGtTarget();
        $target->set_appId(Config::get("getui.APPID"));
        $target->set_clientId($CID);
        // $target->set_alias(Alias);
        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            return $rep;
        }catch(RequestException $e){
            $requstId =$e.getRequestId();
            //失败时重发
            $rep = $igt->pushMessageToSingle($message, $target,$requstId);
            return $rep;
        }
    }

    // 列表推送
    public static function pushMessageToList($template,$config,$data,$CID){
        $igt = self::IGtPush();
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板
        switch ($template) {
            case 'IGtNotificationTemplate':
                $template = self::IGtNotificationTemplate($data,$config);
                break;
            case 'IGtLinkTemplate':
                $template = self::IGtLinkTemplate($data,$config);
                break;
            case 'IGtNotyPopLoadTemplate':
                $template = self::IGtNotyPopLoadTemplate($data,$config);
                break;
            default:
                $template = self::IGtTransmissionTemplate($data,$config);
                break;
        }

        //定义"ListMessage"信息体
        $message = new \IGtListMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(1);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $contentId = $igt->getContentId($message);
        // dd($message);

        $CID = empty($CID) ? Config::get("getui.CID") : $CID;
        //接收方1
        $target1 = new \IGtTarget();
        $target1->set_appId(Config::get("getui.APPID"));
        $target1->set_clientId($CID);
        //$target1->set_alias(Alias1);
        //接收方2
        // $target2 = new \IGtTarget();
        // $target2->set_appId(Config::get("getui.APPID"));
        // $target2->set_clientId(Config::get("getui.CID"));
        //$target2->set_alias(Alias2);
        $targetList[0] = $target1;
        // $targetList[1] = $target2;
        $rep = $igt->pushMessageToList($contentId, $targetList);
        return $rep;
    }

    // 群推接口案例
    public static function pushMessageToApp($template,$config,$data,$choice){
        $igt = self::IGtPush();
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板
        switch ($template) {
            case 'IGtNotificationTemplate':
                $template = self::IGtNotificationTemplate($data,$config);
                break;
            case 'IGtLinkTemplate':
                $template = self::IGtLinkTemplate($data,$config);
                break;
            case 'IGtNotyPopLoadTemplate':
                $template = self::IGtNotyPopLoadTemplate($data,$config);
                break;
            default:
                $template = self::IGtTransmissionTemplate($data,$config);
                break;
        }
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);

        $appIdList=array(Config::get("getui.APPID"));
        $phoneTypeList=$choice["phoneTypeList"];
        $provinceList=$choice["provinceList"];
        $tagList=$choice["tagList"];
        $age = $choice["age"];

        $cdt = new \AppConditions();
        $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
        $cdt->addCondition(AppConditions::REGION, $provinceList);
        $cdt->addCondition(AppConditions::TAG, $tagList);
        $cdt->addCondition("age", $age);

        $message->set_appIdList($appIdList);
        $message->condition = $cdt;

        $rep = $igt->pushMessageToApp($message);

        return $rep;
    }


    // 透传数据构造
    public static function IGtTransmissionTemplate($data,$config){
            $template = new \IGtTransmissionTemplate();
            $template->set_appId(Config::get("getui.APPID"));//应用appid 
            $template->set_appkey(Config::get("getui.APPKEY"));//应用appkey
            $template->set_transmissionType(1);//透传消息类型 
            $template->set_transmissionContent($data);//透传内容
            // $template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

            $type = empty($config["type"]) ? "simple" : $config["type"];
            $body = empty($config["body"]) ? "测试内容" : $config["body"];
            $logo = empty($config["logo"]) ? "" : $config["logo"];
            $logourl = empty($config["logourl"]) ? "simple" : $config["logourl"];
            $title = empty($config["title"]) ? "测试标题" : $config["title"];
            // 如下有两个推送模版，一个简单一个高级，可以互相切换使用。
            if ($config["type"]=="SIMPLE") {
                // APN简单推送
                $apn = new \IGtAPNPayload();
                $alertmsg=new \SimpleAlertMsg();
                $alertmsg->alertMsg=$body;
                $apn->alertMsg=$alertmsg;
                $apn->badge=2;
                $apn->sound="";
                $apn->add_customMsg("payload","payload");
                $apn->contentAvailable=1;
                $apn->category="ACTIONABLE";
                $template->set_apnInfo($apn);
            }
            else
            {
                // APN高级推送
                $apn = new \IGtAPNPayload();
                $alertmsg=new \DictionaryAlertMsg();
                $alertmsg->body=$body;
                $alertmsg->actionLocKey="ActionLockey";
                $alertmsg->locKey="LocKey";
                $alertmsg->locArgs=array("locargs");
                $alertmsg->launchImage="launchimage";
                $alertmsg->set_logo=$logo;
                $alertmsg->set_logoURL=$logourl;
                // iOS8.2 支持
                $alertmsg->title=$title; 
                $alertmsg->titleLocKey="TitleLocKey"; 
                $alertmsg->titleLocArgs=array("TitleLocArg");

                $apn->alertMsg=$alertmsg;
                $apn->badge=7;
                $apn->sound=""; 
                $apn->add_customMsg("payload","payload");
                $apn->contentAvailable=1;
                $apn->category="ACTIONABLE";
                $template->set_apnInfo($apn);
            }
            return $template;
    }

    // 点击通知打开应用模板
    public static function IGtNotificationTemplate($data,$config)
    {
        $body = empty($config["body"]) ? "测试内容" : $config["body"];
        $logo = empty($config["logo"]) ? "" : $config["logo"];
        $logourl = empty($config["logourl"]) ? "simple" : $config["logourl"];
        $title = empty($config["title"]) ? "测试标题" : $config["title"];
        $url = empty($config["url"]) ? "" : $config["url"];
        // 数据
        $template = new \IGtNotificationTemplate();
        $template->set_appId(Config::get("getui.APPID"));//应用appid
        $template->set_appkey(Config::get("getui.APPKEY"));//应用appkey
        $template->set_transmissionType(1); //透传消息类型
        $template->set_transmissionContent($data); //透传内容
        $template->set_title($title); //通知栏标题
        $template->set_text($body); //通知栏内容
        $template->set_logo($logo); //通知栏logo
        $template->set_logoURL($logourl); //通知栏logo链接
        $template->set_isRing(true); //是否响铃
        $template->set_isVibrate(true); //是否震动
        $template->set_isClearable(true); //通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    // 点击通知打开网页模板
    public static function IGtLinkTemplate($data,$config)
    {
        $body = empty($config["body"]) ? "测试内容" : $config["body"];
        $logo = empty($config["logo"]) ? "" : $config["logo"];
        $logourl = empty($config["logourl"]) ? "simple" : $config["logourl"];
        $title = empty($config["title"]) ? "测试标题" : $config["title"];
        $url = empty($config["url"]) ? "" : $config["url"];
        $template = new \IGtLinkTemplate();
        $template ->set_appId(Config::get("getui.APPID")); //应用appid
        $template ->set_appkey(Config::get("getui.APPKEY")); //应用appkey
        $template ->set_title($title); //通知栏标题
        $template ->set_text($body); //通知栏内容
        $template->set_logo($logo); //通知栏logo
        $template->set_logoURL($logourl); //通知栏logo链接
        $template ->set_isRing(true); //是否响铃
        $template ->set_isVibrate(true); //是否震动
        $template ->set_isClearable(true); //通知栏是否可清除
        $template ->set_url($url); //打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    // 点击通知弹窗下载模板,(iOS 不支持使用该模板)
    public static function IGtNotyPopLoadTemplate($data,$config)
    {
        $type = empty($config["type"]) ? "simple" : $config["type"];
        $body = empty($config["body"]) ? "测试内容" : $config["body"];
        $logo = empty($config["logo"]) ? "" : $config["logo"];
        $title = empty($config["title"]) ? "测试标题" : $config["title"];
        $loadurl = empty($config["loadurl"]) ? "" : $config["loadUrl"];

        $template = new \IGtNotyPopLoadTemplate();
        $template ->set_appId(Config::get("getui.APPID")); //应用appid
        $template ->set_appkey(Config::get("getui.APPKEY")); //应用appkey
        if ($config["type"]=="notice") {
            //通知栏
            $template ->set_notyTitle($title); //通知栏标题
            $template ->set_notyContent($body); //通知栏内容
            $template ->set_notyIcon(""); //通知栏logo
            $template ->set_isBelled(true); //是否响铃
            $template ->set_isVibrationed(true); //是否震动
            $template ->set_isCleared(true); //通知栏是否可清除
        }
        else if ($config["type"]=="bomb") {
            //弹框
            $template ->set_popTitle($title); //弹框标题
            $template ->set_popContent($body); //弹框内容
            $template ->set_popImage(""); //弹框图片
            $template ->set_popButton1("下载"); //左键
            $template ->set_popButton2("取消"); //右键
        }
        else{
            //下载
            $template ->set_loadIcon(""); //弹框图片
            $template ->set_loadTitle($title);
            $template ->set_loadUrl($loadurl);
            $template ->set_isAutoInstall(false);
            $template ->set_isActived(true);
        }
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }
    

}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统初始化指南</title>

</head>
<body>
    <div class="row-content am-cf">
        <div class="row">
            <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
                <div class="widget am-cf">
                    <div class="widget-body">
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">系统初始化指南</div>
                        </div>
                        <div class="link-list">
                            <div data-am-widget="accordion" class="am-accordion am-accordion-gapped" data-am-accordion='{ "multiple": false }'>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第一步：新建仓库 
                                        <?php if(isset($shoplist) && count($shoplist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            仓库管理：<a target="_blank" href="<?= url('/store/shop/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第二步：创建支持的国家或地区
                                        <?php if(isset($countrylist) && count($countrylist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            国家支持：<a target="_blank" href="<?= url('/store/setting.country/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第三步：创建运输方式
                                        <?php if(isset($linecategory) && count($linecategory)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            运输方式：<a target="_blank" href="<?= url('/store/setting.line_category/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第四步：创建货品分类
                                        <?php if(isset($categorylist) && count($categorylist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            货品分类：<a target="_blank" href="<?= url('/store/setting.category/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第五步：集运线路
                                        <?php if(isset($linelist) && count($linelist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            集运线路：<a target="_blank" href="<?= url('/store/setting.line/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第六步：打包服务
                                        <?php if(isset($packageservicelist) && count($packageservicelist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            打包服务：<a target="_blank" href="<?= url('/store/setting.package/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第七步：增值服务
                                        <?php if(isset($lineservicelist) && count($lineservicelist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            增值服务：<a target="_blank" href="<?= url('/store/setting.addservice/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第八步：物流公司
                                        <?php if(isset($expresslist) && count($expresslist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            物流公司：<a target="_blank" href="<?= url('/store/setting.express/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第九步：渠道商管理
                                        <?php if(isset($ditchlist) && count($ditchlist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            渠道商管理：<a target="_blank" href="<?= url('/store/setting.ditch/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第十步：保险服务
                                        <?php if(isset($insurelist) && count($insurelist)>0): ?>
                                        <span class="am-badge am-badge-success">已填写</span>
                                        <?php endif;?>
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            保险服务：<a target="_blank" href="<?= url('/store/setting.insure/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">小程序端功能说明</div>
                        </div>
                       <div class="link-list">
                            <div data-am-widget="accordion" class="am-accordion am-accordion-gapped" data-am-accordion='{ "multiple": false }'>
                                <dl class="am-accordion-item">
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序首页简化或美化
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                               用户端设置：<a target="_blank" href="<?= url('/store/setting/userclient') ?>">点击前往</a>
                                            </div>
                                            <div class="am-accordion-content"> 在用户端设置中，你可以对小程序中的部分内容进行替换文字和图片以及跳转链接等；部分功能可以进行关闭隐藏。
                                            </div>
                                            <div class="am-accordion-content">
                                               系统设置（全局设置）：<a target="_blank" href="<?= url('/store/setting/store') ?>">点击前往</a>
                                            </div>
                                            <div class="am-accordion-content"> 在系统设置中，找到【功能开启隐藏设置】【个人中心功能开关】即可根据自己的需要隐藏或开启功能；能够极大简化小程序页面；
                                            </div>
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序轮播图
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                               轮播管理：<a target="_blank" href="<?= url('/store/setting.banner/index') ?>">点击前往</a>
                                            </div>
                                            <div class="am-accordion-content"> 轮播图就是首页顶部的滚动图片，一般用于营销活动的入口。你可以自行设计轮播图，尺寸推荐使用【750*350】，其他尺寸亦可。不过需要在<a target="_blank" href="<?= url('/store/setting/store') ?>">系统设置</a>中找到【轮播图高度】设置项，系统默认为280，你可以根据设计的图片高度进行多次调整，直到自己满意。
                                            </div>
                                            
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            引导关注的公众号设置
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                               在<a target="_blank" href="<?= url('/store/setting/userclient') ?>">用户端设置</a>中找到【引导用户关注公众号】，按系统说明设置即可
                                            </div>
                                            <div class="am-accordion-content">
                                               公众号二维码在<a target="_blank" href="https://mp.weixin.qq.com/">微信公众平台</a>中，请使用管理员账号扫码登录，有的管理员扫码后有多个选择，请选择标题为【选择服务号登录】，在后台找到【设置与开发】【账号设置】【二维码】眼睛往右边看可以找到【下载二维码】按钮，点击选择合适大小二维码下载；
                                            </div>
                                            <div class="am-accordion-content">
                                               注意：集运物流平台使用公众号主要是让用户接受包裹的出入库等通知，如需发布公众号推文，请在<a target="_blank" href="https://mp.weixin.qq.com/">微信公众平台</a>中自行发布。
                                            </div>
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序首页最佳路线设置
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                               在<a target="_blank" href="<?= url('/store/setting.line/index') ?>">集运线路</a>中找到你想首页展示的集运路线，点击对应的【编辑】按钮，将【推荐至首页】设置为【是】即可
                                            </div>
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序首页客服
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                               在<a target="_blank" href="<?= url('/store/setting/store') ?>">系统设置</a>找到【客服设置】按提示填写即可，开发小程序客服后，请前往<a target="_blank" href="https://mp.weixin.qq.com/">小程序后台</a>使用管理员微信扫码进入，在【基础功能】->【客服】中添加绑定客服人员
                                            </div>
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序隐私协议
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                               文章分类：<a target="_blank" href="<?= url('/store/content.article.category/index') ?>">点击前往</a>
                                            </div>
                                            <div class="am-accordion-content"> 先在【文章分类】中点击【新增】，分类类别选择【隐私协议】或【保险协议】，添加分类后，在【文章列表】中点【新增】，【文章分类】选择【隐私协议】或【保险协议】
                                            </div>
                                            
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序底部菜单
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                                底部菜单：<a target="_blank" href="<?= url('/store/setting.menus/index') ?>">点击前往</a>
                                            </div>
                                            <div class="am-accordion-content">
                                            在配置前，需要先前往【用户端设置】中找到<a href="/store/setting/userclient">【底部菜单设置】</a>栏 ，并选择自定义模式，然后根据需要可以选择带快捷按钮或不带，也可以选择系统预设的底部菜单。
                                            </div>
                                            
                                        </dd>
                                    </dl>
                                
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序导航
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                                小程序导航：<a target="_blank" href="<?= url('/store/setting.nav/index') ?>">点击前往</a>
                                            </div>
                                            <div class="am-accordion-content">
                                            系统预设了8个小程序导航，如需自定义设置导航，则需将所有的图标，导航名称，导航链接重新设置。
                                            </div>
                                            
                                        </dd>
                                    </dl>
                                    
                                    <dl class="am-accordion-item">
                                        <dt class="am-accordion-title">
                                            小程序仓库地址显示方式设置
                                        </dt>
                                        <dd class="am-accordion-bd am-collapse">
                                            <div class="am-accordion-content">
                                            在<a target="_blank" href="<?= url('/store/setting/store') ?>">系统设置</a>找到【小程序仓库地址显示方式设置】，选择你需要的显示方式即可
                                            </div>
                                        </dd>
                                    </dl>
                                        
                                    
                                
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        用户编号(ID)规则
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            在<a target="_blank" href="<?= url('/store/setting/store') ?>">系统设置</a>中找到【用户编号设置】，并按照所需要的生成规则选择
                                            系统ID：系统ID为数据库自增的，是数字型，并且不可更改
                                            编号CODE：推荐使用，编号模式比较灵活，可以自定义编号是随机数字，随机字母，或者固定字母+随机数字组合
                                            唛头模式：唛头模式是用户可以自定义唛头，因为此功能暂不成熟，暂时不推荐使用，未来开发完善后可以选择
                                        </div>
                                    </dd>
                                </dl>

                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        电子秤
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            电子秤接口：<a target="_blank" href="<?= url('/store/tools/apipost') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        系统已经接入了能够识别单号，拍照，称重的普通电子秤。如需购买请联系软件开发商；
                                        如需更加强大的电子秤或自有的电子秤，请将上面的电子秤接口发送给电子秤厂商，让其接入接口即可；
                                        </div>
                                        
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">后台管理端功能说明</div>
                        </div>
                        <div class="link-list">
                            <div data-am-widget="accordion" class="am-accordion am-accordion-gapped" data-am-accordion='{ "multiple": false }'>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        新建管理员账号
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            管理员：<a target="_blank" href="<?= url('/store/store.user/index') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        如需给添加的管理员设置对应的功能权限，请在【角色管理】中先添加角色，给角色设置能够操作的功能权限；
                                        </div>
                                        
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        自提点管理
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            自提点：<a target="_blank" href="<?= url('/store/shop.address/index') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        请根据需要添加自提点，如需要关闭用户自填地址，请在<a target="_blank" href="<?= url('/store/setting/userclient') ?>">用户端设置</a>找到【用户打包功能设置】开启或关闭
                                        </div>
                                        
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        优惠券功能设置
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            优惠券设置：<a target="_blank" href="<?= url('/store/market.coupon/index') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        点击【新增】添加优惠券，选择公开到领券中心则所有用户都可以领取，否则只能给用户私发。私发优惠券需要进入<a target="_blank" href="<?= url('store/user/index') ?>">用户列表</a>，选中需要发送优惠券的用户，点击上面按钮【发放优惠券】，选择需要发放的优惠券即可；
                                        
                                        </div>
                                        <div class="am-accordion-content"> 在优惠券设置中，你可以根据需要设置【新用户注册发放】【下单并评价完成后发放】的优惠券，设置此功能必须先添加优惠券
                                        </div>
                                        <div class="am-accordion-content"> 如需回收优惠券，在<a target="_blank" href="<?= url('/store/market.coupon/receive') ?>">领取记录</a>中找到对应的记录删除即可
                                        </div>
                                    </dd>
                                </dl>
                                <dt class="am-accordion-title">
                                        支付功能配置
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            微信支付：<a target="_blank" href="<?= url('/store/wxapp/setting') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        在配置前，请先请通过微信支付平台<a href="https://pay.weixin.qq.com/">申请微信支付</a> ，申请成功后将商户号跟小程序进行绑定，并获取商户号和支付密钥（V2）请输入【微信支付商户号】【微信支付密钥】参数
                                        </div>
                                        <div class="am-accordion-content">
                                            其他支付设置：
                                        </div>
                                        <div class="am-accordion-content">
                                            其他支付设置：<a target="_blank" href="<?= url('/store/setting/paytype') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        如开启了【线下支付】，请设置线下支付的收款银行或收款码，<a target="_blank" href="<?= url('/store/setting.bank/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        给用户充值余额&积分
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                        在<a target="_blank" href="<?= url('/store/user/index') ?>">用户列表</a>中，在用户列后面找到【充值】按钮，点击按提示充值
                                        </div>
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        充值设置&充值套餐
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                        充值设置：<a target="_blank" href="<?= url('/store/market.recharge/setting') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content"> 
                                        充值套餐：<a target="_blank" href="<?= url('/store/market.recharge.plan/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        积分功能设置
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            积分设置：<a target="_blank" href="<?= url('/store/market.points/setting') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        设置积分开启后，可以设置积分的赠送规则和抵扣规则。如需要积分兑换<a target="_blank" href="<?= url('/store/market.coupon/index') ?>">优惠券</a>或<a target="_blank" href="<?= url('/store/user.grade/index') ?>">会员等级</a>则可以在添加优惠券或会员等级时，设置兑换所需的积分即可；
                                        如需隐藏个人中心的积分功能，可以在<a target="_blank" href="<?= url('/store/setting/store') ?>">系统设置</a>中的【功能开启隐藏设置】关闭即可;
                                        </div>
                                        
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        公众号菜单管理
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            菜单管理：<a target="_blank" href="<?= url('/store/wxapp/mp') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        公众号菜单设置需要在<a target="_blank" href="https://mp.weixin.qq.com/">微信公众平台</a>的服务号中设置跟域名的绑定，此绑定操作请联系系统技术客服。绑定完成后可以在此处设置公众号的菜单，可以跳转你的集运小程序的首页，包裹预报等任意页面，也可以跳转到其他外部网站链接，公众号内部文章等。
                                        </div>
                                        
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        公众号自动回复
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            自动回复：<a target="_blank" href="<?= url('/store/wxapp/wechat') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        公众号自动回复设置需要在<a target="_blank" href="https://mp.weixin.qq.com/">微信公众平台</a>的服务号中设置跟域名的绑定，此绑定操作请联系系统技术客服。绑定完成后可以在此处设置自己的回复规则，你可以设置用户关注公众号后展示用户编号，比如你可以设置：欢迎关注xx集运，你的编号为{code}。程序会自动将{code}用用户真实的编号替换。
                                        </div>
                                        
                                    </dd>
                                </dl>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        站内信
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            站内信：<a target="_blank" href="<?= url('/store/market.push/sendsms') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content"> 站内信用于给用户发送系统通知，如生日祝福或者系统运营中的通知公告等；需要手动选择发送到用户，目前只支持单人发送；
                                        </div>
                                    </dd>
                                </dl>

                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        仓管账号添加
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            员工管理：<a target="_blank" href="<?= url('/store/shop.clerk/index') ?>">点击前往</a>
                                        </div>
                                        <div class="am-accordion-content">
                                        请根据需要添加员工账号，设置账号密码和对应的功能权限
                                        </div>
                                        
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            // AmazeUI 会自动初始化折叠面板
            // 如果需要自定义行为，可以这样写：
            
            // 确保所有面板初始状态为折叠
            $('.am-accordion-bd').removeClass('am-in');
            
            // 添加点击事件处理（可选）
            $('.am-accordion-title').click(function() {
                // 可以在这里添加自定义逻辑
                console.log('点击了折叠面板标题');
            });
        });
    </script>
</body>
</html> 
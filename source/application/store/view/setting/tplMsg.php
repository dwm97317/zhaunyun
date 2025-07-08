<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="tips am-margin-top-sm am-margin-bottom-sm">
                                <div class="pre">
                                    <p>
                                        模板消息仅用于微信小程序向用户发送服务通知，因微信限制，每笔支付订单可允许向用户在7天内推送最多3条模板消息。
                                        <a href="<?= url('store/setting.help/tplmsg') ?>" target="_blank">如何获取模板消息ID？</a>
                                    </p>
                                    <p>第一步：<a class="set_industry" href="javascript:;">设置所属行业</a></p>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">是否为老模板消息（微信模板消息更改为了类目消息，2023年8月后注册的公众号请选择否）</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[is_oldtps]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_oldtps'] == '1' ? 'checked' : '' ?>
                                               required>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[is_oldtps]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_oldtps'] == '0' ? 'checked' : '' ?>>
                                        否
                                    </label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹入库通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payment][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['payment']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payment][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['payment']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[payment][template_id]"
                                           value="<?= $values['payment']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号OPENTM418500641，关键词 (到货仓库、包裹单号、入库时间)</small>
                                    </div>
                                </div>
                            </div>
        

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">运单状态更新通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[delivery][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['delivery']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[delivery][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['delivery']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[delivery][template_id]"
                                           value="<?= $values['delivery']['template_id'] ?>">
                                    <small>模板编号OPENTM202199304，关键词 (运单号、时间、状态)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹打包申请通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[packageit][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['packageit']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[packageit][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['packageit']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[packageit][template_id]"
                                           value="<?= $values['packageit']['template_id'] ?>">
                                    <small>模板编号OPENTM418551488，关键词 (用户备注名、用户会员账号、用户打包申请时间、用户申请打包数量、包裹出库编号)</small>
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单支付成功通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paymessage][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['paymessage']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paymessage][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['paymessage']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[paymessage][template_id]"
                                           value="<?= $values['paymessage']['template_id'] ?>">
                                    <small>关键词 (用户备注名、用户会员账号、用户打包申请时间、用户申请打包数量、包裹出库编号)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹入库提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[inwarehouse][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['inwarehouse']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[inwarehouse][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['inwarehouse']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[inwarehouse][template_id]"
                                           value="<?= $values['inwarehouse']['template_id'] ?>">
                                    <small>模板编号45458，关键词 (入库仓库、快递单号、入库时间、入库重量、物品)
                                        <a class="wechat_template" href="javascript:;" data-id="45458">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹出库提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outwarehouse][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['outwarehouse']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outwarehouse][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['outwarehouse']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[outwarehouse][template_id]"
                                           value="<?= $values['outwarehouse']['template_id'] ?>">
                                    <small>模板编号47689，关键词 (包裹单号、重量、仓库、包裹状态、出库时间)
                                        <a class="wechat_template" href="javascript:;" data-id="47689">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单支付成功通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paysuccess][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['paysuccess']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paysuccess][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['paysuccess']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[paysuccess][template_id]"
                                           value="<?= $values['paysuccess']['template_id'] ?>">
                                    <small>模板编号47030，关键词 (订单号、支付金额、订单件数、订单重量)
                                        <a class="wechat_template" href="javascript:;" data-id="47030">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单打包完成通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[dabaosuccess][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['dabaosuccess']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[dabaosuccess][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['dabaosuccess']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[dabaosuccess][template_id]"
                                           value="<?= $values['dabaosuccess']['template_id'] ?>">
                                    <small>模板编号50795，关键词 (订单号、仓库名称、新包裹重量、新包裹体积)
                                        <a class="wechat_template" href="javascript:;" data-id="50795">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">出库申请提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outapply][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['outapply']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outapply][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['outapply']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[outapply][template_id]"
                                           value="<?= $values['outapply']['template_id'] ?>">
                                    <small>模板编号42835，关键词 (单号、货主名称、数量、库房名称、时间)
                                        <a class="wechat_template" href="javascript:;" data-id="42835">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">货物到仓通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[toshop][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['toshop']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[toshop][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['toshop']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[toshop][template_id]"
                                           value="<?= $values['toshop']['template_id'] ?>">
                                    <small>模板编号48064，关键词 (运单号、仓库、到仓时间)
                                        <a class="wechat_template" href="javascript:;" data-id="48064">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[sendpack][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['sendpack']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[sendpack][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['sendpack']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[sendpack][template_id]"
                                           value="<?= $values['sendpack']['template_id'] ?>">
                                    <small>模板编号44375，关键词 (订单号、运单号、发货量、承运商、发货时间)
                                        <a class="wechat_template" href="javascript:;" data-id="44375">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">付款单生成提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payorder][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['payorder']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payorder][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['payorder']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[payorder][template_id]"
                                           value="<?= $values['payorder']['template_id'] ?>">
                                    <small>模板编号45318，关键词 (订单号、客户代号、重量、金额、时间)
                                        <a class="wechat_template" href="javascript:;" data-id="45318">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">余额充值成功通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[balancepay][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['balancepay']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[balancepay][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['balancepay']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[balancepay][template_id]"
                                           value="<?= $values['balancepay']['template_id'] ?>">
                                    <small>模板编号43369，关键词 (支付单号,充值金额,充值时间)
                                        <a class="wechat_template" href="javascript:;" data-id="43369">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">餘額充值成功通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[balancepayft][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['balancepayft']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[balancepayft][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['balancepayft']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[balancepayft][template_id]"
                                           value="<?= $values['balancepayft']['template_id'] ?>">
                                    <small>模板编号43369，关键词 (支付單號,支付金額,充值時間)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单待审核提醒(新类目模板)-通知员工</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[orderreview][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['orderreview']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[orderreview][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['orderreview']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[orderreview][template_id]"
                                           value="<?= $values['orderreview']['template_id'] ?>">
                                    <small>模板编号55117，关键词 (订单编号,提交人,提交时间)
                                        <a class="wechat_template" href="javascript:;" data-id="55117">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">充值待審核提醒(新类目模板)-通知员工</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[orderreviewft][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['orderreviewft']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[orderreviewft][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['orderreviewft']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[orderreviewft][template_id]"
                                           value="<?= $values['orderreviewft']['template_id'] ?>">
                                    <small>模板编号55117，关键词 (訂單編號,提交人,提交時間)
                                        
                                    </small>
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">预约成功通知(新类目模板)-通知员工</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[Reservationconfirmed][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['Reservationconfirmed']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[Reservationconfirmed][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['Reservationconfirmed']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[Reservationconfirmed][template_id]"
                                           value="<?= $values['Reservationconfirmed']['template_id'] ?>">
                                    <small>模板编号46591，关键词 (预约单号,姓名,联系电话,取件时间,取件地址)
                                        <a class="wechat_template" href="javascript:;" data-id="46591">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">下单成功通知(新类目模板)-通知用户</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[VisitOrdersuccess][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['VisitOrdersuccess']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[VisitOrdersuccess][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['VisitOrdersuccess']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[VisitOrdersuccess][template_id]"
                                           value="<?= $values['VisitOrdersuccess']['template_id'] ?>">
                                    <small>模板编号50716，关键词 (订单号,收件人,收件地址,下单时间)
                                        <a class="wechat_template" href="javascript:;" data-id="50716">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹认领通知(新类目模板)-通知用户</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[claimpackage][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['claimpackage']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[claimpackage][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['claimpackage']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[claimpackage][template_id]"
                                           value="<?= $values['claimpackage']['template_id'] ?>">
                                    <small>模板编号55992，关键词 (快递单号,客户昵称,客户ID,申请时间,重量)
                                        <a class="wechat_template" href="javascript:;" data-id="55992">拉取模板</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
        //余额抵扣集运
        $('.set_industry').click(function (e) {
            var url = "<?= url('store/WechatMenu/api_set_industry') ?>";
            $.post(url,{}, function (result) {
                $.show_success(result.msg, result.url)
            });
        });
        //余额抵扣集运
        $('.wechat_template').click(function (e) {
            var data = $(this).data();
            var id=  $(this).data().id;
            var url = "<?= url('store/WechatMenu/api_add_template') ?>";
            $.post(url,{id:id}, function (result) {
                if(result.code == 1 ){
                    //下单成功通知
                    if(id == 50716){
                        document.querySelector('input[name="tplMsg[VisitOrdersuccess][template_id]"]').value = result.data;
                    }
                    //预约成功通知
                    if(id == 46591){
                        document.querySelector('input[name="tplMsg[Reservationconfirmed][template_id]"]').value = result.data;
                    }
                    //订单待审核提醒
                    if(id == 55117){
                        document.querySelector('input[name="tplMsg[orderreview][template_id]"]').value = result.data;
                    }
                    //余额充值成功通知
                    if(id == 43369){
                        document.querySelector('input[name="tplMsg[balancepay][template_id]"]').value = result.data;
                    }
                    //付款单生成提醒
                    if(id == 45318){
                        document.querySelector('input[name="tplMsg[payorder][template_id]"]').value = result.data;
                    }
                    //发货通知
                    if(id == 44375){
                        document.querySelector('input[name="tplMsg[sendpack][template_id]"]').value = result.data;
                    }
                    //货物到仓通知
                    if(id == 48064){
                        document.querySelector('input[name="tplMsg[toshop][template_id]"]').value = result.data;
                    }
                    //出库申请提醒
                    if(id == 42835){
                        document.querySelector('input[name="tplMsg[outapply][template_id]"]').value = result.data;
                    }
                    //订单打包完成通知
                    if(id == 50795){
                        document.querySelector('input[name="tplMsg[dabaosuccess][template_id]"]').value = result.data;
                    }
                    //订单支付成功通知
                    if(id == 47030){
                        document.querySelector('input[name="tplMsg[paysuccess][template_id]"]').value = result.data;
                    }
                    //包裹出库提醒
                    if(id == 47689){
                        document.querySelector('input[name="tplMsg[outwarehouse][template_id]"]').value = result.data;
                    }
                    //包裹入库提醒
                    if(id == 45458){
                        document.querySelector('input[name="tplMsg[inwarehouse][template_id]"]').value = result.data;
                    }
                    if(id == 55992){
                        document.querySelector('input[name="tplMsg[claimpackage][template_id]"]').value = result.data;
                    }
                }else{
                  $.show_error(result.msg);   
                }
            });
        });

    });
</script>

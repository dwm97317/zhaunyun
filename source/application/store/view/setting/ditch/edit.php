<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑渠道公司</div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select onchange="changeexpress(this)"  id="deliveryitem" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['key'] ?>"><?= $item['_name'] ?>-<?= $item['_name_zh-cn'] ?>-<?= $item['key'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：请选择对应的渠道公司，并根据使用习惯填写对应的渠道公司名称</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[ditch_name]"
                                           value="<?= $model['ditch_name'] ?>" required>
                                    <small>请对照 <a href="<?= url('setting.ditch/company') ?>" target="_blank">渠道公司编码表</a> 填写</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司代码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="ditch_no" type="text" class="tpl-form-input" name="express[ditch_no]"
                                           value="<?= $model['ditch_no'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 17track中是否有该渠道 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="0" data-am-ucheck <?= $model['type'] == 0 ?'checked' : '' ?>>
                                        有
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="1" data-am-ucheck <?= $model['type'] == 1 ? 'checked' : '' ?>>
                                        无
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php $dt = isset($model['ditch_type']) ? (int)$model['ditch_type'] : 1; ?>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="1" data-am-ucheck <?= $dt == 1 ? 'checked' : '' ?> id="ditch_type_1">
                                        专线
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="2" data-am-ucheck <?= $dt == 2 ? 'checked' : '' ?> id="ditch_type_2">
                                        中通
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="3" data-am-ucheck <?= $dt == 3 ? 'checked' : '' ?> id="ditch_type_3">
                                        中通管家
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="4" data-am-ucheck <?= $dt == 4 ? 'checked' : '' ?> id="ditch_type_4">
                                        顺丰快递
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="5" data-am-ucheck <?= $dt == 5 ? 'checked' : '' ?> id="ditch_type_5">
                                        京东快递
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司官网 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="website" type="text" class="tpl-form-input" name="express[website]"
                                           value="<?= $model['website'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司API地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[api_url]"
                                           value="<?= $model['api_url'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道打印地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[print_url]"
                                           value="<?= $model['print_url'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> AppKey </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[app_key]"
                                           value="<?= $model['app_key'] ?>" required>
                                    <small>顺丰快递填写 <strong>partnerID</strong>，中通填写 AppKey，京东填写 AppKey</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> AppSecret </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[app_token]"
                                           value="<?= $model['app_token'] ?>" required>
                                    <small>顺丰快递填写 <strong>appSecret</strong>（用于生成msgDigest签名），中通填写 AppSecret，京东填写 AppSecret</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="shop_key_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 店铺Key(ShopKey) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="shop_key" type="text" class="tpl-form-input" name="express[shop_key]"
                                           value="<?= isset($model['shop_key']) ? htmlspecialchars($model['shop_key']) : '' ?>" placeholder="中通管家接口必填">
                                    <small>对应接口文档中的 shopKey</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="customer_code_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 客户编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="customer_code" type="text" class="tpl-form-input" name="express[customer_code]"
                                           value="<?= isset($model['customer_code']) ? htmlspecialchars($model['customer_code']) : '' ?>" placeholder="中通集团客户编号 / 顺丰月结卡号">
                                    <small>中通集团客户填写客户编号，顺丰快递填写月结卡号</small>>
                                </div>
                            </div>
                            <div class="am-form-group" id="account_id_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 电子面单账号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="account_id" type="text" class="tpl-form-input" name="express[account_id]"
                                           value="<?= isset($model['account_id']) ? htmlspecialchars($model['account_id']) : '' ?>" placeholder="如 KDGJ221000015957">
                                    <small>非集团电子面单时必填；与客户编号二选一</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="account_password_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 电子面单密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="account_password" type="text" class="tpl-form-input" name="express[account_password]"
                                           value="<?= isset($model['account_password']) ? htmlspecialchars($model['account_password']) : '' ?>" placeholder="测试环境可填 ZTO123">
                                    <small>非集团电子面单时填写；测试环境默认 ZTO123</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="sf_express_type_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 快递产品类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php $sfExpressType = isset($model['sf_express_type']) ? (int)$model['sf_express_type'] : 1; ?>
                                    <select class="tpl-form-input" name="express[sf_express_type]" id="sf_express_type">
                                        <optgroup label="常用产品">
                                            <option value="1" <?= $sfExpressType == 1 ? 'selected' : '' ?>>顺丰特快</option>
                                            <option value="2" <?= $sfExpressType == 2 ? 'selected' : '' ?>>顺丰标快</option>
                                            <option value="6" <?= $sfExpressType == 6 ? 'selected' : '' ?>>顺丰即日</option>
                                            <option value="202" <?= $sfExpressType == 202 ? 'selected' : '' ?>>顺丰微小件</option>
                                            <option value="247" <?= $sfExpressType == 247 ? 'selected' : '' ?>>电商标快</option>
                                            <option value="323" <?= $sfExpressType == 323 ? 'selected' : '' ?>>电商微小件</option>
                                        </optgroup>
                                        <optgroup label="时效产品">
                                            <option value="207" <?= $sfExpressType == 207 ? 'selected' : '' ?>>限时次日</option>
                                            <option value="263" <?= $sfExpressType == 263 ? 'selected' : '' ?>>顺丰半日达</option>
                                            <option value="235" <?= $sfExpressType == 235 ? 'selected' : '' ?>>预售当天达</option>
                                            <option value="253" <?= $sfExpressType == 253 ? 'selected' : '' ?>>前置当天达</option>
                                            <option value="303" <?= $sfExpressType == 303 ? 'selected' : '' ?>>专享即日</option>
                                        </optgroup>
                                        <optgroup label="电商产品">
                                            <option value="236" <?= $sfExpressType == 236 ? 'selected' : '' ?>>电商退货</option>
                                            <option value="261" <?= $sfExpressType == 261 ? 'selected' : '' ?>>O2O店配</option>
                                            <option value="262" <?= $sfExpressType == 262 ? 'selected' : '' ?>>前置标快</option>
                                            <option value="265" <?= $sfExpressType == 265 ? 'selected' : '' ?>>预售电标</option>
                                        </optgroup>
                                        <optgroup label="冷运生鲜">
                                            <option value="201" <?= $sfExpressType == 201 ? 'selected' : '' ?>>冷运标快</option>
                                            <option value="325" <?= $sfExpressType == 325 ? 'selected' : '' ?>>温控包裹</option>
                                            <option value="374" <?= $sfExpressType == 374 ? 'selected' : '' ?>>生鲜专递</option>
                                            <option value="381" <?= $sfExpressType == 381 ? 'selected' : '' ?>>大闸蟹专递</option>
                                        </optgroup>
                                        <optgroup label="国际产品">
                                            <option value="10" <?= $sfExpressType == 10 ? 'selected' : '' ?>>国际小包</option>
                                            <option value="23" <?= $sfExpressType == 23 ? 'selected' : '' ?>>顺丰国际特惠(文件)</option>
                                            <option value="24" <?= $sfExpressType == 24 ? 'selected' : '' ?>>顺丰国际特惠(包裹)</option>
                                            <option value="99" <?= $sfExpressType == 99 ? 'selected' : '' ?>>顺丰国际标快(文件)</option>
                                            <option value="100" <?= $sfExpressType == 100 ? 'selected' : '' ?>>顺丰国际标快(包裹)</option>
                                            <option value="308" <?= $sfExpressType == 308 ? 'selected' : '' ?>>国际特快(文件)</option>
                                            <option value="310" <?= $sfExpressType == 310 ? 'selected' : '' ?>>国际特快(包裹)</option>
                                            <option value="29" <?= $sfExpressType == 29 ? 'selected' : '' ?>>国际电商专递-标准</option>
                                            <option value="218" <?= $sfExpressType == 218 ? 'selected' : '' ?>>国际电商专递-CD</option>
                                            <option value="241" <?= $sfExpressType == 241 ? 'selected' : '' ?>>国际电商专递-快速</option>
                                        </optgroup>
                                        <optgroup label="重货大件">
                                            <option value="111" <?= $sfExpressType == 111 ? 'selected' : '' ?>>顺丰干配</option>
                                            <option value="215" <?= $sfExpressType == 215 ? 'selected' : '' ?>>大票直送</option>
                                            <option value="231" <?= $sfExpressType == 231 ? 'selected' : '' ?>>陆运包裹</option>
                                            <option value="255" <?= $sfExpressType == 255 ? 'selected' : '' ?>>顺丰卡航</option>
                                            <option value="266" <?= $sfExpressType == 266 ? 'selected' : '' ?>>顺丰空配(新)</option>
                                            <option value="293" <?= $sfExpressType == 293 ? 'selected' : '' ?>>特快包裹</option>
                                            <option value="299" <?= $sfExpressType == 299 ? 'selected' : '' ?>>标快零担</option>
                                        </optgroup>
                                        <optgroup label="其他产品">
                                            <option value="59" <?= $sfExpressType == 59 ? 'selected' : '' ?>>E顺递</option>
                                            <option value="244" <?= $sfExpressType == 244 ? 'selected' : '' ?>>店到店</option>
                                            <option value="245" <?= $sfExpressType == 245 ? 'selected' : '' ?>>店到门</option>
                                            <option value="246" <?= $sfExpressType == 246 ? 'selected' : '' ?>>门到店</option>
                                            <option value="252" <?= $sfExpressType == 252 ? 'selected' : '' ?>>即时城配</option>
                                            <option value="259" <?= $sfExpressType == 259 ? 'selected' : '' ?>>极速配</option>
                                            <option value="407" <?= $sfExpressType == 407 ? 'selected' : '' ?>>帮我送</option>
                                        </optgroup>
                                    </select>
                                    <small>不同产品类型时效和价格不同，请根据业务需求选择。常用产品：特快、标快、电商标快</small>
                                </div>
                            </div>
                            <!-- 顺丰云打印选项配置 -->
                            <div class="am-form-group" id="sf_print_options_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 云打印选项 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php 
                                        $sfPrintOptions = [];
                                        if (!empty($model['sf_print_options'])) {
                                            $sfPrintOptions = json_decode($model['sf_print_options'], true);
                                            if (!is_array($sfPrintOptions)) {
                                                $sfPrintOptions = [];
                                            }
                                        }
                                    ?>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="sf_print_preview" id="sf_print_preview" value="1" 
                                            <?= isset($sfPrintOptions['enable_preview']) && $sfPrintOptions['enable_preview'] ? 'checked' : '' ?>>
                                        启用打印预览
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="sf_print_select_printer" id="sf_print_select_printer" value="1"
                                            <?= isset($sfPrintOptions['enable_select_printer']) && $sfPrintOptions['enable_select_printer'] ? 'checked' : '' ?>>
                                        启用打印机选择
                                    </label>
                                    <div style="margin-top: 10px;">
                                        <select class="tpl-form-input" name="sf_default_printer" id="sf_default_printer" 
                                                style="max-width: 400px;"
                                                data-saved-value="<?= isset($sfPrintOptions['default_printer']) ? htmlspecialchars($sfPrintOptions['default_printer']) : '' ?>">
                                            <option value="">使用系统默认打印机</option>
                                            <!-- 打印机列表将通过 JavaScript 动态加载 -->
                                            <?php if (isset($sfPrintOptions['default_printer']) && !empty($sfPrintOptions['default_printer'])): ?>
                                            <option value="<?= htmlspecialchars($sfPrintOptions['default_printer']) ?>" selected>
                                                <?= htmlspecialchars($sfPrintOptions['default_printer']) ?> (已保存)
                                            </option>
                                            <?php endif; ?>
                                        </select>
                                        <button type="button" class="am-btn am-btn-secondary am-btn-xs" id="refresh_printers" style="margin-left: 10px;">
                                            <i class="am-icon-refresh"></i> 刷新打印机列表
                                        </button>
                                        <small style="display: block; margin-top: 5px;">
                                            选择默认打印机。如果未选择，将使用系统默认打印机。点击"刷新"按钮重新加载打印机列表。
                                        </small>
                                        <small id="printer_loading" style="display: none; color: #0e90d2;">
                                            <i class="am-icon-spinner am-icon-spin"></i> 正在加载打印机列表...
                                        </small>
                                        <small id="printer_error" style="display: none; color: #dd514c;">
                                            <i class="am-icon-warning"></i> 加载打印机列表失败，请确保已安装顺丰云打印插件
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 顺丰快递推送增强配置 -->
                            <div class="am-form-group" id="sf_push_config_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 推送增强配置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php 
                                        $sfPushConfig = [];
                                        if (!empty($model['push_config_json'])) {
                                            $sfPushConfig = json_decode($model['push_config_json'], true);
                                            if (!is_array($sfPushConfig)) {
                                                $sfPushConfig = [];
                                            }
                                        }
                                    ?>
                                    <div style="border: 1px solid #eee; padding: 15px; border-radius: 4px;">
                                        <div style="margin-bottom: 10px;">
                                            <label style="font-weight: bold; display: block; margin-bottom: 5px;">备注信息 (remark)</label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" id="enableSfRemark" <?= (isset($sfPushConfig['enableSfRemark']) && $sfPushConfig['enableSfRemark']) ? 'checked' : '' ?>> 启用自定义配置
                                            </label>
                                            <div class="config-editor" id="sf-remark-config-editor" style="display:none; border: 1px solid #eee; padding: 10px; margin-top: 10px;">
                                                <div class="field-list" style="margin-bottom: 10px;">
                                                    <small>点击添加字段:</small><br>
                                                    <!-- JS populated -->
                                                </div>
                                                <div class="block-container" id="sf-remark-blocks" style="min-height: 50px; background: #f9f9f9; padding: 5px;">
                                                    <!-- Blocks go here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group" id="jd_product_code_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 京东主产品 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php $jdProduct = isset($model['product_json']) ? $model['product_json'] : 'ed-m-0001'; ?>
                                    <select class="tpl-form-input" name="express[product_json]" id="jd_product_code">
                                        <option value="ed-m-0001" <?= $jdProduct == 'ed-m-0001' ? 'selected' : '' ?>>京东标快 (ed-m-0001) - 推荐</option>
                                        <option value="ed-m-0002" <?= $jdProduct == 'ed-m-0002' ? 'selected' : '' ?>>京东特快 (ed-m-0002)</option>
                                        <option value="ed-m-0012" <?= $jdProduct == 'ed-m-0012' ? 'selected' : '' ?>>特惠包裹 (ed-m-0012)</option>
                                        <option value="ed-m-0059" <?= $jdProduct == 'ed-m-0059' ? 'selected' : '' ?>>电商标快 (ed-m-0059)</option>
                                        <option value="ed-m-0019" <?= $jdProduct == 'ed-m-0019' ? 'selected' : '' ?>>电商特惠 (ed-m-0019)</option>
                                        <option value="fr-m-0004" <?= $jdProduct == 'fr-m-0004' ? 'selected' : '' ?>>特快重货 (fr-m-0004)</option>
                                        <option value="fr-m-0017" <?= $jdProduct == 'fr-m-0017' ? 'selected' : '' ?>>特惠专配 (fr-m-0017)</option>
                                        <option value="LL-HD-M" <?= $jdProduct == 'LL-HD-M' ? 'selected' : '' ?>>生鲜标快 (LL-HD-M)</option>
                                        <option value="LL-SD-M" <?= $jdProduct == 'LL-SD-M' ? 'selected' : '' ?>>生鲜特快 (LL-SD-M)</option>
                                        <option value="ed-m-0005" <?= $jdProduct == 'ed-m-0005' ? 'selected' : '' ?>>同城急送 (ed-m-0005)</option>
                                    </select>
                                    <small>请选择京东物流主产品类型。标快/特快适用于大部分B2C/C2C场景。</small>
                                </div>
                            </div>

                            <!-- 京东多包裹打单配置 -->
                            <div class="am-form-group" id="jd_multibox_config_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 多包裹打单 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php 
                                        $jdMultiboxConfig = [];
                                        if (!empty($model['jd_multibox_config'])) {
                                            $jdMultiboxConfig = json_decode($model['jd_multibox_config'], true);
                                            if (!is_array($jdMultiboxConfig)) {
                                                $jdMultiboxConfig = [];
                                            }
                                        }
                                    ?>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" id="jd_multibox_enabled" name="jd_multibox_enabled" value="1"
                                            <?= (isset($jdMultiboxConfig['enabled']) && $jdMultiboxConfig['enabled']) ? 'checked' : '' ?>>
                                        该集运订单包含多个包裹，需要分别使用独立的单号进行打单
                                    </label>
                                    <small style="display: block; margin-top: 5px;">
                                        <strong>勾选：</strong>为集运订单中的每个包裹生成独立的京东物流单号，分别打单<br>
                                        <strong>不勾选（默认）：</strong>使用子母件方式下单，一个主单号对应多个子单号
                                    </small>
                                </div>
                            </div>

                            <!-- 京东云打印配置 -->
                            <div class="am-form-group" id="jd_print_config_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 云打印配置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 启动打印组件按钮 -->
                                    <div style="margin-bottom: 15px; padding: 10px; background: #f0f9ff; border: 1px solid #0e90d2; border-radius: 4px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <i class="am-icon-print" style="font-size: 24px; color: #0e90d2;"></i>
                                            <div style="flex: 1;">
                                                <strong style="display: block; margin-bottom: 5px;">京东云打印组件</strong>
                                                <small style="color: #666;">使用京东云打印功能前，请先启动打印组件</small>
                                            </div>
                                            <a href="jdprint://" class="am-btn am-btn-primary" style="white-space: nowrap;">
                                                <i class="am-icon-rocket"></i> 启动打印组件
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <?php 
                                        $jdPrintConfig = [];
                                        if (!empty($model['jd_print_config'])) {
                                            $jdPrintConfig = json_decode($model['jd_print_config'], true);
                                            if (!is_array($jdPrintConfig)) {
                                                $jdPrintConfig = [];
                                            }
                                        }
                                    ?>
                                    <div style="border: 1px solid #eee; padding: 15px; border-radius: 4px;">
                                        <!-- 任务类型配置 -->
                                        <div style="margin-bottom: 15px;">
                                            <label style="font-weight: bold; display: block; margin-bottom: 8px;">任务类型 (orderType)</label>
                                            <div style="margin-left: 10px;">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="jd_print_order_type" value="PRE_View" 
                                                        <?= (isset($jdPrintConfig['orderType']) && $jdPrintConfig['orderType'] == 'PRE_View') ? 'checked' : 'checked' ?>>
                                                    PRE_View - 打印预览（默认预览第一张面单）
                                                </label>
                                            </div>
                                            <div style="margin-left: 10px; margin-top: 5px;">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="jd_print_order_type" value="PRE_View:multi" 
                                                        <?= (isset($jdPrintConfig['orderType']) && $jdPrintConfig['orderType'] == 'PRE_View:multi') ? 'checked' : '' ?>>
                                                    PRE_View:multi - 预览所有面单
                                                </label>
                                            </div>
                                            <div style="margin-left: 10px; margin-top: 5px;">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="jd_print_order_type" value="GET_Printers" 
                                                        <?= (isset($jdPrintConfig['orderType']) && $jdPrintConfig['orderType'] == 'GET_Printers') ? 'checked' : '' ?>>
                                                    GET_Printers - 获取打印机列表
                                                </label>
                                            </div>
                                            <div style="margin-left: 10px; margin-top: 5px;">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="jd_print_order_type" value="PRINT" 
                                                        <?= (isset($jdPrintConfig['orderType']) && $jdPrintConfig['orderType'] == 'PRINT') ? 'checked' : '' ?>>
                                                    PRINT - 打印
                                                </label>
                                            </div>
                                        </div>

                                        <!-- 启用标准模板开关 -->
                                        <div style="margin-bottom: 15px;">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" id="jd_use_standard_template" 
                                                    <?= (isset($jdPrintConfig['tempUrl']) && !empty($jdPrintConfig['tempUrl'])) ? 'checked' : '' ?>>
                                                <strong>启用标准模板 (tempUrl)</strong>
                                            </label>
                                            <small style="display: block; margin-top: 5px; color: #666;">
                                                勾选后可以指定标准模板URL，不勾选则使用京东默认模板
                                            </small>
                                        </div>

                                        <!-- 标准模板URL -->
                                        <div id="jd_standard_template_group" style="margin-bottom: 15px; <?= (isset($jdPrintConfig['tempUrl']) && !empty($jdPrintConfig['tempUrl'])) ? '' : 'display: none;' ?>">
                                            <label style="font-weight: bold; display: block; margin-bottom: 5px;">标准模板URL (tempUrl)</label>
                                            <input type="text" class="tpl-form-input" id="jd_temp_url" 
                                                value="<?= isset($jdPrintConfig['tempUrl']) ? htmlspecialchars($jdPrintConfig['tempUrl']) : 'https://template-content.jd.com/template-oss?tempCode=jdkd76x130isv' ?>" 
                                                placeholder="输入标准模板URL">
                                            <small style="display: block; margin-top: 5px;">
                                                默认: https://template-content.jd.com/template-oss?tempCode=jdkd76x130isv
                                            </small>
                                        </div>

                                        <!-- 启用自定义区模板开关 -->
                                        <div style="margin-bottom: 15px;">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" id="jd_use_custom_template" 
                                                    <?= (isset($jdPrintConfig['customTempUrl']) && !empty($jdPrintConfig['customTempUrl'])) ? 'checked' : '' ?>>
                                                <strong>启用自定义区模板 (customTempUrl)</strong>
                                            </label>
                                            <small style="display: block; margin-top: 5px; color: #666;">
                                                勾选后可以添加自定义区模板，用于显示额外信息
                                            </small>
                                        </div>

                                        <!-- 自定义区模板URL -->
                                        <div id="jd_custom_template_group" style="margin-bottom: 15px; <?= (isset($jdPrintConfig['customTempUrl']) && !empty($jdPrintConfig['customTempUrl'])) ? '' : 'display: none;' ?>">
                                            <label style="font-weight: bold; display: block; margin-bottom: 5px;">自定义区模板URL (customTempUrl)</label>
                                            <input type="text" class="tpl-form-input" id="jd_custom_temp_url" 
                                                value="<?= isset($jdPrintConfig['customTempUrl']) ? htmlspecialchars($jdPrintConfig['customTempUrl']) : '' ?>" 
                                                placeholder="输入自定义区模板URL">
                                            <small style="display: block; margin-top: 5px;">
                                                用于自定义打印模板的URL地址，可选配置
                                            </small>
                                        </div>

                                        <!-- 打印机选择开关 -->
                                        <div style="margin-bottom: 15px;">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" id="jd_use_specific_printer" 
                                                    <?= (isset($jdPrintConfig['printName']) && !empty($jdPrintConfig['printName'])) ? 'checked' : '' ?>>
                                                <strong>指定打印机</strong>
                                            </label>
                                            <small style="display: block; margin-top: 5px; color: #666;">
                                                勾选后可以选择特定打印机，不勾选则使用系统默认打印机
                                            </small>
                                        </div>

                                        <!-- 打印机选择 -->
                                        <div id="jd_printer_selection_group" style="margin-bottom: 10px; <?= (isset($jdPrintConfig['printName']) && !empty($jdPrintConfig['printName'])) ? '' : 'display: none;' ?>">
                                            <label style="font-weight: bold; display: block; margin-bottom: 8px;">打印机选择 (printName)</label>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <select class="tpl-form-input" id="jd_print_name" style="max-width: 400px; flex: 1;"
                                                        data-saved-value="<?= isset($jdPrintConfig['printName']) ? htmlspecialchars($jdPrintConfig['printName']) : '' ?>">
                                                    <option value="">-- 选择打印机 --</option>
                                                    <!-- 打印机列表将通过 JavaScript 动态加载 -->
                                                    <?php if (isset($jdPrintConfig['printName']) && !empty($jdPrintConfig['printName'])): ?>
                                                    <option value="<?= htmlspecialchars($jdPrintConfig['printName']) ?>" selected>
                                                        <?= htmlspecialchars($jdPrintConfig['printName']) ?> (已保存)
                                                    </option>
                                                    <?php endif; ?>
                                                </select>
                                                <button type="button" class="am-btn am-btn-secondary am-btn-xs" id="jd_refresh_printers" style="white-space: nowrap;">
                                                    <i class="am-icon-refresh"></i> 刷新打印机列表
                                                </button>
                                                <div id="jd_current_printer" style="min-width: 200px; padding: 5px 10px; background: #f5f5f5; border-radius: 3px; font-size: 12px;">
                                                    <strong>当前打印机:</strong> <span id="jd_current_printer_name" style="color: #0e90d2;">
                                                        <?= isset($jdPrintConfig['printName']) && !empty($jdPrintConfig['printName']) ? htmlspecialchars($jdPrintConfig['printName']) : '未选择' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <small style="display: block; margin-top: 5px;">
                                                选择京东云打印的打印机。点击"刷新"按钮重新加载打印机列表。
                                            </small>
                                            <small id="jd_printer_loading" style="display: none; color: #0e90d2;">
                                                <i class="am-icon-spinner am-icon-spin"></i> 正在加载打印机列表...
                                            </small>
                                            <small id="jd_printer_error" style="display: none; color: #dd514c;">
                                                <i class="am-icon-warning"></i> 加载打印机列表失败
                                            </small>
                                        </div>

                                        <!-- 打印组件地址配置 -->
                                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                                            <label style="font-weight: bold; display: block; margin-bottom: 10px;">打印组件地址</label>
                                            <div style="margin-bottom: 10px;">
                                                <input type="text" class="tpl-form-input" name="express[jd_print_component_url]"
                                                    id="jd_print_component_url"
                                                    value="<?= isset($model['jd_print_component_url']) ? htmlspecialchars($model['jd_print_component_url']) : '' ?>"
                                                    placeholder="例如: ws://127.0.0.1:9113 或 ws://192.168.1.100:9113">
                                                <small style="display: block; margin-top: 5px; color: #666;">
                                                    配置京东云打印组件的 WebSocket 地址。如果为空，将使用默认地址 ws://127.0.0.1:9113
                                                </small>
                                            </div>
                                        </div>

                                        <!-- 清除云打印缓存 -->
                                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                                            <label style="font-weight: bold; display: block; margin-bottom: 10px;">缓存管理</label>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <button type="button" class="am-btn am-btn-warning am-btn-sm" id="jd_clear_cache_btn" onclick="clearJdCache()">
                                                    <i class="am-icon-trash"></i> 清除云打印缓存
                                                </button>
                                                <small id="jd_cache_status" style="color: #666;">
                                                    点击按钮清除京东云打印的缓存数据（AccessToken、打印数据、打印机列表）
                                                </small>
                                            </div>
                                            <small id="jd_cache_message" style="display: none; margin-top: 5px; padding: 5px 10px; border-radius: 3px;"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 快递管家推送增强配置 -->
                            <div id="zto_manager_config_group" style="display:none;">
                                <div class="widget-head am-cf">
                                    <div class="widget-title am-fl">快递管家推送增强配置</div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 功能开关 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <?php $pushConfig = !empty($model['push_config_json']) ? json_decode($model['push_config_json'], true) : []; ?>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enableSkuPropertiesName" <?= (isset($pushConfig['enableSkuPropertiesName']) && $pushConfig['enableSkuPropertiesName']) ? 'checked' : '' ?>> 推送包裹单号 (skuPropertiesName)
                                        </label>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enablePayDate" <?= (isset($pushConfig['enablePayDate']) && $pushConfig['enablePayDate']) ? 'checked' : '' ?>> 推送支付时间 (payDate)
                                        </label>
                                    </div>
                                </div>

                                <!-- Buyer Message Config -->
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 用户留言 (buyerMessage) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enableBuyerMessage" <?= (isset($pushConfig['enableBuyerMessage']) && $pushConfig['enableBuyerMessage']) ? 'checked' : '' ?>> 启用自定义配置
                                        </label>
                                        <div class="config-editor" id="buyer-config-editor" style="display:none; border: 1px solid #eee; padding: 10px; margin-top: 10px;">
                                            <div class="field-list" style="margin-bottom: 10px;">
                                                <small>点击添加字段:</small><br>
                                                <!-- JS populated -->
                                            </div>
                                            <div class="block-container" id="buyer-blocks" style="min-height: 50px; background: #f9f9f9; padding: 5px;">
                                                <!-- Blocks go here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Seller Message Config -->
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 后台备注 (sellerMessage) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enableSellerMessage" <?= (isset($pushConfig['enableSellerMessage']) && $pushConfig['enableSellerMessage']) ? 'checked' : '' ?>> 启用自定义配置
                                        </label>
                                        <div class="config-editor" id="seller-config-editor" style="display:none; border: 1px solid #eee; padding: 10px; margin-top: 10px;">
                                            <div class="field-list" style="margin-bottom: 10px;">
                                                <small>点击添加字段:</small><br>
                                                <!-- JS populated -->
                                            </div>
                                            <div class="block-container" id="seller-blocks" style="min-height: 50px; background: #f9f9f9; padding: 5px;">
                                                <!-- Blocks go here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 商品标题策略 (goodsTitle) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enableGoodsTitle" <?= (isset($pushConfig['enableGoodsTitle']) && $pushConfig['enableGoodsTitle']) ? 'checked' : '' ?>> 启用动态标题映射
                                        </label>
                                        <div id="goods_title_rules_container" style="margin-top: 15px; display: <?= (isset($pushConfig['enableGoodsTitle']) && $pushConfig['enableGoodsTitle']) ? 'block' : 'none' ?>;">
                                            <table class="am-table am-table-compact am-table-bordered am-table-centered">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40%;">商品标题文本</th>
                                                        <th style="width: 20%;">优先级</th>
                                                        <th style="width: 20%;">状态</th>
                                                        <th>操作</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="rules_body">
                                                    <!-- Javascript rendered -->
                                                </tbody>
                                            </table>
                                            <button type="button" class="am-btn am-btn-xs am-btn-primary am-round" onclick="addTitleRule()">
                                                <i class="am-icon-plus"></i> 添加新标题规则
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="express[push_config_json]" id="push_config_json_input" value='<?= htmlspecialchars($model['push_config_json'], ENT_QUOTES) ?>'>
                            
                            <!-- 中通快递打印机配置 -->
                            <div id="zto_printer_config_group" style="display:none;">
                                <div class="widget-head am-cf">
                                    <div class="widget-title am-fl">中通快递打印机配置</div>
                                </div>
                                <?php 
                                    $ztoPrinterConfig = [];
                                    if (!empty($model['push_config_json'])) {
                                        $pushConfigData = json_decode($model['push_config_json'], true);
                                        if (isset($pushConfigData['ztoPrinterConfig'])) {
                                            $ztoPrinterConfig = $pushConfigData['ztoPrinterConfig'];
                                        }
                                    }
                                ?>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 打印机名称 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_printer_id" 
                                               value="<?= isset($ztoPrinterConfig['printerId']) ? htmlspecialchars($ztoPrinterConfig['printerId']) : '' ?>"
                                               placeholder="打印机名称（PC端客户端打印时必填）">
                                        <small>PC端客户端打印时必填，例如：打印机名称</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 设备ID </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_device_id" 
                                               value="<?= isset($ztoPrinterConfig['deviceId']) ? htmlspecialchars($ztoPrinterConfig['deviceId']) : '' ?>"
                                               placeholder="设备ID（与二维码ID二选一）">
                                        <small>设备ID或二维码ID二者必传其一，例如：8CEEC48B18D0:52</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 二维码ID </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_qrcode_id" 
                                               value="<?= isset($ztoPrinterConfig['qrcodeId']) ? htmlspecialchars($ztoPrinterConfig['qrcodeId']) : '' ?>"
                                               placeholder="二维码ID（与设备ID二选一）">
                                        <small>设备ID或二维码ID二者必传其一，例如：epe338c5e</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 打印渠道 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_print_channel" 
                                               value="<?= isset($ztoPrinterConfig['printChannel']) ? htmlspecialchars($ztoPrinterConfig['printChannel']) : 'ZOP' ?>"
                                               placeholder="打印渠道" readonly>
                                        <small>打印渠道固定填写：ZOP</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 打印参数类型 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <?php $paramType = isset($ztoPrinterConfig['paramType']) ? $ztoPrinterConfig['paramType'] : 'DEFAULT_PRINT'; ?>
                                        <select class="tpl-form-input" id="zto_param_type" onchange="handleParamTypeChange()">
                                            <option value="DEFAULT_PRINT" <?= $paramType == 'DEFAULT_PRINT' ? 'selected' : '' ?>>DEFAULT_PRINT - 采用默认电子面单账号</option>
                                            <option value="ELEC_MARK" <?= $paramType == 'ELEC_MARK' ? 'selected' : '' ?>>ELEC_MARK - 指定电子面单和指定大头笔信息</option>
                                            <option value="ELEC_NOMARK" <?= $paramType == 'ELEC_NOMARK' ? 'selected' : '' ?>>ELEC_NOMARK - 指定电子面单和不指定大头笔信息</option>
                                            <option value="NOELEC_MARK" <?= $paramType == 'NOELEC_MARK' ? 'selected' : '' ?>>NOELEC_MARK - 不指定电子面单和指定大头笔信息</option>
                                            <option value="NOELEC_NOMARK" <?= $paramType == 'NOELEC_NOMARK' ? 'selected' : '' ?>>NOELEC_NOMARK - 不指定电子面单和不指定大头笔信息</option>
                                        </select>
                                        <small>
                                            <strong>参数类型说明：</strong><br>
                                            • DEFAULT_PRINT：使用默认电子面单账号（推荐）<br>
                                            • ELEC_MARK：需要指定电子面单账号和大头笔信息<br>
                                            • ELEC_NOMARK：需要指定电子面单账号，不需要大头笔<br>
                                            • NOELEC_MARK：需要电子面单账号密码获取运单号，需要大头笔<br>
                                            • NOELEC_NOMARK：需要电子面单账号密码获取运单号，不需要大头笔
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group" id="zto_print_mark_group" style="display:none;">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 大头笔信息 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_print_mark" 
                                               value="<?= isset($ztoPrinterConfig['printMark']) ? htmlspecialchars($ztoPrinterConfig['printMark']) : '' ?>"
                                               placeholder="大头笔信息，如：沪">
                                        <small>大头笔信息，用于快速分拣识别</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group" id="zto_print_bagaddr_group" style="display:none;">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 集包地 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_print_bagaddr" 
                                               value="<?= isset($ztoPrinterConfig['printBagaddr']) ? htmlspecialchars($ztoPrinterConfig['printBagaddr']) : '' ?>"
                                               placeholder="集包地信息">
                                        <small>集包地信息（可选）</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group" id="zto_elec_account_group" style="display:none;">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 电子面单账号 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_elec_account" 
                                               value="<?= isset($ztoPrinterConfig['elecAccount']) ? htmlspecialchars($ztoPrinterConfig['elecAccount']) : '' ?>"
                                               placeholder="电子面单账号">
                                        <small>NOELEC_MARK 和 NOELEC_NOMARK 模式下必填</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group" id="zto_elec_pwd_group" style="display:none;">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 电子面单密码 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" id="zto_elec_pwd" 
                                               value="<?= isset($ztoPrinterConfig['elecPwd']) ? htmlspecialchars($ztoPrinterConfig['elecPwd']) : '' ?>"
                                               placeholder="电子面单密码">
                                        <small>NOELEC_MARK 和 NOELEC_NOMARK 模式下必填</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 增值服务 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="zto_appreciation_enabled" id="zto_appreciation_enabled_yes" value="1" 
                                                <?= (isset($ztoPrinterConfig['appreciationEnabled']) && $ztoPrinterConfig['appreciationEnabled']) ? 'checked' : '' ?>>
                                            启用
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="zto_appreciation_enabled" id="zto_appreciation_enabled_no" value="0"
                                                <?= (!isset($ztoPrinterConfig['appreciationEnabled']) || !$ztoPrinterConfig['appreciationEnabled']) ? 'checked' : '' ?>>
                                            不启用
                                        </label>
                                        <div id="appreciation_services_config" style="border: 1px solid #eee; padding: 15px; border-radius: 4px; margin-top: 10px; display: <?= (isset($ztoPrinterConfig['appreciationEnabled']) && $ztoPrinterConfig['appreciationEnabled']) ? 'block' : 'none' ?>;">
                                            <?php 
                                                $appreciationServices = isset($ztoPrinterConfig['appreciationDTOS']) ? $ztoPrinterConfig['appreciationDTOS'] : [];
                                            ?>
                                            <div id="appreciation_services_container">
                                                <!-- 增值服务列表将通过 JavaScript 渲染 -->
                                            </div>
                                            <button type="button" class="am-btn am-btn-xs am-btn-primary am-round" onclick="addAppreciationService()">
                                                <i class="am-icon-plus"></i> 添加增值服务
                                            </button>
                                            <div style="margin-top: 10px;">
                                                <small>
                                                    <strong>增值服务类型说明：</strong><br>
                                                    1=到付 | 2=代收货款 | 6=中通标快 | 16=隐私服务 | 18=保价 | 29=中通好快<br>
                                                    <strong>增值服务金额：</strong>代收金额、保价金额、到付金额（整数，单位：元）
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 回单号 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="zto_back_bill_enabled" id="zto_back_bill_enabled_yes" value="1"
                                                <?= (isset($ztoPrinterConfig['backBillEnabled']) && $ztoPrinterConfig['backBillEnabled']) ? 'checked' : '' ?>>
                                            启用
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="zto_back_bill_enabled" id="zto_back_bill_enabled_no" value="0"
                                                <?= (!isset($ztoPrinterConfig['backBillEnabled']) || !$ztoPrinterConfig['backBillEnabled']) ? 'checked' : '' ?>>
                                            不启用
                                        </label>
                                        <div id="back_bill_code_config" style="margin-top: 10px; display: <?= (isset($ztoPrinterConfig['backBillEnabled']) && $ztoPrinterConfig['backBillEnabled']) ? 'block' : 'none' ?>;">
                                            <input type="text" class="tpl-form-input" id="zto_back_bill_code" 
                                                   value="<?= isset($ztoPrinterConfig['backBillCode']) ? htmlspecialchars($ztoPrinterConfig['backBillCode']) : '' ?>"
                                                   placeholder="回单号">
                                            <small>签单返还时填写回单号</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 用户留言 (buyerMessage) 配置 -->
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 用户留言 (buyerMessage) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <?php 
                                            $enableBuyerMessage = false;
                                            if (!empty($model['push_config_json'])) {
                                                $pushConfigData = json_decode($model['push_config_json'], true);
                                                if (isset($pushConfigData['enableBuyerMessage'])) {
                                                    $enableBuyerMessage = $pushConfigData['enableBuyerMessage'];
                                                }
                                            }
                                        ?>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="zto_enableBuyerMessage" <?= $enableBuyerMessage ? 'checked' : '' ?>> 启用自定义配置
                                        </label>
                                        <div class="config-editor" id="zto-buyer-config-editor" style="display:<?= $enableBuyerMessage ? 'block' : 'none' ?>; border: 1px solid #eee; padding: 10px; margin-top: 10px;">
                                            <div class="field-list" style="margin-bottom: 10px;">
                                                <small>点击添加字段:</small><br>
                                                <!-- JS populated -->
                                            </div>
                                            <div class="block-container" id="zto-buyer-blocks" style="min-height: 50px; background: #f9f9f9; padding: 5px;">
                                                <!-- Blocks go here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 后台备注 (sellerMessage) 配置 -->
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 后台备注 (sellerMessage) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <?php 
                                            $enableSellerMessage = false;
                                            if (!empty($model['push_config_json'])) {
                                                $pushConfigData = json_decode($model['push_config_json'], true);
                                                if (isset($pushConfigData['enableSellerMessage'])) {
                                                    $enableSellerMessage = $pushConfigData['enableSellerMessage'];
                                                }
                                            }
                                        ?>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="zto_enableSellerMessage" <?= $enableSellerMessage ? 'checked' : '' ?>> 启用自定义配置
                                        </label>
                                        <div class="config-editor" id="zto-seller-config-editor" style="display:<?= $enableSellerMessage ? 'block' : 'none' ?>; border: 1px solid #eee; padding: 10px; margin-top: 10px;">
                                            <div class="field-list" style="margin-bottom: 10px;">
                                                <small>点击添加字段:</small><br>
                                                <!-- JS populated -->
                                            </div>
                                            <div class="block-container" id="zto-seller-blocks" style="min-height: 50px; background: #f9f9f9; padding: 5px;">
                                                <!-- Blocks go here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 商品标题策略 (goodsTitle) -->
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 商品标题策略 (goodsTitle) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <?php 
                                            $enableGoodsTitle = false;
                                            $goodsTitleRules = [];
                                            if (!empty($model['push_config_json'])) {
                                                $pushConfigData = json_decode($model['push_config_json'], true);
                                                if (isset($pushConfigData['enableGoodsTitle'])) {
                                                    $enableGoodsTitle = $pushConfigData['enableGoodsTitle'];
                                                }
                                                if (isset($pushConfigData['goodsTitleRules'])) {
                                                    $goodsTitleRules = $pushConfigData['goodsTitleRules'];
                                                }
                                            }
                                        ?>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="zto_enableGoodsTitle" <?= $enableGoodsTitle ? 'checked' : '' ?>> 启用动态标题映射
                                        </label>
                                        <div id="zto_goods_title_rules_container" style="margin-top: 15px; display: <?= $enableGoodsTitle ? 'block' : 'none' ?>;">
                                            <table class="am-table am-table-compact am-table-bordered am-table-centered">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40%;">商品标题文本</th>
                                                        <th style="width: 20%;">优先级</th>
                                                        <th style="width: 20%;">状态</th>
                                                        <th>操作</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="zto_rules_body">
                                                    <!-- Javascript rendered -->
                                                </tbody>
                                            </table>
                                            <button type="button" class="am-btn am-btn-xs am-btn-primary am-round" onclick="addZtoTitleRule()">
                                                <i class="am-icon-plus"></i> 添加新标题规则
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 旧的 MessageBuilder 配置（保留用于向后兼容，但隐藏） -->
                                <div class="am-form-group" style="display:none;">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 启用卖家留言模板 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="enable_seller_message" id="enable_seller_message_yes" value="1"
                                                <?= $enableSellerMessage ? 'checked' : '' ?>>
                                            启用
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="enable_seller_message" id="enable_seller_message_no" value="0"
                                                <?= !$enableSellerMessage ? 'checked' : '' ?>>
                                            不启用
                                        </label>
                                        <small>启用后，面单备注将使用下方模板构建，支持变量替换</small>
                                    </div>
                                </div>
                                
                                <div class="am-form-group" id="seller_schema_config" style="display: none;">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 卖家留言模板 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <div class="config-editor" id="zto-seller-config-editor-old" style="border: 1px solid #eee; padding: 10px;">
                                            <div class="field-list" style="margin-bottom: 10px;">
                                                <small>点击添加字段:</small><br>
                                                <!-- JS populated -->
                                            </div>
                                            <div class="block-container" id="zto-seller-blocks-old" style="min-height: 50px; background: #f9f9f9; padding: 5px;">
                                                <!-- Blocks go here -->
                                            </div>
                                        </div>
                                        <small style="display: block; margin-top: 10px;">
                                            <strong>可用字段说明：</strong><br>
                                            • 自定义文本：添加固定文本内容<br>
                                            • 订单号、创建时间、支付时间等：订单基本信息<br>
                                            • 收件人姓名、手机、地址：收件人信息<br>
                                            • 用户留言、后台备注：留言信息<br>
                                            • 商品名称、包裹列表：商品信息
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否启用 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[status]" value="0" data-am-ucheck <?= $model['status'] == 0 ?'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[status]" value="1" data-am-ucheck <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        不启用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="express[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            
                            <!-- 发货人信息配置 (新增) -->
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货人信息设置 (可选 - 用于覆盖仓库默认地址)</div>
                            </div>
                            <?php 
                                $senderData = !empty($model['sender_json']) ? json_decode($model['sender_json'], true) : [];
                            ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">发货人姓名</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input sender-field" id="s_name" name="express[sender_name]" value="<?= !empty($model['sender_name']) ? htmlspecialchars($model['sender_name']) : (isset($senderData['name']) ? htmlspecialchars($senderData['name']) : '') ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">发货人电话</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input sender-field" id="s_phone" name="express[sender_phone]" value="<?= !empty($model['sender_phone']) ? htmlspecialchars($model['sender_phone']) : (isset($senderData['phone']) ? htmlspecialchars($senderData['phone']) : '') ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">省份</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input sender-field" id="s_province" name="express[sender_province]" value="<?= !empty($model['sender_province']) ? htmlspecialchars($model['sender_province']) : (isset($senderData['province']) ? htmlspecialchars($senderData['province']) : '') ?>" placeholder="如：广西壮族自治区">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">城市</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input sender-field" id="s_city" name="express[sender_city]" value="<?= !empty($model['sender_city']) ? htmlspecialchars($model['sender_city']) : (isset($senderData['city']) ? htmlspecialchars($senderData['city']) : '') ?>" placeholder="如：防城港市">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">区/县</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input sender-field" id="s_district" name="express[sender_district]" value="<?= !empty($model['sender_district']) ? htmlspecialchars($model['sender_district']) : (isset($senderData['district']) ? htmlspecialchars($senderData['district']) : '') ?>" placeholder="如：东兴市">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">详细地址</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input sender-field" id="s_address" name="express[sender_address]" value="<?= !empty($model['sender_address']) ? htmlspecialchars($model['sender_address']) : (isset($senderData['address']) ? htmlspecialchars($senderData['address']) : '') ?>" placeholder="街道门牌号">
                                </div>
                            </div>
                            <!-- 隐藏域存储 JSON -->
                            <input type="hidden" name="express[sender_json]" id="sender_json_input" value='<?= htmlspecialchars($model['sender_json'], ENT_QUOTES) ?>'>
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
<!-- 顺丰云打印 SDK -->
<script src="https://scp-tcdn.sf-express.com/prd/sdk/lodop/2.7/SCPPrint.js"></script>

<script>
    // Dictionary
    var fieldDictionary = [
        {key: 'text', label: '自定义文本', type: 'text'},
        {key: 'order_sn', label: '订单号', type: 'field'},
        {key: 'create_time', label: '创建时间', type: 'field'},
        {key: 'pay_time', label: '支付时间', type: 'field'},
        {key: 'apply_time', label: '申请打包时间', type: 'field'},
        {key: 'pay_status', label: '支付状态', type: 'field'},
        {key: 'weight', label: '总重量', type: 'field'},
        {key: 'volume_weight', label: '体积重', type: 'field'},
        {key: 'chargeable_weight', label: '计费重量', type: 'field'},
        {key: 'warehouse_name', label: '寄送仓库', type: 'field'},
        {key: 'sub_order_count', label: '子订单数量', type: 'field'},
        {key: 'service_items', label: '打包服务项目', type: 'field'},
        {key: 'goods_name', label: '商品名称', type: 'field'},
        {key: 'buyer_remark', label: '用户留言', type: 'field'},
        {key: 'seller_remark', label: '后台备注', type: 'field'},
        {key: 'user_id', label: '用户ID', type: 'field'},
        {key: 'user_nickname', label: '用户昵称', type: 'field'},
        {key: 'shipping_mark', label: '唛头', type: 'field'},
        {key: 'receiver.name', label: '收件人姓名', type: 'field'},
        {key: 'receiver.mobile', label: '收件人手机', type: 'field'},
        {key: 'receiver.address', label: '收件地址', type: 'field'},
        {key: 'items', label: '包裹列表(循环)', type: 'loop'}
    ];
    
    // 顺丰字段字典（完整版，与中通管家保持一致）
    // 注意：buyer_remark 和 seller_remark 需要从订单数据中获取
    var sfFieldDictionary = [
        {key: 'text', label: '自定义文本', type: 'text'},
        {key: 'order_sn', label: '订单号', type: 'field'},
        {key: 'create_time', label: '创建时间', type: 'field'},
        {key: 'pay_time', label: '支付时间', type: 'field'},
        {key: 'apply_time', label: '申请打包时间', type: 'field'},
        {key: 'pay_status', label: '支付状态', type: 'field'},
        {key: 'weight', label: '总重量', type: 'field'},
        {key: 'volume_weight', label: '体积重', type: 'field'},
        {key: 'chargeable_weight', label: '计费重量', type: 'field'},
        {key: 'warehouse_name', label: '寄送仓库', type: 'field'},
        {key: 'sub_order_count', label: '子订单数量', type: 'field'},
        {key: 'service_items', label: '打包服务项目', type: 'field'},
        {key: 'goods_name', label: '商品名称', type: 'field'},
        {key: 'buyer_remark', label: '用户留言', type: 'field'},
        {key: 'seller_remark', label: '后台备注', type: 'field'},
        {key: 'user_id', label: '用户ID', type: 'field'},
        {key: 'user_nickname', label: '用户昵称', type: 'field'},
        {key: 'shipping_mark', label: '唛头', type: 'field'},
        {key: 'receiver.name', label: '收件人姓名', type: 'field'},
        {key: 'receiver.mobile', label: '收件人手机', type: 'field'},
        {key: 'receiver.address', label: '收件地址', type: 'field'},
        {key: 'items', label: '包裹列表(循环)', type: 'loop'}
    ];

    function changeexpress(_this){
      var a =  _this.options[_this.selectedIndex].value;
      $("#ditch_no").val(a);
    }

    function toggleCustomerCode() {
        var v = $('input[name="express[ditch_type]"]:checked').val();
        var $g = $('#customer_code_group');
        var $g2 = $('#account_id_group');
        var $g3 = $('#account_password_group');
        var $g4 = $('#shop_key_group');
        var $g5 = $('#sf_express_type_group');
        var $g6 = $('#jd_product_code_group');
        var $g7 = $('#sf_print_options_group');
        var $g8 = $('#sf_push_config_group');
        var $i = $('#customer_code');
        
        // Reset requirements and labels
        $i.prop('required', false);
        $('input[name="express[app_key]"]').next('small').html('顺丰填写 partnerID，中通填写 AppKey');
        $('input[name="express[app_token]"]').next('small').html('顺丰填写 appSecret，中通填写 AppSecret');
        $('input[name="express[print_url]"]').parent().prev('label').text('渠道打印地址');
        
        // Hide all optional groups first
        $g.hide();
        $g2.hide();
        $g3.hide();
        $g4.hide();
        $g5.hide();
        $g6.hide();
        $g7.hide();
        $g8.hide();
        $('#zto_manager_config_group').hide();
        $('#zto_printer_config_group').hide();
        $('#jd_multibox_config_group').hide();
        $('#jd_print_config_group').hide();

        if (v == '2') { // 中通
            $g.show();
            $g2.show();
            $g3.show();
            $('#zto_printer_config_group').show(); // 显示中通打印机配置
            
            // 重新初始化中通云打印的字段按钮（确保在显示后初始化）
            setTimeout(function() {
                console.log('🔄 中通快递渠道切换 - 重新初始化字段按钮');
                renderFieldButtonsFor('zto-buyer-blocks', fieldDictionary);
                renderFieldButtonsFor('zto-seller-blocks', fieldDictionary);
                
                // 如果复选框已勾选，确保配置区域显示
                if ($('#zto_enableBuyerMessage').is(':checked')) {
                    $('#zto-buyer-config-editor').show();
                    renderBlocks('zto-buyer-blocks', ztoBuyerSchema);
                }
                if ($('#zto_enableSellerMessage').is(':checked')) {
                    $('#zto-seller-config-editor').show();
                    renderBlocks('zto-seller-blocks', ztoSellerSchema);
                }
            }, 100);
        } else if (v == '3') { // 中通管家
            $g4.show();
            $('#zto_manager_config_group').show();
        } else if (v == '4') { // 顺丰快递
            $g.show(); // 显示客户编号（月结卡号）
            $g5.show(); // 显示快递产品类型选择器
            $g7.show(); // 显示云打印选项配置
            $g8.show(); // 显示推送增强配置
        } else if (v == '5') { // 京东快递
            $g.show(); // 显示客户编号 (CustomerCode)
            $g6.show(); // 显示京东产品选择器
            $('#jd_multibox_config_group').show(); // 显示京东多包裹打单配置
            $('#jd_print_config_group').show(); // 显示京东云打印配置
            
            // Update Labels for JD
            $('input[name="express[app_key]"]').next('small').html('填写京东 <strong>AppKey</strong>');
            $('input[name="express[app_token]"]').next('small').html('填写京东 <strong>AppSecret</strong>');
            $('input[name="express[customer_code]"]').next('small').html('填写京东 <strong>CustomerCode (商家编码)</strong>');
            $('input[name="express[print_url]"]').parent().prev('label').text('京东 AccessToken');
        }
    }

    // --- 快递管家增强配置 JS ---
    var titleRules = <?= isset($pushConfig['goodsTitleRules']) ? json_encode($pushConfig['goodsTitleRules']) : '[]' ?>;
    var buyerSchema = <?= isset($pushConfig['buyerSchema']) ? json_encode($pushConfig['buyerSchema']) : '[]' ?>;
    var sellerSchema = <?= isset($pushConfig['sellerSchema']) ? json_encode($pushConfig['sellerSchema']) : '[]' ?>;
    
    // 顺丰推送增强配置
    var sfRemarkSchema = <?= isset($sfPushConfig['sfRemarkSchema']) ? json_encode($sfPushConfig['sfRemarkSchema']) : '[]' ?>;
    
    // 中通云打印 MessageBuilder 配置
    var ztoBuyerSchema = [];
    var ztoSellerSchema = [];
    var ztoTitleRules = [];
    <?php 
        if (!empty($model['push_config_json'])) {
            $ztoPushConfig = json_decode($model['push_config_json'], true);
            // buyerMessage schema
            if (isset($ztoPushConfig['ztoBuyerSchema']) && is_array($ztoPushConfig['ztoBuyerSchema'])) {
                echo "ztoBuyerSchema = " . json_encode($ztoPushConfig['ztoBuyerSchema']) . ";";
            }
            // sellerMessage schema
            if (isset($ztoPushConfig['ztoSellerSchema']) && is_array($ztoPushConfig['ztoSellerSchema'])) {
                echo "ztoSellerSchema = " . json_encode($ztoPushConfig['ztoSellerSchema']) . ";";
            }
            // 兼容旧的字符串格式
            elseif (isset($ztoPushConfig['sellerSchema']) && is_string($ztoPushConfig['sellerSchema'])) {
                echo "ztoSellerSchema = [{type: 'text', value: " . json_encode($ztoPushConfig['sellerSchema']) . "}];";
            }
            // 商品标题规则
            if (isset($ztoPushConfig['goodsTitleRules']) && is_array($ztoPushConfig['goodsTitleRules'])) {
                echo "ztoTitleRules = " . json_encode($ztoPushConfig['goodsTitleRules']) . ";";
            }
        }
    ?>


    function renderFieldButtonsFor(containerId, dictionary) {
        var dict = dictionary || fieldDictionary;
        var html = '<small>点击添加字段:</small><br>';
        dict.forEach(function(field) {
            html += `<button type="button" class="am-btn am-btn-xs am-btn-default am-round" style="margin-right:5px; margin-bottom:5px;" onclick="addBlock('${containerId}', '${field.type}', '${field.key}', '${field.label}')">${field.label}</button>`;
        });
        html += '<br><small>点击上方按钮添加到对应配置区域</small>';
        
        // 查找对应容器的 field-list 元素
        var $container = $('#' + containerId);
        var $fieldList = $container.prev('.field-list');
        
        // 如果找不到，尝试在父元素中查找
        if ($fieldList.length === 0) {
            $fieldList = $container.parent().find('.field-list');
        }
        
        if ($fieldList.length > 0) {
            $fieldList.html(html);
            console.log('✅ 成功渲染字段按钮到:', containerId, '按钮数量:', dict.length);
        } else {
            console.error('❌ 找不到 field-list 元素，容器ID:', containerId);
        }
    }

    function renderBlocks(containerId, schema) {
        var $container = $('#' + containerId);
        $container.empty();
        if (!schema) return;
        schema.forEach(function(block, index) {
            var label = block.type === 'text' ? '文本' : (block.label || block.key);
            var value = block.type === 'text' ? (block.value || '') : block.key;
            var prefix = block.prefix || '';
            var suffix = block.suffix || '';
            
            var html = `<div class="block-item" style="background:#fff; border:1px solid #ddd; padding:5px; margin-bottom:5px; display:flex; align-items:center;">
                <span class="am-badge am-badge-primary am-radius" style="margin-right:5px;">${label}</span>
                <input type="hidden" class="block-type" value="${block.type}">
                <input type="hidden" class="block-key" value="${block.key}">
                
                ${block.type === 'text' ? 
                    `<input type="text" class="block-value am-form-field am-input-sm" style="width:150px; display:inline-block;" value="${value}" placeholder="文本内容">` :
                    `<div style="display:flex; align-items:center;">
                        <input type="text" class="block-prefix am-form-field am-input-sm" style="width:80px;" value="${prefix}" placeholder="前缀">
                        <span style="margin:0 5px;">${value}</span>
                        <input type="text" class="block-suffix am-form-field am-input-sm" style="width:80px;" value="${suffix}" placeholder="后缀">
                     </div>`
                }
                
                <button type="button" class="am-btn am-btn-xs am-btn-danger am-round" style="margin-left:auto;" onclick="$(this).parent().remove(); updatePushConfigJson();">x</button>
            </div>`;
            $container.append(html);
        });
    }

    function addBlock(containerId, type, key, label) {
        var block = {type: type, key: key, label: label};
        if (type === 'text') block.value = ' - ';
        var schema = getSchemaFromBlocks(containerId);
        schema.push(block);
        renderBlocks(containerId, schema);
        updatePushConfigJson();
    }

    function getSchemaFromBlocks(containerId) {
        var schema = [];
        $('#' + containerId).find('.block-item').each(function() {
            var type = $(this).find('.block-type').val();
            var block = {type: type};
            if (type === 'text') {
                block.value = $(this).find('.block-value').val();
            } else {
                block.key = $(this).find('.block-key').val();
                block.prefix = $(this).find('.block-prefix').val();
                block.suffix = $(this).find('.block-suffix').val();
            }
            schema.push(block);
        });
        return schema;
    }

    function renderRules() {
        var html = '';
        titleRules.forEach(function(rule, index) {
            html += `<tr>
                <td><input type="text" class="am-form-field am-input-sm" value="${rule.title}" onchange="updateRule(${index}, 'title', this.value)"></td>
                <td><input type="number" class="am-form-field am-input-sm" value="${rule.priority}" onchange="updateRule(${index}, 'priority', this.value)"></td>
                <td>
                    <select class="am-input-sm" onchange="updateRule(${index}, 'status', this.value)">
                        <option value="1" ${rule.status == 1 ? 'selected' : ''}>启用</option>
                        <option value="0" ${rule.status == 0 ? 'selected' : ''}>禁用</option>
                    </select>
                </td>
                <td><button type="button" class="am-btn am-btn-xs am-btn-danger" onclick="removeRule(${index})">删除</button></td>
            </tr>`;
        });
        $('#rules_body').html(html);
        updatePushConfigJson();
    }

    function addTitleRule() {
        titleRules.push({title: '默认商品', priority: 10, status: 1});
        renderRules();
    }

    function updateRule(index, field, value) {
        titleRules[index][field] = value;
        updatePushConfigJson();
    }

    function removeRule(index) {
        titleRules.splice(index, 1);
        renderRules();
    }

    // 中通快递商品标题策略函数
    // ztoTitleRules 已在上面从配置中加载，不要重复声明
    
    function renderZtoRules() {
        var html = '';
        ztoTitleRules.forEach(function(rule, index) {
            html += `<tr>
                <td><input type="text" class="am-form-field am-input-sm" value="${rule.title}" onchange="updateZtoRule(${index}, 'title', this.value)"></td>
                <td><input type="number" class="am-form-field am-input-sm" value="${rule.priority}" onchange="updateZtoRule(${index}, 'priority', this.value)"></td>
                <td>
                    <select class="am-input-sm" onchange="updateZtoRule(${index}, 'status', this.value)">
                        <option value="1" ${rule.status == 1 ? 'selected' : ''}>启用</option>
                        <option value="0" ${rule.status == 0 ? 'selected' : ''}>禁用</option>
                    </select>
                </td>
                <td><button type="button" class="am-btn am-btn-xs am-btn-danger" onclick="removeZtoRule(${index})">删除</button></td>
            </tr>`;
        });
        $('#zto_rules_body').html(html);
        updatePushConfigJson();
    }

    function addZtoTitleRule() {
        ztoTitleRules.push({title: '默认商品', priority: 10, status: 1});
        renderZtoRules();
    }

    function updateZtoRule(index, field, value) {
        ztoTitleRules[index][field] = value;
        updatePushConfigJson();
    }

    function removeZtoRule(index) {
        ztoTitleRules.splice(index, 1);
        renderZtoRules();
    }

    function updatePushConfigJson() {
        var ditchType = $('input[name="express[ditch_type]"]:checked').val();
        var config = {};
        
        console.log('📝 更新配置 JSON，渠道类型:', ditchType);
        
        if (ditchType == '2') {
            // 中通快递打印机配置
            var buyerSchema = getSchemaFromBlocks('zto-buyer-blocks');
            var sellerSchema = getSchemaFromBlocks('zto-seller-blocks');
            
            console.log('中通快递配置:');
            console.log('- buyerMessage 启用:', $('#zto_enableBuyerMessage').is(':checked'));
            console.log('- buyerSchema:', buyerSchema);
            console.log('- sellerMessage 启用:', $('#zto_enableSellerMessage').is(':checked'));
            console.log('- sellerSchema:', sellerSchema);
            
            config = {
                ztoPrinterConfig: {
                    printerId: $('#zto_printer_id').val(),
                    deviceId: $('#zto_device_id').val(),
                    qrcodeId: $('#zto_qrcode_id').val(),
                    printChannel: $('#zto_print_channel').val() || 'ZOP',
                    paramType: $('#zto_param_type').val() || 'DEFAULT_PRINT',
                    printMark: $('#zto_print_mark').val(),
                    printBagaddr: $('#zto_print_bagaddr').val(),
                    elecAccount: $('#zto_elec_account').val(),
                    elecPwd: $('#zto_elec_pwd').val(),
                    appreciationEnabled: $('input[name="zto_appreciation_enabled"]:checked').val() == '1',
                    appreciationDTOS: $('input[name="zto_appreciation_enabled"]:checked').val() == '1' ? getAppreciationServices() : [],
                    backBillEnabled: $('input[name="zto_back_bill_enabled"]:checked').val() == '1',
                    backBillCode: $('input[name="zto_back_bill_enabled"]:checked').val() == '1' ? $('#zto_back_bill_code').val() : ''
                },
                // MessageBuilder 配置（积木格式）
                enableBuyerMessage: $('#zto_enableBuyerMessage').is(':checked'),
                ztoBuyerSchema: buyerSchema,
                enableSellerMessage: $('#zto_enableSellerMessage').is(':checked'),
                ztoSellerSchema: sellerSchema,
                // 商品标题策略
                enableGoodsTitle: $('#zto_enableGoodsTitle').is(':checked'),
                goodsTitleRules: ztoTitleRules
            };
        } else if (ditchType == '3') {
            // 中通管家配置
            config = {
                enableSkuPropertiesName: $('#enableSkuPropertiesName').is(':checked'),
                enablePayDate: $('#enablePayDate').is(':checked'),
                enableBuyerMessage: $('#enableBuyerMessage').is(':checked'),
                enableSellerMessage: $('#enableSellerMessage').is(':checked'),
                enableGoodsTitle: $('#enableGoodsTitle').is(':checked'),
                goodsTitleRules: titleRules,
                buyerSchema: getSchemaFromBlocks('buyer-blocks'),
                sellerSchema: getSchemaFromBlocks('seller-blocks')
            };
        } else if (ditchType == '4') {
            // 顺丰快递配置
            config = {
                enableSfRemark: $('#enableSfRemark').is(':checked'),
                sfRemarkSchema: getSchemaFromBlocks('sf-remark-blocks')
            };
        }
        
        var configJson = JSON.stringify(config);
        $('#push_config_json_input').val(configJson);
        console.log('✅ 配置已更新到隐藏字段');
        console.log('配置 JSON:', configJson);
    }
    
    /**
     * 获取增值服务列表
     */
    function getAppreciationServices() {
        var services = [];
        $('#appreciation_services_container .appreciation-service-item').each(function() {
            var type = $(this).find('.service-type').val();
            var amount = $(this).find('.service-amount').val();
            if (type) {
                services.push({
                    type: parseInt(type),
                    amount: amount ? parseFloat(amount) : 0
                });
            }
        });
        return services;
    }
    
    /**
     * 添加增值服务
     */
    function addAppreciationService(type, amount) {
        type = type || '';
        amount = amount || '';
        
        var html = `
            <div class="appreciation-service-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                <div class="am-g">
                    <div class="am-u-sm-5">
                        <label>服务类型：</label>
                        <select class="am-form-field service-type" onchange="updatePushConfigJson()">
                            <option value="">请选择</option>
                            <option value="1" ${type == '1' ? 'selected' : ''}>到付</option>
                            <option value="2" ${type == '2' ? 'selected' : ''}>代收货款</option>
                            <option value="6" ${type == '6' ? 'selected' : ''}>中通标快</option>
                            <option value="16" ${type == '16' ? 'selected' : ''}>隐私服务</option>
                            <option value="18" ${type == '18' ? 'selected' : ''}>保价</option>
                            <option value="29" ${type == '29' ? 'selected' : ''}>中通好快</option>
                        </select>
                    </div>
                    <div class="am-u-sm-5">
                        <label>金额（元）：</label>
                        <input type="number" step="1" min="0" class="am-form-field service-amount" value="${amount}" placeholder="整数金额" onchange="updatePushConfigJson()">
                        <small style="display:block; color:#999;">代收金额/保价金额/到付金额</small>
                    </div>
                    <div class="am-u-sm-2">
                        <button type="button" class="am-btn am-btn-danger am-btn-xs" onclick="removeAppreciationService(this)" style="margin-top: 20px;">
                            <i class="am-icon-trash"></i> 删除
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#appreciation_services_container').append(html);
        updatePushConfigJson();
    }
    
    /**
     * 删除增值服务
     */
    function removeAppreciationService(btn) {
        $(btn).closest('.appreciation-service-item').remove();
        updatePushConfigJson();
    }
    
    /**
     * 处理 paramType 变化，控制相关字段显示/隐藏
     */
    function handleParamTypeChange() {
        var paramType = $('#zto_param_type').val();
        
        // 隐藏所有条件字段
        $('#zto_print_mark_group').hide();
        $('#zto_print_bagaddr_group').hide();
        $('#zto_elec_account_group').hide();
        $('#zto_elec_pwd_group').hide();
        
        // 根据 paramType 显示对应字段
        switch(paramType) {
            case 'ELEC_MARK':
                // 指定电子面单和指定大头笔信息
                $('#zto_print_mark_group').show();
                $('#zto_print_bagaddr_group').show();
                break;
            case 'ELEC_NOMARK':
                // 指定电子面单和不指定大头笔信息
                // 不需要额外字段
                break;
            case 'NOELEC_MARK':
                // 不指定电子面单和指定大头笔信息（需传电子面单账号密码）
                $('#zto_print_mark_group').show();
                $('#zto_print_bagaddr_group').show();
                $('#zto_elec_account_group').show();
                $('#zto_elec_pwd_group').show();
                break;
            case 'NOELEC_NOMARK':
                // 不指定电子面单和不指定大头笔信息（需传电子面单账号密码）
                $('#zto_elec_account_group').show();
                $('#zto_elec_pwd_group').show();
                break;
            case 'DEFAULT_PRINT':
            default:
                // 采用默认电子面单账号，不需要额外字段
                break;
        }
        
        // 更新配置JSON
        updatePushConfigJson();
    }
    
    /**
     * 渲染已保存的增值服务
     */
    function renderAppreciationServices() {
        var ditchType = $('input[name="express[ditch_type]"]:checked').val();
        if (ditchType != '2') return;
        
        try {
            var pushConfigJson = $('#push_config_json_input').val();
            if (pushConfigJson) {
                var config = JSON.parse(pushConfigJson);
                if (config.ztoPrinterConfig && config.ztoPrinterConfig.appreciationDTOS) {
                    config.ztoPrinterConfig.appreciationDTOS.forEach(function(service) {
                        addAppreciationService(service.type, service.amount);
                    });
                }
            }
        } catch (e) {
            console.error('解析增值服务配置失败:', e);
        }
    }

    function updateSenderJson() {
        var data = {
            name: $('#s_name').val(),
            phone: $('#s_phone').val(),
            province: $('#s_province').val(),
            city: $('#s_city').val(),
            district: $('#s_district').val(),
            address: $('#s_address').val()
        };
        // Simple validation: If no province, assume empty config to allow fallback
        if (!data.province) {
            $('#sender_json_input').val('');
        } else {
            $('#sender_json_input').val(JSON.stringify(data));
        }
    }

    function updateSfPrintOptions() {
        var options = {
            enable_preview: $('#sf_print_preview').is(':checked'),
            enable_select_printer: $('#sf_print_select_printer').is(':checked'),
            default_printer: $('#sf_default_printer').val()
        };
        
        // 创建隐藏字段保存 JSON 数据
        var $hiddenInput = $('input[name="express[sf_print_options]"]');
        if ($hiddenInput.length === 0) {
            $hiddenInput = $('<input type="hidden" name="express[sf_print_options]">');
            $('#my-form').append($hiddenInput);
        }
        $hiddenInput.val(JSON.stringify(options));
    }

    /**
     * 更新京东多包裹打单配置
     */
    function updateJdMultiboxConfig() {
        var config = {
            enabled: $('#jd_multibox_enabled').is(':checked')
        };
        
        // 创建隐藏字段保存 JSON 数据
        var $hiddenInput = $('input[name="express[jd_multibox_config]"]');
        if ($hiddenInput.length === 0) {
            $hiddenInput = $('<input type="hidden" name="express[jd_multibox_config]">');
            $('#my-form').append($hiddenInput);
        }
        $hiddenInput.val(JSON.stringify(config));
    }

    /**
     * 更新京东云打印配置
     */
    function updateJdPrintConfig() {
        // 如果未勾选"指定打印机"，则 printName 为空（使用默认打印机）
        var useSpecificPrinter = $('#jd_use_specific_printer').is(':checked');
        var printName = useSpecificPrinter ? $('#jd_print_name').val() : '';
        
        // 如果未勾选"启用标准模板"，则 tempUrl 为空
        var useStandardTemplate = $('#jd_use_standard_template').is(':checked');
        var tempUrl = useStandardTemplate ? $('#jd_temp_url').val() : '';
        
        // 如果未勾选"启用自定义区模板"，则 customTempUrl 为空
        var useCustomTemplate = $('#jd_use_custom_template').is(':checked');
        var customTempUrl = useCustomTemplate ? $('#jd_custom_temp_url').val() : '';
        
        var config = {
            orderType: $('input[name="jd_print_order_type"]:checked').val() || 'PRE_View',
            tempUrl: tempUrl,
            customTempUrl: customTempUrl,
            printName: printName
        };
        
        // 创建隐藏字段保存 JSON 数据
        var $hiddenInput = $('input[name="express[jd_print_config]"]');
        if ($hiddenInput.length === 0) {
            $hiddenInput = $('<input type="hidden" name="express[jd_print_config]">');
            $('#my-form').append($hiddenInput);
        }
        $hiddenInput.val(JSON.stringify(config));
    }

    /**
     * 加载京东云打印机列表
     * 通过 WebSocket 连接到京东云打印服务
     */
    function loadJdPrinters() {
        // 检查是否为京东渠道
        var ditchType = $('input[name="express[ditch_type]"]:checked').val();
        if (ditchType != '5') {
            return; // 不是京东渠道，不加载
        }

        // 显示加载状态
        $('#jd_printer_loading').show();
        $('#jd_printer_error').hide();

        try {
            // WebSocket 连接参数
            var wsProtocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
            var wsPort = window.location.protocol === 'https:' ? '9114' : '9113';
            var wsUrl = wsProtocol + '://localhost:' + wsPort;
            
            console.log('🔗 正在连接到京东云打印服务: ' + wsUrl);
            
            // 创建 WebSocket 连接
            var ws = new WebSocket(wsUrl);
            var requestTimeout;
            
            ws.onopen = function() {
                console.log('✅ WebSocket 连接成功');
                
                // 发送获取打印机列表请求
                var request = {
                    orderType: 'GET_Printers'
                };
                
                console.log('📤 发送请求:', JSON.stringify(request));
                ws.send(JSON.stringify(request));
                
                // 设置请求超时（5秒）
                requestTimeout = setTimeout(function() {
                    console.error('❌ 请求超时');
                    $('#jd_printer_loading').hide();
                    $('#jd_printer_error').show().text('获取打印机列表超时，请检查京东云打印服务是否正常运行');
                    ws.close();
                }, 5000);
            };
            
            ws.onmessage = function(event) {
                clearTimeout(requestTimeout);
                console.log('📥 收到响应:', event.data);
                
                try {
                    var response = JSON.parse(event.data);
                    
                    // 检查响应状态
                    if (response.success === 'true' || response.success === true) {
                        // 成功获取打印机列表
                        if (response.detailinfo && response.detailinfo.printers && response.detailinfo.printers.length > 0) {
                            var $select = $('#jd_print_name');
                            var savedValue = $select.data('saved-value') || $select.val();
                            
                            // 清空现有选项（保留第一个默认选项）
                            $select.find('option:not(:first)').remove();
                            
                            // 添加打印机选项
                            response.detailinfo.printers.forEach(function(printerName) {
                                var option = $('<option></option>')
                                    .val(printerName)
                                    .text(printerName);
                                $select.append(option);
                            });
                            
                            // 恢复之前选中的值（如果存在）
                            if (savedValue) {
                                $select.val(savedValue);
                                // 如果恢复失败（打印机不在列表中），显示提示
                                if ($select.val() !== savedValue) {
                                    console.warn('⚠️ 已保存的打印机 "' + savedValue + '" 不在当前打印机列表中');
                                    // 添加一个临时选项显示已保存但不可用的打印机
                                    var tempOption = $('<option></option>')
                                        .val(savedValue)
                                        .text(savedValue + ' (已保存，但当前不可用)')
                                        .prop('selected', true);
                                    $select.append(tempOption);
                                }
                                // 更新隐藏字段，确保选中的打印机被保存到表单
                                updateJdPrintConfig();
                                console.log('✅ 已恢复打印机选择: ' + savedValue);
                            }
                            
                            $('#jd_printer_loading').hide();
                            console.log('✅ 成功加载 ' + response.detailinfo.printers.length + ' 个打印机');
                        } else {
                            $('#jd_printer_loading').hide();
                            $('#jd_printer_error').show().text('未获取到打印机列表，请检查京东云打印服务配置');
                            console.error('❌ 响应中没有打印机列表');
                        }
                    } else {
                        // 请求失败
                        $('#jd_printer_loading').hide();
                        var errorMsg = '获取打印机列表失败';
                        if (response.message) {
                            errorMsg += ': ' + response.message;
                        }
                        $('#jd_printer_error').show().text(errorMsg);
                        console.error('❌ 请求失败:', response);
                    }
                } catch (parseError) {
                    $('#jd_printer_loading').hide();
                    $('#jd_printer_error').show().text('解析响应数据失败: ' + parseError.message);
                    console.error('❌ JSON 解析错误:', parseError);
                }
                
                // 关闭 WebSocket 连接
                ws.close();
            };
            
            ws.onerror = function(error) {
                clearTimeout(requestTimeout);
                $('#jd_printer_loading').hide();
                $('#jd_printer_error').show().text('WebSocket 连接错误，请确保京东云打印服务已启动');
                console.error('❌ WebSocket 错误:', error);
            };
            
            ws.onclose = function() {
                console.log('🔌 WebSocket 连接已关闭');
            };
            
        } catch (error) {
            $('#jd_printer_loading').hide();
            $('#jd_printer_error').show().text('加载打印机列表时发生错误: ' + error.message);
            console.error('❌ 加载打印机列表错误:', error);
        }
    }

    /**
     * 加载顺丰云打印机列表
     * 使用顺丰 SDK 的 getPrinters() 方法
     */
    function loadSfPrinters() {
        // 检查是否为顺丰渠道
        var ditchType = $('input[name="express[ditch_type]"]:checked').val();
        if (ditchType != '4') {
            return; // 不是顺丰渠道，不加载
        }

        // 检查是否已加载 SDK
        if (typeof SCPPrint === 'undefined') {
            $('#printer_error').show().text('顺丰云打印 SDK 未加载，请确保已正确引入 SDK 文件');
            return;
        }

    /**
     * 清除京东云打印缓存
     */
    function clearJdCache() {
        var $btn = $('#jd_clear_cache_btn');
        var $msg = $('#jd_cache_message');
        
        // 禁用按钮，显示加载状态
        $btn.prop('disabled', true).html('<i class="am-icon-spinner am-icon-spin"></i> 清除中...');
        $msg.hide();
        
        // 发送 AJAX 请求到后端清除缓存
        $.ajax({
            url: '<?= url("store.tr_order/clearJdCache") ?>',
            type: 'POST',
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="am-icon-trash"></i> 清除云打印缓存');
                
                if (response.code === 1) {
                    // 成功
                    $msg.removeClass('am-alert-danger').addClass('am-alert-success')
                        .html('<i class="am-icon-check-circle"></i> ' + (response.message || '缓存已清除'))
                        .show();
                    console.log('✅ 京东云打印缓存已清除');
                } else {
                    // 失败
                    $msg.removeClass('am-alert-success').addClass('am-alert-danger')
                        .html('<i class="am-icon-warning"></i> ' + (response.message || '清除缓存失败'))
                        .show();
                    console.error('❌ 清除缓存失败:', response);
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('<i class="am-icon-trash"></i> 清除云打印缓存');
                
                var errorMsg = '清除缓存失败';
                if (status === 'timeout') {
                    errorMsg = '请求超时，请检查网络连接';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                $msg.removeClass('am-alert-success').addClass('am-alert-danger')
                    .html('<i class="am-icon-warning"></i> ' + errorMsg)
                    .show();
                console.error('❌ 清除缓存错误:', error);
            }
        });
    }

        // 显示加载状态
        $('#printer_loading').show();
        $('#printer_error').hide();

        try {
            // 获取 partnerID（客户号）
            var partnerID = $('input[name="express[app_key]"]').val();
            if (!partnerID) {
                $('#printer_loading').hide();
                $('#printer_error').show().text('请先填写客户号（partnerID）');
                return;
            }

            // 创建 SDK 实例（使用沙箱环境进行测试）
            var printSdk = new SCPPrint({
                partnerID: partnerID,
                env: 'sbox', // 沙箱环境，生产环境改为 'pro'
                notips: true // 不显示 SDK 的弹窗提示
            });

            // 调用 getPrinters 方法
            printSdk.getPrinters(function(result) {
                $('#printer_loading').hide();
                
                if (result.code === 1 && result.printers && result.printers.length > 0) {
                    // 成功获取打印机列表
                    var $select = $('#sf_default_printer');
                    var savedValue = $select.data('saved-value') || $select.val(); // 获取已保存的值
                    
                    // 清空现有选项（保留第一个默认选项）
                    $select.find('option:not(:first)').remove();
                    
                    // 添加打印机选项
                    result.printers.forEach(function(printer) {
                        var option = $('<option></option>')
                            .val(printer.name)
                            .text(printer.name + ' (序号: ' + printer.index + ')');
                        $select.append(option);
                    });
                    
                    // 恢复之前选中的值（如果存在）
                    if (savedValue) {
                        $select.val(savedValue);
                        // 如果恢复失败（打印机不在列表中），显示提示
                        if ($select.val() !== savedValue) {
                            console.warn('⚠️ 已保存的打印机 "' + savedValue + '" 不在当前打印机列表中');
                            // 添加一个临时选项显示已保存但不可用的打印机
                            var tempOption = $('<option></option>')
                                .val(savedValue)
                                .text(savedValue + ' (已保存，但当前不可用)')
                                .prop('selected', true);
                            $select.append(tempOption);
                        }
                    }
                    
                    console.log('✅ 成功加载 ' + result.printers.length + ' 个打印机');
                } else {
                    // 加载失败
                    var errorMsg = '加载打印机列表失败';
                    if (result.code === 2 || result.code === 3) {
                        errorMsg = '请先安装顺丰云打印插件';
                        if (result.downloadUrl) {
                            errorMsg += '，<a href="' + result.downloadUrl + '" target="_blank">点击下载</a>';
                        }
                    } else if (result.msg) {
                        errorMsg += ': ' + result.msg;
                    }
                    $('#printer_error').show().html(errorMsg);
                }
            });
        } catch (error) {
            $('#printer_loading').hide();
            $('#printer_error').show().text('加载打印机列表时发生错误: ' + error.message);
            console.error('❌ 加载打印机列表错误:', error);
        }
    }

    $(function () {
        $('input[name="express[ditch_type]"]').on('change', toggleCustomerCode);
        toggleCustomerCode();
        renderRules();
        renderZtoRules(); // 初始化中通快递商品标题策略表格

        renderFieldButtonsFor('buyer-blocks', fieldDictionary);
        renderFieldButtonsFor('seller-blocks', fieldDictionary);
        renderBlocks('buyer-blocks', buyerSchema);
        renderBlocks('seller-blocks', sellerSchema);
        
        // 顺丰推送增强配置初始化
        renderFieldButtonsFor('sf-remark-blocks', sfFieldDictionary);
        renderBlocks('sf-remark-blocks', sfRemarkSchema);
        
        // 中通云打印 MessageBuilder 初始化
        console.log('📝 初始化中通云打印 MessageBuilder');
        console.log('buyerSchema:', ztoBuyerSchema);
        console.log('sellerSchema:', ztoSellerSchema);
        
        renderFieldButtonsFor('zto-buyer-blocks', fieldDictionary);
        renderBlocks('zto-buyer-blocks', ztoBuyerSchema);
        renderFieldButtonsFor('zto-seller-blocks', fieldDictionary);
        renderBlocks('zto-seller-blocks', ztoSellerSchema);
        
        // 初始化显示状态
        var buyerEnabled = $('#zto_enableBuyerMessage').is(':checked');
        var sellerEnabled = $('#zto_enableSellerMessage').is(':checked');
        $('#zto-buyer-config-editor').toggle(buyerEnabled);
        $('#zto-seller-config-editor').toggle(sellerEnabled);
        
        console.log('✅ 中通云打印 MessageBuilder 初始化完成');
        console.log('buyerMessage 启用:', buyerEnabled);
        console.log('sellerMessage 启用:', sellerEnabled);

        // Listen for switch changes
        $('#enableSkuPropertiesName, #enablePayDate, #enableGoodsTitle').change(function() {
            if ($(this).attr('id') === 'enableGoodsTitle') {
                $('#goods_title_rules_container').toggle($(this).is(':checked'));
            }
            updatePushConfigJson();
        });

        $('#enableBuyerMessage').change(function() {
             $('#buyer-config-editor').toggle($(this).is(':checked'));
             updatePushConfigJson();
        });
        $('#enableSellerMessage').change(function() {
             $('#seller-config-editor').toggle($(this).is(':checked'));
             updatePushConfigJson();
        });
        
        // 顺丰 remark 配置开关
        $('#enableSfRemark').change(function() {
             $('#sf-remark-config-editor').toggle($(this).is(':checked'));
             updatePushConfigJson();
        });
        
        // 中通快递商品标题策略开关
        $('#zto_enableGoodsTitle').change(function() {
            $('#zto_goods_title_rules_container').toggle($(this).is(':checked'));
            updatePushConfigJson();
        });
        
        // Initial visibility
        $('#buyer-config-editor').toggle($('#enableBuyerMessage').is(':checked'));
        $('#seller-config-editor').toggle($('#enableSellerMessage').is(':checked'));
        $('#sf-remark-config-editor').toggle($('#enableSfRemark').is(':checked'));
        $('#zto_goods_title_rules_container').toggle($('#zto_enableGoodsTitle').is(':checked'));

        // Listen for changes in sender fields
        $('.sender-field').on('input change', updateSenderJson);
        
        // Listen for changes in SF push config
        $('#sf_push_remark').on('input change', updatePushConfigJson);
        
        // Bind input changes to update json
        $(document).on('input', '.block-value, .block-prefix, .block-suffix', function() {
            updatePushConfigJson();
        });

        // 中通打印机配置初始化
        renderAppreciationServices();
        
        // 初始化 paramType 字段显示状态
        handleParamTypeChange();
        
        // 监听中通打印机配置字段的变化
        $('#zto_printer_id, #zto_device_id, #zto_qrcode_id, #zto_print_channel, #zto_param_type, #zto_print_mark, #zto_print_bagaddr, #zto_elec_account, #zto_elec_pwd, #zto_back_bill_code').on('input change', function() {
            updatePushConfigJson();
        });
        
        // 监听增值服务开关
        $('input[name="zto_appreciation_enabled"]').on('change', function() {
            var enabled = $(this).val() == '1';
            $('#appreciation_services_config').toggle(enabled);
            updatePushConfigJson();
        });
        
        // 监听回单号开关
        $('input[name="zto_back_bill_enabled"]').on('change', function() {
            var enabled = $(this).val() == '1';
            $('#back_bill_code_config').toggle(enabled);
            updatePushConfigJson();
        });
        
        // 监听 MessageBuilder 开关
        $('#zto_enableBuyerMessage').on('change', function() {
            var enabled = $(this).is(':checked');
            $('#zto-buyer-config-editor').toggle(enabled);
            // 当显示时，重新渲染字段按钮
            if (enabled) {
                setTimeout(function() {
                    console.log('🔄 重新渲染 buyerMessage 字段按钮');
                    renderFieldButtonsFor('zto-buyer-blocks', fieldDictionary);
                    // 如果有已保存的 schema，也重新渲染积木
                    if (ztoBuyerSchema && ztoBuyerSchema.length > 0) {
                        renderBlocks('zto-buyer-blocks', ztoBuyerSchema);
                    }
                }, 50);
            }
            updatePushConfigJson();
        });
        
        $('#zto_enableSellerMessage').on('change', function() {
            var enabled = $(this).is(':checked');
            $('#zto-seller-config-editor').toggle(enabled);
            // 当显示时，重新渲染字段按钮
            if (enabled) {
                setTimeout(function() {
                    console.log('🔄 重新渲染 sellerMessage 字段按钮');
                    renderFieldButtonsFor('zto-seller-blocks', fieldDictionary);
                    // 如果有已保存的 schema，也重新渲染积木
                    if (ztoSellerSchema && ztoSellerSchema.length > 0) {
                        renderBlocks('zto-seller-blocks', ztoSellerSchema);
                    }
                }, 50);
            }
            updatePushConfigJson();
        });
        
        // 监听中通云打印积木输入变化
        $(document).on('input', '#zto-buyer-blocks .block-value, #zto-buyer-blocks .block-prefix, #zto-buyer-blocks .block-suffix', function() {
            updatePushConfigJson();
        });
        $(document).on('input', '#zto-seller-blocks .block-value, #zto-seller-blocks .block-prefix, #zto-seller-blocks .block-suffix', function() {
            updatePushConfigJson();
        });
        
        // 顺丰打印机列表加载
        // 当选择顺丰渠道时，自动加载打印机列表
        $('input[name="express[ditch_type]"]').on('change', function() {
            if ($(this).val() === '4') {
                // 延迟加载，确保 partnerID 已填写
                setTimeout(loadSfPrinters, 500);
            }
        });
        
        // 刷新打印机列表按钮
        $('#refresh_printers').on('click', function() {
            loadSfPrinters();
        });
        
        // 京东打印机列表刷新按钮
        $('#jd_refresh_printers').on('click', function() {
            loadJdPrinters();
        });
        
        // 监听京东云打印配置字段的变化
        $('input[name="jd_print_order_type"], #jd_temp_url, #jd_custom_temp_url, #jd_print_name').on('input change', function() {
            updateJdPrintConfig();
        });
        
        // 监听京东多包裹打单勾选框的变化
        $('#jd_multibox_enabled').on('change', function() {
            updateJdMultiboxConfig();
        });
        
        // 监听京东"启用标准模板"开关的变化
        $('#jd_use_standard_template').on('change', function() {
            var isChecked = $(this).is(':checked');
            if (isChecked) {
                $('#jd_standard_template_group').slideDown(200);
            } else {
                $('#jd_standard_template_group').slideUp(200);
            }
            // 更新配置
            updateJdPrintConfig();
        });
        
        // 监听京东"启用自定义区模板"开关的变化
        $('#jd_use_custom_template').on('change', function() {
            var isChecked = $(this).is(':checked');
            if (isChecked) {
                $('#jd_custom_template_group').slideDown(200);
            } else {
                $('#jd_custom_template_group').slideUp(200);
            }
            // 更新配置
            updateJdPrintConfig();
        });
        
        // 监听京东"指定打印机"开关的变化
        $('#jd_use_specific_printer').on('change', function() {
            var isChecked = $(this).is(':checked');
            if (isChecked) {
                $('#jd_printer_selection_group').slideDown(200);
            } else {
                $('#jd_printer_selection_group').slideUp(200);
                // 清空打印机选择
                $('#jd_print_name').val('');
            }
            // 更新配置
            updateJdPrintConfig();
        });
        
        // 页面加载时，如果是顺丰渠道，自动加载打印机列表
        if ($('input[name="express[ditch_type]"]:checked').val() === '4') {
            setTimeout(loadSfPrinters, 1000); // 延迟1秒，确保页面完全加载
        }
        
        // 页面加载时，如果是京东渠道，自动加载打印机列表
        if ($('input[name="express[ditch_type]"]:checked').val() === '5') {
            setTimeout(loadJdPrinters, 1000); // 延迟1秒，确保页面完全加载
        }

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            // 自定义验证函数
            validation: function () {
                console.log('🚀 表单提交验证开始');
                updatePushConfigJson();
                updateSenderJson();
                updateSfPrintOptions();
                updateJdMultiboxConfig();
                updateJdPrintConfig();
                
                // 验证配置是否正确保存
                var pushConfigValue = $('#push_config_json_input').val();
                console.log('📝 push_config_json 字段值长度:', pushConfigValue.length);
                console.log('📝 push_config_json 内容预览:', pushConfigValue.substring(0, 200) + '...');
                
                if (pushConfigValue.length > 0) {
                    try {
                        var config = JSON.parse(pushConfigValue);
                        console.log('✅ JSON 解析成功');
                        console.log('配置对象:', config);
                        
                        // 检查中通快递配置
                        if (config.ztoBuyerSchema || config.ztoSellerSchema) {
                            console.log('✅ 检测到中通云打印配置');
                            console.log('- buyerMessage 启用:', config.enableBuyerMessage);
                            console.log('- buyerSchema 长度:', config.ztoBuyerSchema ? config.ztoBuyerSchema.length : 0);
                            console.log('- sellerMessage 启用:', config.enableSellerMessage);
                            console.log('- sellerSchema 长度:', config.ztoSellerSchema ? config.ztoSellerSchema.length : 0);
                        }
                    } catch (e) {
                        console.error('❌ JSON 解析失败:', e.message);
                    }
                } else {
                    console.warn('⚠️ push_config_json 字段为空');
                }
                
                return true;
            }
        });

        // 提交前强制同步一次配置 (Backup)
        $('.j-submit').on('click', function() {
            console.log('🖱️ 提交按钮被点击');
            updatePushConfigJson();
            updateSenderJson();
            updateSfPrintOptions();
            updateJdMultiboxConfig();
            updateJdPrintConfig();
            
            // 再次验证
            setTimeout(function() {
                var pushConfigValue = $('#push_config_json_input').val();
                console.log('🔍 最终检查 - push_config_json 长度:', pushConfigValue.length);
            }, 100);
        });

    });
</script>

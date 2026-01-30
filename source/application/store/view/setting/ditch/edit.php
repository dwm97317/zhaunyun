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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 客户号（Key） </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[app_key]"
                                           value="<?= $model['app_key'] ?>" required>
                                    <small>顺丰快递填写 <strong>partnerID</strong>，中通填写 AppKey</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 密钥（Token） </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[app_token]"
                                           value="<?= $model['app_token'] ?>" required>
                                    <small>顺丰快递填写 <strong>appSecret</strong>（用于生成msgDigest签名），中通填写 AppSecret</small>
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
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enableBuyerMessage" <?= (isset($pushConfig['enableBuyerMessage']) && $pushConfig['enableBuyerMessage']) ? 'checked' : '' ?>> 推送用户留言 (buyerMessage)
                                        </label>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" id="enableSellerMessage" <?= (isset($pushConfig['enableSellerMessage']) && $pushConfig['enableSellerMessage']) ? 'checked' : '' ?>> 推送后台备注 (sellerMessage)
                                        </label>
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
<script>
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
        $('#zto_manager_config_group').hide();

        if (v == '2') { // 中通
            $g.show();
            $g2.show();
            $g3.show();
        } else if (v == '3') { // 中通管家
            $g4.show();
            $('#zto_manager_config_group').show();
        } else if (v == '4') { // 顺丰快递
            $g.show(); // 显示客户编号（月结卡号）
            $g5.show(); // 显示快递产品类型选择器
        } else if (v == '5') { // 京东快递
            $g.show(); // 显示客户编号 (CustomerCode)
            $g6.show(); // 显示京东产品选择器
            
            // Update Labels for JD
            $('input[name="express[app_key]"]').next('small').html('填写京东 <strong>AppKey</strong>');
            $('input[name="express[app_token]"]').next('small').html('填写京东 <strong>AppSecret</strong>');
            $('input[name="express[customer_code]"]').next('small').html('填写京东 <strong>CustomerCode (商家编码)</strong>');
            $('input[name="express[print_url]"]').parent().prev('label').text('京东 AccessToken');
        }
    }

    // --- 快递管家增强配置 JS ---
    var titleRules = <?= isset($pushConfig['goodsTitleRules']) ? json_encode($pushConfig['goodsTitleRules']) : '[]' ?>;

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

    function updatePushConfigJson() {
        var config = {
            enableSkuPropertiesName: $('#enableSkuPropertiesName').is(':checked'),
            enablePayDate: $('#enablePayDate').is(':checked'),
            enableBuyerMessage: $('#enableBuyerMessage').is(':checked'),
            enableSellerMessage: $('#enableSellerMessage').is(':checked'),
            enableGoodsTitle: $('#enableGoodsTitle').is(':checked'),
            goodsTitleRules: titleRules
        };
        $('#push_config_json_input').val(JSON.stringify(config));
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

    $(function () {
        $('input[name="express[ditch_type]"]').on('change', toggleCustomerCode);
        toggleCustomerCode();
        renderRules();

        // Listen for switch changes
        $('#enableSkuPropertiesName, #enablePayDate, #enableBuyerMessage, #enableSellerMessage, #enableGoodsTitle').change(function() {
            if ($(this).attr('id') === 'enableGoodsTitle') {
                $('#goods_title_rules_container').toggle($(this).is(':checked'));
            }
            updatePushConfigJson();
        });

        // Listen for changes in sender fields
        $('.sender-field').on('input change', updateSenderJson);

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        // 提交前强制同步一次配置
        $('.j-submit').on('click', function() {
            updatePushConfigJson();
            updateSenderJson();
        });

    });
</script>

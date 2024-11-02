<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">用户列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-9 am-u-sm-push-3">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $gender = $request->get('gender'); ?>
                                        <select name="gender"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择性别'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $gender === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $gender === '1' ? 'selected' : '' ?>>男
                                            </option>
                                            <option value="2"
                                                <?= $gender === '2' ? 'selected' : '' ?>>女
                                            </option>
                                            <option value="0"
                                                <?= $gender === '0' ? 'selected' : '' ?>>未知
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_id"
                                                   placeholder="请输入用户ID" value="<?= $request->get('user_id') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_code"
                                                   placeholder="请输入用户编号" value="<?= $request->get('user_code') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="nickName"
                                                   placeholder="请输入微信昵称"
                                                   value="<?= $request->get('nickName') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <!--<th>来源</th>-->
                                <th>手机号</th>
                                <th>用户余额</th>
                                <th>待结算订单数</th>
                                <th>时间</th>
                                <th width="300px">操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (isset($list) && count($list)>0): foreach ($list as $item): ?>
                                <?php $typeMap = [0=>'普通用户',1=>'入库员',2=>'分拣员',3=>'打包员',4=>'签收员',5=>'仓管员'] ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['user_id'] ?>"> 
                                    </td>
                                    <td class="am-text-middle"><?= $item['user_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <?php if($item['avatarUrl']) :?>
                                                 <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                                            <?php else:?>
                                                 <img src="assets/admin/img/head.jpg" width="72" height="72" alt="">
                                            <?php endif;?>
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['nickName'] ?> <br> 
                                        <?php if($set['is_show']!=0) :?>
                                             CODE: <span><?= $item['user_code'] ?></span>
                                        <?php endif;?>
                                    </td>
                                    <!--<?php $usource = [1=>'小程序',2=>'公众号',3=>'PC端',4=>'App'] ?>-->
                                    <!--<td class="am-text-middle"><?= $usource[$item['u_source']] ?></td>-->
                                    <td class="am-text-middle"><?= $item['mobile'] ?></td>
                                    <td class="am-text-middle"><?= $item['balance'] ?></td>
          
                                    <td class="am-text-middle"><?= $item['total'] ?></td>
                                    <td class="am-text-middle">
                                        注册时间：<?= $item['create_time'] ?><br/>
                                        最近登录：<?= $item['last_login_time'] ?><br/>
                                        最近订单：<?= $item['update_time']=='2000-01-01 00:00:00'?'暂未下单':$item['update_time'] ?><br/>
                                    </td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user/recharge')): ?>
                                                <a class="j-recharge tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   title="用户充值"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   data-balance="<?= $item['balance'] ?>"
                                                   data-points="<?= $item['points'] ?>"
                                                >
                                                    <i class="iconfont icon-qiandai"></i>
                                                    充值
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('tr_order/all_list')): ?>
                                             <a class="tpl-table-black-operation-default" target="_blank" href="<?= url('tr_order/arrearsorder', ['user_id' => $item['user_id']]) ?>">未结订单</a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.recharge/order')): ?>
                                               <a class="tpl-table-black-operation-default" target="_blank" href="<?= url('user.recharge/order', ['user_id' => $item['user_id']]) ?>">充值记录</a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.balance/log')): ?>
                                               <a class="tpl-table-black-operation-default" target="_blank" href="<?= url('user.balance/log', ['user_id' => $item['user_id']]) ?>">余额明细</a>
                                            <?php endif; ?>
                             
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="12" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 模板：用户充值 -->
<script id="tpl-recharge" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">

                <ul class="am-tabs-nav am-nav am-nav-tabs">
                    <li class="am-active"><a href="#tab1">充值余额</a></li>
                    <li><a href="#tab2">充值积分</a></li>
                </ul>

                <div class="am-tabs-bd am-padding-xs">

                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前余额
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static">{{ balance }}</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]" value="dec" data-am-ucheck>
                                    减少
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]" value="final" data-am-ucheck>
                                    最终金额
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                变更金额
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的金额" name="recharge[balance][money]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[balance][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="am-tab-panel am-padding-0" id="tab2">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前积分
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static">{{ points }}</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]" value="dec" data-am-ucheck>
                                    减少
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]" value="final" data-am-ucheck>
                                    最终积分
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                变更数量
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="recharge[points][value]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[points][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>

<script>
    $(function () {
               checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('body').getElementsByTagName('input');
              this.num = this.check.length;
              this.bindEvent();
          },
          bindEvent:function(){
              var that = this;
              for(var i=0; i< this.check.length; i++){
                  this.check[i].onclick = function(){
                       var _check = that.isFullCheck();
                       if (_check){
                           document.getElementById('checkAll').checked = 'checked';
                       }else{
                           document.getElementById('checkAll').checked = '';
                       }
                  }
              }
              
              var  allCheck = document.getElementById('checkAll');
              allCheck.onclick = function(){
                  if (this.checked){
                      that.setFullCheck();
                  }else{
                      that.setFullCheck('');
                  }
              }
              
          },
          setFullCheck:function(checked='checked'){
             for (var ik =0; ik<this.num; ik++){
                  this.check[ik].checked = checked; 
              } 
          },
          isFullCheck:function(){
              var hasCheck = 0;
              for (var k =0; k<this.num; k++){
                   if (this.check[k].checked){
                       hasCheck++;
                   }
              }
              return hasCheck==this.num?true:false;
          },
          getCheckSelect:function(){
              var selectIds = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectIds.push(this.check[i].value);
                    }
              }
              return selectIds;
          }
       }
       
       checker.init();
 
        /**
         * 账户充值
         */
        $('.j-recharge').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '用户充值'
                , area: '460px'
                , content: template('tpl-recharge', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/recharge') ?>',
                        data: {
                            user_id: data.id,
                            source: $tabs.data('amui.tabs').activeIndex
                        }
                    });
                    return true;
                }
            });
        });
        
     
   
 

    });
</script>


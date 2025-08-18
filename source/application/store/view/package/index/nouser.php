<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">待认领</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                 <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                   
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '仓库名称'}">
                                            <option value=""></option>
                                            <option value=" "
                                                <?= $extractShopId === ' ' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"
                                                    <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                           <input type="text" class="am-form-field" name="express_num"
                                                   placeholder="请输入快递单号" value="<?= $request->get('express_num') ?>">
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
                    <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                        <!--修改所属用户-->
                        <?php if (checkPrivilege('package.index/changeUser')): ?>
                        <button type="button" id="j-upuser" class="am-btn am-btn-success am-radius"><i class="iconfont icon-yonghu "></i> 修改所属用户</button>
                        <?php endif;?>
                        <!--修改包裹位置-->
                        <?php if (checkPrivilege('package.index/changeShelf')): ?>
                        <button type="button" id="j-change" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-dingwei "></i> 修改包裹位置</button>
                        <?php endif;?>
                        <?php if (checkPrivilege('package.index/changepackageuser')): ?>
                        <button type="button" id="j-changepackageuser" class="am-btn am-btn-danger  am-radius"><i class="iconfont icon-dingwei "></i> 补齐包裹所属用户</button>
                        <?php endif;?>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>包裹ID</th>
                                <th>包裹预报单号/快递单号</th>
                                <th>包裹预览图</th>
                                <th>仓库</th>
                                <th>运往国家</th>
                                <th>包裹信息</th>
                                <th>备注</th>
                                <th>状态</th>
                                <th>时间</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                             <?php $status = [-1=>'问题件',1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成']; ?>
                             <?php $taker_status = [1=>'待认领',2=>'已认领',3=>'已丢弃']; ?>
                             <?php $source = [1=>'小程序预报',2=>'从平台录入','3'=>'代购单同步',4=>'批量导入','5'=>'PC','6'=>'拼团','7'=>'预约取件','8'=>'仓管录入',9=>'API录入']; ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>"  > 
                                    </td>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['order_sn'] ?><br>
                                    <?= $item['express_num'] ?> <span style="color:#ff6666;cursor:pointer" text="<?= $item['express_num'];?>" onclick="copyUrl2(this)">[复制]</span> <?= $item['express_name']?$item['express_name']:'' ?> </br> <span class="am-badge am-badge-secondary">
                                        <?= $source[$item['source']]?></span>
                                        <?php if (!$item['category_attr']->isEmpty()): foreach ($item['category_attr'] as $attr): ?>
                                              <span class="am-badge am-badge-success"><?= $attr['class_name']?></span> 
                                        <?php endforeach;endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "   data-am-figure="{  pureview: 'true' }">
                                            <?php if (!$item['packageimage']->isEmpty()): foreach ($item['packageimage'] as $itemd): ?>
                                            <a href="<?= $itemd['file_path'] ?>" title="点击查看大图" target="_blank">
                                                <img src="<?= $itemd['file_path'] ?>" width="50" height="50" alt="评论图片">
                                            </a>
                                            <?php endforeach;endif; ?>
                                        </figure>
                                    </td>
                                    <td class="am-text-middle"><?= $item['shop_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['title'] ?></td>
                                    <td class="am-text-middle">长:<?= $item['length']?></br>宽:<?= $item['width']?></br>高:<?= $item['height']?></br>称重:<?= $item['weight']?></br></td>
                                    <td class="am-text-middle"><?= $item['remark'] ?></td>
                                    <td class="am-text-middle">包裹状态:<?= $status[$item['a_status']];?></br>认领状态:<?= $taker_status[$item['is_take']];?></td>
                                    <td class="am-text-middle"></td>
                                    <td class="am-text-middle">预报时间:<?= $item['created_time'] ?></br>更新时间:<?= $item['updated_time'] ?></br>入库时间:<?= $item['entering_warehouse_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <!--详情-->
                                            <?php if (checkPrivilege('package.report/item')): ?>
                                            <a href="<?= url('store/package.report/item', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-xiangqing"></i> 详情
                                            </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('package.index/delete')): ?>
                                            <a href="javascript:void(0);"
                                               class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="package[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>

<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择用户
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius"  onclick="doSelectUser()">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>

<script id="tpl-shelf" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择仓库
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="shelf[shop_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <option value="">请选择</option>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择货架
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <select id="select-shelf" data-select_type = 'shelf_unit'
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'选择货架', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <option value=""></option>
                                    </select> 
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择货位
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <select id="select_shelf_unit" name="shelf[shelf_unit]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择货位', maxHeight: 400}">
                                        <option value=""></option>
                                    </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script id="tpl-package-images" type="text/template">
    <div class="fullscreen-image-form-container">
        <!-- 进度信息 -->
        <div class="progress-info">
            共 {{ totalImages }} 张图片，当前处理第 {{ currentIndex + 1 }} 张
        </div>
        
        <!-- 主要内容区域 -->
        <div class="main-content">
            <!-- 左侧：全屏图片 -->
            <div class="image-section">
                                    <div class="image-container">
                        <div class="image-wrapper">
                            <img id="current-image" src="" alt="包裹图片" class="zoomable-image">
                            <div class="image-controls">
                                <button type="button" class="zoom-btn zoom-in" onclick="zoomImage(1.2)" title="放大">
                                    <i class="am-icon-plus"></i>
                                </button>
                                <button type="button" class="zoom-btn zoom-out" onclick="zoomImage(0.8)" title="缩小">
                                    <i class="am-icon-minus"></i>
                                </button>
                                <button type="button" class="zoom-btn zoom-reset" onclick="resetZoom()" title="重置">
                                    <i class="am-icon-refresh"></i>
                                </button>
                                <button type="button" class="zoom-btn rotate-left" onclick="rotateImage(-90)" title="向左旋转">
                                    <i class="am-icon-rotate-left"></i>
                                </button>
                                <button type="button" class="zoom-btn rotate-right" onclick="rotateImage(90)" title="向右旋转">
                                    <i class="am-icon-rotate-right"></i>
                                </button>
                                <button type="button" class="zoom-btn move-left" onclick="moveImage('left')" title="向左移动">
                                    <i class="am-icon-arrow-left"></i>
                                </button>
                                <button type="button" class="zoom-btn move-right" onclick="moveImage('right')" title="向右移动">
                                    <i class="am-icon-arrow-right"></i>
                                </button>
                                <button type="button" class="zoom-btn move-up" onclick="moveImage('up')" title="向上移动">
                                    <i class="am-icon-arrow-up"></i>
                                </button>
                                <button type="button" class="zoom-btn move-down" onclick="moveImage('down')" title="向下移动">
                                    <i class="am-icon-arrow-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="image-info">
                            快递单号: <span id="current-express-num"></span>
                        </div>
                        <div class="zoom-tips">
                            <small>💡 提示：图片已自动放大 | 滚轮缩放 | 按钮移动 | 双击重置 | 悬停显示控制按钮</small>
                        </div>
                    </div>
            </div>
            
            <!-- 右侧：表单区域 -->
            <div class="form-section">
                <div class="form-content">
                    <h4 class="form-title">用户绑定</h4>
                    
                    <div class="form-group">
                        <label class="form-label">输入用户编号</label>
                        <input type="text" id="user-id-input" class="form-input" placeholder="请输入用户编号">
                        <button type="button" class="search-user-btn" onclick="doSelectUser()">
                            <i class="am-icon-search"></i> 搜索用户
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">选择用户</label>
                        <div class="user-list-container">
                            <div class="user-list uploader-list am-cf">
                            </div>
                            <div class="help-text">
                                <small>选择后不可更改</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="confirm-btn" class="action-btn confirm-btn" onclick="confirmUserBinding()">
                            <i class="am-icon-check"></i> 确认绑定
                        </button>
                        <button type="button" id="skip-btn" class="action-btn skip-btn" onclick="skipCurrentImage()">
                            <i class="am-icon-forward"></i> 跳过
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>





<script id="tpl-inpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
            </div>
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择线路
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                     <select name="inpack[line_id]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择线路'}">
                        <option value="">请选择线路</option>
                    </select>
                    </div>
                </div>
            </div>
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包装服务
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                      <select name="inpack[id]" data-am-selected="{btnSize: 'sm', placeholder: '请选择包装服务'}">
                        <option value="">请选择包装服务</option>
                    </select>
                    </div>
                </div>
            </div>
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择用户地址
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                      <select id="storeAddress" name="inpack[address_id]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择自提点'}">
                                <option value="-1">不选择则使用用户自填的默认地址</option>
                            </select>
                        <div class="am-block">
                            <small><a target="_blank" href="<?= url('store/user/address') ?>">新增用户地址</a></small>
                        </div>
                    </div>
                </div>
            </div>
             <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 管理员备注 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="grade[remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
        </form>
    </div>
</script>
<style>
/* 全屏图片表单容器 */
.fullscreen-image-form-container {
    width: 100%;
    height: 100%;
    background: #fff;
}

/* 进度信息 */
.progress-info {
    background: #e3f2fd;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: bold;
    color: #1976d2;
    font-size: 16px;
}

/* 主要内容区域 */
.main-content {
    display: flex;
    height: calc(100vh - 200px);
    gap: 20px;
}

/* 左侧图片区域 */
.image-section {
    flex: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.image-container {
    text-align: center;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.image-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
    height: 100%;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
    transition: transform 0.1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    cursor: default;
    will-change: transform;
}

.zoomable-image {
    transform-origin: center center;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

/* 图片控制按钮 */
.image-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-wrapper:hover .image-controls {
    opacity: 1;
}

.zoom-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.zoom-btn:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

.zoom-in {
    background: rgba(40, 167, 69, 0.8);
}

.zoom-in:hover {
    background: rgba(40, 167, 69, 1);
}

.zoom-out {
    background: rgba(108, 117, 125, 0.8);
}

.zoom-out:hover {
    background: rgba(108, 117, 125, 1);
}

.zoom-reset {
    background: rgba(255, 193, 7, 0.8);
}

.zoom-reset:hover {
    background: rgba(255, 193, 7, 1);
}

.rotate-left {
    background: rgba(40, 167, 69, 0.8);
}

.rotate-left:hover {
    background: rgba(40, 167, 69, 1);
}

.rotate-right {
    background: rgba(40, 167, 69, 0.8);
}

.rotate-right:hover {
    background: rgba(40, 167, 69, 1);
}

.move-left, .move-right, .move-up, .move-down {
    background: rgba(0, 123, 255, 0.8);
}

.move-left:hover, .move-right:hover, .move-up:hover, .move-down:hover {
    background: rgba(0, 123, 255, 1);
}



.zoom-tips {
    margin-top: 10px;
    font-size: 11px;
    color: #999;
    text-align: center;
    background: rgba(255,255,255,0.6);
    padding: 6px 12px;
    border-radius: 12px;
    backdrop-filter: blur(5px);
}

.image-info {
    margin-top: 15px;
    font-size: 14px;
    color: #666;
    background: rgba(255,255,255,0.9);
    padding: 8px 16px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

.package-info {
    margin-top: 8px;
    font-size: 13px;
    color: #888;
    background: rgba(255,255,255,0.8);
    padding: 6px 12px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

/* 右侧表单区域 */
.form-section {
    flex: 0 0 280px;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    padding: 15px;
    max-width: 280px;
}

.form-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.form-title {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    color: #555;
    font-weight: 500;
    font-size: 13px;
}

.form-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.search-user-btn {
    margin-top: 6px;
    width: 100%;
    padding: 6px 10px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.3s ease;
}

.search-user-btn:hover {
    background: #5a6268;
}

.user-list-container {
    margin-top: 8px;
}

.help-text {
    margin-top: 6px;
    color: #6c757d;
    font-size: 11px;
    text-align: center;
}

/* 操作按钮 */
.form-actions {
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.action-btn {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 8px;
}

.confirm-btn {
    background: #28a745;
    color: white;
    margin-left: 0px;
}

.confirm-btn:hover {
    background: #218838;
    transform: translateY(-1px);
}

.skip-btn {
    background: #6c757d;
    color: white;
}

.skip-btn:hover {
    background: #5a6268;
    transform: translateY(-1px);
}



.user-list-container {
    margin-top: 10px;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .main-content {
        flex-direction: column;
        height: auto;
    }
    
    .image-section {
        flex: none;
        height: 300px;
        margin-bottom: 15px;
    }
    
    .form-section {
        flex: none;
        max-width: none;
        width: 100%;
    }
    
    .progress-info {
        font-size: 14px;
        padding: 12px 16px;
    }
}

@media (max-width: 480px) {
    .image-section {
        height: 250px;
        padding: 12px;
    }
    
    .form-section {
        padding: 12px;
    }
    
    .form-title {
        font-size: 15px;
    }
    
    .action-btn {
        padding: 8px;
        font-size: 12px;
    }
}
</style>



<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    var _render = false;
    var getSelectData = function(_this){
        if (_render){
            return 
        }
        var sType = _this.getAttribute('data-select_type');
        var api_group = {'shelf':'<?= url('store/shelf_manager.index/getShelf')?>','shelf_unit':'<?= url('store/shelf_manager.index/getshelf_unit')?>'};
        if (sType=='shelf'){
            var $selected = $('#select-shelf');
            var data = {'shop_id':_this.value}
        }
        if (sType=='shelf_unit'){
            var $selected = $('#select_shelf_unit');
            var data = {'shelf_id':_this.value}
        }
        $.ajax({
            type:"GET",
            url:api_group[sType],
            data:data,
            dataType:'json',
            success:function(res){
                var _data = res.msg.data;
                if (sType=='shelf'){
                    console.log($selected,'$selected');
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['id'] +'">' + _data[i]['shelf_name'] + '</option>');
                    }
                }else{
                    console.log(444);
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['shelf_unit_id'] +'">' +_data[i]['shelf_unit_floor']+ '层'+ _data[i]['shelf_unit_no'] + '号</option>');
                    }
                }
                _render = true;
                setTimeout(function() {
                    _render = false;
                }, 10);
            }
        })
    }
    

    // 图片浏览和用户绑定相关变量
    var translateX = 0, translateY = 0; // 图片移动位置变量
    
    var imageProcessor = {
        images: [],
        currentIndex: 0,
        currentModal: null,
        init: function() {
            this.images = [];
            this.currentIndex = 0;
        },
        setImages: function(images) {
            this.images = images;
            this.currentIndex = 0;
        },
        getCurrentImage: function() {
            return this.images[this.currentIndex];
        },
        nextImage: function() {
            this.currentIndex++;
            if (this.currentIndex >= this.images.length) {
                // 所有图片处理完成
                this.complete();
                return false;
            }
            return true;
        },
        complete: function() {
            if (this.currentModal) {
                this.currentModal.modal('close');
            }
            layer.msg('所有包裹图片处理完成！', {icon: 1});
        }
    };

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

// 删除元素
        var url = "<?= url('store/package.index/delete') ?>";
        $('.item-delete').delete('id', url);


     /**
         * 修改包裹位置
         */
        $('#j-change').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '修改包裹位置'
                , area: '460px'
                , content: template('tpl-shelf', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/package.index/changeShelf') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        }); 
        
      
    
        /**
         * 修改会员
         */
        $('#j-upuser').on('click', function () {
             var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '修改会员'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/package.index/changeUser') ?>',
                        data: {selectIds:data.selectId}
                    });
                    return true;
                }
            });
        });
        
        
      /**
         * 代用户打包
         */
        $('#j-inpack').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '用户打包'
                , area: '460px'
                , content: template('tpl-inpack', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/package.index/inpack') ?>',
                        data: {
                            selectIds:data.selectId,
                        }
                    });
                    return true;
                }
            });
        });

        /**
         * 补齐包裹所属用户
         */
        $('#j-changepackageuser').on('click', function () {
            var selectIds = checker.getCheckSelect();
            if (selectIds.length == 0) {
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            
            // 获取包裹图片
            $.ajax({
                type: 'GET',
                url: '<?= url('store/package.index/getPackageImages') ?>',
                data: {ids: selectIds.join(',')},
                dataType: 'json',
                success: function(res) {
                    if (res.code == 1) {
                        if (res.data.length == 0) {
                            layer.alert('选中的包裹没有图片', {icon: 5});
                            return;
                        }
                        
                        // 初始化图片处理器
                        imageProcessor.init();
                        imageProcessor.setImages(res.data);
                        
                        // 显示图片处理模态框
                        showImageProcessingModal();
                    } else {
                        layer.alert(res.msg || '获取包裹图片失败', {icon: 5});
                    }
                },
                error: function() {
                    layer.alert('网络错误，请重试', {icon: 5});
                }
            });
        });

    });
    
     function doSelectUser(){
           var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    var user = [data[0]];
                    $userList.html(template('tpl-user-item', user));
                    
                    // 如果是在图片处理模态框中，自动填充用户编号
                    if (imageProcessor.currentModal) {
                        $('#user-id-input').val(data[0].user_id);
                    }
                }
            });
    }

    /**
     * 显示图片处理模态框
     */
    function showImageProcessingModal() {
        var currentImage = imageProcessor.getCurrentImage();
        var data = {
            totalImages: imageProcessor.images.length,
            currentIndex: imageProcessor.currentIndex
        };
        
        $.showModal({
            title: '补齐包裹所属用户 - 图片处理',
            area: '90%',
            content: template('tpl-package-images', data),
            uCheck: true,
            success: function ($content) {
                imageProcessor.currentModal = $content;
                updateCurrentImage();
            }
        });
    }

    /**
     * 更新当前显示的图片
     */
    function updateCurrentImage() {
        var currentImage = imageProcessor.getCurrentImage();
        if (!currentImage) return;
        
        $('#current-image').attr('src', currentImage.file_path);
        $('#current-express-num').text(currentImage.package.express_num);
        $('#user-id-input').val('');
        $('.user-list').html('');
        
        // 初始化图片状态
        window.currentImageRotation = 0;
        window.currentImageScale = 1;
        translateX = 0;
        translateY = 0;
        
        // 图片加载完成后自动放大到合适尺寸
        $('#current-image').on('load', function() {
            // 延迟一点时间确保图片完全加载
            setTimeout(function() {
                autoZoomImage();
            }, 100);
        });
        
        // 如果图片已经加载完成（缓存的情况）
        if ($('#current-image')[0].complete) {
            setTimeout(function() {
                autoZoomImage();
            }, 100);
        }
    }

    /**
     * 确认用户绑定
     */
    function confirmUserBinding() {
        var currentImage = imageProcessor.getCurrentImage();
        var userId = $('#user-id-input').val().trim();
        
        if (!userId) {
            layer.alert('请输入用户编号', {icon: 5});
            return;
        }
        
        // 调用绑定接口
        $.ajax({
            type: 'POST',
            url: '<?= url('store/package.index/changepackageuser') ?>',
            data: {
                package_id: currentImage.package_id,
                user_id: userId
            },
            dataType: 'json',
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('绑定成功！', {icon: 1});
                    
                    // 处理下一张图片
                    if (imageProcessor.nextImage()) {
                        updateCurrentImage();
                    }
                } else {
                    layer.alert(res.msg || '绑定失败', {icon: 5});
                }
            },
            error: function() {
                layer.alert('网络错误，请重试', {icon: 5});
            }
        });
    }

    /**
     * 跳过当前图片
     */
    function skipCurrentImage() {
        if (imageProcessor.nextImage()) {
            updateCurrentImage();
        }
    }

    /**
     * 图片放大
     */
    function zoomImage(scale) {
        var $img = $('#current-image');
        
        // 获取当前状态
        var currentRotation = window.currentImageRotation || 0;
        var currentScale = window.currentImageScale || 1;
        
        var newScale = currentScale * scale;
        
        // 限制缩放范围
        if (newScale < 0.5) newScale = 0.5;
        if (newScale > 5) newScale = 5;
        
        // 更新全局状态
        window.currentImageScale = newScale;
        
        // 应用变换：旋转 + 缩放 + 平移
        var transform = `rotate(${currentRotation}deg) scale(${newScale}) translate(${translateX}px, ${translateY}px)`;
        $img.css('transform', transform);
        
        // 调试信息
        console.log('缩放操作:', scale, '新缩放:', newScale, '旋转:', currentRotation, '位置:', translateX, translateY);
        console.log('完整transform:', transform);
    }

    /**
     * 自动缩放图片到合适尺寸
     */
    function autoZoomImage() {
        var $img = $('#current-image');
        var $container = $('.image-section');
        var containerWidth = $container.width() - 40; // 减去padding
        var containerHeight = $container.height() - 80; // 减去padding和底部信息区域
        
        // 获取图片的原始尺寸
        var imgWidth = $img[0].naturalWidth;
        var imgHeight = $img[0].naturalHeight;
        
        if (imgWidth && imgHeight) {
            // 计算合适的缩放比例，让图片填满容器
            var scaleX = containerWidth / imgWidth;
            var scaleY = containerHeight / imgHeight;
            var scale = Math.min(scaleX, scaleY) * 1.2; // 稍微放大一点，让图片更清晰
            
            // 限制最小和最大缩放
            if (scale < 1) scale = 1; // 至少保持原始大小
            if (scale > 4) scale = 4; // 最大放大4倍
            
            // 更新全局状态
            window.currentImageScale = scale;
            window.currentImageRotation = 0;
            
            // 重置移动位置
            translateX = 0;
            translateY = 0;
            
            // 应用变换：旋转 + 缩放
            var transform = `rotate(0deg) scale(${scale})`;
            $img.css('transform', transform);
            
            console.log('自动缩放完成 - 缩放:', scale, '旋转: 0');
            console.log('完整transform:', transform);
        }
    }

    /**
     * 重置图片缩放
     */
    function resetZoom() {
        // 重置所有状态
        window.currentImageRotation = 0;
        window.currentImageScale = 1;
        translateX = 0;
        translateY = 0;
        
        // 应用重置后的变换
        var transform = 'scale(1)';
        $('#current-image').css('transform', transform);
        
        console.log('重置完成 - 缩放: 1, 旋转: 0, 位置: 0,0');
        console.log('完整transform:', transform);
    }

    /**
     * 鼠标滚轮缩放
     */
    var wheelThrottle = 0;
    $(document).on('wheel', '.image-wrapper', function(e) {
        e.preventDefault();
        
        // 节流处理，防止滚轮事件过于频繁
        var now = Date.now();
        if (now - wheelThrottle < 50) return; // 50ms节流
        wheelThrottle = now;
        
        var delta = e.originalEvent.deltaY > 0 ? 0.9 : 1.1;
        
        // 使用requestAnimationFrame确保流畅性
        requestAnimationFrame(function() {
            zoomImage(delta);
        });
    });





    /**
     * 双击重置缩放
     */
    $(document).on('dblclick', '.zoomable-image', function() {
        resetZoom();
    });

    /**
     * 图片旋转
     */
    function rotateImage(degrees) {
        var $img = $('#current-image');
        
        // 获取当前状态
        var currentRotation = window.currentImageRotation || 0;
        var currentScale = window.currentImageScale || 1;
        
        // 计算新的旋转角度
        var newRotation = currentRotation + degrees;
        
        // 标准化旋转角度到0-360度范围
        newRotation = ((newRotation % 360) + 360) % 360;
        
        // 更新全局状态
        window.currentImageRotation = newRotation;
        window.currentImageScale = currentScale;
        
        // 应用变换：旋转 + 缩放 + 平移
        var transform = `rotate(${newRotation}deg) scale(${currentScale}) translate(${translateX}px, ${translateY}px)`;
        $img.css('transform', transform);
        
        // 调试信息
        console.log('旋转角度:', newRotation, '缩放:', currentScale, '位置:', translateX, translateY);
        console.log('完整transform:', transform);
    }

    /**
     * 图片移动
     */
    function moveImage(direction) {
        var $img = $('#current-image');
        
        // 获取当前状态
        var currentRotation = window.currentImageRotation || 0;
        var currentScale = window.currentImageScale || 1;
        
        // 移动步长
        var step = 50;
        
        // 根据方向计算新的位置
        switch (direction) {
            case 'left':
                translateX -= step;
                break;
            case 'right':
                translateX += step;
                break;
            case 'up':
                translateY -= step;
                break;
            case 'down':
                translateY += step;
                break;
        }
        
        // 限制移动范围，防止图片移动过远
        var maxTranslate = 300;
        if (Math.abs(translateX) > maxTranslate) {
            translateX = translateX > 0 ? maxTranslate : -maxTranslate;
        }
        if (Math.abs(translateY) > maxTranslate) {
            translateY = translateY > 0 ? maxTranslate : -maxTranslate;
        }
        
        // 应用变换：旋转 + 缩放 + 平移
        var transform = `rotate(${currentRotation}deg) scale(${currentScale}) translate(${translateX}px, ${translateY}px)`;
        $img.css('transform', transform);
        
        // 调试信息
        console.log('移动方向:', direction, '旋转角度:', currentRotation, '缩放:', currentScale, '位置:', translateX, translateY);
        console.log('完整transform:', transform);
    }

</script>


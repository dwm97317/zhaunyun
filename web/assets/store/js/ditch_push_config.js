(function ($) {
    "use strict";

    var PushConfigEditor = {
        config: {
            buyerMessage: [],
            sellerMessage: [],
            enableSkuPropertiesName: false,
            enablePayDate: false
        },
        fieldDict: {},
        container: null,
        urls: {
            getFields: 'index.php?s=/store/setting.ditch_push/getFields',
            preview: 'index.php?s=/store/setting.ditch_push/preview'
        },

        init: function (containerId, initialConfig) {
            this.container = $('#' + containerId);
            if (initialConfig && typeof initialConfig === 'object') {
                $.extend(this.config, initialConfig);
            }
            
            // Inject Styles
            this.injectStyles();
            
            // Render Skeleton
            this.renderSkeleton();
            
            // Fetch Fields
            this.fetchFields();
            
            // Bind Global Events
            this.bindEvents();
            
            // Initial Sync
            this.syncHiddenInput();
        },

        injectStyles: function() {
            var css = `
                .push-config-editor { border: 1px solid #eee; padding: 15px; background: #fff; margin-top: 10px; }
                .push-editor-row { display: flex; gap: 20px; }
                .push-field-list { width: 30%; border-right: 1px solid #eee; padding-right: 15px; max-height: 500px; overflow-y: auto; }
                .push-block-area { width: 70%; }
                .push-field-group { margin-bottom: 15px; }
                .push-field-group-title { font-weight: bold; margin-bottom: 5px; color: #666; font-size: 12px; }
                .push-field-item { 
                    display: inline-block; padding: 5px 10px; background: #f8f8f8; border: 1px solid #ddd; 
                    border-radius: 4px; margin: 0 5px 5px 0; cursor: pointer; font-size: 12px;
                }
                .push-field-item:hover { background: #e6f7ff; border-color: #1890ff; color: #1890ff; }
                
                .push-block-container { min-height: 100px; border: 1px dashed #ccc; padding: 10px; background: #fafafa; border-radius: 4px; margin-bottom: 10px; }
                .push-block-item { 
                    background: #fff; border: 1px solid #e8e8e8; padding: 8px; margin-bottom: 8px; 
                    display: flex; align-items: center; justify-content: space-between; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                }
                .push-block-info { display: flex; align-items: center; gap: 10px; flex: 1; }
                .push-block-tag { padding: 2px 6px; background: #e6f7ff; color: #1890ff; border-radius: 2px; font-size: 11px; }
                .push-block-tag.text { background: #f6ffed; color: #52c41a; }
                .push-block-actions { display: flex; gap: 5px; }
                .push-btn-icon { cursor: pointer; padding: 4px; color: #999; }
                .push-btn-icon:hover { color: #f5222d; }
                
                .push-preview-box { background: #f0f2f5; padding: 10px; border-radius: 4px; color: #666; font-size: 12px; min-height: 40px; margin-top: 10px; }
                .push-tabs { display: flex; border-bottom: 1px solid #ddd; margin-bottom: 15px; }
                .push-tab-item { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; }
                .push-tab-item.active { border-bottom-color: #1890ff; color: #1890ff; font-weight: bold; }
                .push-tab-content { display: none; }
                .push-tab-content.active { display: block; }
                
                .push-config-form { display: flex; gap: 10px; margin-top: 5px; padding-top: 5px; border-top: 1px dashed #eee; width: 100%; }
                .push-input-sm { padding: 4px; border: 1px solid #ddd; border-radius: 2px; font-size: 11px; width: 80px; }
                .push-btn-add-text { width: 100%; padding: 8px; border: 1px dashed #ddd; background: #fff; cursor: pointer; text-align: center; margin-bottom: 10px; color: #666; }
                .push-btn-add-text:hover { border-color: #1890ff; color: #1890ff; }
            `;
            $('<style>').text(css).appendTo('head');
        },

        renderSkeleton: function() {
            var html = `
                <div class="push-config-editor">
                    <div class="push-tabs">
                        <div class="push-tab-item active" data-tab="buyer">买家留言 (buyerMessage)</div>
                        <div class="push-tab-item" data-tab="seller">卖家备注 (sellerMessage)</div>
                    </div>
                    
                    <div class="push-editor-row">
                        <div class="push-field-list" id="push-field-list">
                            <div style="text-align:center; padding: 20px;">加载中...</div>
                        </div>
                        <div class="push-block-area">
                            <div id="tab-buyer" class="push-tab-content active">
                                <div class="push-btn-add-text" onclick="PushConfigEditor.addTextBlock('buyerMessage')">+ 添加固定文本</div>
                                <div class="push-block-container" id="blocks-buyerMessage"></div>
                                <div class="am-text-sm am-margin-top-xs">实时预览：</div>
                                <div class="push-preview-box" id="preview-buyerMessage"></div>
                            </div>
                            <div id="tab-seller" class="push-tab-content">
                                <div class="push-btn-add-text" onclick="PushConfigEditor.addTextBlock('sellerMessage')">+ 添加固定文本</div>
                                <div class="push-block-container" id="blocks-sellerMessage"></div>
                                <div class="am-text-sm am-margin-top-xs">实时预览：</div>
                                <div class="push-preview-box" id="preview-sellerMessage"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            this.container.html(html);
        },

        fetchFields: function () {
            var self = this;
            $.post(this.urls.getFields, {}, function (res) {
                if (res.code === 1) {
                    self.fieldDict = res.data.fields;
                    self.renderFieldSelector();
                    // Initial Render Blocks
                    self.renderBlocks('buyerMessage');
                    self.renderBlocks('sellerMessage');
                } else {
                    $('#push-field-list').html('<div style="color:red">加载字段失败</div>');
                }
            }, 'json');
        },

        renderFieldSelector: function () {
            var html = '';
            $.each(this.fieldDict, function(groupKey, group) {
                html += `<div class="push-field-group">
                    <div class="push-field-group-title">${group.label}</div>
                    <div>`;
                $.each(group.fields, function(idx, field) {
                    html += `<div class="push-field-item" onclick="PushConfigEditor.addFieldBlock('${field.key}', '${groupKey}', '${field.label}')" title="示例: ${field.example}">
                        ${field.label}
                    </div>`;
                });
                html += `</div></div>`;
            });
            $('#push-field-list').html(html);
        },

        bindEvents: function() {
            var self = this;
            // Tabs
            this.container.on('click', '.push-tab-item', function() {
                var tab = $(this).data('tab');
                $('.push-tab-item').removeClass('active');
                $(this).addClass('active');
                $('.push-tab-content').removeClass('active');
                $('#tab-' + tab).addClass('active');
            });

            // Input Change Sync
            this.container.on('change input', '.push-config-input', function() {
                var type = $(this).closest('.push-tab-content').attr('id').replace('tab-', '') === 'buyer' ? 'buyerMessage' : 'sellerMessage';
                var index = $(this).closest('.push-block-item').data('index');
                var key = $(this).data('key');
                self.config[type][index][key] = $(this).val();
                self.syncHiddenInput();
                self.updatePreview(type);
            });

            // Delete Block
            this.container.on('click', '.btn-delete-block', function() {
                var type = $(this).closest('.push-tab-content').attr('id').replace('tab-', '') === 'buyer' ? 'buyerMessage' : 'sellerMessage';
                var index = $(this).closest('.push-block-item').data('index');
                self.config[type].splice(index, 1);
                self.renderBlocks(type);
                self.syncHiddenInput();
                self.updatePreview(type);
            });
            
            // Move Up
            this.container.on('click', '.btn-move-up', function() {
                var type = $(this).closest('.push-tab-content').attr('id').replace('tab-', '') === 'buyer' ? 'buyerMessage' : 'sellerMessage';
                var index = $(this).closest('.push-block-item').data('index');
                if (index > 0) {
                    var temp = self.config[type][index];
                    self.config[type][index] = self.config[type][index-1];
                    self.config[type][index-1] = temp;
                    self.renderBlocks(type);
                    self.syncHiddenInput();
                    self.updatePreview(type);
                }
            });

            // Move Down
            this.container.on('click', '.btn-move-down', function() {
                var type = $(this).closest('.push-tab-content').attr('id').replace('tab-', '') === 'buyer' ? 'buyerMessage' : 'sellerMessage';
                var index = $(this).closest('.push-block-item').data('index');
                if (index < self.config[type].length - 1) {
                    var temp = self.config[type][index];
                    self.config[type][index] = self.config[type][index+1];
                    self.config[type][index+1] = temp;
                    self.renderBlocks(type);
                    self.syncHiddenInput();
                    self.updatePreview(type);
                }
            });
        },

        getCurrentType: function() {
            return $('.push-tab-item.active').data('tab') === 'buyer' ? 'buyerMessage' : 'sellerMessage';
        },

        addTextBlock: function(type) {
            this.config[type].push({
                source: 'text',
                value: '文本'
            });
            this.renderBlocks(type);
            this.syncHiddenInput();
            this.updatePreview(type);
        },

        addFieldBlock: function(fieldKey, source, label) {
            var type = this.getCurrentType();
            this.config[type].push({
                source: source,
                field: fieldKey,
                label: label,
                prefix: '',
                suffix: ''
            });
            this.renderBlocks(type);
            this.syncHiddenInput();
            this.updatePreview(type);
        },

        renderBlocks: function(type) {
            var html = '';
            var list = this.config[type] || [];
            
            list.forEach(function(item, index) {
                var contentHtml = '';
                if (item.source === 'text') {
                    contentHtml = `
                        <div class="push-block-info">
                            <span class="push-block-tag text">固定文本</span>
                            <input type="text" class="push-input-sm push-config-input" style="width:200px" data-key="value" value="${item.value || ''}" placeholder="输入文本内容">
                        </div>
                    `;
                } else {
                    contentHtml = `
                        <div style="flex:1">
                            <div class="push-block-info">
                                <span class="push-block-tag">字段</span>
                                <strong>${item.label || item.field}</strong>
                                <span style="color:#999;font-size:10px">(${item.field})</span>
                            </div>
                            <div class="push-config-form">
                                <input type="text" class="push-input-sm push-config-input" data-key="prefix" value="${item.prefix || ''}" placeholder="前缀">
                                <input type="text" class="push-input-sm push-config-input" data-key="suffix" value="${item.suffix || ''}" placeholder="后缀">
                                <input type="text" class="push-input-sm push-config-input" data-key="default" value="${item.default || ''}" placeholder="默认值">
                                <input type="text" class="push-input-sm push-config-input" data-key="format" value="${item.format || ''}" placeholder="格式化(如Y-m-d)">
                            </div>
                        </div>
                    `;
                }

                html += `
                    <div class="push-block-item" data-index="${index}">
                        ${contentHtml}
                        <div class="push-block-actions">
                            <span class="push-btn-icon btn-move-up" title="上移">↑</span>
                            <span class="push-btn-icon btn-move-down" title="下移">↓</span>
                            <span class="push-btn-icon btn-delete-block" title="删除">×</span>
                        </div>
                    </div>
                `;
            });

            if (list.length === 0) {
                html = '<div style="text-align:center;color:#999;padding:10px;">暂无配置，请从左侧点击字段添加</div>';
            }

            $('#blocks-' + type).html(html);
        },

        updatePreview: function(type) {
            var self = this;
            var list = this.config[type] || [];
            
            // Debounce
            if (this._previewTimeout) clearTimeout(this._previewTimeout);
            this._previewTimeout = setTimeout(function() {
                $.post(self.urls.preview, {config: list}, function(res) {
                    if (res.code === 1) {
                        $('#preview-' + type).text(res.data.preview_content);
                    }
                }, 'json');
            }, 300);
        },

        syncHiddenInput: function() {
            // Read checkboxes
            this.config.enableSkuPropertiesName = $('#enableSkuPropertiesName').is(':checked');
            this.config.enablePayDate = $('#enablePayDate').is(':checked');
            this.config.enableBuyerMessage = $('#enableBuyerMessage').is(':checked'); // Add this if needed

            // Serialize
            var json = JSON.stringify(this.config);
            // Assuming there is a hidden input with name 'ditch[push_config_json]'
            $('input[name="ditch[push_config_json]"]').val(json);
        }
    };
})(jQuery);

# 2026-02-02 06:21:19
ALTER TABLE yoshop_package 
ADD COLUMN print_status TINYINT NOT NULL DEFAULT 0 COMMENT '打印状态: 0=未打印, 1=已打印',
ADD COLUMN print_time INT NOT NULL DEFAULT 0 COMMENT '打印时间戳',
ADD COLUMN print_count INT NOT NULL DEFAULT 0 COMMENT '打印次数';
# 2026-02-02 11:30:44
ALTER TABLE yoshop_ditch ADD COLUMN sf_print_options TEXT NULL COMMENT '顺丰打印选项配置JSON' AFTER sf_waybill_config;
# 2026-02-04 09:00:25
ALTER TABLE yoshop_inpack 
ADD COLUMN zto_print_mark VARCHAR(50) DEFAULT NULL COMMENT '中通大头笔' AFTER print_status_jhd,
ADD COLUMN zto_print_bagaddr VARCHAR(50) DEFAULT NULL COMMENT '中通集包地' AFTER zto_print_mark,
ADD COLUMN zto_cache_address_id INT DEFAULT NULL COMMENT '大头笔缓存对应的地址ID' AFTER zto_print_bagaddr;

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
# 2026-02-05 06:34:30
ALTER TABLE yoshop_ditch ADD COLUMN jd_multibox_config TEXT NULL DEFAULT NULL COMMENT '京东多包裹打单配置' AFTER sf_print_options;
# 2026-02-05 06:53:52
ALTER TABLE yoshop_ditch ADD COLUMN jd_print_config TEXT NULL DEFAULT NULL COMMENT '京东云打印配置' AFTER jd_multibox_config;
# 2026-02-05 10:23:19
ALTER TABLE yoshop_ditch ADD COLUMN jd_print_component_url VARCHAR(255) DEFAULT '' COMMENT '京东打印组件WebSocket地址' AFTER jd_print_config;
# 2026-02-05 22:58:11
CREATE TABLE IF NOT EXISTS `yoshop_async_task_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `task_type` varchar(50) NOT NULL DEFAULT '' COMMENT '任务类型 (order_batch_printer, order_batch_pusher)',
  `task_data` text NOT NULL COMMENT '任务数据 (JSON格式)',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '任务状态 (pending, processing, completed, failed)',
  `priority` tinyint(2) NOT NULL DEFAULT '5' COMMENT '优先级 (1-10, 数字越大优先级越高)',
  `retry_count` tinyint(2) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `max_retries` tinyint(2) NOT NULL DEFAULT '3' COMMENT '最大重试次数',
  `result` text COMMENT '执行结果 (JSON格式)',
  `created_time` datetime NOT NULL COMMENT '创建时间',
  `updated_time` datetime NOT NULL COMMENT '更新时间',
  `finished_time` datetime DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_created_time` (`created_time`),
  KEY `idx_task_type` (`task_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='异步任务队列表';

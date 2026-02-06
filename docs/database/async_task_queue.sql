-- 异步任务队列表
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

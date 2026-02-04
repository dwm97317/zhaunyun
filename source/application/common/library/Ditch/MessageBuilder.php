<?php

namespace app\common\library\Ditch;

/**
 * Class MessageBuilder
 * Handles the dynamic construction of buyerMessage and sellerMessage based on JSON configuration.
 * @package app\common\library\Ditch
 */
class MessageBuilder
{
    /**
     * Build the message string based on data and schema.
     *
     * @param array $data The data source (order, user, etc.)
     * @param string|array $schema The configuration schema (JSON string or array)
     * @return string The constructed message
     */
    public static function build($data, $schema)
    {
        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }

        if (empty($schema) || !is_array($schema)) {
            return '';
        }

        $result = '';

        foreach ($schema as $block) {
            $result .= self::processBlock($block, $data);
        }

        // Truncate to 500 characters
        if (mb_strlen($result) > 500) {
            $result = mb_substr($result, 0, 499) . '…';
        }

        return $result;
    }

    /**
     * Process a single block.
     *
     * @param array $block
     * @param array $data
     * @return string
     */
    private static function processBlock($block, $data)
    {
        $type = isset($block['type']) ? $block['type'] : 'text';
        $content = '';

        switch ($type) {
            case 'text':
                $content = isset($block['value']) ? $block['value'] : '';
                // 🔧 NEW: 支持模板变量替换 {{field.name}}
                $content = self::replaceTemplateVariables($content, $data);
                break;

            case 'field':
                $key = isset($block['key']) ? $block['key'] : '';
                $value = self::getValue($data, $key);
                
                // 🔧 FIX: 如果字段值为空，返回空字符串（不添加 prefix/suffix），但不中断后续积木的处理
                // 这样可以让 MessageBuilder::build() 继续处理后续的积木
                if ((is_string($value) && trim($value) === '') || $value === null) {
                    return ''; // 返回空字符串，但不影响后续积木
                }
                
                // Format value (e.g., date) - Simple implementation
                if (isset($block['format'])) {
                    $value = self::formatValue($value, $block['format']);
                }

                $prefix = isset($block['prefix']) ? $block['prefix'] : '';
                $suffix = isset($block['suffix']) ? $block['suffix'] : '';
                $content = $prefix . $value . $suffix;
                break;

            case 'loop':
                $target = isset($block['target']) ? $block['target'] : '';
                $template = isset($block['template']) ? $block['template'] : [];
                $list = self::getValue($data, $target);

                if (is_array($list) && !empty($template)) {
                    foreach ($list as $item) {
                        foreach ($template as $subBlock) {
                            $content .= self::processBlock($subBlock, $item);
                        }
                    }
                }
                break;
        }

        return $content;
    }

    /**
     * Replace template variables in text (e.g., {{receiver.name}})
     *
     * @param string $text
     * @param array $data
     * @return string
     */
    private static function replaceTemplateVariables($text, $data)
    {
        // 匹配 {{field.name}} 格式的模板变量
        return preg_replace_callback('/\{\{([a-zA-Z0-9_.]+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            $value = self::getValue($data, $key);
            return $value !== null ? $value : $matches[0]; // 如果找不到值，保留原始模板
        }, $text);
    }

    /**
     * Get value from array using dot notation.
     *
     * @param array $data
     * @param string $key
     * @return mixed
     */
    private static function getValue($data, $key)
    {
        if (empty($key)) {
            return null;
        }

        $keys = explode('.', $key);
        $current = $data;

        foreach ($keys as $k) {
            if (is_array($current) && isset($current[$k])) {
                $current = $current[$k];
            } elseif (is_object($current) && isset($current->$k)) {
                $current = $current->$k;
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Format the value.
     *
     * @param mixed $value
     * @param string $format
     * @return string
     */
    private static function formatValue($value, $format)
    {
        switch ($format) {
            case 'date':
                return is_numeric($value) ? date('Y-m-d', $value) : $value;
            case 'datetime':
                return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
            case 'money':
                return number_format((float)$value, 2);
            default:
                return $value;
        }
    }
}

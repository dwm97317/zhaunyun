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
                break;

            case 'field':
                $key = isset($block['key']) ? $block['key'] : '';
                $value = self::getValue($data, $key);
                
                // Condition check: if value is empty and block has "required" or similar, maybe skip?
                // Current requirement: "Condition filtering (only when field value meets condition)".
                // Simple implementation: if value is empty, skip the whole block (including prefix/suffix)
                if ((is_string($value) && trim($value) === '') || $value === null) {
                    return '';
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

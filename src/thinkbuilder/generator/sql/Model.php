<?php
namespace thinkbuilder\generator\sql;

use thinkbuilder\generator\Generator;
use thinkbuilder\helper\TemplateHelper;

/**
 * Class Model 基于模型的 sql 代码生成器
 * @package thinkbuilder\generator\sql
 */
class Model extends Generator
{
    public function generate(): Generator
    {
        $data = $this->params['data'];

        //处理SQL的字段
        $content = $this->params['template'];
        $content_field = '';
        if (isset($data['fields'])) {
            $fields = $data['fields'];
            foreach ($fields as $field) {
                $content_field = "\t" . self::getFieldSQL($field);
                $content_field = str_replace('{{FIELD_NAME}}', $field['name'], $content_field);
                $content_field = str_replace('{{FIELD_TITLE}}', $field['title'], $content_field);
                $content = str_replace('{{FIELD_LOOP}}', $content_field . "\n{{FIELD_LOOP}}", $content);
            }
        }

        $tags = [
            'NAME_SPACE' => $data['namespace'],
            'MODEL_NAME' => $this->params['model_name'],
            'MODEL_COMMENT' => $data['caption'],
            'CONTROLLER_PARAMS' => $content_field,
            'CLASS_NAME' => $data['name'],
            'APP_PATH' => APP_PATH,
            'MODULE_NAME' => str_replace('|model', '', str_replace('\\', '|', $data['namespace'])),
            'FIELD_LOOP' => '',
        ];
        $this->content = TemplateHelper::parseTemplateTags($tags, $content);

        return $this;
    }

    /**
     * 判断字段的类型和参数，生成不同类型的字段 sql
     * @param $field
     * @return string
     */
    public static function getFieldSQL($field)
    {
        if (isset($field)) {
            //字段是否必须
            $null_string = (isset($field->required)) ? ($field->required ? ' NOT NULL ' : '') : '';

            $default = (array_key_exists('default', $field)) ? ' DEFAULT \'' . $field['default'] . '\' ' : ' DEFAULT NULL';
            $null_string = $default !== '' ? $default : $null_string;

            if (preg_match('/_id$/', $field['name'])) {
                return "`{{FIELD_NAME}}` bigint(20) $null_string COMMENT '{{FIELD_TITLE}}',";
            }

            if (preg_match('/^is_/', $field['name'])) {
                $default = (array_key_exists('default', $field)) ? ($field['default'] ? '\'1\'' : '\'0\'') : '\'0\'';
                return "`{{FIELD_NAME}}` tinyint(1) DEFAULT $default COMMENT '{{FIELD_TITLE}}',";
            }

            if ($field['rule'] == 'datetime') {
                return "`{{FIELD_NAME}}` datetime $null_string DEFAULT CURRENT_TIMESTAMP COMMENT '{{FIELD_TITLE}}',";
            }

            if ($field['rule'] == 'text') {
                return "`{{FIELD_NAME}}` TEXT $null_string  COMMENT '{{FIELD_TITLE}}',";
            }

            if ($field['rule'] == 'image') {
                return "`{{FIELD_NAME}}` varchar(255) $null_string  COMMENT '{{FIELD_TITLE}}',";
            }

            if ($field['rule'] == 'float') {
                return "`{{FIELD_NAME}}` float(10,2) $null_string  COMMENT '{{FIELD_TITLE}}',";
            }

            return "`{{FIELD_NAME}}` varchar(100) $null_string COMMENT '{{FIELD_TITLE}}',";
        }
        return '';
    }
}
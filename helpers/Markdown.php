<?php
/**
 * Markdown.php
 * @author      {@link https://socialveo.com Socialveo}
 * @copyright   Copyright (C) 2017 Socialveo Sagl - All Rights Reserved
 * @license     Proprietary Software Socialveo (C) 2017, Socialveo Sagl {@link https://socialveo.com/legal Socialveo Legal Policies}
 * @package     yii\apidoc\helpers
 * @category    yii\apidoc\helpers
 */

namespace yii\apidoc\helpers;

use yii\apidoc\models\MethodDoc;
use yii\helpers\Markdown as BaseMarkdown;

/**
 * Class Markdown
 * @package yii\apidoc\helpers
 */
class Markdown extends BaseMarkdown
{
    /**
     * Converts markdown into HTML.
     *
     * @param string $markdown the markdown text to parse
     * @param string $flavor the markdown flavor to use. See [[$flavors]] for available values.
     * Defaults to [[$defaultFlavor]], if not set.
     * @param MethodDoc $method [optional]
     * @return string the parsed HTML output
     */
    public static function process($markdown, $flavor = null, $method = null)
    {
        $parser = static::getParser($flavor);

        return $parser->parse($markdown, $method);
    }
}
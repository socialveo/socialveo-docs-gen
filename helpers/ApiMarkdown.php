<?php
/**
 * @link        http://www.yiiframework.com/
 * @copyright   Copyright (c) 2008 Yii Software LLC
 * @license     http://www.yiiframework.com/license/
 *
 * @link        https://socialveo.com Socialveo
 * @copyright   Copyright (C) 2017 Socialveo Sagl - All Rights Reserved
 * @license     Proprietary Software Socialveo (C) 2017, Socialveo Sagl {@link https://socialveo.com/legal Socialveo Legal Policies}
 */

namespace yii\apidoc\helpers;

use cebe\markdown\GithubMarkdown;
use Phalcon\Text;
use Socialveo\Core\models\SocialveoModel;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiMarkdown extends GithubMarkdown
{
    use ApiMarkdownTrait;
    use MarkdownHighlightTrait;

    /**
     * @var BaseRenderer
     */
    public static $renderer;
    /**
     * @var array translation for guide block types
     * @since 2.0.5
     */
    public static $blockTranslations = [];

    /** @var  ClassDoc */
    protected $renderingContext;
    protected $headings = [];

    /**
     * Notices
     * @var array
     */
    public static $notices = [];


    /**
     * @return array the headlines of this document
     * @since 2.0.5
     */
    public function getHeadings()
    {
        return $this->headings;
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        parent::prepare();
        $this->headings = [];
    }

    /**
     * Parse inline Socialveo models
     * @param string $text
     * @return string
     */
    public function parseModels(&$text)
    {
        $text = preg_replace_callback('/(?:^|\n)([a-z\s]+):/isxSX', function ($m) {
            return $this->getModelLink($m[1]);
        }, $text);
    }

    /**
     * Create html link
     * @param array $block
     * @return string
     */
    private function linkTo($block)
    {
        return '<a href="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
        . (empty($block['text']) ? '' : ' title="' . htmlspecialchars($block['text'],
                ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
        . '>' . $block['text'] . '</a>';
    }

    /**
     * Get Socialveo model and returns link to model
     * @param array $match
     * @return string
     */
    public function getModelLink($match)
    {
        $model = SocialveoModel::getModel($match);
        if (!$model || !class_exists($model)) {
            return $match;
        }
        $link = implode('-', explode('\\', strtolower(trim($model, '\\')))) . '.html';
        $l = $this->linkTo(['url' => $link, 'text' => $match]);
        return $l;
    }

    /**
     * Parse inline Socialveo models and creates links to models
     * @param string $text
     * @return string
     */
    public function parseLinks2ModelsInline($text)
    {
        $text = preg_replace_callback('/{([A-Z][a-z]+[A-Za-z]+)}/sxSX', function ($m) {
            return $this->getModelLink($m[1]);
        }, $text);

        return $text;
    }

    /**
     * Parse Socialveo model and creates link to model
     * @param string $text
     * @param array $data
     * @return string
     */
    public function parseLink2Model($text, &$data)
    {
        $text = preg_replace_callback('/([a-z]+)/isxSX', function ($m) {
            return $this->getModelLink($m[0]);
        }, $text);

        $text = preg_replace_callback('/<[^>\/]*>[^<]*<\/[^>]*>/isxSX', function ($m) use (&$data) {
            $key = '$$$' . sprintf('%02d', count($data)) . '$$$';
            $data[$key] = $m[0];
            return $key . "\n\n";
        }, $text);

        return $text;
    }

    /**
     * Returns Socialveo webapi router config
     * @return array
     */
    private function getRouterConfig()
    {
        static $routes;

        if (!isset($routes)) {
            $filename = SOCIALVEO_WEBAPI_DIR . '/config/router.php';
            $file = file_get_contents($filename);

            if (preg_match('/(\$routes\s*\=\s*\[.*\]\s*;)/isxSX', $file, $m)) {
                $routes = eval("return {$m[1]}");
            } else {
                $routes = [];
            }
        }

        return $routes;
    }

    /**
     * Returns access role as string
     * @param mixed $rule
     * @return string
     */
    protected function roleAccessAsString($rule)
    {
        if (is_array($rule)) {
            foreach ($rule as &$group) {
                if (is_array($group)) {
                    $group = '( ' . implode(' && ', $group) . ' )';
                }
            }

            if (count($rule) > 1) {
                $rule = implode(' || ', $rule);
            } else {
                $rule = trim(implode('', $rule), '() ');
            }
        }

        $rule = explode(' ', $rule);

        $aliases = [
            'user' => 'logged',
            '*' => 'public',
        ];

        foreach ($rule as &$_rule) {
            if (isset($aliases[$_rule])) {
                $_rule = $aliases[$_rule];
            }
            $_rule = Text::camelize($_rule);
        }

        return implode(' ', $rule);
    }

    /**
     * Parse doc-block and returns as html
     * @param string $text
     * @param MethodDoc $method [optional] Current method
     * @return string
     * @throws \Exception
     */
    public function parse($text, $method = null)
    {
        if ($method && preg_match('~Url:\s/~', $text)) {
            $data = [];

            // table
            $text = preg_replace_callback('/([^\\n]+?\|[^\\n]+?\|[^\\n]*\\n)?(\-\-+\|:\-\-+:\|\-\-+)((?!\\n\\n).)*\\n\\n/isxSX',
                function ($m) use (&$data) {
                    $text = $m[0];
                    if (empty($m[1])) {
                        $text = str_replace($m[2], "0 | 0 | 0 \n--|:--:|--", $text);
                    }
                    $text = $this->parseLinks2ModelsInline($text);
                    $parser = new \Parsedown();
                    $mark = $parser->text($text);
                    $mark = str_replace('<table>',
                        '<table class="summary-table table table-striped  table-bordered table-hover">', $mark);
                    if (empty($m[1])) {
                        $mark = preg_replace('/<thead.*<\/thead>/isxSX', '', $mark);
                    } else {
                        $mark = preg_replace('/<thead[^>]*>(.*)<\/thead>\s*<tbody>/isxSX', '<tbody>\1', $mark);
                    }
                    $key = '$$$' . sprintf('%02d', count($data)) . '$$$';
                    $data[$key] = $mark;
                    return $key . "\n\n";
                }, $text);

            // code
            $text = preg_replace_callback('/```((?!```).)*```/isxSX', function ($m) use (&$data) {
                $parser = new \Parsedown();
                $key = '$$$' . sprintf('%02d', count($data)) . '$$$';
                $data[$key] = $parser->text($m[0]);
                return $key . "\n\n";
            }, $text);

            $options = [];

            // strong
            $text = preg_replace_callback('/\b([a-z\s]+):([^\n]*(\n[^\n:]+\n)?)/isxSX',
                function ($m) use (&$data, &$options) {
                    list(, $property, $text) = $m;

                    $text = trim($text);
                    $property = trim($property);
                    $option = strtolower($property);

                    $options[$option] = $text;

                    if ($option == 'url') {
                        return '<code class="hljs api">' . $text . '</code>' . "\n";
                    }

                    if (in_array($option, ['returns', 'filter by', 'applicable to', 'affects'])) {
                        $text = $this->parseLink2Model($text, $data);
                    }

                    $text = $this->parseLinks2ModelsInline($text);

                    if (in_array($option, ['note', 'info'])) {
                        return '<div class="alert alert-info">' . $property . '. ' . $text . '</div>';
                    }
                    if (in_array($option, ['notice', 'danger', 'alert'])) {
                        return '<div class="alert alert-danger">' . $property . '. ' . $text . '</div>';
                    }
                    if (in_array($option, ['warning', 'warn'])) {
                        return '<div class="alert alert-warning">' . $property . '. ' . $text . '</div>';
                    }

                    return '<p style=" font-size: 17px;"><strong>' . $property . ':</strong> ' . $text . '</p>' . "\n";
                }, $text);

            $context = $this->renderingContext;
            $routes = $this->getRouterConfig();
            $controller = $context->name;
            $name = substr(array_reverse(explode('\\', $controller))[0], 0, -10);
            $index = str_replace('_', '-', Text::uncamelize($name));

            if (!isset($routes[$index])) {
                throw new \Exception(sprintf('Can\'t find controller route %s in webapi router.php', $index));
            }

            $route = null;

            foreach ($routes[$index] as $url => $methods) {
                foreach ($methods as $_method => $_route) {
                    if (!isset($_route['action'])) {
                        throw new \Exception(sprintf('Undefined option "action" in route %s for url %s', $index, $url));
                    }
                    if (!isset($_route['allow'])) {
                        throw new \Exception(sprintf('Undefined option "method" in route %s for url %s', $index, $url));
                    }

                    if ($_route['action'] . 'Action' == $method->name) {
                        $route = array_merge($_route, [
                            'url' => $url,
                            'method' => strtoupper($_method),
                        ]);
                    }
                }
            }

            if (empty($route)) {
                // disabled
                $notice = '<div class="alert alert-danger">
                    Notice. This action is disabled, the reasons can be: 
                    not implemented, unsafe, duplicated or deprecated (autodetect: disabled in the router)</div>';

                $text = $notice . $text;
            } else {
                if (!isset($options['method'])) {
                    throw new \Exception(sprintf('Required option "Method" in %s::%s', $context->name, $method->name));
                }

                if (!isset($options['access'])) {
                    throw new \Exception(sprintf('Required option "Access" in %s::%s', $context->name, $method->name));
                }

                if ($options['method'] !== $route['method']) {
                    self::$notices[] = sprintf("Wrong method for route %s::%s\nRight method: %s", $controller,
                        $method->name, strtoupper($route['method']));
                }

                $access = $this->roleAccessAsString($route['allow']);
                $currentAccess = preg_replace('/\([^\(\)\|\&]+\)/', '', $options['access']);
                $currentAccess = str_replace('LoggedUserHasAccess', 'Logged && UserHasAccess', $currentAccess);
                $currentAccess = str_replace(' and ', ' && ', $currentAccess);
                $currentAccess = str_replace('  ', ' ', $currentAccess);

                if (implode(' || ', explode(', ', trim($currentAccess))) !== $access) {
                    self::$notices[] = sprintf("Wrong access for route %s::%s\nRight access: %s", $controller,
                        $method->name, $access);
                }
            }

            if (!preg_match('~' . preg_replace('/\([^\)]+\)/', '(\(?{[a-z_]+}\)?)', $route['url']) . '~isxSX',
                $options['url'])
            ) {
                self::$notices[] = sprintf('Wrong api url for %s\nUrl in route: %s', $options['url'], $route['url']);
            }

            $text = preg_replace('/\b_(.+?)_\b/', '<i>\1</i>', $text);
            $text = preg_replace('/\b\*\*(.+?)\*\*\b/', '<strong>\1</strong>', $text);


            $r = str_replace('- - -', '', strtr($text, $data));

            return $r;
        }

//        $markup = \Michelf\MarkdownExtra::defaultTransform($text);

        $markup = parent::parse($text);
        $markup = $this->applyToc($markup);
        return $markup;
    }

    /**
     * @since 2.0.5
     */
    protected function applyToc($content)
    {
        // generate TOC if there is more than one headline
        if (!empty($this->headings) && count($this->headings) > 1) {
            $toc = [];
            foreach ($this->headings as $heading) {
                $toc[] = '<li>' . Html::a(strip_tags($heading['title']), '#' . $heading['id']) . '</li>';
            }
            $toc = '<div class="toc"><ol>' . implode("\n", $toc) . "</ol></div>\n";
            if (strpos($content, '</h1>') !== false)
                $content = str_replace('</h1>', "</h1>\n" . $toc, $content);
            else
                $content = $toc . $content;
        }
        return $content;
    }

    /**
     * @inheritDoc
     */
    protected function renderHeadline($block)
    {
        $content = $this->renderAbsy($block['content']);
        if (preg_match('~<span id="(.*?)"></span>~', $content, $matches)) {
            $hash = $matches[1];
            $content = preg_replace('~<span id=".*?"></span>~', '', $content);
        } else {
            $hash = Inflector::slug(strip_tags($content));
        }
        $hashLink = "<span id=\"$hash\"></span><a href=\"#$hash\" class=\"hashlink\">&para;</a>";

        if ($block['level'] == 2) {
            $this->headings[] = [
                'title' => trim($content),
                'id' => $hash,
            ];
        } elseif ($block['level'] > 2) {
            if (end($this->headings)) {
                $this->headings[key($this->headings)]['sub'][] = [
                    'title' => trim($content),
                    'id' => $hash,
                ];
            }
        }

        $tag = 'h' . $block['level'];
        return "<$tag>$content $hashLink</$tag>";
    }

    /**
     * @inheritdoc
     */
    protected function renderLink($block)
    {
        $result = parent::renderLink($block);

        // add special syntax for linking to the guide
        $result = preg_replace_callback('/href="guide:([A-z0-9-.#]+)"/i', function ($match) {
            return 'href="' . static::$renderer->generateGuideUrl($match[1]) . '"';
        }, $result, 1);

        return $result;
    }

    /**
     * @inheritdoc
     * @since 2.0.5
     */
    protected function translateBlockType($type)
    {
        $key = ucfirst($type) . ':';
        if (isset(static::$blockTranslations[$key])) {
            $translation = static::$blockTranslations[$key];
        } else {
            $translation = $key;
        }
        return "$translation ";
    }

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param TypeDoc $context
     * @param bool $paragraph
     * @return string
     */
    public static function process($content, $context = null, $paragraph = false, $method = null)
    {
        if (!isset(Markdown::$flavors['api'])) {
            Markdown::$flavors['api'] = new static;
        }

        if (is_string($context)) {
            $context = static::$renderer->apiContext->getType($context);
        }
        Markdown::$flavors['api']->renderingContext = $context;

        if ($paragraph) {
            return Markdown::processParagraph($content, 'api');
        } else {
            return Markdown::process($content, 'api', $method);
        }
    }

    /**
     * Add bootstrap classes to tables.
     * @inheritdoc
     */
    public function renderTable($block)
    {
        return str_replace('<table>', '<table class="table table-bordered table-striped">', parent::renderTable($block));
    }
}

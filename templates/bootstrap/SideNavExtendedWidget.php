<?php
/**
 * SideNavExtendedWidget.php
 * @author      {@link https://socialveo.com Socialveo}
 * @copyright   Copyright (C) 2017 Socialveo Sagl - All Rights Reserved
 * @license     Proprietary Software Socialveo (C) 2017, Socialveo Sagl {@link https://socialveo.com/legal Socialveo Legal Policies}
 * @package     yii\apidoc\templates\bootstrap
 * @category    yii\apidoc\templates\bootstrap
 * @since       1.0
 * @version     1.0
 */

namespace yii\apidoc\templates\bootstrap;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Class SideNavExtendedWidget
 * @package yii\apidoc\templates\bootstrap
 */
class SideNavExtendedWidget extends SideNavWidget
{

    /**
     * Renders widget items.
     */
    public function render2Items()
    {
        $namespaces = $this->getNsItems();

        $content = [];
//        var_export([6, $namespaces]);exit;

        $items = [];
        foreach ($namespaces as $name => $data) {
            $block = [];
//            var_export([7, 'items' => $this->items, 'data' => $data]);exit;

//            var_export([10, $name, $data]);exit;
            if (isset($data[$name])) {
                echo 'yes';
//                var_export([12, '', [$name => $data[$name]]]);exit;
                $text = $this->getSubmenuItems([$name => $data[$name]]);
//                unset($data[$name]);
            }
        }

        var_export([2, $items]);exit;
        var_export([3, $namespaces]);exit;

        return Html::tag('div', implode("\n", $items), $this->options);
    }

    /**
     * Get items sorted by ns
     * @return array
     */
    protected function getNsItems()
    {
        $namespaces = [];

        foreach ($this->items as $namespace => $data) {
            if (preg_match('/^(socialveo\\\\[a-z0-9_]+)(?:\\\\.*)?/isxSX', $namespace, $m)) {
                list (, $short) = $m;
                if (!isset($namespaces[$short])) {
                    $namespaces[$short] = [];
                }
                $namespaces[$short][$namespace] = $data;
            }
        }

        return $namespaces;
    }

    /**
     * Get items sorted by ns
     * @return array
     */
    protected function getNs2Items()
    {
        $namespaces = [];

        foreach ($this->items as $namespace => $data) {
            if (preg_match('/^(socialveo\\\\[a-z0-9_]+)(?:\\\\.*)?/isxSX', $namespace, $m)) {
                list (, $short) = $m;
                if (!isset($namespaces[$short])) {
                    $namespaces[$short] = [];
                }
                $namespaces[$short][$namespace] = $data;
            }
        }

        $tree = [];

        foreach ($namespaces as $name => $data) {

            $item = [];

            if (isset($data[$name])) {
                $item = $data[$name];
                unset($data[$name]);
            } else {
                $item['label'] = $name;
                $item['url'] = '#';
                $item['items'] = [];
            }

            ksort($data, SORT_NATURAL);

            $item['items'] = array_merge(array_values($data), $item['items']);

            $tree[$name] = $item;
        }

        return $tree;
    }

    public function renderItems()
    {
        $this->items = $this->getNs2Items();
        return parent::renderItems();
    }

    /**
     * Render submenu items
     * @param array $submenu
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getSubmenuItems($submenu)
    {
        $items = [];

        foreach ($submenu as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            $items[] = $this->renderItem($item);
        }
        var_export([4, $items]);exit;

        return $items;
    }

}
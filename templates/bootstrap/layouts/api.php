<?php

use yii\apidoc\templates\bootstrap\ApiRenderer;
use yii\apidoc\templates\bootstrap\SideNavExtendedWidget;
use yii\apidoc\templates\bootstrap\SideNavWidget;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $types array */
/* @var $content string */

/** @var $renderer ApiRenderer */
$renderer = $this->context;

$this->beginContent('@yii/apidoc/templates/bootstrap/layouts/main.php', isset($type) ? ['type' => $type] : []); ?>

<div class="row">
    <div class="col-md-3">
        <?php
        $types = $renderer->getNavTypes(isset($type) ? $type : null, $types);
        ksort($types);
        $nav = [];
        foreach ($types as $i => $class) {
            $namespace = $class->namespace;
            if (empty($namespace)) {
                $namespace = 'Not namespaced classes';
            }
            if (!isset($nav[$namespace])) {
                $nav[$namespace] = [
                    'label' => $namespace,
                    'url' => '#',
                    'items' => [],
                ];
            }
            $nav[$namespace]['items'][] = [
                'label' => StringHelper::basename($class->name),
                'url' => './' . $renderer->generateApiUrl($class->name),
                'active' => isset($type) && ($class->name == $type->name),
            ];
        } ?>
        <?= SideNavExtendedWidget::widget([
            'id' => 'navigation',
            'items' => $nav,
            'view' => $this,
        ])?>
    </div>
    <div class="col-md-9 api-content" role="main">
        <?= $content ?>
    </div>
</div>

<script type="text/javascript">
    /*<![CDATA[*/
    $("a.toggle").on('click', function () {
        var $this = $(this);
        if ($this.hasClass('properties-hidden')) {
            $this.text($this.text().replace(/Show/,'Hide'));
            $this.parents(".summary").find(".inherited").show();
            $this.removeClass('properties-hidden');
        } else {
            $this.text($this.text().replace(/Hide/,'Show'));
            $this.parents(".summary").find(".inherited").hide();
            $this.addClass('properties-hidden');
        }

        return false;
    }).trigger("click");

    var openNavigation = function($item) {
        var $e = $item.parent();
        if ($e[0] && $e.is(".submenu")) {
            $e.addClass("in");
            $e.prev().removeClass("active");
            openNavigation($e);
        }
    }

    $(document).ready(function() {
        openNavigation($("#navigation .list-group-item.active"));
        openNavigation($("#navigation .submenu.active"));

        $('.list-group-item[data-toggle="collapse"] + .submenu.collapse').each(function() {
            if (!$(this).is(".in")) {
                $(this).prev().addClass("collapsed");
            }
        })
    });
    /*]]>*/
</script>

<?php $this->endContent(); ?>

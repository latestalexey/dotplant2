<?php

/**
 * @var $breadcrumbs array
 * @var $category_group_id integer
 * @var $object \app\models\Object
 * @var $pages \yii\data\Pagination
 * @var $products \app\modules\shop\models\Product[]
 * @var $selected_category \app\modules\shop\models\Category
 * @var $selected_category_id integer
 * @var $selected_category_ids integer[]
 * @var $selections
 * @var $this app\components\WebView
 * @var $title_append string
 * @var $values_by_property_id
 */

use \app\modules\shop\models\UserPreferences;
use yii\helpers\Url;
use yii\helpers\Html;


$this->params['breadcrumbs'] = $breadcrumbs;
$listView = UserPreferences::preferences()->getAttributes()['listViewType'];


?>
<div id="product-list-block">
<h1>
    <?=$this->blocks['h1']?>
</h1>
<?php if (!empty($this->blocks['announce'])): ?>
    <div class="block-announce">
        <?= $this->blocks['announce'] ?>
    </div>
<?php endif; ?>


<div id="<?=($listView === 'listView' ? 'listView' : 'blockView')?>" class="block-product-list">
    <?php
    if ($listView === 'blockView') {
        echo '<div class="row">';
    }
    ?>
    <?php foreach ($products as $product) {
        if ($this->beginCache('Product-item:'.$listView.':'.$product->id, [
            'duration' => 86400,
            'dependency' => new \yii\caching\TagDependency([
                'tags' => $product->getCacheTags(),
            ])
        ])) {
            $url = Url::to(
                [
                    '/shop/product/show',
                    'model' => $product,
                    'properties' => $values_by_property_id,
                    'category_group_id' => $category_group_id,
                ]
            );

            echo $this->render(($listView === 'listView' ? 'item-row' : 'item'),
                ['product' => $product, 'url' => $url]);
            $this->endCache();
        }
    } ?>
    <?php
    if ($listView === 'blockView') {
        echo '</div><hr class="soft">';
    }
    ?>
</div>


<div class="pagination">
    <?php
    if ($pages->pageCount > 1):
        $_GET = $selections;
        ?>
        <?=yii\widgets\LinkPager::widget(
        [
            'pagination' => $pages,
        ]
    );?>
    <?php endif; ?>
</div>

<?php if (!isset($_GET['page']) && count($values_by_property_id) === 0): ?>
    <div class="content"><?=$this->blocks['content']?></div>
<?php endif; ?>
</div>
<?php

$js = <<<JS
$(".product-item .product-image,.product-item .product-announce").click(function() {
    var that = $(this),
        parent = null;
    if (that.hasClass('product-image')) {
        parent = that.parent();
    } else {
        parent = that.parent().parent();
    }

    document.location = parent.find('a.product-name').attr('href');
    return false;
});
JS;
$this->registerJs($js);
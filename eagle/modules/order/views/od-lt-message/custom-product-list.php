<?php 
use yii\helpers\Html;
use yii\grid\GridView;
use eagle\modules\util\helpers\TranslateHelper;
use yii\helpers\Url;
use yii\jui\Dialog;
use yii\data\Sort;

$this->registerJsFile(\Yii::getAlias('@web')."/js/project/order/trackingMessage.js", ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJs("customProduct.init()", \yii\web\View::POS_READY);
// $platform = [
//     '1'=>'bonanza',
//     '2'=>'cidscount'
// ];

// $seller = [
//     '1'=>'mary@qq.com',
//     '2'=>'Jack@qq.com'
// ];

$SelleruseridLabelMap = \eagle\modules\platform\apihelpers\PlatformAccountApi::getAllPlatformOrderSelleruseridLabelMap();
?>
<style>
.custom-list-top{
	
}
.serach-button{
  border-radius: 0px 3px 3px 0px !important;
  margin-bottom: 2px;
  height: 28px;
}
.custom-table{
	margin-top:10px;
}
.table td{
	border-right:0px solid #d9effc !important;
	border-bottom:1px solid #d9effc !important;
	text-align:center;
	vertical-align: middle !important;
}
.table th{
	text-align:center !important;
	vertical-align: middle !important;
}
</style>

<div class="custom-list-top">
	<form id="customListSearch" method="post">
		<?=Html::dropDownList('platform_search',@$_REQUEST['platform_search'],$platform,['onchange'=>"customProduct.customProductListOnChange(this);",'class'=>'eagle-form-control','id'=>'','style'=>'padding-top:3px;','prompt'=>'展示平台'])?>
		<?=Html::dropDownList('seller_search',@$_REQUEST['seller_search'],array(),['onchange'=>"",'class'=>'eagle-form-control','id'=>'','style'=>'padding-top:3px;','prompt'=>'展示店铺'])?>
		<input type="text" class="eagle-form-control" id="condition_search" name="condition_search" value="<?php echo !empty($_REQUEST['condition_search'])?$_REQUEST['condition_search']:"";?>" placeholder="商品名称、商品SKU">
		<button type="button" onclick="customProductSearch()" class="iv-btn btn-search serach-button"><span class="iconfont icon-sousuo"></span></button>
	</form>
	<input type="button" class="btn btn-success btn-sm" onclick="customProduct.newProduct()" value="新建展示商品">
	<input type="button" class="btn btn-success btn-sm" onclick="customProduct.newGroup()" value="新建商品组">
	<input type="button" class="btn btn-success btn-sm" onclick="customProduct.addToGroup()" value="添加商品到商品组">
	
	<div class="custom-table">
		<table class="table table-bordered" id="custom-product-list-tb">
			<thead>
				<tr>
					<th><input type="checkbox" id="chk_all"></th>
					<th>图片</th>
					<th>名称</th>
					<th style="width: 120px;">SKU</th>
					<th style="width: 70px;">店铺</th>
					<th>价格</th>
					<th>平台</th>
					<th>商品描述</th>
					<th>创建时间</th>
					<th>操作</th>
				</tr>
			 </thead>
			 <?php if(!empty($data)):?>
			 <tbody class="lzd_body">
			 <?php $num=1;foreach ($data as $data_detail):?>
				<tr data-id="<?php echo $data_detail['id']?>" <?php echo $num%2==0?"class='striped-row'":null;$num++;?>>
					<td><input type="checkbox" id="chk_one"></td>
					<td><img src="<?php echo $data_detail['photo_url'];?>" style="max-width:60px;max-height:60px;"></td>
					<td style="word-break:break-all;"><?php echo $data_detail['title'];?></td>
					<td><?php echo $data_detail['sku'];?></td>
					<td>
					<?=empty($SelleruseridLabelMap[$data_detail['platform']][$data_detail['seller_id']])?
						$data_detail['seller_id']:
						$SelleruseridLabelMap[$data_detail['platform']][$data_detail['seller_id']].
						(($SelleruseridLabelMap[$data_detail['platform']][$data_detail['seller_id']]==$data_detail['seller_id'])?'':'('.$data_detail['seller_id'].')');
					?>
					</td>
					<td><?php echo $data_detail['price']?>&nbsp;<?php echo $data_detail['currency']?></td>
					<td><?php echo $data_detail['platform']?></td>
					<td style="word-break:break-all;"><?php echo $data_detail['comment']?></td>
					<td><?php echo date("Y-m-d H:i:s",$data_detail['create_time']);?></td>
					<td>
						<input type="button" class="btn btn-success btn-sm" value="编辑" onclick="customProduct.editProduct(<?php echo $data_detail['id']?>);">
						<input type="button" class="btn btn-success btn-sm" value="删除" onclick="customProduct.deleteProduct(<?php echo $data_detail['id']?>);">
					</td>
				</tr>
			 <?php endforeach;?>
			 </tbody>
			<?php endif;?>
		</table>
	</div>
	<?php if($pages):?>
	<div>
		<div id="custom-product-list-pager" class="pager-group" style="text-align: left;">
			<div class="btn-group" >
				<?=\eagle\widgets\ELinkPager::widget(['isAjax'=>true , 'pagination'=>$pages,'options'=>['class'=>'pagination']]);?>
			</div>
				<?=\eagle\widgets\SizePager::widget(['isAjax'=>true ,'pagination'=>$pages , 'pageSizeOptions'=>array( 5 , 10 , 20 , 50 ) , 'class'=>'btn-group dropup']);?>
		</div>
	<div>
	
	<?php 
	// 该例子支持通过 ajax 更新table 和 pagination页面，启动该功能需要下方代码初始化js配置，以及给下面两个widget配置"isAjax"=>true,
		$options = array();
		$options['pagerId'] = 'custom-product-list-pager';// 下方包裹 分页widget的id
		$options['action'] = 'order/od-lt-message/custom_product_list'; // ajax请求的 action
		$options['page'] = $pages->getPage();// 当前页码
		$options['per-page'] = $pages->getPageSize();// 当前page size
		
		$param_str='platform_search='.(empty($_REQUEST['platform_search'])?'':$_REQUEST['platform_search']);
		$param_str.= '&seller_search='.(empty($_REQUEST['seller_search'])?'':$_REQUEST['seller_search']);
		$param_str.= '&condition_search='.(empty($_REQUEST['condition_search'])?'':$_REQUEST['condition_search']);

		$options['action'] .= '?'.$param_str;
		
		$options['sort'] = isset($_REQUEST['sort'])?$_REQUEST['sort']:'';// 当前排序
		$this->registerJs('$("#custom-product-list-tb").initGetPageEvent('.json_encode($options).')' , \Yii\web\View::POS_READY);
	?>
	<?php endif; ?>
</div>


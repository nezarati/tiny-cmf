<?
namespace Product;
class Hook {	
	public function __construct() {
		\Hook::add('product_presave', '\Product\Hook::postPresave');
		\Hook::add('product_form', '\Product\Hook::postForm');
		\Hook::add('schemaPost', '\Product\Hook::schemaPost');
		
		\Hook::add('product_taxonomy_form', '\Product\Hook::formTaxonomy');
		\Hook::add('product_taxonomy_presave', '\Product\Hook::presaveTaxonomy');
		\Hook::add('schemaTaxonomy', '\Product\Hook::schemaTaxonomy');
		
		\Hook::add('install', '\Product\Hook::install');
	}
	
	public static function postPresave($data) {
		$data->content = \Post\API::tidy($data->content);
		
		$data->product['description'] = nl2br($data->description);
		unset($data->description);
		
		$data->product['stock'] = (float)$data->stock;
		unset($data->stock);
		
		$data->product['cost'] = (float)$data->cost;
		unset($data->cost);
		
		$data->product['price'] = $data->price === '' ? $data->price : (float)$data->price;
		unset($data->price);
	}
	public static function postForm($form, $data) {
		$form->data->title['label'] = __('Product Name');
		$form->data->description->attr(array('label' => __('Description'), 'type' => 'textarea', 'value' => $data->product['description']));
		$form->data->stock->attr(array('label' => __('Stock Keeping Unit'), 'value' => isset($data->product['stock']) ? $data->product['stock'] : 0, 'class' => 'tiny', 'dir' => 'ltr'));
		$form->data->cost->attr(array('label' => __('Price'), 'value' => isset($data->product['cost']) ? $data->product['cost'] : '0.0', 'class' => 'tiny', 'dir' => 'ltr', 'suffix' => 'IRR'));
		$form->data->price->attr(array('label' => __('Sale Price'), 'value' => isset($data->product['price']) ? $data->product['price'] : '', 'class' => 'tiny', 'dir' => 'ltr', 'suffix' => 'IRR'));
	}
	
	public static function commentPresave($doc) {
		
	}
	
	public static function schemaPost(&$schema) {
		$schema['fields']['product.stock'] = array('type' => 'integer');
		$schema['fields']['product.cost'] = array('type' => 'float');
		$schema['fields']['product.price'] = array('type' => 'float');
		$schema['fields']['product.tags'] = array('type' => 'array');
		$schema['fields']['product.description'] = array('type' => 'text');
	}
	
	public static function formTaxonomy($form, $data) {
		$form->data->tax->attr(array('label' => __('Tax Settings'), 'value' => isset($data->product['tax']) ? $data->product['tax'] : 0, 'class' => 'tiny', 'dir' => 'ltr', 'prefix' => '%'));
		$form->data->discount->attr(array('label' => __('Discount percentage'), 'value' => isset($data->product['discount']) ? $data->product['discount'] : 0, 'class' => 'tiny', 'dir' => 'ltr', 'prefix' => '%'));
		$form->data->donate->attr(array('label' => __('Donation'), 'value' => isset($data->product['donate']) ? $data->product['donate'] : 0, 'class' => 'tiny', 'dir' => 'ltr', 'suffix' => 'IRR'));
		$form->data->className->attr(array('label' => __('Class name'), 'value' => $data->product['className'], 'class' => '', 'dir' => 'ltr'));
	}
	public static function presaveTaxonomy($data) {
		$data->product['tax'] = (float)$data->tax;
		unset($data->tax);
		
		$data->product['discount'] = (float)$data->discount;
		unset($data->discount);
		
		$data->product['donate'] = (float)$data->donate;
		unset($data->donate);
		
		$data->product['className'] = $data->className;
		unset($data->className);
		
		$data->parent = 0;
	}
	public static function schemaTaxonomy(&$schema) {
		$schema['fields'] += array(
			'product.donate' => array(
				'type' => 'float',
			),
			'product.discount' => array(
				'type' => 'float',
			),
			'product.tax' => array(
				'type' => 'float',
			),
			'product.className' => array(
				'type' => 'string',
			)
		);
	}

	public static function install() {
		\Service::install('post', 'product');
		//\Service::install('comment', 'product');
		\Service::install('taxonomy', 'product');
		\Service::install('storage', 'product');
	}
}
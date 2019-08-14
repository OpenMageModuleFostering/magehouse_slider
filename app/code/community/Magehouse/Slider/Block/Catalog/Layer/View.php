<?php 

/**
 * Catalog layer price filter
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Magehouse_Slider_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
	
	public $_currentCategory;
	public $_productCollection;
	public $_maxPrice;
	public $_minPrice;
	public $_currMinPrice;
	public $_currMaxPrice;
	public $_imagePath;
	
	public function __construct(){
	
		$this->_currentCategory = Mage::registry('current_category');
		$this->setProductCollection();
		$this->setMinPrice();
		$this->setMaxPrice();
		$this->setCurrentPrices();
		$this->_imagePath = $this->getUrl('media/magehouse/slider');
		parent::__construct();		
	}
	
	public function getSliderStatus(){
		if(Mage::getStoreConfig('price_slider/price_slider_settings/slider_loader_active'))
			return true;
		else
			return false;			
	}
	
	public function getSlider(){
		if($this->getSliderStatus()){
			$text='
				<div class="price">
					<p>
						<input type="text" id="amount" readonly="readonly" style="background:none; border:none;" />
					</p>
					<div id="slider-range"></div>
				</div>
			';	
			
			return $text;
		}	
	}
	
	public function prepareParams(){
		$url="";
	
		$params=$this->getRequest()->getParams();
		foreach ($params as $key=>$val)
			{
					if($key=='id'){ continue;}
					if($key=='min'){ continue;}
					if($key=='max'){ continue;}
					$url.='&'.$key.'='.$val;		
			}		
		return $url;
	}
	
	public function getSliderJs(){
		$baseUrl = $this->_currentCategory->getUrl();
		$timeout = $this->getConfig('price_slider/price_slider_conf/timeout');
		$styles = $this->prepareCustomStyles();
		if($this->_currMaxPrice > 0){$max = $this->_currMaxPrice;} else{$max = $this->_maxPrice;}
		if($this->_currMinPrice > 0){$min = $this->_currMinPrice;} else{$min = $this->_minPrice;}
		$html = '
			<script type="text/javascript">
				jQuery(function($) {
					$( "#slider-range" ).slider({
						range: true,
						min: '.$this->_minPrice.',
						max: '.$this->_maxPrice.',
						values: [ '.$min.', '.$max.' ],
						slide: function( event, ui ) {
							$( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
						},stop: function( event, ui ) {
							var x1 = ui.values[0];
							var x2 = ui.values[1];
							$( "#amount" ).val( "$"+x1+" - $"+x2 );
							var url = "'.$baseUrl.'"+"/?min="+x1+"&max="+x2+"'.$this->prepareParams().'";
							if(x1 != '.$min.' && x2 != '.$max.'){
								clearTimeout(timer);
								window.location= url;
							}else{
									timer = setTimeout(function(){
										window.location= url;
									}, '.$timeout.');     
								}
						}
					});
					$( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
						" - $" + $( "#slider-range" ).slider( "values", 1 ) );
				});
			</script>
			
			'.$styles.'
		';	
		
		return $html;
	}
	
	public function prepareCustomStyles(){
		$useImage = $this->getConfig('price_slider/price_slider_conf/use_image');
		
		$handleHeight = $this->getConfig('price_slider/price_slider_conf/handle_height');
		$handleWidth = $this->getConfig('price_slider/price_slider_conf/handle_width');
		
		$sliderHeight = $this->getConfig('price_slider/price_slider_conf/slider_height');
		$sliderWidth = $this->getConfig('price_slider/price_slider_conf/slider_width');
		
		$amountStyle = $this->getConfig('price_slider/price_slider_conf/amount_style');
		
		
		if($useImage){
			$handle = $this->getConfig('price_slider/price_slider_conf/handle_image');
			$range = $this->getConfig('price_slider/price_slider_conf/range_image');
			$slider = $this->getConfig('price_slider/price_slider_conf/background_image');	
			
			if($handle){$bgHandle = 'url('.$this->_imagePath.$handle.') no-repeat';}
			if($range){$bgRange = 'url('.$this->_imagePath.$range.') no-repeat';}
			if($slider){$bgSlider = 'url('.$this->_imagePath.$slider.') no-repeat';}
		}else{	
			$bgHandle = $this->getConfig('price_slider/price_slider_conf/handle_color');
			$bgRange = $this->getConfig('price_slider/price_slider_conf/range_color');
			$bgSlider = $this->getConfig('price_slider/price_slider_conf/background_color');	
			
		}
		
		$html = '<style type="text/css">';	
			$html .= '.ui-slider .ui-slider-handle{';
			if($bgHandle){$html .= 'background:'.$bgHandle;}
			$html .= 'width:'.$handleWidth.'px; height:'.$handleHeight.'px; border:none;}';
			
			$html .= '.ui-slider{';
			if($bgSlider){$html .= 'background:'.$bgSlider;}
			$html .= ' width:'.$sliderWidth.'px; height:'.$sliderHeight.'px; border:none;}';
			
			$html .= '.ui-slider .ui-slider-range{';
			if($bgRange){$html .= 'background:'.$bgRange;}
			$html .= 'border:none;}';
			
			$html .= '#amount{'.$amountStyle.'}';	
		$html .= '</style>';		
		return $html;
	}
	
	public function getConfig($key){
		return Mage::getStoreConfig($key);
	}
	
	public function setMinPrice(){
		$this->_minPrice = $this->_productCollection
							->getFirstItem()
							->getPrice();
	}
	
	public function setMaxPrice(){
		$this->_maxPrice = $this->_productCollection
							->getLastItem()
							->getPrice();
	}
	
	public function setProductCollection(){
		$this->_productCollection = $this->_currentCategory
							->getProductCollection()
							->addAttributeToSelect('*')
							->setOrder('price', 'ASC');
	}
	
	public function setCurrentPrices(){
		
		$this->_currMinPrice = $this->getRequest()->getParam('min');
		$this->_currMaxPrice = $this->getRequest()->getParam('max'); 
	}	
		
}

?>
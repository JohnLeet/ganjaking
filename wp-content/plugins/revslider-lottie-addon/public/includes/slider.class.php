<?php
/* 
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2021 ThemePunch
*/

if(!defined('ABSPATH')) exit();

class RsLottieSliderFront extends RevSliderFunctions {
	
	private $version,
			$pluginUrl, 
			$pluginTitle;
			
	public function __construct($version, $pluginUrl, $pluginTitle, $isAdmin = false){
		$this->version     = $version;
		$this->pluginUrl   = $pluginUrl;
		$this->pluginTitle = $pluginTitle;
		
		add_action('revslider_slider_init_by_data_post', array($this, 'check_addon_active'), 10, 1);
		if($isAdmin){
			//add_action('wp_enqueue_scripts', array($this, 'add_scripts'));
		}
		add_action('revslider_fe_javascript_output', array($this, 'write_init_script'), 10, 2);
		add_action('revslider_get_slider_wrapper_div', array($this, 'check_if_ajax_loaded'), 10, 2);
		add_filter('revslider_get_slider_html_addition', array($this, 'add_html_script_additions'), 10, 2);
		add_action('revslider_export_html_write_footer', array($this, 'write_export_footer'), 10, 1);
		add_filter('revslider_export_html_file_inclusion', array($this, 'add_addon_files'), 10, 2);
		
	}
	
	public function write_export_footer($export){
		$output = $export->slider_output;
		$array = $this->add_html_script_additions(array(), $output);
		$toload = $this->get_val($array, 'toload', array());
		if(!empty($toload)){
			foreach($toload as $script){
				echo $script;
			}
		}
	}

	public function add_addon_files($html, $export){
		$output = $export->slider_output;
		$addOn = $this->isEnabled($output->slider);
		if(empty($addOn)) return $html;

		$_jsPathMin = file_exists(RS_LOTTIE_PLUGIN_PATH . 'public/assets/js/revolution.addon.' . $this->pluginTitle . '.js') ? '' : '.min';
		if(!$export->usepcl){
			$export->zip->addFile(RS_LOTTIE_PLUGIN_PATH . 'public/assets/js/revolution.addon.' . $this->pluginTitle . $_jsPathMin . '.js', 'js/revolution.addon.' . $this->pluginTitle . $_jsPathMin . '.js');
			$export->zip->addFile(RS_LOTTIE_PLUGIN_PATH . 'public/assets/js/lottie.min.js', 'js/lottie.min.js');
			$export->zip->addFile(RS_LOTTIE_PLUGIN_PATH . 'public/assets/css/revolution.addon.' . $this->pluginTitle . '.css', 'css/revolution.addon.' . $this->pluginTitle . '.css');
		}else{
			$export->pclzip->add(RS_LOTTIE_PLUGIN_PATH.'public/assets/js/revolution.addon.' . $this->pluginTitle . $_jsPathMin . '.js', PCLZIP_OPT_REMOVE_PATH, RS_LOTTIE_PLUGIN_PATH.'public/assets/js/', PCLZIP_OPT_ADD_PATH, 'js/');
			$export->pclzip->add(RS_LOTTIE_PLUGIN_PATH.'public/assets/js/lottie.min.js', PCLZIP_OPT_REMOVE_PATH, RS_LOTTIE_PLUGIN_PATH.'public/assets/js/', PCLZIP_OPT_ADD_PATH, 'js/');
			$export->pclzip->add(RS_LOTTIE_PLUGIN_PATH.'public/assets/css/revolution.addon.' . $this->pluginTitle . '.css', PCLZIP_OPT_REMOVE_PATH, RS_LOTTIE_PLUGIN_PATH.'public/assets/js/', PCLZIP_OPT_ADD_PATH, 'js/');
		}

		$html = str_replace($this->pluginUrl.'public/assets/css/revolution.addon.' . $this->pluginTitle . '.css', 'css/revolution.addon.' . $this->pluginTitle . '.css', $html);
		$html = str_replace(array($this->pluginUrl.'public/assets/js/revolution.addon.' . $this->pluginTitle . '.min.js', $this->pluginUrl.'public/assets/js/revolution.addon.' . $this->pluginTitle . '.js'), $export->path_js .'revolution.addon.' . $this->pluginTitle . $_jsPathMin . '.js', $html);
		$html = str_replace($this->pluginUrl.'public/assets/js/lottie.min.js', $export->path_js .'lottie.min.js', $html);
		
		$slides = $output->get_current_slides();
		//$front = new RsLottieSlideFront($this->pluginTitle);
		if(!empty($slides)){
			$upload_folder = wp_upload_dir();
			$upload_url = $this->get_val($upload_folder, 'baseurl');
			$upload_path = $this->get_val($upload_folder, 'basedir');
			foreach($slides as $slide){
				$layers = $slide->get_layers();
				if(empty($layers)) continue;

				foreach($layers as $layer){
					$subtype = $this->get_val($layer, 'subtype', '');
					if($subtype !== 'lottie') continue;

					$jsonUrl = $this->get_val($layer, array('addOns', 'revslider-lottie-addon', 'config', 'jsonUrl'), '');
					$jsonPath = str_replace($upload_url, $upload_path, $jsonUrl);

					$basename = basename($jsonPath);
					if(!$export->usepcl){
						$export->zip->addFile($jsonPath, 'assets/'.$basename);
					}else{
						$base = dirname($jsonPath);
						$export->pclzip->add($jsonPath, PCLZIP_OPT_REMOVE_PATH, $base, PCLZIP_OPT_ADD_PATH, 'assets/');
					}
					$jsonUrl_slashed = str_replace('/', '\/', $jsonUrl);
					$html = str_replace($jsonUrl, 'assets/'.$basename, $html);
					$html = str_replace($jsonUrl_slashed, 'assets\/'.$basename, $html);
				}

			}
		}

		return $html;
	}

	// HANDLE ALL TRUE/FALSE
	private function isFalse($val){
		if(empty($val)) return true;
		if($val === true || $val === 'on' || $val === 1 || $val === '1' || $val === 'true') return false;
		
		return true;
	}
	
	private function isEnabled($slider){
		$slides = $slider->get_slides();
		if(empty($slides)) return false;

		$settings = $slider->get_params();
		if(empty($settings)) return false;
		
		$addOns = $this->get_val($settings, 'addOns', false);
		if(empty($addOns)) return false;
		
		$addOn = $this->get_val($addOns, 'revslider-' . $this->pluginTitle . '-addon', false);
		if(empty($addOn)) return false;
		
		$enabled = $this->get_val($addOn, 'enable', false);
		if($this->isFalse($enabled)) return false;
		
		$enabled = false;
		foreach($slides as $slide){
			$layers = $slide->get_layers();
			if(empty($layers)) continue;
			
			foreach($layers as $layer){
				if($this->get_val($layer, 'subtype', false) === 'lottie'){
					$enabled = true;
					break;
				}
			}
			
			if($enabled) break;
		}

		// check static layers
		$static_slide = $slider->get_static_slide();
		$layers = ($static_slide instanceof RevSliderSlide) ? $static_slide->get_layers() : array();
		if(!empty($layers)){
			foreach($layers as $layer){
				if($this->get_val($layer, 'subtype', false) === 'charts'){
					$enabled = true;
					break;
				}
			}
		}
		
		return $enabled;
	}
	
	/*private function isEnabled($slider){
		$settings = $slider->get_params();
		if(empty($settings)) return false;
		
		$addOns = $this->get_val($settings, 'addOns', false);
		if(empty($addOns)) return false;
		
		$addOn = $this->get_val($addOns, 'revslider-' . $this->pluginTitle . '-addon', false);
		if(empty($addOn)) return false;
		
		$enabled = $this->get_val($addOn, 'enable', false);
		if($this->isFalse($enabled)) return false;
		
		return $addOn;
	}*/
	
	public function check_addon_active($record){
		if(empty($record)) return $record;
		// addon enabled
		$addOn = $this->isEnabled($record);
		if(empty($addOn)) return $record;
		
		$this->add_scripts();
		remove_action('revslider_slider_init_by_data_post', array($this, 'check_addon_active'), 10);
		
		return $record;
		
	}
	
	public function add_scripts(){
		
		$handle = 'rs-' . $this->pluginTitle . '-front';
		$base = $this->pluginUrl . 'public/assets/';

		$_jsPathMin = file_exists(RS_LOTTIE_PLUGIN_PATH . 'public/assets/js/revolution.addon.' . $this->pluginTitle . '.js') ? '' : '.min';
		
		wp_enqueue_style($handle, $base . 'css/revolution.addon.' . $this->pluginTitle . '.css', array(), $this->version);
		wp_enqueue_script('rslottie', $base . 'js/lottie.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($handle, $base . 'js/revolution.addon.' . $this->pluginTitle . $_jsPathMin . '.js', array('rslottie'), $this->version, true);

		add_filter('revslider_modify_waiting_scripts', array($this, 'add_waiting_script_slugs'), 10, 1);
	}
	
	public function add_html_script_additions($return, $output){
		if($output instanceof RevSliderSlider){
			$addOn = $this->isEnabled($output);
			if(empty($addOn)) return $return;
		}else{
			$me = $output->get_markup_export();
			if($me !== true && $output->ajax_loaded !== true) return $return;
			
			$addOn = $this->isEnabled($output->slider);
			if(empty($addOn)) return $return;
		}
		
		$waiting = array();
		$waiting = $this->add_waiting_script_slugs($waiting);
		if(!empty($waiting)){
			if(!isset($return['waiting'])) $return['waiting'] = array();
			foreach($waiting as $wait){
				$return['waiting'][] = $wait;
			}
		}
		
		$global = $output->get_global_settings();
		$addition = ($output->_truefalse($output->get_val($global, array('script', 'defer'), false)) === true) ? ' async="" defer=""' : '';
		$_jsPathMin = file_exists(RS_LOTTIE_PLUGIN_PATH . 'public/assets/js/revolution.addon.' . $this->pluginTitle . '.js') ? '' : '.min';
		
		$return['toload']['lottie'] = '<script'. $addition .' src="'. $this->pluginUrl . 'public/assets/js/lottie.min.js"></script>';
		$return['toload']['lottiejs'] = '<script'. $addition .' src="'. $this->pluginUrl . 'public/assets/js/revolution.addon.' . $this->pluginTitle . $_jsPathMin . '.js"></script>';
		
		return $return;
	}
	
	public function add_waiting_script_slugs($wait){
		$wait[] = 'lottie';
		$wait[] = 'lottiejs';
		return $wait;
	}
	
	public function check_if_ajax_loaded($r, $output){
		$me = $output->get_markup_export();
		if($me !== true && $output->ajax_loaded !== true) return $r;
		
		$addOn = $this->isEnabled($output->slider);
		if(empty($addOn)) return $r;
		
		$html = '<link rel="stylesheet" href="'. $this->pluginUrl . 'public/assets/css/revolution.addon.' . $this->pluginTitle . '.css">'."\n";
		return $html . $r;
	}
	
	public function write_init_script($slider, $id){
		// addon enabled
		$addOn = $this->isEnabled($slider);
		if(!empty($addOn)){
		
			$id = $slider->get_id();
			$params = $this->get_val($slider, 'params', array());
			$carousel = $this->get_val($params, 'type', 'standard')  !== 'carousel' ? 'false' : 'true';
			
			echo "\n";
			echo "\t\t\t\t\t\t" . 'LottieAddOn(jQuery, revapi' . $id . ', ' . $carousel . ');' . "\n";
			
		}
		
	}
	
}
?>
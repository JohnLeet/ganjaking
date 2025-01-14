<?php
/** 
 * Ticket Variations and Options
 * @version 1.1.2
 */

class EVOVO_Var_opts{
	public $dataset = array();
	public $item_data = array();
	public $parent_id, $parent_type;
	private $user_loggedin = false;
	private $vo_id;

	public $event, $wcid, $method;

	// methods option, variation_type, variation
	public function __construct($EVENT, $wcid='' , $method= 'option'){
		if( is_numeric($EVENT)) $EVENT = new EVO_Event( $EVENT);
		$this->event = $EVENT;
		$this->wcid = $wcid;
		$this->method = $method;

		$this->user_loggedin = is_user_logged_in();

		// set data
		$this->set_data();	
	}


// RETURNS	
	// whether variations and options enabled for the event
	function is_vo(){
		return $this->event->check_yn('_evovo_activate');
	}
	function is_set(){
		if( is_array($this->dataset) && sizeof( $this->dataset)>0) return true;
		return false;
	}
	function is_item_set(){
		if(sizeof($this->item_data)==0) return false;
		if(empty($this->item_data)) return false;
		return true;
	}
	function is_exists($vo_id, $vo_method){
		$dataset = $this->dataset; // data for all of the methods of this vo type
		if( $vo_method != $this->method){
			$dataset = $this->event->get_prop('_evovo_'. $vo_method);
		}

		if(!isset( $dataset[$vo_id])) return false;
		return true;
	}

	// check for item method for stock in all
	function method_has_stock(){
		if(sizeof($this->dataset)==0) return false;	

		$has_stock = false;
		foreach($this->dataset as $id=>$data){
			$this->set_item_data($id);
			if( $this->in_stock()) $has_stock = true;
		}	

		return $has_stock;
	}
	// check for one item for stock
	function item_has_stock(){
		if(sizeof($this->item_data)==0) return false;		
		$stock_status = $this->get_item_prop('stock_status');
		if($stock_status == 'outofstock') return false;
		
		$stock = $this->get_item_prop('stock');

		if($stock== 0) return false;

		if(!$stock && $stock_status =='instock') return true;
		
		return true;

	}
	function in_stock(){
		if( count($this->item_data) == 0) return false;

		$stock_status = $this->get_item_prop('stock_status');

		// for price options alternative with no stock options
		if( $this->method =='option' && empty($stock_status) && empty($this->item_data['stock'])) return true;

		if(empty($stock_status)) return false;
		if($stock_status =='outofstock') return false;
		if( !isset($this->item_data['stock']) && $stock_status =='outofstock') return false;
		if( empty($this->item_data['stock']) && $stock_status =='instock') return true; // unlimited
		if( empty($this->item_data['stock']) && $stock_status =='outofstock') return false;
		if( $this->item_data['stock']=='0' && $stock_status =='instock') return false;
		if( $this->item_data['stock']>0) return  $this->item_data['stock'];

		return true;
	}

	function is_PO_in_stock(){
		if( count($this->item_data) == 0) return false;
		return true;
	}

	// return show remainging count at stock number or true if set to show remainging ticket
	function is_event_show_remaining_stock(){
		$count = $this->event->get_prop('remaining_count');

		if($this->event->check_yn('_show_remain_tix')){
			return $count>0? $count: true;
		}  
		return false;
	}

	// check if non loggedin users can see variation
	function _can_user_see($variation_array ){
		if(empty($variation_array['loggedin'])) return true;
		if($variation_array['loggedin'] == 'nonmember') return true;
		if( $variation_array['loggedin'] == 'member' && $this->user_loggedin) return true;
		return false;
	}

// GETTER
	// get method stock for a parent id
	function get_total_stock_for_method($parent_id, $parent_type){
		if($this->method == 'variation_type') return false;
		if($this->method == 'option') return false;
		$stock = 0;
		$vos = $this->get_parent_vos($parent_id, $parent_type);

		foreach($vos as $vo_id=>$vo){
			$this->set_item_data($vo_id);
			if(!$this->in_stock()) continue;
			$s = $this->get_item_stock();
			if($s) $stock +=$s;
		}
		return $stock;
	}
	function get_parent_id(){
		return $this->get_item_prop('parent_id');
	}
	function get_parent_type(){
		return $this->get_item_prop('parent_type');
	}
	function get_item_stock(){
		$stock = apply_filters('evovo_vo_item_stock_return', $this->get_item_prop('stock'), $this );
		if($stock === false) return false;
		return $stock;
	}
	
	function get_parent_vos($parent_id, $parent_type){
		$parent_vos = array();
		$all_vo = $this->dataset;

		if( !is_array($all_vo)) return false;

		foreach($all_vo as $vo_id=>$data){
			if( !isset($data['parent_type']) ) continue;
			if( $data['parent_type'] != $parent_type) continue;
			if( !isset($data['parent_id']) ) continue;
			if( $data['parent_id'] != $parent_id) continue;

			$this->set_item_data( $vo_id, $data);
			$stock = $this->get_item_stock();

			if( $stock) $data['stock'] = $stock;		

			$parent_vos[ $vo_id ] = $data;
		}

		//print_r($parent_vos);

		return $parent_vos;
	}

	// @since 1.1
	function get_parent_vos_by_id( $passed_vo_id){
		$return = array();
		$all_vo = $this->dataset;

		if( !is_array($all_vo)) return false;

		if( !empty($this->item_data) && is_array($this->item_data))
			return $this->item_data;

		foreach($all_vo as $vo_id => $data){
			if( $passed_vo_id != $vo_id ) continue;
			$return = $data;
		}

		return $return;
	}

	function get_vo_icon_class($method){
		$d = array(
			'variation_type'=>'sliders',
			'variation'=>'random',
			'option'=>'plug'
		);
		return $d[$method];
	}
	// get vos name
		function get_vos_name($id){
			if($this->method == 'option'){
				$this->set_item_data($id);
				return $this->get_item_prop('name');
			}
		}

	// get all vo data array
		public function get_all_vo_data_for_parent($parent_id, $parent_type, $skip_vt = false){
			$vo_data = array(
				'variation'=>array(),
				'option'=> array()
			);

			foreach( array(
				'variation_type'=> __('Variation Types','evovo'),
				'variation'=> __('Variations','evovo'),
				'option'=> __('Price Options','evovo'),
			) as $key=>$val):

				// skip variation type values
				if($skip_vt && $key == 'variation_type') continue;

				$output = '';
				$VO = new EVOVO_Var_opts($this->event, $this->wcid, $key);
				
				if($this->event->get_prop('_evovo_'.$key)){
					
					if( $VO->is_set() ){

						foreach($VO->dataset as $index=>$data){

							if( !empty($parent_id) && $data['parent_id'] != $parent_id) continue;
							if( !empty($parent_type) && $data['parent_type'] != $parent_type) continue;

							$vo_data[$key][ $index ] = array(
								'event_id'=>	$this->event->ID,
								'wcid'=>		$this->wcid,
								'method'=>		$key,
								'vo_id'=> $index,
							);
							$vo_data[$key][ $index ] = $data;
							
						}
					}
				}
			endforeach;

			return $vo_data;
		}

// ACTIONS		
	function get_item_prop($field){
		if( count($this->item_data) == 0) return false;
		if( !isset($this->item_data[$field])) return false;
		if( empty($this->item_data[$field])) return false;
		return $this->item_data[$field];
	}

	function item_adjust_qty($type='reduce', $by=1){
		if( count($this->item_data) == 0) return false;

		$stock = $this->get_item_stock();

		if($stock){
			$new_stock = ($type=='reduce')? 
				$stock - (int)$by:
				$stock + (int)$by;

			$new_stock = $new_stock<0? 0: $new_stock; // make sure stock is not negative

			// if new stock is zero set item to be out of stock
			if($new_stock == 0){
				$this->set_item_prop('stock_status', 'outofstock');
			}

			//save new stock
			$this->set_item_prop('stock', $new_stock);
		}

	}
	
	// save individual data item
	function save_item($vo_id, $data){
		if(empty($vo_id)) return false;

		$dataset = $this->dataset;
		$dataset[$vo_id] = $data;

		// new dataset with new data included along with old data
		$this->save_dataset( $dataset );

		return true;
	}		

	// save all vo type data for a single parent - while removeing old saved values
	public function save_parent_vo_data($parent_id, $parent_type, $all_vo_data, $skip_variation_type = true){

		$return_vo_id = false;

		foreach( array('variation_type','variation','option') as $method ){

			if($skip_variation_type && $method == 'variation_type') continue;

			$this->method = $method;
			$this->set_data();
			$existing_data = $this->dataset;

			$new_data = array();

			// remove existing vo_data for parent
			if($existing_data && is_array($existing_data)){
				foreach( $existing_data as $vo_id=>$data){

					// skip this parent's vos
					if($data['parent_id'] == $parent_id && $data['parent_type'] == $parent_type) continue;
					$new_data[ $vo_id ] = $data;
				}
			}

			// if this method data passed
			if(isset($all_vo_data[ $method ]) && is_array( $all_vo_data[ $method ] )){
				foreach( $all_vo_data[ $method ] as $vo_id=>$data){
					unset($data['event_id']);
					unset($data['save']);
					unset($data['wcid']);
					unset($data['vo_id']);
					unset($data['all_vo_data']);
					$new_data[ $vo_id ] = $data;
					$return_vo_id = $vo_id;

					// in case the parent info is not in the passed vo_data
					$new_data[ $vo_id ]['parent_id'] = $parent_id;
					$new_data[ $vo_id ]['parent_type'] = $parent_type;
				}
			}

			// save
			$this->save_dataset( $new_data );
		}

		return $return_vo_id;
	}

	// save new parent VO order
		public function save_parent_vo_data_order($parent_id, $parent_type , $passed_vo_data ){
			$all_vos = $this->get_all_vo_data_for_parent($parent_id, $parent_type);

			$new_vo_data = array();

			foreach($passed_vo_data as $method=>$mdata){
				foreach($mdata as $vo_id){
					if(!isset($all_vos[$method])) continue;
					if(!isset($all_vos[$method][$vo_id])) continue;

					$new_vo_data[$method][$vo_id] = $all_vos[$method][$vo_id];
				}
			}

			// save 
			$this->save_parent_vo_data( $parent_id, $parent_type, $new_vo_data );

			return $new_vo_data;
		}

	// DELETE
		function delete_item($vo_id){		
			$dataset = $this->dataset;

			if(!isset($dataset[$vo_id])) return $dataset;
			unset($dataset[$vo_id]);

			$this->save_dataset($dataset, true);

			return $dataset;
		}

		// delete all the vo items for a parent or parent type
		public function delete_allitems_for_parent($parent_id, $parent_type){
			foreach( array('variation','option') as $method ){				
				
				$existing_data = $this->event->get_prop( '_evovo_'. $method);
				
				$this->method = $method;
				
				$new_data = array();

				if($existing_data && is_array($existing_data)){
					foreach( $existing_data as $vo_id=>$data){

						// if parent id is given and parent id and parent type is same - skip
						if( !empty($parent_id) && $parent_id == $data['parent_id'] && $data['parent_type'] == $parent_type) continue;

						// if parent id is not given and parent type is same - skip
						if( empty($parent_id) && $data['parent_type'] == $parent_type) continue;

						// leave only other parent's items
						$new_data[ $vo_id ] = $data;						
					}
				}
				$this->save_dataset( $new_data );
			}
		}
	

	// convert variation type options values into array after processing
		function _process_vt_options($vt){
			if(!isset($vt['options'])) return false;

			$vts = $vt['options'];
			$vts = str_replace(' ,', ',', $vts);
			$vts = str_replace(', ', ',', $vts);
			$vts = str_replace(' ', '-', $vts);

			return explode(',', $vts);
		}

// HTML
	function get_all_variation_types_dataset($parent_id, $parent_type ){

		$VOs = new EVOVO_Var_opts($this->event, $this->wcid,'variation');
		
		if($VOs->is_set()):

			// get tickets with vo alredy in cart
			$cart_vos = $this->get_vos_in_cart();
			
			$VTs = new EVOVO_Var_opts($this->event, $this->wcid,'variation_type');
			$variation_types = $VTs->dataset;

			$variations = $VOs->get_parent_vos($parent_id, $parent_type);
			//print_r($variations);

			// BUILD an array of VTs used for making existing Vs
			$vts_exists = array(); 
			$variation_types_array = array();

			foreach($variations as $v_id=>$v){

				$variation_types_array = array();
				$this->set_item_data( $v_id );

				if(!isset($v['variations'])) continue;
				if(sizeof($v['variations'])==0) continue;

				// check if user can see variation
				if( !$this->_can_user_see($v) ) continue;

				$variation_data[$v_id] = $v;

				// adjust with cart existing stock
				if( $cart_vos && count($cart_vos)>0 && isset( $cart_vos['vars']) && isset( $cart_vos['vars'][$v_id] )){
					$this_var_cart_stock = $cart_vos['vars'][$v_id];

					// variations stock
					$v_stock = 0;
					if( isset($v['stock'])) $v_stock = $v['stock'];
					
					$new_var_stock = $v_stock - $this_var_cart_stock;
					if( $new_var_stock < 0 ) $new_var_stock = 0;

					//$variations[$v_id]['stock'] = $new_var_stock;
					$variations[$v_id]['stock'] = 99;

				}

				// hide out of stock variations
				if( $this->event->check_yn('_evovo_v_hide_sold') && $v['stock_status'] == 'outofstock') continue;
				if( $this->event->check_yn('_evovo_v_hide_sold') && isset($v['stock']) && $v['stock'] < 1) continue;

				// run through each existing variation types
				// Add ALL value for variations that doesnt have matches
				foreach($variation_types as $vt_id=>$vt){
					
					// variation types after processed
						$vts = $this->_process_vt_options($vt);
						
					foreach($vts as $vtv){
						$variation_types_array[$vt_id][] = $vtv;

						if( !array_key_exists($vt_id, $v['variations'])){
							$variations[$v_id]['variations'][$vt_id] = 'All';
							$v['variations'][$vt_id] = 'All';
						}
					}
				}

				//print_r($variation_types_array);
				//print_r($variation_types);

				foreach($v['variations'] as $vt_id=>$vval){

					// decode url encoding of signs
					$vval = urldecode($vval);

					// update variations array with decoded values
					$variations[$v_id]['variations'][$vt_id] = $vval;
					
					// check if variation types exists
					if(isset($vts_exists[$vt_id]) && in_array($vval, $vts_exists[$vt_id])) continue;

					if(!isset($variation_types[$vt_id])) continue;

					// All value
					if($vval == 'All'){
						$vts_exists[$vt_id][$vval][] = $v_id;
						continue;
					}

					// check if set variation's vts actually exists in vts
					$vval__ = str_replace('-', ' ', $vval);
					if(strpos($variation_types[$vt_id]['options'], $vval__) === false) continue;
					
					$vts_exists[$vt_id][$vval][] = $v_id;
				}
			}

			return array(
				'variations'=> $variations,
				'vts_exists'=> $vts_exists,
				'variation_types_array'=> $variation_types_array
			);

		endif;

		return false;
	}

	function get_vos_in_cart(){
		$vo_in_cart = array();
		if( !WC()->cart) return false;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			//print_r($cart_item);

			//print_r($cart_item['evotx_event_id_wc']);
			// if event id and repeat interval missing skip those cart items
			if(empty($cart_item['evotx_event_id_wc'])) continue;	
 
			if( $cart_item['evotx_event_id_wc'] != $this->event->ID ) continue;

			if( isset($cart_item[ 'evovo_data'])){
				if( $cart_item['evovo_data'][ 'var_id'] ){
					$vo_in_cart[ 'vars' ][ $cart_item['evovo_data'][ 'var_id'] ] = $cart_item['quantity'];
				}				
			}
			
		}
		return $vo_in_cart;
	}
	function print_frontend_html($parent_id, $parent_type, $evotx_data= array(), $product='', $args = array()){
		
		// initials
		$this->evotx_data = $evotx_data;
		$has_vos = $this->has_vos = false;

		$POs = new EVOVO_Var_opts($this->event, $this->wcid,'option');			
		$VOs = new EVOVO_Var_opts($this->event, $this->wcid,'variation');
		//$VTs = new EVOVO_Var_opts($this->event, $this->wcid,'variation_type');


		// DEFAULTS
			// get WC Product
			if(empty($product)){
				global $product;
				$product = wc_get_product( $this->wcid );
				$this->product = $product;
			}

			$default_stock = $product->get_stock_quantity();

			$defaults = array(
				'default_price' => $product->get_price(),
				'default_max_qty'=> ($default_stock? $default_stock: 'na'),
				'outofstock'=>false,
				'show_pricing_fields'=>true,
				'pluggable'=>false,
				'hidableSection' => false,
			);

			$args = array_merge($defaults, $args);
			extract($args);

		// check if variations enabled
		if($POs->is_set()) $has_vos = $this->has_vos = true;
		if($VOs->is_set()) $has_vos = $this->has_vos = true;		

		if( !$has_vos) return false;

		$VTs = new EVOVO_Var_opts($this->event, $this->wcid,'variation_type');
		$variation_types = $VTs->dataset;
		//print_r($variation_types);


		$curSYM = get_woocommerce_currency_symbol();

		$variations = $price_options = $json_po = array();			

		$variation_data = array();
		$default_var_id = '';
		$def_variation_data = ''; 

		$op_as_separate = $this->event->check_yn('_evovo_po_sep_sold');
		$var_as_separate = $this->event->check_yn('_evovo_var_sep_sold');
		$_evovo_po_uncor_qty = $op_as_separate ? false: $this->event->check_yn('_evovo_po_uncor_qty');

		$initials = array();
		$i = array();

		$help = $Helper = new evotx_helper();

		ob_start();

		if($hidableSection) echo "<div class='evotx_hidable_section with_evovo'>";

		$using_single_variation = false;

		

		// variations
			if($VOs->is_set() && !$op_as_separate):

				$variations_data = $this->get_all_variation_types_dataset( $parent_id , $parent_type );
				extract( $variations_data );
				//print_r($variations_data);

				// if there are no variations for parent
				if(count($variations)>0):

					//print_r($variations);

					// Variation types exists
					if( count($vts_exists)>0){

						//print_r($variations);
						//print_r($vts_exists);

						// variations as separate ticket for single variation type
						if( $var_as_separate && count($vts_exists) == 1){
							
							$using_single_variation = true;

							echo "<div class=''>";

							foreach( $vts_exists as $vte_id=>$vtd){
								if(!isset($variation_types[$vte_id])) continue;

								$vt_data = $variation_types[$vte_id];

								echo "<h4 class='evo_h4' style='margin-bottom:5px;'>".$vt_data['name']. "</h4>";

								echo "<div class='evovo_variation_types_ind'>";

								foreach($vtd as $vtv=>$vs){

									if(!isset($variations[ $vs[0] ])) continue;

									$var_data = $variations[ $vs[0] ];

									$_vs_price = $var_data['regular_price'];
									$_vs_stock = isset($var_data['stock']) ? (int)$var_data['stock']: '-';
									$_vs_stock_status = $var_data['stock_status'];

									// remove dashes added during save
									$vtv = str_replace('-', ' ', $vtv);

									echo "<p class='evovo_var_types_ind' data-vid='$vs[0]'><label>".$vtv. ' - <i>'. $Helper->convert_to_currency($_vs_price) ."</i></label>";


									// based on stock
									if( $_vs_stock == '0' || $_vs_stock_status == 'outofstock'):
										?>	
										<span class='' style='float:right'>Sold out</span>
										<?php
									else:
									?>
									<span class="qty evotx_qty_adjuster">
										<b class="min evotx_qty_change zpos evovo_addremove">-</b>
										<em>0</em>
										<b class="plu evotx_qty_change evovo_addremove">+</b>
										<input type="hidden" name='po_quantity' value='0' max='<?php echo $_vs_stock;?>'/>
									</span>
									<?php
									endif;
									echo "</p>";
								}
							}

							echo "</div></div>";
						
						// variations as select options
						}else{
							echo "<div class='evovo_variation_types'>";

								// for each variation type
								foreach( $vts_exists as $vte_id=>$vtd){

									// if variation type doesnt exists
									if(!isset($variation_types[$vte_id])) continue;

									// get values for the variation type
									$vt_data = $variation_types[$vte_id];

									echo "<p class='evovo_var_types evovo_vt_style1' data-vt_id='{$vte_id}'><label>".$vt_data['name']. "</label>";
									echo "<select name='{$vte_id}'>";

									// for each vt value
									$c = 1;

									$vts_options_array = array();
									$include_all_option = false;// use this to add all option at the bottom

									foreach($vtd as $vtv=>$vs){

										// initial variation type value
										if($c == 1){
											if(empty($i)){
												$i = $vs;
											}else{
												$i = array_intersect($i, $vs);
											}
											$initials[$vte_id] = $vtv;
										}
										
										$slug = str_replace(' ', '-', $vtv);
										$vtv__ = str_replace('-', ' ', $vtv);
										if($vtv!= 'All') $vts_options_array[$slug] = $vtv__;

										// vt is All show all values in there including All
										if($vtv == 'All'){
											$include_all_option = true;
											foreach($variation_types_array[$vte_id] as $vtoption){
												if(array_key_exists($vtoption, $vtd)) continue; // skip multiple variation types showing
												$slug = str_replace(' ', '-', $vtoption);
												$vts_options_array[$slug] = str_replace('-', ' ', $vtoption);
												
											}									
										}

										$c++;
									}

									// print options
									foreach($vts_options_array as $K=>$V){
										echo "<option value='{$K}'>". $V . "</option>";
									}

									if($include_all_option) echo "<option value='all'>All</option>";

									echo "</select>";
									echo "</p>";
								}

								echo "</div>";
							}
						}

					
					// set initial default values
						$initial_v = (isset($i[0]) && isset($variations[$i[0]])) ? $variations[$i[0]]: false;						
						
						$default_price = isset($initial_v['regular_price']) ? $initial_v['regular_price']:'';
						$default_var_id = isset($i[0])? $i[0]: false;
						$default_max_qty = (isset($initial_v['stock'])) ? $initial_v['stock']: 'na';
						$def_variation_data = $initial_v;
				
				endif;
				
			endif;

		// check for variation stock if there are variations
			if( !empty($def_variation_data) && !$using_single_variation){
				$VOs->set_item_data( $default_var_id );
				$var_stock = $VOs->in_stock();

				if(!$var_stock && $parent_type != 'seat') $outofstock = true;
			}

		// Price Options
			if( $POs->is_set() && !$using_single_variation){
				$price_options = $POs->get_parent_vos($parent_id, $parent_type);

				if(count($price_options)>0):

				echo "<div class='evovo_price_options' style='display:". ($outofstock?'none':'')."'>";
				

				if(!$op_as_separate):?>
					<h4 class='evo_h4' style='margin-bottom:5px;padding:10px'><?php evo_lang_e('Optional Ticket Additions');?></h4>
				<?php endif;

				// for each option
				foreach($price_options as $po_id=>$po_data){

					foreach($po_data as $f=>$v){
						$json_po[$po_id][$f] = str_replace("'", '', $v);
					}

					$POs->set_item_data($po_id);

					// stock check
					$sellable = true;

					if( isset($po_data['stock']) && $po_data['stock'] < 1) $sellable = false;
					if( isset($po_data['stock_status']) && $po_data['stock_status'] == 'outofstock') $sellable = false;

					if( $this->event->check_yn('_evovo_op_hide_sold') && !$sellable ) continue;
					
					//if( !$PO_Stock) continue;

					$sold_style = isset($po_data['sold_style']) && $po_data['sold_style']=='mult'? 'mult': 'one';

					echo "<p class='evovo_price_option evovo_po_style1 add {$sold_style} ".( !$sellable ? 'soldout':'' )."' data-poid='{$po_id}'>";
					echo "<label>".$po_data['name']. "<span class='value'>". $Helper->convert_to_currency($po_data['regular_price']) .  "</span>";

					if(isset($po_data['description'])){
						echo '<span class="desc">' . $po_data['description'] . '</span>';
					}
					echo "</label>";


					if( $sellable ):
						if( $sold_style == 'one'):
							?>
							<span class='evovo_addremove' >
								<em class='a'><?php evo_lang_e( 'Add');?></em>
								<em class='ad'><?php evo_lang_e('Added');?></em>
								<em class='rm'><?php evo_lang_e('remove');?></em>
								<input type="hidden" name='po_quantity' value='0' max=''/>
							</span>
							<?php
						else:
							$max = isset($po_data['stock'])? (int)$po_data['stock']: '';
							?>
							<span class="qty evotx_qty_adjuster">
								<b class="min evotx_qty_change zpos evovo_addremove">-</b>
								<em>0</em>
								<b class="plu evotx_qty_change evovo_addremove">+</b>
								<input type="hidden" name='po_quantity' value='0' max='<?php echo $max;?>'/>
							</span>
							<?php
						endif;
					else:
						?><span class='evovo_soldout'><?php evo_lang_e('Sold Out');?></span><?php
					endif;
					echo "</p>";
				}

				echo "</div>";
				endif;
				
			}
		
		// pluggable function 
		do_action('evovo_add_to_cart_before', $this);

		// if final price feilds
			if($show_pricing_fields):
				?>		
				<div class='evovo_add_to_cart_bottom evotx_add_to_cart_bottom' style='display:<?php echo $outofstock?'none':'';?>'>
					
					<?php	
					// base price
					$Helper->base_price_html( $default_price );	
					?>

					<div class='evovo_price_option_prices_container'></div>
					
					<?php 
					// ticket quantity fields
					if ( ! $product->is_sold_individually() && !$op_as_separate && !$using_single_variation):
						$Helper->ticket_qty_html( $default_max_qty );	
					else:
						$Helper->ticket_qty_one_hidden();
					endif;?>

					<?php
						if( $default_stock && $default_stock>0 && $using_single_variation){
							?><input type='hidden' name='all_max_qty' value='<?php echo $default_stock;?>'/><?php
						}
					?>
					
					<div class='evovo_price_option_prices_container_extra'></div>

					<?php	

					$Helper->total_price_html( $default_price ,'evovo_total_price' );
					$Helper->add_to_cart_btn_html( 'evotx_addtocart');


					// show remaining stock
					if( isset($this->evotx_data['event_data']['showRem']) && $this->evotx_data['event_data']['showRem'] 
					){
						$showRem = $this->evotx_data['event_data']['showRem'];
						$visible = ( $default_max_qty == 'na') ? false: true;
						$Helper->remaining_stock_html($default_max_qty,'', $visible);
					}
			

				?></div>
				
				<?php		
			endif;

		// PASS data to evotx data array in tix helper
			echo "<div class='evotx_addon_data'>";
			if( $has_vos){
				$new_evotx_data = array(
					'evovo_data'=> apply_filters('evovo_evotx_addon_data', array(
						'defp'=>$default_price,
						'var_id'=>$default_var_id,
						'v'=>	$variations,
						'vart'=>$initials,
						'po'=>	$json_po,
						'pomethod'=> $op_as_separate? 'separate':'combined',
						'po_uncor_qty'=> $_evovo_po_uncor_qty ? 'yes':'no',
						'varmethod'=> $var_as_separate? 'separate':'combined',
						'outofstock'=>$outofstock
					), $this)
				);

				$this->evotx_data = array_merge_recursive ($this->evotx_data, $new_evotx_data);

				$Helper->print_select_data_element( array(
					'class'=>'evotx_other_data evovo',
					'data'=> array(
						'has_vo'=>true,
						'defp'=> $default_price,
						'var_ids'=> array( $default_var_id => 1),
						'vart'=>$initials, // initial variation types and variation option names
						'options'=> array(),
						'parent_id'=> $parent_id,
						'parent_type'=> $parent_type,
					)
				) );
			}

		// include VO json data - used for client calculation
			echo "<div class='evovo_data' ". $help->array_to_html_data( $this->evotx_data ) .'></div></div>';
	
		if($hidableSection) echo "</div>";
	
		// OUTPUT
		$new_content = ob_get_clean();
		if( !empty($new_content)) return $new_content;
		return false;

	}

// ADMIN only
	// get all HTML for the VOs
		function get_all_vos_html($parent_id, $parent_type, $skip_vt = false, $all_vo_data = ''){

			ob_start();

			$has_content = false;

			foreach( array(
				'variation_type'=> __('Variation Types','evovo'),
				'variation'=> __('Variations','evovo'),
				'option'=> __('Price Options','evovo'),
			) as $key=>$val):

				$this->set_new_method( $key );

				// skip vt
				if($skip_vt && $key == 'variation_type') continue;

				$output = '';
				
				// if all vo data passed set it local
				if(!empty($all_vo_data) && isset($all_vo_data[ $key])){
					$this->dataset = $all_vo_data[ $key];
				}
				
				//print_r($this->dataset);
					
				if( $this->is_set() ){
					foreach($this->dataset as $index=>$data){
						if( $data['parent_type'] != $parent_type) continue;
						$output .= $this->get_item_html($index, $parent_id, $parent_type);
					}
				}

				if(!empty($output)) $has_content = true;
				
			?>
				<div class='evovo_<?php echo $key;?>' style='display:<?php echo (!empty($output))? 'block':'none'; ?>;margin-bottom:10px'>	
					<p><i class='fa fa-<?php echo $this->get_vo_icon_class($key);?>' style='margin-right:5px;'></i> <?php echo $val;?></p>			
					<ul class="evovo_<?php echo $key;?>_<?php echo $parent_type;?> evovo_variations_int evovo_<?php echo $key;?> evovo_vo_list ">
						<?php echo $output;?>
					</ul>
				</div>
			<?php
			endforeach;

			if( !$has_content) echo "<span>". __('There are no Variations & Options') . "</span>";

			return ob_get_clean();
		}
		function print_all_vos_container_html( $parent_id, $parent_type, $skip_vt = false, $all_vo_data =''){
			$help = new evo_helper();
			$data = $help->array_to_html_data(array(
				'd'=> array(
					'eid'=>$this->event->ID,
					'wcid'=>$this->wcid,
					'parent_type'=> $parent_type,
					'parent_id'=> $parent_id
				)
			));
			echo "<div id='evovo_items_content' class='evovo_vos_container_event evovo_vos_container' {$data}>";
			echo  $this->get_all_vos_html( $parent_id, $parent_type, $skip_vt, $all_vo_data );
			echo "</div>";
		}

	// get action buttons html
		function get_vos_action_btn_html($parent_id, $parent_type, $skip_vt = false, $save = true){

			$attrs = '';
			$json = array(
				'type'=>		'new',								
				'wcid'=>		$this->wcid,
				'event_id'=>	$this->event->ID,
				'parent_id'=>	$parent_id,
				'parent_type'=>	$parent_type,
				'save'=> $save ? 'yes':'no',
			);
			foreach(array(
				'data-popc'=>'evovo_lightbox',								
				'title'=>__('Add New Ticket Variations & Options','evovo'),
			) as $key=>$val){
				$attrs .= $key .'="'. htmlentities($val) .'" ';
			}	
			ob_start();						
			?>	

				<?php 

				// variations type
				if(!$skip_vt):

					EVO()->elements->print_trigger_element(array(
						'title'=> '+ ' .__('New Variation Type','evovo'),
						'uid'=>'evovo_editor',
						'lb_class' =>'evovo_editor',
						'lb_title'=>__('Variations & Options Editor','evovo'),	
						'ajax_data'=>array(					
							'event_id'=> $this->event->ID,
							'wcid'=> 	$this->wcid,
							'parent_id'=> $parent_id,
							'parent_type'=> $parent_type,
							'save'=> $save, 'type'=>'new',
							'method'=> 'variation_type',
							'a'=> 'evovo_get_vo_form',
						),
					), 'trig_lb');

				endif;	

				// variation
				EVO()->elements->print_trigger_element(array(
					'title'=> '+ ' .__('New Variation','evovo'),
					'uid'=>'evovo_editor',
					'lb_class' =>'evovo_editor',
					'lb_title'=>__('Variations & Options Editor','evovo'),	
					'ajax_data'=>array(					
						'event_id'=> $this->event->ID,
						'wcid'=> 	$this->wcid,
						'parent_id'=> $parent_id,
						'parent_type'=> $parent_type,
						'save'=> $save, 'type'=>'new',
						'method'=> 'variation',
						'a'=> 'evovo_get_vo_form',
					),
				), 'trig_lb');

				// options
				EVO()->elements->print_trigger_element(array(
					'title'=> '+ ' .__('New Price Option','evovo'),
					'uid'=>'evovo_editor',
					'lb_class' =>'evovo_editor',
					'lb_title'=>__('Variations & Options Editor','evovo'),	
					'ajax_data'=>array(					
						'event_id'=> $this->event->ID,
						'wcid'=> 	$this->wcid,
						'parent_id'=> $parent_id,
						'parent_type'=> $parent_type,
						'save'=> $save, 'type'=>'new',
						'method'=> 'option',
						'a'=> 'evovo_get_vo_form',
					),
				), 'trig_lb');

				
			return ob_get_clean();
		}

	// for admin get item HTML
		function get_item_html($index, $parent_id='', $parent_type='', $vo_item_data = ''){
			
			$curSYM = get_woocommerce_currency_symbol();

			// populate VO item data to object
			if(!empty($vo_item_data)){
				$this->vo_id = $vo_item_data['vo_id'];
				$this->item_data = $vo_item_data;
			}else{
				$this->set_item_data($index);
			}


			// stock alteration
				if( !$this->get_item_prop('stock') && $this->method == 'variation') $stock = 0;
				
			// add price argument
				$price = $this->get_item_prop('sales_price')? $this->get_item_prop('sales_price'): $this->get_item_prop('regular_price');
			// conditional validation content
				$continue = true;
				if( $this->method == 'variation' && !$this->get_item_prop('variations') ) $continue = false;
				if( !empty($parent_id) && $this->get_item_prop('parent_id') != $parent_id) $continue = false;
				if( !empty($parent_type) && $this->get_item_prop('parent_type') != $parent_type) $continue = false;

			if(!$continue) return false;

			// get VO item name
				$name = '';
				switch($this->method){
					case 'variation_type':
						$name = $this->get_item_prop('name');
					break;
					case 'option':
						$name = $this->get_item_prop('name');
					break;
					case 'variation':

						$name = '';
						$Variation_Types = new EVOVO_Var_opts($this->event, $this->wcid, 'variation_type');
						$var_types = $Variation_Types->dataset;

						//print_r($var_types);
						//print_r($this->get_item_prop('variations'));
						
						foreach($this->get_item_prop('variations') as $ind=>$data){
							// check if variation type option values exists
							
							//print_r($data);
							if(!isset($var_types[$ind])) continue;

							$data = urldecode($data);

							// all 
							if($data == 'All'){
								$name .= '<i data-id="'.$ind.'">'. $var_types[$ind]['name'].' <i class="evovolo">'.__('All') .'</i></i> ';
								continue;
							}
							
							$data__ = str_replace('-', ' ', $data);
							if(strpos( $var_types[$ind]['options'], $data__) === false) continue;

							if( $this->is_exists($ind, 'variation_type')){
								$name .= '<i data-id="'.$ind.'">'. $var_types[$ind]['name'].' <i class="evovolo">'.$data__ .'</i></i> ';
							}							
						}
					break;

				}

			// if the VO item doesnt have a name return false and remove the VO item
			if( empty($name)){
				$this->delete_item($index);
				return false;
			}


			ob_start();
			?>
			<li data-cnt="<?php echo $index;?>" class="new" >
				
				<span class='evovo_details'>			
					
					<span class='evovo_name'><i class='evovolo'><?php _e('Name','evovo');?></i> <b><?php echo $name;?> </b></span>	

					<span class='evovo_otherdata evovolo'>
						<?php if($this->get_item_prop('stock_status')=='outofstock'):?>
							<i class="nostock"><?php _e('Out of stock','evovo');?></i>
						<?php elseif($this->method == 'variation' && $this->get_item_prop('stock')!== false && $this->get_item_prop('stock')<1):?>
							<i class="nostock"><?php _e('Sold out','evovo'); echo $this->get_item_prop('stock')?></i>
						<?php endif;?>			
				
						<?php if($this->method == 'variation'):?>
							<?php if( $this->get_item_prop('sales_price') ):?>
								<span><?php _e('Price','evovo');?></span> <b><strike><?php echo $curSYM.$this->get_item_prop('regular_price');?> </strike> <?php echo $curSYM.$this->get_item_prop('sales_price');?></b>
							<?php else:?>
								<span><?php _e('Price','evovo');?></span> <b><?php echo $curSYM.$this->get_item_prop('regular_price');?> </b>
							<?php endif;?>
						<?php else:?>
							<?php if( $this->get_item_prop('regular_price') ):?>
								<span><?php _e('Price','evovo');?></span> <b><?php echo $curSYM.$this->get_item_prop('regular_price');?> </b>
							<?php endif;?>
						<?php endif;?>

						<?php 
							if($this->get_item_prop('fees')):
								$fees = strpos($this->get_item_prop('fees'), '%')!== false? $this->get_item_prop('fees'): $curSYM.str_replace('%', '', $this->get_item_prop('fees'));
						?>
							<span><?php _e('Fees','evovo');?></span> <b><?php echo $fees;?> </b>
						<?php endif;?>
						
						<?php if($this->get_item_prop('stock')):?>
							<span><?php _e('Stock','evovo');?></span> <b><?php echo $this->get_item_prop('stock');?> </b>
						<?php endif;?>
						<?php if($this->get_item_prop('loggedin') && $this->get_item_prop('loggedin') == 'member'):?>
							<span><?php _e('Member Only','evovo');?></span>
						<?php endif;?>
					</span>

				</span>
				<span class='evovo_actions'>	
					<?php

					// common attrs
					$btn_data = array(
						'type'=>		($this->get_item_prop('save') =='no'? 'temp':'edit'),
						'event_id'=>	$this->event->ID,
						'wcid'=>		$this->wcid,
						'parent_type'=>	$this->get_item_prop('parent_type'),
						'parent_id'=>	($this->get_item_prop('parent_id') ? $this->get_item_prop('parent_id') :''),
						'method'=>		$this->method,
						'vo_id' =>		$index,
					);

					$edit_title = __('Edit','evovo'). ' '. ( $this->get_item_prop('save') =='no'? 'Temp ':''). $this->method.' #'. $index;

					$btn_data['a'] = 'evovo_get_vo_form';
					EVO()->elements->print_trigger_element(array(
						'class_attr'=>'edit evolb_trigger',
						'title'=> "<i class='fa fa-pencil'></i>",
						'dom_element'=> 'em',
						'uid'=>'evovo_edit_editor',
						'lb_class' =>'evovo_editor',
						'lb_title'=> __('Edit') . ' '. $this->method,
						'ajax_data' =>$btn_data,
					), 'trig_lb');

					$btn_data['a'] = 'evovo_delete_item';
					EVO()->elements->print_trigger_element(array(
						'class_attr'=>'delete evo_trigger_ajax_run',
						'title'=> "x",
						'dom_element'=> 'em',
						'uid'=>'evovo_delete_vo',
						'lb_class' =>'evovo_lightbox',
						'lb_loader'=>true,
						'lb_load_new_content'=> true,			
						'load_new_content_id'=> 'evovo_items_content',	
						'ajax_data' =>$btn_data,
					), 'trig_ajax');

					?>							
				</span>
							
			</li>
			<?php
			return ob_get_clean();
		}

// data setting	
	public function set_new_method($method, $load_data = true){
		$this->method = $method;
		if( $load_data) $this->set_data();
	}
	function set_item_prop($field, $value){
		if( count($this->item_data) == 0) return false;

		$this->item_data[$field] = $value;

		return $this->save_item( $this->vo_id, $this->item_data);
	}
	public function save_dataset($data, $save = true){
		$this->dataset = $data;
		if( $save){
			$this->event->set_prop( '_evovo_'. $this->method, $data);
		}
	}
	function set_data(){
		$data = $this->event->get_prop('_evovo_'. $this->method);
		$this->dataset = false;
		if($data && is_array($data))	$this->dataset = $data;
	}
	public function set_item_data($vo_id, $vo_data = ''){

		// set passed on data set for the vo item
		if(!empty($vo_data) && is_array($vo_data)){
			$this->item_data = $vo_data;
		}else{
			$dataset = $this->dataset;

			$this->vo_id = $vo_id;

			$this->item_data = array();

			// set item data if they exist
			if( isset( $dataset[$vo_id])) $this->item_data = $dataset[$vo_id];
		}
	}
}
<?php

/*
Plugin Name: Awesome CCDC
Plugin URI: https://raddcreative.com
Description:  Custom WordPress Plugin Developed for Colorado Cohousing Development Co.
Version: 0.0.1
Author: RADD Creative
Author URI: https://raddcreative.com
*/
if ( ! defined( 'ABSPATH' ) ) exit;


if(!defined('AWESOME_LAUNCH__DIR')) define('AWESOME_LAUNCH__DIR',  trailingslashit( plugin_dir_path( __FILE__ ) ) );
if(!defined('AWESOME_LAUNCH_URI')) define('AWESOME_LAUNCH_URI',  trailingslashit( plugin_dir_url( __FILE__ ) ) );
if(!defined('AWESOME_LAUNCH__MODULES_URL'))	define('AWESOME_LAUNCH__MODULES_URL',trailingslashit( AWESOME_LAUNCH_URI.'framework/modules') );
if(!defined('AWESOME_LAUNCH__MODULES_BASE'))	define('AWESOME_LAUNCH__MODULES_BASE', trailingslashit( AWESOME_LAUNCH__DIR.'framework/modules') );
if(!defined('AWESOME_LAUNCH_VER')) define('AWESOME_LAUNCH_VER',  '0.0.1' );
if(!defined('AWESOME_LAUNCH__SLUG')) define('AWESOME_LAUNCH__SLUG',"makeit");

//var_dump(AWESOME_LAUNCH_URI); die();

global $AWESOME_FRAMEWORK;
global $AWESOME_FRAMEWORK_PLUGIN_FILE_NAME;
$AWESOME_FRAMEWORK_PLUGIN_FILE_NAME = __FILE__;

/**
 * AWESOME_LAUNCH
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Awesome_Launch{
    /**
	 * 
	 */
	const TRANSIENT_PREFIX = "awesome_launch_transient_";
	 
	 /** 
	  * 
	  */
	const DATA_CACHE_TIME_SHORT = 30 * MINUTE_IN_SECONDS;
	 
	/** 
	* 
	*/
	const DATA_CACHE_TIME_MEDIUM = 8 * HOUR_IN_SECONDS;
	 
	/** 
	* 
	*/
	const DATA_CACHE_TIME_LONG = DAY_IN_SECONDS;
	
	/**
    * 
    */
    const SLUG = "awesome_launch";
    
    /**
	 * Instance of class
	 * 
	 * Limit intance of class to one
	 */
	protected static $instance;
	
	protected static $Mobile_Detect;
	
    private static $OPTIONS;
    
    private $timber;
    
    /**
	* Data about site's current visitor
	*/
	public $visitor = array();
	
	/**
	 * get_instance
	 * Insert description here
	 *
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @see
	 * @since
	 */
	 public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	 }
	  
	/** 
	 * return transient key prefixed with plugin's prefix
	 */
	 public static function get_transient_key($append=""){
	 	return self::TRANSIENT_PREFIX.$append;
	 }
	 
	 /**
     * __construct
     * Class Contructor
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    protected function __construct(){
      $this->load_vendors();
    	
    	$this->timber = new \Timber\Timber();
    	
    	self::$Mobile_Detect = new Mobile_Detect;
		
      $this->load_includes();
      
      $this->load_requires();
      
      self::get_options();
      
      add_action('init', function(){
          $this->load_classes();
          $this->init();    
      }, 20);
      
        //Load custom and potentially 3rd parties modules
    	$this->load_modules();
        
    }
    
    
    /**
     * get_options
     * Bootstrap Awesome Framework options framework
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    public static function get_options(){
    	if(self::$OPTIONS != null) return self::$OPTIONS;
    	
    	self::$OPTIONS = get_option('awesome_launch_settings',[]);
    	
    	if(isset(self::$OPTIONS['modules']))
    		self::$OPTIONS['modules'] = explode(',', self::$OPTIONS['modules']);
    	
    	global $AWESOME_FRAMEWORK;
		
			if(isset(self::$OPTIONS['modules']))
				$AWESOME_FRAMEWORK['ENABLED_MODULES'] = self::$OPTIONS['modules'];
				
			return self::$OPTIONS;
    }

    /**
     * _body_class
     * Added additional classes to the body element
     *
     * @param $classes
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    public function _body_class($classes){
    	$classes[] = 'makeit';
    	
    	if(Awesome_Launch::$Mobile_Detect->isMobile())
    		$classes[] = 'mobile';
    		
    	if(Awesome_Launch::$Mobile_Detect->isTablet())
    		$classes[] = 'tablet';
    		
    	if( !Awesome_Launch::$Mobile_Detect->isMobile() && !Awesome_Launch::$Mobile_Detect->isTablet() )
    		$classes[] = 'desktop';
    		
    	return $classes;
    }
    
    /**
	 * add_to_twig_filters
	 * Add custom filters to twig
	 *
	 * @param $twig
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @see
	 * @since
	 */
	public function add_to_twig_filters($twig){
	 	/** AR_STRING_REPLACEs */
	 	$twig->addFilter(new Twig_SimpleFilter('AWESOME_LAUNCH_FILTER_STR_REPLACE', function($str){
	 		$str = $this->twig_filter_ar_this_year($str);
	 		$str = $this->twig_filter_ar_last_year($str);
	 		
	 		return $str;
	 	} ) );
	 	
	 	/** Format Phone filter */
	 	$twig->addFilter(new Twig_SimpleFilter('AWESOME_LAUNCH_FORMATTED_PHONE', function($str){
	 		return csip_get_formatted_phone($str, 'US');
	 	} ) );
	 	
	 	$twig->addFilter(new Twig_SimpleFilter('AWESOME_LAUNCH_UNESCAPE', function($str){
	 		return html_entity_decode($str);
	 	} ) );
	 	
	 	/** Pretty text */
	 	$twig->addFilter(new Twig_SimpleFilter('AWESOME_LAUNCH_BEAUTIFY', function($str){
	 		return csip_beautify($str);
	 	} ) );
	 	
	 	/** Ugly db friendly text */
	 	$twig->addFilter(new Twig_SimpleFilter('AWESOME_LAUNCH_UGLIFY', function($str){
	 		return csip_uglify($str,"_");
	 	} ) );
	 	
	 	$twig->addExtension(new Twig_Extensions_Extension_Array());
	 	
	 	$twig->addExtension(new Twig_Extension_StringLoader());
	 	
	 	$twig->addExtension(new Twig_Extension_Core());
	 	
    	return $twig;
	}
	
	public function load_frontend_scripts(){
		wp_enqueue_script( 'makeit-core');
	}
	
	/**
	 * 
	 * 
	 */
	public function load_global_styles_scripts(){ 
		wp_enqueue_style( 'select2', AWESOME_LAUNCH_URI . 'assets/css/vendor/select2/select2.min.css', array(), AWESOME_LAUNCH_VER );
		wp_register_style( 'slick-theme', AWESOME_LAUNCH_URI."assets/css/vendor/slick/slick-theme.css", array(), AWESOME_LAUNCH_VER );
		wp_register_style( 'slick', AWESOME_LAUNCH_URI."assets/css/vendor/slick/slick.css", array('slick-theme'), AWESOME_LAUNCH_VER );
		wp_register_style( 'simplemde', AWESOME_LAUNCH_URI."assets/css/vendor/simplemde/simplemde.min.css", array(), AWESOME_LAUNCH_VER );
		wp_register_style( 'vue-slider-component', AWESOME_LAUNCH_URI."assets/css/vendor/awesoome-vue-es5-slider-component/vue-slider-component.css", array(), AWESOME_LAUNCH_VER  );
	
		if(defined('WP_DEBUG') && WP_DEBUG){
			$js_ext = '.js';
		}else{
			$js_ext = '.min.js';
		}
		
		if(!is_admin()){
			wp_deregister_script( 'jquery');
			wp_register_script( 'jquery', AWESOME_LAUNCH_URI."assets/js/vendor/jquery/jquery{$js_ext}", array(), AWESOME_LAUNCH_VER );
		}
		$OPTIONS = self::get_options();
		
		$maps_api_key = isset($OPTIONS['api']) && isset($OPTIONS['api']['google']) && isset($OPTIONS['api']['google']['maps_api_key']) ? $OPTIONS['api']['google']['maps_api_key']: '';
		
		if(!is_admin())
			wp_register_script( 'lodash', AWESOME_LAUNCH_URI . "assets/js/vendor/lodash/lodash{$js_ext}", array(), AWESOME_LAUNCH_VER, true );
		
		wp_register_script( 'google-places', '//maps.googleapis.com/maps/api/js?libraries=places&key='.$maps_api_key, array(), AWESOME_LAUNCH_VER);
		wp_register_script( 'leaflet', AWESOME_LAUNCH_URI.'assets/js/vendor/leaflet/leaflet.js', array(), AWESOME_LAUNCH_VER, true);
		wp_register_script( 'vue', AWESOME_LAUNCH_URI . "assets/js/vendor/vue/vue{$js_ext}", array(), AWESOME_LAUNCH_VER, true );
		wp_register_script( 'awesome-vue-toolbox', AWESOME_LAUNCH_URI.'assets/js/awesome-vue-toolbox.js', array('vue', 'jquery'), AWESOME_LAUNCH_VER, true);
		wp_register_script( 'vue-slider-component', AWESOME_LAUNCH_URI."assets/js/vendor/awesoome-vue-slider-component/index.js", array('vue'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'vue-snippet-component', AWESOME_LAUNCH_URI."assets/js/vendor/awesome-vue-snippet-component/snippet-component.js", array('vue'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'js-cookie', AWESOME_LAUNCH_URI.'assets/js/vendor/js-cookie/js.cookie.js', array(), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'simplemde', AWESOME_LAUNCH_URI.'assets/js/vendor/simplemde/simplemde.min.js', array(), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'jquery-geocomplete', AWESOME_LAUNCH_URI.'assets/js/vendors/jquery/jquery.geocomplete.min.js', array('jquery'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'jquery-select2', AWESOME_LAUNCH_URI."assets/js/vendor/select2/select2{$js_ext}", array('jquery'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'localforage', AWESOME_LAUNCH_URI."assets/js/vendor/localforage/localforage{$js_ext}", array('jquery'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'masonry', AWESOME_LAUNCH_URI."assets/js/vendor/masonry/masonry.pkgd{$js_ext}", array('jquery'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'jquery-select2-full', AWESOME_LAUNCH_URI."assets/js/vendor/select2/select2.full{$js_ext}", array('jquery'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'tether', AWESOME_LAUNCH_URI."assets/js/vendor/tether/tether{$js_ext}", array('jquery'), AWESOME_LAUNCH_VER, true );
		wp_register_script( 'bootstrap-slider', AWESOME_LAUNCH_URI."assets/js/vendor/bootstrap-slider/bootstrap-slider{$js_ext}", array('jquery', 'bootstrap'), AWESOME_LAUNCH_VER, true  );
		wp_register_script( 'jquery-numeral', AWESOME_LAUNCH_URI.'assets/js/vendor/numeral/numeral.min.js', array('jquery'), AWESOME_LAUNCH_VER, true  );
		
		wp_register_script( 'bootstrap', AWESOME_LAUNCH_URI."assets/js/vendor/bootstrap/bootstrap{$js_ext}", array('jquery', 'tether'), AWESOME_LAUNCH_VER, true );
		
		wp_register_script( 'slick', AWESOME_LAUNCH_URI."assets/js/vendor/slick/slick{$js_ext}", array('jquery'), AWESOME_LAUNCH_VER );
		wp_register_script( 'vue-select', AWESOME_LAUNCH_URI."assets/js/vendor/vue-select/vue-select.js", array('jquery'), AWESOME_LAUNCH_VER );
		
		//mdb modules

		wp_register_script( 'makeit-core', AWESOME_LAUNCH_URI . 'assets/js/app.js', array('bootstrap', 'jquery','masonry', 'jquery-select2','slick', 'vue'), AWESOME_LAUNCH_VER, true );
		wp_localize_script('makeit-core','AWESOME_LAUNCH__ARGS', array(
				'API_BASE'	=>	site_url('/wp-json/cap')
			)
		);
		
		wp_register_script( 'faqs-viewer', AWESOME_LAUNCH_URI . 'assets/js/faqs-viewer.js', array('makeit-core'), AWESOME_LAUNCH_VER, true );
		wp_register_script( 'services-viewer', AWESOME_LAUNCH_URI . 'assets/js/services-viewer.js', array('makeit-core'), AWESOME_LAUNCH_VER, true );
	}
	
	/**
	* Populate the visitor's location based on their ip address
	*/
	private function get_visitor(){ 
		if(is_array($this->visitor) && !empty($this->visitor))
			return $this->visitor;
			
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		$this->visitor['geo_location'] = TimberHelper::transient(AWESOME_LAUNCH::get_transient_key("ip_geo_location_".str_replace(".","_",$ip)), function(){
		  
		   if(isset($_SERVER['GEOIP_LATITUDE'])):
			   return[
			   		'ip'			=>	isset( $_SERVER['GEOIP_ADDR'] ) ? $_SERVER['GEOIP_ADDR'] : $_SERVER['REMOTE_ADDR'],
			   		'city'			=>	$_SERVER['GEOIP_CITY'],
			   		'region'		=>	$_SERVER['GEOIP_REGION'],
			   		'region_name'	=>	$_SERVER['GEOIP_REGION_NAME'],
			   		'postal_code'	=>	$_SERVER['GEOIP_POSTAL_CODE'],
			   		'country'		=>	$_SERVER['GEOIP_COUNTRY_CODE'],
			   		'country_name'	=>	$_SERVER['GEOIP_COUNTRY_NAME'],
			   		'loc'			=>	$_SERVER['GEOIP_LATITUDE'].','.$_SERVER['GEOIP_LONGITUDE'],
			   		'longitude'		=>	floatval($_SERVER['GEOIP_LONGITUDE']),
			   		'latitude'		=>	floatval($_SERVER['GEOIP_LATITUDE'])
			   	];
		   	else:
		   		return [
		   			'country'		=>	'US',
		   			'country_name'	=>	'United States',
		   			'loc'			=>	'39.8282'.','.'-98.5795',
		   			'longitude'		=>	-98.5795,
			   		'latitude'		=>	39.8282
		   		];
		   	endif;
		},AWESOME_LAUNCH::DATA_CACHE_TIME_SHORT);
		
		if(isset($this->visitor['geo_location']['loc'])){
			$loc = explode(',', $this->visitor['geo_location']['loc']);
			if(is_array($loc) && isset($loc[0]) )
				$this->visitor['geo_location']['lat'] = $loc[0];
		
			if(is_array($loc) && isset($loc[1]) )
				$this->visitor['geo_location']['lng'] = $loc[1];
		}
		
		return $this->visitor;
	}
	
	/**
	 * 
	 * 
	 */
	public function add_to_context($context){
    	global $post;
    	
    	$data = TimberHelper::transient(AWESOME_LAUNCH::get_transient_key("csip_global_context"), function(){
    		$data = array();
    		
    		$data['site'] = new TimberSite();
    		$data['cap']['AWESOME_LAUNCH_URI'] = AWESOME_LAUNCH_URI;
    		$data['meta']['url'] = site_url('/');
			$data['meta']['type'] = 'website';
			$data['options'] = self::get_options();
    		
    		return $data;
    	}, AWESOME_LAUNCH::DATA_CACHE_TIME_LONG);
    	
    	$context = array_merge($context, $data);
		$context['http_host'] = 'https://' . TimberURLHelper::get_host();
		
		if(!is_admin()):
			$context['wp_title'] = TimberHelper::get_wp_title();
			$context['is_user_logged_in'] = is_user_logged_in();
			$context['user'] = get_object_vars(new TimberUser());
		endif;
		
		$context['_POST'] = $_POST;
		$context['_GET']  = $_GET;
		$context['_REQUEST']  = $_REQUEST;

		if(!is_admin() && is_archive())
			$context['posts'] = Timber::query_posts();
		
		if(isset($post->ID))
			$context['post'] = new Awesome_Post_Launch($post->ID);
		
		$context['cap']['is_tablet'] = AWESOME_LAUNCH::$Mobile_Detect->isTablet();
		$context['cap']['is_mobile'] = AWESOME_LAUNCH::$Mobile_Detect->isMobile();
		$context['cap']['is_desktop'] = (!AWESOME_LAUNCH::$Mobile_Detect->isTablet() && !AWESOME_LAUNCH::$Mobile_Detect->isMobile() );
		
		return array_merge((array)$data, $context);
	}
	
	/**
	 * Category thumbnail fields.
	 */
	public function add_term_fields() {
		$context = array();
		
		Timber::render('admin/partials/category-term-meta.twig', $context);
	}

	/**
	 * Edit category thumbnail field.
	 *
	 * @param mixed $term Term (category) being edited
	 */
	public function edit_term_fields( $term ) {
		$thumbnail_id = absint( get_woocommerce_term_meta( $term->term_id, '_csip_term_meta_category_thumbnail_id', true ) );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_thumb_url( $thumbnail_id );
		} else {
			$image = wc_placeholder_img_src();
		}
		
		$context = array(
		    'image'         =>  esc_url($image),
		    'featured'      =>  get_term_meta($term->term_id, '_csip_term_meta_category_featured', true),
		    'excerpt'		=>	get_term_meta($term->term_id, '_csip_term_meta_category_excerpt', true),
		    'menu_label'	=>	get_term_meta($term->term_id, '_csip_term_meta_menu_label', true),
		    'menu_url'		=>	get_term_meta($term->term_id, '_csip_term_meta_url_override', true),
		    'non_catalog_link'	=>	get_term_meta($term->term_id, '_csip_term_meta_non_catalog_link', true),
		);
		
		Timber::render('admin/partials/category-term-meta.twig', $context);
	}
	
	/**
	 * 
	 */
	public function save_term_metas($term_id, $tt_id = '', $taxonomy = ''){
		foreach($_POST as $f_key => $field):
			if(!preg_match("/_csip_term_meta_/", $f_key)) continue;
			
			$field = trim($field);

			update_term_meta( $term_id, $f_key, csip_sanitize_input_KSES($field));
		endforeach;
	}
	
	public function bootstrap_api(){
	    foreach(glob(AWESOME_LAUNCH__DIR."/framework/api/*.php") as $file): 
    		require_once($file);
    	endforeach;
	}
	
	/**
     * 
     */
    protected function load_modules(){
    	$module_dirs = $dirs = array_filter(glob(trailingslashit( AWESOME_LAUNCH__DIR ).'framework/modules/*'), 'is_dir');
    	
    	$module_dirs = apply_filters('awesome_framework_module_dirs', $module_dirs);
    	
    	if(!is_array($module_dirs) && !empty($module_dirs))
    		return;
    		
    	foreach($module_dirs as $dir){
    		$dir = explode('/', trim($dir, '/') );
    		$dir = $dir[count($dir) -1];
    		
    		if(!file_exists(trailingslashit( AWESOME_LAUNCH__DIR )."framework/modules/$dir/$dir.php"))
    			continue;
    		
    		include_once(trailingslashit( AWESOME_LAUNCH__DIR )."framework/modules/$dir/$dir.php");
    	}
    }
	
	/**
     * load_includes
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    private function load_includes(){
        foreach(glob(AWESOME_LAUNCH__DIR."/framework/inc/*.php") as $file):				
			include_once($file); 
		endforeach;
    }
    
    /**
     * load_requires
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    private function load_requires(){
        foreach(glob(AWESOME_LAUNCH__DIR."framework/req/*.php") as $file): 			
			require_once($file); 
		endforeach;
    }
    
    /**
     * load_classes
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    private function load_classes(){	
        foreach(glob(AWESOME_LAUNCH__DIR."framework/classes/*.php") as $file):				
			require_once($file); 
			
			/** Load Widgets */
			if(strpos( strtolower( $file), 'widget') !== false){ 
				add_action('widgets_init', function() use ($file){
					register_widget( str_replace('.php', '', basename($file)) );
				});
			}
		endforeach;
    }
   
   /**
    * 
    */
    private function load_vendors(){
        require_once(AWESOME_LAUNCH__DIR."framework/vendor/autoload.php");
    }
    
    /**
	 * Bootstrap awesome framework modules views override To allow theme to override module views
	 */
	private function bootstrap_view_locations(){
		$views = [ 
			AWESOME_LAUNCH__DIR.'views'
		];
		
		if( isset( self::$OPTIONS['modules'] ) && !empty( self::$OPTIONS['modules'] ) ){
      $views = array_merge($views, array_map(function($m){
	    	$m = str_replace('_', '-', $m);
	    	$dir = trailingslashit(get_stylesheet_directory())."views/awesome-framework/{$m}";
	    
	    	if(is_dir($dir))
	    		return $dir;
	    		
	    	return false;
      }, self::$OPTIONS['modules'] ) );
    }
    
    if( isset( self::$OPTIONS['modules'] ) && !empty( self::$OPTIONS['modules'] ) ){
      $views = array_merge($views, array_map(function($m){
      	$m = str_replace('_', '-', $m);
      	return trailingslashit( AWESOME_LAUNCH__DIR )."framework/modules/$m/views";
      }, self::$OPTIONS['modules'] ) );
    }
  
    //Filter to only valid directories
    $views = array_filter($views, function($v){
    	if($v === false)
    		return false;
    	
    	return true;
    });
    
    // echo '<pre>'; var_dump($views); die();
    
		Timber::$locations = apply_filters('af_views', $views);
		Timber::$locations = apply_filters('awesome_framework_views', $views);
	}
    
    /**
     * 
     */
    private function init(){
    $this->bootstrap_view_locations();	
		$loader = new TimberLoader();
		
    add_action('wp_enqueue_scripts', array(&$this, 'load_global_styles_scripts'));
    add_action('wp_enqueue_scripts', array(&$this, 'load_frontend_scripts'));
    add_action('admin_enqueue_scripts', array(&$this,'load_global_styles_scripts') );
    add_action( 'rest_api_init', array(&$this,'bootstrap_api') );
    remove_action( 'wp_head', 'rel_canonical' );
		
		//Add default Visitor location for Awesome Directory module
		add_filter('awesome_framework_awesome_directory_jars', function($jars){
			$jars['VISITOR'] = $this->get_visitor();
			$jars['MAP_TILE'] = "//cartodb-basemaps-{s}.global.ssl.fastly.net/dark_all/{z}/{x}/{y}.png";
			
			return $jars;
		});
		
		add_filter( 'timber_context', array(&$this, 'add_to_context'), 10 );
        
        /** Add Extra classes to body */
        add_filter( 'body_class', array(&$this,'_body_class') );
        
        unset($loader);
        
        //Admin only hooks and scripts
        if(!is_admin()) return;
        
        add_action( 'category_add_form_fields', array( $this, 'add_term_fields' ) );
		add_action( 'category_edit_form_fields', array( $this, 'edit_term_fields' ), 10 );
    
        add_action( 'created_term', array( &$this, 'save_term_metas' ), 10, 3 );
		add_action( 'edit_term', array( &$this, 'save_term_metas' ), 10, 3 );
    }
}AWESOME_LAUNCH::get_instance();

function testElasticPHP(){
	$hosts = [
	    [
	    	'host'		=>	'elk.raddwebstudio.com',
	    	'port'		=>	'9200',
	    	'scheme'	=>	'http',
	    	'user'		=>	'elastic',
	    	'pass'		=>	'D@r11ngt0n2o1G'
	    ]
	    	
	];
	$client = ClientBuilder::create()->setHosts($hosts)->build(); //print_r($client); die();
	
	$indexParams = [
	    'index' => 'my_index'
	];
	// Create the index
	$response = [];
	try{
		$response = $client->indices()->create($indexParams);
		print_r($response); die();
	}catch( Elasticsearch\Common\Exceptions\NoNodesAvailableException $e){
		echo '<pre>';
		print_r($e->getMessage()); 
		echo '<br />';
		echo $e->getTraceAsString();
		die();	
	}
	
	print_r($response); die();
	
	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id',
	    'body' => ['testField' => 'abc']
	];
	$response = $client->index($params);
	print_r($response); die();
}
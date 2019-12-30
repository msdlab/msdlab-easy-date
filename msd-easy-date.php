<?php
/*
Plugin Name: MSD Easy Date
Description: A small set of tools to restrict display of content by date.
Author: MSDLAB
Version: 0.0.2
Author URI: http://msdlab.com
*/

if(!class_exists('GitHubPluginUpdater')){
    require_once (plugin_dir_path(__FILE__).'/lib/resource/GitHubPluginUpdater.php');
}

if ( is_admin() ) {
    new GitHubPluginUpdater( __FILE__, 'msdlab', "msdlab-easy-date" );
}

global $msd_easy_date;

/*
 * Pull in some stuff from other files
*/
if(!function_exists('requireDir')){
    function requireDir($dir){
        $dh = @opendir($dir);

        if (!$dh) {
            throw new Exception("Cannot open directory $dir");
        } else {
            while($file = readdir($dh)){
                $files[] = $file;
            }
            closedir($dh);
            sort($files); //ensure alpha order
            foreach($files AS $file){
                if ($file != '.' && $file != '..') {
                    $requiredFile = $dir . DIRECTORY_SEPARATOR . $file;
                    if ('.php' === substr($file, strlen($file) - 4)) {
                        require_once $requiredFile;
                    } elseif (is_dir($requiredFile)) {
                        requireDir($requiredFile);
                    }
                }
            }
        }
        unset($dh, $dir, $file, $requiredFile);
    }
}
if (!class_exists('MSDEasyDate')) {
    class MSDEasyDate {
        //Properites
        /**
         * @var string The plugin version
         */
        var $version = '0.0.1';
        
        /**
         * @var string The options string name for this plugin
         */
        var $optionsName = 'msd_easy_date_options';
        
        /**
         * @var string $nonce String used for nonce security
         */
        var $nonce = 'msd_easy_date-update-options';
        
        /**
         * @var string $localizationDomain Domain used for localization
         */
        var $localizationDomain = "msd_easy_date";
        
        /**
         * @var string $pluginurl The path to this plugin
         */
        var $plugin_url = '';
        /**
         * @var string $pluginurlpath The path to this plugin
         */
        var $plugin_path = '';
        
        /**
         * @var array $options Stores the options for this plugin
         */
        var $options = array();
        //Methods
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //"Constants" setup
            $this->plugin_url = plugin_dir_url(__FILE__).'/';
            $this->plugin_path = plugin_dir_path(__FILE__).'/';
            //Initialize the options
            $this->get_options();
            //check requirements
            register_activation_hook(__FILE__, array(&$this,'check_requirements'));
            //get sub-packages
            requireDir(plugin_dir_path(__FILE__).'/lib/inc');
            add_action( 'wp_enqueue_scripts', array( &$this, 'maybe_load_bootstrap' ), 30 );
            add_action( 'admin_enqueue_scripts', array( &$this, 'maybe_load_jqueryui' ), 30 );
            add_action('admin_menu', array(&$this,'settings_page'));
            //here are some examples to get started with
            if(class_exists('MSDEasyDateShortcodes')){
                add_action( 'plugins_loaded', array( 'MSDEasyDateShortcodes', 'get_instance' ) );
            }
        }

        /**
         * @desc Loads the options. Responsible for handling upgrades and default option values.
         * @return array
         */
        function check_options() {
            $options = null;
            if (!$options = get_option($this->optionsName)) {
                // default options for a clean install
                $options = array(
                        'version' => $this->version,
                        'reset' => true
                );
                update_option($this->optionsName, $options);
            }
            else {
                // check for upgrades
                if (isset($options['version'])) {
                    if ($options['version'] < $this->version) {
                        // post v1.0 upgrade logic goes here
                    }
                }
                else {
                    // pre v1.0 updates
                    if (isset($options['admin'])) {
                        unset($options['admin']);
                        $options['version'] = $this->version;
                        $options['reset'] = true;
                        update_option($this->optionsName, $options);
                    }
                }
            }
            return $options;
        }
        
        /**
         * @desc Retrieves the plugin options from the database.
         */
        function get_options() {
            $options = $this->check_options();
            $this->options = $options;
        }
        /**
         * @desc Check to see if requirements are met
         */
        function check_requirements(){
            
        }
        /***************************/
        
        function maybe_load_bootstrap(){
            if(!wp_script_is( 'bootstrap-jquery', $list = 'enqueued' ) && !wp_script_is( 'bootstrap', $list = 'enqueued' )){
                wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',array('jquery'));
            }
            if(!wp_style_is( 'bootstrap-style', $list = 'enqueued' ) && !wp_style_is( 'bootstrap', $list = 'enqueued' )){
                wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
            }
        }
        function maybe_load_jqueryui(){
            if(!wp_script_is( 'jquery-ui-core', $list = 'enqueued' )){
                wp_enqueue_script('jquery-ui-core');
            }
            if(!wp_script_is( 'jquery-ui-datepicker', $list = 'enqueued' )){
                wp_enqueue_script('jquery-ui-datepicker');
            }
            if(!wp_style_is( 'jquery-ui-smoothness', $list = 'enqueued' )){
                global $wp_scripts;
                $ui = $wp_scripts->query('jquery-ui-core');
                $protocol = is_ssl() ? 'https' : 'http';
                $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
                wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
            }
            wp_enqueue_style('easy-date-style',$this->plugin_url.'lib/css/style.css');
        }
        
        function settings_page()
        {
            if ( count($_POST) > 0 && isset($_POST['easy_date_settings']) )
            {
                $options = array (
                'clientid',
                );
                
                foreach ( $options as $opt )
                {
                    delete_option ( 'easydate_'.$opt, $_POST[$opt] );
                    add_option ( 'easydate_'.$opt, $_POST[$opt] );   
                }           
                 
            }
            add_menu_page(__('EasyDate'),__('EasyDate'), 'administrator', 'easy-date-options', array(&$this,'settings_page_content'),'dashicons-calendar');
        }
        function settings_page_content()
        {
        
            ?>
        <style>
            span.note{
                display: block;
                font-size: 0.9em;
                font-style: italic;
                color: #999999;
            }
            body{
                background-color: transparent;
            }
            .input-table.even{background-color: rgba(0,0,0,0.1);padding: 2rem 0;}
            .input-table .description{display:none}
            .input-table li:after{content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}
            .input-table label{display:block;font-weight:bold;margin-right:1%;float:left;width:14%;text-align:right}
            .input-table label span{display:inline;font-weight:normal}
            .input-table span{color:#999;display:block}
            .input-table .input{width:85%;float:left}
            .input-table .input .half{width:48%;float:left}
            .input-table textarea,.input-table input[type='text'],.input-table select{display:inline;margin-bottom:3px;width:90%}
            .input-table .mceIframeContainer{background:#fff}
            .input-table h4{color:#999;font-size:1em;margin:15px 6px;text-transform:uppercase}
        </style>
        <div class="wrap">
            <h2>Easy Date Settings</h2>
            <p>This initial version of the EasyDate plugin provides a simple shortcode [easy-date][/easy-date] to show or hide small portions of content based on the current date.</p>
            <p>The shortcode accepts the following parameters:
                <dl>
                    <dt>start</dt>
                    <dd>The date you would like the content within the shortcode to begin display. If you do not specify a start date, it will be default to a far past date.</dd>
                    <dt>end</dt>
                    <dd>The date you would like the content within the shortcode to stop display. If you do not specify an end date, it will be default to a far future date.</dd>
                    <dt>default</dt>
                    <dd>The content (HTML string) you would like to display when the current date is not within the allowed displayed window. The default is an empty string.</dd>
                    <dt>annual</dt>
                    <dd>If you would like the date window to be reset annually on the same dates, set annual to 1.</dd>
                    <dt>monthly</dt>
                    <dd>If you would like the date window to be reset monthly on the same dates, set monthly to 1.</dd>
                </dl>
                </p>
            <?php /*
        <form method="post" action="">
              <ul class="input-table">
                  <li>
                      <label for="newton_code">ClientID</label>
                      <div class="input">
                        <input name="clientid" type="text" id="clientid" value="<?php echo get_option('njb_clientid'); ?>" class="regular-text" />
                      </div>
                  </li>
              </ul>
        
            <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="Save Changes" />
            <input type="hidden" name="easydate_settings" value="save" style="display:none;" />
            </p>
        </form>*/?>
        </div>
        <?php
        }
        
        function in_date_window($date = '',$start = 0,$end = 0, $convert = TRUE){
            if($convert){
                $start = $start?strtotime($start):strtotime(0);
                $end = $end?strtotime($end):33923664000;
            }
            if(empty($date)){
                $convert = FALSE;
                $date = time();
            }
            if($convert){
                $date = strtotime($date);
            }
            if(($date >= $start && $date <= $end) || ($date >= $start && $end == '') || ($start == '' && $end == '')){
                return TRUE;
            } else {
                return FALSE;
            }
        }
        
  } //End Class
} //End if class exists statement

//instantiate
$msd_easy_date = new MSDEasyDate();
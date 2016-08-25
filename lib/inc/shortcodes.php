<?php
class MSDEasyDateShortcodes{
    /**
         * A reference to an instance of this class.
         */
        private static $instance;


        /**
         * Returns an instance of this class. 
         */
        public static function get_instance() {

                if( null == self::$instance ) {
                        self::$instance = new MSDEasyDateShortcodes();
                } 

                return self::$instance;

        } 
        
    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct() {
        add_action('init', array(&$this,'shortcode_button_init'));
        add_shortcode('easy-date',array(&$this,'easy_date_shortcode_handler'));
        add_shortcode('easy_date',array(&$this,'easy_date_shortcode_handler'));
    }
    
    function easy_date_shortcode_handler($atts, $content = null){
        extract(shortcode_atts( array(
            'start' => false,
            'end' => false,
            'annual' => false,
            'monthly' => false,
            'default' => ''
        ), $atts ));
        if($annual){
            $start = date('F j,',strtotime($start));
            $end = date('F j,',strtotime($end));
            if(strtotime($end)<strtotime($start)){
                $today = strtotime(date('F j'));
                if($today<$end){
                    $start .= date('Y')-1;
                    $end .= date('Y');
                } else {
                    $start .= date('Y');
                    $end .= date('Y')+1;
                }
            } else {
                $start .= date('Y');
                $end .= date('Y');
            }
        } elseif($monthly){
            $start = date('F ');
            $start .= date('j,',strtotime($start));
            $start .= date('Y');
            $end = date('F ');
            $end .= date('j,',strtotime($end));
            $end .= date('Y');
        }
        if(MSDEasyDate::in_date_window('',$start,$end)){
            return do_shortcode($content);
        } else {
            return $default;
        }
    }


        function shortcode_button_init() {
        
              //Abort early if the user will never see TinyMCE
              if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
                   return;
        
              //Add a callback to regiser our tinymce plugin   
              add_filter("mce_external_plugins", array(&$this,'register_tinymce_plugin')); 
        
              // Add a callback to add our button to the TinyMCE toolbar
              add_filter('mce_buttons', array(&$this,'add_tinymce_button'));
        }
        
        
        //This callback registers our plug-in
        function register_tinymce_plugin($plugin_array) {
            global $msd_easy_date;
            $plugin_array['msd_easy_date_button'] = $msd_easy_date->plugin_url.'lib/js/tinymce_button.js';
            return $plugin_array;
        }
        
        //This callback adds our button to the toolbar
        function add_tinymce_button($buttons) {
                    //Add the button ID to the $button array
            $buttons[] = "msd_easy_date_button";
            return $buttons;
        }


}
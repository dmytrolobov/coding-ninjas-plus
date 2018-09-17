<?php
	/*
		Plugin Name: Coding Ninjas Plus
		Description: Coding Ninjas Plus
		Author: Dmytro Lobov.
		Author URI: https://profiles.wordpress.org/lobov
		Plugin URI: https://wordpress.org/
		Version: 1.0
		Text Domain: cn-plus
	*/
	
	if( !class_exists( 'Coding_Ninjas_Plus' ) ) {
		
		class Coding_Ninjas_Plus {
			
			function __construct() {						
				add_action('init', array( $this, 'load_textdomain' ) );
				add_action( 'admin_notices', array( $this, 'cod_admin_notice' ) );				
				add_action( 'init', array( $this, 'freelancer_post_type' ) );				
				add_filter( 'enter_title_here', array( $this, 'freelancer_post_title' ) );				
				add_action('add_meta_boxes', array( $this, 'freelancers_add_box_task' ) );				
				add_action( 'save_post', array( $this, 'freelancer_save_task' ) );				
				add_filter( 'document_title_parts', array( $this, 'menu_title' ) );				
				add_filter( 'cn_tasks_thead_cols', array( $this, 'freelanse_table_col' ) );				
				add_filter( 'cn_tasks_tbody_row_cols', array( $this, 'freelanse_table_row' ), 10, 2 );				
				add_action('wp_enqueue_scripts', array( $this, 'table_scripts' ), 22 );				
				add_filter( 'cn_menu', array( $this, 'add_menu' ) );				
				add_action( 'wp_footer', array( $this, 'modal_window' ) );					
				if( defined('DOING_AJAX') && DOING_AJAX ){
					add_action( 'wp_ajax_send_modal_window', array($this, 'send_new_task' ) );
					add_action( 'wp_ajax_nopriv_send_modal_window', array($this, 'send_new_task') );
				}				
				add_shortcode('cn_dashboard', array($this, 'shortcode_dashboard') );				
			}	
			// Localization
			function load_textdomain(){
				load_plugin_textdomain('cn-plus', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
			}
			
			// Display a warning message requesting to install the basic plugin
			function cod_admin_notice(){
				if( !is_plugin_active( 'coding-ninjas/coding-ninjas.php' ) ){
					echo '<div class="notice notice-error"> <p/>Please, activate the plugin <b>"Coding Ninjas"</b><p/></div>';
				}				
			}
			
			// Create a post type "freelancer"
			function freelancer_post_type() {
				$labels = array(
					'name'                  => __( 'Freelancer', 'cn-plus' ),
					'singular_name'         => __( 'Freelancer',  'cn-plus' ),
					'menu_name'             => __( 'Freelancers', 'cn-plus' ),
					'name_admin_bar'        => __( 'Freelancer',  'cn-plus' ),
					'add_new'               => __( 'Add New', 'cn-plus' ),
					'add_new_item'          => __( 'Add New Freelancer', 'cn-plus' ),
					'new_item'              => __( 'New Freelancer', 'cn-plus' ),
					'edit_item'             => __( 'Edit Freelancer', 'cn-plus' ),
					'view_item'             => __( 'View Freelancer', 'cn-plus' ),
					'all_items'             => __( 'All Freelancers', 'cn-plus' ),
					'search_items'          => __( 'Search Freelancers', 'cn-plus' ),
					'parent_item_colon'     => __( 'Parent Freelancers:', 'cn-plus' ),
					'not_found'             => __( 'No freelancers found.', 'cn-plus' ),
					'not_found_in_trash'    => __( 'No freelancers found in Trash.', 'cn-plus' ),
					'featured_image'        => __( 'Freelancer avatar', 'cn-plus' ),
					'set_featured_image'    => __( 'Set avatar', 'cn-plus' ),
					'remove_featured_image' => __( 'Remove avatar', 'cn-plus' ),				
        );
				
        $args = array(
					'labels'             => $labels,
					'public'             => true,
					'publicly_queryable' => true,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'query_var'          => true,
					'rewrite'            => array( 'slug' => 'freelancer' ),						
					'menu_icon'          => 'dashicons-groups',
					'capability_type'    => 'post',
					'has_archive'        => true,
					'hierarchical'       => false,
					'menu_position'      => 58,
					'supports'           => array( 'title', 'thumbnail' ),				
        );
				register_post_type( 'freelancer', $args );				
			}
			
			// Chanche placeholder for post type "freelancer" title 
			function freelancer_post_title( $input ) {
				if ( 'freelancer' === get_post_type() ) {
					return __( 'Enter Freelancer name', 'cn-plus' );
				}				
				return $input;
			}
			
			// Add a metabox with a drop-down list of all freelancers on the page for editing tasks
			function freelancers_add_box_task(){
				$screens = 'task';
				add_meta_box( 'freelancers_section', __( 'Freelancer', 'cn-plus' ), array( $this, 'freelancers_section_select' ), $screens, 'side' );
			}
			
			function freelancers_section_select( $post, $meta ) {
				global $wpdb;
				
				$freelancers = 'freelancer';				
				
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s and post_status = 'publish'", $freelancers ), ARRAY_A );				
				
				if ( ! $results )
        return;
				
				$post_id = $post->ID;				
				$freelancer_id = get_post_meta( $post_id, '_freelancer', true  );				
				
				$output = '<select name="freelancer" id="freelancer">';				
				$output .= !empty( $freelancer_id ) ? '<option value="0">Select freelancer</option>' : '<option value="0" selected="selected">Select freelancer</option>';
				
				foreach( $results as $index => $post ) {
					$selected = ( $post['ID'] == $freelancer_id ) ? ' selected="selected"' : '';
					$output .= '<option value="' . $post['ID'] . '"' . $selected . '>' . $post['post_title'] . '</option>';
				}				
				
				$output .= '</select>'; 				
				
				wp_nonce_field( plugin_basename(__FILE__), 'freelancer_noncename' );				
				echo $output;
			}
			
			// Save a freelancer for task in the admin panel
			function freelancer_save_task( $post_id ) {
				if ( ! isset( $_POST['freelancer'] ) )
				return;
				
				if ( ! wp_verify_nonce( $_POST['freelancer_noncename'], plugin_basename(__FILE__) ) )
				return;
				
				if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
				return;
				
				if( ! current_user_can( 'edit_post', $post_id ) )
				return;				
				
				$freelancer_id = sanitize_text_field( $_POST['freelancer'] );
				
				update_post_meta( $post_id, '_freelancer', $freelancer_id );
				
			}
			
			// Change page titles for the pages “Tasks” and “Dashboard”
			function menu_title( $title_parts ) {
				if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/dashboard' ) !== false ) { 
	        $title_parts['title'] = __('Dashboard', 'cn-plus');
				}
				elseif ( strpos( $_SERVER[ 'REQUEST_URI' ], '/tasks' ) !== false ) { 
					$title_parts['title'] = __('Tasks', 'cn-plus');
				}
				
				return $title_parts;
			}
			
			// Add colum 'Freelancer' in the Task table
			function freelanse_table_col( $cols ) {
				$cols = [
					__('ID', 'cn'),
					__('Title', 'cn'),
					__('Freelancer', 'cn-plus'),
					__('Date', 'cn')
        ];
				
				return $cols;
			}
			
			// Display the name of a selected freelancer for every task.
			function freelanse_table_row( $cols, $task ) {
			  $ppp = get_page_by_title( $task->title(), $output = OBJECT, $post_type = 'task' );								
				
				$freelancer_id = get_post_meta( $ppp->ID, '_freelancer', true  );
				if ( absint( $freelancer_id ) ) {
					$freelancer = get_the_title( $freelancer_id );
				}
				else {
					$freelancer = __('Not selected yet', 'cn-plus');
				}
				$cols = [
					$task->id(),
					$task->title(),
					$freelancer,
					$task->cdate()
				];
				
				return $cols;
			}
			
			// Include the style and scripts on the page Task
			function table_scripts() {
				if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/tasks' ) !== false ) {
					wp_enqueue_script( 'datatable', plugins_url('assets/js/dataTables.js', __FILE__) , array( 'jquery' ) );
					wp_enqueue_script( 'datatable-custom', plugins_url('assets/js/custom.js', __FILE__), array( 'jquery' ) );					
					wp_enqueue_style('datatable-css', plugins_url('assets/css/data_style.css', __FILE__), null );
					
				}
			}
			
			// Add an option "Add New Task" to the sidebar menu 			
			function add_menu( $menu ) {
				$add_menu = [
					'#modal' => [
					'title'  => __('Add New Task', 'cn-plus'),
					'icon'   => 'fa-plus-circle'
					],            
        ];
				$menu = array_merge( $menu, $add_menu );
				
				return $menu;
			}
			
			// Insert the modal window on the pages “Tasks” and “Dashboard”
			function modal_window() {
				if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/dashboard' ) !== false ) {
					include_once ( 'modal_window.php' );
					wp_enqueue_script( 'modal', plugins_url('assets/js/modal.js', __FILE__) , array( 'jquery' ) );					
					wp_localize_script( 'modal', 'send_modal_form', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				}
				elseif ( strpos( $_SERVER[ 'REQUEST_URI' ], '/tasks' ) !== false ) {
					include_once ( 'modal_window.php' );
					wp_enqueue_script( 'modal', plugins_url('assets/js/modal.js', __FILE__) , array( 'jquery' ) );
					wp_localize_script( 'modal', 'send_modal_form', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				}
			}
			
			// Save a new task
			function send_new_task() {
				$title = sanitize_text_field( $_POST['title'] );
				$freelancer_id = sanitize_text_field( $_POST['freelancer'] );
				$task_data = array( 
					'post_title'    => wp_strip_all_tags( $title ),
					'post_status'   => 'publish',
					'post_type'     => 'task',
				);
				$task_id = wp_insert_post( $task_data );
				
				update_post_meta( $task_id, '_freelancer', $freelancer_id );
				
				exit();
				
			}
			
			// Show dashboard shortcode
			function shortcode_dashboard() {
				ob_start();
				require plugin_dir_path( __FILE__ ) . 'public/shortcode.php';				
				$content = ob_get_contents();
				ob_end_clean();					
				return $content;
			}
			
		}
		
		$plugin = new Coding_Ninjas_Plus();
	}	
	

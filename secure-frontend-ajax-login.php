<?php
/*
Plugin Name: Secure Frontend Ajax Login
Plugin URI: http://nddw.com/demo3/sws-res-slider/
Description: This plugin provides frotend login by ajax in popup and redirect to particular page
Version:  1.0.2
Author: Technoscore
Author URI: http://www.technoscore.com/
Text Domain: techno_
*/

add_action('admin_menu', 'techno_login_redirection');

function techno_login_redirection() {

	//create new top-level menu
	add_menu_page('Secure Frontend Ajax Login', 'Secure Frontend Ajax Login', 'administrator', __FILE__, 'techno_login_redirection_page');
	
		//call register settings function
	add_action( 'admin_init', 'techno_login_redirection_register_settings' );
}

function techno_login_redirection_register_settings() {
	//register our settings
	register_setting( 'techno-settings-group', 'techno_login_redirection' );
	
}

function techno_login_redirection_page() {
$args = array(
   'public'   => true,
	'posts_per_page' => -1,
);
?>
<div class="wrap">
<h1>Secure Frontend Ajax Login</h1>
<form method="post" action="options.php">
    <?php settings_fields( 'techno-settings-group' ); ?>
    <?php do_settings_sections( 'techno-settings-group' ); ?>
    <table class="form-table">
      
		   <tr valign="top">
        <th scope="row">Redirection Link</th>
        <td>
		<input type="text" name="techno_login_redirection" value="<?php echo esc_attr( get_option('techno_login_redirection') ); ?>" class="regular-text code"/>&nbsp; ex: http://www.example.com/page1</td>
        </tr>	
		
			
		<tr valign="top">
        <th scope="row">Login Shortcode</th>
        <td>[techno_redirection]</td>
        </tr>
    </table>

    <?php submit_button(); ?>
	
</form>
</div>

<?php  
}

function techno_redirection(){
	wp_enqueue_style( 'techno_popup', plugin_dir_url( __FILE__ ) . 'assets/css/techno_popup.css' );
	wp_enqueue_script( 'techno_form_validate', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.validate.js' );
	?>
	<button class="techno_popup">Login</button>
	<div class="popupForm" id="popupForm">
	<div class="darkTransBg"></div>
	<div class="formArea">
	<div id="closePopUp">
<img src="<? echo plugin_dir_url( __FILE__ ) ; ?>assets/images/close3.png" alt="close3" class="alignright size-full wp-image-92" height="36" width="36">
</div>

	<form class="techno_login" id="techno_login" name="techno_login" method="post">
		  <div class="form-group"><h1>Login</h1></div>
		  	 <div class="form-group"><div id="techno_login_err"></div></div>
		  <div class="form-group">
			 <span id="err"></span>
			 <input type="text" class="form-control" id="techno_user" name="techno_user" placeholder="User Name" required>
		  </div>
		  <div class="form-group">
			<span id="err"></span>
			<input type="password" class="form-control" id="techno_pass" name="techno_pass" placeholder="User Password" required>
		  </div>
			<button type="submit" class="button btn-default techno_submit" id="target">Sign In</button>
	 </form>

		  <div id='techno_loadingmessage' style="display:none">
			<img src='<? echo plugin_dir_url( __FILE__ ) ; ?>assets/images/loader.gif' />
		</div>
	</div>
	</div> 

	<script>
		jQuery('.techno_popup').click(function(){
		//jQuery(document).ready(function(){
		jQuery('#popupForm').fadeIn();
		});

		jQuery('#closePopUp').click( function(){
	jQuery('#popupForm').hide();
	});
	   jQuery(".techno_submit").click( function(e) {
	   e.preventDefault();
	   
		var $form = jQuery("#techno_login");
					  jQuery($form).validate({
						rules: {
						 "techno_user": {
								required: true,
								}, 
						 "techno_pass": {
								required: true,
								}, 
						},
					  
					});

					/* // check if the input is valid */
					if(! $form.valid()) return false;
				else{
					var se_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
						var final_link = se_ajax_url + "?action=techno_login";
						jQuery.ajax({
							 type : "post",
							 /* dataType : "json", */
							 url : final_link,
							 data : jQuery('.techno_login').serialize(),
							  beforeSend: function()
										{
											 jQuery('#techno_loadingmessage').show(); 
											 jQuery('#techno_login').hide(); 
										},
							 success: function(response) {
							 jQuery('#techno_loadingmessage').hide(); 
								   var techno_json = jQuery.parseJSON(response);
							   if(techno_json.loggedin == false) {
									jQuery('#techno_login').show(); 
								   jQuery("#techno_login_err").html(techno_json.message);
								   setTimeout(function(){
										jQuery('#techno_login_err').html(""); 
								   },'3000');
								}
								if(techno_json.loggedin == true)  {
								jQuery('.formArea').hide();
									if(techno_json.techno_has_link == true){
										window.location.replace('http://'+techno_json.techno_url);
									}
									if(techno_json.techno_has_link == false){
										window.location.replace(techno_json.techno_url);;
									}
								}
							 }
						  })   
		  }

	   });


	</script>
	<?
}
	add_shortcode('techno_redirection','techno_redirection');


add_action("wp_ajax_techno_login", "techno_login");
add_action("wp_ajax_nopriv_techno_login", "techno_login");
function techno_login() {
/* echo'<pre>';print_r($_POST);die; */
extract($_POST);
$info = array();
    $info['user_login'] = $techno_user;
    $info['user_password'] = $techno_pass;
    $info['remember'] = false;
	$user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
		echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
			 $techno_post_redirect =  esc_attr( get_option('techno_login_redirection') );
			if(isset($techno_post_redirect ) && !empty($techno_post_redirect )){
				$techno_post_redirect_new = str_replace( parse_url( $url, PHP_URL_SCHEME ) . 'http://', '',$techno_post_redirect);
				wp_set_current_user($user_signon->ID); 
				echo json_encode(array('loggedin'=>true, 'message'=>__(' successful, redirecting...'),'techno_url'=>$techno_post_redirect_new,'techno_has_link'=>true));			
			}else{ echo json_encode(array('loggedin'=>true, 'message'=>__(' successful, redirecting...'),'techno_url'=>site_url(),'techno_has_link'=>false));	 }
		
    }
	die();
}



// Creating the widget 
class techno_redirect_widget extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'techno_redirect_widget', 

// Widget name will appear in UI
__('Techno Redirect', 'techno_redirect_widget_domain'), 

// Widget description
array( 'description' => __( 'Simply redirect after login', 'techno_redirect_widget_domain' ), ) 
);
}

// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
// before and after widget arguments are defined by themes
echo $args['before_widget'];
 if( ! is_user_logged_in() )
{ echo do_shortcode('[techno_redirection]'); }else{ 
echo'<a href="'.wp_logout_url( home_url() ).'">Logout</a>';
 }

echo $args['after_widget'];
}
		
// Widget Backend 
public function form( $instance ) {}
	
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {}
} // Class wpb_widget ends here

// Register and load the widget
function techno_redirect_load_widget() {
	register_widget( 'techno_redirect_widget' );
}
add_action( 'widgets_init', 'techno_redirect_load_widget' );
?>
<?

register_activation_hook(__FILE__, 'hss_add_defaults');
register_uninstall_hook(__FILE__, 'hss_delete_plugin_options');
add_action('admin_init', 'hss_init' );

function hss_add_defaults() {
        $tmp = get_option('hss_woo_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
                delete_option('hss_woo_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
                $arr = array(   "api_key" => "",
                );
                update_option('hss_woo_options', $arr);
        }
}

function hss_delete_plugin_options() {
        delete_option('hss_woo_options');
}

function hss_init(){
        register_setting( 'hss_plugin_options', 'hss_woo_options', 'hss_validate_options' );

 	//hss_create_page( esc_sql( _x( 'my-videos', 'page_slug', 'woocommerce' ) ), 'woocommerce_my_videos_page_id', __( 'My Videos', 'woocommerce' ), '[woocommerce_my_videos]', hss_get_page_id( 'myaccount' ) );

}

function hss_create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
        global $wpdb;

        $option_value = get_option( $option );

        if ( $option_value > 0 && get_post( $option_value ) )
                return;

        $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
        if ( $page_found ) {
                if ( ! $option_value )
                        update_option( $option, $page_found );
                return;
        }

        $page_data = array(
        'post_status'           => 'publish',
        'post_type'             => 'page',
        'post_author'           => 1,
        'post_name'             => $slug,
        'post_title'            => $page_title,
        'post_content'          => $page_content,
        'post_parent'           => $post_parent,
        'comment_status'        => 'closed'
    );
    $page_id = wp_insert_post( $page_data );

    update_option( $option, $page_id );
}

function hss_get_page_id( $page ) {
                $page = apply_filters('woocommerce_get_' . $page . '_page_id', get_option('woocommerce_' . $page . '_page_id'));
                return ( $page ) ? $page : -1;
}


function hss_validate_options($input) {
         // strip html from textboxes
        $input['api_key'] =  wp_filter_nohtml_kses($input['api_key']); // Sanitize textarea input (strip html tags, and escape characters)
        return $input;
}

add_action('admin_head', 'my_action_javascript');

function my_action_javascript() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
    $('#myajax').click(function(){
        var data = {
            action: 'my_action'
        };
	$("#updateprogress").html("Updating... please wait!");

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
	    $("#updateprogress").html("");
            alert(response);
	    
        });
    });
});
</script>
<?php
}

add_action('wp_ajax_my_action', 'my_action_callback');

function my_action_callback() {
	$res = update_videos();
	if($res==True)
		echo "Success";
	else
		echo "Error occurred ".$res;
	die(); // this is required to return a proper result
}






add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

add_action('wp_ajax_get_download_links', 'get_download_links_callback');
function get_download_links_callback() {
 $purchase_id = $_POST['purchase_id'];
 #$video_id = get_post_meta($purchase_id, '_woo_video_id', true);
 $video_id = $purchase_id;
 echo get_video_download_links($video_id);

 die(); // this is required to return a proper result
}
 
add_action('wp_print_footer_scripts', 'get_download_links_javascript');
 
function get_download_links_javascript() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
    $('.myajaxdownloadlinks').attr("disabled", false);
    $('.myajaxdownloadlinks').click(function(event){
	$('#'+event.target.id).attr("disabled", true);
        var data = {
            action: 'get_download_links',
            purchase_id: event.target.id
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
	    //$('#'+event.target.id).css("visibility", "hidden");
            $("#download_links_"+event.target.id).html(response);
	    setTimeout(function() {
		    $('#download_links_'+event.target.id).html("");
		    $('#'+event.target.id).attr("disabled", false);
		    //$('#'+event.target.id).css("visibility", "visible");
	    }, 240000);
        });
    });
});
</script>
<?php
}





function hss_woo_options_page () {
?>
        <div class="wrap">

                <!-- Display Plugin Icon, Header, and Description -->
                <div class="icon32" id="icon-options-general"><br></div>
                <h2>HostStreamSell Plugin Settings</h2>
                <p>Please enter the settings below...</p>

                <!-- Beginning of the Plugin Options Form -->
                <form method="post" action="options.php">
                        <?php settings_fields('hss_plugin_options'); ?>
                        <?php $options = get_option('hss_woo_options'); ?>

                        <!-- Table Structure Containing Form Controls -->
                        <!-- Each Plugin Option Defined on a New Table Row -->
                        <table class="form-table">

                                <!-- Textbox Control -->
                                <tr>
                                        <th scope="row">HostStreamSell API Key<BR><i>(available from your account on www.hoststreamsell.com)</i></th>
                                        <td>
                                                <input type="text" size="40" name="hss_woo_options[api_key]" value="<?php echo $options['api_key']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Video Player Size<BR><i>(leave blank to use defaults)</i></th>
                                        <td>
                                                Width <input type="text" size="10" name="hss_woo_options[player_width_default]" value="<?php echo $options['player_width_default']; ?>" /> Height  <input type="text" size="10" name="hss_woo_options[player_height_default]" value="<?php echo $options['player_height_default']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Mobile Device Video Player Size<BR><i>(leave blank to use defaults)</i></th>
                                        <td>
                                                Width <input type="text" size="10" name="hss_woo_options[player_width_mobile]" value="<?php echo $options['player_width_mobile']; ?>" /> Height  <input type="text" size="10" name="hss_woo_options[player_height_mobile]" value="<?php echo $options['player_height_mobile']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">JW Player License Key<BR><i>(available from www.longtailvideo.com)</i></th>
                                        <td>
                                                <input type="text" size="50" name="hss_woo_options[jwplayer_license]" value="<?php echo $options['jwplayer_license']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Disable updating video descriptions</th>
                                        <td>
                                                <input type="checkbox" name="hss_woo_options[disable_desc_updates]" value="1"<?php checked( 1 == $options['disable_desc_updates']); ?> />
                                        </td>
                                </tr>				
				<tr>
				        <th scope="row">Add/Update Videos</th>
				        <td>
						<div><input type="button" value="Update" id="myajax" /></div>
                                        <div id="updateprogress"></div></td>
                                </tr>
                        </table>
                        <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        </p>
                </form>
        </div>
<?
}

function hss_menu () {
        add_options_page('HostStreamSell Admin','HSS WOO Admin','manage_options','hss_admin', 'hss_woo_options_page');
}

add_action('admin_menu','hss_menu');

function hss_woo_before_download_content($download_id) {
        global $post;
	global $is_iphone;
        global $user_ID;
	$video = "";
        if($post->post_type == 'product' && is_singular() && is_main_query()) {

			if(get_post_meta($post->ID, 'is_streaming_video', true)) {
				$options = get_option('hss_woo_options');
				$userId = $user_ID;
        
				$hss_video_id = get_post_meta($post->ID, '_woo_video_id', true);
		                $params = array(
		                   'method' => 'secure_videos.get_video_playback_details',
		                   'api_key' => $options['api_key'],
		                   'video_id' => $hss_video_id,
		                   'private_user_id' => $userId
		                );
				_log($params);
		                $response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
		                        'method' => 'POST',
		                        'timeout' => 15,
		                        'redirection' => 5,
		                        'httpversion' => '1.0',
		                        'blocking' => true,
		                        'headers' => array(),
		                        'body' => $params,
		                        'cookies' => array()
		                    )
		                );
		                $res = "";
		                if( is_wp_error( $response ) ) {
		                   $return_string .= 'Error occured retieving video information, please try refresh the page';
		                } else {
		                   $res = $response['body'];
		                }
		
		                $xml = new SimpleXMLElement($res);
				_log($xml);
		                $title = $xml->result->title;
		                $hss_video_title = $title;
		                $user_has_access = $xml->result->user_has_access;
				//$video = "".$user_has_access;
				if($user_has_access=="true")
					$video = "<center>You have access to this video</center>";
				elseif(is_user_logged_in()){
					_log("checking orders");
					$args=array(
                                        	'meta_key'=>'_customer_user',
                                                'meta_value'=> $userId,
                                                'post_type' => 'shop_order',
                                        );
                                        _log($args);
                                        $my_query = null;
                                        $my_query = new WP_Query($args);
                                        if( $my_query->have_posts() ) {
						_log("has orders");
						$video_post = $my_query->next_post();
                                                $order_id = $video_post->ID;
						$order = new WC_Order( $order_id );
					        if ( count( $order->get_items() ) > 0 ) {
					                foreach( $order->get_items() as $item ) {
								if(get_post_meta($item->ID, '_woo_hss_added', true)==false)
								{
									_log("order item video added false!");
									$product_obj = $order->get_product_from_item( $item );
						                        $product = $product_obj->get_post_data();
						                        if((get_post_meta($product->ID, 'is_streaming_video', true)) or (get_post_meta($product->ID, 'is_streaming_video_bundle', true))) {
						                                $options = get_option('hss_woo_options');
						                                $userId = $order->user_id;
                						                $ppv_option = null;
                                						$ppv_option = get_post_meta($product->ID, '_woo_ppv_id', true);
						                                _log("ppv option = ".$ppv_option);
						                                $params = array(
						                                   'method' => 'secure_videos.add_user_ppv',
						                                   'api_key' => $options['api_key'],
						                                   'ppv_id' => $ppv_option,
						                                   'private_user_id' => $userId
						                                );
						                                _log($params);
										try{
						                                $response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
						                                        'method' => 'POST',
						                                        'timeout' => 15,
						                                        'redirection' => 5,
						                                        'httpversion' => '1.0',
						                                        'blocking' => true,
						                                        'headers' => array(),
						                                        'body' => $params,
						                                        'cookies' => array()
						                                    )
						                                );
						                                if( is_wp_error( $response ) ) {
						                                        _log("error msg: ".$response->get_error_message()."\n");
                               							}else if( $response['response']['code'] != "200" ) {
						                                        _log("request code bad: ".$response['response']['code']."\n");
						                                }else{
						                                        _log("request code good: ".$response['response']['code']."\n");
							                                update_post_meta($item->ID, '_woo_hss_added', true);
						                                }
						                                $res = $response['body'];
	
        						                        $xml = new SimpleXMLElement($res);
						                                _log($xml);
										}catch(Exception $e){
											_log("Error");
										}
									}
								}
							}
						}
					}
				}
		                $description = $xml->result->description;
		                $feature_duration = $xml->result->feature_duration;
		                $trailer_duration = $xml->result->trailer_duration;
                		$video_width = $xml->result->width;
		                $video_height = $xml->result->height;
		                if($video_width>640){
					$video_width = "640";
					$video_height = "370";
		                }
		                $referrer = site_url();
				$hss_video_user_token = $xml->result->user_token;
		
		                $hss_video_mediaserver_ip = $xml->result->wowza_ip;
		
		                $hss_video_smil_token = "?privatetoken=".$hss_video_user_token;
		                $hss_video_mediaserver_ip = $xml->result->wowza_ip;
		
		                $hss_video_smil = $xml->result->smil;
		                $hss_video_big_thumb_url = $xml->result->big_thumb_url;
		                $hss_rtsp_url = $xml->result->rtsp_url;
		                $referrer = site_url();
		
				$content_width = $video_width;
				$content_height = $video_height;

		                if($is_iphone){
		                	if($content_width<320){
			                        $content_width=320;
					}
		                }

		                if($video_width>$content_width){
		                        $mod = $content_width%40;
		                        $video_width = $content_width-$mod;
		                        $multiple = $video_width/40;
		                        $video_height = $multiple*30;
		                }
				
				if($is_iphone){
	                                if($options['player_width_mobile']!="")
	                                        $video_width=$options['player_width_mobile'];
	                                if($options['player_height_mobile']!="")
	                                        $video_height=$options['player_height_mobile'];
				}else{
	                                if($options['player_width_default']!="")
	                                        $video_width=$options['player_width_default'];
        	                        if($options['player_height_default']!="")
                	                        $video_height=$options['player_height_default'];
				}

		                $video = $video."
		                <script type=\"text/javascript\" src=\"https://www.hoststreamsell.com/mod/secure_videos/jwplayer-6/jwplayer.js\"></script>
				<script type=\"text/javascript\" src=\"https://www.hoststreamsell.com/mod/secure_videos/jwplayer/swfobject.js\"></script>
				<script type=\"text/javascript\">jwplayer.key=\"".$options['jwplayer_license']."\";</script>
		                <center>
                		<div>
		                <div id='videoframe'>If you are seing this you may not have Flash installed!</div>

                		<SCRIPT type=\"text/javascript\">

		                var viewTrailer = false;
                		var videoFiles = new Array();;
		                var trailerFiles = new Array();;

                		var agent=navigator.userAgent.toLowerCase();
		                var is_iphone = (agent.indexOf('iphone')!=-1);
		                var is_ipad = (agent.indexOf('ipad')!=-1);
		                var is_playstation = (agent.indexOf('playstation')!=-1);
		                var is_safari = (agent.indexOf('safari')!=-1);
		                var is_iemobile = (agent.indexOf('iemobile')!=-1);
		                var is_blackberry = (agent.indexOf('BlackBerry')!=-1);
		                var is_android = (agent.indexOf('android')!=-1);
		                var is_webos = (agent.indexOf('webos')!=-1);
	
				if (is_iphone) { html5Player();}
                                else if (is_ipad) { html5Player(); }
				else if (is_android) { rtspPlayer(); }
				else if (is_webos) { rtspPlayer(); }
				else if (is_blackberry) { rtspPlayer(); }
				else if (is_playstation) { newJWPlayer(); }
				else { newJWPlayer(); }
		
		                function newJWPlayer()
		                {
					jwplayer('videoframe').setup({
					    playlist: [{
					        image: '$hss_video_big_thumb_url',
				        	sources: [{
					            file: 'https://www.hoststreamsell.com/mod/secure_videos/private_media_playlist_v2.php?params=".$hss_video_id."!".urlencode($referrer)."!".$hss_video_user_token."!',
					            type: 'rtmp'
					        },{
				        	    file: 'http://".$hss_video_mediaserver_ip.":1935/hss/smil:".$hss_video_smil."/playlist.m3u8".$hss_video_smil_token."&referer=".urlencode($referrer)."'
					        }]
					    }],
					    height: $video_height,
					    primary: 'flash',		
					    width: $video_width
					});
		                }

				function rtspPlayer()
				{
			                var player=document.getElementById(\"videoframe\");
					player.innerHTML='<A HREF=\"rtsp://".$hss_video_mediaserver_ip."/hss/mp4:".$hss_rtsp_url."".$hss_video_smil_token."&referer=".urlencode($referrer)."\">'+
					'<IMG SRC=\"".$hss_video_big_thumb_url."\" '+
					'ALT=\"Start Mobile Video\" '+
					'BORDER=\"0\" '+
					'HEIGHT=\"$video_height\"'+
					'WIDTH=\"$video_width\">'+
					'</A>';
				}

                                function html5Player()
                                {
                                        var player=document.getElementById(\"videoframe\");
                                        player.innerHTML='<video controls '+
                                        'src=\"http://".$hss_video_mediaserver_ip.":1935/hss/smil:".$hss_video_smil."/playlist.m3u8".$hss_video_smil_token."&referer=".urlencode($referrer)."\" '+
                                        'HEIGHT=\"".$video_height."\" '+
                                        'WIDTH=\"".$video_width."\" '+
                                        'poster=\"".$hss_video_big_thumb_url."\" '+
                                        'title=\"".$hss_video_title."\">'+
                                        '</video>';
                                }

			        </script>
				</div>
			        </center>";
				if($user_has_access=="true"){
				        $video .= "<BR><div><input type='button' id='$hss_video_id' class='myajaxdownloadlinks' value='Get Download Links'></div>
					<div id='download_links_$hss_video_id'></div>";
				}

			}
        }
	echo $video;
}

function my_tab( $tabs ) {
    $my_tab = array( 'my_tab' =>  array( 'title' => 'Video', 'priority' => 9, 'callback' => 'hss_woo_before_download_content' ) );

    return array_merge( $my_tab, $tabs );
}

add_filter( 'woocommerce_product_tabs', 'my_tab' );

function woo_complete_purchase_add_video($order_status, $order_id) {

	_log("woo_complete_purchase_add_video");
	// order object (optional but handy)
	$order = new WC_Order( $order_id );

	if ( count( $order->get_items() ) > 0 ) {
		foreach( $order->get_items() as $item ) {

			$product_obj = $order->get_product_from_item( $item );
			$product = $product_obj->get_post_data();
			if((get_post_meta($product->ID, 'is_streaming_video', true)) or (get_post_meta($product->ID, 'is_streaming_video_bundle', true))) {
                                $options = get_option('hss_woo_options');
                                $userId = $order->user_id;

				$ppv_option = null;
				//if(empty($download['options']))
				$ppv_option = get_post_meta($product->ID, '_woo_ppv_id', true);
				//else
				//	$ppv_option = $download['options']['price_id'];
				_log("ppv option = ".$ppv_option);
				try{
			        $params = array(
			           'method' => 'secure_videos.add_user_ppv',
			           'api_key' => $options['api_key'],
			           'ppv_id' => $ppv_option,
			           'private_user_id' => $userId
			        );
               			_log($params); 
				$response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
		                        'method' => 'POST',
		                        'timeout' => 15,
	                	        'redirection' => 5,
		                        'httpversion' => '1.0',
		                        'blocking' => true,
		                        'headers' => array(),
		                        'body' => $params,
		                        'cookies' => array()
        		            )
        		        );

				$video_id = get_post_meta($product->ID, '_woo_video_id', true);
				update_post_meta($item->ID, '_woo_video_id', $video_id);

		                if( is_wp_error( $response ) ) {
                		        _log("error msg: ".$response->get_error_message()."\n");
					update_post_meta($item->ID, '_woo_hss_added', false);
		                }else if( $response['response']['code'] != "200" ) {
                		        _log("request code bad: ".$response['response']['code']."\n");
					update_post_meta($item->ID, '_woo_hss_added', false);
		                }else{
                		        _log("request code good: ".$response['response']['code']."\n");
					update_post_meta($item->ID, '_woo_hss_added', true);
                		}
				
	                   		$res = $response['body'];

			                $xml = new SimpleXMLElement($res);
	                		_log($xml);
				}catch(Exception $e) {
				}
			}
		}
	}
	return $order_status;
}
add_action( 'woocommerce_payment_complete_order_status', 'woo_complete_purchase_add_video', 10, 2 );

function update_videos()
{
	#global $post;
	$options = get_option('hss_woo_options');

        $params = array(
          'method' => 'secure_videos.get_user_video_groups',
          'api_key' => $options['api_key']
        );

        $group_response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
                'method' => 'POST',
                'timeout' => 15,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $params,
                'cookies' => array()
            )
        );
        $group_res = "";
        if( is_wp_error( $group_response ) ) {
   	   _log("ERROR");
        } else {
           $group_res = $group_response['body'];
        }


        $group_xml = new SimpleXMLElement($group_res);
	_log($group_xml);

        $status = $group_xml->status;
        _log("STATUS: ".$status);
        if($status == "0")
        {
		$seen_videos = array();

        	$group_count = $group_xml->result->video_group_count;
                $group_index = 1;
                while($group_index <= $group_count)
                {
                	$group_video_count = (int)$group_xml->result[0]->{'video_group'.$group_index}[0]->video_count;
			_log("video count = ".$group_video_count);
                        if($group_video_count > 0){
				$group_video_post_ids = array();
				$group_video_post_index=0;
                        	$group_id = (string)$group_xml->result[0]->{'video_group'.$group_index}[0]->video_group_id;
                                $group_title = (string)$group_xml->result[0]->{'video_group'.$group_index}[0]->title[0];
                                $group_description = (string)$group_xml->result[0]->{'video_group'.$group_index}[0]->description[0];
				$group_thumbnail = (string)$group_xml->result[0]->{'video_group'.$group_index}[0]->thumbnail[0];
				_log("Group id=".$group_id);
				_log(get_cat_ID( $group_title ));
				if( !term_exists( $group_title,'product_cat' )){
				 	_log("Creating category ".$group_title);   	
					wp_insert_term(
					  $group_title, // the term 
					  'product_cat' // the taxonomy
					);
				}
				$params = array(
			          'method' => 'secure_videos.get_user_video_list_by_group_with_purchase_options',
			          'api_key' => $options['api_key'],
			          'group_id' => $group_id,
			        );
				_log("group_id=".$params['group_id']);
			        $response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
                        		   'method' => 'POST',
                     			   'timeout' => 15,
                     			   'redirection' => 5,
                     			   'httpversion' => '1.0',
                     			   'blocking' => true,
                     			   'headers' => array(),
                     			   'body' => $params,
                     			   'cookies' => array()
                 			)
                		);
			        $res = "";
			        if( is_wp_error( $response ) ) {
				   _log("ERROR");
			        } else {
			        	$res = $response['body'];
                		}

			        $xml = new SimpleXMLElement($res);
				_log($xml);
				_log("STATUS: ".$status);
			        if($status == "0")
                		{
                       			$count = (int)$xml->result->video_count;
					_log("Video count=".$count);
		                        $index = 1;
		                        while($index <= $count)
		                        {
						_log("checking video");
		                                $video_id = (string)$xml->result[0]->{'video'.$index}[0]->video_id;
                       			        $title = (string)$xml->result[0]->{'video'.$index}[0]->title[0];
		                                $description = (string)$xml->result[0]->{'video'.$index}[0]->description[0];
                       			        $thumbnail = (string)$xml->result[0]->{'video'.$index}[0]->thumbnail[0];
						$args=array(
						  'meta_key'=>'_woo_video_id',
						  'meta_value'=> $video_id,
						  'post_type' => 'product',
						);
						_log($args);
						$my_query = null;
						$my_query = new WP_Query($args);
						$post_ID = -1;
						if( $my_query->have_posts() ) {
							_log("Video already a post");
							$video_post = $my_query->next_post();
							_log("video_post ID=".$video_post->ID);
							if($options['disable_desc_updates']==1){
								$my_post = array(
								     'ID' => $video_post->ID,
								     'post_title' => $title,
								);
							}else{
                                                                $my_post = array(
                                                                     'ID' => $video_post->ID,
                                                                     'post_title' => $title,
                                                                     'post_content' => $description,
                                                                 );
							}
							// Update the post into the database
							#remove_action('save_post', 'wpse51363_save_post');
							$post_ID = wp_update_post( $my_post );
							_log("RESULT FROM UPDATE: ".$post_ID);
							#add_action('save_post', 'wpse51363_save_post');
						}else{
							// Create post object
							_log("Create video post");
							$my_post = array(
				  			     'post_title' => $title,
							     'post_content' => $description,
							     'post_status' => 'publish',
							     'post_author' => 1,
							     'post_type' => 'product',
							);
							
							// Insert the post into the database
							$post_ID = wp_insert_post( $my_post );

							$url = $thumbnail; 
							$tmp = download_url( $url );
    							$file_array = array(
    							    'name' => basename( $url ),
    							    'tmp_name' => $tmp
    							);

    							// Check for download errors
    							if ( is_wp_error( $tmp ) ) {
								_log($tmp);
        							@unlink( $file_array[ 'tmp_name' ] );
        							return $tmp;
    							}

    							$thumb_id = media_handle_sideload( $file_array, 0 );
    							// Check for handle sideload errors.
    							if ( is_wp_error( $thumb_id ) ) {
								_log($thumb_id);
        							@unlink( $file_array['tmp_name'] );
							        return $thumb_id;
    							}

    							$attachment_url = wp_get_attachment_url( $thumb_id );
							_log("Attachment URL (".$thumb_id."): ".$attachment_url);
    							// Do whatever you have to here
							set_post_thumbnail( $post_ID, $thumb_id );

						}
						$category_found = false;
						$terms = array();
						if(!in_array($video_id,$seen_videos))
						        array_push($seen_videos,$video_id);
						else
							$terms = wp_get_object_terms($post_ID,'product_cat');
						$vid_cats = array();
						if(!empty($terms)){
						  	if(!is_wp_error( $terms )){
								foreach($terms as $term){
									array_push($vid_cats,$term->name);
								}
							}
						}
						_log($vid_cats);
						if(!in_array($group_title,$vid_cats)){
							_log("adding term");
							array_push($vid_cats,$group_title);
							_log($vid_cats);
							wp_set_object_terms($post_ID,$vid_cats,'product_cat');
						}
						$term = get_term_by( 'name',$group_title,'product_cat');
						wp_update_term($term->term_id, 'product_cat', array('description' => $group_description));
						update_post_meta($post_ID, '_woo_video_id', $video_id);

						$group_video_post_ids[$group_video_post_index] = $post_ID;
						$group_video_post_index+=1;
					        $purchase_option_count = (int)$xml->result[0]->{'video'.$index}[0]->option_count;
						$prices = array();
						$option_index = 1;
						$option_price = "";
						$lowest_price = 0;
						$option_name = "";
						if($purchase_option_count > 0)
						{
							$purchase_option_details = array();
						        while($option_index <= $purchase_option_count)
						        {
						        	$option_id = (int)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->option_id;
						                $option_type = (string)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->type;
						                $option_price = (string)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->price;
								if( ( ( (float)$option_price) < $lowest_price) or ($lowest_price==0))
									$lowest_price = (float)$option_price;

					                        $bandwidth_cap = (string)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->bandwidth_cap;
					                        $time_limit = (string)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->time_limit;
					                        $rate_limit = (string)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->rate_limit;
						                $download_limit = (string)$xml->result[0]->{'video'.$index}[0]->{'option'.$option_index}[0]->download_limit;
								$option_name = $time_limit.' streaming access';
								if($bandwidth_cap!="Unlimited")
									$option_name = $option_name.' '.$bandwidth_cap.' Data Cap';
								if($rate_limit!="No limit")
                                                                        $option_name = $option_name.' rate limited to '.$rate_limit.' kbps';
								if($download_limit=="No Downloads")
                                                                        $option_name = $option_name.' (no download access)';
								elseif($download_limit=="Any Bitrate Available")
                                                                        $option_name = $option_name.' (includes download access)';
								else
									$option_name = $option_name.' (download accesss '.$download_limit.')';

								$prices[$option_id] = array('name' => $option_name,'amount' => $option_price);
								_log("option id=".$option_id);
								_log($prices[$option_id]["name"]);
						                $option_index+=1;
						        }
						}

						update_post_meta($post_ID, '_visibility','visible');
						update_post_meta($post_ID, '_downloadable','no');
						update_post_meta($post_ID, '_virtual','yes');
						_log("PostID=".$post_ID);
						if($option_index==1){
							//add no selling options
							delete_post_meta($post_ID, '_regular_price');
							delete_post_meta($post_ID, '_price');
							delete_post_meta($post_ID, '_woo_ppv_id');
						}elseif($option_index==2){
							update_post_meta($post_ID, '_regular_price',$option_price);
							update_post_meta($post_ID, '_price',$option_price);
							update_post_meta($post_ID, '_woo_ppv_id',$option_id);
						}else{
							update_post_meta($post_ID, '_regular_price',$option_price);
							update_post_meta($post_ID, '_price',$option_price);
							update_post_meta($post_ID, '_woo_ppv_id',$option_id);
						}
						update_post_meta($post_ID, 'is_streaming_video',true);
							
						$index+=1;
					}
                                }
			}
                        $group_index+=1;
                }
	}
	return True;
}

function get_video_download_links($hss_video_id) {

        global $user_ID;
        $options = get_option('hss_woo_options');
        $userId = $user_ID;

	//$encode_id = 162;

                $params = array(
                   'method' => 'secure_videos.get_all_video_download_links',
                   'api_key' => $options['api_key'],
                   'video_id' => $hss_video_id,
		   //'encode_id' => $encode_id,
                   'private_user_id' => $userId
                );
                _log($params);
                $response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
                        'method' => 'POST',
                        'timeout' => 15,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => array(),
                        'body' => $params,
                        'cookies' => array()
                    )
                );
                $res = "";
                if( is_wp_error( $response ) ) {
                   $return_string .= 'Error occured retieving video information, please try refresh the page';
                } else {
                   $res = $response['body'];
                }

                $xml = new SimpleXMLElement($res);
                _log($xml);

                $purchase_option_count = (int)$xml->result[0]->download_option_count;
                $option_index = 1;
		$return_string = "";
                if($purchase_option_count > 0)
                {
			$return_string = "<div>Video file downloads:</div>";
                	while($option_index <= $purchase_option_count)
                        {
                        	$url = $xml->result[0]->{'download_option'.$option_index}[0]->url;
                        	$name = $xml->result[0]->{'download_option'.$option_index}[0]->name;
				#$return_string = $return_string.'<LI><a href="'.$url.'">'.$name.'</a></LI>';
				$return_string = $return_string.'<div class="hss_download_file"><a href="'.$url.'">'.$name.'</a></div>';
				$option_index+=1;
			}
			//$return_string = $return_string."</UL>";
		}else{
			$return_string = "<div>No Video file downloads</div>";
		}


		return $return_string;
}

add_shortcode('hss_woo_list_purchased_videos', 'hss_woo_list_purchased_videos_function');
function hss_woo_list_purchased_videos_function($atts, $content, $sc){
        global $wp_query;
        global $current_user;
        $options = get_option('hss_woo_options');

	$type = 'product';
	$args=array(
	  'post_type' => $type,
	  'post_status' => 'publish',
	  'posts_per_page' => -1,
	  'caller_get_posts'=> 1
	);
	$my_query = null;
	$my_query = new WP_Query($args);
	if( $my_query->have_posts() ) {
	  while ($my_query->have_posts()) : $my_query->the_post(); ?>
	    <p><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></p>
	    <?php
	  endwhile;
	}
}

?>

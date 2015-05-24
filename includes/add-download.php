<?php

register_activation_hook(__FILE__, 'hss_add_defaults');
register_uninstall_hook(__FILE__, 'hss_delete_plugin_options');
add_action('admin_init', 'hss_init' );

function hss_add_defaults() {
        $tmp = get_option('hss_woo_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
                delete_option('hss_woo_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
                $arr = array(   "api_key" => "","jwplayer_stretching" => "uniform","logging" => "NORMAL", "database_id" => "0" );
                update_option('hss_woo_options', $arr);
        }
}

function hss_delete_plugin_options() {
        delete_option('hss_woo_options');
}

function hss_init(){
        register_setting( 'hss_plugin_options', 'hss_woo_options', 'hss_validate_options' );

 	//hss_create_page( esc_sql( _x( 'my-videos', 'page_slug', 'woocommerce' ) ), 'woocommerce_my_videos_page_id', __( 'My Videos', 'woocommerce' ), '[woocommerce_my_videos]', hss_get_page_id( 'myaccount' ) );

        $options = get_option('hss_woo_options');
        $options['responsive_player'] = 0;
        $options['disable_desc_updates'] = 0;
        $options['add_video_on_processing'] = 0;
        $options['add_video_on_processing'] = 0;
        $options['use_non_loggedin_video_links'] = 0;

        if(is_array($options)){
	        if (array_key_exists('database_id', $options)) {
	                if($options['database_id'] == ""){
	                        $options['database_id'] = "0";
	                        update_option('hss_woo_options', $options);
	                }
	        }else{
	                $options['database_id'] = "0";
	                update_option('hss_woo_options', $options);
	        }
		if (array_key_exists('watching_video_text', $options)==false) {
	                $options['watching_video_text'] = "You have access to this video";
	                update_option('hss_woo_options', $options);
	        }
	}
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
	$input['api_key'] =  trim(wp_filter_nohtml_kses($input['api_key']));

	if(!isset( $input['responsive_player'] ) )
		$input['responsive_player'] = 0;
        if(!isset( $input['disable_desc_updates'] ) )
                $input['disable_desc_updates'] = 0;
        if(!isset( $input['add_video_on_processing'] ) )
                $input['add_video_on_processing'] = 0;
        if(!isset( $input['add_video_on_processing'] ) )
                $input['add_video_on_processing'] = 0;
        if(!isset( $input['use_non_loggedin_video_links'] ) )
                $input['use_non_loggedin_video_links'] = 0;

        if (!is_numeric($input['database_id'])) {
                $input['database_id'] = "0";
        }else{
                $input['database_id'] =  trim(wp_filter_nohtml_kses($input['database_id']));
        }
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


// Register style sheet.
add_action( 'wp_enqueue_scripts', 'register_hss_woo_plugin_styles' );

/**
 * Register style sheet.
 */
function register_hss_woo_plugin_styles() {
        wp_register_style( 'woocommerce-hss-extension-for-streaming-video', plugins_url( 'woocommerce-hss-extension-for-streaming-video/css/hss-woo.css' ) );
        wp_enqueue_style( 'woocommerce-hss-extension-for-streaming-video' );
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
                                        <th scope="row">Website Reference ID<BR><i>(leave at 0 unless you sell the same videos from multiple WordPress websites, in which case each website needs a unique reference ID)</i></th>
                                        <td>
                                                <input type="text" size="40" name="hss_woo_options[database_id]" value="<?php echo $options['database_id']; ?>" />
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
                                        <th scope="row">Make Player Width and Height Responsive</th>
                                        <td>
                                                <input type="checkbox" name="hss_woo_options[responsive_player]" value="1"<?php checked( $options['responsive_player'], 1); ?> />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Reponsive Player Max Width<BR><i>(default is 640 if left blank, only used when Reponsive Player checkbox is checked)</i></th>
                                        <td>
                                                Width <input type="text" size="10" name="hss_woo_options[player_responsive_max_width]" value="<?php echo $options['player_responsive_max_width']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">JW Player License Key<BR><i>(available from www.longtailvideo.com)</i></th>
                                        <td>
                                                <input type="text" size="50" name="hss_woo_options[jwplayer_license]" value="<?php echo $options['jwplayer_license']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Subtitle Font Size<BR><i>(leave blank for default size)</i></th>
                                        <td>
                                                <input type="text" size="5" name="hss_woo_options[subtitle_font_size]" value="<?php echo $options['subtitle_font_size']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Disable updating video title and descriptions</th>
                                        <td>
                                                <input type="checkbox" name="hss_woo_options[disable_desc_updates]" value="1"<?php checked( $options['disable_desc_updates'], 1); ?> />
                                        </td>
                                </tr>			
                                <tr>
                                        <th scope="row">Add video access when order is in processing state</th>
                                        <td>
                                                <input type="checkbox" name="hss_woo_options[add_video_on_processing]" value="1"<?php checked( $options['add_video_on_processing'], 1); ?> />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Watching Trailer Text (leave blank for no message)</th>
                                        <td>
                                                <input type="text" size="50" name="hss_woo_options[watching_trailer_text]" value="<?php echo $options['watching_trailer_text']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Watching Full Video Text (leave blank for no message)</th>
                                        <td>
                                                <input type="text" size="50" name="hss_woo_options[watching_video_text]" value="<?php echo $options['watching_video_text']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Enable video sharing links for access to purchased videos by URL</th>
                                        <td>
                                                <input type="checkbox" name="hss_woo_options[use_non_loggedin_video_links]" value="1"<?php checked( $options['use_non_loggedin_video_links'], 1); ?> />
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
<?php
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
                                $guestcheckout = get_option( "woocommerce_enable_guest_checkout", "notfound" );
                                if($guestcheckout == "yes")
                                        $video .= "<BR><CENTER><B>WARNING - guest checkout is enabled. Please do not puchase without registering as you will not get access to the video</B></CENTER><BR>";

				$options = get_option('hss_woo_options');
				$userId = $user_ID;
       
				if(isset($_GET['videolink'])){
					global $wpdb;
					$videolink = $_GET['videolink'];
					$sql = "
					SELECT order_id FROM {$wpdb->prefix}woocommerce_order_itemmeta oim 
					LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi 
					ON oim.order_item_id = oi.order_item_id 
					WHERE meta_key = '_hss_woo_link' AND meta_value = '%s'
					GROUP BY order_id;
					";
					$order_id = $wpdb->get_col( $wpdb->prepare( $sql, $videolink ) );
					if ($order_id){
						$order = new WC_Order( $order_id[0] );
						$userId = $order->user_id;
					}
				}

                                if($userId!=0){
                                        $hss_errors = get_user_meta( $userId, "hss_errors", true );
                                        if (!empty($hss_errors)){
                                                _log("there are hss_errors");
						_log($hss_errors);
                                                foreach ($hss_errors as $key => $ppv_option) {
                                                        $params = array(
                                                           'method' => 'secure_videos.add_user_ppv',
                                                           'api_key' => $options['api_key'],
                                                           'ppv_id' => $ppv_option,
                                                           'private_user_id' => $userId,
                                                           'database_id' => $options['database_id']
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

                                                        // need to add method to record failed rest requests for retry

                                                        if( is_wp_error( $response ) ) {
                                                                _log("error msg: ".$response->get_error_message()."\n");
                                                        }else if( $response['response']['code'] != "200" ) {
                                                                _log("request code bad: ".$response['response']['code']."\n");
                                                        }else{
								_log("request code good: ".$key."=>".$ppv_option." ".$response['response']['code']."\n");
                                                                unset($hss_errors[$key]);
                                                                update_user_meta( $userId, "hss_errors", $hss_errors);
                                                                $hss_errors_new = get_user_meta( $userId, "hss_errors", true );
                                                                _log($hss_errors_new);
                                                        }
                                                }
                                        }
                                }
 
				$hss_video_id = get_post_meta($post->ID, '_woo_video_id', true);
                                $response = wp_remote_post( "https://www.hoststreamsell.com/api/1/xml/videos?api_key=".$options['api_key']."&video_id=$hss_video_id&private_user_id=$userId&database_id=".$options['database_id']."&expands=playback_details&force_allows=no", array(
                                        'method' => 'GET',
                                        'timeout' => 15,
                                        'redirection' => 5,
                                        'httpversion' => '1.0',
                                        'blocking' => true,
                                        'headers' => array(),
                                        //'body' => $params,
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
		                $user_can_download = $xml->result->user_can_download;
				//$video = "".$user_has_access;
				if($user_has_access=="true")
					$video .= '<div class="hss_woo_watching_video_text">'.$options['watching_video_text'].'</div>';
				else
					$video .= '<div class="hss_woo_watching_trailer_text">'.$options['watching_trailer_text'].'</div>';
		                $description = $xml->result->description;
		                $feature_duration = $xml->result->feature_duration;
		                $trailer_duration = $xml->result->trailer_duration;
                		$video_width = $xml->result->width;
		                $video_height = $xml->result->height;
				$aspect_ratio = $xml->result->aspect_ratio;
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
				$httpString = "http";
				if (is_ssl()) {
					$httpString = "https";
				}

		                $subtitle_count = $xml->result->subtitle_count;
		                $subtitle_index = 1;
				$subtitle_text = "";
				$default_language = "English";
				$captions = "";
				if($subtitle_count>0){
					$subtitle_text = ",
						tracks: [{";
			                while($subtitle_index <= $subtitle_count)
			                {
			                	$subtitle_label = (string)$xml->result[0]->subtitles->{'subtitle_label_'.$subtitle_index}[0];
						$subtitle_file = (string)$xml->result[0]->subtitles->{'subtitle_file_'.$subtitle_index}[0];
						$subtitle_text .= "
					            file: \"https://www.hoststreamsell.com/mod/secure_videos/subtitles/$subtitle_file?rand=".randomString()."\",
					            label: \"$subtitle_label\",
					            kind: \"captions\",
					            \"default\": true";
						$subtitle_index += 1;
						if($subtitle_index <= $subtitle_count){
							$subtitle_text .= "
                                                },{";
						}
					}
					$subtitle_text .= "
						}]";
                                        $fontSize = "";
                                        if($options["subtitle_font_size"]!=""){
                                                $fontSize = "
                                                        fontSize: ".$options["subtitle_font_size"].",";
                                        }
                                        $captions = "
                                                captions: {
                                                        color: '#FFFFFF',".$fontSize."
                                                        backgroundOpacity: 0
                                                },";

				}

		                $video .= "
		                <script type=\"text/javascript\" src=\"https://www.hoststreamsell.com/mod/secure_videos/jwplayer-6/jwplayer.js\"></script>
				<script type=\"text/javascript\">jwplayer.key=\"".$options['jwplayer_license']."\";</script>";
                                if($options["responsive_player"]==1){
                                        $responsive_width="640";
                                        if($options["player_responsive_max_width"]!="")
                                                $responsive_width=$options["player_responsive_max_width"];
                                        $video.="<div class='hss_video_player' style='max-width:".$responsive_width."px;'>";
                                }else{
                                        $video.="<div class='hss_video_player'>";
                                }
                                $video.="<div id='videoframe'>An error occurred setting up the video player</div>
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
					            file: '$httpString://www.hoststreamsell.com/mod/secure_videos/private_media_playlist_v2.php?params=".$hss_video_id."!".urlencode($referrer)."!".$hss_video_user_token."!',
					            type: 'rtmp'
					        },{
				        	    file: 'http://".$hss_video_mediaserver_ip.":1935/hss/smil:".$hss_video_smil."/playlist.m3u8".$hss_video_smil_token."&referer=".urlencode($referrer)."'
					        }]$subtitle_text
					    }],$captions
                                            primary: 'flash',   ";
                                if($options["responsive_player"]==1){
                                        $video.="                  width: '100%',
                                            aspectratio: '".$aspect_ratio."'";
                                }else{
                                        $video.="                 height: $video_height,
                                          width: $video_width";
                                }

        $video.="			});
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
				</div>";
				if($user_can_download=="true"){
				        $video .= "<div class='hss_download_button'><input type='button' id='$hss_video_id' class='myajaxdownloadlinks' value='Get Download Links'></div>
					<div class='hss_download_links' id='download_links_$hss_video_id'></div>";
				}

			}
        }
        if($user_has_access=="true"){
                echo $video;
                do_action( 'hss_woo_show_video_purchased_extra_content', $post->ID);
        }else{
                echo $video;
        }
}

function my_tab( $tabs ) {
    global $post;
    if(get_post_meta($post->ID, 'is_streaming_video', true)){
	    $my_tab = array( 'my_tab' =>  array( 'title' => 'Video', 'priority' => 9, 'callback' => 'hss_woo_before_download_content' ) );
    return array_merge( $my_tab, $tabs );
    }
}

add_filter( 'woocommerce_product_tabs', 'my_tab' );

function woo_complete_purchase_add_video($order_id) {

	_log("woo_complete_purchase_add_video");
	// order object (optional but handy)
	$order = new WC_Order( $order_id );
	$options = get_option('hss_woo_options');

	if(get_post_meta($order_id, '_hss_woo_processed',true))
	{
		_log("access already added, skipping...");
	}else{
		if ( count( $order->get_items() ) > 0 ) {
			foreach( $order->get_items() as $item_id => $item ) {
				$product_obj = $order->get_product_from_item( $item );
				$product = $product_obj->get_post_data();

				/*if((get_post_meta($product->ID, '_force_sell_synced_ids', true))){
					_log("_force_sell_synced_ids");
					$forced_sells = get_post_meta($product->ID, '_force_sell_synced_ids', true);
					foreach( $forced_sells as $forced_sell ) {
						_log("_force_sell_synced_id = ".$forced_sell);
						hss_add_video_access(get_post($forced_sell),$order);
					}
				}
                                if((get_post_meta($product->ID, '_force_sell_ids', true))){
                                        _log("_force_sell_ids");
                                        $forced_sells = get_post_meta($product->ID, '_force_sell_ids', true);
                                        foreach( $forced_sells as $forced_sell ) {
                                                _log("_force_sell_id = ".$forced_sell);
                                                hss_add_video_access(get_post($forced_sell),$order);
                                        }
                                }*/

				_log("product id = ".$product->ID);
				if((get_post_meta($product->ID, 'is_streaming_video', true)) or (get_post_meta($product->ID, 'is_streaming_video_bundle', true))) {
        	                        $userId = $order->user_id;

					$ppv_option = null;
					//if(empty($download['options']))
					$ppv_option = get_post_meta($product->ID, '_woo_ppv_id', true);

                                        if ( ! empty( $item['variation_id'] ) && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
                                              $var_product = get_post( $item['variation_id'] );
                                                _log("variation id = ".$var_product->ID);
                                              $ppv_option = get_post_meta($var_product->ID, '_woo_ppv_id', true);
                                        }

					//else
					//	$ppv_option = $download['options']['price_id'];
					_log("ppv option = ".$ppv_option);
				        $params = array(
				           'method' => 'secure_videos.add_user_ppv',
				           'api_key' => $options['api_key'],
				           'ppv_id' => $ppv_option,
				           'private_user_id' => $userId,
	                                   'database_id' => $options['database_id']
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

	
					//$video_id = get_post_meta($product->ID, '_woo_video_id', true);
					//update_post_meta($item->ID, '_woo_video_id', $video_id);

					if($options['use_non_loggedin_video_links']==1){
						$randString = md5(uniqid(rand(), true));
						wc_add_order_item_meta($item_id,'_hss_woo_link',$randString);
					}

	                                if( is_wp_error( $response ) ) {
        	                                _log("error msg: ".$response->get_error_message()."\n");
	                                        $hss_errors = get_user_meta( $userId, "hss_errors", true );
	                                        $hss_errors[] = $ppv_option;
	                                        update_user_meta( $userId, "hss_errors", $hss_errors);
	                                }else if( $response['response']['code'] != "200" ) {
	                                        _log("request code bad: ".$response['response']['code']."\n");
	                                        $hss_errors = get_user_meta( $userId, "hss_errors", true );
	                                        $hss_errors[] = $ppv_option;
	                                        update_user_meta( $userId, "hss_errors", $hss_errors);
	                                }else{
	                                        _log("request code good: ".$response['response']['code']."\n");
	                                }

		                	$res = $response['body'];

				        $xml = new SimpleXMLElement($res);
	        	        	_log($xml);
				}
			}
		}
	}
	update_post_meta($order_id, '_hss_woo_processed',true);
	#return $order_status;
	#return ‘completed’;
}
#add_action( 'woocommerce_payment_complete_order_status', 'woo_complete_purchase_add_video', 10, 2 );
add_action( 'woocommerce_order_status_completed', 'woo_complete_purchase_add_video');

function hss_add_video_access($product,$order){
	$options = get_option('hss_woo_options');
                                        $userId = $order->user_id;

                                        $ppv_option = null;
                                        //if(empty($download['options']))
                                        $ppv_option = get_post_meta($product->ID, '_woo_ppv_id', true);
                                        //else
                                        //      $ppv_option = $download['options']['price_id'];
                                        _log("ppv option = ".$ppv_option);
                                        $params = array(
                                           'method' => 'secure_videos.add_user_ppv',
                                           'api_key' => $options['api_key'],
                                           'ppv_id' => $ppv_option,
                                           'private_user_id' => $userId,
                                           'database_id' => $options['database_id']
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

                                        //$video_id = get_post_meta($product->ID, '_woo_video_id', true);
                                        //update_post_meta($item->order_item_id, '_woo_video_id', $video_id);

                                        $randString = md5(uniqid(rand(), true));
                                        wc_add_order_item_meta($item->order_item_id,'_hss_woo_link',$randString);

                                        if( is_wp_error( $response ) ) {
                                                _log("error msg: ".$response->get_error_message()."\n");
                                                $hss_errors = get_user_meta( $userId, "hss_errors", true );
                                                $hss_errors[] = $ppv_option;
                                                update_user_meta( $userId, "hss_errors", $hss_errors);
                                        }else if( $response['response']['code'] != "200" ) {
                                                _log("request code bad: ".$response['response']['code']."\n");
                                                $hss_errors = get_user_meta( $userId, "hss_errors", true );
                                                $hss_errors[] = $ppv_option;
                                                update_user_meta( $userId, "hss_errors", $hss_errors);
                                        }else{
                                                _log("request code good: ".$response['response']['code']."\n");
                                        }

                                        $res = $response['body'];

                                        $xml = new SimpleXMLElement($res);
                                        _log($xml);

}


function woo_complete_purchase_add_video_processing($order_id) {

        _log("woo_complete_purchase_add_video_processing");
        // order object (optional but handy)
        $order = new WC_Order( $order_id );
        $options = get_option('hss_woo_options');

	if($options['add_video_on_processing']==1){
		if ( count( $order->get_items() ) > 0 ) {
        	        foreach( $order->get_items() as $item_id => $item ) {

                	        $product_obj = $order->get_product_from_item( $item );
	                        $product = $product_obj->get_post_data();
	                        if((get_post_meta($product->ID, 'is_streaming_video', true)) or (get_post_meta($product->ID, 'is_streaming_video_bundle', true))) {
	                                $userId = $order->user_id;

	                                $ppv_option = null;
	                                //if(empty($download['options']))
	                                _log("product id = ".$product->ID);
	                                $ppv_option = get_post_meta($product->ID, '_woo_ppv_id', true);
	                                //else
	                                //      $ppv_option = $download['options']['price_id'];

	                                if ( ! empty( $item['variation_id'] ) && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
	                                      $var_product = get_post( $item['variation_id'] );
	                                	_log("variation id = ".$var_product->ID);
          	                              $ppv_option = get_post_meta($var_product->ID, '_woo_ppv_id', true);
                	                }

	                                _log("ppv option = ".$ppv_option);
	                                $params = array(
	                                   'method' => 'secure_videos.add_user_ppv',
	                                   'api_key' => $options['api_key'],
	                                   'ppv_id' => $ppv_option,
	                                   'private_user_id' => $userId,
					   'database_id' => $options['database_id']
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
	                                //update_post_meta($item->ID, '_woo_video_id', $video_id);

					if($options['use_non_loggedin_video_links']==1){
						$randString = md5(uniqid(rand(), true));
	                                        wc_add_order_item_meta($item_id,'_hss_woo_link',$randString);
					}

                	                if( is_wp_error( $response ) ) {
	                                        _log("error msg: ".$response->get_error_message()."\n");
	                                        $hss_errors = get_user_meta( $userId, "hss_errors", true );
	                                        $hss_errors[] = $ppv_option;
	                                        update_user_meta( $userId, "hss_errors", $hss_errors);
	                                }else if( $response['response']['code'] != "200" ) {
	                                        _log("request code bad: ".$response['response']['code']."\n");
	                                        $hss_errors = get_user_meta( $userId, "hss_errors", true );
	                                        $hss_errors[] = $ppv_option;
	                                        update_user_meta( $userId, "hss_errors", $hss_errors);
	                                }else{
	                                        _log("request code good: ".$response['response']['code']."\n");
	                                }
	
        	                        $res = $response['body'];

	                                $xml = new SimpleXMLElement($res);
	                                _log($xml);
				}
                        }
                }
		update_post_meta($order_id, '_hss_woo_processed',true);
        }
        #return $order_status;
        #return ‘completed’;
}

add_action( 'woocommerce_order_status_processing', 'woo_complete_purchase_add_video_processing');

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
	    $error_string = $group_response->get_error_message();
	    _log($error_string);
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
						$video_existed = false;
						if( $my_query->have_posts() ) {
							_log("Video already a post");
							$video_existed = true;
							$video_post = $my_query->next_post();
							_log("video_post ID=".$video_post->ID);
							if($options['disable_desc_updates']==1){
								$my_post = array(
								     'ID' => $video_post->ID,
								     'post_title' => $video_post->post_title,
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
							if($url!=""){
							$tmp = download_url( $url );
    							$file_array = array(
    							    'name' => basename( $url ),
    							    'tmp_name' => $tmp
    							);

    							// Check for download errors
    							if ( is_wp_error( $tmp ) ) {
								_log($tmp);
        							@unlink( $file_array[ 'tmp_name' ] );
        							#return $tmp;
    							}

    							$thumb_id = media_handle_sideload( $file_array, 0 );
    							// Check for handle sideload errors.
    							if ( is_wp_error( $thumb_id ) ) {
								_log($thumb_id);
        							@unlink( $file_array['tmp_name'] );
							        #return $thumb_id;
    							}

    							$attachment_url = wp_get_attachment_url( $thumb_id );
							#_log("Attachment URL (".$thumb_id."): ".$attachment_url);
    							// Do whatever you have to here
							set_post_thumbnail( $post_ID, $thumb_id );
							}

						}
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
								$option_name = __($time_limit.' access','my_text_domain');
								/*if($bandwidth_cap!="Unlimited")
									$option_name = $option_name.' '.$bandwidth_cap.' Data Cap';
								if($rate_limit!="No limit")
                                                                        $option_name = $option_name.' rate limited to '.$rate_limit.' kbps';
								if($download_limit=="No Downloads")
                                                                        $option_name = $option_name.' (no download access)';
								elseif($download_limit=="Any Bitrate Available")
                                                                        $option_name = $option_name.' (includes download access)';
								else
									$option_name = $option_name.' (download accesss '.$download_limit.')';
								*/
								$prices[$option_id] = array('name' => $option_name,'amount' => $option_price);
								_log("option id=".$option_id);
								_log($prices[$option_id]["name"]);
						                $option_index+=1;
						        }
						}

						update_post_meta($post_ID, '_visibility','visible');
						update_post_meta($post_ID, '_downloadable','yes');
						update_post_meta($post_ID, '_virtual','yes');
						_log("PostID=".$post_ID);
						if($option_index==1){
							//add no selling options
							_log("1");
                                                        $variations = get_posts( array(
                                                                'post_parent'    => $post_ID,
                                                                'post_type'    => 'product_variation',
                                                        ));

                                                        if( $variations ) {
                                                                foreach($variations as $variation_post)
                                                                        wp_delete_post( $variation_post->ID);
                                                        }
							delete_post_meta($post_ID, '_product_attributes');
							delete_post_meta($post_ID, '_regular_price');
							delete_post_meta($post_ID, '_price');
							delete_post_meta($post_ID, '_woo_ppv_id');
							wp_set_object_terms ($post_ID, 'standard', 'product_type');
						}elseif($option_index==2){
							_log("2 setting _woo_ppv_id to ".$option_id);
                                                        $variations = get_posts( array(
                                                                'post_parent'    => $post_ID,
                                                                'post_type'    => 'product_variation',
                                                        ));

                                                        if( $variations ) {
                                                                foreach($variations as $variation_post)
                                                                        wp_delete_post( $variation_post->ID);
                                                        }
							delete_post_meta($post_ID, '_product_attributes');
							update_post_meta($post_ID, '_regular_price',$option_price);
							update_post_meta($post_ID, '_price',$option_price);
							update_post_meta($post_ID, '_woo_ppv_id',$option_id);
							wp_set_object_terms ($post_ID, 'standard', 'product_type');
						}else{
							_log("3 post id=".$post_ID." setting _woo_ppv_id to ".$option_id);
							update_post_meta( $post_ID, '_regular_price', '' );
							update_post_meta( $post_ID, '_sale_price', '' );
							update_post_meta( $post_ID, '_sale_price_dates_from', '' );
							update_post_meta( $post_ID, '_sale_price_dates_to', '' );
							update_post_meta( $post_ID, '_price', '' );
							delete_post_meta( $post_ID, '_woo_ppv_id');

							//Sets the attributes up to be used as variations but doesnt actually set them up as variations
							wp_set_object_terms ($post_ID, 'variable', 'product_type');

							$attribute_options = "";
							foreach($prices as $option_id => $option_values)
							{
								$attribute_options.=$option_values['name']."|";
							}
	
							$thedata = array(
							   'access-period'=> array(
						                'name'=>'Access Period',
						                'value'=>$attribute_options,
						                'is_visible' => '1',
						                'is_variation' => '1',
						                'is_taxonomy' => '0'
					                   )
							);
							update_post_meta( $post_ID,'_product_attributes',$thedata);

                                                        $variations = get_posts( array(
                                                                'post_parent'    => $post_ID,
                                                                'post_type'    => 'product_variation',
                                                        ));

							if( $variations ) {
								foreach($variations as $variation_post)
									wp_delete_post( $variation_post->ID);
							}

							foreach($prices as $option_id => $option_values)
                                                        {
								$new_variation = array(
									'post_title'   => 'Variation # '.$option_id.' of Access Period',
									'post_name'   => 'product-'.$post_ID.'-'.$option_id.'-variation',
									'post_status'  => 'publish',
									'post_parent'  => $post_ID,
									'post_type'    => 'product_variation',
								);
								$variation_id = wp_insert_post( $new_variation );
								do_action( 'woocommerce_create_product_variation', $variation_id );

								update_post_meta( $variation_id, '_virtual', 'yes' );
								update_post_meta( $variation_id, '_regular_price', $option_values['amount'] );
								update_post_meta( $variation_id, '_price', $option_values['amount'] );
								update_post_meta( $variation_id, 'attribute_access-period', sanitize_title_with_dashes($option_values['name']) );
								update_post_meta( $variation_id, '_woo_ppv_id',$option_id);				
								update_post_meta( $variation_id, 'is_streaming_video',true);
							}		
						}
						update_post_meta($post_ID, 'is_streaming_video',true);
							
						$index+=1;
					}
                                }
                                $prices = array();
                                $option_index = 1;
                                $purchase_option_count = (int)$xml->result->group_option_count;
                                $option_name = "";
                                if($purchase_option_count > 0)
                                {
                                        $args=array(
                                                'meta_key'=>'_hss_woo_group_id',
                                                'meta_value'=> $group_id,
                                                'post_type' => 'product',
                                        );
                                        _log($args);
                                        $my_query = null;
                                        $my_query = new WP_Query($args);
                                        $post_ID = -1;
                                        if( $my_query->have_posts() ) {
                                                _log("Video group already a post");
                                                $video_group_post = $my_query->next_post();
                                                _log("video_group_post ID=".$video_group_post->ID);
                                                if($options['disable_desc_updates']==1){
                                                        $my_post = array(
                                                           'ID' => $video_group_post->ID,
                                                           'post_title' => $video_group_post->post_title,
                                                        );
                                                }else{
                                                        $my_post = array(
                                                           'ID' => $video_group_post->ID,
                                                           'post_title' => $group_title,
                                                           'post_content' => $group_description,
                                                        );
                                                }
                                                // Update the post into the database
                                                $post_ID = wp_update_post( $my_post );
                                                _log("RESULT FROM UPDATE: ".$post_ID);
                                        }else{
                                                // Create post object
                                                _log("Create video group post");
                                                $my_post = array(
                                                   'post_title' => $group_title,
                                                   'post_content' => $group_description,
                                                   'post_status' => 'publish',
                                                   'post_author' => 1,
                                                   'post_type' => 'product',

                                                );

                                                // Insert the post into the database
                                                $post_ID = wp_insert_post( $my_post );
                                                $url = $group_thumbnail;
						if($url!=""){
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
                                        }

                                        update_post_meta($post_ID, '_hss_woo_group_id', $group_id);

					$vid_cats = array();
                                        array_push($vid_cats,$group_title);
                                        _log($vid_cats);
                                        wp_set_object_terms($post_ID,$vid_cats,'product_cat');
                                                
                                        $purchase_option_details = array();
                                        while($option_index <= $purchase_option_count)
                                        {
                                                $option_id = (int)$xml->result[0]->{'group_option'.$option_index}[0]->option_id;
                                                $option_type = (string)$xml->result[0]->{'group_option'.$option_index}[0]->type;
                                                $option_price = (string)$xml->result[0]->{'group_option'.$option_index}[0]->price;
                                                if( ( ( (float)$option_price) < $lowest_price) or ($lowest_price==0))
                                                       $lowest_price = (float)$option_price;
                                                $bandwidth_cap = (string)$xml->result[0]->{'group_option'.$option_index}[0]->bandwidth_cap;
                                                $time_limit = (string)$xml->result[0]->{'group_option'.$option_index}[0]->time_limit;
                                                $rate_limit = (string)$xml->result[0]->{'group_option'.$option_index}[0]->rate_limit;
                                                //$download_limit = (string)$group_xml->result[0]->{'group_option'.$option_index}[0]->download_limit;
                                                $option_name = $time_limit.' access';
                                                /*if($bandwidth_cap!="Unlimited")
                                                        $option_name = $option_name.' '.$bandwidth_cap.' Data Cap';
                                                if($rate_limit!="No limit")
                                                         $option_name = $option_name.' rate limited to '.$rate_limit.' kbps';
                                                if($download_limit=="No Downloads")
                                                         $option_name = $option_name.' (no download access)';
                                                elseif($download_limit=="Any Bitrate Available")
                                                         $option_name = $option_name.' (includes download access)';
                                                else
                                                         $option_name = $option_name.' (download accesss '.$download_limit.')';*/
                                                $prices[$option_id] = array('name' => $option_name,'amount' => $option_price);
                                                _log("group option id=".$option_id);
                                                _log($prices[$option_id]["name"]);
                                                $option_index+=1;
                                        }

                                        update_post_meta($post_ID, '_visibility','visible');
                                        update_post_meta($post_ID, '_downloadable','yes');
                                        update_post_meta($post_ID, '_virtual','yes');

                                        _log("PostID=".$post_ID);
                                        	if($option_index==1){
                                                       _log("1");
                                                        $variations = get_posts( array(
                                                                'post_parent'    => $post_ID,
                                                                'post_type'    => 'product_variation',
                                                        ));

                                                        if( $variations ) {
                                                                foreach($variations as $variation_post)
                                                                        wp_delete_post( $variation_post->ID);
                                                        }
                                                        delete_post_meta($post_ID, '_product_attributes');
                                                        delete_post_meta($post_ID, '_regular_price');
                                                        delete_post_meta($post_ID, '_price');
                                                        delete_post_meta($post_ID, '_woo_ppv_id');
                                                        wp_set_object_terms ($post_ID, 'standard', 'product_type');
                                                }elseif($option_index==2){
                                                        _log("2 setting _woo_ppv_id to ".$option_id);
                                                        $variations = get_posts( array(
                                                                'post_parent'    => $post_ID,
                                                                'post_type'    => 'product_variation',
                                                        ));

                                                        if( $variations ) {
                                                                foreach($variations as $variation_post)
                                                                        wp_delete_post( $variation_post->ID);
                                                        }
                                                        delete_post_meta($post_ID, '_product_attributes');
                                                        update_post_meta($post_ID, '_regular_price',$option_price);
                                                        update_post_meta($post_ID, '_price',$option_price);
                                                        update_post_meta($post_ID, '_woo_ppv_id',$option_id);
                                                        wp_set_object_terms ($post_ID, 'standard', 'product_type');
                                                }else{
                                                        _log("3 post id=".$post_ID." setting _woo_ppv_id to ".$option_id);
                                                        update_post_meta( $post_ID, '_regular_price', '' );
                                                        update_post_meta( $post_ID, '_sale_price', '' );
                                                        update_post_meta( $post_ID, '_sale_price_dates_from', '' );
                                                        update_post_meta( $post_ID, '_sale_price_dates_to', '' );
                                                        update_post_meta( $post_ID, '_price', '' );
                                                        delete_post_meta( $post_ID, '_woo_ppv_id');

                                                        //Sets the attributes up to be used as variations but doesnt actually set them up as variations
                                                        wp_set_object_terms ($post_ID, 'variable', 'product_type');

                                                        $attribute_options = "";
                                                        foreach($prices as $option_id => $option_values)
                                                        {
                                                                $attribute_options.=$option_values['name']."|";
                                                        }

                                                        $thedata = array(
                                                           'access-period'=> array(
                                                                'name'=>'Access Period',
                                                                'value'=>$attribute_options,
                                                                'is_visible' => '1',
                                                                'is_variation' => '1',
                                                                'is_taxonomy' => '0'
                                                           )
                                                        );
                                                        update_post_meta( $post_ID,'_product_attributes',$thedata);

                                                        $variations = get_posts( array(
                                                                'post_parent'    => $post_ID,
                                                                'post_type'    => 'product_variation',
                                                        ));

                                                        if( $variations ) {
                                                                foreach($variations as $variation_post)
                                                                        wp_delete_post( $variation_post->ID);
                                                        }

                                                        foreach($prices as $option_id => $option_values)
                                                        {
                                                                $new_variation = array(
                                                                        'post_title'   => 'Variation # '.$option_id.' of Access Period',
                                                                        'post_name'   => 'product-'.$post_ID.'-'.$option_id.'-variation',
                                                                        'post_status'  => 'publish',
                                                                        'post_parent'  => $post_ID,
                                                                        'post_type'    => 'product_variation',
                                                                );
                                                                $variation_id = wp_insert_post( $new_variation );
                                                                do_action( 'woocommerce_create_product_variation', $variation_id );

                                                                update_post_meta( $variation_id, '_virtual', 'yes' );
                                                                update_post_meta( $variation_id, '_regular_price', $option_values['amount'] );
                                                                update_post_meta( $variation_id, '_price', $option_values['amount'] );
                                                                update_post_meta( $variation_id, 'attribute_access-period', sanitize_title_with_dashes($option_values['name']) );
                                                                update_post_meta( $variation_id, '_woo_ppv_id',$option_id);
                                                                update_post_meta( $variation_id, 'is_streaming_video',true);
                                                        }
						}
                                        update_post_meta($post_ID, 'is_streaming_video_bundle',true);
                                        update_post_meta($post_ID, '_hss_woo_bundled_products', $group_video_post_ids);
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
                   'private_user_id' => $userId,
                   'database_id' => $options['database_id']
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

/*  
* Create a random string  
*@param $length the length of the string to create  
* @return $str the string  
*/ 
function randomString($length = 12) {  
	$str = "";  
	$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));  
	$max = count($characters) - 1;  
	for ($i = 0; $i < $length; $i++) {   
		$rand = mt_rand(0, $max);   
		$str .= $characters[$rand];  
	}  
	return $str; 
}
?>

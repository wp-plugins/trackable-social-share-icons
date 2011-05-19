<?php
	/*
	Plugin Name: Trackable Social Share Icons
	Plugin URI: http://www.ecreativeim.com/trackable-social-share-icons
	Description: The Trackable Social Share Icons plugin enables blog readers to easily share posts via social media networks, including Facebook and Twitter. All share clicks are automatically tracked in your Google Analytics.
	Version: 0.9
	Author: Name: Ecreative Internet Marketing
	Author URI: http://www.ecreativeim.com/
	License: MIT
	*/

	// Make Sure it is a WordPress Blog
    if(!defined('WP_PLUGIN_DIR')) { die('This WordPress plugin is not supported with your system.'); }
	
	// Define Version
	define('TRACKABLESHARE_VERSION','0.9');
	define('TRACKABLESHARE_DIRNAME',basename(dirname(__FILE__)));
	define('TRACKABLESHARE_PLUGINS',basename(WP_CONTENT_DIR).'/'.basename(WP_PLUGIN_DIR));
	
	// Activate Hooks
	register_activation_hook( __FILE__, '_trackableshare_activate' );
	register_sidebar_widget(__('Trackable Social Share Icons'), '_trackableshare_embed');
	add_action('admin_menu', '_trackableshare_menu');
	
	add_filter('the_content', '_trackableshare_tag');
	add_filter('the_content', '_trackableshare_process');
	if(get_option('_trackablesharebutton_excerpt') == '1') {
		add_filter('the_excerpt', '_trackableshare_process_excerpt');
	}
	
	add_filter('wp_head', '_trackableshare_header');
	add_filter('wp_footer', '_trackableshare_footer');
	
	
	// Activation Function
	function _trackableshare_activate() {
		add_option('_trackablesharebuttons', 'facebook,twitter,email');
		add_option('_trackablesharebutton_additionalbuttons', '');
		add_option('_trackablesharebutton_type', '1');
		add_option('_trackablesharebutton_text', '0');
		add_option('_trackablesharebutton_size', '100%');
		add_option('_trackablesharebutton_google', '1');
		add_option('_trackablesharebutton_excerpt', '0');
		add_option('_trackablesharebutton_page', '1');
		add_option('_trackablesharebutton_post', '1');
		add_option('_trackablesharebutton_position', 'bottom');
		//add_option('_trackablesharebutton_bitlylogin', '');
		//add_option('_trackablesharebutton_bitlykey', '');
		add_option('_trackablesharebutton_header', '');
		add_option('_trackablesharebutton_footer', '0');
		// Code Check Cache
		add_option('_trackablesharebuttons_code_check',false);
		add_option('_trackablesharebuttons_code_check_time','0');
	}


	// Menu Function
	function _trackableshare_menu() {
		if(function_exists('add_submenu_page')) {
			add_submenu_page('plugins.php','Trackable Sharing', 'Trackable Sharing', 1, 'trackable_sharing', '_trackableshare_admin');
		}
	}


	// Return Current URL
	function _trackableshare_buildurl() {
		return 'http'.($_SERVER['HTTPS']?'s':'').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	
	// Return Button Paths
	function _trackableshare_setpaths($file,$admin=false) {
		if($file == 'custom') {
			$path = WP_CONTENT_DIR.'/uploads/trackableshare/';
			$url = ($_SERVER['HTTPS']?str_replace('http://','https://',WP_CONTENT_URL):WP_CONTENT_URL).'/uploads/trackableshare/';
		} else {
			$path = ($admin?'../':'').TRACKABLESHARE_PLUGINS.'/'.TRACKABLESHARE_DIRNAME.'/buttons/'.$file.'/';
			$url = ($_SERVER['HTTPS']?str_replace('http://','https://',WP_PLUGIN_URL):WP_PLUGIN_URL).'/'.TRACKABLESHARE_DIRNAME.'/buttons/'.$file.'/';
		}
		return array($path,$url);
	}

	
	// Add direct function embed
	function _trackableshare_embed($params = '') {
		global $post;
		
		$suggested_url = get_permalink( $post->ID );
		$page_url = _trackableshare_buildurl();
		
		if($page_url == $suggested_url) {
			$page_title = the_title_attribute(array('echo'=>0));
		} else {
			$page_title = get_bloginfo('name');
		}
		
		if(isset($params['echo']) && $params['echo'] == 0) {
			return _trackableshare_process('',true,$page_url,$page_title);
		} else {
			echo _trackableshare_process('',true,$page_url,$page_title);
		}
	}


	// Add direct tag embed
	function _trackableshare_tag($content) {
		return str_replace('[trackable_share]',_trackableshare_process('',true),$content);
	}
	
	
	// Process Excerpts if enabled
	function _trackableshare_process_excerpt($content) {
		return _trackableshare_process($content,true);
	}


	// Build Buttons
	function _trackableshare_process($content, $excerpt = false, $page_url = false, $page_title = false) {
		global $post;
		//echo $post->ID.'<hr />';
		//echo $post->post_type;
		
		if(get_option('_trackablesharebutton_page') !== false && !$excerpt) {
				
			if($post->post_type == 'page') {
				if(get_option('_trackablesharebutton_page') == '0') {
					return $content;
				}
			} else {
				if(get_option('_trackablesharebutton_post') == '0') {
					return $content;
				}
			}
		}
		
		
		$urls = array(
		'bookmark.png' => array('url'=>'javascript:alert(\'test\',\'test\')','popup'=>'false'), // not enabled yet
		'digg.png' => array('url'=>'http://digg.com/submit?partner=addthis&url=%url%&title=%title%&bodytext=', 'popup'=>'750x450'),
		'delicious.png' => array('url'=>'http://www.delicious.com/save?v=5&noui&jump=close&url=%url%&title=%title%', 'popup'=>'550x350'),
		'email.png' => array('url'=>'mailto:?subject=Check out %url%', 'popup'=>'false'),
		'facebook.png' => array('url'=>'http://www.facebook.com/sharer.php?u=%url%', 'popup'=>'500x350'),
		'linkedin.png' => array('url'=>'http://www.linkedin.com/shareArticle?mini=true&url=%url%&title=%url%&ro=false&summary=&source=', 'popup'=>'500x350'),
		'posterous.png' => array('url'=>'http://posterous.com/share?linkto=%url%', 'popup'=>'900x600'),
		'print.png' => array('url'=>'javascript:window.print();', 'popup'=>'false'),
		'reddit.png' => array('url'=>'http://www.reddit.com/login?dest=%2Fsubmit%3Furl=%url%&title=%url%', 'popup'=>'700x500'),
		'snailmail.png' => array('url'=>'javascript:alert(\'== Mail this to a Friend ==\r\n\r\nInstructions:\r\n1. Print this page\r\n2. Put in envelope\r\n3. Address envelope \r\n4. Affix appropriate postage\r\n5. Mail\r\n\r\nThank you for sharing!!!\')','popup'=>'false'), // not enabled yet
		'stumbleupon.png' => array('url'=>'http://www.stumbleupon.com/submit?url=%url%&title=%url%', 'popup'=>'750x450'),
		'tumblr.png' => array('url'=>'http://www.tumblr.com/login?s=&t=%title%&u=%url%&v=3&o=0', 'popup'=>'500x400'),
		'twitter.png' => array('url'=>'http://twitter.com/share?url=%url%&text=%title%', 'popup'=>'500x350')
		);
		
		$add_buttons = get_option('_trackablesharebutton_additionalbuttons');
		$add_rows = explode("\n",$add_buttons);
		if(is_array($add_rows)) {
			foreach($add_rows as $row) {
				$columns = explode('|',$row);
				if(is_array($columns)) {
					$urls[strtolower(preg_replace('/[^a-z0-9\.]/i','',$columns[0]))] = array('url'=>trim($columns[1]),'popup'=>trim($columns[2]));
				}
			}
		}
		
		$return = '<div class="trackable_sharing">';
		
		$buttons = explode(',',get_option('_trackablesharebuttons'));
		list($button_dir,$button_url) = _trackableshare_setpaths( get_option('_trackablesharebutton_type'));
		$size = get_option('_trackablesharebutton_size');
		//$bitly_login = get_option('_trackablesharebutton_bitlylogin');
		//$bitly_key = get_option('_trackablesharebutton_bitlykey');
		$google = get_option('_trackablesharebutton_google');
		$text = get_option('_trackablesharebutton_text');
		
		if(!$page_url) {
			$page_url = get_permalink( $post->ID );
		}
		
		if(!$page_title) {
			$page_title = the_title_attribute(array('echo'=>0));
		}
		
		foreach($buttons as $button) {
			
			$button = strtolower(preg_replace('/[^a-z]/i','',$button)).'.png';
			//if(get_option('_trackablesharebutton_bitly_'.preg_replace('/[^a-z0-9]+/i','',$page_url))) {
			//	$bitlyurl = get_option('_trackablesharebutton_bitly_'.preg_replace('/[^a-z0-9]+/i','',$page_url));
			//} elseif(!empty($bitly_login) && !empty($bitly_key)) {
			//	$bitlyurl = @file_get_contents('http://api.bitly.com/v3/shorten?login='.$bitly_login.'&apiKey='.$bitly_key.'&longUrl='.urlencode($page_url).'&format=txt');
			//	add_option('_trackablesharebutton_bitly_'.preg_replace('/[^a-z0-9]+/i','',$page_url),$bitlyurl);
			//} else {
				$bitlyurl = $page_url;
			//}
			$url = str_replace(array('%bitlyurl%','%url%','%title%'),array(urlencode($bitlyurl),urlencode($page_url),urlencode($page_title)),$urls[$button]['url']);
			
			//if($urls[$button]['popup'] != 'false') {
				//$content .= '<a href="'.$page_url.'" style="text-decoration: none; white-space: nowrap;" title="'.ucfirst(substr($button,0,-4)).'"';
			//} else {
				$return .= '<a href="'.$url.'" style="text-decoration: none; white-space: nowrap;" title="'.ucfirst(substr($button,0,-4)).'"';
			//}
			
			if($google == 1 || $urls[$button]['popup'] != 'false') {
				if($urls[$button]['popup'] != 'false') {
					$return .= ' target="_blank"';
				}
				
				$return .= ' onclick="';

				if($google == '1') {
					$return .= 'that=this;_gaq.push([\'_trackEvent\',\'SocialSharing\',\''.ucfirst(substr($button,0,-4)).'\',\''.$page_url.'\']); ';
				}
				
				if($urls[$button]['popup'] != 'false') {
					$jsize = explode('x',$urls[$button]['popup']);
					$return .= 'window.open(this.href,\'share\',\'menubar=0,resizable=1,width='.$jsize[0].',height='.$jsize[1].'\'); return false;';
				}

				$return .= '"';
			}

			$return .= '><img align="absmiddle" src="'.$button_url.'/'.$button.'" alt="'.ucfirst(substr($button,0,-4)).'"';
			
			if(preg_match('/[0-9]+x[0-9]+/i',$size)) {
				list($width,$height) = explode('x',strtolower($size));
				$return .= ' width="'.$width.'" height="'.$height.'"';
			} elseif(preg_match('/[0-9]+\%/',$size)) {
					list($width,$height) = @getimagesize($button_dir.'/'.$button);
					if($height > 36) {
						$width = $width * 36 / $height; $height = 36;
					}
					$per = preg_replace('/[^0-9]/','',$size) / 100;
					$height = $height * $per;
					$width = $width * $per;
					$return .= ' width="'.$width.'" height="'.$height.'"';
			} else {
				list($width,$height) = @getimagesize($button_dir.'/'.$button);
				if($height > 36) { $content .= ' height="36"'; }
			}
			
			$return .= '>'.($text == '1'?' '.strtolower(substr($button,0,-4)).' &nbsp; ':'').'</a> ';
		}
		
		$return .= '</div>';
		
		if(get_option('_trackablesharebutton_position') == 'both') {
			return $return.$content.$return;
		} elseif(get_option('_trackablesharebutton_position') == 'top') {
			return $return.$content;
		} else {
			return $content.$return;
		}

	}

		
	// Display Buttons
	function _trackableshare_showbuttons($file) {
		list($path,$url) = _trackableshare_setpaths($file,true);
		
		foreach(explode(',',get_option('_trackablesharebuttons')) as $button_prev) {
			$button_prev = strtolower(preg_replace('/[^a-z]/i','',$button_prev)).'.png';
			
			if(preg_match('/[0-9]+x[0-9]+/i',get_option('_trackablesharebutton_size'))) {
				list($width,$height) = explode('x',strtolower(get_option('_trackablesharebutton_size')));
				$height = ' width="'.$width.'" height="'.$height.'"';
			} elseif(preg_match('/[0-9]+\%/',get_option('_trackablesharebutton_size'))) {
				list($width,$height) = @getimagesize($path.$button_prev);
				if($height > 36) {
					$width = $width * 36 / $height; $height = 36;
				}
				$per = preg_replace('/[^0-9]/','',get_option('_trackablesharebutton_size')) / 100;
				$height = $height * $per;
				$width = $width * $per;
				$height = ' width="'.$width.'" height="'.$height.'"';
			} else {
				list($width,$height) = @getimagesize($path.$button_prev);
				if($height > 36) { $height = ' height="36"'; } else { $height = ''; }
			}
			
			echo '<img src="'.$url.$button_prev.'"'.$height.' align="absmiddle" /> ';
			
		}
	}


	// Admin Panel
	function _trackableshare_admin() {
		// Database Update
		if(isset($_POST['buttons'])) {
			update_option('_trackablesharebuttons', $_POST['buttons']);
			update_option('_trackablesharebutton_additionalbuttons', $_POST['additional']);
			update_option('_trackablesharebutton_type', $_POST['type']);
			update_option('_trackablesharebutton_text', $_POST['text']);
			update_option('_trackablesharebutton_size', $_POST['size']);
			update_option('_trackablesharebutton_google', $_POST['google']);
			update_option('_trackablesharebutton_excerpt', (isset($_POST['excerpt'])?'1':'0'));
			update_option('_trackablesharebutton_post', (isset($_POST['post'])?'1':'0'));
			update_option('_trackablesharebutton_page', (isset($_POST['page'])?'1':'0'));
			update_option('_trackablesharebutton_position', $_POST['position']);
			//update_option('_trackablesharebutton_bitlylogin', $_POST['bitlylogin']);
			//update_option('_trackablesharebutton_bitlykey', $_POST['bitlykey']);
			update_option('_trackablesharebutton_header', $_POST['css']);
			update_option('_trackablesharebutton_footer', $_POST['footer']);
		}
		
		list($uploadpath,) = _trackableshare_setpaths('custom',true);
		foreach($_FILES as $file) {
			if(!empty($file['name']) && substr($file['name'],-3) == 'png') {
				$file['name'] = strtolower(preg_replace('/[^a-z0-9\.]/i','',$file['name']));
				move_uploaded_file($file['tmp_name'],$uploadpath.$file['name']) or die('error');
			}
		}
		
		if(get_option('_trackablesharebuttons_code_check_time')+60 < time()) {
			$analytic_error = false;
			$analytics = @file_get_contents(get_bloginfo('url'));
			if($analytics != false) {
				if(!preg_match('/_gaq\.push\(\[\'_setAccount\',(\s)*\'UA\-[0-9]+\-[0-9]+\'\]\);.+<\/head>/ism',$analytics)) {
					$analytics_error = true;
				}
			}
			update_option('_trackablesharebuttons_code_check',$analytics_error);
			update_option('_trackablesharebuttons_code_check_time',time());
		} else {
			$analytic_error = get_option('_trackablesharebuttons_code_check');
		}
		
		// Setup Customizeable Icons (one time only)
		if(is_dir(WP_CONTENT_DIR.'/uploads') && is_writable(WP_CONTENT_DIR.'/uploads') && !is_dir(WP_CONTENT_DIR.'/uploads/trackableshare')) {
			mkdir(WP_CONTENT_DIR.'/uploads/trackableshare');
			chmod(WP_CONTENT_DIR.'/uploads/trackableshare',0777);
			$default_folder = '../'.TRACKABLESHARE_PLUGINS.'/'.TRACKABLESHARE_DIRNAME.'/buttons/1';
			$copyall = scandir($default_folder);
			foreach($copyall as $copy) {
				if(is_file($default_folder.'/'.$copy) && substr($copy,-3) == 'png') {
					 copy ($default_folder.'/'.$copy, WP_CONTENT_DIR.'/uploads/trackableshare/'.$copy);
				}
			}
		}
		
		$add_buttons = get_option('_trackablesharebutton_additionalbuttons');
		$add_buttons_text = '';
		$add_rows = explode("\n",$add_buttons);
		if(is_array($add_rows)) {
			foreach($add_rows as $row) {
				$columns = explode('|',$row);
				if(is_array($columns)) {
					$add_buttons_text .= ', '.substr(strtolower(preg_replace('/[^a-z0-9\.]/i','',$columns[0])),0,-4);
				}
			}
		}
		echo '<style type="text/css">tt { color: #D54E21; font-weight: bold; }</style>';
		echo '<div style="background: url('.WP_PLUGIN_URL.'/'.TRACKABLESHARE_DIRNAME.'/images/left.jpg) repeat-y #fff;">';
		echo '<div style="margin-bottom: 10px; width: 100%; height: 115px; background: url('.WP_PLUGIN_URL.'/'.TRACKABLESHARE_DIRNAME.'/images/bg.jpg);">';
		echo '<a href="http://www.ecreativeim.com/blog" target="_blank"><img src="'.WP_PLUGIN_URL.'/'.TRACKABLESHARE_DIRNAME.'/images/logo.jpg" /></a>';
		echo '<h2 style="float: right; font-style: italic; color: #56959E; margin: 60px 5% 0 0;">Trackable <span style="color: #2E5282;">Sharing</span></h2>';
		echo '<div style="clear: right; float: right; margin-right: 10%; color: #333; font-size: 12px;">version '.TRACKABLESHARE_VERSION.'</div>';
		echo '</div>';
		echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="padding: 0 40px;" enctype="multipart/form-data">';
		
		echo '<table width="100%">';
		echo '<tr><td valign="top" colspan="2" width="150"><h3>Buttons:</h3><textarea style="width: 60%;" name="buttons">'.get_option('_trackablesharebuttons').'</textarea><br />(separate multiple buttons with a comma, ie: "facebook, twitter, stumble upon, digg, email")';
		
		echo '<br /><strong style="color: #56959E">Options include: facebook, twitter, linked in, digg, delicious, reddit, stumble upon, tumblr, posterous, email, snail mail'.$add_buttons_text.'</strong></td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		
		echo '<td colspan="2"><h3>Display Buttons On:';
		echo '</h3>
		<input type="radio" name="position" value="both" '.('both' == get_option('_trackablesharebutton_position')?'checked':'').' /> Top and Bottom of  &nbsp; <input type="radio" name="position" value="top" '.('top' == get_option('_trackablesharebutton_position')?'checked':'').' /> Top of  &nbsp; <input type="radio" name="position" value="bottom" '.('top' != get_option('_trackablesharebutton_position') && 'both' != get_option('_trackablesharebutton_position')?'checked':'').' /> Bottom of<br />
		<input type="checkbox" name="post" value="1" '.(1 == get_option('_trackablesharebutton_post')?'checked':'').' /> Posts &nbsp; <input type="checkbox" name="page" value="1" '.(1 == get_option('_trackablesharebutton_page')?'checked':'').' /> Pages &nbsp; <input type="checkbox" name="excerpt" value="1" '.(1 == get_option('_trackablesharebutton_excerpt')?'checked':'').' /> Excerpts</td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		
		echo '<tr><td valign="top" colspan="2"><h3>Button Size:</h3><input style="width: 60%;" type="text" name="size" value="'.get_option('_trackablesharebutton_size').'" /><br />Enter a percentage (ie 75%), a pixel width x height (ie 24x24), or leave blank for default size</td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		echo '<tr><td valign="top" colspan="2"><h3>Button Style:</h3>';
		echo '<em>Note: not all button styles have all share buttons</em><br /><br /><div style="width: 100%; overflow: auto;">';
		$files = array();
		$files = scandir('../'.TRACKABLESHARE_PLUGINS.'/'.TRACKABLESHARE_DIRNAME.'/buttons/');
		natsort($files);
		foreach($files as $file) {
			if(substr($file,0,1) != '.' && substr($file,0,1) != '_' && is_dir('../'.TRACKABLESHARE_PLUGINS.'/'.TRACKABLESHARE_DIRNAME.'/buttons/'.$file)) {
				echo '<input type="radio" name="type" value="'.$file.'" '.($file == get_option('_trackablesharebutton_type')?'checked':'').' /> &nbsp; ';
				_trackableshare_showbuttons($file);
				echo '<br /><br /><br />';
			}
		}
		echo '</div>';
		if(is_dir(WP_CONTENT_DIR.'/uploads/trackableshare')) {
			echo '<strong>&nbsp; &nbsp; - Or Use Custom Buttons - </strong><br /><br />';
			echo '<input type="radio" name="type" value="custom" '.('custom' == get_option('_trackablesharebutton_type')?'checked':'').' /> &nbsp; ';
			_trackableshare_showbuttons('custom');
			echo '<br /><br /><strong>Upload Buttons:</strong> button should be named identical to the correlating tag and must be a .png<br />';
			echo '<span style="color: #56959E">ie: facebook.png, twitter.png, linkedin.png, digg.png, stumbleupon.png, etc.</span><br /><br />';
			echo '<input type="file" name="upload1"> <input type="file" name="upload2"> <input type="file" name="upload3">';
			echo '<br /><br /><br />';
			
		}

		echo '<h4>Add text after button?</h4>Do you want your icons to look like this: &nbsp;';
		echo '<a href="javascript:void(0);" style="text-decoration: none;"><img src="../'.TRACKABLESHARE_PLUGINS.'/'.TRACKABLESHARE_DIRNAME.'/buttons/1/facebook.png" height="20" align="absmiddle" /> facebook</a><br />';
		echo '<input type="radio" name="text" value="1" '.(1 == get_option('_trackablesharebutton_text')?'checked':'').' /> Yes &nbsp; <input type="radio" name="text" value="0" '.(0 == get_option('_trackablesharebutton_text')?'checked':'').' /> No';
		echo '<br /><br />';
		echo '</td></tr>';
		echo '<td colspan="2"><h3>Google Analytics';
		if($analytics_error) {
			echo '<br /><span style="font-size: 12px; color: #900;">Unable to detect Google Analytics code.  Please make sure latest Analytics code has been installed or set to &quot;no&quot; to prevent javascript errors.</span>';
		}
		echo '</h3>
		Do you want to track Social Media clicks with Google Analytics - requires latest, asynchronous code<br />
		<input type="radio" name="google" value="1" '.(1 == get_option('_trackablesharebutton_google')?'checked':'').' /> Yes &nbsp; <input type="radio" name="google" value="0" '.(0 == get_option('_trackablesharebutton_google')?'checked':'').' /> No</td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		/*
		echo '<tr><td colspan="2"><h3>bitly API (bit.ly)</h3>If you would like to use your own bit.ly account for tracking purposes<br /><br />';
		echo 'bitly login: <input type="text" name="bitlylogin" size="30" value="'.get_option('_trackablesharebutton_bitlylogin').'" /> &nbsp; &nbsp; bitly API key: <input type="text" size="55" name="bitlykey" value="'.get_option('_trackablesharebutton_bitlykey').'" />';
		echo '</td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		*/
		
		echo '<td colspan="2"><h3>Show Your Support</h3>Would you like to add a link to "Trackable Sharing" in your footer?<br /><input type="radio" name="footer" value="1" '.(1 == get_option('_trackablesharebutton_footer')?'checked':'').' /> Yes &nbsp; <input type="radio" name="footer" value="0" '.(0 == get_option('_trackablesharebutton_footer')?'checked':'').' /> No<br /><br />';
		
		echo '<h3>Advanced (<a href="javascript:void(0);" onclick="if(document.getElementById(\'trackable_advanced\').style.display == \'none\') { document.getElementById(\'trackable_advanced\').style.display = \'block\'; } else { document.getElementById(\'trackable_advanced\').style.display = \'none\'; }">Show/Hide</a>)</h3>';
		echo '<div style="background: #ffc; border: 1px solid #ccc; padding: 10px; display: none;" id="trackable_advanced">';
		echo '<strong>Button CSS:</strong><br /><textarea style="width: 60%;" name="css">'.get_option('_trackablesharebutton_header').'</textarea><br /><tt style="color: #666;">.trackable_sharing { margin-bottom: 10px; } .trackable_sharing a { border: 1px solid #333; }</tt><br /><br />';
		echo '<br /><strong>Additional Buttons:</strong> (requires use of custom button images)<br /><textarea style="width: 60%;" name="additional">'.$add_buttons.'</textarea><br /><tt>Button Name | Url (use %url% and %title% for post attributes) | Pop-up Size (W x H in pixels or false)</tt><tt style="color: #666;"><br />buttonname.png|http://samplesite.com?url=%url%&title=%title|400x250<br />buttonname2.png|javascript:alert(\'hello world\');|false</tt><br /><em>note: buttons cannot share images</em><br /><br />';
		echo '<br /><strong>Trackable_Share Tag:</strong><br />You can add icons to individual posts by adding the <tt>[trackable_share]</tt> tag where you would like the buttons to appear.  Buttons will follow the settings set in the admin.  Note that this functions separately from the &quot;Display Buttons On&quot; settings above.<br /><br />';
		echo '<br /><strong>Trackable Share Template Function:</strong><br />You can add icons to your template by using the <tt>_trackableshare_embed()</tt> function.  This will echo the buttons out onto the page, or if you prefer you may return the buttons for further manipulation using the <tt>_trackableshare_embed(array(\'echo\'=>0))</tt> function and parameters.  Note that this functions separately from the &quot;Display Buttons On&quot; settings above.';
		echo '</div>';
		echo '<br /><br />';
		echo 'Please remember <a href="http://www.ecreativeim.com" target="_blank">Ecreative Internet Marketing</a> for your web design and SEO needs.</td></tr>';
		echo '</table>';
		echo '<br /><br /><br /><input type="reset" value="CANCEL" style="padding: 10px; font-size: 14px; background: #900; color: #fff;" /> &nbsp; <input type="submit" value="SAVE CHANGES" style="padding: 10px; font-size: 14px; background: green; color: #fff;" /><br /><br /><br /><br /><br />';
		echo '</form>';
		echo '</div>';
	}


	// Header CSS
	function _trackableshare_header() {
		if(strlen(trim(get_option('_trackablesharebutton_header'))) > 0) {
			echo '<style type="text/css">'.get_option('_trackablesharebutton_header').'</style>';
		}
	}
	
	
	// Footer
	function _trackableshare_footer() {
		if(get_option('_trackablesharebutton_footer') == '1') {
			echo '<div id="trackable_credits" style="text-align: center;">Social links powered by <a href="http://www.ecreativeim.com/trackable-social-share-icons" target="_blank">Trackable Sharing</a></div>';
		}
	}

	
	// Developed by Michael Stowe, last updated May 19, 2011
	
?>
<?php 
/* Plugin Name: Semi-Private Comments
Plugin URI: http://ok-cleek.com/blogs/?p=4061
Description: Masks comments so that user X can't see comments he didn't write (or that weren't from an admin).
Author: Cleek
Version: 1.0.1
Author URI: http://ok-cleek.com/blogs
*/ 
?>
<?php 

function plugin_is_spc_post() 
{
    global $post;
    return (bool) get_post_meta($post->ID, '_semi_private_post', true);
}

// ----------------------- basic operation -----------------------

function plugin_spc_get_IP() 
{
	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	} else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if(!empty($_SERVER['REMOTE_ADDR'])) {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip_address = '';
	}
	if(strpos($ip_address, ',') !== false) {
		$ip_address = explode(',', $ip_address);
		$ip_address = $ip_address[0];
	}
	return $ip_address;
}


function plugin_spc_commentFilter($content) 
{ 
    global $comment;
    global $current_user;
	global $user_ID;


    if (plugin_is_spc_post())
    {
        get_currentuserinfo();

        $method = get_option('spc_matching_method');
        $user_matched = 0;

        if ($method=="User ID")
        {
			// v1.0.1 - using global user_ID, since current_user->user_ID sometimes isn't filled (?? apperently)
            // $user_matched = ($comment->user_id == $current_user->user_ID) ? 1 : 0;
			$user_matched = ($comment->user_id == $user_ID) ? 1 : 0;
        }
        else // assume $method=="IP Address")
        {
            $user_matched = ($comment->comment_author_IP == plugin_spc_get_IP()) ? 1 : 0;
        }


        if (current_user_can('edit_users') ||   // user is admin, or 
            $user_matched==1 ||                 // user is original author, or
            $comment->user_id == 1)             // comment author is admin
        {
            return $content;
        }
        else
        {
            $hidden_comment_text = get_option('spc_hidden_comment_text');
            return $hidden_comment_text;
        }
    }
    else
    {
        return $content;
    }
} 

add_filter('comment_text', 'plugin_spc_commentFilter'); 
add_filter('comment_text_rss', 'plugin_spc_commentFilter'); 
add_filter('comment_excerpt', 'plugin_spc_commentFilter'); 


// ----------------------- post page options -----------------------

function spcposts_nonce() {
    // Returns the nonce that we use to defend ourself against 
    //  guessing attacks. 
    $nonce = get_option("spcposts_nonce");

    if (is_null($nonce) || strlen($nonce) == 0) {
        $nonce = crc32( time() . $_SERVER['QUERY_STRING'] 
            . $_SERVER['REMOTE_ADDR'] . $_SERVER['SCRIPT_FILENAME'] 
            );
        update_option("spcposts_nonce", $nonce);
    }

    return $nonce;
}

function plugin_spc_sidebar() {
    $is_spc = get_post_meta($_REQUEST['post'], '_semi_private_post', true);
    $check = $is_spc ? 'checked="checked" ' : '';
    ?>
<div id="semi_private_post_dbx" class="dbx-box postbox">
  <h3>Semi-Private</h3> 
  <div class="inside dbx-content">
    <label for="is_spc_post">
      <input type="checkbox" name="is_spc_post" id="is_spc_post" value="1" 
            <?php echo $check; ?> />
        <?php print __('Post has semi-private comments', 'SemiPrivate Comments'); ?>
    </label>
    <input type="hidden" name="spc_nonce" value="<?php 
        print spcposts_nonce();
    ?>">
  </div>
</div>
    <?php
}

add_action('dbx_post_sidebar', 'plugin_spc_sidebar');	

function plugin_spc_update_post($id) {
    if ( current_user_can('edit_post', $post_id) 
        && isset($_POST["spc_nonce"]) 
        && $_POST['spc_nonce'] == spcposts_nonce()
    ) {
        $setting = (isset($_POST["is_spc_post"]) && $_POST["is_spc_post"] == "1") ? 1 : 0;
        delete_post_meta($id, '_semi_private_post');
        add_post_meta($id, '_semi_private_post', $setting);
    }

    return $post_id;
}

add_action('save_post', 'plugin_spc_update_post');
add_action('edit_post', 'plugin_spc_update_post');
add_action('publish_post', 'plugin_spc_update_post');

// ------------------ options -------------------------

// default options
if (get_option('spc_options_ok') == '' || get_option('spc_options_ok') == 'NO') 
{
    delete_option('spc_hidden_comment_text');
    add_option('spc_hidden_comment_text', '<i>comment hidden</i>', 'Text to show for hidden comments', false);

    delete_option('spc_matching_method');
    add_option('spc_matching_method', 'IP Address', 'Method used to match users', false);
}

add_action('admin_menu', 'spc_plugin_menu');

function spc_plugin_menu() 
{
  add_options_page('Semi-Private Comment Options', 'Semi-Private Comments', 8, __FILE__, 'spc_plugin_options');
}

function spc_plugin_options() 
{
  if (get_option('spc_options_ok') == '')
  {
      add_option('spc_options_ok', 'YES', "SPC Comment Options Flag", false);
  }

  $method = get_option('spc_matching_method');

?>

  <div class="wrap">
  <h2>Semi-Private Comments</h2>

  <form method="post" action="options.php">
      <?php echo wp_nonce_field('update-options'); ?>
      <table class="form-table" width=100%>
          <tr valign="top">
              <th width=30% scope="row">Hidden comment text</th>
              <td><input type="text" name="spc_hidden_comment_text" value="<?php echo get_option('spc_hidden_comment_text');?>" /></td>
          </tr>
          <tr valign="top">
              <th width=30% scope="row">User matching method</th>
              <td>
                  <input type="radio" name="spc_matching_method" value="IP Address" <?php echo ($method=="IP Address") ? ('checked') : (''); ?> >IP Address<br>
                  <input type="radio" name="spc_matching_method" value="User ID" <?php echo ($method=="User ID") ? ('checked') : (''); ?> >User ID (should only be used if your commenters are registered users)<br>
              </td>
          </tr>
      </table>

      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="page_options" value="spc_hidden_comment_text, spc_matching_method" />

      <p class="submit">
      <input type="submit" name="Submit" value="Save Changes" />
      </p>
  </form>
  </div>

  <?php
}
?>
<?php
/*
Plugin Name: Gravity Forms RSS Add-On
Plugin URI: https://katz.co/plugins/gravity-forms-rss/
Description: Display your form entries as an RSS feed.
Author: Katz Web Services, Inc.
Version: 1.1.3
Author URI: http://www.katzwebservices.com
Text Domain: gravity-forms-rss
Domain Path: languages

Copyright 2016 Katz Web Services, Inc.  (email: info@katzwebservices.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

add_action('plugins_loaded', array('KWSGFRSS', 'export'), 99999);
add_action('init', array('KWSGFRSS', 'init'));

class KWSGFRSS {
		private static $path = "gravity-forms-rss/gravity-forms-rss.php";
		private static $name = "Gravity Forms RSS Add-On";

		static function init() {
			global $pagenow;

		    if(is_admin() && $pagenow === 'plugins.php') {
				add_action("admin_notices", array('KWSGFRSS', 'is_gravity_forms_installed'), 10);
			}
			if(self::is_gravity_forms_installed(false, false) !== 1){
				add_action('after_plugin_row_' . self::$path, array('GFHighrise', 'plugin_row') );
	           return;
	        }

	        add_action("admin_footer", array('KWSGFRSS', 'add_form_option_js'), 1000);

	        add_filter( 'gform_form_settings', array('KWSGFRSS', 'filter_form_settings'), 10, 2);
	        add_filter('gform_tooltips', array('KWSGFRSS', 'add_form_option_tooltip'));

			load_plugin_textdomain('gravity-forms-rss', FALSE, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		public static function plugin_row(){
			$message = sprintf(__("%sGravity Forms%s is required. %sPurchase it today!%s"), "<a href='https://katz.si/gravityforms'>", "</a>", "<a href='https://katz.si/gravityforms'>", "</a>");
			self::display_plugin_message($message, true);
	    }

	    private static function display_plugin_message($message, $is_error = false){
	    	$style = '';
	        if($is_error)
	            $style = 'style="background-color: #ffebe8;"';

	        echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update"><div class="update-message" ' . $style . '>' . $message . '</div></td>';
	    }

		public static function export() {
			if(!self::is_rss() || !class_exists('GFCommon')) { return; }
			require_once(GFCommon::get_base_path() . "/entry_detail.php");
			$form_id = (int)$_REQUEST['id'];
			$page_size = 200;
			$offset = 0;
			$entry_count = RGFormsModel::get_lead_count($form_id, "", null, null, null, null);
			$leads = RGFormsModel::get_leads($form_id,"date_created", "DESC", "", $offset, $page_size, null, null, false, null, null);
			$form = RGFormsModel::get_form_meta($form_id);

			if(!isset($form['rssToken']) || @$form['rssToken'] !== @$_REQUEST['token']) {
				wp_die( __( 'You do no have access to this form\'s RSS feed (or it may not have been configured yet).' ) );
			}
#header('Content-Type: text/plain');
ob_start();
#print_r($leads);
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0">
<channel>
  <title>'.apply_filters('gforms_rss_feed_title', 'Entries for Form #'.$form_id, $form).'</title>
  <link>'.esc_attr(apply_filters('gforms_rss_feed_link', admin_url(sprintf('admin.php?page=gf_entries&id=%d', $form_id)), $form)).'</link>
  <description>'.apply_filters('gforms_rss_feed_title', $form['description'], $form).'</description>';

            foreach($leads as $lead){
            	echo'
           <item>
           		<title>'.apply_filters('gforms_rss_entry_title', sprintf('Entry #%d', $lead['id']), $form, $lead).'</title>
           		<guid isPermaLink="false"/>
           		<link>'.esc_attr(apply_filters('gforms_rss_entry_link', admin_url(sprintf('admin.php?page=gf_entries&view=entry&id=%d&lid=%d&filter=&paged=1&pos=0', $form_id, $lead['id'])), $form, $lead)).'</link>
           		<pubDate>'.mysql2date('D, d M Y H:i:s +0000', $lead['date_created'], false).'</pubDate>
           		<description><![CDATA[
           	';
           		ob_start();
           		GFEntryDetail::lead_detail_grid($form, $lead, false);
           		$grid = ob_get_clean();

            	echo apply_filters('gforms_rss_entry_description', $grid, $form, $lead);

            echo '
            	]]></description>
            </item>
            ';
            }
            echo '</channel></rss>';

            $contents = ob_get_clean();

            echo $contents;
            exit();
		}

		public static function is_gravity_forms_installed($asd = '', $echo = true) {
			global $pagenow, $page; $message = '';

			$installed = 0;
			$name = self::$name;
			if(!class_exists('RGForms')) {
				if(file_exists(WP_PLUGIN_DIR.'/gravityforms/gravityforms.php')) {
					$installed = 2;
					$message .= __(sprintf('%sGravity Forms is installed but not active. %sActivate Gravity Forms%s to use the %s plugin.%s', '<p>', '<strong><a href="'.wp_nonce_url(admin_url('plugins.php?action=activate&plugin=gravityforms/gravityforms.php'), 'activate-plugin_gravityforms/gravityforms.php').'">', '</a></strong>', $name,'</p>'), 'gravity-forms-rss');
				} else {
					$installed = 0;
					$message .= <<<EOD
	<p><a href="http://katz.si/gravityforms?con=banner" title="Gravity Forms Contact Form Plugin for WordPress"><img src="http://gravityforms.s3.amazonaws.com/banners/728x90.gif" alt="Gravity Forms Plugin for WordPress" width="728" height="90" style="border:none;" /></a></p>
			<h3><a href="http://katz.si/gravityforms" target="_blank">Gravity Forms</a> is required for the $name</h3>
			<p>You do not have the Gravity Forms plugin installed. <a href="http://katz.si/gravityforms">Get Gravity Forms</a> today.</p>
EOD;
				}

				if(!empty($message) && $echo) {
					echo '<div id="message" class="updated">'.$message.'</div>';
				}
			} else {
				$installed = 1;
			}
			return $installed;
		}

		public static function is_rss(){
			if(!class_exists('RGFormsModel')) { return false; }
	        $url_info = parse_url(RGFormsModel::get_current_page_url());
	        $file_name = basename($url_info["path"]);
	        return $file_name == "preview.php" || rgget("gf_page", $_GET) == "rss";
	    }

		protected static function has_access($required_permission){
	        $has_members_plugin = function_exists('members_get_capabilities');
	        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
	        if($has_access)
	            return $has_members_plugin ? $required_permission : "level_7";
	        else
	            return false;
	    }

	public static function add_form_option_tooltip($tooltips) {
		$tooltips["form_rss"] = "<h6>" . __("Access a RSS Feed of Form Entries", "gravity-forms-rss") . "</h6>" . __("This is the access key to view form entries as an RSS feed. You can use an RSS feed reader to read the latest entries by following the 'Go to RSS Feed' link.", "gravity-forms-rss");
		return $tooltips;
	}

	public static function filter_form_settings($settings, $form) {

		$tr_rss = '
		<tr>
		    <th>
		        ' . __("Form RSS", "gravity-forms-rss") . ' ' . gform_tooltip("form_rss", '', true) . '
		    </th>
		    <td> <small class="alignright"><a id="gform_generate_new_rss_token" href="#">'.__('Generate a New Token', 'gravity-forms-rss').'</a></small><label for="gform_rss_token" id="gform_rss_token_label" class="description" style="clear:left; display:block;">'.__("RSS Feed Token", "gravity-forms-rss").'<input type="text" class="widefat" id="gform_rss_token" /></label>
		    </td>
		</tr>';

		$settings['Form Options']['rss'] = $tr_rss;

		return $settings;
	}

	public static function add_form_option_js() {
		global $plugin_page;

		if($plugin_page !== 'gf_edit_forms' || (@$_GET['view'] !== 'settings') ) { return; }
?>
<script>

	jQuery(document).ready(function($) {

		if(!form.rssToken || form.rssToken.length === 0) {
			var token = '<?php echo sha1(rand(0,100000000).'KWSGFRSS'); ?>';
			rssNotSetup = true;
			show_rss_token_warning('active');
		} else {
			rssNotSetup = false;
			var token = form.rssToken;
		}

		set_gform_rss_token(token);

		$('#gform_generate_new_rss_token').click(function(e) {

			var r = confirm("Reset the RSS token? Previous feed URLs will no longer work.");

			if(r === true) {
				var str1 = Math.random(0, 10000000).toString(32).substr(2);
				var str2 = Math.random(0, 10000000).toString(32).substr(2);
				var str3 = Math.random(0, 10000000).toString(32).substr(2);
				var str4 = Math.random(0, 10000000).toString(32).substr(2);
				var str5 = Math.random(0, 10000000).toString(32).substr(2);
				var str6 = Math.random(0, 10000000).toString(32).substr(2);
				$("#gform_rss_token").val(str1 + str2 + str3 + str4 + str5 + str6).trigger('change');
				show_rss_token_warning('updated');
			}

			return false;
		});

		function show_rss_token_warning(type) {
			$('#gform_rss_warning').remove();
			$('#gform_rss_token').parents('label').before('<div id="gform_rss_warning" class="clear updated inline" style="margin-bottom:0; margin-top:1em;"><p><strong><?php printf(__('You must save the form before the RSS token is %s.', 'gravity-forms-rss'), "'+type+'"); ?></strong></p></div>');
		}

		function set_gform_rss_token(val) {
			if($('#gform_rss_feed_link').length) {
				$('#gform_rss_feed_link').remove();
			}

			if(rssNotSetup === false) {
				var $link = $('<a />')
					.addClass('button button-secondary button-small alignleft')
					.attr('id', 'gform_rss_feed_link')
					.attr('href', '<?php echo site_url('?gf_page=rss&id='.$_GET['id'].'&token='); ?>'+val)
					.text('Go to RSS Feed').attr('target', '_blank');

				$("#gform_rss_token").parents('td').prepend($link);
			}

			$("#gform_rss_token").val(val);

			form.rssToken = val;
		}

		$('body').on('change ready', "#gform_rss_token", function() {

			set_gform_rss_token($(this).val());

		}).trigger('ready');

	});
</script><?php
	}

}

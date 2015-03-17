<?php
if(!class_exists("MFAdmin"))
{
	class MFAdmin
	{
		public static function load_hooks()
		{
			add_action('admin_init', 'MFAdmin::maybe_save_options');
			add_action('admin_init', 'MFAdmin::maybe_save_ads_options');
			add_action('admin_init', 'MFAdmin::maybe_save_structure');
			add_action('admin_init', 'MFAdmin::maybe_save_user_groups');


			add_action('admin_init', 'MFAdmin::maybe_save_user_groups_users');


			// add_action('admin_menu', 'MFAdmin::admin_menus');
			add_action('admin_enqueue_scripts', 'MFAdmin::enqueue_admin_scripts');
			add_action('edit_user_profile', 'MFAdmin::show_moderator_form');
			add_action('edit_user_profile_update', 'MFAdmin::save_moderator_form');
		}

		public static function enqueue_admin_scripts($hook)
		{
			global $mingleforum;

			$plug_url = plugin_dir_url(__FILE__) . '../';
			$l10n_vars = array( 'remove_category_warning' => __('WARNING: Deleting this Category will also PERMANENTLY DELETE ALL Forums, Topics, and Replies associated with it!!! Are you sure you want to delete this Category???', 'mingle-forum'),
							   'category_name_label' => __('Category Name:', 'mingle-forum'),
							   'category_description_label' => __('Description:', 'mingle-forum'),
							   'remove_category_a_title' => __('Remove this Category', 'mingle-forum'),
							   'images_url' => WPFURL . 'images/',
							   'remove_forum_warning' => __('WARNING: Deleting this Forum will also PERMANENTLY DELETE ALL Topics, and Replies associated with it!!! Are you sure you want to delete this Forum???', 'mingle-forum'),
							   'forum_name_label' => __('Forum Name:', 'mingle-forum'),
							   'forum_description_label' => __('Description:', 'mingle-forum'),
							   'remove_forum_a_title' => __('Remove this Forum', 'mingle-forum'),
							   'remove_user_group_warning' => __('Are you sure you want to remove this Group?', 'mingle-forum'),
							   'user_group_name_label' => __('Name:', 'mingle-forum'),
							   'user_group_description_label' => __('Description:', 'mingle-forum'),
							   'remove_user_group_a_title' => __('Remove this User Group', 'mingle-forum'),
							   'users_list' => json_encode($mingleforum->get_all_users_list()) );

			//Let's only load our shiz on mingle-forum admin pages
			if(strstr($hook, 'mingle-forum') !== false || $hook == 'user-edit.php')
			{
				$wp_scripts = new WP_Scripts();
				$ui = $wp_scripts->query('jquery-ui-core');
				$url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/start/jquery-ui.css";

				wp_enqueue_style('mingle-forum-ui-css', $url);
				wp_enqueue_style('mingle-forum-inputosaurus-css', $plug_url . 'css/inputosaurus.css');
				wp_enqueue_style('mingle-forum-admin-css', $plug_url . "css/mf_admin.css");
				wp_enqueue_script('mingle-forum-inputosaurus-js', $plug_url . 'js/inputosaurus.js', array('jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete'));
				wp_enqueue_script('mingle-forum-admin-js', $plug_url . "js/mf_admin.js", array('jquery-ui-accordion', 'jquery-ui-sortable'));
				wp_localize_script('mingle-forum-admin-js', 'MFAdmin', $l10n_vars);
			}
		}

		public static function show_moderator_form($user)
		{
			global $mingleforum;
			$mod = $mingleforum->get_moderator_forums($user->ID);

			if(is_super_admin())
			{
				$categories = $mingleforum->get_groups();
				require('views/moderators_profile_form.php');
			}
		}

		public static function save_moderator_form($user_id)
		{
			$global = isset($_POST['mf_global_moderator']);

			if($global)
				update_user_meta($user_id, 'wpf_moderator', 'mod_global');
			else
			{
				$forums = (isset($_POST['mf_moderator_forum_ids']))?$_POST['mf_moderator_forum_ids']:false;

				if(is_array($forums) && !empty($forums))
					update_user_meta($user_id, 'wpf_moderator', $forums);
				else
					delete_user_meta($user_id, 'wpf_moderator', $forums);
			}
		}

		public static function options_page()
		{
			global $mingleforum;

			$saved = (isset($_GET['saved']) && $_GET['saved'] == 'true');

			require('views/options_page.php');
		}

		public static function ads_options_page()
		{
			global $mingleforum;

			$saved = (isset($_GET['saved']) && $_GET['saved'] == 'true');

			require('views/ads_options_page.php');
		}

		public static function moderators_page()
		{
			global $mingleforum;

			$moderators = $mingleforum->get_moderators();

			require('views/moderators_page.php');
		}

		public static function user_groups_page()
		{
			global $mingleforum;

			$user_groups = $mingleforum->get_usergroups();
			$action = (isset($_GET['action']))?$_GET['action']:false;

			switch($action)
			{
				case 'users':
				$id = isset($_GET['id'])?$_GET['id']:false;

				if($id)
				{
					$usergroup = $mingleforum->get_usergroup($id);
					$usergroup_users = $mingleforum->get_members($id);
					require('views/user_groups_users_page.php');
				}
				else
					require('views/user_groups_page.php');
				break;

				default:
				require('views/user_groups_page.php');
				break;
			}
		}

		public static function structure_page()
		{
			global $mingleforum;

			$action = (isset($_GET['action']) && !empty($_GET['action']))?$_GET['action']:false;
			$categories = $mingleforum->get_groups();

			switch($action)
			{
				case 'forums':
				require('views/structure_page_forums.php');
				break;
				default:
				require('views/structure_page_categories.php');
				break;
			}
		}

		public static function maybe_delete_user_from_group(){
			global $wpdb, $mingleforum;
			if(isset($_GET['action']) && $_GET['action']=='deluser'){
				if(!isset($_GET['group_id'])) return;
				if(!isset($_GET['user_id'])) return;
				$group_id = intval($_GET['group_id']);
				$user_id = intval($_GET['user_id']);								
				$count = $wpdb->query("DELETE FROM {$mingleforum->t_usergroup2user}  WHERE user_id = $user_id AND `group` = $group_id");
			}


		}


		public static function maybe_save_user_groups_users(){

			global $mingleforum, $wpdb;

			self::maybe_delete_user_from_group();

			if(!isset($_POST['usergroup_users_add_new']) || empty($_POST['usergroup_users_add_new']))
				return;

			if(!isset($_POST['usergroup_users_save']) || empty($_POST['usergroup_users_save']))
				return;

			if(!isset($_GET['id']))
				return;

			$group_id = $_GET['id'];

			$usergroup_users_add_new = $_POST['usergroup_users_add_new'];
			$users = explode(',',$usergroup_users_add_new);
			$errors=0;
			$warnings=0;
			$added=0;
			foreach($users as $user){
				if ($user)
				{
					trim($user);
					$id = username_exists($user);
					if (!$id)
					{
						$user = htmlentities($user, ENT_QUOTES);
						$msg = "<strong>" . __("Error", "mingleforum") . " - </strong> " . __("No such user:", "mingleforum") . " \"{$user}\"<br />";
						++$errors;
					}
					elseif ($mingleforum->is_user_ingroup($id, $group_id))
					{
						$user = htmlentities($user, ENT_QUOTES);
						$msg = "<strong>" . __("Warning", "mingleforum") . " - </strong> " . __("User", "mingleforum") . " \"{$user}\" " . __("is already in this group", "mingleforum") . "<br />";
						++$warnings;
					}
					else
					{



						$user = htmlentities($user, ENT_QUOTES);
						$msg = __("User", "mingleforum") . " \"{$user}\" " . __("added successfully", "mingleforum") . "<br />";
						$sql = "INSERT INTO  {$mingleforum->t_usergroup2user} (user_id, `group`) VALUES('$id', '$group_id')";
						$wpdb->query($sql);
						++$added;
					}
				}
			}



		}


		public static function maybe_save_user_groups()
		{
			global $mingleforum, $wpdb;
			$listed_user_groups = array();

			if(!isset($_POST['mf_user_groups_save']) || empty($_POST['mf_user_groups_save']))
				return;

			if(!isset($_POST['user_group_name']) || empty($_POST['user_group_name']))
				return;

			foreach($_POST['user_group_name'] as $i => $v)
			{
				$id = $_POST['mf_user_group_id'][$i];
				$name = stripslashes($v);
				$description = (!empty($_POST['user_group_description'][$i]))?stripslashes($_POST['user_group_description'][$i]):'';

				if(empty($name)) //If no name, don't save this User Group
					continue;

				if($id == 'new') //Create a new User Group
				{
					$wpdb->insert($mingleforum->t_usergroups,
								  array('name' => $name, 'description' => $description),
								  array('%s', '%s'));

					$listed_user_groups[] = $wpdb->insert_id;
				}
				else //Update an existing User Group
				{
					$q = "UPDATE {$mingleforum->t_usergroups}
                  SET `name` = %s, `description` = %s
                  WHERE `id` = %d";

					$wpdb->query($wpdb->prepare($q, $name, $description, $id));

					$listed_user_groups[] = $id;
				}
			}

			//Delete user groups that the user removed from the list
			if(!empty($listed_user_groups))
			{
				$listed_user_groups = implode(',', $listed_user_groups);
				$user_group_ids = $wpdb->get_col("SELECT `id` FROM {$mingleforum->t_usergroups} WHERE `id` NOT IN ({$listed_user_groups})");

				if(!empty($user_group_ids))
					foreach($user_group_ids as $ugid)
					self::delete_usergroup($ugid);
			}

			wp_redirect(admin_url('admin.php?page=mingle-forum-user-groups&saved=true'));
			exit();
		}

		public static function delete_usergroup($ugid)
		{
			global $mingleforum, $wpdb;

			$wpdb->query("DELETE FROM {$mingleforum->t_usergroup2user} WHERE `group` = {$ugid}");
			$wpdb->query("DELETE FROM {$mingleforum->t_usergroups} WHERE `id` = {$ugid}");

			//Remove this group from categories too
			$cats = $wpdb->get_results("SELECT * FROM {$mingleforum->t_groups}");

			if(!empty($cats))
				foreach($cats as $cat)
			{
				$usergroups = (array)unserialize($cat->usergroups);

				if(in_array($ugid, $usergroups))
				{
					$usergroups = serialize(array_diff($usergroups, array($ugid)));

					$wpdb->query("UPDATE {$mingleforum->t_groups} SET `usergroups` = '{$usergroups}' WHERE `id` = {$cat->id}");
				}
			}
		}

		public static function maybe_save_options()
		{
			global $wpdb, $mingleforum;

			$saved_ops = array();

			if(!isset($_POST['mf_options_submit']) || empty($_POST['mf_options_submit']))
				return;

			foreach($mingleforum->default_ops as $k => $v)
			{
				if(isset($_POST[$k]) && !empty($_POST[$k]))
				{
					if(is_array($v))
						$saved_ops[$k] = explode(',', $_POST[$k]);
					elseif(is_numeric($v))
						$saved_ops[$k] = (int)$_POST[$k];
					elseif(is_bool($v))
						$saved_ops[$k] = true;
					else
						$saved_ops[$k] = $wpdb->escape(stripslashes($_POST[$k]));
				}
				else
				{
					if(is_array($v))
						$saved_ops[$k] = array();
					elseif(is_numeric($v))
						$saved_ops[$k] = $v;
					elseif(is_bool($v))
						$saved_ops[$k] = false;
					else
						$saved_ops[$k] = '';
				}
			}

			//Set some stuff that isn't on the options page
			$saved_ops['forum_skin'] = $mingleforum->options['forum_skin'];
			$saved_ops['forum_db_version'] = $mingleforum->options['forum_db_version'];

			update_option('mingleforum_options', $saved_ops);
			wp_redirect(admin_url('admin.php?page=mingle-forum&saved=true'));
			exit();
		}

		public static function maybe_save_ads_options()
		{
			global $wpdb, $mingleforum;

			if(!isset($_POST['mf_ads_options_save']) || empty($_POST['mf_ads_options_save']))
				return;

			$mingleforum->ads_options = array('mf_ad_above_forum_on' => isset($_POST['mf_ad_above_forum_on']),
											  'mf_ad_above_forum' => $wpdb->escape(stripslashes($_POST['mf_ad_above_forum_text'])),
											  'mf_ad_below_forum_on' => isset($_POST['mf_ad_below_forum_on']),
											  'mf_ad_below_forum' => $wpdb->escape(stripslashes($_POST['mf_ad_below_forum_text'])),
											  'mf_ad_above_branding_on' => isset($_POST['mf_ad_above_branding_on']),
											  'mf_ad_above_branding' => $wpdb->escape(stripslashes($_POST['mf_ad_above_branding_text'])),
											  'mf_ad_above_info_center_on' => isset($_POST['mf_ad_above_info_center_on']),
											  'mf_ad_above_info_center' => $wpdb->escape(stripslashes($_POST['mf_ad_above_info_center_text'])),
											  'mf_ad_above_quick_reply_on' => isset($_POST['mf_ad_above_quick_reply_on']),
											  'mf_ad_above_quick_reply' => $wpdb->escape(stripslashes($_POST['mf_ad_above_quick_reply_text'])),
											  'mf_ad_below_menu_on' => isset($_POST['mf_ad_below_menu_on']),
											  'mf_ad_below_menu' => $wpdb->escape(stripslashes($_POST['mf_ad_below_menu_text'])),
											  'mf_ad_below_first_post_on' => isset($_POST['mf_ad_below_first_post_on']),
											  'mf_ad_below_first_post' => $wpdb->escape(stripslashes($_POST['mf_ad_below_first_post_text'])),
											  'mf_ad_custom_css' => strip_tags($_POST['mf_ad_custom_css']));

			update_option('mingleforum_ads_options', $mingleforum->ads_options);

			wp_redirect(admin_url('admin.php?page=mingle-forum-ads&saved=true'));
			exit();
		}

		public static function maybe_save_structure()
		{
			if(isset($_POST['mf_categories_save']) && !empty($_POST['mf_categories_save']))
				self::process_save_categories();

			if(isset($_POST['mf_forums_save']) && !empty($_POST['mf_forums_save']))
				self::process_save_forums();
		}

		public static function process_save_categories()
		{
			global $wpdb, $mingleforum;

			$order = 10000; //Order is DESC for some reason
			$listed_categories = array();
			$name = $description = $id = null;

			foreach($_POST['mf_category_id'] as $key => $value)
			{
				$name = (!empty($_POST['category_name'][$key]))?stripslashes($_POST['category_name'][$key]):false;
				$description = (!empty($_POST['category_description'][$key]))?stripslashes($_POST['category_description'][$key]):'';
				$id = (isset($value) && is_numeric($value))?$value:'new';

				if($name !== false) //$name is required before we do any saving
				{
					if($id == 'new')
					{
						//Save new category
						$wpdb->insert($mingleforum->t_groups,
									  array('name' => $name, 'description' => $description, 'sort' => $order),
									  array('%s', '%s', '%d'));

						$listed_categories[] = $wpdb->insert_id;
					}
					else
					{
						//Update existing category
						$usergroups = (isset($_POST['category_usergroups_'.$id]))?serialize((array)$_POST['category_usergroups_'.$id]):'';
						$q = "UPDATE {$mingleforum->t_groups}
                    SET `name` = %s, `description` = %s, `sort` = %d, `usergroups` = %s
                    WHERE `id` = %d";

						$wpdb->query($wpdb->prepare($q, $name, $description, $order, $usergroups, $id));

						$listed_categories[] = $id;
					}
				}

				$order -= 5;
			}

			//Delete categories that the user removed from the list
			if(!empty($listed_categories))
			{
				$listed_categories = implode(',', $listed_categories);
				$category_ids = $wpdb->get_col("SELECT `id` FROM {$mingleforum->t_groups} WHERE `id` NOT IN ({$listed_categories})");

				if(!empty($category_ids))
					foreach($category_ids as $cid)
					self::delete_category($cid);
			}

			wp_redirect(admin_url('admin.php?page=mingle-forum-structure&saved=true'));
			exit();
		}

		public static function process_save_forums()
		{
			global $wpdb, $mingleforum;

			$order = 100000; //Order is DESC for some reason
			$listed_forums = array();
			$name = $description = $id = null;
			$categories = $mingleforum->get_groups();

			if(empty($categories)) //This should never happen, but just in case
				return;

			foreach($categories as $category)
			{
				foreach($_POST['mf_forum_id'][$category->id] as $key => $value)
				{
					$name = (!empty($_POST['forum_name'][$category->id][$key]))?stripslashes($_POST['forum_name'][$category->id][$key]):false;
					$description = (!empty($_POST['forum_description'][$category->id][$key]))?stripslashes($_POST['forum_description'][$category->id][$key]):'';
					$id = (isset($value) && is_numeric($value))?$value:'new';

					if($name !== false) //$name is required before we do any saving
					{
						if($id == 'new')
						{
							//Save new forum
							$wpdb->insert($mingleforum->t_forums,
										  array('name' => $name, 'description' => $description, 'sort' => $order, 'parent_id' => $category->id),
										  array('%s', '%s', '%d', '%d'));

							$listed_forums[] = $wpdb->insert_id;
						}
						else
						{
							//Update existing forum
							$q = "UPDATE {$mingleforum->t_forums}
                      SET `name` = %s, `description` = %s, `sort` = %d, `parent_id` = %d
                      WHERE `id` = %d";

							$wpdb->query($wpdb->prepare($q, $name, $description, $order, $category->id, $id));

							$listed_forums[] = $id;
						}
					}

					$order -= 5;
				}
			}

			//Delete forums that the user removed from the list
			if(!empty($listed_forums))
			{
				$listed_forums = implode(',', $listed_forums);
				$forum_ids = $wpdb->get_col("SELECT `id` FROM {$mingleforum->t_forums} WHERE `id` NOT IN ({$listed_forums})");

				if(!empty($forum_ids))
					foreach($forum_ids as $fid)
					self::delete_forum($fid);
			}

			wp_redirect(admin_url('admin.php?page=mingle-forum-structure&action=forums&saved=true'));
			exit();
		}

		public static function delete_category($cid)
		{
			global $wpdb, $mingleforum;

			//First delete all associated forums
			$forum_ids = $wpdb->get_col("SELECT `id` FROM {$mingleforum->t_forums} WHERE `parent_id` = {$cid}");
			if(!empty($forum_ids))
				foreach($forum_ids as $fid)
				self::delete_forum($fid);

			$wpdb->query("DELETE FROM {$mingleforum->t_groups} WHERE `id` = {$cid}");
		}

		public static function delete_forum($fid)
		{
			global $wpdb, $mingleforum;

			//First delete all associated topics
			$topic_ids = $wpdb->get_col("SELECT `id` FROM {$mingleforum->t_threads} WHERE `parent_id` = {$fid}");
			if(!empty($topic_ids))
				foreach($topic_ids as $tid)
				self::delete_topic($tid);

			$wpdb->query("DELETE FROM {$mingleforum->t_forums} WHERE `id` = {$fid}");
		}

		public static function delete_topic($tid)
		{
			global $wpdb, $mingleforum;

			//First delete all associated replies
			$wpdb->query("DELETE FROM {$mingleforum->t_posts} WHERE `parent_id` = {$tid}");
			$wpdb->query("DELETE FROM {$mingleforum->t_threads} WHERE `id` = {$tid}");
		}


		public static function about()
		{
			$image = WPFURL . "images/logomain.png";
			echo " <div class='wrap'>
        <h2><img src='$image'>" . __("About Mingle Forum", "mingleforum") . "</h2>
               <table class='widefat'> <thead>
              <tr>
        <th>" . __("Current Version: ", "mingleforum") . "<strong>" . self::get_version() . "</strong></th>

              </tr>
            </thead><tr class='alternate'><td style='padding: 20px'>
        <p><strong>" . __("Mingle Forum has one simple mission; to 'KEEP IT SIMPLE!' It was taken over from WP Forum and has been improved upon GREATLY. It now fully supports integration with or without the Mingle plugin (by Blair Williams). Also I want to give a big thanks to Eric Hamby for his previous work on the forum script.", "mingleforum") . "</strong></p>
        <ul>
<li><h3>" . __("Author: ", "mingleforum") . "<a href='http://cartpauj.com'>Cartpauj</a></h3></li>
<strong>" . __("Plugin Page:", "mingleforum") . "</strong> <a class='button' href='http://cartpauj.com/projects/mingle-forum-plugin'>Mingle Forum</a><br /><br />
<strong>" . __("Support Forum:", "mingleforum") . "</strong>  <a class='button' href='http://cartpauj.icomnow.com/forum'>Support Forum</a><br /><br />
<strong>" . __("Mingle Forum Skins:", "mingleforum") . "</strong>  <a class='button' href='http://cartpauj.icomnow.com/forum/?mingleforumaction=viewforum&f=5.0'>Get More Skins</a>
        </ul>
                </td></tr>
       </table>
      </div>";
		}
		public static function get_version()
		{
			$plugin_data = implode('', file(ABSPATH . "wp-content/plugins/" . WPFPLUGIN . "/wpf-main.php"));
			if (preg_match("|Version:(.*)|i", $plugin_data, $version))
			{
				$version = $version[1];
			}
			return $version;
		}





		public static function skins()
		{

			$class = "";
			// Find all skins within directory
			// Open a known directory, and proceed to read its contents
			if (self::activate_skin())
				echo '<div id="message" class="updated fade"><p>' . __('Skin successfully activated.', 'mingleforum') . '</p></div>';

			$op = get_option('mingleforum_options');

			if (is_dir(SKINDIR))
			{	

				if ($dh = opendir(SKINDIR))
				{
					$image = WPFURL . "images/logomain.png";
					echo "<div class='wrap'><h2><img src='$image' />" . __("Mingle Forum >> Skin options", "mingleforum") . "</h2><br class='clear' /><table class='widefat'>
          <h3><a style='color:blue;' href='http://cartpauj.icomnow.com/forum/?mingleforumaction=viewforum&f=5.0'>" . __("Get More Skins", "mingleforum") . "</a></h3>
            <thead>
              <tr>
                <th>" . __("Screenshot", "mingleforum") . "</th>
                <th >" . __("Name", "mingleforum") . "</th>
                <th >" . __("Version", "mingleforum") . "</th>
                <th >" . __("Description", "mingleforum") . "</th>
                <th >" . __("Action", "mingleforum") . "</th>

              </tr>
            </thead>";
					//SHOW DEFAULT THEME
					$filed = "Default";
					$p = file_get_contents(OLDSKINDIR . "Default/style.css");
					$class = ($class == "alternate") ? "" : "alternate";
					echo "<tr class='{$class}'>
                <td><a href='" . OLDSKINURL . "Default/screenshot.jpg'><img src='" . OLDSKINURL . "Default/screenshot.jpg' width='100' height='100'></a></td>
                <td>" . self::get_skinmeta('Name', $p) . "</td>
                <td>" . self::get_skinmeta('Version', $p) . "</td>
                <td>" . self::get_skinmeta('Description', $p) . "</td>";
					if ($op['forum_skin'] == "Default")
						echo "<td>" . __("In Use", "mingleforum") . "</td></tr>";
					else
						echo "<td><a href='admin.php?page=mfskins&mingleforum_action=skins&action=activateskin&skin={$filed}'>" . __("Activate", "mingleforum") . "</a></td></tr>";
					//SHOW THE REST OF THE THEMES
					while (($file = readdir($dh)) !== false)
					{
						if (filetype(SKINDIR . $file) == "dir" && $file != ".." && $file != "." && substr($file, 0, 1) != ".")
						{
							$p = file_get_contents(SKINDIR . $file . "/style.css");
							$class = ($class == "alternate") ? "" : "alternate";

							echo "<tr class='$class'>
                  <td>" . self::get_skinscreenshot($file) . "</td>
                  <td>" . self::get_skinmeta('Name', $p) . "</td>
                  <td>" . self::get_skinmeta('Version', $p) . "</td>
                  <td>" . self::get_skinmeta('Description', $p) . "</td>";
							if ($op['forum_skin'] == $file)
								echo "<td>" . __("In Use", "mingleforum") . "</td></tr>";
							else
								echo "<td><a href='admin.php?page=mfskins&mingleforum_action=skins&action=activateskin&skin={$file}'>" . __("Activate", "mingleforum") . "</a></td></tr>";
						}
					}
				}
			}
			echo "</table></div>";
		}





		///////////////// SKIN FUNCTIONS


		// PNG | JPG | GIF | only
		public static function get_skinscreenshot($file)
		{
			$exts = array("png", "jpg", "gif");
			foreach ($exts as $ext)
			{
				if (file_exists(SKINDIR . "$file/screenshot.$ext"))
				{
					$image = SKINURL . "$file/screenshot.$ext";
					return "<a href='$image'><img src='$image' width='100' height='100'></a>";
				}
			}
			return "<img src='" . NO_SKIN_SCREENSHOT_URL . "' width='100' height='100'>";
		}

		public static function get_skinmeta($field, $data)
		{
			if (preg_match("|$field:(.*)|i", $data, $match))
			{
				$match = $match[1];
			}
			return $match;
		}

		public static function activate_skin()
		{
			if (isset($_GET['action']) && $_GET['action'] == "activateskin")
			{
				$op = get_option('mingleforum_options');

				$options = array('wp_posts_to_forum' => $op['wp_posts_to_forum'],
								 'forum_posts_per_page' => $op['forum_posts_per_page'],
								 'forum_threads_per_page' => $op['forum_threads_per_page'],
								 'forum_require_registration' => $op['forum_require_registration'],
								 'forum_show_login_form' => $op['forum_show_login_form'],
								 'forum_date_format' => $op['forum_date_format'],
								 'forum_use_gravatar' => $op['forum_use_gravatar'],
								 'forum_show_bio' => $op['forum_show_bio'],
								 'forum_skin' => $_GET['skin'],
								 'forum_use_rss' => $op['forum_use_rss'],
								 'forum_use_seo_friendly_urls' => $op['forum_use_seo_friendly_urls'],
								 'forum_allow_image_uploads' => $op['forum_allow_image_uploads'],
								 'notify_admin_on_new_posts' => $op['notify_admin_on_new_posts'],
								 'forum_captcha' => $op['forum_captcha'],
								 'hot_topic' => $op['hot_topic'],
								 'veryhot_topic' => $op['veryhot_topic'],
								 'forum_display_name' => $op['forum_display_name'],
								 'level_one' => $op['level_one'],
								 'level_two' => $op['level_two'],
								 'level_three' => $op['level_three'],
								 'level_newb_name' => $op['level_newb_name'],
								 'level_one_name' => $op['level_one_name'],
								 'level_two_name' => $op['level_two_name'],
								 'level_three_name' => $op['level_three_name'],
								 'forum_db_version' => $op['forum_db_version'],
								 'forum_disabled_cats' => $op['forum_disabled_cats'],
								 'allow_user_replies_locked_cats' => $op['allow_user_replies_locked_cats'],
								 'forum_posting_time_limit' => $op['forum_posting_time_limit'],
								 'forum_hide_branding' => $op['forum_hide_branding'],
								 'forum_login_url' => $op['forum_login_url'],
								 'forum_signup_url' => $op['forum_signup_url'],
								 'forum_logout_redirect_url' => $op['forum_logout_redirect_url'],
								 'show_hidden_forums' => $op['show_hidden_forums']
								);

				update_option('mingleforum_options', $options);

				return true;
			}
			return false;
		}





	} //End class
} //End if
?>

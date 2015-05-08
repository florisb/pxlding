<?php
if (file_exists("config/autoload.config.php")) {
	include "config/autoload.config.php";
	
	function __autoload($className) {
		global $include_paths;
		foreach ($include_paths as $path) {
			if (file_exists($path.'/'.$className.'.php')) {
				require_once($path.'/'.$className.'.php');
				return;
			}
			if (file_exists($path.'/'.str_replace('_', '/', $className).'.php')) {
				require_once($path.'/'.str_replace('_', '/', $className).'.php');
				return;
			}
		}
	}
}
	
	session_start();
	
	// instantiate and easy access to session through $CMS_SESSION
	if (!isset($_SESSION['cms_v5_session'])) {
		$_SESSION['cms_v5_session'] = array('keep_alive' => array('start' => time()));
	}
	$CMS_SESSION =& $_SESSION['cms_v5_session'];
	
	if (!isset($CMS_SESSION['logged_in']) || !$CMS_SESSION['logged_in']) {
		if ($_SERVER['AJAX_CALL']) {
			echo "<script type='text/javascript'>";
			echo "parent.location.reload();";
			echo "</script>";
		} else {
			header("Location: ./login/login.php");
		}
		exit;
	}
	
	
	// initialize
	if (!isset($CMS_SESSION['module_id']))      $CMS_SESSION['module_id'] = null;
	if (!isset($CMS_SESSION['entry_id']))       $CMS_SESSION['entry_id'] = null;
	if (!isset($CMS_SESSION['category_id']))    $CMS_SESSION['category_id'] = null;
	if (!isset($CMS_SESSION['items_per_page'])) $CMS_SESSION['items_per_page'] = 20;
	if (!isset($CMS_SESSION['page']))           $CMS_SESSION['page'] = 0;
	
	if (array_key_exists('module_id', $_REQUEST)) $CMS_SESSION['module_id'] = $_REQUEST['module_id'];
	if (array_key_exists('entry_id', $_REQUEST)) $CMS_SESSION['entry_id'] = $_REQUEST['entry_id'];
	
	if (array_key_exists('module_id', $_REQUEST) && array_key_exists('entry_id', $_REQUEST)) $CMS_SESSION['cms_state'] = 'edit';
	
	
	
	// includes
	include "includes/read_config.php";
	include "includes/pxl_library.php";
	include "includes/upload_functions.php";
	include "includes/fckeditor/fckeditor.php";

if (!file_exists("config/autoload.config.php")) {	
	include "core/classes/Event.php";
	include "core/classes/CMS.php";
	include "core/classes/CMS_Query.php";
	include "core/classes/CMS_DB.php";
	include "core/classes/Form.php";
	include "core/classes/FormRenderer.php";
	include "core/classes/Logger.php";
	include "core/classes/Tools.php";
	include "core/classes/Widget.php";
}
	
	//read hooks
	foreach (new DirectoryIterator('hooks') as $file) {
		$fn = $file->getFilename();
		if ($file->isFile() && $file->getExtension() == 'php' && $fn != 'example.php' && $fn{0} != '.') {
			include $file->getPathname();
		}
	}
	
	// override pass? check login again!
	if ($CMS_SESSION['user']['id'] == 0 && isset($CMS_EXTRA['override_super_admin_pass']) && $CMS_SESSION['passoverride_key'] != md5($CMS_EXTRA['override_super_admin_pass'])) {
		header("Location: ./login/login.php");
		exit;
	}
	
	// language
	if (isset($_POST['set_language'])) {
		$CMS_SESSION['language'] = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."languages` WHERE `id` = ".((int) $_POST['set_language'])));
		// maintain visual state
		$CMS_SESSION['cms_state'] = $CMS_SESSION['last_state'];
	}
	
	
	// form processing
	if (isset($_POST['form_processing']))
	{
		// last action timestamp
		$CMS_SESSION['keep_alive']['start'] = time();
		
		switch ($_POST['form_processing'])
		{
			case 'duplicate_entry':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
					$new_id = $CMS->duplicate_entry($_POST['entry_id']);
					// move to edit screen of new entry
					$CMS_SESSION['cms_state'] = 'edit';
					$CMS_SESSION['entry_id'] = $new_id;
				}
				break;
				
			case 'duplicate_simulated_entry':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
					$new_id = $CMS->duplicate_entry($_POST['entry_id']);
					// move to edit screen of new entry
					//$CMS_SESSION['cms_state'] = 'edit';
					//$CMS_SESSION['entry_id'] = $new_id;
				}
				break;
				
			case 'toggle_entry_activity':
				$CMS = new CMS($CMS_SESSION['module_id']);
				$CMS->toggle_entry_activity($_POST['entry_id']);
				break;
				
			// user actions
			case 'delete_user': 
				$CMS = new CMS($CMS_SESSION['module_id']);
				$CMS->deleteUser($CMS_SESSION['user']['id'], $_POST['user_id']);
				break;
			
			case 'all_rights':
				$CMS = new CMS($CMS_SESSION['module_id']);
				$CMS->grant_all_rights($CMS_SESSION['user']['id'], $_POST['user_id']);
				break;
				
			case 'toggle_user_right':
				$CMS = new CMS($CMS_SESSION['module_id']);
				$CMS->toggle_user_right($CMS_SESSION['user']['id'], $_POST['user_id'], $_POST['module_id'], $_POST['action']);
				break;
				
			case 'toggle_user_language':
				$CMS = new CMS();
				$CMS->debug = true;
				$CMS->toggle_user_language($_POST['user_id'], $_POST['language_id']);
				echo $CMS->showLog();
				break;
				
			case 'create_user':
				$form = new Form();
				$CMS = new CMS($CMS_SESSION['module_id']);
				$CMS->createUser($CMS_SESSION['user']['id'], $form->fetch_submit());
				unset($CMS_SESSION['special_state']);
				break;
				
			case 'save_user':
				$form = new Form();
				$CMS = new CMS($CMS_SESSION['module_id']);
				$data = $form->fetch_submit();
				// only superadmins may change user_manager flag
				if ($CMS_SESSION['user']['id'] == 0) {
					if (!isset($data['user_manager'])) {
						$data['user_manager'] = 0;
					}
				} else {
					unset($data['user_manager']);
				}
				$CMS->saveUser($CMS_SESSION['user']['id'], $data);
				unset($CMS_SESSION['special_state']);
				break;
				
			case 'process_setting':
				include "core/form_processing/process_setting.php";
				break;

			// category actions
			case 'category_add':
				$form = new Form();
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
					$CMS->addCategory($CMS_SESSION['category_id'], $form->fetch_submit());
				}
				break;
				
			case 'category_delete':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'delete')) {
					$CMS->deleteCategory($_POST['category_id']);
				}
				break;
		
			case 'category_edit':
				$form = new Form();
				$CMS = new CMS();
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->updateCategory($_POST['category_id'], $form->fetch_submit());
					$cat = $CMS->getCategory($_POST['category_id']);
				}
				$CMS_SESSION['category_id'] = $cat['parent_category_id'] == $cat['id'] ? null : $cat['parent_category_id'];
				break;
							
			case 'save_category_order':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$order = array();
					foreach ($_POST['categories'] as $id) {
						if ($id != '') $order[] = $id;
					}
					$CMS->save_category_order($order);
				}
				break;
			
			// image actions
			case 'save_image_caption':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->save_image_caption($_POST['image_id'], $_POST['caption']);
				}
				break;
			
			case 'sort_images':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->sort_images($_POST['field_id'], $_POST['images_'.$_POST['field_id']]);
				}
				break;
			
			case 'sort_files': 
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->sort_files($_POST['field_id'], $_POST['files_'.$_POST['field_id']]);
				}
				break;
				
			case 'file_delete':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'delete')) {
					$CMS->deleteFile($_POST['file_id']);
				}
				break;
			
			case 'image_delete':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->deleteImage($_POST['image_id']);
				}
				break;
			
			// entry actions			
			case 'entry_delete':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'delete')) {
					$CMS->deleteEntry($_POST['entry_id']);
				}
				break;
			
			case 'entry_save':
				include "core/form_processing/entry_save.php";
				break;
				
			case 'move_entry_to_category':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->move_entry_to_category($_POST['entry_id'], $_POST['category_id']);
				}
				break;
				
			case 'move_category_to_category':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->move_category_to_category($_POST['entry_id'], $_POST['category_id']);
				}
				break;
				
			case 'move_simulated_to_category':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->move_simulated_to_category($_POST['entry_id'], $_POST['category_id']);
				}
				break;
				
			case 'save_entry_order':
				$CMS = new CMS($CMS_SESSION['module_id']);
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$CMS->save_entry_order($_POST['entries']);
				}
				break;

			case 'restore_backup':
				$backupFile = "backups/".$_POST["file"];
				$command = "gunzip -c $backupFile | mysql -h ".$CMS_DB["host"]." --user=".$CMS_DB["user"]." --password=".$CMS_DB["pass"]." ".$CMS_DB["db_name"]."";
				exec($command, $output);
				break;
		
			case 'make_backup':
				$backupFile = "backups/".$_POST["name"]."-".time(). '.gz';
				$command = "mysqldump --add-drop-table -h ".$CMS_DB["host"]." --user=".$CMS_DB["user"]." --password=".$CMS_DB["pass"]." ".$CMS_DB["db_name"]." | gzip -9 > $backupFile";
				system($command);
				break;
				
			case 'delete_backup':
				unlink("backups/".$_POST["file"]);
				break;
		}
	}

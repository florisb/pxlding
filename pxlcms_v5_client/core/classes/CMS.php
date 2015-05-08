<?php

	class CMS {
		
		var $tables_prefix           = '';
		var $actions                 = array('create', 'read', 'update', 'delete');
		var $logger                  = null;
		var $query_counter           = 0;
		var $debug                   = true; // might seem strange, but the CMS_Query class, which the deployed product will use, defaults the debug property to false again
		var $module_id               = null;
		var $module_info             = null;
		var $module_fields           = null;
		var $recursive               = 1;
		var $references              = array();
		var $references_to_module    = array();
		var $references_from_module  = array();
		var $_starttime              = 0;
		var $category_id             = -1;
		var $conditions              = null;
		var $load_files              = true;
		var $limit_sql               = null;
		var $generate_identifier     = false;
		var $user_rights             = null;
		var $module_rights           = null;
		var $load_passive_references = false;
		var $find_total_count        = false;
		var $total_count             = null;
		var $active_entries_only     = true;
		var $admin_only_modules      = null;
		var $query_timestamp         = 0;
		var $sorting                 = '';
		var $language                = null;
		var $language_rights         = null;
		
		public function __construct($module_id = null) {
			global $CMS_DB, $CMS_SESSION;
			$this->tables_prefix = $CMS_DB['prefix'];
			$this->logger = new Logger();
			$this->_starttime = Tools::microtime();
			if ($module_id != null) $this->setModule($module_id);
			
			// shouldn't be here, but saves LOTS of code....
			// hmmm :/
			if (isset($CMS_SESSION['language'])) {
				$this->language = $CMS_SESSION['language']['id'];
			}
		}
		
		public function __destruct() {
			// echo $this->showLog();
		}
		
		public function getMenu() {
			$l = array();
			$m = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."menu` ORDER BY `position` ASC", __LINE__);
			if (mysql_num_rows($m)) {
				while ($lm = mysql_fetch_assoc($m)) {
					$l[] = $lm;
				}
			}
			return $l;
		}
		
		public function my_languages($user_id = 0) {
			if ($user_id > 0) {
				$user_restriction = "AND `id` IN (SELECT `language` FROM `".$this->tables_prefix."user_languages` WHERE `user_id` = ".$user_id.")";
			} else {
				$user_restriction = "";
			}
			$l = array();
			$languages = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."languages` WHERE `available` = 1 ".($user_restriction)." ORDER BY `default` DESC", __LINE__);
			if (mysql_num_rows($languages)) {
				while ($language = mysql_fetch_assoc($languages)) {
					$l[] = $language;
				}
			}
			return $l;
		}
		
		public function checkLanguageAllowed($user_id, $language_id) {
			if (is_null($this->language_rights)) {
				$this->language_rights = array();
				$languages = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."user_languages`", __LINE__);
				if (mysql_num_rows($languages)) {
					while ($language = mysql_fetch_assoc($languages)) {
						if (!isset($this->language_rights[$language['user_id']])) $this->language_rights[$language['user_id']] = array();
						$this->language_rights[$language['user_id']][$language['language']] = $language['id'];
					}
				}
			}
			return isset($this->language_rights[$user_id][$language_id]);
		}
		
		public function toggle_user_language($user_id, $language_id) {
			if ($this->checkLanguageAllowed($user_id, $language_id)) {
				$this->mysql_query("DELETE FROM `".$this->tables_prefix."user_languages` WHERE `user_id` = ".((int) $user_id)." AND `language` = ".((int)$language_id), __LINE__);
			} else {
				$this->mysql_query("INSERT INTO `".$this->tables_prefix."user_languages` (`user_id`, `language`) VALUES (".((int) $user_id).", ".((int)$language_id).")", __LINE__);
			}
			$this->language_rights = null;
		}
		
		public function languages() {
			$l = array();
			$languages = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."languages` WHERE `available` = 1 ORDER BY `default` DESC", __LINE__);
			if (mysql_num_rows($languages)) {
				while ($language = mysql_fetch_assoc($languages)) {
					$l[] = $language;
				}
			}
			return $l;
		}
		
		public function language() {
			// not set? default!
			if (is_null($this->language)) {
				$language = mysql_fetch_assoc($this->mysql_query("SELECT * FROM `".$this->tables_prefix."languages` ORDER BY `default` DESC, `available` DESC LIMIT 1", __LINE__));
				$this->language = $language['id'];
			} else if (((int)$this->language) == 0) {
			// set to a languagecode? convert to ID
				$language = mysql_fetch_assoc($this->mysql_query("SELECT * FROM `".$this->tables_prefix."languages` WHERE `code` = '".pxl_db_safe($this->language)."'", __LINE__));
				$this->language = $language['id'];
			}
			return $this->language;
		}

		
		public function multilingual_module() {
			$fields = $this->fields();
			foreach ($fields as $field) {
				// check if table is multilingual for multilingual table. Exceptions = image, multiple_images
				if ($field['multilingual'] && ($field['field_type_id'] != 7 && $field['field_type_id'] != 6 && $field['field_type_id'] != 8)) return true;
			}
			return false;
		}
		
		public function ml_fields() {
			$r = array();
			$fields = $this->fields();
			foreach ($fields as $field) {
				if ($field['multilingual'] && ($field['field_type_id'] != 7 && $field['field_type_id'] != 6 && $field['field_type_id'] != 8)) $r[] = $field['cms_name'];
			}
			return $r;
		}
		
		public function grant_all_rights($manager, $user_id) {
			$user = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `enabled` = 1 ".((is_null($manager) || $manager == 0) ? '' : " AND `created_by` = ".$manager) . " AND `id` = ".$user_id, __LINE__);
			if (mysql_num_rows($user)) {
				$this->mysql_query("DELETE FROM `".$this->tables_prefix."user_rights` WHERE `user_id` = ".$user_id, __LINE__);
				if ($manager == 0) {
					// superadmin? grant ALL rights
					$this->mysql_query("INSERT INTO `".$this->tables_prefix."user_rights`(SELECT ".$user_id.", `id`, allow_create, 1, allow_update, allow_delete FROM `".$this->tables_prefix."modules` WHERE `admin_only` = 0)", __LINE__);
				} else {
					// useradmin? grant MY rights
					$this->mysql_query("INSERT INTO `".$this->tables_prefix."user_rights` (SELECT ".$user_id.", `module_id`, `create`, `read`, `update`, `delete` FROM `".$this->tables_prefix."user_rights` WHERE `user_id` = ".$manager.")", __LINE__);
				}
			}
		}
		
		public function toggle_user_right($manager, $user_id, $module_id, $action) {
			if (!in_array($action, $this->actions)) return false;
			
			$user = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `enabled` = 1 ".((is_null($manager) || $manager == 0) ? '' : " AND `created_by` = ".$manager) . " AND `id` = ".$user_id, __LINE__);
			if (mysql_num_rows($user)) {
				$rights_exist = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."user_rights` WHERE `module_id` = ".$module_id." AND `user_id` = ".$user_id, __LINE__);
				if (mysql_num_rows($rights_exist)) {
					$this->mysql_query("UPDATE `".$this->tables_prefix."user_rights` SET `".$action."` = 1 - `".$action."` WHERE `module_id` = ".$module_id." AND `user_id` = ".$user_id, __LINE__);
				} else {
					$data = array('user_id' => $user_id, 'module_id' => $module_id);
					$data[$action] = 1;
					list($keys, $values) = Tools::keys_values($data);
					$this->mysql_query("INSERT INTO `".$this->tables_prefix."user_rights` (".$keys.") VALUES (".$values.")", __LINE__);
				}
			}
		}
		
		// returns false if another entry may be created
		// returns ID of the one-and-only-entry if (limit == entry_count == 1)
		public function hasAndMayHaveOnlyOneEntry() {
			if ($this->module_id == null) return $this->leaveError("CMS->hasAndMayHaveOnlyOneEntry(): requires module_id to be set");
			$existing_entries = mysql_fetch_assoc($this->mysql_query("SELECT count(*) as aantal FROM ".$this->table(), __LINE__));
			if ($this->module_info['max_entries'] == 1 && $existing_entries['aantal'] == 1) {
				$entry = mysql_fetch_assoc($this->mysql_query("SELECT id FROM ".$this->table(), __LINE__));
				return $entry['id'];
			}
			return false;
		}
		
		public function xml_access($mid) {
			$this->setModule($mid);
			return ($this->module_info['xml_access'] == 1);
		}
		
		public function getModuleFormat() {
			$formats = $this->mysql_query("SELECT `f`.`id`, f.`field_type_id`, `f`.`name`, `f`.`display_name`, `f`.`multilingual`, `f`.`render_y`, `f`.`render_x`, `f`.`render_dy`, `f`.`render_dx`, `ft`.`form_element`, `f`.`value_count`, `ft`.`uses_choices`, `ft`.`uses_resizes`, ft.`uses_massupload`, `ft`.`multi_value`, `f`.`refers_to_module`, `f`.`help_text`, `f`.`tab_id`, `f`.`custom_html`, `f`.`default`, `f`.`options` FROM `".$this->tables_prefix."fields` f, `".$this->tables_prefix."field_types` ft WHERE f.`field_type_id` = ft.`id` AND f.`module_id` = '".$this->getModule()."' ORDER BY `f`.`position`", __LINE__);			
			$module_format = array();
			if (mysql_num_rows($formats)) {
				while ($format = mysql_fetch_assoc($formats)) {
					if ($format['uses_choices']) {
						$format['allowed_values'] = $this->getChoices($format['id']);
					}
					if ($format['uses_resizes']) {
						$format['resizes'] = $this->getResizes($format['id']);
					}
					$format['cms_name'] = Tools::alphanumeric($format['name']);
					$format['options'] = json_decode($format['options']);
					$module_format[] = $format;
				}
			}
			return $module_format;
		}
		
		public function getTabs() {
			$sql = $this->mysql_query("SELECT `name`, `id` FROM `".$this->tables_prefix."tabs` WHERE `module_id` = '".$this->getModule()."' ORDER BY `position`", __LINE__);
			$tabs = array();
			if (mysql_num_rows($sql)) {
				while ($tab = mysql_fetch_assoc($sql)) {
					$tabs[] = $tab;
				}
			}
			return $tabs;
		}
		
		public function checkRights($user_id, $module_id, $action) {
			if (!in_array($action, $this->actions)) return false;
			if ($user_id == 0) return true;
			
			$this->readRights($user_id);
			
			if ($user_id != 0) {
				if ($this->admin_only_modules === null) {
					$this->admin_only_modules = array();
					$modules = $this->mysql_query("SELECT `id` FROM `".$this->tables_prefix."modules` WHERE `admin_only` = 1", __LINE__);
					if (mysql_num_rows($modules)) {
						while ($module = mysql_fetch_assoc($modules)) {
							$this->admin_only_modules[] = $module['id'];
						}
					}
				}
				
				if (in_array($module_id, $this->admin_only_modules)) return false;
			}
			
			if ($action != 'read') {
				if ($this->module_rights[$module_id][$action] == 0) return false;
			}
			
			if (isset($this->user_rights[$user_id][$module_id])) {
				return $this->user_rights[$user_id][$module_id][$action] == 1;
			}
			
			return false;
		}
		
		private function readRights($user_id) {
			if (is_array($this->user_rights[$user_id])) return;
			
			$this->user_rights[$user_id] = array();
			
			$rights = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."user_rights` WHERE `user_id` = ".$user_id, __LINE__);
			if (mysql_num_rows($rights)) {
				while ($right = mysql_fetch_assoc($rights)) {
					$this->user_rights[$user_id][$right['module_id']]['create'] = $right['create'];
					$this->user_rights[$user_id][$right['module_id']]['read']   = $right['read'];
					$this->user_rights[$user_id][$right['module_id']]['update'] = $right['update'];
					$this->user_rights[$user_id][$right['module_id']]['delete'] = $right['delete'];
				}
			}
			
			if (is_array($this->module_rights[$user_id])) return;
			
			$this->module_rights = array();
			$rights = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."modules`", __LINE__);
			if (mysql_num_rows($rights)) {
				while ($right = mysql_fetch_assoc($rights)) {
					$this->module_rights[$right['id']]['create'] = $right['allow_create'];
					$this->module_rights[$right['id']]['update'] = $right['allow_update'];
					$this->module_rights[$right['id']]['delete'] = $right['allow_delete'];
					$this->module_rights[$right['id']]['read']   = 1; // ....
				}
			}
		}
		
		public function duplicate_entry($entry_id) {
			global $CMS_ENV;
			
			$entry_id = (int) $entry_id;
			
			$entry_id = Event::fire($this->module_id, 'preDuplicate', $entry_id);
			
			// 1:1 row
			// ================
			// entry row itself
			$fields = array();
			$columns = $this->mysql_query("SHOW COLUMNS FROM ".$this->table(), __LINE__);
			if (mysql_num_rows($columns)) {
				while ($column = mysql_fetch_assoc($columns)) {
					if ($column['Field'] != 'id') $fields[] = $column['Field'];
				}
			}
			$this->mysql_query("INSERT INTO `".$this->table()."` (`".implode("`, `", $fields)."`) SELECT `".implode("`, `", $fields)."` FROM `".$this->table()."` WHERE `id` = ".$entry_id, __LINE__);
			
			$new_entry_id = mysql_insert_id();
			
			if($this->multilingual_module()) {
				$fields = array();
				$columns = $this->mysql_query("SHOW COLUMNS FROM ".$this->table()."_ml", __LINE__);
				if (mysql_num_rows($columns)) {
					while ($column = mysql_fetch_assoc($columns)) {
						if ($column['Field'] != 'id' && $column['Field'] != 'entry_id') $fields[] = $column['Field'];
					}
				}
				$this->mysql_query("INSERT INTO `".$this->table()."_ml` (`entry_id`, `".implode("`, `", $fields)."`) SELECT '".$new_entry_id."', `".implode("`, `", $fields)."` FROM `".$this->table()."_ml` WHERE `entry_id` = ".$entry_id, __LINE__);
			}			
			
			// REFERENCED VALUES
			foreach ($this->fields() as $field)
			{
				// referenced values only
				if ($field['db_field'] != '#REF') continue;
				
				switch ($field['form_element'])
				{
					case 'image':
					case 'image_multi':
						
						// read images to duplicate
						$images = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						if (mysql_num_rows($images)) {
							while ($image = mysql_fetch_assoc($images)) {
								
								if (file_exists($CMS_ENV['root_path_uploads'].$image['file'])) {
								
									// find available filename
									$target = $this->find_free_filename($CMS_ENV['root_path_uploads'], $image['file']);
												
									if (copy($CMS_ENV['root_path_uploads'].$image['file'], $CMS_ENV['root_path_uploads'].$target)) {
										$this->leaveMessage("Copied '".$CMS_ENV['root_path_uploads'].$image['file']."' to '".$CMS_ENV['root_path_uploads'].$target."'");
										// duplicate in database
										$this->mysql_query("INSERT INTO `".$this->tables_prefix."m_images` (`entry_id`, `field_id`, `language_id`, `file`, `caption`, `extension`, `uploaded`, `position`) SELECT ".$new_entry_id." as entry_id, `field_id`, `language_id`, '".pxl_db_safe($target)."' as file, `caption`, `extension`, `uploaded`, `position` FROM `".$this->tables_prefix."m_images` WHERE `file` = '".pxl_db_safe($image['file'])."' AND `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
										$new_image_id = mysql_insert_id();
										
										// duplicate related thumbnails
										$thumbs = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_thumbs` WHERE `image_id` = ".$image['id'], __LINE__);
										if (mysql_num_rows($thumbs)) {
											while ($thumb = mysql_fetch_assoc($thumbs)) {
												
												if (file_exists($CMS_ENV['root_path_uploads'].$thumb['filename'])) {
													
													// find available filename
													$target = $this->find_free_filename($CMS_ENV['root_path_uploads'], $thumb['filename']);
													
													if (copy($CMS_ENV['root_path_uploads'].$thumb['filename'], $CMS_ENV['root_path_uploads'].$target)) {
														$this->leaveMessage("Copied '".$CMS_ENV['root_path_uploads'].$thumb['filename']."' to '".$CMS_ENV['root_path_uploads'].$target."'");
														// duplicate in database
														$this->mysql_query("INSERT INTO `".$this->tables_prefix."m_thumbs` (`image_id`, `resize_id`, `filename`) SELECT ".$new_image_id." as image_id, `resize_id`, '".pxl_db_safe($target)."' as filename FROM `".$this->tables_prefix."m_thumbs` WHERE `filename` = '".pxl_db_safe($thumb['filename'])."' AND `image_id` = ".$image['id'], __LINE__);
													}
												}
											}
										}
									}
								}
							}
						}
						
						break;
					
					case 'file':
						// read files to duplicate
						$files = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						if (mysql_num_rows($files)) {
							while ($file = mysql_fetch_assoc($files)) {
								if (file_exists($CMS_ENV['root_path_uploads'].$file['file'])) {
									// find available filename
									$target = $this->find_free_filename($CMS_ENV['root_path_uploads'], $file['file']);
									copy($CMS_ENV['root_path_uploads'].$file['file'], $CMS_ENV['root_path_uploads'].$target);
									
									// duplicate in database
									$this->mysql_query("INSERT INTO `".$this->tables_prefix."m_files` (`entry_id`, `field_id`, `file`, `extension`, `uploaded`, `position`) SELECT ".$new_entry_id." as entry_id, `field_id`, '".pxl_db_safe($target)."' as file, `extension`, `uploaded`, `position` FROM `".$this->tables_prefix."m_files` WHERE `file` = '".pxl_db_safe($file['file'])."' AND `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
								}
							}
						}
						break;
					
					case 'checkbox':
						$this->mysql_query("INSERT INTO `".$this->tables_prefix."m_checkboxes` (`entry_id`, `field_id`, `choice`) SELECT ".$new_entry_id." as entry_id, `field_id`, `choice` FROM `".$this->tables_prefix."m_checkboxes` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						break;
					
					case 'reference_multi':
						$this->mysql_query("INSERT INTO `".$this->tables_prefix."m_references` (`from_field_id`, `from_entry_id`, `to_entry_id`, `position`) SELECT `from_field_id`, ".$new_entry_id." as `from_entry_id`, `to_entry_id`, `position` FROM `".$this->tables_prefix."m_references` WHERE `from_field_id` = ".$field['id']." AND `from_entry_id` = ".$entry_id, __LINE__);
						break;
					
					default:
						$this->leaveError("CMS->duplicate_entry(): unknown referenced field '".$field['cms_name']."' => nothing duplicated here!");
						break;
				}
			}
			
			$new_entry_id = Event::fire($this->module_id, 'postDuplicate', $new_entry_id);
			return $new_entry_id;
		}
		
		private function find_free_filename($path, $file) {
			$fname = Tools::file_name($file);
			$fext  = Tools::file_extension($file);
			$target = $file;
			for ($i = 1; file_exists($path.$target); $i++) {
				$target = $fname."_".$i.".".$fext;
			}
			return $target;
		}
		
		public function loginUser($username, $password) {
			$u = pxl_db_safe($username);
			$p = md5($password);
			
			$user = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `username` = '".$u."' AND `enabled` = 1 AND `password` = '".$p."'", __LINE__);
			
			if (mysql_num_rows($user) == 0) {
				return false;
			} else {
				$userdata = mysql_fetch_assoc($user);
				$this->mysql_query("UPDATE `".$this->tables_prefix."users` SET `last_login` = ".time()." WHERE `id` = ".$userdata['id'], __LINE__);
				return $userdata;
			}
		}
		
		// addEntry()
		// adds another entry to this module, returns the entry_id of the newly added entry
		public function addEntry($category_id = null) {
			if ($this->module_id == null) return $this->leaveError("CMS->addEntry(): requires module_id to be set");
			
			if (!$this->mayAddEntry()) {
				return $this->leaveError("addEntry: no more entries allowed, maximum is ".$this->module_info['max_entries']);
			}
			
			$lastposition = mysql_fetch_row($this->mysql_query("SELECT max(`e_position`) FROM `".$this->table()."`", __LINE__));
			$this->mysql_query("INSERT INTO `".$this->table()."` ( `e_category_id`, `e_position`, `e_user_id` ) VALUES ( ".($category_id == null ? "NULL" : "'".$category_id."'").", '".($lastposition[0]+1)."', '".$_SESSION['userdata']['user_id']."' )", __LINE__);
			
			return mysql_insert_id();
		}
		
		// getImages()
		public function getImages($field_id, $entry_id) {
			foreach($this->fields() as $field) {
				if($field['id'] == $field_id) break;
			}
			
			$i = array();
			if($field['multilingual']) {
				$images = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field_id." AND `entry_id` = ".$entry_id." AND `language_id` = '".$this->language."' ORDER BY `position` ASC", __LINE__);
			} else {
				$images = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field_id." AND `entry_id` = ".$entry_id." AND `language_id` = 0 ORDER BY `position` ASC", __LINE__);
			}
			if (mysql_num_rows($images)) {
				while ($image = mysql_fetch_assoc($images)) {
					$i[] = $image;
				}
			}
			return $i;
		}
		
		public function getFiles($field_id, $entry_id) {
			$f = array();
			foreach ($this->fields() as $field) {
				if ($field['id'] == $field_id) break;
			}
			if ($field['multilingual']) {
				$files = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `field_id` = ".$field_id." AND `entry_id` = ".$entry_id." AND `language_id` = '".$this->language."' ORDER BY `position` ASC", __LINE__);
			} else {
				$files = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `field_id` = ".$field_id." AND `entry_id` = ".$entry_id." ORDER BY `position` ASC", __LINE__);
			}
			if (mysql_num_rows($files)) {
				while ($file = mysql_fetch_assoc($files)) {
					$f[] = $file;
				}
			}
			return $f;
		}
		
		// deleteImage()
		public function deleteImage($image_id) {
			global $CMS_ENV;
			if (!isset($CMS_ENV['root_path_uploads'])) return $this->leaveError("CMS->deleteImage(".$image_id."): the environment variables are not loaded");
			
			$image = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `id` = '".$image_id."'", __LINE__);
			if (mysql_num_rows($image)) {
				$image = mysql_fetch_assoc($image);
				$thumbs = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_thumbs` WHERE `image_id` = ".$image['id'], __LINE__);
				if (mysql_num_rows($thumbs)) {
					while ($thumb = mysql_fetch_assoc($thumbs)) {
						if (file_exists($CMS_ENV['root_path_uploads'].$thumb['filename'])) {
							unlink($CMS_ENV['root_path_uploads'].$thumb['filename']);
						}
					}
				}
				$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_thumbs` WHERE `image_id` = ".$image['id'], __LINE__);
				$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_images` WHERE `id` = ".$image['id'], __LINE__);
				if (file_exists($CMS_ENV['root_path_uploads'].$image['file'])) {
					unlink($CMS_ENV['root_path_uploads'].$image['file']);
				}
			} else {
				return $this->leaveError("CMS->deleteImage(): unknown image '".$image_id."'");
			}
		}
		
		public function deleteFile($file_id) {
			global $CMS_ENV;
			if (!isset($CMS_ENV['root_path_uploads'])) return $this->leaveError("CMS->deleteFile(".$file_id."): the environment variables are not loaded");
			
			$file = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `id` = '".$file_id."'", __LINE__);
			if (mysql_num_rows($file)) {
				$file = mysql_fetch_assoc($file);
				$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_files` WHERE `id` = ".$file['id'], __LINE__);
				if (file_exists($CMS_ENV['root_path_uploads'].$file['file'])) {
					unlink($CMS_ENV['root_path_uploads'].$file['file']);
				}
			} else {
				return $this->leaveError("CMS->deleteFile(): unknown file '".$file_id."'");
			}
		}
		
		public function row_count() {
			if ($this->module_id == null) return $this->leaveError("CMS->row_count(): requires module_id to be set");
			$r = mysql_fetch_row($this->mysql_query("SELECT count(id) FROM `".$this->table()."`", __LINE__));
			return $r[0];
		}
		
		// deleteCategory()
		public function deleteCategory($id) {
			if ($this->module_id == null) return $this->leaveError("CMS->deleteCategory(): requires module_id to be set");
			
			// 1st delete child-categories
			$categories = $this->mysql_query("SELECT `id` FROM `".$this->tables_prefix."categories` WHERE `id` != ".$id." AND `parent_category_id` = ".$id, __LINE__);
			if (mysql_num_rows($categories)) {
				while ($category = mysql_fetch_assoc($categories)) {
					$this->deleteCategory($category['id']);
					$this->leaveMessage('delete category '.$category['id']);
				}
			}
			
			// 2nd delete entries in this category
			$this->leaveMessage('Select and delete entries from this category:');
			$entries = $this->mysql_query("SELECT `id` FROM `".$this->table()."` WHERE `e_category_id` = ".$id, __LINE__);
			if (mysql_num_rows($entries)) {
				while ($entry = mysql_fetch_assoc($entries)) {
					$this->deleteEntry($entry['id']);
				}
			}
			
			// and delete this category itself
			$this->mysql_query("DELETE FROM `".$this->tables_prefix."categories` WHERE `id` = ".$id, __LINE__);
		}
		
		// deleteEntry()
		public function deleteEntry($entry_id) {
			global $CMS_ENV;
			if (!isset($CMS_ENV['root_path_uploads'])) return $this->leaveError("CMS->removeEntry(".$entry_id."): the environment variables are not loaded");
			
			$entry_id = Event::fire($this->module_id, 'preDelete', $entry_id);
			
			$this->fields();
			
			// delete referenced fields (including any physically related files)
			foreach ($this->module_fields as $field)
			{
				if ($field['db_field'] != '#REF') continue;
				
				switch ($field['form_element'])
				{
					case 'image':
					case 'image_multi':

						// read files to remove
						$remove = array();
						$thumbs = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_thumbs` WHERE `image_id` IN ( SELECT `id` FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id." )", __LINE__);
						if (mysql_num_rows($thumbs)) {
							while ($thumb = mysql_fetch_assoc($thumbs)) {
								$remove[] = $thumb['filename'];
							}
						}
						$images = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						if (mysql_num_rows($images)) {
							while ($image = mysql_fetch_assoc($images)) {
								$remove[] = $image['file'];
							}
						}
						
						// remove physical files
						foreach ($remove as $file) {
							if (file_exists($CMS_ENV['root_path_uploads'].$file)) {
								unlink($CMS_ENV['root_path_uploads'].$file);
							}
						}
						
						// remove from database
						$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_thumbs` WHERE `image_id` IN ( SELECT `id` FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id." )", __LINE__);
						$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_images` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						break;
					
					case 'file':
						// read files to remove
						$remove = array();
						$files = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						if (mysql_num_rows($files)) {
							while ($file = mysql_fetch_assoc($files)) {
								$remove[] = $file['file'];
							}
						}
						
						// remove physical files
						foreach ($remove as $file) {
							if (file_exists($CMS_ENV['root_path_uploads'].$file)) {
								unlink($CMS_ENV['root_path_uploads'].$file);
							}
						}
						
						// remove from database
						$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_files` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						break;
					
					case 'checkbox':
						$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_checkboxes` WHERE `field_id` = ".$field['id']." AND `entry_id` = ".$entry_id, __LINE__);
						break;
					
					case 'reference_multi':
						$this->leaveMessage("Removing ACTIVE references");
						$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_references` WHERE `from_entry_id` = ".$entry_id." AND `from_field_id` = ".$field['id'], __LINE__);
						break;
					
					default:
						$this->leaveError("CMS->deleteEntry(): unknown referenced field '".$field['cms_name']."' => nothing removed here: orphan alert!");
						break;
				}
			}
			
			$this->leaveMessage("Removing PASSIVE references");
			$passive_references = $this->mysql_query("SELECT `f`.*, `ft`.`form_element`, `m`.`id` AS module_id, `m`.`name` AS module_name FROM `".$this->tables_prefix."fields` f, `".$this->tables_prefix."field_types` ft, `".$this->tables_prefix."modules` m WHERE `m`.`id` = `f`.`module_id` AND `f`.`field_type_id` = `ft`.`id` AND `f`.`refers_to_module` = ".$this->module_id, __LINE__);
			if (mysql_num_rows($passive_references)) {
				while ($passive_reference = mysql_fetch_assoc($passive_references)) {
					switch ($passive_reference['form_element'])
					{
						case 'reference':
							$column = Tools::alphanumeric($passive_reference['name']);
							$this->leaveMessage("Removing passive 1:1-reference from `".$passive_reference['module_name']."`:");
							$this->mysql_query("UPDATE `".$this->table_name($passive_reference['module_id'], $passive_reference['module_name'])."` SET `".$column."` = 0 WHERE `".$column."` = ".$entry_id, __LINE__);
							break;
						
						case 'reference_multi':
							$this->leaveMessage("Removing passive 1:N-reference from `".$passive_reference['module_name']."`:");
							$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_references` WHERE `from_field_id` = ".$passive_reference['id']." AND `to_entry_id` = ".$entry_id, __LINE__);
							break;
					}
				}
			}
			
			// and delete the entry-row itself
			$this->mysql_query("DELETE FROM `".$this->table()."` WHERE `id` = ".$entry_id, __LINE__);
			if ($this->multilingual_module()) $this->mysql_query("DELETE FROM `".$this->table()."_ml` WHERE `entry_id` = ".$entry_id, __LINE__);
			
			Event::fire($this->module_id, 'postDelete', $entry_id);
		}
		
		// mayAddEntry()
		// tells if another entry may be added to this module: returns true/false
		public function mayAddEntry() {
			if ($this->module_id == null) return $this->leaveError("CMS->mayAddEntry(): requires module_id to be set");
			$entries_count = mysql_fetch_row($this->mysql_query("SELECT count(*) FROM `".$this->table()."`", __LINE__));
			return ($entries_count[0] < $this->module_info['max_entries'] || $this->module_info['max_entries'] == 0);
		}
		
		// determine module-table name
		// ---------------------------
		// separate function to easily change naming convention for module-tables
		public function table() {
			return $this->table_name($this->module_info['id'], $this->module_info['name']);
		}
		
		private function table_name($m_id, $m_name) {
			return $this->tables_prefix.'m'.$m_id.'_'.Tools::alphanumeric($m_name);
		}
		
		public function save_image_caption($image_id, $caption) {
			$this->mysql_query("UPDATE `".$this->tables_prefix."m_images` SET `caption` = '".pxl_db_safe($caption)."' WHERE `id` = ".$image_id, __LINE__);
		}
		
		public function saveEntry($id, $entry) {
			$updates    = array();
			$updates_ml = array();
			
			$updates['e_user_id'] = $entry['user_id'];
			unset($entry['user_id']);
			
			foreach ($entry as $field_id => $values) {
				$field = $this->fieldData($field_id);
				
				// skip unknown fields for now
				if ($field == null) {
					$this->leaveError('CMS->saveEntry(): unknown field_id: '.$field_id.' - skipping');
					continue;
				}
				
				// multilingual value?
				if ($field['multilingual'])
				{
					$updates_ml[$field['cms_name']] = $values[0];
				}
				// multilingual value?
				else
				{
					// treat refering field different
					if ($field['db_field'] == '#REF')
					{
						switch ($field['form_element'])
						{
							case 'reference_multi':
								
								$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_references` WHERE `from_entry_id` = '".$id."' AND `from_field_id` = '".$field['id']."'", __LINE__);
								$q = "INSERT INTO `".$this->tables_prefix."m_references` ( `from_field_id` , `from_entry_id` , `to_entry_id` , `position` ) VALUES ";
								$pairs = array();
								$position = 0;
								foreach ($values as $value) {
									if ($value) $pairs[] = "('".$field['id']."', '".$id."', '".$value."', '".($position++)."')";
								}
								if (sizeof($pairs)) {
									$q .= implode(', ', $pairs);
									$this->mysql_query($q, __LINE__);
								}
								
								if($field["field_type_id"] == 28) {
									
									$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_references` WHERE `to_entry_id` = '".$id."' AND `from_field_id` = '".$field['id']."'", __LINE__);
									$q = "INSERT INTO `".$this->tables_prefix."m_references` ( `from_field_id` , `from_entry_id` , `to_entry_id` , `position` ) VALUES ";
									$pairs = array();
									$position = 0;
									foreach ($values as $value) {
										if ($value) $pairs[] = "('".$field['id']."', '".$value."', '".$id."', '".($position++)."')";
									}
									if (sizeof($pairs)) {
										$q .= implode(', ', $pairs);
										$this->mysql_query($q, __LINE__);
									}
								}
								
								break;
							
							case 'checkbox':
								$this->mysql_query("DELETE FROM `".$this->tables_prefix."m_checkboxes` WHERE `entry_id` = '".$id."' AND `field_id` = '".$field['id']."'", __LINE__);
								$q = "INSERT INTO `".$this->tables_prefix."m_checkboxes` ( `entry_id` , `field_id` , `choice` ) VALUES "; 
								$pairs = array();
								foreach ($values as $value) {
									$pairs[] = "('".$id."', '".$field['id']."', '".pxl_db_safe($value)."')";
								}
								$q .= implode(', ', $pairs);
								$this->mysql_query($q, __LINE__);
								break;
						}
					}
					else if ($field['multi_value'] == 0)
					{
						// prevent FCK-saves-null-value bug
						if ($field['form_element'] == 'htmltext_fck' && $values[0] == 'null') {
							// do not save this; don't add to updates array
						} else {
							// other fields... save; add to updates array
							$updates[$field['cms_name']] = $values[0]; 
						}
					}
					else
					{
						// multi-value but not a referred??
						// we are not using this
						$this->leaveError('CMS->saveEntry(): `'.$field['name'].'` is multi-value but still stored as '.$field['db_field'].' ?');
					}
				}
			}
			
			global $run_insert_events;
			$updates['id'] = $id;
			$updates = Event::fire($this->module_id, 'preSave', array_merge($updates, $updates_ml));
			$updates = Event::fire($this->module_id, ($run_insert_events ? 'preInsert' : 'preUpdate'), $updates);

			foreach ($updates_ml as $ml_key => $ml_value) {
				if (isset($updates[$ml_key])) {
					$updates_ml[$ml_key] = $updates[$ml_key];
					unset($updates[$ml_key]);
				}
			}

			// if we need to update our record on 1 or more fields, run update query
			if (count($updates)) {
				$this->mysql_query("UPDATE `".$this->table()."` SET ".Tools::mysql_update($updates)." WHERE `id` = ".$id, __LINE__);
			}
			
			// if we need to update our record on 1 or more fields, run update query
			if (count($updates_ml)) {
				$exist = $this->mysql_query("SELECT * FROM `".$this->table()."_ml` WHERE `entry_id` = ".$id." AND `language_id` = ".$this->language(), __LINE__);
				if (mysql_num_rows($exist) == 0) {
					$this->mysql_query("INSERT INTO `".$this->table()."_ml` ( `entry_id`, `language_id` ) VALUES ( ".$id.", ".$this->language().")", __LINE__);
				}
				$this->mysql_query("UPDATE `".$this->table()."_ml` SET ".Tools::mysql_update($updates_ml)." WHERE `entry_id` = ".$id." AND `language_id` = ".$this->language(), __LINE__);
			}
			
			Event::fire($this->module_id, ($run_insert_events ? 'postInsert' : 'postUpdate'), $id);
			Event::fire($this->module_id, 'postSave', $id);
		}
		
		public function getEntries($eids = null) {
			$eids = Event::fire($this->module_id, 'preSelectMultiple', $eids);
			$e = $this->_getEntries((array)$eids);
			$e = Event::fire($this->module_id, 'postSelectMultiple', $e);
			return $e;
		}
		
		public function getEntry($id) {
			$id = Event::fire($this->module_id, 'preSelectSingle', $id);
			$e = $this->_getEntries(array($id));
			$e = Event::fire($this->module_id, 'postSelectSingle', $e);
			return $e;
		}
		
		public function setConditions($sql) {
			$this->conditions = $sql;
		}
		
		private function _getEntries($eid = null) {
			global $CMS_ENV;
			
			if ($this->module_id == null) return $this->leaveError("CMS->getEntries(): requires module_id to be set");
			
			$this->fields();
			$this->leaveMessage('CMS->getEntries()');
			$e = array();
			$conditions = array();
			
			// QUERY FORMATTING
			// - select
			if ($this->multilingual_module()) {
				$selector = "SELECT `d`.*, `ml`.`".implode("`, `ml`.`", $this->ml_fields())."`";
				$query = "FROM `".$this->table()."` d LEFT JOIN `".$this->table()."_ml` ml ON `d`.`id` = `ml`.`entry_id` AND `ml`.`language_id` = ".$this->language().' ';
			} else {
				$selector = "SELECT *";
				$query = "FROM `".$this->table()."` d ";
			}
			
			// CONDITIONS
			if ($this->active_entries_only) $conditions[] = "`d`.`e_active` = 1";
			if ($eid != null) {
				if (is_array(array_peek($eid))) {
					//rare case were we're getting a full entry instead of an array of id's
					$eid = array_keys($eid);
				}
				$conditions[] = "`d`.`id` IN (".implode(', ', array_unique($eid)).")";
			}
			else {
				// - category
				if ($this->category_id != -1) {
					if ($this->category_id == null) {
						$conditions[] = "`d`.`e_category_id` IS NULL";
					} else {
						$conditions[] = "`d`.`e_category_id` = ".$this->category_id;
					}
				}
			}
			
			// - where
			if (count($conditions) || $this->conditions != null)
			{
				$query  .= "WHERE ";
				if (count($conditions)) $query .= "(".implode(" AND ", $conditions).") ";
				if ($this->conditions != null) {
					if (count($conditions)) $query .= ' AND ';
					$query .= $this->conditions.' ';
				}
			}
			
			$query = str_replace(" `id`", " `d`.`id`", $query);
			
			// DETERMINE SORTING
			if ($this->sorting != '') {
				if (Tools::string_starts(strtolower($this->sorting), 'ORDER BY')) {
					$sort_query = $this->sorting;
				} else {
					$sort_query = 'ORDER BY '.$this->sorting;
				}
			} else {
				if ($this->module_info['sort_entries_manually']) {
					$sort_query = 'ORDER BY `d`.`e_position` ASC';
				} else if ($this->module_info['sort_entries_by'] != '') {
					$sort_query = 'ORDER BY '.$this->module_info['sort_entries_by'];
				}
			}
			$sort_query = str_replace(" `id`", " `d`.`id`", $sort_query);
			
			// - order by
			$query  .= $sort_query.' '; // to allow possible caching in mysql for identical queries, we put this BEFORE the totalcount query!
			
			
			// if find_total_count is enabled, first count rows
			if ($this->find_total_count) {
				$this->total_count = mysql_fetch_assoc($this->mysql_query("SELECT count(*) as aantal ".$query, __LINE__));
				$this->total_count = $this->total_count['aantal'];
			}
			
			// - limit
			if ($this->limit_sql != null) $query .= $this->limit_sql;
			// execute!
			$entries = $this->mysql_query($selector.' '.$query, __LINE__);
			
			// READING & PREPROCESSING RESULT
			if ($entries !== false) {
				if (mysql_num_rows($entries)) {
					while ($entry = mysql_fetch_assoc($entries)) {
						// instantiate refered values
						foreach ($this->module_fields as $field) {
							// we only process reference fields here
							if ($field['db_field'] == '#REF') {
								// instantiate refered values
								$entry[$field['cms_name']] = array();
							}
						}
						$e[$entry['id']] = $entry;
					}
				}
			}

			// IF WE HAVE NO RESULTS, RETURN HERE
			if (sizeof($e) == 0) return $e;
			

			// ADD REFERENCED VALUES
			$eids = array_keys($e);
			// first preload referenced entry ID's from other modules
			$field_references  = array();
			$module_references = array();
			foreach ($this->module_fields as $field) {
				// only process reference fields
				if ($field['form_element'] != 'reference' && $field['form_element'] != 'reference_multi') continue;
				// if selected references indicated, only load if in selection
				if (count($this->references) && !in_array($field['refers_to_module'], $this->references)) continue;
				
				$this->leaveMessage('Pre-processing reference: `'.$field['cms_name'].'` ('.$field['id'].')');
				if ($field['form_element'] == 'reference') {
					$ref_ids = "SELECT `id` AS from_entry_id, `".$field['cms_name']."` AS to_entry_id FROM `".$this->table()."` WHERE `id` IN (".implode(', ', $eids).")";
				} else {
					$ref_ids = "SELECT `from_entry_id`, `to_entry_id` FROM `".$this->tables_prefix."m_references` WHERE `from_entry_id` IN (".implode(', ', $eids).") AND `from_field_id` = ".$field['id']." ORDER BY `position` ASC";
				}
				
				$field_references[$field['id']] = $ref_ids;
				// preload the referencing entry_id's from foreign modules
				$ref_ids = $this->mysql_query($ref_ids, __LINE__);
				if (mysql_num_rows($ref_ids)) {
					while ($ref_id = mysql_fetch_assoc($ref_ids)) {
						if(!empty($ref_id['to_entry_id'])) {
							$module_references[$field['module_id']][] = $ref_id['to_entry_id'];
						}
					}
				}
			}
			
			foreach ($this->module_fields as $field)
			{
				switch ($field['form_element'])
				{
					case 'image':
					case 'image_multi':
						// skip?
						if (!$this->load_files) break;
						
						$this->leaveMessage('Loading referenced images `'.$field['cms_name'].'` ('.$field['id'].')');
						
						if($field['multilingual']) {
							$images = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `entry_id` IN (".implode(', ', $eids).") AND `field_id` = ".$field['id']." AND `language_id` = ".$this->language()." ORDER BY `position`", __LINE__);
						} else {
							$images = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_images` WHERE `entry_id` IN (".implode(', ', $eids).") AND `field_id` = ".$field['id']." AND `language_id` = 0 ORDER BY `position`", __LINE__);
						}
						if (mysql_num_rows($images)) {
							while ($image = mysql_fetch_assoc($images)) {
								if (in_array($image['entry_id'], $eids)) $e[$image['entry_id']][$field['cms_name']][$image['id']] = array('file' => $image['file'], 'caption' => $image['caption']);
							}
						}
						break;
					
					case 'file':
						// skip?
						if (!$this->load_files) break;
						
						$this->leaveMessage('Loading referenced files `'.$field['cms_name'].'` ('.$field['id'].')');
						
						if($field['multilingual']) {
							$files = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `entry_id` IN (".implode(', ', $eids).") AND `field_id` = ".$field['id']." AND `language_id` = ".$this->language()." ORDER BY `position`", __LINE__);
						} else {
							$files = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_files` WHERE `entry_id` IN (".implode(', ', $eids).") AND `field_id` = ".$field['id']." AND `language_id` = 0 ORDER BY `position`", __LINE__);
						}
						if (mysql_num_rows($files)) {
							while ($file = mysql_fetch_assoc($files)) {
								if (in_array($file['entry_id'], $eids)) $e[$file['entry_id']][$field['cms_name']][] = $file['file'];
							}
						}
						break;
					
					case 'checkbox':
						$this->leaveMessage('Loading referenced choices `'.$field['cms_name'].'` ('.$field['id'].')');
						$choices = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."m_checkboxes` WHERE `entry_id` IN (".implode(', ', $eids).") AND `field_id` = ".$field['id'], __LINE__);
						if (mysql_num_rows($choices)) {
							while ($choice = mysql_fetch_assoc($choices)) {
								if (in_array($choice['entry_id'], $eids)) $e[$choice['entry_id']][$field['cms_name']][] = $choice['choice'];
							}
						}
						break;
					
					case 'reference':
					case 'reference_multi':
						// if selected references indicated, only load if in selection
						if (count($this->references) && !in_array($field['refers_to_module'], $this->references)) break;
						
						$this->leaveMessage('Loading referenced field `'.$field['cms_name'].'` ('.$field['id'].')');
						$ref_ids = $this->mysql_query($field_references[$field['id']], __LINE__);
						// only fetch referenced entries when the active recursive level is 1 or more
						if ($this->recursive >= 1)
						{
							if (mysql_num_rows($ref_ids))
							{
								if (!isset($this->references_to_module[$field['refers_to_module']]))
								{
									
									if(empty($module_references[$field['module_id']])) {
										$this->references_to_module[$field['refers_to_module']] = array();
									} else {
										$REF_CMS = new CMS();
										$REF_CMS->setModule($field['refers_to_module']);
										$REF_CMS->load_passive_references = $this->load_passive_references;
										$REF_CMS->active_entries_only     = false; // include all referred entries, even though they may be de-actived (...right?)
										$REF_CMS->recursive               = $this->recursive - 1;
										$REF_CMS->references              = $this->references;
										$REF_CMS->load_files              = $this->load_files;
										$REF_CMS->language                = $this->language;
										$REF_CMS->generate_identifier     = $this->generate_identifier;
										$this->references_to_module[$field['refers_to_module']] = $REF_CMS->getEntries($module_references[$field['module_id']]);
										// integrate logs
										$REF_CMS->titleLog('Loading referenced entries from field `'.$field['name'].'` &raquo; module_id '.$field['refers_to_module'].' [active recursivity level = '.$this->recursive.']');
										$this->logger->merge($REF_CMS->logger);
										// -
									}
								}
								else
								{
									$this->leaveMessage('Reference to previously loaded module '.$field['refers_to_module'].' (was cached in memory)');
								}
								$refs = $this->references_to_module[$field['refers_to_module']];
								
								$ref_entries = array();
								if (mysql_num_rows($ref_ids)) {
									while ($ref = mysql_fetch_assoc($ref_ids))
									{
										
										if (in_array($ref['from_entry_id'], $eids)) {
											if (!is_array($e[$ref['from_entry_id']][$field['cms_name']])) $e[$ref['from_entry_id']][$field['cms_name']] = array();
											if($field["field_type_id"] != 26) {
												$e[$ref['from_entry_id']][$field['cms_name']][$ref['to_entry_id']] = $refs[$ref['to_entry_id']];
											}
											$ref_entries[$ref['to_entry_id']][] = $ref['from_entry_id'];
										}
									}
								}

								if($field["field_type_id"] == 26) {
									foreach($refs as $ref) {
										if(isset($ref_entries[$ref["id"]])) {
											foreach($ref_entries[$ref["id"]] as $r) {
												$e[$r][$field['cms_name']][$ref["id"]] = $ref;
											}
										}
									}
								}
								
							}
							else
							{
								$this->leaveMessage('Skipping reference loading, no references found');
							}
						} else {
							$this->leaveMessage("Skipping reference loading: recursivity = 0, providing ID's");
							while ($ref = mysql_fetch_assoc($ref_ids))
							{
								if (in_array($ref['from_entry_id'], $eids)) {
									if (!is_array($e[$ref['from_entry_id']][$field['cms_name']])) $e[$ref['from_entry_id']][$field['cms_name']] = array();
									$e[$ref['from_entry_id']][$field['cms_name']][$ref['to_entry_id']] = $ref['to_entry_id'];
								}
							}
						}
						break;
				}
			}
			
			// ADD PASSIVELY REFERENCED VALUES
			if ($this->load_passive_references || (is_array($this->load_passive_references) && count($this->load_passive_references)))
			{
				if ($this->recursive >= 1)
				{
					$this->leaveMessage('Identifying passive references');
					$passive_references = array();
					$passive_references_q = $this->mysql_query("SELECT `f`.*, `ft`.`form_element`, `m`.`name` AS module_name, `m`.`sort_entries_manually`, `m`.`sort_entries_by` FROM `".$this->tables_prefix."fields` f, `".$this->tables_prefix."field_types` ft, `".$this->tables_prefix."modules` m WHERE `m`.`id` = `f`.`module_id` AND ".(is_array($this->load_passive_references) ? "`m`.`id` IN (".implode(',', $this->load_passive_references).") AND " : "")." `f`.`field_type_id` = `ft`.`id` AND `f`.`refers_to_module` = ".$this->module_id, __LINE__);
					
					$field_references  = array();
					$module_references = array();
					
					// FIRST LOOP
					// - cache all passive referencing fields
					// - preload the referencing entry_id's from foreign modules
					if (mysql_num_rows($passive_references_q)) {
						while ($passive_reference = mysql_fetch_assoc($passive_references_q)) {
							$passive_references[] = $passive_reference;
							
							$this->leaveMessage("Pre-processing passive reference: `".$passive_reference['module_name']."`.`".$passive_reference['name']."` (".$passive_reference['id'].")");
							
							// - determine reference type (1:1 / 1:N)
							// - find refering & referenced entry_id's (1:1 -> through module_table / 1:N -> m_references)
							switch ($passive_reference['form_element'])
							{
								case 'reference':
									$ref_ids = "SELECT `id` AS from_entry_id, `".Tools::alphanumeric($passive_reference['name'])."` AS to_entry_id FROM `".$this->table_name($passive_reference['module_id'], $passive_reference['module_name'])."` WHERE `".Tools::alphanumeric($passive_reference['name'])."` IN (".implode(', ', $eids).") ORDER BY ".($passive_reference['sort_entries_manually'] ? "`e_position` ASC" : $passive_reference['sort_entries_by']);
									break;
								
								case 'reference_multi':
									$ref_ids = "SELECT `from_entry_id`, `to_entry_id` FROM `".$this->tables_prefix."m_references` r, `".$this->table_name($passive_reference['module_id'], $passive_reference['module_name'])."` m WHERE `r`.`to_entry_id` IN (".implode(', ', $eids).") AND `r`.`from_field_id` = ".$passive_reference['id']." AND `r`.`from_entry_id` = `m`.`id` ORDER BY ".($passive_reference['sort_entries_manually'] ? "`e_position` ASC" : $passive_reference['sort_entries_by']);
									break;
							}
							
							// save SQL
							$field_references[$passive_reference['id']] = $ref_ids;
							
							// preload the referencing entry_id's from foreign modules
							$ref_ids = $this->mysql_query($ref_ids, __LINE__);
							if (mysql_num_rows($ref_ids)) {
								while ($ref_id = mysql_fetch_assoc($ref_ids)) {
									$module_references[$passive_reference['module_id']][] = $ref_id['from_entry_id'];
								}
							}
						}
					}
					
					// SECOND LOOP
					// - load and cache refering entries from refering module
					// - map refering entries onto referenced entries
					foreach ($passive_references as $passive_reference) {
						
						$ref_ids = $this->mysql_query($field_references[$passive_reference['id']], __LINE__);
						if (mysql_num_rows($ref_ids))
						{
							if (!isset($this->references_from_module[$passive_reference['module_id']]))
							{
								$REF_CMS = new CMS();
								$REF_CMS->setModule($passive_reference['module_id']);
								$REF_CMS->load_passive_references = $this->load_passive_references;
								$REF_CMS->active_entries_only     = true; // skip inactive passive references
								$REF_CMS->language                = $this->language;
								$REF_CMS->recursive               = $this->recursive - 1;
								$REF_CMS->references              = $this->references;
								$REF_CMS->load_files              = $this->load_files;
								$REF_CMS->generate_identifier     = $this->generate_identifier;
								if (count($module_references[$passive_reference['module_id']])) {
									$this->references_from_module[$passive_reference['module_id']] = $REF_CMS->getEntries($module_references[$passive_reference['module_id']]);
								} else {
									$this->references_from_module[$passive_reference['module_id']] = array();
								}
								// integrate logs
								$REF_CMS->titleLog('Preloading passively referenced entries from `'.$passive_reference['module_name'].'`: '.implode(',', $module_references[$passive_reference['module_id']]).' [active recursivity level = '.$this->recursive.']');
								$this->logger->merge($REF_CMS->logger);
								// -
							}
							else
							{
								$this->leaveMessage('Reference to previously loaded module '.$passive_reference['module_id'].' (was cached in memory)');
							}
							$refs = $this->references_from_module[$passive_reference['module_id']];
							if (mysql_num_rows($ref_ids)) {
								while ($ref = mysql_fetch_assoc($ref_ids))
								{
									// skip entries that were not loaded (occurs when deactivated)
									if (!isset($refs[$ref['from_entry_id']])) continue;
									
									if (in_array($ref['to_entry_id'], $eids)) {
										if (!is_array($e[$ref['to_entry_id']]['_referenced'])) $e[$ref['to_entry_id']]['_referenced'] = array();
										if (!is_array($e[$ref['to_entry_id']]['_referenced'][$passive_reference['module_id']])) $e[$ref['to_entry_id']]['_referenced'][$passive_reference['module_id']] = array();
										$e[$ref['to_entry_id']]['_referenced'][$passive_reference['module_id']][$ref['from_entry_id']] = $refs[$ref['from_entry_id']];
									}
								}
							}
						}
						else
						{
							$this->leaveMessage('Skipping passive reference loading, no references found');
						}
					}
				}
				else
				{
					$this->leaveMessage('Skipping passive reference loading: recursivity = 0');
				}
			}
			if ($this->generate_identifier) {
				foreach ($e as &$entry) {
					$entry['_identifier'] = $this->generateIdentifier($entry);
				}
			}
			return $e;
		}
		
		protected function generateIdentifier($entry) {
			$identifier = $entry['id'];
			foreach ($this->module_fields as $field) {
				if ($field['identifier']) {
					if ($field['form_element'] == 'time') {
						$identifier .= ' - '.date('d-m-Y @ H:i:s', $entry[$field['cms_name']]);
					} else if ($field['form_element'] == 'date') {
						$identifier .= ' - '.date('d-m-Y', $entry[$field['cms_name']]);
					} else if ($field['form_element'] == 'image' || $field['form_element'] == 'multi_image') {
						$images = $this->mysql_query("SELECT `file` FROM ".$this->tables_prefix."m_images WHERE `id` = ".$entry["id"], __LINE__);
						$values = array();
						if (mysql_num_rows($images)) {
							while($image = mysql_fetch_assoc($images)) {
								$values[] = " <img height='14' src='".$CMS_ENV['base_url_uploads']."pxl20_".$image['file']."' alt='' title='' ".($entry['e_active'] ? '' : "class='transparent25'")." />";
							}
						}
						$identifier .=  implode(" ", $values);
					} elseif ($field['refers_to_module'] > 0 && !empty($entry[$field['cms_name']])) {
						$REF_CMS = new CMS();
						$REF_CMS->setModule($field['refers_to_module']);
						$REF_CMS->active_entries_only     = false; // include all referred entries, even though they may be de-actived (...right?)
						$REF_CMS->language                = $this->language;
						$REF_CMS->generate_identifier     = true;
						$REF_CMS->recursive					 = $this->recursive - 1;
						$ref = $REF_CMS->getEntries($entry[$field['cms_name']]);
						foreach ($ref as $r) {
							$identifier .= ' - '.$r['_identifier'];
						}
					} else {
						$identifier .= ' - '.$entry[$field['cms_name']];
					}
				}
			}
			if (substr($identifier, 0, strlen($entry['id'].' - ')) == $entry['id'].' - ') {
				$identifier = substr($identifier, strlen($entry['id'].' - '));
			}
			return $identifier;
		}
		
		public function setCategory($id) {
			$this->category_id = $id;
		}
		
		public function getCategory($id) {
			if ($id == null) return $this->leaveError("CMS->getCategory(): invalid category ID (NULL)");
			$category = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."categories` WHERE `id` = ".$id, __LINE__);
			return mysql_fetch_assoc($category);
		}
		
		public function getCategories() {
			if ($this->category_id == -1) return $this->leaveError("CMS->getCategories(): requires category_ID to be set through setCategory()");

			$c = array();
			$stats = $this->_getCategoryTreeStatistics();
			
			if ($this->category_id == null) {
				$categories = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."categories` WHERE `module_id` = ".$this->module_info['id']." AND `depth` = 1 ORDER BY `position`", __LINE__);
			} else {
				$categories = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."categories` WHERE `module_id` = ".$this->module_info['id']." AND `parent_category_id` = ".$this->category_id." AND `id` != ".$this->category_id." ORDER BY `position`", __LINE__);
			}
			if (mysql_num_rows($categories)) {
				while ($category = mysql_fetch_assoc($categories)) {
					$category['statistics'] = $stats[$category['id']];
					$c[$category['id']] = $category;
				}
			}
			return $c;
		}
		
		private function _getCategoryTreeStatistics() {
			$c = array();
			// traverse categories bottom-up (= order by depth desc) to allow cumulative statistics generation in 1 run
			$categories = $this->mysql_query("SELECT `c`.*, count(`e`.`id`) as `entries` FROM `".$this->tables_prefix."categories` c LEFT JOIN `".$this->table()."` e ON `c`.`id` = `e`.`e_category_id` WHERE `module_id` = ".$this->module_info['id']." GROUP BY `c`.`id` ORDER BY `depth` DESC", __LINE__);
			if (mysql_num_rows($categories)) {
				while ($category = mysql_fetch_assoc($categories)) {
					if (!isset($c[$category['id']])) $c[$category['id']] = array('entries' => 0, 'categories' => 0);
					$c[$category['id']]['entries'] += $category['entries'];
					if ($category['parent_category_id'] != $category['id']) {
						$c[$category['parent_category_id']]['categories']++; // myself
						$c[$category['parent_category_id']]['categories'] += $c[$category['id']]['categories']; // my children
						$c[$category['parent_category_id']]['entries'] += $c[$category['id']]['entries']; // my children
					}
				}
			}
			return $c;
		}
		
		public function addCategory($parent_category_id, $data) {
			$data['module_id'] = $this->getModule();
			if ($parent_category_id == null) {
				list($keys, $values) = Tools::keys_values($data);
				$this->mysql_query("INSERT INTO `".$this->tables_prefix."categories` (".$keys.") VALUES (".$values.")", __LINE__);
				$id = mysql_insert_id();
				$this->mysql_query("UPDATE `".$this->tables_prefix."categories` SET `parent_category_id` = `id`, `depth` = 1 WHERE `id` = ".$id, __LINE__);
				return $id;
			} else {
				$parent = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."categories` WHERE `id` = ".$parent_category_id, __LINE__);
				if (mysql_num_rows($parent)) {
					$parent = mysql_fetch_assoc($parent);
					$data['depth'] = $parent['depth'] + 1;
					$data['parent_category_id'] = $parent_category_id;
					list($keys, $values) = Tools::keys_values($data);
					$this->mysql_query("INSERT INTO `".$this->tables_prefix."categories` (".$keys.") VALUES (".$values.")", __LINE__);
					$id = mysql_insert_id();
					return $id;
				} else {
					return $this->leaveError('CMS->addCategory(): invalid parent category id ('.$parent_category_id.')');
				}
			}
		}
		
		public function updateCategory($id, $data) {
			$this->mysql_query("UPDATE `".$this->tables_prefix."categories` SET ".Tools::mysql_update($data)." WHERE `id` = ".$id, __LINE__);
		}
		
		public function getStructure() {
			$g = array();
			$groups = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."groups` ORDER BY `position`", __LINE__);
			if (mysql_num_rows($groups)) {
				while ($group = mysql_fetch_assoc($groups)) {
					$group['sections'] = array();
					$sections = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."sections` WHERE `group_id` = '".$group['id']."' ORDER BY `position`", __LINE__);
					while ($section = mysql_fetch_assoc($sections)) {
						$group['sections'][] = $section;
					}
					$g[] = $group;
				}
			}
			return $g;
		}
		
		public function getUsers($user_id = null) {
			$u = array();
			/* check for null or 0, since superadmin has userid 0 */
			$users = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `enabled` = 1".((is_null($user_id) || $user_id == 0) ? '' : " AND `created_by` = ".$user_id), __LINE__);
			if (mysql_num_rows($users)) {
				while ($user = mysql_fetch_assoc($users)) {
					$this->readRights($user['id']);
					$user['rights'] = $this->user_rights[$user['id']];
					$u[$user['id']] = $user;
				}
			}
			return $u;
		}
		
		public function getUser($user_id) {
			return mysql_fetch_assoc($this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `id` = ".$user_id, __LINE__));
		}
		
		public function saveUser($manager_id, $userdata) {
			$editable = array('username', 'fullname', 'email', 'password', 'user_manager', 'ref_filter_module_id', 'ref_filter_entry_id');
			
			$user = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE (`created_by` = ".$manager_id." OR ".$manager_id." = 0 ) AND `id` = ".$userdata['id'], __LINE__);
			if (mysql_num_rows($user)) {
				$data = array();
				foreach ($editable as $e) {
					if (isset($userdata[$e])) {
						if ($e == 'password') {
							$userdata[$e] = md5($userdata[$e]);
						}
						$data[$e] = $userdata[$e];
					}
				}
				$this->mysql_query("UPDATE `".$this->tables_prefix."users` SET ".Tools::mysql_update($data)." WHERE `id` = ".$userdata['id'], __LINE__);
			}
		}
		
		public function resetPassword($email) {
			$user = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `enabled` = 1 AND `email` = '".pxl_db_safe($email)."'", __LINE__);
			if (mysql_num_rows($user)) {
				$user = mysql_fetch_assoc($user);
				$new_password = substr(md5(Tools::microtime()), 0, 8);
				$this->mysql_query("UPDATE `".$this->tables_prefix."users` SET `password` = '".md5($new_password)."' WHERE `id` = ".$user['id'], __LINE__);
				return array('new_password' => $new_password, 'user' => $this->getUser($user['id']));
			} else {
				return false;
			}
		}
		
		public function deleteUser($manager, $user_id) {
			$user = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."users` WHERE `created_by` = ".$manager." AND `id` = ".$user_id, __LINE__);
			if (mysql_num_rows($user)) {
				$this->mysql_query("DELETE FROM `".$this->tables_prefix."user_rights` WHERE `user_id` = ".$user_id, __LINE__);
				$this->mysql_query("UPDATE `".$this->tables_prefix."users` SET `enabled` = 0 WHERE `id` = ".$user_id, __LINE__);
			}
		}
		
		public function createUser($manager, $userdata) {
			$editable = array('username', 'fullname', 'email', 'password');
			
			$data = array('created_by' => $manager);
			foreach ($editable as $e) {
				if ($e == 'password') {
					$userdata[$e] = md5($userdata[$e]);
				}
				$data[$e] = $userdata[$e];
			}
			
			list($keys, $values) = Tools::keys_values($data);
			$this->mysql_query("INSERT INTO `".$this->tables_prefix."users` (".$keys.") VALUES (".$values.")", __LINE__);
		}
		
		private function getChoices($field_id) {
			// this function returns an array of all choices available for field_id
			$choices = $this->mysql_query("SELECT `choice` FROM `".$this->tables_prefix."field_options_choices` WHERE `field_id` = '".$field_id."' ORDER BY `position`, `choice`", __LINE__);
			$return_choices = array();
			if (mysql_num_rows($choices)) {
				while ($choice = mysql_fetch_assoc($choices)) {
					array_push($return_choices, $choice['choice']);
				}
			}
			return $return_choices;
		}
		
		public function getResizes($field_id) {
			// this function returns an array of all resize information for field_id
			$resizes = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."field_options_resizes` WHERE `field_id` = '".$field_id."'", __LINE__);
			$return_resizes = array();
			if (mysql_num_rows($resizes)) {
				while ($resize = mysql_fetch_assoc($resizes)) {
					$resize['resize_id'] = $resize['id'];
					$return_resizes[] = $resize;
				}
			}
			return $return_resizes;
		}
		
		public function getCustomModules() {
			$m = array();
			$modules = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."modules` WHERE `is_custom` = 1", __LINE__);
			if (mysql_num_rows($modules)) {
				while ($module = mysql_fetch_assoc($modules)) {
					$m[] = $module;
				}
			}
			return $m;
		}
		
		public function getModules($section_id) {
			$m = array();
			$modules = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."modules` WHERE `section_id` = ".((int) $section_id)." ORDER BY `position`", __LINE__);
			if (mysql_num_rows($modules)) {
				while ($module = mysql_fetch_assoc($modules)) {
					$m[] = $module;
				}
			}
			return $m;
		}
		
		public function getModule() {
			return $this->module_id;
		}
		
		public function setModule($mid) {
			$mid = (int) $mid;
			if (!$mid) return $this->leaveError('No valid module ID');
			if ($mid == $this->module_id) return $this->leaveMessage('Module ID ('.$mid.') already set.');
			
			$this->module_id     = $mid;
			$this->module_fields = null;
			$m = $this->mysql_query("SELECT * FROM `".$this->tables_prefix."modules` WHERE `id` = ".((int)$mid), __LINE__);
		 	if (mysql_num_rows($m) == 0) {
				$this->leaveError('Non-existing module: '.$mid);
				return false;
			}
			$this->module_info   = mysql_fetch_assoc($m);
		}
		
		private function _loadFields() {
			if ($this->module_id == null) return $this->leaveError("CMS->_loadFields(): requires module_id to be set");
			$fields = $this->mysql_query("SELECT `ft`.*, `f`.* FROM `".$this->tables_prefix."fields` f, `".$this->tables_prefix."field_types` ft WHERE `f`.`module_id` = '".$this->module_id."' AND `f`.`field_type_id` = `ft`.`id` ORDER BY `f`.`position`", __LINE__);
			$this->module_fields = array();
			for ($i = 0; $field = mysql_fetch_assoc($fields); $i++) {
				$field['index'] = $i;
				$field['cms_name'] = Tools::alphanumeric($field['name']);
				array_push($this->module_fields, $field);
			}
		}
		
		public function fields() {
			if ($this->module_id == null) return $this->leaveError("CMS->fields(): requires module_id to be set");
			if (is_array($this->module_fields)) {
				return $this->module_fields;
			} else {
				$this->_loadFields();
				return $this->module_fields;
			}
		}
		
		public function fieldData($fid) {
			if ($this->module_fields == null) $this->_loadFields();
			foreach ($this->module_fields as $field) {
				if ($field['id'] == $fid) return $field;
			}
			return null;
		}
		
		public function move_entry_to_category($eid, $cid) {
			if ($this->module_id == null) return $this->leaveError("CMS->move_entry_to_category(): requires module_id to be set");
			$eid = (int) $eid;
			$cid = ($cid == '' || $cid == null) ? 'NULL' : (int) $cid;
			$this->mysql_query("UPDATE `".$this->table()."` SET `e_category_id` = ".$cid." WHERE `id` = ".$eid, __LINE__);
		}
		
		public function move_category_to_category($eid, $cid) {
			if ($this->module_id == null) return $this->leaveError("CMS->move_category_to_category(): requires module_id to be set");
			$eid = (int) $eid;
			$cid = ($cid == '' || $cid == null) ? 'NULL' : (int) $cid;
			$cat = mysql_fetch_assoc($this->mysql_query("SELECT `depth` FROM `".$this->tables_prefix."categories` WHERE `id` = ".$cid, __LINE__));
			if($cid == 0) $cid = $eid;
			if($this->module_info["max_cat_depth"] > $cat["depth"]) {
				$this->mysql_query("UPDATE `".$this->tables_prefix."categories` SET `parent_category_id` = ".$cid.", `depth` = ".($cat["depth"] + 1)." WHERE `id` = ".$eid, __LINE__);
			}
		}
		
		public function sort_images($field_id, $order) {
			foreach ($order as $position => $id) {
				$this->mysql_query("UPDATE `".$this->tables_prefix."m_images` SET `position` = ".$position." WHERE `id` = ".$id, __LINE__);
			}
		}
		
		public function sort_files($field_id, $order) {
			foreach ($order as $position => $id) {
				$this->mysql_query("UPDATE `".$this->tables_prefix."m_files` SET `position` = ".$position." WHERE `id` = ".$id, __LINE__);
			}
		}
		
		public function save_entry_order($order) {
			if ($this->module_id == null) return $this->leaveError("CMS->save_entry_order(): requires module_id to be set");
		
			$old_positions = $this->mysql_query("SELECT `id`, `e_position` FROM `".$this->table()."` WHERE `id` IN (".implode(', ', array_values($order)).") ORDER BY `e_position` ASC", __LINE__);
			$positions = array();
			$current   = array();
			if (mysql_num_rows($old_positions)) {
				while ($old_position = mysql_fetch_assoc($old_positions)) {
					$positions[] = $old_position['e_position'];
					$current[$old_position['id']] = $old_position['e_position'];
				}
			}

			foreach ($order as $position => $id) {
				if ($current[$id] != $positions[$position]) {
					$this->mysql_query("UPDATE `".$this->table()."` SET `e_position` = ".$positions[$position]." WHERE `id` = ".$id, __LINE__);
				}
			}
			
			// if a bugged position is present, reorder all entries
			$duplicate_positions = $this->mysql_query("SELECT count(*) as duplicates FROM `".$this->table()."` GROUP BY `e_position` HAVING duplicates > 1", __LINE__);
			if (mysql_num_rows($duplicate_positions)) {
				$this->reorderEntries();
			}
		}
		
		public function reorderEntries() {
			if (!isset($this->module_id)) return $this->leaveError("CMS->reorderEntries(): requires module_id to be set");
			
			$entries = $this->mysql_query("SELECT * FROM `".$this->table()."` ORDER BY `e_position`", __LINE__);
			for ($pos = 0; $entry = mysql_fetch_assoc($entries); $pos++) {
				$this->mysql_query("UPDATE `".$this->table()."` SET `e_position` = ".$pos." WHERE `id` = ".$entry['id'], __LINE__);
			}
		}
		
		
		public function save_category_order($order) {
			if ($this->module_id == null) return $this->leaveError("CMS->save_category_order(): requires module_id to be set");
			foreach ($order as $position => $id) {
				$this->mysql_query("UPDATE `".$this->tables_prefix."categories` SET `position` = ".$position." WHERE `id` = ".$id, __LINE__);
			}
		}
		
		public function toggle_entry_activity($entry_id) {
			if ($this->module_id == null) return $this->leaveError("CMS->toggle_entry_activity(): requires module_id to be set");
			$this->mysql_query("UPDATE `".$this->table()."` SET `e_active` = 1 - `e_active` WHERE `id` = ".$entry_id, __LINE__);

			$check_state = $this->mysql_query("SELECT `e_active` FROM `".$this->table()."` WHERE `id` = ".$entry_id, __LINE__);

			if($check_state){
				$row = mysql_fetch_assoc($check_state);
				if($row['e_active'] == 1)
					Event::fire($this->module_id, 'postToggleToActive', $entry_id);
				else
					Event::fire($this->module_id, 'postToggleToInactive', $entry_id);
			}
			
		}
		
		public function distinctValues($field_id) {
			$v = array();
			$field = $this->fieldData($field_id);
			
			$q = "SELECT DISTINCT(`".$field['cms_name']."`) FROM `".$this->table()."` WHERE `e_active` = 1";
			if ($this->conditions != '') $q .= " AND ".$this->conditions;
			
			$values = $this->mysql_query($q, __LINE__);
			if (mysql_num_rows($values)) {
				while ($value = mysql_fetch_row($values)) {
					$v[] = $value[0];
				}
			}
			return $v;
		}
		
		public function save_custom_rendering_position($field_id, $positions) {
			$this->mysql_query("UPDATE `".$this->tables_prefix."fields` SET ".Tools::mysql_update($positions)." WHERE `id` = ".((int) $field_id), __LINE__);
		}
		
		public function getPassiveReferencingFields() {
			if ($this->module_id == null) return $this->leaveError("CMS->getPassiveReferencingFields(): requires module_id to be set");
			$f = array();
			$fields = $this->mysql_query("SELECT `f`.`name`, `f`.`module_id`, f.`id` as field_id, `ft`.`form_element`, `f`.`refers_to_module`, `m`.`name` as module_name, `m`.`icon_image` FROM `".$this->tables_prefix."fields` f, `".$this->tables_prefix."field_types` ft, `".$this->tables_prefix."modules` m WHERE `ft`.`id` = `f`.`field_type_id` AND `m`.`admin_only` = 0 AND `f`.`refers_to_module` = ".$this->module_id." AND `f`.`module_id` = `m`.`id` ORDER BY `f`.`position`", __LINE__);
			if (mysql_num_rows($fields)) {
				while ($field = mysql_fetch_assoc($fields)) {
					$f[] = $field;
				}
			}
			return $f;
		}
		
		public function realign_to_grid($gridsize = 30) {
			$gridsize = (int) $gridsize;
			$this->mysql_query("UPDATE `".$this->tables_prefix."fields` SET `render_y` = ".$gridsize." * round(render_y / ".$gridsize."), `render_x` = ".$gridsize." * round(render_y / ".$gridsize."), `render_dy` = ".$gridsize." * round(render_dy / ".$gridsize."), `render_dx` = ".$gridsize." * round(render_dx / ".$gridsize.")", __LINE__);
		}
		
		
		/* LOGGING */
		private function leaveError($e)   { $this->logger->log($e, 'red'); }
		private function leaveMessage($e) { $this->logger->log($e, '#f0a'); }
		
		public function titleLog($note = '') {
			$this->logger->title($note);
		}
		
		public function lifetime() {
			return round(1000 * (Tools::microtime() - $this->_starttime));
		}
		
		public function time($n  = '') {
			$this->logger->log($n.'CMS lifetime '.$this->lifetime().' ms', 'darkblue');
		}
		
		public function query_timestamp() {
			$ms = round(1000 * (Tools::microtime() - ($this->query_timestamp ? $this->query_timestamp : $this->_starttime)));
			$this->logger->log($n.'> query time '.$ms.' ms', 'darkblue');
		}
		
		public function showLog() {
			$this->titleLog();
			$this->time();
			echo "<div style='background: #fff; padding: 10px;'>".$this->logger->show_log()."</div>";
		}
		
		private function mysql_query($q, $line) {
			global $db_connection;
			$this->query_counter++;
			if ($this->debug) {
				$this->query_timestamp = Tools::microtime();
				$s = CMS_DB::mysql_query($q);
				$this->logger->log_query($q, $s, 'CMS.php line '.$line);
				$this->query_timestamp();
				return $s;
			}
			return CMS_DB::mysql_query($q);
		}
	}
	
?>

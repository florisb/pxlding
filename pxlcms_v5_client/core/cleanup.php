<?php
   ini_set('memory_limit', '128M');
   set_time_limit(0);
   
   $dirname = $CMS_ENV['root_path_uploads'];
   
   $removeAll = false;
   $cleaned = 0;
   
   function convert($size) {
      $steps = array('bytes', 'kB', 'MB', 'GB');
      $step = 0;
      while ($size > 1024) {
         $size = $size/1024;
         $step++;
      }
      return round($size, 2).' '.$steps[$step];
   }
   
   
   if (isset($_POST['do_cleanup']) && $_POST['do_cleanup']) {
      if (isset($_POST['removeAll']) && $_POST['removeAll']) {
         $removeAll = true;
      } elseif (is_array($_POST['files']) && count($_POST['files'])) {
         //delete given files
         foreach ($_POST['files'] as $file) {
            $cleaned += filesize($dirname.$file);
            unlink($dirname.$file);
            //also remove pxl thumbs
            if (file_exists($dirname.'pxl20_'.$file)) {
               $cleaned += filesize($dirname.'pxl20_'.$file);
               unlink($dirname.'pxl20_'.$file);
            }
            if (file_exists($dirname.'pxl80_'.$file)) {
               $cleaned += filesize($dirname.'pxl80_'.$file);
               unlink($dirname.'pxl80_'.$file);
            }
         }
      }
   }
   
   $filenames = array();
   
   //Remove entries from DB for which the field no longer exists
   $q = "DELETE FROM `cms_m_images` WHERE `field_id` NOT IN (SELECT `id` FROM `cms_fields`)";
   CMS_DB::mysql_query($q);
   $q = "DELETE FROM `cms_m_files` WHERE `field_id` NOT IN (SELECT `id` FROM `cms_fields`)";
   CMS_DB::mysql_query($q);
   $q = "DELETE FROM `cms_m_thumbs` WHERE `image_id` NOT IN (SELECT `id` FROM `cms_m_images`)";
   CMS_DB::mysql_query($q);
   
   //Remove entries from DB for which the entry no longer exists
   function createTableName($modId) {
      $modId = (int) $modId;
      $name = CMS_DB::mysql_query("SELECT `name` FROM `cms_modules` WHERE `id` = ".$modId);
      $name = mysql_fetch_assoc($name);
      $modName = $name['name'];
      return 'cms_m'.$modId.'_'.Tools::alphanumeric($modName);
   }
   
   $iFields = CMS_DB::mysql_query("SELECT `id`, `module_id` FROM `cms_fields` WHERE `field_type_id` IN (6,7,8)");
   while ($field = mysql_fetch_assoc($iFields)) {
      $field_id = $field['id'];
      $mod_name = createTableName($field['module_id']);
      $eIds = CMS_DB::mysql_query("SELECT DISTINCT `id` FROM `".$mod_name."`");
      $entry_ids = array();
      while ($e = mysql_fetch_assoc($eIds)) {
         $entry_ids[] = $e['id'];
      }
      CMS_DB::mysql_query("DELETE FROM `cms_m_images` WHERE `field_id` = ".$field_id." AND `entry_id` NOT IN (".implode(',', $entry_ids).")");
   }
   
   
   
   $q = "";
   //find filenames
   $prefix = $CMS_DB['prefix'];
   if ($CMS_EXTRA['delete_uploaded_img_originals'] != true) {
      //no need to search for originals when these have been deleted on upload
      $q .= "
         SELECT
            CONVERT(`i`.`file` USING utf8) COLLATE utf8_general_ci AS `file`
         FROM
            `".$prefix."m_images` AS `i`
         UNION
      ";
   }
   $q .= "
      SELECT
         CONVERT(CONCAT(`r`.`prefix`, `i`.`file`) USING utf8) COLLATE utf8_general_ci AS `file`
      FROM
         `".$prefix."m_images` AS `i`
         LEFT JOIN `".$prefix."field_options_resizes` AS `r` ON (`i`.`field_id` = `r`.`field_id`)
      UNION
      SELECT
         CONVERT(`f`.`file` USING utf8) COLLATE utf8_general_ci AS `file`
      FROM
         `".$prefix."m_files` AS `f`
   ";
   $names = CMS_DB::mysql_query($q);
   while ($name = mysql_fetch_assoc($names)) {
      $filenames[$name['file']] = 1;
   }
   
   //show not found files, so they may be deleted
   ?>
      <h2>Please select the stray files to delete:</h2>
      <script type="text/javascript">
         window.cleanupselect = function(check) {
            var form = document.getElementById('cleanupform');
            for (var i = 0, j = form.elements.length; i < j; i++) {
               if (form.elements[i].getAttribute('type') == 'checkbox') {
                  form.elements[i].checked = check;
               }
            }
         };
         
         window.removeSelected = function() {
            var form = document.getElementById('cleanupform');
            var files = '';
            for (var i = 0, j = form.elements.length; i < j; i++) {
               if (form.elements[i].getAttribute('name') == 'files[]' && form.elements[i].checked) {
                  files += '&files[]='+form.elements[i].value;
               }
            }
            postback('page=body.php&special_page=cleanup.php&do_cleanup=1'+files, function() {});
         };
         
         window.removeAll = function() {
            postback('page=body.php&special_page=cleanup.php&removeAll=1&do_cleanup=1', function() {});
         };
         
         window.postback = function(params, callback) {
            loading_indicator(true);
            //disable buttons
            document.getElementById('delsel').disabled = true;
            if (document.getElementById('delall')) {
               document.getElementById('delall').disabled = true;
            }
            
            new Ajax.Updater(
               'mainbody',
               'ajax.php',
               {
                  method: 'post',
                  parameters: params,
                  requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
                  evalScripts: true,
                  onComplete: function(result) {
                     callback(result);
                     loading_indicator(false);
                  }
               }
            );
         }
         
      </script>
   <?php
   $dir = new DirectoryIterator($dirname);
   $found = 0;
   $break = false;
   ?>
   <form action="." method="post" id="cleanupform" name="cleanupform">
      <a onclick="cleanupselect(true);">select all</a> | <a onclick="cleanupselect(false);">unselect all</a><br />
      <input type="hidden" name="do_cleanup" value="1" />
      <?php
         foreach ($dir as $file) {
            $name = $file->getFilename();
            if ($file->isFile() && !isset($filenames[$name]) && $name != 'index.php' && strpos($name, 'pxl20_') === false && strpos($name, 'pxl80_') === false) {
               if ($removeAll) {
                  $cleaned += $file->getSize();
                  unlink($dirname.$name);
                  //also remove pxl thumbs
                  if (file_exists($dirname.'pxl20_'.$file)) {
                     $cleaned += filesize($dirname.'pxl20_'.$file);
                     unlink($dirname.'pxl20_'.$file);
                  }
                  if (file_exists($dirname.'pxl80_'.$file)) {
                     $cleaned += filesize($dirname.'pxl80_'.$file);
                     unlink($dirname.'pxl80_'.$file);
                  }
               } else {
                  ?>
                     <input type="checkbox" name="files[]" value="<?php echo($name); ?>" style="display: inline; vertical-align: middle; margin-right: 4px;" /> <?php echo($name); ?> (<?php echo(convert($file->getSize())); ?>)<br />
                  <?php
                  $found++;
               }
            }
            if ($found >= 100 && !$removeAll) {
               $break = true;
               break;
            }
         }
         if ($found > 0) {
            ?>
               <?php echo($found); ?> stray files <?php echo($break ? 'shown' : 'found'); ?><br /><br />
               <?php if ($found > 30) { ?><a onclick="cleanupselect(true);">select all</a> | <a onclick="cleanupselect(false);">unselect all</a><br /><br /><?php } ?>
               <input type="submit" name="submit_cleanup" value="Delete selected" onclick="removeSelected(); return false;" id="delsel" />
               <?php if ($break) { ?>
                  More files have been found. <input type="button" value="Delete all" onclick="removeAll(); return false;" style="display: inline;" id="delall" />
               <?php } ?>
          <?php
         } else {
            ?>
               No stray files found!
            <?php
         }
         if ($cleaned > 0) {
            ?>
            <br /><br />
            A total of <?php echo(convert($cleaned)); ?> has been cleared.
            <?php
         }
      ?>
   </form>
<?php

/**
  * All available events and the data they're receiving:
  * preDuplicate (duplication of entry)						=> $entry_id
  * postDuplicate														=> $new_entry_id
  * preDelete (deletion of entry)								=> $entry_id
  * postDelete															=> $entry_id (no longer existent in DB)
  * preSelectMultiple (selection of more than one entry)	=> $eids (ID's)
  * postSelectMultiple												=> $entries
  * preSelectSingle (selection of just one entry)			=> $id
  * postSelectSingle													=> $entry
  * preSave																=> $data (the data about to be saved)
  * postSave															=> $id (the id of the saved entry)
  * preInsert															=> $data (the data about to be inserted)
  * postInsert															=> $id (the id of the inserted entry)
  * preUpdate															=> $data (the data about to be updated)
  * postUpdate															=> $id (the id of the updated entry)
  * preUploadImage														=> $data (entry_id, filename and extension)
  * postUploadImage														=> $data (entry_id, filename and extension)
  * postToggleToInactive                 => $id (the id of the saved entry)
  * postToggleToActive                   => $id (the id of the saved entry)
  */

/**
 * Events are run in the following sequence (in case of updating/inserting):
 * 1) preSave
 * 2) preInsert or preUpdate
 * 3) postInsert or postUpdate
 * 4) postSave
 */


/* example 1: encrypt a password before inserting */
function encryptPass($data) { //$data is the data to be inserted
	$data['pass'] = sha1($data['pass']); //encrypt the password
	return $data; //return the data to insert
}
Event::register('encryptPass', 'preInsert', 10); //register the hook for preInsert (right before the insertion) of module 10


/* example 2: update a field after saving the entry */
function updateURL($id) {
	$q = 'SELECT * FROM `table` WHERE `id` = '.$id;
	$res = mysql_query($q);
	$entry = mysql_fetch_assoc($res);
	
	$q = 'UPDATE `table` SET `field` = '.($entry['other_field'] / M_PI);
	mysql_query($q);
}
Event::register('updateURL', 'postSave', 3);

/* example 3: use a Model to normalize data */
function model($data) {
	require_once('../models/Model.php');
	$m = new Model();
	$m->field = $data['field'];
	$data['field'] = $m->field;
	return $data;
}
Event::register('model', 'preSave', 8);

/* example 4: use a Model's static method */
require_once('../models/Model.php');
Event::register(array('Model', 'notifyAdministation'), 'postInsert', 12);
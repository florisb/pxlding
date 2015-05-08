<?php
	namespace PXL\Core\Model\Entity;
	
	use Imagick;
	use PXL\Core\Db;
	use PXL\Core\Exception;
	use PXL\Core\Model\Value;
	
	/**
	 * Abstract CMSEntity class.
	 * 
	 * Entity that offers CMS v5 functionality.
	 *
	 * @abstract
	 * @extends BaseEntity
	 */
	abstract class CMSV5Entity extends AbstractEntity {
		
		/**
		 * save
		 *
		 * Saves the entity to the database, creating a new row
		 * if it's a new entity and updating an existing row if
		 * it's an existing entity.
		 *
		 */
		public function save() {

			if (empty($this->_table)) {
				return false;
			}
		
			$data               = array();
			$insertedReferences = array();
			
			foreach($this->_dbFields as $field) {
				if (is_null($this->$field) && !array_key_exists($field, $this->_data)) {
					continue;
				}
				
				$value = $this->$field;
				
				switch(true) {
					case ($value instanceof AbstractEntity):
						$value->save();
						$data[$field] = $value->id;
						break;
						
					case ($value instanceof \PXL\Core\Collection\SimpleMap):
						if (!empty($this->_data['id'])) {
							continue;
						}
					
						$fromFieldId = $this->_determineReferenceFieldId($field);
					
						if (!$fromFieldId) {
							$insertedReferences[] = $value;
						} else {
							$insertedReferences[] = array($fromFieldId, $value);
						}
						break;
					
					case ($value instanceof \PXL\Core\Collection\SimpleList):
						$insertedReferences[] = $value;
						break;
						
					case ($value instanceof Value\AbstractValue):
						$data[$field] = $value->toStorage();
						break;
						
					case ($value instanceof \DateTime):
						$data[$field] = $value->getTimestamp();
						break;
						
					default:
						$data[$field] = $value;
						break;
				}
			}
			
			$data = $this->preSave($data);
			

			if (empty($data)) {
				return false;
			} else {
				if (empty($this->id)) {
					$result = $this->_insertEntity($this->preInsert($data));
					$this->postInsert();
					
					foreach($insertedReferences as $values) {
						if (is_array($values)) {
							list($fromFieldId, $values) = $values;
						} else {
							$fromFieldId = null;
						}
						
						foreach($values as $value) {
							$value->save();
							
							if (!is_null($fromFieldId)) {
								$this->_insertNewReference($fromFieldId, $id, $value->id);
							}
						}
					}
				} else {
					$this->_updateEntity($this->preUpdate($data));
					$this->postUpdate();

					$result = null;
				}
			}

			$this->postSave();
			return $result;
		}
		
		/**
	     * _insertNewReference
	     *
	     * Inserts a new reference into the cms_m_references table, to support
	     * 1:N references to be made. Positioning is retained as well.
	     *
	     * @param $fromFieldId int
	     * @param $fromEntryId int
	     * @param $toEntryId   int
	     */
	    protected function _insertNewReference($fromFieldId, $fromEntryId, $toEntryId) {
		    //Determine next position of reference entry
		    $q = <<<SQL
		    	SELECT
		    		MAX(`position`) AS `last_position`
		    	FROM
		    		`cms_m_references`
		    	WHERE
		    		`from_field_id`='%d'
		    	AND
		    		`from_entry_id`='%d'
SQL;

				$q            = sprintf($q, $fromFieldId, $fromEntryId);
				$stmt         = new Db\Statement($q);
				$lastPosition = $this->_db->row($stmt)->last_position;

				if (empty($lastPosition)) {
					$newPosition = 1;
				} else {
					$newPosition = (int) $lastPosition + 1;
				}
				
				//Define insert data
				$insertData = array(
					'from_field_id' => $fromFieldId,
					'from_entry_id' => $fromEntryId,
					'to_entry_id'   => $toEntryId,
					'position'      => $newPosition
				);
				
				//Insert reference into table
				$this->_db->insert('cms_m_references', $insertData);
	    }

	    protected function _clearReferences($fromFieldId, $fromEntryId){
	    	Db\Db::delete('cms_m_references','WHERE `from_field_id` = '.(int) $fromFieldId.' AND `from_entry_id` = '.(int) $fromEntryId);
	    }

		protected function _determineModuleId() {
			if (empty($this->_table)) {
				return false;
			}
			
			preg_match('#cms_m(\d+)[0-9A-z_]+#i', $this->_table, $matches);
			
			return !empty($matches[1]) ? $matches[1] : false;
		}
		
		protected function _determineReferenceFieldId($fieldName) {
			$q = "
				SELECT
					`id`
				FROM
					`cms_fields`
				WHERE
					`module_id`=?
				AND
					LOWER(REPLACE(`name`, ' ', '_'))=?
				AND
					`field_type_id`=17
			";
			
			$moduleId = $this->_determineModuleId();
			
			if (!$moduleId) {
				return false;
			}
			
			$stmt     = new Db\Statement($q, array(
				$moduleId,
				$fieldName
			));
			
			$fieldId = $this->_db->row($stmt);
			
			return is_null($fieldId) ? false : $fieldId->id;
		}

		protected function _handleFileSave(Value\File $file, $fieldId, $directory = '', $position = 0) {
			$directory = trim($directory, '\\/');
			$directory = $this->uploads_path . (empty($directory) ? '' : DIRECTORY_SEPARATOR . $directory);

			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
			}

			// Determine unique filename
			$filename = $file->getFileName() ?: md5(uniqid($this->id, true)) . '.' . $file->getExtension();

			$q = "
				SELECT
					`id`,
					`file`
				FROM
					`cms_m_files`
				WHERE
					`entry_id`=?
				AND
					`field_id`=?
				AND
					`position`=?
				LIMIT 0,1
			";

			$stmt = new Db\Statement($q, array(
				array($this->id, 'i'),
				array($fieldId,  'i'),
				array($position, 'i')
			));

			$result = $this->_db->row($stmt);

			if (!empty($result)) {

				$this->_db->update('cms_m_files', array(
					'file'      => $filename,
					'extension' => $file->getExtension(),
					'uploaded'  => array(time(), 'i'),
				), "`id`={$result->id}");

				$oldFilePath = path("$directory/{$result->file}");

				if ($result->file !== $filename) {
					if (is_file($oldFilePath)) {
						unlink($oldFilePath);
					}
				}
			} else {
				$this->_db->insert('cms_m_files', array(
					'entry_id'  => array($this->id, 'i'),
					'uploaded'  => array(time(),    'i'),
					'field_id'  => array($fieldId,  'i'),
					'position'  => array($position, 'i'),
					'file'      => $filename,
					'extension' => $file->getExtension()
				));
			}

			$filePath = path("$directory/$filename");
			file_put_contents($filePath, $file->value);
		}

		protected function _handleImageSave(Value\Image $image, $fieldId, $directory = '', $position = 0, $caption = '', $deletePrevious = true) {

			$resizes   = $this->_fetchImageResizes($fieldId);
			$directory = trim($directory, '\\/');
			$directory = $this->uploads_path . (empty($directory) ? '' : DIRECTORY_SEPARATOR . $directory);

			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
			}

			// Determine unique filename
			$filename = $image->getFileName() ?: md5(uniqid($this->id, true)) . '.' . $image->getExtension();

			$q = "
				SELECT
					`id`,
					`file`
				FROM
					`cms_m_images`
				WHERE
					`entry_id`=?
				AND
					`field_id`=?
				AND
					`position`=?
				LIMIT
					0,1
			";

			$stmt = new Db\Statement($q, array(
				array($this->id, 'i'),
				array($fieldId,  'i'),
				array($position, 'i')
			));

			$result = $this->_db->row($stmt);

			if ($deletePrevious && !empty($result)) {

				$this->_db->update('cms_m_images', array(
					'file'      => $filename,
					'extension' => $image->getExtension(),
					'uploaded'  => array(time(), 'i'),
					'caption'	=> $caption
				), "`id`={$result->id}");

				$oldImageFile = path("$directory/{$result->file}");
				if ($result->file !== $filename) {
					if (is_file($oldImageFile)) {
						unlink($oldImageFile);
					}

					foreach($resizes as $resize) {
						$resizeFilePath = path("$directory/{$resize->prefix}{$result->file}");
						if (is_file($resizeFilePath)) {
							unlink($resizeFilePath);
						}
					}
				} else {
					// Run resizes if necessary
					foreach($resizes as $resize) {

						$resizeFilePath = path("$directory/{$resize->prefix}{$result->file}");
						if (!is_file($resizeFilePath)) {
							$img = new Imagick($oldImageFile);
							$img->cropThumbnailImage((int) $resize->width, (int) $resize->height);

							if ((boolean) $resize->watermark) {
								$waterMarkImage = path(APPLICATION_PATH . 'pxlcms_v5_client/watermarks/' . $resize->watermark_image);

								if (is_file($waterMarkImage) && is_readable($waterMarkImage)) {
									$waterMarkImage   = new Imagick($waterMarkImage);
									$watermark_width  = $waterMarkImage->getImageWidth();
									$watermark_height = $waterMarkImage->getImageHeight();

									// check if we need to scale the watermark down
									$resizefactor = min($img->getImageWidth() / $watermark_width, $img->getImageHeight() / $watermark_height, 1);
									$waterMarkImage->scaleImage($watermark_width * $resizefactor, $watermark_height * $resizefactor);

									$dst_x = (((int) $resize->watermark_left / 100) * $img->getImageWidth())  - (((int) $resize->watermark_left / 100) * $watermark_width * $resizefactor);
									$dst_y = (((int) $resize->watermark_top / 100) * $img->getImageHeight()) - (((int) $resize->watermark_top / 100) * $watermark_height * $resizefactor);
									
									$img->compositeImage($waterMarkImage, Imagick::COMPOSITE_OVER, $dst_x, $dst_y);
									$waterMarkImage->destroy();
								}
							}

							$img->writeImage(path("$directory/{$resize->prefix}{$filename}"));
							$img->destroy();
						}
					}

					return; // No further action necessary since no the image hasn't changed any further
				}
			} else {

				$this->_db->insert('cms_m_images', array(
					'entry_id'  => array($this->id, 'i'),
					'uploaded'  => array(time(),    'i'),
					'field_id'  => array($fieldId,  'i'),
					'position'  => array($position, 'i'),
					'file'      => $filename,
					'extension' => $image->getExtension(),
					'caption'	=> $caption
				));
			}
					
			// Write image to disk
			$filePath = path("$directory/$filename");
			file_put_contents($filePath, $image->value);
				
			// Handle resizes
			foreach($resizes as $resize) {
				$img = new Imagick($filePath);
				$img->cropThumbnailImage((int) $resize->width, (int) $resize->height);

				if ((boolean) $resize->watermark) {
					$waterMarkImage = path(APPLICATION_PATH . 'pxlcms_v5_client/watermarks/' . $resize->watermark_image);

					if (is_file($waterMarkImage) && is_readable($waterMarkImage)) {
						$waterMarkImage   = new Imagick($waterMarkImage);
						$watermark_width  = $waterMarkImage->getImageWidth();
						$watermark_height = $waterMarkImage->getImageHeight();

						// check if we need to scale the watermark down
						$resizefactor = min($img->getImageWidth() / $watermark_width, $img->getImageHeight() / $watermark_height, 1);
						$waterMarkImage->scaleImage($watermark_width * $resizefactor, $watermark_height * $resizefactor);

						$dst_x = (((int) $resize->watermark_left / 100) * $img->getImageWidth())  - (((int) $resize->watermark_left / 100) * $watermark_width * $resizefactor);
						$dst_y = (((int) $resize->watermark_top / 100) * $img->getImageHeight()) - (((int) $resize->watermark_top / 100) * $watermark_height * $resizefactor);
						
						$img->compositeImage($waterMarkImage, Imagick::COMPOSITE_OVER, $dst_x, $dst_y);
						$waterMarkImage->destroy();
					}
				}

				$img->writeImage(path("$directory/{$resize->prefix}{$filename}"));
				$img->destroy();
			}
		}
		
		protected function _getUploadsPath() {
			$path = path(APPLICATION_PATH . 'pxlcms_v5_client/uploads');
			
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			
			return $this->uploads_path = $path;
		}
		
		protected function _fetchImage($filename, $directory = '') {

			$directory = trim($directory, '\\/') . DIRECTORY_SEPARATOR;
			$directory = $this->uploads_path . (empty($directory) ? '' : DIRECTORY_SEPARATOR . $directory);
			$filePath  = path("{$directory}/$filename");
			$extension = pathinfo($filePath, PATHINFO_EXTENSION);
			
			if (!is_file($filePath)) {
				return null;
			} else {
				return new Value\Image($filePath, "image/$extension", $filename);
			}
		}
		
		protected function _fetchImageResizes($fieldId) {
			$resizesQ = "
				SELECT
					*
				FROM
					`cms_field_options_resizes`
				WHERE
					`field_id`=?
			";
				
			$stmt    = new Db\Statement($resizesQ, array(array($fieldId, 'i')));
			return $this->_db->matrix($stmt, null, 'prefix');
		}
		
        public function toArray() {
			$data = parent::toArray();

			if (array_key_exists('id', $data) && empty($data['id'])) {
				unset($data['id']);
			}

			return $data;
		}
	}
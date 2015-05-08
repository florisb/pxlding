<?php

namespace PXL\Core\Collection;

/**
 * A map entry (key-value pair). The {@see Map::entrySet} method returns a collection-view of the map, whose elements are of this class. The only way to obtain a reference to a map entry is from the iterator of this collection-view. These MapEntry objects are valid only for the duration of the iteration; more formally, the behavior of a map entry is undefined if the backing map has been modified after the entry was returned by the iterator, except through the setValue operation on the map entry.
 * @see Map::entrySet
 */
interface MapEntry extends \Serializable {
	/**
	 * Returns the key corresponding to this entry.
	 * @return mixed The key corresponding to this entry.
	 * @throws UnexpectedValueException Implementations may, but are not required to, throw this exception if the entry has been removed from the backing map.
	 */
	public function getKey();

	/**
	 * Returns the value corresponding to this entry. If the mapping has been removed from the backing map (by the iterator's remove operation), the results of this call are undefined.
	 * @return mixed The value corresponding to this entry
	 * @throws UnexpectedValueException Implementations may, but are not required to, throw this exception if the entry has been removed from the backing map.
	 */
	public function getValue();

	/**
	 * Replaces the value corresponding to this entry with the specified value (optional operation). (Writes through to the map.) The behavior of this call is undefined if the mapping has already been removed from the map (by the iterator's remove operation).
	 * @param mixed $v New value to be stored in this entry
	 * @return mixed   Old value corresponding to the entry
	 * @throws UnexpectedValueException Implementations may, but are not required to, throw this exception if the entry has been removed from the backing map.
	 * @throws BadMethodCallException   If the put operation is not supported by the backing map
	 */
	public function setValue($v);
}
<?php

namespace PXL\Core\Collection;

/**
 * A {@see SortedMap} extended with navigation methods returning the closest matches for given search targets. Methods lowerEntry, floorEntry, ceilingEntry, and higherEntry return {@see MapEntry} objects associated with keys respectively less than, less than or equal, greater than or equal, and greater than a given key, returning null if there is no such key. Similarly, methods lowerKey, floorKey, ceilingKey, and higherKey return only the associated keys. All of these methods are designed for locating, not traversing entries.
 * 
 * A NavigableMap may be accessed and traversed in either ascending or descending key order. The descendingMap method returns a view of the map with the senses of all relational and directional methods inverted. The performance of ascending operations and views is likely to be faster than that of descending ones. Submaps of any NavigableMap must implement the NavigableMap interface.
 * 
 * This interface additionally defines methods firstEntry, pollFirstEntry, lastEntry, and pollLastEntry that return and/or remove the least and greatest mappings, if any exist, else returning null.
 * 
 * Implementations of entry-returning methods are expected to return Map.Entry pairs representing snapshots of mappings at the time they were produced, and thus generally do not support the optional {@see MapEntry::setValue} method. Note however that it is possible to change mappings in the associated map using method put.
 * 
 */
interface NavigableMap extends SortedMap {
	/**
	 * Returns a key-value mapping associated with the greatest key strictly less than the given key, or null if there is no such key.
	 * @param  mixed $key    The key.
	 * @return MapEntry|null An entry with the greatest key less than key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function lowerEntry($key);

	/**
	 * Returns the greatest key strictly less than the given key, or null if there is no such key.
	 * @param  mixed $key    The key.
	 * @return mixed|null    The greatest key strictly less than the given key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function lowerEntry($key);

	/**
	 * Returns a key-value mapping associated with the greatest key less than or equal to the given key, or null if there is no such key.
	 * @param  mxied $key    The key.
	 * @return MapEntry|null An entry with the greatest key less than or equal to key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function floorEntry($key);

	/**
	 * Returns the greatest key less than or equal to the given key, or null if there is no such key.
	 * @param  mixed $key The key.
	 * @return mixed|null The greatest key less than or equal to key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function floorKey($key);

	/**
	 * Returns a key-value mapping associated with the least key greater than or equal to the given key, or null if there is no such key.
	 * @param  mixed $key    The key.
	 * @return MapEntry|null An entry with the least key greater than or equal to key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function ceilingEntry($key);

	/**
	 * Returns the least key greater than or equal to the given key, or null if there is no such key.
	 * @param  mixed $key The key.
	 * @return mixed|null The least key greater than or equal to the given key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function ceilingKey($key);

	/**
	 * Returns a key-value mapping associated with the least key greater than the given key, or null if there is no such key.
	 * @param  mixed $key    The key.
	 * @return MapEntry|null An entry with the least key greater than key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function higherEntry($key);

	/**
	 * Returns the least key greater than the given key, or null if there is no such key.
	 * @param  mixed $key The key.
	 * @return mixed|null The least key greater than the given key, or null if there is no such key.
	 * @throws InvalidArgumentException If the specified key cannot be compared with the keys currently in the map.
	 */
	public function higherKey($key);

	/**
	 * Returns a key-value mapping associated with the least key in this map, or null if the map is empty.
	 * @return MapEntry An entry with the least key, or null if this map is empty.
	 */
	public function firstEntry();

	/**
	 * Returns a key-value mapping associated with the greatest key in this map, or null if the map is empty.
	 * @return MapEntry An entry with the greatest key, or null if this map is empty.
	 */
	public function lastEntry();

	/**
	 * Removes and returns a key-value mapping associated with the least key in this map, or null if the map is empty.
	 * @return MapEntry The removed first entry of this map, or null if this map is empty.
	 */
	public function pollFirstEntry();

	/**
	 * Removes and returns a key-value mapping associated with the greatest key in this map, or null if the map is empty.
	 * @return MapEntry The removed last entry of this map, or null if this map is empty.
	 */
	public function pollLastEntry();

	/**
	 * Returns a reverse order view of the mappings contained in this map. The descending map is backed by this map, so changes to the map are reflected in the descending map, and vice-versa. If either map is modified while an iteration over a collection view of either map is in progress (except through the iterator's own remove operation), the results of the iteration are undefined.
	 * The returned map has an ordering equivalent to Collections.reverseOrder(comparator()). The expression m.descendingMap().descendingMap() returns a view of m essentially equivalent to m.
	 * @return NavigableMap A reverse order view of this map.
	 */
	public function descendingMap();

	/**
	 * Returns a {@see NavigableSet} view of the keys contained in this map. The set's iterator returns the keys in ascending order. The set is backed by the map, so changes to the map are reflected in the set, and vice-versa. If the map is modified while an iteration over the set is in progress (except through the iterator's own remove operation), the results of the iteration are undefined. The set supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll, and clear operations. It does not support the add or addAll operations.
	 * @return NavigableSet A navigable set view of the keys in this map.
	 */
	public function navigableKeySet();

	/**
	 * Returns a reverse order {@see NavigableSet} view of the keys contained in this map. The set's iterator returns the keys in descending order. The set is backed by the map, so changes to the map are reflected in the set, and vice-versa. If the map is modified while an iteration over the set is in progress (except through the iterator's own remove operation), the results of the iteration are undefined. The set supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll, and clear operations. It does not support the add or addAll operations.
	 * @return NavigableSet A reverse order navigable set view of the keys in this map.
	 */
	public function descendingKeySet();
}
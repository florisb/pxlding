<?php

namespace PXL\Core\Collection;

/**
 * A Map that further provides a total ordering on its keys. The map is ordered according to the natural ordering of its keys, or by a {@see Comparator} typically provided at sorted map creation time. This order is reflected when iterating over the sorted map's collection views (returned by the entryMap, keySet and values methods). Several additional operations are provided to take advantage of the ordering. (This interface is the map analogue of {@see SortedSet}.)
 * 
 * All keys inserted into a sorted map must be accepted by the specified comparator. Furthermore, all such keys must be mutually comparable: comparator.compare(k1, k2) must not throw an InvalidArgumentException for any keys k1 and k2 in the sorted map. Attempts to violate this restriction will cause the offending method or constructor invocation to throw an InvalidArgumentException.
 * 
 * Note that the ordering maintained by a sorted map (whether or not an explicit comparator is provided) must be consistent with equals if the sorted map is to correctly implement the Map interface. (See the {@see Comparator} interface for a precise definition of consistent with equals.) This is so because the Map interface is defined in terms of the equals operation, but a sorted map performs all key comparisons using its comparators compare method, so two keys that are deemed equal by this method are, from the standpoint of the sorted map, equal. The behavior of a tree map is well-defined even if its ordering is inconsistent with equals; it just fails to obey the general contract of the Map interface.
 * 
 * All general-purpose sorted map implementation classes should provide four "standard" constructors: 1) A void (no arguments) constructor, which creates an empty sorted map sorted according to the natural ordering of its keys. 2) A constructor with a single argument of type Comparator, which creates an empty sorted map sorted according to the specified comparator. 3) A constructor with a single argument of type Map, which creates a new map with the same key-value mappings as its argument, sorted according to the keys' natural ordering. 4) A constructor with a single argument of type SortedMap, which creates a new sorted map with the same key-value mappings and the same ordering as the input sorted map. There is no way to enforce this recommendation, as interfaces cannot contain constructors.
 * 
 * Note: several methods return submaps with restricted key ranges. Such ranges are half-open, that is, they include their low endpoint but not their high endpoint (where applicable). If you need a closed range (which includes both endpoints), and the key type allows for calculation of the successor of a given key, merely request the subrange from lowEndpoint to successor(highEndpoint). For example, suppose that m is a map whose keys are strings. The following idiom obtains a view containing all of the key-value mappings in m whose keys are between low and high, inclusive:
 * 
 *    SortedMap<String, V> sub = m.subMap(low, high+"\0");
 * A similar technique can be used to generate an open range (which contains neither endpoint). The following idiom obtains a view containing all of the key-value mappings in m whose keys are between low and high, exclusive:
 *    SortedMap<String, V> sub = m.subMap(low+"\0", high);
 *    
 */
interface SortedMap extends Map {
	/**
	 * Creates a new SortedMap
	 * @param null|Comparator|Collection|SortedMap $ccs See class-documentation for more info
	 */
	public function __construct($ccs = null);

	/**
	 * Returns the comparator used to order the elements in this map, or null if this map uses the natural ordering of its elements.
	 * @return Comparator The comparator used to order the elements in this map, or null if this map uses the natural ordering of its elements.
	 */
	public function comparator();

	/**
	 * Returns a view of the portion of this map whose elements range from fromKey, inclusive, to toKey, exclusive. (If fromKey and toKey are equal, the returned map is empty.) The returned map is backed by this map, so changes in the returned map are reflected in this map, and vice-versa. The returned map supports all optional map operations that this map supports.
	 * The returned map will throw an IllegalArgumentException on an attempt to insert an element outside its range.
	 * 
	 * @param  mixed $fromKey Low endpoint (inclusive) of the returned map.
	 * @param  mixed $toKey   High endpoint (exclusive) of the returned map.
	 * @return SortedMap      A view of the portion of this map whose elements range from fromKey, inclusive, to toKey, exclusive.
	 * @throws InvalidArgumentException If fromKey and toKey cannot be compared to one another using this map's comparator (or, if the map has no comparator, using natural ordering). Implementations may, but are not required to, throw this exception if fromKey or toKey cannot be compared to elements currently in the map.
	 * @throws OutOfRangeException If fromKey is greater than toKey; or if this map itself has a restricted range, and fromKey or toKey lies outside the bounds of the range
	 */
	public function subMap($fromKey, $toKey);

	/**
	 * Returns a view of the portion of this map whose elements are strictly less than toKey. The returned map is backed by this map, so changes in the returned map are reflected in this map, and vice-versa. The returned map supports all optional map operations that this map supports.
	 * The returned map will throw an IllegalArgumentException on an attempt to insert an element outside its range.
	 * 
	 * @param  mixed $toKey High endpoint (exclusive) of the returned map.
	 * @return SortedMap    A view of the portion of this map whose elements are strictly less than toKey.
	 * @throws InvalidArgumentException If toKey is not compatible with this map's comparator (or, if the map has no comparator, ordering cannot be obtained through natural ordering). Implementations may, but are not required to, throw this exception if toKey cannot be compared to elements currently in the map..
	 * @throws OutOfRangeException If this map itself has a restricted range, and toKey lies outside the bounds of the range.
	 */
	public function headmap($toKey);

	/**
	 * Returns a view of the portion of this map whose elements are greater than or equal to fromKey. The returned map is backed by this map, so changes in the returned map are reflected in this map, and vice-versa. The returned map supports all optional map operations that this map supports.
	 * The returned map will throw an IllegalArgumentException on an attempt to insert an element outside its range.
	 * @param  mixed $fromKey Low endpoint (inclusive) of the returned map.
	 * @return SortedMap      A view of the portion of this map whose elements are greater than or equal to fromKey.
	 * @throws InvalidArgumentException If fromKey is not compatible with this map's comparator (or, if the map has no comparator, ordering cannot be obtained through natural ordering). Implementations may, but are not required to, throw this exception if fromKey cannot be compared to elements currently in the map.
	 * @throws OutOfRangeException If this map itself has a restricted range, and fromKey lies outside the bounds of the range.
	 */
	public function tailmap($fromKey);

	/**
	 * Returns the first (lowest) key currently in this map.
	 * @return mixed The first (lowest) key currently in this map.
	 * @throws RangeException If this map is empty.
	 */
	public function firstKey();

	/**
	 * Returns the last (highest) key currently in this map.
	 * @return mixed The last (highest) key currently in this map.
	 * @throws RangeException If this map is empty.
	 */
	public function lastKey();

	/**
	 * Returns a {@see SortedSet} view of the keys contained in this map. The set's iterator returns the keys in ascending order. The set is backed by the map, so changes to the map are reflected in the set, and vice-versa. If the map is modified while an iteration over the set is in progress (except through the iterator's own remove operation), the results of the iteration are undefined. The set supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll, and clear operations. It does not support the add or addAll operations.
	 * @return SortedSet A set view of the keys contained in this map, sorted in ascending order.
	 */
	public function keySet();

	/**
	 * Returns a {@see Collection} view of the values contained in this map. The collection's iterator returns the values in ascending order of the corresponding keys. The collection is backed by the map, so changes to the map are reflected in the collection, and vice-versa. If the map is modified while an iteration over the collection is in progress (except through the iterator's own remove operation), the results of the iteration are undefined. The collection supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Collection.remove, removeAll, retainAll and clear operations. It does not support the add or addAll operations.
	 * @return Collection A collection view of the values contained in this map, sorted in ascending key order.
	 */
	public function values();

	/**
	 * Returns a {@see SortedSet} view of the mappings contained in this map. The set's iterator returns the entries in ascending key order. The set is backed by the map, so changes to the map are reflected in the set, and vice-versa. If the map is modified while an iteration over the set is in progress (except through the iterator's own remove operation, or through the setValue operation on a map entry returned by the iterator) the results of the iteration are undefined. The set supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll and clear operations. It does not support the add or addAll operations.
	 * @return SortedSet A set view of the mappings contained in this map, sorted in ascending key order
	 * @see MapEntry
	 */
	public function entrySet();
}
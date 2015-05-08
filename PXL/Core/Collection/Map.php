<?php

namespace PXL\Core\Collection;

/**
 * An object that maps keys to values. A map cannot contain duplicate keys; each key can map to at most one value.
 * 
 * The Map interface provides three collection views, which allow a map's contents to be viewed as a set of keys, collection of values, or set of key-value mappings. The order of a map is defined as the order in which the iterators on the map's collection views return their elements. Some map implementations, like the TreeMap class, make specific guarantees as to their order; others, like the HashMap class, do not.
 * 
 * Note: great care must be exercised if mutable objects are used as map keys. The behavior of a map is not specified if the value of an object is changed in a manner that affects equals comparisons while the object is a key in the map. A special case of this prohibition is that it is not permissible for a map to contain itself as a key. While it is permissible for a map to contain itself as a value, extreme caution is advised: testing for equality is no longer well defined on such a map.
 * 
 * All general-purpose map implementation classes should provide two "standard" constructors: a void (no arguments) constructor which creates an empty map, and a constructor with a single argument of type Map, which creates a new map with the same key-value mappings as its argument. In effect, the latter constructor allows the user to copy any map, producing an equivalent map of the desired class.
 * 
 * The "destructive" methods contained in this interface, that is, the methods that modify the map on which they operate, are specified to throw BadMethodCallException if this map does not support the operation. If this is the case, these methods may, but are not required to, throw an BadMethodCallException if the invocation would have no effect on the map. For example, invoking the putAll(Map) method on an unmodifiable map may, but is not required to, throw the exception if the map whose mappings are to be "superimposed" is empty.
 * 
 * Some map implementations have restrictions on the keys and values they may contain. For example, some implementations prohibit null keys and values, and some have restrictions on the types of their keys. Attempting to insert an ineligible key or value throws an exception, typically InvalidArgumentException. Attempting to query the presence of an ineligible key or value may throw an exception, or it may simply return false; some implementations will exhibit the former behavior and some will exhibit the latter. More generally, attempting an operation on an ineligible key or value whose completion would not result in the insertion of an ineligible element into the map may throw an exception or it may succeed, at the option of the implementation. Such exceptions are marked as "optional" in the specification for this interface.
 * 
 */
interface Map extends \Countable, \Serializable, \IteratorAggregate {
	/**
	 * Returns the number of key-value mappings in this map. If the map contains more than PHP_INT_MAX elements, returns PHP_INT_MAX.
	 * @return int The number of key-value mappings in this map.
	 */
	//public function count();

	/**
	 * Returns <true> if this map contains no key-value mappings.
	 * @return boolean <true> if this map contains no key-value mappings.
	 */
	public function isEmpty();

	/**
	 * Returns an iterator for this Map
	 * @return Iterator
	 */
	//public function getIterator();

	/**
	 * Returns true if this map contains a mapping for the specified key. More formally, returns true if and only if this map contains a mapping for a key k such that (key==null ? k==null : key === k). (There can be at most one such mapping.)
	 * @param  mixed $k Key whose presence in this map is to be tested.
	 * @return boolean  <true> if this map contains a mapping for the specified key.
	 * @throws InvalidArgumentException If the key is of an inappropriate type for this map (optional).
	 */
	public function containsKey($k);

	/**
	 * Returns true if this map maps one or more keys to the specified value. More formally, returns true if and only if this map contains at least one mapping to a value v such that (value==null ? v==null : value === v). This operation will probably require time linear in the map size for most implementations of the Map interface.
	 * @param  mixed $v Value whose presence in this map is to be tested.
	 * @return boolean  <true> if this map maps one or more keys to the specified value.
	 * @throws InvalidArgumentException If the value is of an inappropriate type for this map (optional).
	 */
	public function containsValue($v);

	/**
	 * Returns the value to which the specified key is mapped, or null if this map contains no mapping for the key.
	 * More formally, if this map contains a mapping from a key k to a value v such that (key==null ? k==null : key === k), then this method returns v; otherwise it returns null. (There can be at most one such mapping.)
	 * 
	 * If this map permits null values, then a return value of null does not necessarily indicate that the map contains no mapping for the key; it's also possible that the map explicitly maps the key to null. The {@see Map::containsKey} operation may be used to distinguish these two cases.
	 * @param  mixed $k   The key whose associated value is to be returned.
	 * @return mixed|null The value to which the specified key is mapped, or <null> if this map contains no mapping for the key.
	 * @throws InvalidArgumentException If the key is of an inappropriate type for this map (optional).
	 */
	public function get($k);

	/**
	 * Associates the specified value with the specified key in this map (optional operation). If the map previously contained a mapping for the key, the old value is replaced by the specified value. (A map m is said to contain a mapping for a key k if and only if m.containsKey(k) would return true.)
	 * @param  mixed $k Key with which the specified value is to be associated.
	 * @param  mixed $v Value to be associated with the specified key.
	 * @return mixed    The previous value associated with key, or null if there was no mapping for key. (A null return can also indicate that the map previously associated null with key, if the implementation supports null values.)
	 * @throws BadMethodCallException   If the put operation is not supported by this map.
	 * @throws InvalidArgumentException If the key or value is of an inappropriate type for this map
	 */
	public function put($k, $v);

	/**
	 * Removes the mapping for a key from this map if it is present (optional operation). More formally, if this map contains a mapping from key k to value v such that (key==null ? k==null : key === k), that mapping is removed. (The map can contain at most one such mapping.)
	 * Returns the value to which this map previously associated the key, or null if the map contained no mapping for the key.
	 * 
	 * If this map permits null values, then a return value of null does not necessarily indicate that the map contained no mapping for the key; it's also possible that the map explicitly mapped the key to null.
	 * 
	 * The map will not contain a mapping for the specified key once the call returns.
	 * 
	 * @param  mixed $k Key whose mapping is to be removed from the map.
	 * @return mixed    The previous value associated with key, or null if there was no mapping for key.
	 * @throws BadMethodCallException If the remove operation is not supported by this map.
	 * @throws InvalidArgumentException If the key is of an inappropriate type for this map (optional).
	 */
	public function remove($k);

	/**
	 * Copies all of the mappings from the specified map to this map (optional operation). The effect of this call is equivalent to that of calling put(k, v) on this map once for each mapping from key k to value v in the specified map. The behavior of this operation is undefined if the specified map is modified while the operation is in progress.
	 * @param  Map    $m Mappings to be stored in this map.
	 * @return void
	 * @throws BadMethodCallException   If the putAll operation is not supported by this map.
	 * @throws InvalidArgumentException If the key is of an inappropriate type for this map.
	 */
	public function putAll(Map $m);

	/**
	 * Removes all of the mappings from this map (optional operation). The map will be empty after this call returns.
	 * @return void
	 * @throws BadMethodCallException   If the clear operation is not supported by this map.
	 */
	public function clear();

	/**
	 * Returns a Set view of the keys contained in this map. The set is backed by the map, so changes to the map are reflected in the set, and vice-versa. If the map is modified while an iteration over the set is in progress (except through the iterator's own remove operation), the results of the iteration are undefined. The set supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll, and clear operations. It does not support the add or addAll operations.
	 * @return Set A set view of the keys contained in this map.
	 */
	public function keySet();

	/**
	 * Returns a Collection view of the values contained in this map. The collection is backed by the map, so changes to the map are reflected in the collection, and vice-versa. If the map is modified while an iteration over the collection is in progress (except through the iterator's own remove operation), the results of the iteration are undefined. The collection supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Collection.remove, removeAll, retainAll and clear operations. It does not support the add or addAll operations.
	 * @return Collection A collection view of the values contained in this map.
	 */
	public function values();

	/**
	 * Returns a Set view of the mappings contained in this map. The set is backed by the map, so changes to the map are reflected in the set, and vice-versa. If the map is modified while an iteration over the set is in progress (except through the iterator's own remove operation, or through the setValue operation on a map entry returned by the iterator) the results of the iteration are undefined. The set supports element removal, which removes the corresponding mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll and clear operations. It does not support the add or addAll operations.
	 * @return Set A set view of the mappings contained in this map.
	 * @see MapEntry
	 */
	public function entrySet();

	public function fromArray(array $a, $replace = true);
}
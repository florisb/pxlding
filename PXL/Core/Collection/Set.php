<?php

namespace PXL\Core\Collection;

/**
 * A collection that contains no duplicate elements. More formally, sets contain no pair of elements e1 and e2 such that e1 === e2, and at most one null element. As implied by its name, this interface models the mathematical set abstraction.
 * 
 * The Set interface places additional stipulations, beyond those inherited from the Collection interface, on the contracts of all constructors and on the contracts of the add method. Declarations for other inherited methods are also included here for convenience. (The specifications accompanying these declarations have been tailored to the Set interface, but they do not contain any additional stipulations.)
 * 
 * The additional stipulation on constructors is, not surprisingly, that all constructors must create a set that contains no duplicate elements (as defined above).
 * 
 * Note: Great care must be exercised if mutable objects are used as set elements. The behavior of a set is not specified if the value of an object is changed in a manner that affects equals comparisons while the object is an element in the set. A special case of this prohibition is that it is not permissible for a set to contain itself as an element.
 * 
 * Some set implementations have restrictions on the elements that they may contain. For example, some implementations prohibit null elements, and some have restrictions on the types of their elements. Attempting to add an ineligible element throws an exception, typically InvalidArgumentException or OutOfBoundsException. Attempting to query the presence of an ineligible element may throw an exception, or it may simply return false; some implementations will exhibit the former behavior and some will exhibit the latter. More generally, attempting an operation on an ineligible element whose completion would not result in the insertion of an ineligible element into the set may throw an exception or it may succeed, at the option of the implementation. Such exceptions are marked as "optional" in the specification for this interface.
 * 
 */
interface Set extends Collection {
	/**
	 * Constructs a new Collection. When an existing Collection is passed as a parameter to the constructor, a copy of the passed collection will be made, where duplicates will be removed.
	 * @param Collection $c If provided, a copy of this collection, with duplicates removed, will be made.
	 */
	//public function __construct(Collection $c = null);

	/**
	 * Adds the specified element to this set if it is not already present (optional operation). More formally, adds the specified element e to this set if the set contains no element e2 such that (e==null ? e2==null : e === e2). If this set already contains the element, the call leaves the set unchanged and returns false. In combination with the restriction on constructors, this ensures that sets never contain duplicate elements.
	 * The stipulation above does not imply that sets must accept all elements; sets may refuse to add any particular element, including null, and throw an exception, as described in the specification for {@see Collection::add}. Individual set implementations should clearly document any restrictions on the elements that they may contain.
	 * 
	 * @param  mixed   $e Element whose presence in this set is to be ensured.
	 * @return boolean    <true> if this set changed as a result of the call.
	 * @throws BadMethodCallException If the add operation is not supported by this set.
	 */
	//public function add($e);

	/**
	 * Adds all of the elements in the specified collection to this set if they're not already present (optional operation). If the specified collection is also a set, the addAll operation effectively modifies this set so that its value is the union of the two sets. The behavior of this operation is undefined if the specified collection is modified while the operation is in progress.
	 * @param  Collection $c Collection containing elements to be added to this set.
	 * @return boolean       <true> if this set changed as a result of the call
	 * @throws BadMethodCallException If the addAll operation is not supported by this set.
	 */
	//public function addAll(Collection $c);

	/**
	 * Removes all of the elements from this set (optional operation). The set will be empty after this method returns.
	 * @return void
	 * @throws BadMethodCallException If the clear operation is not supported by this set.
	 */
	//public function clear();

	/**
	 * Returns true if this set contains the specified element. More formally, returns true if and only if this set contains at least one element e such that (o==null ? e==null : o === e).
	 * @param  mixed   $o Element whose presence in this set is to be tested
	 * @return boolean    <true> if this set contains the specified element
	 */
	//public function contains($o);

	/**
	 * Returns true if this set contains all of the elements in the specified collection.
	 * @param  Collection $c Collection to be checked for containment in this set
	 * @return boolean       <true> if this set contains all of the elements in the specified collection
	 */
	//public function containsAll(Collection $c);

	/**
	 * Returns true if this set contains no elements.
	 * @return boolean <true> if this set contains no elements
	 */
	//public function isEmpty();

	/**
	 * Returns an iterator over the elements in this set. There are no guarantees concerning the order in which the elements are returned (unless this set is an instance of some class that provides a guarantee).
	 * @return Iterator An Iterator over the elements in this set
	 */
	//public function getIterator();

	/**
	 * Removes the specified element from this set if it is present (optional operation). More formally, removes an element e such that (o==null ? e==null : o === e), if this set contains such an element. Returns true if this set contained the element (or equivalently, if this set changed as a result of the call). (This set will not contain the element once the call returns.)
	 * @param  mixed   $o Element to be removed from this set, if present
	 * @return boolean    <true> if an element was removed as a result of this call
	 * @throws BadMethodCallException If the remove operation is not supported by this set.
	 */
	//public function remove($o);

	/**
	 * Removes from this set all of its elements that are contained in the specified collection (optional operation). If the specified collection is also a set, this operation effectively modifies this set so that its value is the asymmetric set difference of the two sets.
	 * @param  Collection $c Collection containing elements to be removed from this set
	 * @return boolean       <true> if this set changed as a result of the call
	 * @throws BadMethodCallException If the removeAll operation is not supported by this set.
	 */
	//public function removeAll(Collection $c);

	/**
	 * Adds all of the elements in the specified collection to this set if they're not already present (optional operation). If the specified collection is also a set, the addAll operation effectively modifies this set so that its value is the union of the two sets. The behavior of this operation is undefined if the specified collection is modified while the operation is in progress.
	 * @param  Collection $c Collection containing elements to be retained in this set
	 * @return boolean       <true> if this set changed as a result of the call
	 * @throws BadMethodCallException If the retainAll operation is not supported by this set.
	 */
	//public function retainAll(Collection $c);

	/**
	 * Returns the number of elements in this set. If this set contains more than PHP_INT_MAX elements, returns PHP_INT_MAX.
	 * @return int The number of elements in this set.
	 */
	//opublic function count();

	/**
	 * Returns an array containing all of the elements in this set. If this set makes any guarantees as to what order its elements are returned by its iterator, this method must return the elements in the same order.
	 * The returned array will be "safe" in that no references to it are maintained by this set. (In other words, this method must allocate a new array even if this set is backed by an array). The caller is thus free to modify the returned array.
	 *
	 * This method acts as bridge between array-based and collection-based APIs.
	 * 
	 * @return array An array containing all of the elements in this set.
	 */
	//public function toArray();
}
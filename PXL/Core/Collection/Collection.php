<?php

namespace PXL\Core\Collection;

/**
 * The root interface in the collection hierarchy. A collection represents a group of objects, known as its elements. Some collections allow duplicate elements and others do not. Some are ordered and others unordered. The PXL-Core does not provide any direct implementations of this interface: it provides implementations of more specific subinterfaces like Set and List. This interface is typically used to pass collections around and manipulate them where maximum generality is desired.
 *
 * Bags or multisets (unordered collections that may contain duplicate elements) should implement this interface directly.
 * 
 * The "destructive" methods contained in this interface, that is, the methods that modify the collection on which they operate, are specified to throw BadMethodCallException if this collection does not support the operation. If this is the case, these methods may, but are not required to, throw an BadMethodCallException if the invocation would have no effect on the collection. For example, invoking the addAll(Collection) method on an unmodifiable collection may, but is not required to, throw the exception if the collection to be added is empty.
 * 
 * Some collection implementations have restrictions on the elements that they may contain. For example, some implementations prohibit null elements, and some have restrictions on the types of their elements. Attempting to add an ineligible element throws an exception, typically InvalidArgumentException or OutOfBoundsException. Attempting to query the presence of an ineligible element may throw an exception, or it may simply return false; some implementations will exhibit the former behavior and some will exhibit the latter. More generally, attempting an operation on an ineligible element whose completion would not result in the insertion of an ineligible element into the collection may throw an exception or it may succeed, at the option of the implementation. Such exceptions are marked as "optional" in the specification for this interface.
 * 
 * It is up to each collection to determine its own synchronization policy. In the absence of a stronger guarantee by the implementation, undefined behavior may result from the invocation of any method on a collection that is being mutated by another thread; this includes direct invocations, passing the collection to a method that might perform invocations, and using an existing iterator to examine the collection.
 *
 * @package PXL
 * @subpackage Core
 * @author R. Mansveld <ronald@pixelindustries.com>
 * @version 1.0
 */
interface Collection extends \IteratorAggregate, \Countable, \Serializable {
	/**
	 * Constructs a new Collection. When an existing Collection is passed as a parameter to the constructor, a copy of the passed collection will be made.
	 * @param Collection $c If provided, a copy of this collection will be made.
	 */
	public function __construct(Collection $c = null);

	/**
	 * Ensures that this collection contains the specified element (optional operation). Returns true if this collection changed as a result of the call. (Returns false if this collection does not permit duplicates and already contains the specified element.)
	 * Collections that support this operation may place limitations on what elements may be added to this collection. In particular, some collections will refuse to add null elements, and others will impose restrictions on the type of elements that may be added. Collection classes should clearly specify in their documentation any restrictions on what elements may be added.
	 *
	 * If a collection refuses to add a particular element for any reason other than that it already contains the element, it must throw an exception (rather than returning false). This preserves the invariant that a collection always contains the specified element after this call returns.
	 * 
	 * @param  mixed   $e Element whose presence in this collection is to be ensured.
	 * @return boolean    <true> if this collection changed as a result of the call.
	 * @throws BadMethodCallException If the add operation is not supported by this collection.
	 */
	public function add($e);

	/**
	 * Adds all of the elements in the specified collection to this collection (optional operation). The behavior of this operation is undefined if the specified collection is modified while the operation is in progress. (This implies that the behavior of this call is undefined if the specified collection is this collection, and this collection is nonempty.)
	 * @param  Collection $c Collection containing elements to be added to this collection.
	 * @return boolean       <true> if this collection changed as a result of the call
	 * @throws BadMethodCallException If the addAll operation is not supported by this collection.
	 */
	public function addAll(Collection $c);

	/**
	 * Removes all of the elements from this collection (optional operation). The collection will be empty after this method returns.
	 * @return void
	 * @throws BadMethodCallException If the clear operation is not supported by this collection.
	 */
	public function clear();

	/**
	 * Returns true if this collection contains the specified element. More formally, returns true if and only if this collection contains at least one element e such that (o==null ? e==null : o === e).
	 * @param  mixed   $o Element whose presence in this collection is to be tested
	 * @return boolean    <true> if this collection contains the specified element
	 */
	public function contains($o);

	/**
	 * Returns true if this collection contains all of the elements in the specified collection.
	 * @param  Collection $c Collection to be checked for containment in this collection
	 * @return boolean       <true> if this collection contains all of the elements in the specified collection
	 */
	public function containsAll(Collection $c);

	/**
	 * Returns true if this collection contains no elements.
	 * @return boolean <true> if this collection contains no elements
	 */
	public function isEmpty();

	/**
	 * Returns an iterator over the elements in this collection. There are no guarantees concerning the order in which the elements are returned (unless this collection is an instance of some class that provides a guarantee).
	 * @return Iterator An Iterator over the elements in this collection
	 */
	//public function getIterator();

	/**
	 * Removes a single instance of the specified element from this collection, if it is present (optional operation). More formally, removes an element e such that (o==null ? e==null : o === e), if this collection contains one or more such elements. Returns true if this collection contained the specified element (or equivalently, if this collection changed as a result of the call).
	 * @param  mixed   $o Element to be removed from this collection, if present
	 * @return boolean    <true> if an element was removed as a result of this call
	 * @throws BadMethodCallException If the remove operation is not supported by this collection.
	 */
	public function remove($o);

	/**
	 * Removes all of this collection's elements that are also contained in the specified collection (optional operation). After this call returns, this collection will contain no elements in common with the specified collection.
	 * @param  Collection $c Collection containing elements to be removed from this collection
	 * @return boolean       <true> if this collection changed as a result of the call
	 * @throws BadMethodCallException If the removeAll operation is not supported by this collection.
	 */
	public function removeAll(Collection $c);

	/**
	 * Retains only the elements in this collection that are contained in the specified collection (optional operation). In other words, removes from this collection all of its elements that are not contained in the specified collection.
	 * @param  Collection $c Collection containing elements to be retained in this collection
	 * @return boolean       <true> if this collection changed as a result of the call
	 * @throws BadMethodCallException If the retainAll operation is not supported by this collection.
	 */
	public function retainAll(Collection $c);

	/**
	 * Returns the number of elements in this collection. If this collection contains more than PHP_INT_MAX elements, returns PHP_INT_MAX.
	 * @return int The number of elements in this collection.
	 */
	//public function count();

	/**
	 * Returns an array containing all of the elements in this collection. If this collection makes any guarantees as to what order its elements are returned by its iterator, this method must return the elements in the same order.
	 * The returned array will be "safe" in that no references to it are maintained by this collection. (In other words, this method must allocate a new array even if this collection is backed by an array). The caller is thus free to modify the returned array.
	 *
	 * This method acts as bridge between array-based and collection-based APIs.
	 * 
	 * @return array An array containing all of the elements in this collection.
	 */
	public function toArray();

	public function fromArray(array $a, $replace = true);
}
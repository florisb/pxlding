<?php

namespace PXL\Core\Collection;

/**
 * An ordered collection (also known as a sequence). The user of this interface has precise control over where in the list each element is inserted. The user can access elements by their integer index (position in the list), and search for elements in the list.
 * 
 * Unlike sets, lists typically allow duplicate elements. More formally, lists typically allow pairs of elements e1 and e2 such that e1 === e2, and they typically allow multiple null elements if they allow null elements at all. It is not inconceivable that someone might wish to implement a list that prohibits duplicates, by throwing runtime exceptions when the user attempts to insert them, but we expect this usage to be rare.
 * 
 * The Enumerated interface places additional stipulations, beyond those specified in the Collection interface, on the contracts of the iterator, add, and remove methods. Declarations for other inherited methods are also included here for convenience.
 * 
 * The Enumerated interface provides four methods for positional (indexed) access to list elements. Lists (like arrays) are zero based. Note that these operations may execute in time proportional to the index value for some implementations (the LinkedList class, for example). Thus, iterating over the elements in a list is typically preferable to indexing through it if the caller does not know the implementation.
 * 
 * The Enumerated interface provides a special iterator, called a ListIterator, that allows element insertion and replacement, and bidirectional access in addition to the normal operations that the Iterator interface provides. A method is provided to obtain a list iterator that starts at a specified position in the list.
 * 
 * The Enumerated interface provides two methods to search for a specified object. From a performance standpoint, these methods should be used with caution. In many implementations they will perform costly linear searches.
 * 
 * The Enumerated interface provides two methods to efficiently insert and remove multiple elements at an arbitrary point in the list.
 * 
 * Note: While it is permissible for lists to contain themselves as elements, extreme caution is advised: some methods are no longer well defined on such a list.
 * 
 * Some list implementations have restrictions on the elements that they may contain. For example, some implementations prohibit null elements, and some have restrictions on the types of their elements. Attempting to add an ineligible element throws an exception, typically InvalidArgumentException or OutOfBoundsException. Attempting to query the presence of an ineligible element may throw an exception, or it may simply return false; some implementations will exhibit the former behavior and some will exhibit the latter. More generally, attempting an operation on an ineligible element whose completion would not result in the insertion of an ineligible element into the list may throw an exception or it may succeed, at the option of the implementation. Such exceptions are marked as "optional" in the specification for this interface.
 * 
 */
interface Enumerated extends Collection {
	/**
	 * Constructs a new Collection. When an existing Collection is passed as a parameter to the constructor, a copy of the passed collection will be made.
	 * @param Collection $c If provided, a copy of this collection will be made.
	 */
	//public function __construct(Collection $c = null);

	/**
	 * Ensures that this Enumerated contains the specified element (optional operation). Returns true if this Enumerated changed as a result of the call. (Returns false if this Enumerated does not permit duplicates and already contains the specified element.)
	 * Enumerateds that support this operation may place limitations on what elements may be added to this Enumerated. In particular, some Enumerateds will refuse to add null elements, and others will impose restrictions on the type of elements that may be added. Enumerated classes should clearly specify in their documentation any restrictions on what elements may be added.
	 *
	 * Shifts the element currently at that position (if any) and any subsequent elements to the right (adds one to their indices) if an index has been provided.
	 *
	 * If a list refuses to add a particular element for any reason other than that it already contains the element, it must throw an exception (rather than returning false). This preserves the invariant that a list always contains the specified element after this call returns.
	 * 
	 * @param  mixed    $e Element whose presence in this list is to be ensured.
	 * @param  int|null $i Position to add the element, or at the end of the list if null.
	 * @return boolean     <true> if this list changed as a result of the call.
	 * @throws BadMethodCallException If the add operation is not supported by this list.
	 * @throws OutOfBoundsException if $i < 0 or $i > count()
	 */
	//public function add($e, $i = null);

	/**
	 * Adds all of the elements in the specified collection to this list (optional operation). Shifts the element currently at that position (if any) and any subsequent elements to the right (increases their indices). The new elements will appear in this list in the order that they are returned by the specified collection's iterator. The behavior of this operation is undefined if the specified collection is modified while the operation is in progress. (This implies that the behavior of this call is undefined if the specified collection is this list, and this list is nonempty.)
	 * @param  Collection $c Collection containing elements to be added to this list.
	 * @param  int|null   $i Position to add the elements, or at the end of the list if null.
	 * @return boolean       <true> if this list changed as a result of the call
	 * @throws BadMethodCallException If the addAll operation is not supported by this list.
	 */
	//public function addAll(Collection $c, $i = null);

	/**
	 * Removes all of the elements from this list (optional operation). The list will be empty after this method returns.
	 * @return void
	 * @throws BadMethodCallException If the clear operation is not supported by this list.
	 */
	//public function clear();

	/**
	 * Returns true if this list contains the specified element. More formally, returns true if and only if this list contains at least one element e such that (o==null ? e==null : o === e).
	 * @param  mixed   $o Element whose presence in this list is to be tested
	 * @return boolean    <true> if this list contains the specified element
	 */
	//public function contains($o);

	/**
	 * Returns true if this list contains all of the elements in the specified collection.
	 * @param  Collection $c Collection to be checked for containment in this list
	 * @return boolean       <true> if this list contains all of the elements in the specified collection
	 */
	//public function containsAll(Collection $c);

	/**
	 * Returns true if this list contains no elements.
	 * @return boolean <true> if this list contains no elements
	 */
	//public function isEmpty();

	/**
	 * Returns an iterator over the elements in this list in proper sequence.
	 * @return Iterator An Iterator over the elements in this list.
	 */
	//public function getIterator();

	/**
	 * Returns a list iterator of the elements in this list (in proper sequence), starting at the specified position in this list. The specified index indicates the first element that would be returned by an initial call to current.
	 * @param  integer $index       Index of first element to be returned from the list iterator (by a call to the current method)
	 * @return SeekableIterator     A seekable iterator of the elements in this list (in proper sequence), starting at the specified position in this list
	 * @throws OutOfBoundsException If the index is out of range (index < 0 || index > size())
	 */
	public function listIterator($index = 0);

	/**
	 * Removes a single instance of the specified element from this list, if it is present (optional operation). More formally, removes an element e such that (o==null ? e==null : o === e), if this list contains one or more such elements. Returns true if this list contained the specified element (or equivalently, if this list changed as a result of the call).
	 * @param  mixed   $o Element to be removed from this list, if present
	 * @return boolean    <true> if an element was removed as a result of this call
	 * @throws BadMethodCallException If the remove operation is not supported by this list.
	 */
	//public function remove($o);

	/**
	 * Removes the element at the specified position in this list (optional operation). Shifts any subsequent elements to the left (subtracts one from their indices). Returns the element that was removed from the list.
	 * @param  int $i  The index of the element to be removed
	 * @return mixed   The element previously at the specified position
	 * @throws BadMethodCallException If the removeFrom operation is not supported by this list.
	 * @throws OutOfBoundsException if $i < 0 or $i >= count()
	 */
	public function removeFrom($i);

	/**
	 * Removes all of this list's elements that are also contained in the specified collection (optional operation). After this call returns, this list will contain no elements in common with the specified collection.
	 * @param  Collection $c Collection containing elements to be removed from this list
	 * @return boolean       <true> if this list changed as a result of the call
	 * @throws BadMethodCallException If the removeAll operation is not supported by this list.
	 */
	//public function removeAll(Collection $c);

	/**
	 * Retains only the elements in this list that are contained in the specified collection (optional operation). In other words, removes from this list all of its elements that are not contained in the specified collection.
	 * @param  Collection $c Collection containing elements to be retained in this list
	 * @return boolean       <true> if this list changed as a result of the call
	 * @throws BadMethodCallException If the retainAll operation is not supported by this list.
	 */
	//public function retainAll(Collection $c);

	/**
	 * Returns the number of elements in this list. If this list contains more than PHP_INT_MAX elements, returns PHP_INT_MAX.
	 * @return int The number of elements in this list.
	 */
	//public function count();

	/**
	 * Returns an array containing all of the elements in this list in proper sequence (from first to last element).
	 * The returned array will be "safe" in that no references to it are maintained by this list. (In other words, this method must allocate a new array even if this list is backed by an array). The caller is thus free to modify the returned array.
	 * 
	 * This method acts as bridge between array-based and collection-based APIs.
	 * 
	 * @return array An array containing all of the elements in this list.
	 */
	//public function toArray();

	/**
	 * Returns the element at the specified position in this list.
	 * @param  int $i Index of the element to return
	 * @return mixed  The element at the specified position in this list
	 * @throws OutOfBoundsException if $i < 0 or $i >= count()
	 */
	public function get($i);

	/**
	 * Replaces the element at the specified position in this list with the specified element (optional operation).
	 * @param int    $i  Index of the element to replace
	 * @param mixed  $e  Element to be stored at the specified position
	 * @return mixed     The element previously at the specified position
	 * @throws BadMethodCallException If the set operation is not supported by this list.
	 */
	public function set($i, $e);

	/**
	 * Returns a view of the portion of this list between the specified fromIndex, inclusive, and toIndex, exclusive. (If fromIndex and toIndex are equal, the returned list is empty.) The returned list is backed by this list, so non-structural changes in the returned list are reflected in this list, and vice-versa. The returned list supports all of the optional list operations supported by this list.
	 * This method eliminates the need for explicit range operations (of the sort that commonly exist for arrays). Any operation that expects a list can be used as a range operation by passing a subList view instead of a whole list. For example, the following idiom removes a range of elements from a list:
	 * 
	 *       list.subList(from, to).clear();
	 *  
	 * Similar idioms may be constructed for indexOf and lastIndexOf, and all of the algorithms in the Collections class can be applied to a subList.
	 * The semantics of the list returned by this method become undefined if the backing list (i.e., this list) is structurally modified in any way other than via the returned list. (Structural modifications are those that change the size of this list, or otherwise perturb it in such a fashion that iterations in progress may yield incorrect results.)
	 * 
	 * @param  int $from Low endpoint (inclusive) of the subList
	 * @param  int $to   High endpoint (exclusive) of the subList
	 * @return List      A view of the specified range within this list
	 * @throws OutOfBoundsException  For an illegal endpoint index value (from < 0 || to > size || from > to)
	 */
	public function subList($from, $to);

	/**
	 * Returns the index of the first occurrence of the specified element in this list, or -1 if this list does not contain the element. More formally, returns the lowest index i such that (o==null ? get(i)==null : o === get(i)), or -1 if there is no such index
	 * @param  mixed $o The element to search for
	 * @return int      The index of the first occurrence of the specified element in this list, or -1 if this list does not contain the element
	 */
	public function indexOf($o);

	/**
	 * Returns the index of the last occurrence of the specified element in this list, or -1 if this list does not contain the element. More formally, returns the highest index i such that (o==null ? get(i)==null : o === get(i)), or -1 if there is no such index.
	 * @param  mixed $o The element to search for
	 * @return int      The index of the last occurrence of the specified element in this list, or -1 if this list does not contain the element
	 */
	public function lastIndexOf($o);
}
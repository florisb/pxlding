<?php

namespace PXL\Core\Collection;

/**
 * A Set that further provides a total ordering on its elements. The elements are ordered using their natural ordering, or by a {@see Comparator} typically provided at sorted set creation time. The set's iterator will traverse the set in ascending element order. Several additional operations are provided to take advantage of the ordering. (This interface is the set analogue of {@see SortedMap}.)
 * 
 * All elements inserted into a sorted set must be accepted by the specified comparator. Furthermore, all such elements must be mutually comparable: comparator.compare(e1, e2) must not throw an InvalidArgumentException for any elements e1 and e2 in the sorted set. Attempts to violate this restriction will cause the offending method or constructor invocation to throw an InvalidArgumentException.
 * 
 * Note that the ordering maintained by a sorted set (whether or not an explicit comparator is provided) must be consistent with equals if the sorted set is to correctly implement the Set interface. (See the {@see Comparator} interface for a precise definition of consistent with equals.) This is so because the Set interface is defined in terms of the equals operation, but a sorted set performs all element comparisons using its compare method, so two elements that are deemed equal by this method are, from the standpoint of the sorted set, equal. The behavior of a sorted set is well-defined even if its ordering is inconsistent with equals; it just fails to obey the general contract of the Set interface.
 * 
 * All general-purpose sorted set implementation classes should provide four "standard" constructors: 1) A void (no arguments) constructor, which creates an empty sorted set sorted according to the natural ordering of its elements. 2) A constructor with a single argument of type Comparator, which creates an empty sorted set sorted according to the specified comparator. 3) A constructor with a single argument of type Collection, which creates a new sorted set with the same elements as its argument, sorted according to the natural ordering of the elements. 4) A constructor with a single argument of type SortedSet, which creates a new sorted set with the same elements and the same ordering as the input sorted set.
 * 
 * Note: several methods return subsets with restricted ranges. Such ranges are half-open, that is, they include their low endpoint but not their high endpoint (where applicable). If you need a closed range (which includes both endpoints), and the element type allows for calculation of the successor of a given value, merely request the subrange from lowEndpoint to successor(highEndpoint). For example, suppose that s is a sorted set of strings. The following idiom obtains a view containing all of the strings in s from low to high, inclusive:
 * 
 *    SortedSet<String> sub = s.subSet(low, high+"\0");
 * A similar technique can be used to generate an open range (which contains neither endpoint). The following idiom obtains a view containing all of the Strings in s from low to high, exclusive:
 *    SortedSet<String> sub = s.subSet(low+"\0", high);
 *    
 */
interface SortedSet extends Set {
	/**
	 * Creates a new SortedSet
	 * @param null|Comparator|Collection|SortedSet $ccs See class-documentation for more info
	 */
	public function __construct($ccs = null);

	/**
	 * Returns the comparator used to order the elements in this set, or null if this set uses the natural ordering of its elements.
	 * @return Comparator The comparator used to order the elements in this set, or null if this set uses the natural ordering of its elements.
	 */
	public function comparator();

	/**
	 * Returns a view of the portion of this set whose elements range from fromElement, inclusive, to toElement, exclusive. (If fromElement and toElement are equal, the returned set is empty.) The returned set is backed by this set, so changes in the returned set are reflected in this set, and vice-versa. The returned set supports all optional set operations that this set supports.
	 * The returned set will throw an IllegalArgumentException on an attempt to insert an element outside its range.
	 * 
	 * @param  mixed $fromElement Low endpoint (inclusive) of the returned set.
	 * @param  mixed $toElement   High endpoint (exclusive) of the returned set.
	 * @return SortedSet          A view of the portion of this set whose elements range from fromElement, inclusive, to toElement, exclusive.
	 * @throws InvalidArgumentException If fromElement and toElement cannot be compared to one another using this set's comparator (or, if the set has no comparator, using natural ordering). Implementations may, but are not required to, throw this exception if fromElement or toElement cannot be compared to elements currently in the set.
	 * @throws OutOfRangeException If fromElement is greater than toElement; or if this set itself has a restricted range, and fromElement or toElement lies outside the bounds of the range
	 */
	public function subSet($fromElement, $toElement);

	/**
	 * Returns a view of the portion of this set whose elements are strictly less than toElement. The returned set is backed by this set, so changes in the returned set are reflected in this set, and vice-versa. The returned set supports all optional set operations that this set supports.
	 * The returned set will throw an IllegalArgumentException on an attempt to insert an element outside its range.
	 * 
	 * @param  mixed $toElement High endpoint (exclusive) of the returned set.
	 * @return SortedSet        A view of the portion of this set whose elements are strictly less than toElement.
	 * @throws InvalidArgumentException If toElement is not compatible with this set's comparator (or, if the set has no comparator, ordering cannot be obtained through natural ordering). Implementations may, but are not required to, throw this exception if fromElement cannot be compared to elements currently in the set..
	 * @throws OutOfRangeException If this set itself has a restricted range, and toElement lies outside the bounds of the range.
	 */
	public function headSet($toElement);

	/**
	 * Returns a view of the portion of this set whose elements are greater than or equal to fromElement. The returned set is backed by this set, so changes in the returned set are reflected in this set, and vice-versa. The returned set supports all optional set operations that this set supports.
	 * The returned set will throw an IllegalArgumentException on an attempt to insert an element outside its range.
	 * @param  mixed $fromElement Low endpoint (inclusive) of the returned set.
	 * @return SortedSet          A view of the portion of this set whose elements are greater than or equal to fromElement.
	 * @throws InvalidArgumentException If fromElement is not compatible with this set's comparator (or, if the set has no comparator, ordering cannot be obtained through natural ordering). Implementations may, but are not required to, throw this exception if fromElement cannot be compared to elements currently in the set.
	 * @throws OutOfRangeException If this set itself has a restricted range, and fromElement lies outside the bounds of the range.
	 */
	public function tailSet($fromElement);

	/**
	 * Returns the first (lowest) element currently in this set.
	 * @return mixed The first (lowest) element currently in this set.
	 * @throws RangeException If this set is empty.
	 */
	public function first();

	/**
	 * Returns the last (highest) element currently in this set.
	 * @return mixed The last (highest) element currently in this set.
	 * @throws RangeException If this set is empty.
	 */
	public function last();
}
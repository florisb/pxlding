<?php

namespace PXL\Core\Collection;

/**
 * A {@see SortedSet} extended with navigation methods reporting closest matches for given search targets. Methods lower, floor, ceiling, and higher return elements respectively less than, less than or equal, greater than or equal, and greater than a given element, returning null if there is no such element. A NavigableSet may be accessed and traversed in either ascending or descending order. The descendingSet method returns a view of the set with the senses of all relational and directional methods inverted. The performance of ascending operations and views is likely to be faster than that of descending ones. This interface additionally defines methods pollFirst and pollLast that return and remove the lowest and highest element, if one exists, else returning null. Subsets of any NavigableSet must implement the NavigableSet interface.
 * 
 * The return values of navigation methods may be ambiguous in implementations that permit null elements. However, even in this case the result can be disambiguated by checking contains(null). To avoid such issues, implementations of this interface are encouraged to not permit insertion of null elements. (Note that sorted sets of Comparable elements intrinsically do not permit null.)
 * 
 * Methods subSet(E, E), headSet(E), and tailSet(E) are specified to return SortedSet to allow existing implementations of SortedSet to be compatibly retrofitted to implement NavigableSet, but extensions and implementations of this interface are encouraged to override these methods to return NavigableSet.
 */
interface NavigableSet extends SortedSet {
	/**
	 * Returns the greatest element in this set strictly less than the given element, or null if there is no such element.
	 * @param  mixed $e The value to match.
	 * @return mixed    the greatest element less than e, or null if there is no such element
	 * @throws InvalidArgumentException If the specified element cannot be compared with the elements currently in the set.
	 */
	public function lower($e);

	/**
	 * Returns the greatest element in this set less than or equal to the given element, or null if there is no such element.
	 * @param  mixed $e The value to match.
	 * @return mixed    The greatest element less than or equal to e, or null if there is no such element.
	 * @throws InvalidArgumentException If the specified element cannot be compared with the elements currently in the set.
	 */
	public function floor($e);

	/**
	 * Returns the least element in this set greater than or equal to the given element, or null if there is no such element.
	 * @param  mixed $e The value to match.
	 * @return mixed    The least element greater than or equal to e, or null if there is no such element.
	 * @throws InvalidArgumentException If the specified element cannot be compared with the elements currently in the set.
	 */
	public function ceiling($e);

	/**
	 * Returns the least element in this set strictly greater than the given element, or null if there is no such element.
	 * @param  mixed $e The value to match.
	 * @return mixed    The least element greater than e, or null if there is no such element.
	 * @throws InvalidArgumentException If the specified element cannot be compared with the elements currently in the set.
	 */
	public function higher($e);

	/**
	 * Retrieves and removes the first (lowest) element, or returns null if this set is empty.
	 * @return mixed The first element, or null if this set is empty.
	 */
	public function pollFirst();

	/**
	 * Retrieves and removes the last (highest) element, or returns null if this set is empty.
	 * @return mixed The last element, or null if this set is empty.
	 */
	public function pollLast();

	/**
	 * Returns a reverse order view of the elements contained in this set. The descending set is backed by this set, so changes to the set are reflected in the descending set, and vice-versa. If either set is modified while an iteration over either set is in progress (except through the iterator's own remove operation), the results of the iteration are undefined.
	 * The returned set has an ordering equivalent to Collections.reverseOrder(comparator()). The expression s.descendingSet().descendingSet() returns a view of s essentially equivalent to s
	 * @return NavigableSet A reverse order view of this set.
	 */
	public function descendingSet();

	/**
	 * Returns an iterator over the elements in this set, in descending order. Equivalent in effect to descendingSet().iterator().
	 * @return Iterator An iterator over the elements in this set, in descending order.
	 */
	public function descendingIterator();
}
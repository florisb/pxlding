<?php

namespace PXL\Core\Collection;

/**
 * A comparison function, which imposes a total ordering on some collection of objects. Comparators can be passed to a sort method (such as Collections.sort or Arrays.sort) to allow precise control over the sort order. Comparators can also be used to control the order of certain data structures (such as sorted sets or sorted maps), or to provide an ordering for collections of objects that don't have a natural ordering.
 * 
 * The ordering imposed by a comparator c on a set of elements S is said to be consistent with equals if and only if c.compare(e1, e2)==0 has the same boolean value as e1 === e2 for every e1 and e2 in S.
 * 
 * Caution should be exercised when using a comparator capable of imposing an ordering inconsistent with equals to order a sorted set (or sorted map). Suppose a sorted set (or sorted map) with an explicit comparator c is used with elements (or keys) drawn from a set S. If the ordering imposed by c on S is inconsistent with equals, the sorted set (or sorted map) will behave "strangely." In particular the sorted set (or sorted map) will violate the general contract for set (or map), which is defined in terms of equals.
 * 
 * For example, suppose one adds two elements a and b such that (a === b && c.compare(a, b) != 0) to an empty TreeSet with comparator c. The second add operation will return true (and the size of the tree set will increase) because a and b are not equivalent from the tree set's perspective, even though this is contrary to the specification of the Set.add method.
 * 
 * Note: It is generally a good idea for comparators to also implement Serializable, as they may be used as ordering methods in serializable data structures (like TreeSet, TreeMap). In order for the data structure to serialize successfully, the comparator (if provided) must implement Serializable.
 * 
 * For the mathematically inclined, the relation that defines the imposed ordering that a given comparator c imposes on a given set of objects S is:
 * 
 *        {(x, y) such that c.compare(x, y) <= 0}.
 *  
 * The quotient for this total order is:
 * 
 *        {(x, y) such that c.compare(x, y) == 0}.
 *  
 * It follows immediately from the contract for compare that the quotient is an equivalence relation on S, and that the imposed ordering is a total order on S. When we say that the ordering imposed by c on S is consistent with equals, we mean that the quotient for the ordering is the equivalence relation defined by the objects' equals(Object) method(s):
 *      {(x, y) such that x === y}.
 *      
 */
interface Comparator {
	/**
	 * Compares its two arguments for order. Returns a negative integer, zero, or a positive integer as the first argument is less than, equal to, or greater than the second.
	 * In the foregoing description, the notation sgn(expression) designates the mathematical signum function, which is defined to return one of -1, 0, or 1 according to whether the value of expression is negative, zero or positive.
	 * 
	 * The implementor must ensure that sgn(compare(x, y)) == -sgn(compare(y, x)) for all x and y. (This implies that compare(x, y) must throw an exception if and only if compare(y, x) throws an exception.)
	 * 
	 * The implementor must also ensure that the relation is transitive: ((compare(x, y)>0) && (compare(y, z)>0)) implies compare(x, z)>0.
	 * 
	 * Finally, the implementor must ensure that compare(x, y)==0 implies that sgn(compare(x, z))==sgn(compare(y, z)) for all z.
	 * 
	 * It is generally the case, but not strictly required that (compare(x, y)==0) == (x === y). Generally speaking, any comparator that violates this condition should clearly indicate this fact. The recommended language is "Note: this comparator imposes orderings that are inconsistent with equals."
	 * 
	 * @param  mixed $o1 The first object to be compared.
	 * @param  mixed $o2 The second object to be compared.
	 * @return int       A negative integer, zero, or a positive integer as the first argument is less than, equal to, or greater than the second.
	 * @throws InvalidArgumentException If the arguments' types prevent them from being compared by this comparator.
	 */
	public function compare($o1, $o2);
}
<?php

namespace PXL\Core\Collection;

require_once('Collection.php');

/**
 * A collection designed for holding elements prior to processing. Besides basic Collection operations, queues provide additional insertion, extraction, and inspection operations. Each of these methods exists in two forms: one throws an exception if the operation fails, the other returns a special value (either null or false, depending on the operation). The latter form of the insert operation is designed specifically for use with capacity-restricted Queue implementations; in most implementations, insert operations cannot fail.
 *            ____________________________________________
 *   _________| Throws exception | Returns special value |
 *  | Insert  | add(e)           | offer(e)              |
 *  | Remove  | remove()         | poll()                |
 *  |_Examine_|_element()________|_peek()________________|
 *  
 * Queues typically, but do not necessarily, order elements in a FIFO (first-in-first-out) manner. Among the exceptions are priority queues, which order elements according to a supplied comparator, or the elements' natural ordering, and LIFO queues (or stacks) which order the elements LIFO (last-in-first-out). Whatever the ordering used, the head of the queue is that element which would be removed by a call to {@see Queue::remove} or {@see Queue::poll}. In a FIFO queue, all new elements are inserted at the tail of the queue. Other kinds of queues may use different placement rules. Every Queue implementation must specify its ordering properties.
 * 
 * The {@see Queue::offer} method inserts an element if possible, otherwise returning false. This differs from the {@see Collection::add} method, which can fail to add an element only by throwing an unchecked exception. The offer method is designed for use when failure is a normal, rather than exceptional occurrence, for example, in fixed-capacity (or "bounded") queues.
 * 
 * The {@see Queue::remove} and {@see Queue::poll} methods remove and return the head of the queue. Exactly which element is removed from the queue is a function of the queue's ordering policy, which differs from implementation to implementation. The remove() and poll() methods differ only in their behavior when the queue is empty: the remove() method throws an exception, while the poll() method returns null.
 * 
 * The {@see Queue::element} and {@see Queue::peek} methods return, but do not remove, the head of the queue.
 * 
 * Queue implementations generally do not allow insertion of null elements, although some implementations, such as LinkedList, do not prohibit insertion of null. Even in the implementations that permit it, null should not be inserted into a Queue, as null is also used as a special return value by the poll method to indicate that the queue contains no elements.
 * 
 */
interface Queue extends Collection {
	/**
	 * Inserts the specified element into this queue if it is possible to do so immediately without violating capacity restrictions, returning true upon success and throwing an OverflowException if no space is currently available.
	 * @param mixed $e The element to add.
	 * @return true (as specified by {@see Collection::add})
	 * @throws OverflowException If the element cannot be added at this time due to capacity restrictions.
	 */
	public function add($e);

	/**
	 * Inserts the specified element into this queue if it is possible to do so immediately without violating capacity restrictions. When using a capacity-restricted queue, this method is generally preferable to {@see Queue::add}, which can fail to insert an element only by throwing an exception.
	 * @param  mixed $e The element to add.
	 * @return boolean  <true> if the element was added to this queue, else <false>
	 */
	public function offer($e);

	/**
	 * Retrieves and removes the head of this queue. This method differs from {@see Queue::poll} only in that it throws an exception if this queue is empty.
	 * @param void $o Not used, merely here to be compatible with Collection::remove
	 * @return mixed The head of this queue.
	 * @throws UnderflowException If this queue is empty.
	 */
	public function remove($o = null);

	/**
	 * Retrieves and removes the head of this queue, or returns null if this queue is empty.
	 * @return mixed|null The head of this queue, or null if this queue is empty.
	 */
	public function poll();

	/**
	 * Retrieves, but does not remove, the head of this queue. This method differs from {@see Queue::peek} only in that it throws an exception if this queue is empty.
	 * @return mixed The head of this queue.
	 * @throws UnderflowException If this queue is empty.
	 */
	public function element();

	/**
	 * Retrieves, but does not remove, the head of this queue, or returns null if this queue is empty.
	 * @return mixed|null The head of this queue, or null if this queue is empty.
	 */
	public function peek();
}
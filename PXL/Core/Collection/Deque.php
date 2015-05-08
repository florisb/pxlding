<?php

namespace PXL\Core\Collection;

require_once('Queue.php');

/**
 * A linear collection that supports element insertion and removal at both ends. The name deque is short for "double ended queue" and is usually pronounced "deck". Most Deque implementations place no fixed limits on the number of elements they may contain, but this interface supports capacity-restricted deques as well as those with no fixed size limit.
 * 
 * This interface defines methods to access the elements at both ends of the deque. Methods are provided to insert, remove, and examine the element. Each of these methods exists in two forms: one throws an exception if the operation fails, the other returns a special value (either null or false, depending on the operation). The latter form of the insert operation is designed specifically for use with capacity-restricted Deque implementations; in most implementations, insert operations cannot fail.
 * 
 * The twelve methods described above are summarized in the following table:
 * 
 *           _______________________________________________________________________
 *           |_______First_Element_(Head)_______|_______Last_Element_(Tail)________|
 * __________|_Throws_exception_|_Special_value_|_Throws_exception_|_Special_value_|
 * | Insert  | addFirst(e)      | offerFirst(e) | addLast(e)       | offerLast(e)  |
 * | Remove  | removeFirst()    | pollFirst()   | removeLast()     | pollLast()    |
 * |_Examine_|_getFirst()_______|_peekFirst()___|_getLast()________|_peekLast()____|
 * 
 * This interface extends the {@see Queue} interface. When a deque is used as a queue, FIFO (First-In-First-Out) behavior results. Elements are added at the end of the deque and removed from the beginning. The methods inherited from the Queue interface are precisely equivalent to Deque methods as indicated in the following table:
 *
 * __________________________________________
 * | Queue Method | Equivalent Deque Method |
 * | add(e)       | addLast(e)              |
 * | offer(e)     | offerLast(e)            |
 * | remove()     | removeFirst()           |
 * | poll()       | pollFirst()             |
 * | element()    | getFirst()              |
 * |_peek()_______|_peekFirst()_____________|
 * 
 * Deques can also be used as LIFO (Last-In-First-Out) stacks. This interface should be used in preference to the legacy Stack class. When a deque is used as a stack, elements are pushed and popped from the beginning of the deque. Stack methods are precisely equivalent to Deque methods as indicated in the table below:
 *
 * __________________________________________
 * | Stack Method | Equivalent Deque Method |
 * | push(e)      | addFirst(e)             |
 * | pop()        | removeFirst()           |
 * |_peek()_______|_peekFirst()_____________|
 * 
 * Note that the peek method works equally well when a deque is used as a queue or a stack; in either case, elements are drawn from the beginning of the deque.
 * 
 * This interface provides two methods to remove interior elements, {@see Deque::removeFirstOccurrence} and {@see Deque::removeLastOccurrence}.
 * 
 * Unlike the List interface, this interface does not provide support for indexed access to elements.
 * 
 * While Deque implementations are not strictly required to prohibit the insertion of null elements, they are strongly encouraged to do so. Users of any Deque implementations that do allow null elements are strongly encouraged not to take advantage of the ability to insert nulls. This is so because null is used as a special return value by various methods to indicated that the deque is empty.
 * 
 */
interface Deque extends Queue {
	/**
	 * Inserts the specified element at the front of this deque if it is possible to do so immediately without violating capacity restrictions. When using a capacity-restricted deque, it is generally preferable to use method {@see Deque::offerFirst}.
	 * @param mixed $e The element to add.
	 * @throws OverflowException If the element cannot be added at this time due to capacity restrictions.
	 */
	public function addFirst($e);

	/**
	 * Inserts the specified element at the end of this deque if it is possible to do so immediately without violating capacity restrictions. When using a capacity-restricted deque, it is generally preferable to use method {@see Deque::offerLast}.
	 * This method is equivalent to {@see Queue::add}.
	 * @param mixed $e The element to add.
	 * @throws OverflowException If the element cannot be added at this time due to capacity restrictions.
	 */
	public function addLast($e);

	/**
	 * Inserts the specified element at the front of this deque unless it would violate capacity restrictions. When using a capacity-restricted deque, this method is generally preferable to the {@see Deque::addFirst} method, which can fail to insert an element only by throwing an exception.
	 * @param  mixed $e The element to add.
	 * @return bool     <true> if the element was added to this deque, else <false>.
	 */
	public function offerFirst($e);

	/**
	 * Inserts the specified element at the end of this deque unless it would violate capacity restrictions. When using a capacity-restricted deque, this method is generally preferable to the {@see Deque::addLast} method, which can fail to insert an element only by throwing an exception.
	 * @param  mixed $e The element to add.
	 * @return bool     <true> if the element was added to this deque, else <false>.
	 */
	public function offerLast($e);

	/**
	 * Retrieves and removes the first element of this deque. This method differs from {@see Deque::pollFirst} only in that it throws an exception if this deque is empty.
	 * @return mixed The head of this deque.
	 * @throws UnderflowException If this deque is empty.
	 */
	public function removeFirst();

	/**
	 * Retrieves and removes the last element of this deque. This method differs from {@see Deque::pollLast} only in that it throws an exception if this deque is empty.
	 * @return mixed The tail of this deque.
	 * @throws UnderflowException If this deque is empty.
	 */
	public function removeLast();

	/**
	 * Retrieves and removes the first element of this deque, or returns null if this deque is empty.
	 * @return mixed|null The head of this deque, or null if this deque is empty.
	 */
	public function pollFirst();

	/**
	 * Retrieves and removes the last element of this deque, or returns null if this deque is empty.
	 * @return mixed|null The tail of this deque, or null if this deque is empty.
	 */
	public function pollLast();

	/**
	 * Retrieves, but does not remove, the first element of this deque. This method differs from {@see Deque::peekFirst} only in that it throws an exception if this deque is empty.
	 * @return mixed The head of this deque.
	 * @throws UnderflowException If this deque is empty.
	 */
	public function getFirst();

	/**
	 * Retrieves, but does not remove, the last element of this deque. This method differs from {@see Deque::peekLast} only in that it throws an exception if this deque is empty.
	 * @return mixed The tail of this deque.
	 * @throws UnderflowException If this deque is empty.
	 */
	public function getLast();

	/**
	 * Retrieves, but does not remove, the first element of this deque, or returns null if this deque is empty.
	 * @return mixed|null The head of this deque, or null if this deque is empty.
	 */
	public function peekFirst();

	/**
	 * Retrieves, but does not remove, the last element of this deque, or returns null if this deque is empty.
	 * @return mixed|null The tail of this deque, or null if this deque is empty.
	 */
	public function peekLast();

	/**
	 * Removes the first occurrence of the specified element from this deque. If the deque does not contain the element, it is unchanged. More formally, removes the first element e such that (o==null ? e==null : o === e) (if such an element exists). Returns true if this deque contained the specified element (or equivalently, if this deque changed as a result of the call).
	 * @param  mixed $o Element to be removed from this deque, if present
	 * @return boolean  <true> if an element was removed as a result of this call
	 */
	public function removeFirstOccurrence($o);

	/**
	 * Removes the last occurrence of the specified element from this deque. If the deque does not contain the element, it is unchanged. More formally, removes the last element e such that (o==null ? e==null : o === e) (if such an element exists). Returns true if this deque contained the specified element (or equivalently, if this deque changed as a result of the call).
	 * @param  mixed $o Element to be removed from this deque, if present
	 * @return boolean  <true> if an element was removed as a result of this call
	 */
	public function removeLastOccurrence($o);

	/**
	 * Inserts the specified element into the queue represented by this deque (in other words, at the tail of this deque) if it is possible to do so immediately without violating capacity restrictions, returning true upon success and throwing an OverflowException if no space is currently available. When using a capacity-restricted deque, it is generally preferable to use {@see Deque::offer}.
	 * This method is equivalent to {@see Deque::addLast}.
	 * @param mixed $e The element to add.
	 * @return true (as specified by {@see Collection::add})
	 * @throws OverflowException If the element cannot be added at this time due to capacity restrictions.
	 */
	public function add($e);

	/**
	 * Inserts the specified element into the queue represented by this deque (in other words, at the tail of this deque) if it is possible to do so immediately without violating capacity restrictions, returning true upon success and false if no space is currently available. When using a capacity-restricted deque, this method is generally preferable to the {@see Deque::add} method, which can fail to insert an element only by throwing an exception.
	 * This method is equivalent to {@see Deque::offerLast}.
	 * @param  mixed $e The element to add.
	 * @return boolean  <true> if the element was added to this deque, else <false>.
	 */
	public function offer($e);

	/**
	 * Retrieves and removes the head of the queue represented by this deque (in other words, the first element of this deque), or returns null if this deque is empty.
	 * This method is equivalent to {@see Deque::pollFirst}.
	 * @return mixed|null The first element of this deque, or null if this deque is empty.
	 */
	public function poll();

	/**
	 * Retrieves, but does not remove, the head of the queue represented by this deque (in other words, the first element of this deque). This method differs from {@see Deque::peek} only in that it throws an exception if this deque is empty.
	 * This method is equivalent to {@see Deque::getFirst}.
	 * @return mixed The head of the queue represented by this deque.
	 * @throws UnderflowException If this deque is empty.
	 */
	public function element();

	/**
	 * Retrieves, but does not remove, the head of the queue represented by this deque (in other words, the first element of this deque), or returns null if this deque is empty.
	 * This method is equivalent to {@see Deque::peekFirst}.
	 * @return mixed|null The head of the queue represented by this deque, or null if this deque is empty.
	 */
	public function peek();

	/**
	 * Pushes an element onto the stack represented by this deque (in other words, at the head of this deque) if it is possible to do so immediately without violating capacity restrictions, returning true upon success and throwing an OverflowException if no space is currently available.
	 * This method is equivalent to {@see Deque::addFirst}.
	 * @param  mixed $e The element to push.
	 * @return boolean  <true> if the element was added to this deque.
	 * @throws OverflowException If the element cannot be added at this time due to capacity restrictions.
	 */
	public function push($e);

	/**
	 * Pops an element from the stack represented by this deque. In other words, removes and returns the first element of this deque.
	 * This method is equivalent to {@see Deque::removeFirst}.
	 * @return mixed The element at the front of this deque (which is the top of the stack represented by this deque).
	 * @throws UnderflowException If this deque is empty.
	 */
	public function pop();

	/**
	 * Removes the first occurrence of the specified element from this deque. If the deque does not contain the element, it is unchanged. More formally, removes the first element e such that (o==null ? e==null : o === e) (if such an element exists). Returns true if this deque contained the specified element (or equivalently, if this deque changed as a result of the call).
	 * This method is equivalent to {@see Deque::removeFirstOccurrence}.
	 * @param  mixed $o Element to be removed from this deque, if present.
	 * @return true if an element was removed as a result of this call.
	 */
	public function remove($o = null);

	/**
	 * Returns an iterator over the elements in this deque in reverse sequential order. The elements will be returned in order from last (tail) to first (head).
	 * @return Iterator An iterator over the elements in this deque in reverse sequence
	 */
	public function descendingIterator();
}
<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jesus M. Castagnetto <jmcastagnetto@php.net>                |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once "PEAR.php";
require_once "Math/Vector/Tuple.php";
require_once "Math/Vector/VectorOp.php";

/**
 * General Vector class
 * Originally this class was part of NumPHP (Numeric PHP package)
 *
 * @author	Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version	1.0
 * @access	public
 * @package	Math_Vector
 */

class Math_Vector {

	/**
	 * Math_Tuple object
	 *
	 * @var		object	Math_Tuple
	 * @access	private
	 */
	var $tuple;

	/**
	 * Constructor for Math_Vector
	 *
	 * @param	mixed	$data	a Math_Tuple object, a Math_Vetctor object, or an array of numeric data
	 * @access	public
	 * @return	object	Math_Vector (or Pear_Error on error)
	 */
	function Math_Vector($data) /*{{{*/
	{
		if (is_array($data))
			$tuple = new Math_Tuple($data);
		else if (is_object($data) && get_class($data) == "math_tuple")
			$tuple = $data;
		else if (is_object($data) && get_class($data) == "math_vector")
			$tuple = $data->getTuple();
		else
			$tuple = null;
		$this->tuple = $tuple;
	}/*}}}*/

	/**
	 * Checks if the vector has been correctly initialized
	 *
	 * @access	public
	 * @return	boolean
	 */
	function isValid() /*{{{*/
	{
		return (!is_null($this->tuple) && is_object($this->tuple) &&
				get_class($this->tuple) == "math_tuple");
	}/*}}}*/

	/**
	 * Returns the square of the vector's length
	 *
	 * @access	public
	 * @return	float
	 */
	function lengthSquared() /*{{{*/
	{
		$n = $this->size();
		$sum = 0;
		for ($i=0; $i < $n; $i++)
			$sum += pow($this->tuple->getElement($i), 2);
		return $sum;
	}/*}}}*/

	/**
	 * Returns the length of the vector
	 *
	 * @access	public
	 * @return	float
	 */
	function length() /*{{{*/
	{
		return sqrt($this->lengthSquared());
	}/*}}}*/

	/**
	 * Returns the magnitude of the vector. Alias of length
	 *
	 * @access	public
	 * @return	float
	 */
	function magnitude()/*{{{*/
	{
		return $this->length();
	}/*}}}*/

	/**
	 * Normalizes the vector, converting it to a unit vector
	 *
	 * @access	public
	 * @returns	void
	 */
	function normalize() /*{{{*/
	{
		$n = $this->size();
		$length = $this->length();
		for ($i=0; $i < $n; $i++) {
			$this->tuple->setElement($i, $this->tuple->getElement($i)/$length);
		}
	}/*}}}*/

	/**
	 * returns the Math_Tuple object corresponding to the vector
	 *
	 * @access	public
	 * @returns	object	Math_Tuple
	 */
	function getTuple() /*{{{*/
	{
		return $this->tuple;
	}/*}}}*/

	/**
	 * Returns the number of elements (dimensions) of the vector
	 *
	 * @access	public
	 * @returns	float
	 */
	function size() /*{{{*/
	{
		return $this->tuple->getSize();
	}/*}}}*/

	/**
	 * Reverses the direction of the vector negating each element
	 *
	 * @access	public
	 * @return	void
	 */
	function reverse() /*{{{*/
	{
		$n = $this->size();
		for ($i=0; $i < $n; $i++)
			$this->tuple->setElement($i, -1 * $this->tuple->getElement($i));
	}/*}}}*/

	/**
	 * Conjugates the vector. Alias of reverse.
	 *
	 * @access	public
	 * @return	void
	 *
	 * @see		reverse()
	 */
	function conjugate()/*{{{*/
	{
		$this->reverse();
	}/*}}}*/

	/**
	 * Scales the vector elements by the given factor
	 *
	 * @access	public
	 * @param	float	$f	scaling factor
	 * @return	mixed	void on success, a Pear_Error object otherwise
	 */
	function scale($f) /*{{{*/
	{
		if (is_numeric($f)) {
			$n = $this->size();
			$t = $this->getTuple();
			for ($i=0; $i < $n; $i++)
				$this->set($i, $this->get($i) * $f);
		} else {
			return new Pear_Error("Requires a numeric factor and a Math_Vector object");
		}
	}/*}}}*/

	/**
	 * Sets the value of a element
	 *
	 * @access	public
	 * @param	integer	$i	the index of the element
	 * @param	numeric	$value	the value to assign to the element
	 * @return	mixed	true on success, a Pear_Error object otherwise
	 */
	function set($i, $value) /*{{{*/
	{
		$res = $this->tuple->setElement($i, $value);
		if (Pear::isError($res))
			return $res;
		else
			return true;
	}/*}}}*/

	/**
	 * Gets the value of a element
	 *
	 * @access	public
	 * @param	integer	$i	the index of the element
	 * @return	mixed	the element value (numeric) on success, a Pear_Error object otherwise
	 */
	function get($i) {/*{{{*/
		$res = $this->tuple->getElement($i);
		return $res;
	}/*}}}*/

	/**
	 * Returns the distance to another vector
	 *
	 * @access	public
	 * @param	object	Math_Vector
	 * @return	float
	 */
	function distance($vector)/*{{{*/
	{
		$n = $this->size();
		$sum = 0;
		if (Math_VectorOp::isVector($vector))
			if ($vector->size() == $n) {
				for($i=0; $i < $n; $i++)
					$sum += pow(($this->tuple->getElement($i) -
						$vector->tuple->getElement($i)), 2);
				return sqrt($sum);
			} else {
				return new Pear_Error("Vector has to be of the same size");
			}
		else
			return new Pear_Error("Wrong parameter type, expecting a Math_Vector object");
	}/*}}}*/

	/**
	 * Returns a simple string representation of the vector
	 *
	 * @access	public
	 * @return	string
	 */
	function toString() /*{{{*/
	{
		return "Vector: < ".implode(", ",$this->tuple->getData())." >";
	}/*}}}*/
}

?>

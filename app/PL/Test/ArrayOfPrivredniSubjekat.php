<?php
namespace App\PL\Test;

class ArrayOfPrivredniSubjekat implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * @var PrivredniSubjekat[] $PrivredniSubjekat
     */
    protected $PrivredniSubjekat = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return PrivredniSubjekat[]
     */
    public function getPrivredniSubjekat()
    {
      return $this->PrivredniSubjekat;
    }

    /**
     * @param PrivredniSubjekat[] $PrivredniSubjekat
     * @return ArrayOfPrivredniSubjekat
     */
    public function setPrivredniSubjekat(array $PrivredniSubjekat = null)
    {
      $this->PrivredniSubjekat = $PrivredniSubjekat;
      return $this;
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset An offset to check for
     * @return boolean true on success or false on failure
     */
    public function offsetExists($offset)
    {
      return isset($this->PrivredniSubjekat[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return PrivredniSubjekat
     */
    public function offsetGet($offset)
    {
      return $this->PrivredniSubjekat[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param PrivredniSubjekat $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
      if (!isset($offset)) {
        $this->PrivredniSubjekat[] = $value;
      } else {
        $this->PrivredniSubjekat[$offset] = $value;
      }
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to unset
     * @return void
     */
    public function offsetUnset($offset)
    {
      unset($this->PrivredniSubjekat[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return PrivredniSubjekat Return the current element
     */
    public function current()
    {
      return current($this->PrivredniSubjekat);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
      next($this->PrivredniSubjekat);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
      return key($this->PrivredniSubjekat);
    }

    /**
     * Iterator implementation
     *
     * @return boolean Return the validity of the current position
     */
    public function valid()
    {
      return $this->key() !== null;
    }

    /**
     * Iterator implementation
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
      reset($this->PrivredniSubjekat);
    }

    /**
     * Countable implementation
     *
     * @return PrivredniSubjekat Return count of elements
     */
    public function count()
    {
      return count($this->PrivredniSubjekat);
    }

}

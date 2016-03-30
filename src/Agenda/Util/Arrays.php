<?php namespace Agenda\Util;

use Closure;

class Arrays {

    /**
     * Get the first element of array
     *
     * @param array $array
     * @return array
     */
    public static function first(array $array)
    {
        return array_shift($array);
    }

    /**
     * Get the last element of array
     *
     * @param array $array
     * @return array
     */
    public static function last(array $array)
    {
        return array_pop($array);
    }

    /**
     *  Find the first item in an array that passes the truth test
     *
     * @param array $array
     * @param Closure $closure
     * @return mixed
     */
    public static function find(array $array, Closure $closure)
    {
        foreach ($array as $key => $value) {
            if ($closure($value, $key)) {
                return $value;
            }
        }

        return;
    }

    /**
     * Removes a particular value from an array (numeric or associative)
     *
     * @param array $array
     * @param mixed $value
     * @return array
     */
    public static function remove(array $array, $value)
    {
        $isNumericArray = true;
        foreach ($array as $key => $item) {
            if ($item === $value) {
                if (!is_int($key)) {
                    $isNumericArray = false;
                }
                unset($array[$key]);
            }
        }
        if ($isNumericArray) {
            $array = array_values($array);
        }

        return $array;
    }

    /**
     * Iterate over an array and modify the array's value
     *
     * @param array $array
     * @return array
     */
    public static function each($array, Closure $closure)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $closure($value, $key);
        }
        return $array;
    }

     /**
      * Sort a collection by value or by a closure
      * If the sorter is null, the collection is sorted naturally
      *
      * @param array $collection
      * @param Closure|null $sorter
      * @param string $direction asc or desc
      * @return array
      */
    public static function sort(array $collection, $sorter = null, $direction = 'asc')
    {
        // Get correct PHP constant for direction
        $direction = (strtolower($direction) === 'desc') ? SORT_DESC : SORT_ASC;

        // Transform all values into their results
        if ($sorter) {
            $results = static::each($collection, function ($value) use ($sorter) {
                return $sorter($value);
            });
        } else {
            $results = $collection;
        }

        // Sort by the results and replace by original values
        array_multisort($results, $direction, SORT_REGULAR, $collection);

        return $collection;
    }

    /**
     * Merge one or more arrays
     *
     * @param array $arrays,...
     * @return array
     */
    public static function merge()
    {
        return call_user_func_array('array_merge', func_get_args());
    }

    /**
     * Create new array using closure
     *
     * @param Closure $closure
     * @param array $arrays
     * @return array
     */
    public static function map(Closure $closure, array $array)
    {
        return array_map($closure, $array);
    }

    /**
     * Filters elements of an array using a callback function
     *
     * @param array $arrays
     * @param Closure $closure
     * @return array
     */
    public static function filter(array $array, Closure $closure)
    {
        return array_filter($array, $closure);
    }

    /**
     * Group elements of an array using a given callback function, using the
     * result as key of new array returned
     *
     * @param array $array
     * @param Closure $grouper
     * @return array
     */
    public static function group(array $array, Closure $grouper)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $groupKey = call_user_func($grouper, $value, $key);

            if (! is_null($groupKey)) {
                if (! isset($result[$groupKey])) {
                    $result[$groupKey] = array();
                }
                $result[$groupKey][] = $value;
            }
        }

        return $result;
    }
}


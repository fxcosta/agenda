<?php

require_once __DIR__.'/../vendor/autoload.php';

use Agenda\Util\Arrays;

class ArraysTest extends PHPUnit_Framework_TestCase
{
    public function testGroup()
    {
        $arr = array(
            array(
                'age' => 25,
                'name' => 'Skaffo'
            ),
            array(
                'age' => 29,
                'name' => 'Skarfuglia'
            ),
            array(
                'age' => 50,
                'name' => 'Skakko matto'
            ),
            array(
                'age' => 22,
                'name' => 'Gio Va'
            ),
            array(
                'age' => 19,
                'name' => 'Giovannino'
            ),
            array(
                'age' => 25,
                'name' => 'Giorgio'
            ),
            array(
                'age' => 30,
                'name' => 'Loris'
            )
        );

        $results = Arrays::group($arr, function ($guy)
        {
            if ($guy['age'] >= 30) {
                return null;
            }

            return substr(strtolower($guy['name']), 0, 3);
        });

        $this->assertEquals($results, array(
            'ska' => array(
                array(
                    'age' => 25,
                    'name' => 'Skaffo'
                ),
                array(
                    'age' => 29,
                    'name' => 'Skarfuglia'
                )
            ),
            'gio' => array(
                array(
                    'age' => 22,
                    'name' => 'Gio Va'
                ),
                array(
                    'age' => 19,
                    'name' => 'Giovannino'
                ),
                array(
                    'age' => 25,
                    'name' => 'Giorgio'
                )
            )
        ));
    }
}

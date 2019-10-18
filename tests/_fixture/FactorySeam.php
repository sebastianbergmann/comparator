<?php
namespace SebastianBergmann\Comparator;

class FactorySeam extends Factory {
    public static function unsetInstance() {
        self::$instance = null;
    }
}

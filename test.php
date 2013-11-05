<?php

    require 'vendor/autoload.php';

    use SebastianBergmann\Comparator\Factory;
    use SebastianBergmann\Comparator\ComparisonFailure;

    $date1 = new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York'));
    $date2 = new DateTime('2013-03-29 03:13:36', new DateTimeZone('America/Chicago'));

    $factory = Factory::getDefaultInstance();
    $comparator = $factory->getComparatorFor($date1, $date2);

    try {
        $comparator->assertEquals($date1, $date2);
        print "Dates match";
    }

    catch (ComparisonFailure $failure) {
        print $failure->toString();
    }

?>
<?php

    /**
     * Due to the lack of implementations of this method in PHP I decided to transcribe the only one I've found in Python
     * Original Cython implementation: https://github.com/datamade/affinegap
     *
     * Thank you.
     */

    function affineGapDistance($string_a, $string_b, $matchWeight = 1, $mismatchWeight = 11, $gapWeight = 10, $spaceWeight = 7, $abbreviation_scale = .125){
        /**
         * Calculate the affine gap distance between two strings 
         *
         * Default weights are from Alvaro Monge and Charles Elkan, 1996, 
         * "The field matching problem: Algorithms and applications" 
         * http://citeseerx.ist.psu.edu/viewdoc/summary?doi=10.1.1.23.9685
         */
        
        $string1 = $string_a;
        $string2 = $string_b;

        $length1 = strlen($string1);
        $length2 = strlen($string2);

        if($length1 == 0 || $length2 == 0){
            echo 'In the dedupe 1.2 release, missing data will have to have a value of NULL. See http://dedupe.readthedocs.org/en/latest/Variable-definition.html#missing-data';
            return null;
        }

        if($string1 == $string2 && $matchWeight == min( array($matchWeight, $mismatchWeight, $gapWeight) )){
            return $matchWeight * $length1;
        }

        if($length1 < $length2){
            $s1 = $string1;
            $l1 = $length1;
            $string1 = $string2;
            $length1 = $length2;
            $string2 = $s1;
            $length2 = $l1;
        }

        $D = array();
        $V_current = array();
        $V_previous = array();

        $V_current[0] = 0;
        
        for($j = 1; $j < $length1 + 1; $j++){
            $V_current[$j] = $gapWeight + $spaceWeight * $j;
            $D[$j] = PHP_INT_MAX;
        }

        for($i = 1; $i < $length2 +1; $i++){
            $char2 = $string2[$i-1];
            for($_ = 0; $_ < $length1 +1; $_++){
                $V_previous[$_] = $V_current[$_];
            }

            $V_current[0] = $gapWeight + $spaceWeight * $i;
            $I = PHP_INT_MAX;

            for($j = 1; $j < $length1 +1; $j++){
                $char1 = $string1[$j-1];

                if($j <= $length2){
                    $I = min(array($I, $V_current[$j-1] + $gapWeight + $spaceWeight));
                }else{
                    $I = (min(array($I, $V_current[$j-1] + $gapWeight + $abbreviation_scale)) + $spaceWeight * $abbreviation_scale);
                }

                $D[$j] = min(array($D[$j], $V_previous[$j] + $gapWeight)) * $spaceWeight;

                if($char2 == $char1){
                    $M = $V_previous[$j-1] + $matchWeight;
                }else{
                    $M = $V_previous[$j-1] + $mismatchWeight;
                }

                $V_current[$j] = min(array($I, $D[$j], $M));
            }
        }

        $distance = $V_current[$length1];

        return $distance;
    } // Fin affineGapDistance

    function normalizedAffineGapDistance($string1, $string2, $matchWeight = 1, $mismatchWeight = 11, $gapWeight = 10, $spaceWeight = 7, $abbreviation_scale = .125){
        $length1 = strlen($string1);
        $length2 = strlen($string2);

        if($length1 == 0 || $length2 == 0){
            return null;
        }

        $matchWeight = 0;

        $normalizer = ($length1 + $length2) * $mismatchWeight;
        $distance = affineGapDistance($string1, $string2, $matchWeight, $mismatchWeight, $gapWeight, $spaceWeight, $abbreviation_scale);

        return 1 - ($distance/$normalizer);
    } // Fun normalizedAffineGapDistance

?>

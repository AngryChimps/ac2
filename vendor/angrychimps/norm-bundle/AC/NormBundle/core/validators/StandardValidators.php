<?php


namespace AC\NormBundle\core\validators;


function min($fieldData, $min) {
    return ($fieldData >= $min);
}

function max($fieldData, $max) {
    return ($fieldData <= $max);
}


<?php
namespace WcLocations;


function sign($value) {
    return (int)($value > 0) - (int)($value < 0);
}
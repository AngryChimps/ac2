<?php


namespace AngryChimps\ApiBundle\Services;


class CategoriesService {
    public function getCategories() {
        return array(
            array("id" => 100, "name" => 'Home Needs'),
            array("id" => 101, "name" => 'Cleaning'),
            array("id" => 102, "name" => 'Plumbing'),
            array("id" => 103, "name" => 'Roofing'),
            array("id" => 200, "name" => 'Health'),
            array("id" => 201, "name" => 'Dentists'),
            array("id" => 202, "name" => 'Pediatricians'),
            array("id" => 203, "name" => 'Podiatrists'),
            array("id" => 300, "name" => 'Beauty'),
            array("id" => 301, "name" => 'Hair Salons'),
            array("id" => 302, "name" => 'Nail Salons'),
            array("id" => 303, "name" => 'Tanning'),
        );
    }
} 
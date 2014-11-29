<?php


namespace AngryChimps\ApiBundle\Services;


class CategoriesService {
    public function getCategories() {
        return array(
            array("id" => 100, "name" => 'Home Needs', 'icon' => "path/to/icon.png"),
            array("id" => 101, "name" => 'Cleaning', 'icon' => "path/to/icon.png"),
            array("id" => 102, "name" => 'Plumbing', 'icon' => "path/to/icon.png"),
            array("id" => 103, "name" => 'Roofing', 'icon' => "path/to/icon.png"),
            array("id" => 200, "name" => 'Health', 'icon' => "path/to/icon.png"),
            array("id" => 201, "name" => 'Dentists', 'icon' => "path/to/icon.png"),
            array("id" => 202, "name" => 'Pediatricians', 'icon' => "path/to/icon.png"),
            array("id" => 203, "name" => 'Podiatrists', 'icon' => "path/to/icon.png"),
            array("id" => 300, "name" => 'Beauty', 'icon' => "path/to/icon.png"),
            array("id" => 301, "name" => 'Hair Salons', 'icon' => "path/to/icon.png"),
            array("id" => 302, "name" => 'Nail Salons', 'icon' => "path/to/icon.png"),
            array("id" => 303, "name" => 'Tanning', 'icon' => "path/to/icon.png"),
        );
    }
} 
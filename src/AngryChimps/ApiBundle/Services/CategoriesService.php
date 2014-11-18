<?php


namespace AngryChimps\ApiBundle\Services;


class CategoriesService {
    public function getCategories() {
        return array(
            'Home Needs' => array('Cleaning', 'Plumbing', 'Roofing'),
            'Health' => array('Dentists', 'Pediatricians', 'Podiatrists'),
            'Beauty' => array('Hair Salons', 'Nail Salons', 'Tanning'),
        );
    }
} 
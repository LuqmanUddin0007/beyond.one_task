<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->loadProducts($manager);
    }

    private function loadProducts(ObjectManager $manager)
    {
        $productData = [
            ['product_id' => 'P001', 'product_name' => 'Product 1', 'stock_available' => 50],
            ['product_id' => 'P002', 'product_name' => 'Product 2', 'stock_available' => 30],
            ['product_id' => 'P003', 'product_name' => 'Product 3', 'stock_available' => 20],
        ];

        foreach ($productData as $data) {
            $product = new Product();
            $product
                ->setProductId($data['product_id'])
                ->setProductName($data['product_name'])
                ->setStockAvailable($data['stock_available'])
                ->setCreatedAt(new \DateTime())  // Set 'created_at' value
                ->setUpdatedAt(new \DateTime()); // Set 'updated_at' val

            $manager->persist($product);
        }

        $manager->flush();
    }

}

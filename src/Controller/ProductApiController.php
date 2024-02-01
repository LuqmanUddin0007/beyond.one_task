<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;

class ProductApiController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
      $this->doctrine = $doctrine;
    }

    /**
     * Endpoint 1: Server Time
     * @return JsonResponse
     */
    #[Route('/servertime', name: 'servertime', methods: ['GET'])]
    public function getServerTime(): JsonResponse
    {
        return $this->json(['servertime' => (new \DateTime())->format(\DateTime::ISO8601)]);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/filtered-posts', name: 'filtered_posts', methods: ['GET'])]
    public function getFilteredPosts(): JsonResponse
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://jsonplaceholder.typicode.com/posts');

        $posts = $response->toArray();
        $filteredPosts = array_values(array_filter($posts, function ($post) {
            return stripos($post['body'], 'minima') !== false;
        }));

        return $this->json($filteredPosts);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/products', name: 'retrieve_products', methods: ['GET'])]
    public function retrieveProducts(): JsonResponse
    {
        $entityManager = $this->doctrine->getManagerForClass(Product::class);
        $products = $entityManager->getRepository(Product::class)->findAll();

        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product->getId(),
                'product_name' => $product->getProductName(),
            ];
        }

        return $this->json($formattedProducts);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/check-order-fulfillment', name: 'check_order_fulfillment', methods: ['POST'])]
    public function checkOrderFulfillment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        $product = $this->doctrine->getRepository(Product::class)->findOneBy(['product_id' => $productId]);

        if ($product && $product->getStockAvailable() >= $quantity) {
            return $this->json(['fulfillable' => true]);
        }

        return $this->json(['fulfillable' => false]);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/create-product', name: 'create_product', methods: ['POST'])]
    
    public function createProduct(): JsonResponse
    {
        $entityManager = $this->doctrine->getManager();

        $product = new Product();
        $product->setProductId('unique_product_id'); // replace with actual value
        $product->setProductName('Sample Product');
        $product->setStockAvailable(100);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json(['message' => 'Product created successfully']);
    }
}
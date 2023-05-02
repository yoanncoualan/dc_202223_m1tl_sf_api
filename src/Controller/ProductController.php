<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Service\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product', methods:['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findAll();

        return new JsonResponse($products, 200);
    }

    #[Route('/product/{id}', name:'one_product', methods:['GET'])]
    public function one_product($id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->findOneById($id);

        if($product == null){
            return new JsonResponse('Produit introuvable', 404);
        }

        return new JsonResponse($product, 200);
    }

    #[Route('/product', name:'product_add', methods:['POST'])]
    public function add(Request $r, EntityManagerInterface $em, Validator $validator): Response
    {
        $product = new Product();
        $product->setTitle($r->get('title'))
            ->setPrice($r->get('price'))
            ->setQuantity($r->get('quantity'));
            
        // Essaie de récupérer en base la catégory qui correspond au paramètre reçu
        $category = $em->getRepository(Category::class)->findOneBy(['id' => $r->get('category')]);

        if($category == null){
            return new JsonResponse('Catégorie introuvable', 404);
        }

        $product->setCategory($category);

        $isValid = $validator->isValid($product);
        if($isValid !== true){
            return new JsonResponse($isValid, 400);
        }

        $em->persist($product);
        $em->flush();

        return new JsonResponse('ok', 200);
    }

    #[Route('/product/{id}', name:'update_product', methods:['PATCH'])]
    public function update(
        Request $r, 
        EntityManagerInterface $em, 
        Product $product = null, 
        Validator $v
    ): Response
    {
        if($product == null){
            return new JsonResponse('Produit introuvable', 404);
        }

        $params = 0;

        // Si on a reçu une catégorie en paramètres
        if($r->get('category') != null){
            // On regarde en base si elle existe
            $category = $em->getRepository(Category::class)->findOneBy(['id' => $r->get('category')]);

            // Si elle n'existe pas
            if($category == null){
                // On retourne une erreur
                return new JsonResponse('Catégorie introuvable', 404);
            }

            $params++;
            // Si elle existe, on l'attribue au produit
            $product->setCategory($category);
        }

        if($r->get('title') != null){
            $params++;
            $product->setTitle($r->get('title'));
        }
        if($r->get('price') != null){
            $params++;
            $product->setPrice($r->get('price'));
        }
        if($r->get('quantity') != null){
            $params++;
            $product->setQuantity($r->get('quantity'));
        }

        if($params > 0){
            $isValid = $v->isValid($product);
            if($isValid !== true){
                return new JsonResponse($isValid, 400);
            }

            $em->persist($product);
            $em->flush();

            return new JsonResponse('ok', 200);
        }
        else{
            return new JsonResponse('Aucune donnée reçue', 200);
        }
    }

    #[Route('/product/{id}', name:'delete_product', methods:['DELETE'])]
    public function delete(Product $product = null, EntityManagerInterface $em): Response
    {
        if($product == null){
            return new JsonResponse('Produit introuvable', 404);
        }

        $em->remove($product);
        $em->flush();

        return new JsonResponse('Produit supprimé', 200);
    }
}

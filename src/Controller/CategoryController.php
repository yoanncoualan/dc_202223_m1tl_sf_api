<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_category', methods:['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();

        return new JsonResponse($categories);
    }

    #[Route('/category', name:'category_add', methods:['POST'])]
    public function add(Request $r, EntityManagerInterface $em, ValidatorInterface $v) : Response
    {
        $category = new Category();
        $category->setName($r->get('name')); // Récupère le paramètre 'name' de la requête et l'assigne à l'objet

        $errors = $v->validate($category); // Vérifie que l'objet soit conforme avec les validations (assert)
        if(count($errors) > 0){
            // S'il y a au moins une erreur
            $e_list = [];
            foreach($errors as $e){ // On parcours toutes les erreurs
                $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
            }

            return new JsonResponse($e_list, 400); // On retourne le tableau de messages
        }

        $em->persist($category);
        $em->flush();

        return new JsonResponse('success', 201);
    }

    #[Route('/category/{id}', name:'category_update', methods:['PATCH'])]
    public function update(Category $category = null) : Response
    {
        if($category === null){
            return new JsonResponse('Catégorie introuvable', 404); // Retourne un status 404 car le 204 ne retourne pas de message
        }

        return new JsonResponse('Success', 200);
    }
}

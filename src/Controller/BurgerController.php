<?php

namespace App\Controller;

use App\Entity\Burger;
use App\Entity\Category;
use App\Form\BurgerType;
use App\Form\CategoryType;
use App\Repository\BurgerRepository;
use PHPUnit\Framework\Constraint\IsFalse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BurgerController extends AbstractController
{
    /**
     * @Route("/burger", name="burger")
     */
    public function index(BurgerRepository $repo): Response
    {
        $burgers = $repo->findAll();

        return $this->render('burger/index.html.twig', [
            'controller_name' => 'BurgerController',
            'burgers' => $burgers
        ]);
    }

    /**
     * @Route("burger/{id}", name="showBurger")
     * 
     * 
     */
    public function show(Burger $burger)
    {


        return $this->render('burger/show.html.twig', [
            'burger' => $burger
        ]);
    }


    /**
     * @Route("burger/create",  name="createBurger", priority = 2)
     * @Route("burger/edit/{id}", name="editBurger", priority = 2)
     */
    public function create(Request $req, ObjectManager $manager, Burger $burger = null): Response
    {
        $modeCreate = false;

        if (!$burger) {
            $burger = new Burger();
            $modeCreate = true;
        }

        $form = $this->createForm(BurgerType::class, $burger);

        $form->handleRequest($req);

        if ($form->isSubmitted()) {

            // Images
            $imgSended = $form->get('images')->getData();
            if ($imgSended) {
                try {
                    // $originalImgName = pathinfo($imgSended->getClientOriginalName(), PATHINFO_FILENAME);
                    //uniqid()-return string unique that never repeats the same name //guessExtention() - guess the extention of the img (png, jpg.. etc)
                    $newImg = uniqid() . '.' . $imgSended->guessExtension();

                    $imgSended->move(
                        $this->getParameter('burger_images'),
                        $newImg
                    );

                    if ($modeCreate || (!$modeCreate && $imgSended)) {
                        $burger->setImages($newImg);
                    }
                } catch (FileException $e) {
                    throw $e;
                    return $this->redirectToRoute('createBurger');
                }
            }
            // END IMG//
            $manager->persist($burger);
            $manager->flush();

            return $this->redirectToRoute('showBurger', [
                "id" => $burger->getId()

            ]);
        }


        return $this->render('burger/create.html.twig', [
            'form' => $form->createView(),
            'modeCreate' => $modeCreate
        ]);
    }

    /**
     * @Route("/burger/delete/{id}", name="deleteBurger")
     * 
     */
    public function delete(Burger $burger, ObjectManager $manager)
    {

        $manager->remove($burger);
        $manager->flush();


        return $this->redirectToRoute('burger');
    }

    /**
     * @Route("/burger/category/new", name="newCategory")
     *
     */
    public function newGategory(Request $req, ObjectManager $manager)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($req);

        if ($form->isSubmitted()) {

            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('burger');
        }

        return $this->render('burger/create_category.html.twig', [
            'formCategory' => $form->createView()
        ]);
    }
}

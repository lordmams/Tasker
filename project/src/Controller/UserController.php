<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\FileUploader; 
class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="user_profile")
     */
    public function index(): Response
    {
        $user= $this->getUser();
        
        return $this->render('user/index.html.twig', [
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email'  => $user->getEmail(),
            'picture' => $user->getPicture(),
            'nbProjects' => $user->getProjectAdministrator()->count(),
            'projects' => $user->getProjectAdministrator()
        ]);
    }
    
    /**
     * @Route("/edit", name="user_edit_profile")
     */
    public function edit(Request $request, FileUploader $fileUploader,EntityManagerInterface $em): Response{
     
        $user = $this->getUser();
        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            if ($picture) {
                $filename = $fileUploader->upload($picture);
                $user->setPicture($filename);
            }
            
            $em->flush();

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}

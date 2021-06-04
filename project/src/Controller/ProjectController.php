<?php

namespace App\Controller;

use Exception;
use App\Entity\Task;
use App\Entity\Project;
use App\Form\TaskEditType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="project")
     */
    public function index(): Response
    {
        return $this->render('project/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    /**
     * @Route("/{slug}/project", name="project_show")
     */
    public function show(ProjectRepository $rep, $slug): Response
    {
        $isCreator = false;
        $project = $rep->findBy(array('slug' => $slug))[0];
        if($this->getUser() === $project->getUser()){
            $isCreator = true;
        }
        
        return $this->render('project/index.html.twig', [
            'name' => $project->getName(),
            'tasks' => $project->getTask(),
            'members' => $project->getUsers(),
            'admin' => $project->getUser(),
            'chats'=> $project->getChats(),
            'slug' => $project->getSlug(),
            'isCreator' => $isCreator
        ]);
    }

    /**
     * @Route("/add", name="project_add")
     */
    public function addProject(Request $request, SluggerInterface $slug): Response
    {
        $project = new Project;
    
        $user = $this->getUser();
        $name= $request->request->get('name');
        if(!$name){
            return  new Exception('Vous avez pas mis de nom au projet');    
        }
        
        $project->setName($name);
        $project->setUser($user);
        $project->setSlug($slug->slug($name));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($project);
        $entityManager->flush();
        
        return $this->redirectToRoute('user_profile');
    }

     /**
     * @Route("/{slug}/addTask", name="task_add")
     */
    public function addTask(Request $request,  $slug, ProjectRepository $rep, EntityManagerInterface $em): Response
    {
        $task = new Task;
        $project = $rep->findBy(array('slug' => $slug))[0];
        $form = $this->createForm(TaskEditType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setProject($project);
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('project_show', array('slug' => $slug));
        }

        return $this->render('project/task/taskEdit.html.twig', [
            'form' => $form->createView(),
            'slug' => $slug
        ]);
    }
}

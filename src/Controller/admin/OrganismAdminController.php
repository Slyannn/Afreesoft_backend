<?php

namespace App\Controller\admin;

use App\Entity\OrganismAdmin;
use App\Form\OrganismAdminType;
use App\Repository\OrganismAdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/organism')]
class OrganismAdminController extends AbstractController
{
    #[Route('/', name: 'app_organism_index', methods: ['GET'])]
    public function index(OrganismAdminRepository $organismAdminRepository): Response
    {
        return $this->render('organism_admin/index.html.twig', [
            'organism_admins' => $organismAdminRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_organism_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organismAdmin = new OrganismAdmin();
        $form = $this->createForm(OrganismAdminType::class, $organismAdmin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // $data contient les données du formulaire
            $organismAdmin->setAddress();
            $entityManager->persist($organismAdmin);
            $entityManager->flush();

            return $this->redirectToRoute('app_organism_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organism_admin/new.html.twig', [
            'organism_admin' => $organismAdmin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organism_show', methods: ['GET'])]
    public function show(OrganismAdmin $organismAdmin): Response
    {
        return $this->render('organism_admin/show.html.twig', [
            'organism_admin' => $organismAdmin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_organism_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrganismAdmin $organismAdmin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrganismAdminType::class, $organismAdmin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_organism_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organism_admin/edit.html.twig', [
            'organism_admin' => $organismAdmin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organism_delete', methods: ['POST'])]
    public function delete(Request $request, OrganismAdmin $organismAdmin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$organismAdmin->getId(), $request->request->get('_token'))) {
            $entityManager->remove($organismAdmin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_organism_index', [], Response::HTTP_SEE_OTHER);
    }
}

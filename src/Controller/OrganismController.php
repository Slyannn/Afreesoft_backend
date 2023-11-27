<?php

namespace App\Controller;

use App\Entity\Organism;
use App\Form\OrganismType;
use App\Repository\OrganismRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/organism')]
class OrganismController extends AbstractController
{
    #[Route('/', name: 'app_organism_index', methods: ['GET'])]
    public function index(OrganismRepository $organismRepository): Response
    {
        return $this->render('organism/index.html.twig', [
            'organisms' => $organismRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_organism_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organism = new Organism();
        $form = $this->createForm(OrganismType::class, $organism);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organism);
            $entityManager->flush();

            return $this->redirectToRoute('app_organism_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organism/new.html.twig', [
            'organism' => $organism,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organism_show', methods: ['GET'])]
    public function show(Organism $organism): Response
    {
        return $this->render('organism/show.html.twig', [
            'organism' => $organism,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_organism_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Organism $organism, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrganismType::class, $organism);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_organism_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organism/edit.html.twig', [
            'organism' => $organism,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organism_delete', methods: ['POST'])]
    public function delete(Request $request, Organism $organism, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$organism->getId(), $request->request->get('_token'))) {
            $entityManager->remove($organism);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_organism_index', [], Response::HTTP_SEE_OTHER);
    }
}

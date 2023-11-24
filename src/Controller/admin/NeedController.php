<?php

namespace App\Controller\admin;

use App\Entity\Need;
use App\Form\NeedType;
use App\Repository\NeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/need')]
class NeedController extends AbstractController
{
    #[Route('/', name: 'app_need_index', methods: ['GET'])]
    public function index(NeedRepository $needRepository): Response
    {
        return $this->render('need/index.html.twig', [
            'needs' => $needRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_need_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $need = new Need();
        $form = $this->createForm(NeedType::class, $need);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($need);
            $entityManager->flush();

            return $this->redirectToRoute('app_need_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('need/new.html.twig', [
            'need' => $need,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_need_show', methods: ['GET'])]
    public function show(Need $need): Response
    {
        return $this->render('need/show.html.twig', [
            'need' => $need,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_need_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Need $need, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NeedType::class, $need);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_need_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('need/edit.html.twig', [
            'need' => $need,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_need_delete', methods: ['POST'])]
    public function delete(Request $request, Need $need, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$need->getId(), $request->request->get('_token'))) {
            $entityManager->remove($need);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_need_index', [], Response::HTTP_SEE_OTHER);
    }
}

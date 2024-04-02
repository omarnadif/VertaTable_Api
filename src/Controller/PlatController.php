<?php

namespace App\Controller;

use App\Entity\Plat;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/plat')]
class PlatController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PlatRepository $platRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        PlatRepository $platRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->platRepository = $platRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Retourne la liste de tous les plats.
     */
    #[Route('/', name: 'plat_index', methods: ['GET'])]

    public function index(): JsonResponse
    {
        $plats = $this->platRepository->findAll();
        $data = $this->serializer->serialize($plats, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée un nouveau plat.
     */
    #[Route('/', name: 'plat_new', methods: ['POST'])]

    public function new(Request $request): JsonResponse
    {
        $plat = $this->serializer->deserialize($request->getContent(), Plat::class, 'json');

        $errors = $this->validator->validate($plat);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($plat);
        $this->entityManager->flush();

        return $this->json($plat, Response::HTTP_CREATED);
    }

    /**
     * Affiche un plat spécifique.
     */
    #[Route('/{id}', name: 'plat_show', methods: ['GET'])]

    public function show(Plat $plat): JsonResponse
    {
        $data = $this->serializer->serialize($plat, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Met à jour un plat spécifique.
     */
    #[Route('/{id}', name: 'plat_edit', methods: ['PUT'])]

    public function edit(Request $request, Plat $plat): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Plat::class, 'json', ['object_to_populate' => $plat]);

        $errors = $this->validator->validate($plat);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($plat, Response::HTTP_OK);
    }

    /**
     * Supprime un plat spécifique.
     */
    #[Route('/{id}', name: 'plat_delete', methods: ['DELETE'])]

    public function delete(Plat $plat): JsonResponse
    {
        $this->entityManager->remove($plat);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

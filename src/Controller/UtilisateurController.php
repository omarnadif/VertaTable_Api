<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/user')]
class UtilisateurController extends AbstractController
{
    private EntityManagerInterface $em;
    private UtilisateurRepository $utilisateurRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->em = $em;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Retourne la liste de tous les utilisateurs.
     */
    #[Route('/', name: 'utilisateur_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $utilisateurs = $this->utilisateurRepository->findAll();
        $data = $this->serializer->serialize($utilisateurs, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Crée un nouvel utilisateur.
     */
    #[Route('/', name: 'utilisateur_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $utilisateur = $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json');

        // Valider les données de l'utilisateur
        $errors = $this->validator->validate($utilisateur);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $utilisateur->setPassword(
            $this->passwordHasher->hashPassword($utilisateur, $utilisateur->getPassword())
        );

        $this->em->persist($utilisateur);
        $this->em->flush();

        return $this->json($utilisateur, Response::HTTP_CREATED);
    }

    /**
     * Affiche un utilisateur spécifique.
     */
    #[Route('/{id}', name: 'utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): JsonResponse
    {
        $data = $this->serializer->serialize($utilisateur, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Met à jour un utilisateur spécifique.
     */
    #[Route('/{id}', name: 'utilisateur_edit', methods: ['PUT'])]
    public function edit(Request $request, Utilisateur $utilisateur): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json', ['object_to_populate' => $utilisateur]);

        // Valider les données mises à jour
        $errors = $this->validator->validate($utilisateur);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['password'])) {
            $utilisateur->setPassword(
                $this->passwordHasher->hashPassword($utilisateur, $data['password'])
            );
        }

        $this->em->flush();

        return $this->json($utilisateur, Response::HTTP_OK);
    }

    /**
     * Supprime un utilisateur spécifique.
     */
    #[Route('/{id}', name: 'utilisateur_delete', methods: ['DELETE'])]
    public function delete(Utilisateur $utilisateur): JsonResponse
    {
        $this->em->remove($utilisateur);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Retourne les détails d'un utilisateur spécifique.
     */
    #[Route('/{id}/details', name: 'utilisateur_details', methods: ['GET'])]
    public function getUserDetails(int $id): JsonResponse
    {
        $user = $this->utilisateurRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $userData = [
            'id' => $user->getId(),
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
        ];

        return new JsonResponse($userData, Response::HTTP_OK);
    }
}
